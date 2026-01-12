<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitForgotPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->input('email');
        $ip = $request->ip();

        $emailKey = 'forgot-password-email:' . $email;
        $emailLimit = 3;
        $emailDecay = 3600;

        $ipKey = 'forgot-password-ip:' . $ip;
        $ipLimit = 10;
        $ipDecay = 3600;

        if (RateLimiter::tooManyAttempts($emailKey, $emailLimit)) {
            $seconds = RateLimiter::availableIn($emailKey);

            Log::warning('Forgot password rate limit was hit', [
                'email' => $email,
                'ip' => $ip,
                'limit_type' => 'email',
                'retry_after' => $seconds,
            ]);

            return response()->json([
                'message' => 'Too many password reset attempts. Please try again later',
                'retry_after' => $seconds,
            ], 429);
        }

        if (RateLimiter::tooManyAttempts($ipKey, $ipLimit)) {
            $seconds = RateLimiter::availableIn($ipKey);

            Log::warning('Forgot password rate limit was hit', [
                'email' => $email,
                'ip' => $ip,
                'limit_type' => 'ip',
                'retry_after' => $seconds,
            ]);

            return response()->json([
                'message' => 'Too many password reset attempts. Please try again later',
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($emailKey, $emailDecay);
        RateLimiter::hit($ipKey, $ipDecay);

        $response = $next($request);

        $emailRemaining = $emailLimit - RateLimiter::attempts($emailKey);
        $response->headers->add([
            'X-RateLimit-Limit' => $emailLimit,
            'X-RateLimit-Remaining' => max(0, $emailRemaining),
        ]);
        return $response;
    }
}
