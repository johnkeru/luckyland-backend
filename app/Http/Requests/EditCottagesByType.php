<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EditCottagesByType extends FormRequest
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
            'origType' => 'nullable|string',
            'type' => 'required|string',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',

            'attributes' => 'nullable|array',
            'attributes.*.name' => 'sometimes|required|string',
            // 'rate' => 'nullable|numeric|min:0',
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
