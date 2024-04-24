<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateItemRequest extends FormRequest
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

            'name' => 'min:1',
            'price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'isBorrowable' => 'boolean',

            'categories' => 'nullable|array', // array of ids
        ];
    }


    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $data['success'] = false;
        $data['message'] = "Failed to add a new item.";
        $data['errors'] = [];

        foreach ($validator->errors()->messages() as $field => $messages) {
            $data['errors'][] = [
                'field' => $field,
                'msg' => $messages[0]
            ];
        }
        throw new HttpResponseException(
            response()->json($data, 422)
        );
    }
}
