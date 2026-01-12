<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'balance' => $this->balance,
            'currency' => $this->currency,
            'institution' => $this->institution,
            'last_four_digits' => $this->last_four_digits,
            'is_active' => $this->is_active,
        ];
    }
}
