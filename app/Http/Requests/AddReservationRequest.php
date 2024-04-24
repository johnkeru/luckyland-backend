<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddReservationRequest extends FormRequest
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
            'checkIn' => 'required|date',
            'checkOut' => 'required|date',
            'total' => 'required|integer',
            'guests' => 'required|integer',

            'totalRoomsPrice' => 'required|integer',
            'totalCottagesPrice' => 'required|integer',
            'days' => 'required|integer',
            'accommodationType' => 'required|string',

            'rooms' => 'nullable|array',
            'rooms.*.id' => 'nullable|integer|exists:rooms,id',
            'rooms.*.addOns' => 'nullable|array',
            'rooms.*.addOns.*.name' => 'nullable|string',
            'rooms.*.addOns.*.quantity' => 'nullable|integer',
            'rooms.*.addOns.*.item_id' => 'nullable|integer|exists:items,id',

            'cottages' => 'nullable|array',
            'cottages.*.id' => 'nullable|integer|exists:cottages,id',
            'cottages.*.isCottageOvernight' => 'nullable|boolean',
            'cottages.*.addOns' => 'nullable|array',
            'cottages.*.addOns.*.quantity' => 'nullable|integer',
            'cottages.*.addOns.*.item_id' => 'nullable|integer|exists:items,id',

            'customer' => 'required',
            'isWalkIn' => 'boolean',

            'isMinimumAccepted' => 'required|boolean',
            'isPaymentWithinDay' => 'required|boolean',
            'isConfirmed' => 'required|boolean',
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
