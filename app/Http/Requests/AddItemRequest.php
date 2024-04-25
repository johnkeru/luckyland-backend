<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddItemRequest extends FormRequest
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
    public function rules()
    {
        return [
            'currentQuantity' => 'nullable|numeric',
            'maxQuantity' => 'nullable|numeric',
            'reOrderPoint' => 'nullable|numeric',

            'name' => 'required|min:2',
            'price' => 'numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'isBorrowable' => 'boolean',

            'categories' => 'required|array',
            // 'categories.*.name' => 'required|string',
            // 'categories.*.quantity' => 'required|numeric',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = [];

        foreach ($validator->errors()->messages() as $field => $messages) {
            foreach ($messages as $message) {
                // Add each error message to the errors array
                $errors[] = [
                    'field' => $field,
                    'msg' => $message
                ];
            }
        }

        // Throw the HTTP response exception with the desired JSON response
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Failed to add a new item.',
                'errors' => $errors
            ], 422)
        );
    }
}
