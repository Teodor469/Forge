<?php

namespace App\Http\Requests;

use App\Enums\CurrencyType;
use App\Enums\WalletType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateWalletRequest extends FormRequest
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
            'name' => 'required|string|min:5|max:40',
            'type' => ['required', new Enum(WalletType::class)],
            'balance' => 'required|numeric|between:0,999999999999999999999.99',
            'currency' => ['required', new Enum(CurrencyType::class)],
            'institution' => 'required|string|min:3|max:40',
            'last_four_digits' => 'sometimes|string|min:4|max:4',
            'is_active' => 'boolean',
        ];
    }
}
