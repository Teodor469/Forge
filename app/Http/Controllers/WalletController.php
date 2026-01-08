<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;
use function Pest\Laravel\json;

class WalletController extends Controller
{
    public function index()
    {
        $wallets = Wallet::all();
        return response()->json([
            'message' => 'Successfully returned all wallets!',
            'wallets' => WalletResource::collection($wallets),
        ], 200);
    }

    public function store(CreateWalletRequest $request)
    {
        try{
            $validated = $request->validated();
            $wallet = Wallet::create($validated);
    
            return response()->json([
                'message' => 'New wallet created!',
                'wallet' => new WalletResource($wallet),
            ], 201);
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }
    }

    public function show(Wallet $wallet)
    {
        return new WalletResource($wallet);
    }

    public function update(UpdateWalletRequest $request, Wallet $wallet)
    {
        if (auth()->id() !== $wallet->user_id) {
            return response()->json([
                'message' => 'You are unauthorized to perform this action',
            ], 403);
        }

        try {
            $validated = $request->validated();
    
            $wallet->update($validated);
    
            return response()->json([
                'message' => 'Successfully updated wallet!',
                'wallet' => new WalletResource($wallet),
            ], 201);
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }

    }

    public function delete(Wallet $wallet)
    {
        if (auth()->id() !== $wallet->user_id) {
            return response()->json([
                'message' => 'You are unauthorized to perform this action!'
            ], 403);
        }

        try{
            $wallet->delete();
            return response()->json([
                'message' => 'Successfully deleted wallet!'
            ], 200);
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }
    }
}
