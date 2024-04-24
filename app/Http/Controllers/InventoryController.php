<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Responses\InventoryFindResponse;
use App\Http\Responses\InventoryIndexResponse;
use App\Http\Responses\ReturnedItemsIndexResponse;
use App\Models\Category;
use App\Models\Cottage;
use App\Models\Customer;
use App\Models\EmployeeLogs;
use App\Models\Item;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function index()
    {
        try {
            // SEARCH FILTERS
            // http://localhost:8000/api/items?search=anyvalue
            $search = request()->query('search');

            $status = request()->query('status');
            $currentQuantity = request()->query('currentQuantity');
            $lastCheck = request()->query('lastCheck');
            $trash = request()->query('trash');

            $name = request()->query('name');
            $category = request()->query('category');

            $items = Item::search($search)
                ->withTrashcan($trash)
                ->filterByStatus($status)
                ->filterByCategory($category)
                ->latest('created_at')
                ->orderByItemName($name)
                ->orderByCurrentQuantity($currentQuantity)
                ->orderBy($lastCheck)
                ->with(['categories'])
                ->withCount('customersWhoBorrows')
                ->paginate(7);

            return new InventoryIndexResponse($items);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred.'
            ], 500);
        }
    }

    public function findItem()
    {
        try {
            $search = request()->query('search');
            $items = Item::search($search)
                ->latest('created_at')
                ->with(['categories'])
                ->limit(5)
                ->get();

            return new InventoryFindResponse($items);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred.'
            ], 500);
        }
    }

    public function getCategories()
    {
        return response()->json(['success' => true, 'data' => Category::all()]);
    }

    public function addItem(AddItemRequest $request)
    {
        // we have 3 main model here, item, item and category.
        try {
            $id = Auth::id();

            $itemData = $request->validated(); // it has all 3 model data.

            // these are for adding item only in delivery if new item.
            $itemData['currentQuantity'] ??= 0;
            $itemData['reOrderPoint'] ??= 0;
            $itemData['maxQuantity'] ??= 0;
            $itemData['status'] ??= 'In Stock';

            if ($itemData['currentQuantity'] != 0 && $itemData['currentQuantity'] < $itemData['reOrderPoint']) $itemData['status'] = 'Low Stock';
            if ($itemData['currentQuantity'] == 0) $itemData['status'] = 'Out of Stock';

            // Create a new item
            $itemData['lastCheck'] = now();
            $item = Item::create($itemData);

            $categoryNames = $itemData['categories']; // Assuming $itemData['categories'] contains an array of category names
            $categoryIds = Category::whereIn('name', $categoryNames)
                ->pluck('id')
                ->toArray();

            $item->categories()->sync($categoryIds);

            foreach ($categoryNames as $categoryName) {
                if ($categoryName == 'Room') {
                    $rooms = Room::with('roomType')->get();
                    foreach ($rooms as $room) {
                        // Check if the item is already associated with this room
                        if (!$room->items->contains($item->id)) {
                            // If not, attach the item to the room with the default quantity
                            $room->items()->attach($item->id, ['minQuantity' => $room->roomType->minCapacity, 'maxQuantity' => $room->roomType->maxCapacity]);
                        }
                    }
                }
                if ($categoryName == 'Cottage') {
                    $cottages = Cottage::with('cottageType')->get();
                    foreach ($cottages as $cottage) {
                        // Check if the item is already associated with this cottage
                        if (!$cottage->items->contains($item->id)) {
                            // If not, attach the item to the cottage with the default quantity
                            $cottage->items()->attach($item->id, ['quantity' => $cottage->cottageType->capacity]);
                        }
                    }
                }
            }

            EmployeeLogs::create([
                'action' => 'Added item ' . $item->name . ' to inventory.',
                'user_id' => $id,
                'type' => 'add'
            ]);

            // here item is return instead of item.
            return response()->json([
                'success' => true,
                'message' => 'New item has been added.',
                'data' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred.'
            ], 500);
        }
    }

    public function inlineUpdateItem(Item $item)
    {
        try {
            $id = Auth::id();

            $name = request()->name;
            $image = request()->image;
            $item->update([
                'name' => $name,
                'image' => $image,
            ]);
            EmployeeLogs::create([
                'action' => 'Updated item ' . $item->name . ' in inventory.',
                'user_id' => $id,
                'type' => 'update'
            ]);
            return response()->json(['success' => true, 'message' => 'Successfully updated.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage(), 'message' => 'Update failed.'], 500);
        }
    }

    public function updateItem(Item $item, UpdateItemRequest $request)
    {
        $id = Auth::id();

        try {
            $itemData = $request->validated();
            $currentQuantity = $request->input('currentQuantity');
            $maxQuantity = $request->input('maxQuantity');
            $reOrderPoint = $request->input('reOrderPoint');
            $item = Item::find($request->input('item_id'));

            $categoryNames = $itemData['categories']; // Assuming $itemData['categories'] contains an array of category names
            $categoryIds = Category::whereIn('name', $categoryNames)
                ->pluck('id')
                ->toArray();

            $item->categories()->sync($categoryIds);

            foreach ($categoryNames as $categoryName) {
                if ($categoryName == 'Room') {
                    $rooms = Room::with('roomType')->get();
                    foreach ($rooms as $room) {
                        // Check if the item is already associated with this room
                        if (!$room->items->contains($item->id)) {
                            // If not, attach the item to the room with the default quantity
                            $room->items()->attach($item->id, ['minQuantity' => $room->roomType->minCapacity, 'maxQuantity' => $room->roomType->maxCapacity]);
                        }
                    }
                }
                if ($categoryName == 'Cottage') {
                    $cottages = Cottage::with('cottageType')->get();
                    foreach ($cottages as $cottage) {
                        // Check if the item is already associated with this cottage
                        if (!$cottage->items->contains($item->id)) {
                            // If not, attach the item to the cottage with the default quantity
                            $cottage->items()->attach($item->id, ['quantity' => $cottage->cottageType->capacity]);
                        }
                    }
                }
            }

            // check user error
            if ($currentQuantity < 0)
                return response()->json(['success' => false, 'error' => 'Invalid quantity.', 'message' => 'Invalid quantity.'], 400);
            if ($maxQuantity && $maxQuantity < 0)
                return response()->json(['success' => false, 'error' => 'Invalid max quantity.', 'message' => 'Invalid max quantity.'], 400);
            if ($reOrderPoint && $reOrderPoint < 0)
                return response()->json(['success' => false, 'error' => 'Invalid re-order point.', 'message' => 'Invalid re-order point.'], 400);
            if ($maxQuantity && $currentQuantity > $maxQuantity)
                return response()->json(['success' => false, 'error' => 'Invalid quantity.', 'message' => 'Invalid quantity.'], 400);


            if ($currentQuantity > 0 && $currentQuantity <= $item->reOrderPoint) $itemData['status'] = "Low Stock";
            if ($currentQuantity > $item->reOrderPoint) $itemData['status'] = "In Stock";

            if ($reOrderPoint) {
                if ($currentQuantity <= $reOrderPoint) $itemData['status'] = "Low Stock";
                if ($currentQuantity > $reOrderPoint) $itemData['status'] = "In Stock";
            }
            if ($currentQuantity === 0) $itemData['status'] = "Out of Stock";

            $itemData['lastCheck'] = now();
            $item->update($itemData);

            EmployeeLogs::create([
                'action' => 'Updated item ' . $item->name . ' in inventory.',
                'user_id' => $id,
                'type' => 'update'
            ]);

            return response()->json(['success' => true, 'data' => $item, 'message' => 'Successfully updated.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred.'
            ], 500);
        }
    }
    public function customerBorrow(Item $item, Customer $customer)
    {
        try {
            $id = Auth::id();

            $borrowed_quantity = request('borrowed_quantity');

            // Check for possible user input mistakes.
            if ($borrowed_quantity == 0) {
                return response()->json(['success' => false, 'message' => 'No quantity provided.'], 400);
            }

            if ($item->currentQuantity == 0) {
                return response()->json(['success' => false, 'message' => 'Out of Stock.'], 400);
            }

            if ($borrowed_quantity > $item->currentQuantity) {
                return response()->json(['success' => false, 'message' => 'Exceeds available stock.'], 400);
            }

            // Check if the customer is a first-time borrower for this item item
            $isFirstTime = !$item->customersWhoBorrows()->wherePivot('customer_id', '=', $customer->id)->exists();

            // Calculate the total borrowed quantity considering previous borrowings
            $totalBorrowedQuantity = $isFirstTime ? $borrowed_quantity : $item->customersWhoBorrows()
                ->wherePivot('customer_id', '=', $customer->id)
                ->first()
                ->borrows // pivot
                ->borrowed_quantity + $borrowed_quantity;

            // Sync the borrowing information in the pivot table
            if ($isFirstTime) {
                $item->customersWhoBorrows()->attach([
                    $customer->id => [
                        'status' => 'Borrowed',
                        'borrowed_at' => now(),
                        'borrowed_quantity' => $totalBorrowedQuantity,
                    ]
                ]);
            } else {
                $item->customersWhoBorrows()->updateExistingPivot($customer->id, [
                    'status' => 'Borrowed',
                    'borrowed_at' => now(),
                    'borrowed_quantity' => $totalBorrowedQuantity,
                ]);
            }


            // Decide if item is Low Stock, In Stock, or Out of Stock
            $availableQuantityLeft = $item->currentQuantity - $borrowed_quantity;
            $status = null;

            if ($availableQuantityLeft <= 0) {
                $status = "Out of Stock";
            }

            if ($availableQuantityLeft != 0 && $availableQuantityLeft <= $item->reOrderPoint) {
                $status = "Low Stock";
                // Notification here!
            }

            if ($availableQuantityLeft > $item->reOrderPoint && $availableQuantityLeft <= $item->maxQuantity) {
                $status = "In Stock";
                // Notification here!
            }

            EmployeeLogs::create([
                'action' => 'Let customer ' . $customer->firstName . ' ' . $customer->lastName . ' borrow item: ' . $item->name,
                'user_id' => $id,
                'type' => 'borrow'
            ]);

            $item->update(['status' => $status, 'currentQuantity' => $availableQuantityLeft]);

            return response()->json(['success' => true, 'message' => 'The customer successfully borrowed.']);
        } catch (\Exception $e) {
            // Log or handle the exception as needed
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function customerReturnAllBorrowedItems()
    {
        try {

            $id = Auth::id();

            $customerId = request('customer_id');
            $customer = Customer::find($customerId);

            $borrowedItems = $customer->borrowedItems()->get();

            foreach ($borrowedItems as $borrowedItem) {
                $borrowed_quantity = $borrowedItem->borrows->borrowed_quantity;

                $item = Item::find($borrowedItem->borrows->item_id);
                $item->currentQuantity += $borrowed_quantity;

                if ($item->currentQuantity > $item->maxQuantity) {
                    $item->maxQuantity = $item->currentQuantity;
                    $item->status = 'In Stock';
                } else if ($item->currentQuantity > $item->reOrderPoint) {
                    $item->status = 'In Stock';
                } else {
                    $item->status = 'Low Stock';
                }

                // Increase the current quantity based on the borrowed quantity
                $item->save();
                $item->customersWhoBorrows()->updateExistingPivot($customer->id, [
                    'status' => 'Returned',
                    'borrowed_quantity' => 0,
                    'return_quantity' => $borrowed_quantity,
                    'returned_at' => now(),
                ]);

                EmployeeLogs::create([
                    'action' => 'Returned borrowed item: ' . $item->name . ' by customer ' . $customer->firstName . ' ' . $customer->lastName,
                    'user_id' => $id,
                    'type' => 'return'
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Successfully returned.']);
        } catch (\Exception $e) {
            // Log or handle the exception as needed
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function customerPartiallyReturnItems()
    {
        try {
            $id = Auth::id();

            $customerId = request('customer_id');
            $customer = Customer::find($customerId);

            // This data is an array of [item_id, return_quantity, paid]
            $returnItems = request('returnItems');

            foreach ($returnItems as $returnItem) {
                $item = Item::find($returnItem['item_id']);
                // Decrease the customer borrowed quantity
                $borrows = $item->customersWhoBorrows()
                    ->wherePivot('customer_id', '=', $customer->id)
                    ->first()
                    ->borrows;

                if ($borrows->borrowed_quantity < $returnItem['return_quantity']) {
                    return ['success' => false, 'message' => 'Invalid quantity.'];
                }

                $totalBorrowedQuantity = $borrows->borrowed_quantity - $returnItem['return_quantity'];

                if ($totalBorrowedQuantity == 0) {
                    $item->customersWhoBorrows()->updateExistingPivot($customer->id, [
                        'status' => 'Returned',
                        'borrowed_quantity' => 0,
                        'return_quantity' => $returnItem['return_quantity'],
                        'paid' => $returnItem['paid'],
                        'returned_at' => now(),
                    ]);
                } else {
                    $item->customersWhoBorrows()->updateExistingPivot(
                        $customer->id,
                        [
                            'status' => 'Paid',
                            'borrowed_quantity' => $totalBorrowedQuantity,
                            'return_quantity' => $returnItem['return_quantity'],
                            'paid' => $returnItem['paid'],
                            'returned_at' => now(),
                        ]
                    );
                }

                // Add the returned borrowed quantity to the item.
                $availableQuantityNow = $item->currentQuantity + $returnItem['return_quantity'];

                // After modifying the item, check the possible status.
                $status = null;

                if ($availableQuantityNow > $item->reOrderPoint && $availableQuantityNow <= $item->maxQuantity) {
                    $status = "In Stock";
                }

                if ($availableQuantityNow != 0 && $availableQuantityNow <= $item->reOrderPoint) {
                    $status = "Low Stock";
                    // Notification here!
                }

                if ($availableQuantityNow == 0) {
                    $status = "Out of Stock";
                    // Notification here!
                }

                // save the modified columns
                $item->update([
                    'status' => $status,
                    'currentQuantity' => $availableQuantityNow
                ]);

                EmployeeLogs::create([
                    'action' => 'Returned borrowed item: ' . $item->name . ' by customer ' . $customer->firstName . ' ' . $customer->lastName,
                    'user_id' => $id,
                    'type' => 'return'
                ]);
            }

            return ['success' => true, 'message' => 'Successfully returned.'];
        } catch (\Exception $e) {
            // Log or handle the exception as needed
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function returnedItems($id)
    {
        try {
            $item = Item::findOrFail($id);
            $customers = $item->customersWhoReturn()->paginate(10);
            return new ReturnedItemsIndexResponse($customers);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    public function softDeleteOrRestoreItem($id)
    {
        $userId = Auth::id();

        try {
            // $inv = Item::find($id);
            // $inv->delete();
            $item = Item::withTrashed()->find($id);

            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item not found.'], 404);
            }

            if ($item->trashed()) {
                $item->restore();

                EmployeeLogs::create([
                    'action' => 'Restored item ' . $item->name . ' in inventory',
                    'user_id' => $userId,
                    'type' => 'restore'
                ]);

                return response()->json(['success' => true, 'message' => 'Successfully restored.']);
            }

            EmployeeLogs::create([
                'action' => 'Deleted item ' . $item->name . ' from inventory',
                'user_id' => $userId,
                'type' => 'delete'
            ]);

            $item->delete();

            return response()->json(['success' => true, 'message' => 'Successfully deleted.']);
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => $e->getMessage() . ' Please try again or contact support.'], 500);
        }
    }
























    // validation helper function
    public function validateData(array $rules)
    {
        // $rules = [
        //     'itemCode' => 'required|unique:items',
        //     'name' => 'required',
        //     'category' => 'required',
        //     'quantity' => 'required|integer|min:1',
        //     'maxQuantity' => 'required|integer|min:1',
        //     'reOrderPoint' => 'required|integer|min:1',
        //     'addedQuantity' => 'required|integer|min:1',
        // ];
        // $data = $this->validateData($rules);
        $data['success'] = true;
        $validator = Validator::make(request()->all(), $rules);
        if ($validator->fails()) {
            $data['success'] = false;
            $data['errors'] = [];
            foreach ($validator->errors()->messages() as $field => $messages) {
                $data['errors'][] = [
                    'field' => $field,
                    'msg' => $messages[0]
                ];
            }
            return $data;
        }
    }
}






        // $item = Item::find(1);
        // $customersReturned = $item->customersWhoBorrowsReturned()->get();
        // return ['returned' => $customersReturned];