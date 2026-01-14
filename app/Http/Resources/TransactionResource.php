<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'category_id' => $this->category_id,
            'amount' => $this->amount,
            'type' => $this->type,
            'merchant' => $this->merchant,
            'description' => $this->description,
            'transaction_date' => $this->transaction_date,
        ];
    }
}
