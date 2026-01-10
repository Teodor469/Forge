<?php

namespace App\Http\Requests;

use App\Enums\CategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateCategoryRequest extends FormRequest
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
            'name' => 'sometimes|string|min:3',
            'type' => ['sometimes', new Enum(CategoryType::class)],
            'color' => 'sometimes|string|min:7|max:7',
            'icon' => 'sometimes|string|max:50',
            'parent_id' => 'sometimes|nullable|exists:categories,id,user_id,' . auth()->id(),
        ];
    }
}
