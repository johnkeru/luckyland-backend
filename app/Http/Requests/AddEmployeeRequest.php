<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddEmployeeRequest extends FormRequest
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
            'firstName' => 'required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'lastName' => 'required|string|max:255',
            'image' => 'nullable|string', // Assuming it's a string containing the image path or URL
            'phoneNumber' => 'nullable|string|max:20', // Adjust max length as needed
            'graduated_at' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'password' => 'required|string|min:5', // Adjust minimum length as needed

            // Social Media Links
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',

            // for Addresses table's fields
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'zip_code' => 'nullable|string|max:20', // Adjust max length as needed

            // for Roles table's fields
            'roles' => 'required|array',
            // 'roles.*' => 'exists:roles,id', // Check if each role ID exists in the 'roles' table
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
