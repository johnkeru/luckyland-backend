<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateWasteRequest;
use App\Http\Responses\WasteIndexResponse;
use App\Models\EmployeeLogs;
use App\Models\Item;
use App\Models\Waste;
use Illuminate\Support\Facades\Auth;

class WasteController extends Controller
{
    public function index()
    {
        try {
            // SEARCH FILTERS
            // http://localhost:8000/api/inventory?search=anyvalue
            $search = request()->query('search');
            $quantity = request()->query('quantity');
            $name = request()->query('name');
            $category = request()->query('category');

            $wastes = Waste::search($search)
                ->latest()
                ->filterByCategory($category)
                ->orderByItemName($name)
                ->orderByQuantity($quantity)
                ->with(['item', 'item.categories'])
                ->paginate(8);

            return new WasteIndexResponse($wastes);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured.', 'error' => $e->getMessage()], 500);
        }
    }

    public function addWaste(UpdateWasteRequest $request)
    {
        try {
            $id = Auth::id();
            // Validate the request
            $data = $request->validated(); // Assuming this method returns validated data

            $item = Item::findOrFail($data['item_id']);

            // Create a new waste entry
            Waste::create($data);

            // Update related item
            $item->currentQuantity -= $data['quantity']; // Decrease item quantity

            // Determine the item status
            if ($item->currentQuantity != 0 && $item->currentQuantity < $item->reOrderPoint) {
                $item->status = 'Low Stock';
            } elseif ($item->currentQuantity == 0) {
                $item->status = 'Out of Stock';
            }
            $item->save();

            EmployeeLogs::create([
                'action' => 'Recorded waste disposal for item: ' . $item->name,
                'user_id' => $id,
                'type' => 'waste'
            ]);

            return response()->json(['success' => true, 'message' => 'Waste added successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured.', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateWaste(UpdateWasteRequest $request, Waste $waste)
    {
        try {
            $id = Auth::id();

            $validatedData = $request->validated();
            $quantityChange = $validatedData['quantity'] - $waste->quantity; // Calculate the change in waste quantity

            $waste->quantity = $validatedData['quantity'];
            if ($waste->quantity === 0) {
                $waste->delete();
            } else {
                $waste->save();
            }

            $item = $waste->item;

            if ($quantityChange > 0) {
                $item->currentQuantity -= $quantityChange; // Decrease item quantity
            } elseif ($quantityChange < 0) {
                $item->currentQuantity += abs($quantityChange); // Increase item quantity
                if ($item->currentQuantity > $item->maxQuantity) {
                    $item->maxQuantity = $item->currentQuantity;
                    $item->status = 'In Stock';
                }
            }

            if ($item->currentQuantity != 0 && $item->currentQuantity < $item->reOrderPoint) {
                $item->status = 'Low Stock';
            } elseif ($item->currentQuantity == 0) {
                $item->status = 'Out of Stock';
            }

            // Save changes to item
            $item->save();

            EmployeeLogs::create([
                'action' => 'Updated waste disposal for item: ' . $item->name,
                'user_id' => $id,
                'type' => 'waste'
            ]);

            return response()->json(['success' => true, 'message' => 'Successfully updated.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured.', 'error' => $e->getMessage()], 500);
        }
    }
}
