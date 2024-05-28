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
            'cottages.*.addOns' => 'nullable|array',
            'cottages.*.addOns.*.quantity' => 'nullable|integer',
            'cottages.*.addOns.*.item_id' => 'nullable|integer|exists:items,id',

            'others' => 'nullable|array',
            'others.*.id' => 'nullable|integer|exists:others,id',
            'others.*.addOns' => 'nullable|array',
            'others.*.addOns.*.quantity' => 'nullable|integer',
            'others.*.addOns.*.item_id' => 'nullable|integer|exists:items,id',

            'customer' => 'required',
            'isWalkIn' => 'boolean',

            'isMinimumAccepted' => 'required|boolean',
            'isPaymentWithinDay' => 'required|boolean',
            'isConfirmed' => 'required|boolean',
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
