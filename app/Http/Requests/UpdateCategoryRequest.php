<?php

namespace App\Http\Requests;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'name' => [
                'sometimes', 
                'string', 
                'min:3',
                Rule::unique('categories', 'name')
                    ->where('user_id', auth()->id())
                    ->ignore($this->route('category')),
            ],
            'type' => ['sometimes', new Enum(CategoryType::class)],
            'color' => 'sometimes|string|min:7|max:7',
            'icon' => 'sometimes|string|max:50',
            'parent_id' => [
                'sometimes',
                'nullable', 
                'exclude_if:parent_id,null',
                'exists:categories,id,user_id,' . auth()->id(),
                function ($attribute, $value, $fail) {
                    $category = $this->route('category');

                    if ($value && $value === $category->id) {
                        $fail('A category cannot be a child and a parent!');
                        return;
                    }

                    if ($value && $category->children()->exists()) {
                        $fail('Cannot move a parent category!');
                        return;
                    }
                },
            ],
        ];
    }
}
