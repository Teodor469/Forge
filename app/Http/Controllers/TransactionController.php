<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public TransactionService $transactionService;

    public function __construct(TransactionService $transactionService) {
        $this->transactionService = $transactionService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->id();
        //? How to get all the transaction for the specific user
    }
    /**
     * Display listing of transactions specific to a category
     */
    public function transactionsPerCategory(Category $category)
    {
        $transactions = Transaction::where($category->user_id, auth()->id())
                            ->where('category_id', $category->id)
                            ->get();

        return response()->json([
            'message' => 'Returned all transactions for ' . $category,
            'transactions' => TransactionResource::collection($transactions)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request, TransactionService $service)
    {
        $validated = $request->validated();

        try{
            $transaction = $service->createTransaction($validated);
    
            return response()->json([
                'message' => 'Successfully made a transaction!',
                'transaction' => new TransactionResource($transaction),
            ], 201);
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Transaction $transaction)
    {
        //
    }
}
