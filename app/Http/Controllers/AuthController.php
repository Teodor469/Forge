<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    //* Ready to use user Authorization for NON Admin users to create accounts
    public function register(RegisterRequest $request)
    {
        $credentials = $request->validated();

        $user = User::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password']),
        ]);

        $token = $user->createToken($user->name)->plainTextToken;

        return response()->json([
            'message' => $user->name . ' has successfully created a profile!',
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        try {
            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                $token = $user->createToken($user->name)->plainTextToken;

                return response()->json([
                    'message' => $user->name . ' has successfully logged in!',
                    'token' => $token,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Credentials are wrong!'
                ], 401);
            }
        } catch(Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => "Something went wrong!"
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
    
            $user->tokens()->delete();
    
            return response()->json([
                'message' => 'successfully logged out!',
            ], 200);
            
        } catch(Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }
    }
}
