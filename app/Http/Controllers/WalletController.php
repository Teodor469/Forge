<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Http\Resources\WalletResource;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;
use function Pest\Laravel\json;

class WalletController extends Controller
{
    public function index()
    {
        $wallets = Wallet::where('user_id', auth()->id())->get();
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
        $this->authorize('view', $wallet);

        return response()->json([
            'wallet' => new WalletResource($wallet),
        ], 200);
    }

    public function update(UpdateWalletRequest $request, Wallet $wallet)
    {
        $this->authorize('update', $wallet);

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
        $this->authorize('delete', $wallet);

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
