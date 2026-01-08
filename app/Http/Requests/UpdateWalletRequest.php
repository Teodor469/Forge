<?php

namespace App\Http\Requests;

use App\WalletType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateWalletRequest extends FormRequest
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
            'name' => 'sometimes|string|min:5|max:40',
            'type' => ['sometimes', new Enum(WalletType::class)],
            'balance' => 'sometimes|numeric|between:0.01,999999999999999999999.99',
            'currency' => 'sometimes|string|min:3|max:3',
            'institution' => 'sometimes|string|min:3|max:40',
            'last_four_digits' => 'sometimes|string|min:4|max:4',
            'is_active' => 'boolean',
        ];
    }
}
