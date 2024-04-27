<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUnavailableRequest;
use App\Http\Responses\UnavailableIndexResponse;
use App\Models\EmployeeLogs;
use App\Models\Item;
use App\Models\Unavailable;
use App\Models\Waste;
use Illuminate\Support\Facades\Auth;

class UnavailableController extends Controller
{
    public function index()
    {
        try {
            $search = request()->query('search');
            $quantity = request()->query('quantity');
            $name = request()->query('name');
            $category = request()->query('category');

            $unavailables = Unavailable::search($search)
                ->latest()
                ->where('quantity', '>', 0)
                ->filterByCategory($category)
                ->orderByItemName($name)
                ->orderByQuantity($quantity)
                ->with(['item', 'item.categories'])
                ->paginate(7);

            return new UnavailableIndexResponse($unavailables);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured', 'error' => $e->getMessage()], 500);
        }
    }

    public function addUnavailable(UpdateUnavailableRequest $request)
    {
        try {
            $id = Auth::id();
            // Validate the request
            $data = $request->validated(); // Assuming this method returns validated data

            // Find the item
            $item = Item::findOrFail($data['item_id']);

            // Create a new unavailable entry
            Unavailable::create($data);

            // Update related item
            $item->currentQuantity -= $data['quantity']; // Decrease item quantity
            $item->save();

            // Determine the item status
            if ($item->currentQuantity != 0 && $item->currentQuantity < $item->reOrderPoint) {
                $item->status = 'Low Stock';
            } elseif ($item->currentQuantity == 0) {
                $item->status = 'Out of Stock';
            }
            $item->save();

            EmployeeLogs::create([
                'action' => 'Recorded item as unavailable: ' . $item->name,
                'user_id' => $id,
                'type' => 'unavailable'
            ]);


            return response()->json(['success' => true, 'message' => 'Unavailable added successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured', 'error' => $e->getMessage()], 500);
        }
    }

    public function inlineUpdate(Unavailable $unavailable)
    {
        try {
            $id = Auth::id();
            $unavailable->update(['reason' => request()->reason]);
            EmployeeLogs::create([
                'action' => 'Updated item unavailable: ' . $unavailable->item->name,
                'user_id' => $id,
                'type' => 'update'
            ]);

            return response()->json(['success' => true, 'message' => 'Successfully updated.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured', 'error' => $e->getMessage()], 500);
        }
    }

    public function editUnavailable(UpdateUnavailableRequest $request, Unavailable $unavailable)
    {
        try {
            $id = Auth::id();
            // Validate the request
            $validatedData = $request->validated(); // Assuming this method returns validated data

            // Calculate the change in quantity
            $quantityChange = $validatedData['quantity'] - $unavailable->quantity; // Calculate the change in unavailable quantity

            // Update the unavailable quantity
            $unavailable->quantity = $validatedData['quantity'];
            $unavailable->reason = $validatedData['reason'];
            $unavailable->save();

            // Retrieve related item
            $item = $unavailable->item;

            // Check if the unavailable quantity increased
            if ($quantityChange > 0) {
                // Decrease related item
                $item->currentQuantity -= $quantityChange; // Decrease item quantity
            } elseif ($quantityChange < 0) {
                // Increase related item
                $item->currentQuantity += abs($quantityChange); // Increase item quantity
                if ($item->currentQuantity > $item->maxQuantity) {
                    $item->maxQuantity = $item->currentQuantity;
                    $item->status = 'In Stock';
                }
            }

            // Determine the item status
            if (
                $item->currentQuantity != 0 && $item->currentQuantity < $item->reOrderPoint
            ) {
                $item->status = 'Low Stock';
            } elseif ($item->currentQuantity == 0) {
                $item->status = 'Out of Stock';
            }

            // Save changes to item
            $item->save();

            EmployeeLogs::create([
                'action' => 'Updated item unavailable: ' . $item->name,
                'user_id' => $id,
                'type' => 'update'
            ]);


            return response()->json(['success' => true, 'message' => 'Successfully updated.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured', 'error' => $e->getMessage()], 500);
        }
    }

    // should be modal for unavailable to waste (item_id, quantity)
    public function unavailableToWaste($id)
    {
        try {
            $id = Auth::id();

            $unavailable = Unavailable::with('item')->find($id);

            if (!$unavailable) {
                return response()->json(['success' => false, 'message' =>  'Unavailable not found.'], 400);
            }

            $requestQuantity = request()->quantity;
            $currentQuantity = $unavailable->quantity;
            $totalQuantity = $currentQuantity - $requestQuantity;

            Waste::create(['quantity' => $requestQuantity, 'item_id' => $unavailable->item->id]);

            if ($totalQuantity === 0) {
                $unavailable->delete();
            } else {
                $unavailable->update(['quantity' => $totalQuantity]);
            }

            EmployeeLogs::create([
                'action' => 'Moved item: ' . $unavailable->item->name . ' to waste',
                'user_id' => $id,
                'type' => 'move'
            ]);

            return response()->json(['success' => true, 'message' =>  'Successfully updated.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured', 'error' => $e->getMessage()], 500);
        }
    }

    public function unavailableToInventory($id)
    {
        try {
            $id = Auth::id();

            $unavailable = Unavailable::with('item')->find($id);

            if (!$unavailable) {
                return response()->json(['success' => false, 'message' => 'Not found.'], 400);
            }

            $requestQuantity = request()->quantity;
            $currentQuantity = $unavailable->quantity;
            $totalQuantity = $currentQuantity - $requestQuantity;

            $item = $unavailable->item;
            $item->currentQuantity += $requestQuantity;
            if ($item->currentQuantity > $item->maxQuantity) {
                $item->maxQuantity = $item->currentQuantity;
            }
            if ($item->currentQuantity > $item->reOrderPoint)
                $item->status = 'In Stock';
            else {
                $item->status = 'Low Stock';
            }
            $item->save();

            EmployeeLogs::create([
                'action' => 'Moved item: ' . $unavailable->item->name . ' to inventory',
                'user_id' => $id,
                'type' => 'move'
            ]);

            if ($totalQuantity === 0) {
                $unavailable->delete();
            } else {
                $unavailable->update(['quantity' => $totalQuantity]);
            }
            return response()->json(['success' => true, 'message' =>  'Successfully updated.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured', 'error' => $e->getMessage()], 500);
        }
    }
}
