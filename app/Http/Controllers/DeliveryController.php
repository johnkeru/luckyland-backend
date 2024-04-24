<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddDeliveryRequest;
use App\Http\Responses\DeliveryIndexResponse;
use App\Models\Delivery;
use App\Models\EmployeeLogs;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    public function index()
    {
        $search = request()->query('search');
        $companyName = request()->query('companyName');
        $status = request()->query('status');

        $deliveries = Delivery::search($search)
            ->filterByStatus($status)
            ->orderByCompanyName($companyName)
            ->with(['items', 'manage'])
            ->latest('created_at')->paginate(8);
        return new DeliveryIndexResponse($deliveries);
    }

    public function addDelivery(AddDeliveryRequest $request)
    {
        try {
            $id = Auth::id();
            $deliveryData = $request->validated(); // it has all 3 model data.
            $arrivalDate = $deliveryData['status'] === 'Arrived' ? $deliveryData['arrivalDate'] : null;
            $delivery = Delivery::create(
                [
                    'companyName' => $deliveryData['companyName'],
                    'arrivalDate' => $arrivalDate,
                    'status' => $deliveryData['status'],
                    'bill' => intval($deliveryData['bill']),
                    'user_id' => $id,
                ]
            );
            foreach ($deliveryData['items'] as $item) {
                // Attach the Item to the delivery
                $delivery->items()->attach($item['item_id'], ['quantity' => $item['quantity']]);

                // Find the Item and load its item
                if ($arrivalDate) {
                    $localItem = Item::find($item['item_id']);
                    // Update the current quantity in item
                    $localItem->currentQuantity += $item['quantity'];
                    if ($localItem->currentQuantity > $localItem->reOrderPoint) {
                        $localItem->status = 'In Stock';
                    }
                    // Check if the current quantity exceeds the maximum quantity
                    if ($localItem->currentQuantity > $localItem->maxQuantity) {
                        $localItem->maxQuantity = $localItem->currentQuantity;
                    }
                    // Save the changes to the item
                    $localItem->save();
                }
            }

            EmployeeLogs::create([
                'action' => 'Managed the delivery item from ' . $delivery->companyName,
                'user_id' => $id,
                'type' => 'delivery'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully added.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to add.'
            ]);
        }
    }
}
