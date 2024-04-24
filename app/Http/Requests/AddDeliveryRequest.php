<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddDeliveryRequest extends FormRequest
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
            'companyName' => 'required',
            'status' => 'required',
            'arrivalDate' => 'nullable|date',
            'items' => 'array|required',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'bill' => 'nullable'
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
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
