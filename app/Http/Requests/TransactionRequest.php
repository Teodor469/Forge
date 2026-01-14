<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class TransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'wallet_id' => ['required', 'exists:wallets,id,user_id,' . auth()->id()],
            'category_id' => ['required', 'exists:categories,id,user_id,' . auth()->id()],
            'amount' => 'required|numeric|between:0.01,9999999999999.99',
            'type' => ['required', new Enum(TransactionType::class)],
            'merchant' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:655',
            'transaction_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ];
    }
}
