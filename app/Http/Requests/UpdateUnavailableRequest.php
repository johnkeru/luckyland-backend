<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUnavailableRequest extends FormRequest
{

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
            'item_id' => 'nullable',
            'quantity' => 'nullable|numeric|min:0',
            'reason' => 'string|required'
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
