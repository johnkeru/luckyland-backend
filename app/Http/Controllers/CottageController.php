<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCottageRequest;
use App\Http\Requests\EditCottagesByType;
use App\Http\Requests\EditRoomsByType;
use App\Http\Responses\AllCottageResponse;
use App\Http\Responses\CottageTypesResponse;
use App\Http\Responses\ReservationAvailableCottagesResponse;
use App\Models\Cottage;
use App\Models\CottageAttribute;
use App\Models\CottageImage;
use App\Models\CottageType;
use App\Models\EmployeeLogs;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class CottageController extends Controller
{
    // for cottage management
    public function getAllCottages()
    {
        try {
            $cottages = Cottage::with(['images', 'cottageType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Cottage');
                });
            }])
                ->latest() // Retrieve the latest cottages based on their creation timestamp
                ->get();

            return new AllCottageResponse($cottages);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred.'
            ], 500);
        }
    }

    // for landing page;
    public function getLandingPageCottages()
    {
        try {
            $availableCottages = Cottage::with(['images', 'cottageType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Cottage');
                });
            }])
                ->get();

            // Retrieve additional data
            $addOns = Item::whereHas('categories', function ($query) {
                $query->where('name', 'Cottage Add Ons');
            })->get();

            return new ReservationAvailableCottagesResponse($availableCottages, $addOns);
        } catch (\Exception $e) {
            // Return error response if an exception occurs
            return response()->json([
                'message' => 'Failed to get available rooms',
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }


    public function addCottage(AddCottageRequest $request)
    {
        try {
            $id = Auth::id();
            $data = $request->validated();
            $cottageType = CottageType::where('type', $data['origType'])->first();

            if (!$cottageType) {
                $newAttributeIds = [];
                foreach ($data['attributes'] as $attr) {
                    $newAttribute = CottageAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                    $newAttributeIds[] = $newAttribute->id;
                }
                $cottageType = CottageType::create($data);
                $cottageType->attributes()->attach($newAttributeIds);
            } else {
                if (isset($data['attributes'])) {
                    $cottageType->attributes()->detach();

                    CottageAttribute::where('type', $data['origType'])->delete();
                    $newAttributeIds = [];
                    // Delete existing attributes for the cottage's original type
                    foreach ($data['attributes'] as $attr) {
                        $newAttribute = CottageAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                        $newAttributeIds[] = $newAttribute->id;
                    }
                }
                $cottageType->update([
                    'type' => $data['type'],
                    'price' => $data['price'],
                    'capacity' => $data['capacity'],
                    'description' => $data['description'],
                ]);

                $cottageType->attributes()->attach($newAttributeIds);

                if ($cottageType->capacity !== $data['capacity']) {
                    $cottages = $cottageType->cottages;
                    foreach ($cottages as $cottage) {
                        $itemIds = Item::whereHas('categories', function ($query) {
                            $query->where('name', 'Cottage');
                        })->pluck('id')->toArray();

                        $itemCottages = [];
                        foreach ($itemIds as $itemId) {
                            $itemCottages[$itemId] = [
                                'quantity' => $data['capacity'],
                            ];
                        }
                        $cottage->items()->sync($itemCottages);
                    }
                }
            }

            $data['cottage_type_id'] = $cottageType->id;
            $cottage = Cottage::create($data);

            $itemIds = Item::whereHas('categories', function ($query) {
                $query->where('name', 'Cottage');
            })->pluck('id')->toArray();

            $itemCottages = [];
            foreach ($itemIds as $itemId) {
                $itemCottages[$itemId] = [
                    'quantity' => $cottageType->capacity,
                ];
            }
            $cottage->items()->attach($itemCottages);

            foreach ($data['images'] as $img) {
                $image = [
                    'url' => $img['url'],
                    'cottage_id' => $cottage->id
                ];
                CottageImage::create($image);
            }

            EmployeeLogs::create([
                'action' => 'Added a new cottage: ' . $data['name'],
                'user_id' => $id,
                'type' => 'add'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully added the new cottage.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to create a new cottage.'
            ]);
        }
    }

    public function updateCottage(AddCottageRequest $request, Cottage $cottage)
    {
        try {
            $id = Auth::id();
            $data = $request->validated();
            $cottageType = CottageType::where('type', $data['origType'])->first();

            if (!$cottageType) {
                $newAttributeIds = [];
                foreach ($data['attributes'] as $attr) {
                    $newAttribute = CottageAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                    $newAttributeIds[] = $newAttribute->id;
                }
                $cottageType = CottageType::create($data);
                $cottageType->attributes()->attach($newAttributeIds);
            } else {
                if (isset($data['attributes'])) {
                    $cottageType->attributes()->detach();

                    CottageAttribute::where('type', $data['origType'])->delete();
                    $newAttributeIds = [];
                    // Delete existing attributes for the cottage's original type
                    foreach ($data['attributes'] as $attr) {
                        $newAttribute = CottageAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                        $newAttributeIds[] = $newAttribute->id;
                    }
                }
                $cottageType->update([
                    'type' => $data['type'],
                    'price' => $data['price'],
                    'capacity' => $data['capacity'],
                    'description' => $data['description'],
                ]);

                $cottageType->attributes()->attach($newAttributeIds);

                if ($cottageType->capacity !== $data['capacity']) {
                    $cottages = $cottageType->cottages;
                    foreach ($cottages as $cottage) {
                        $itemIds = Item::whereHas('categories', function ($query) {
                            $query->where('name', 'Cottage');
                        })->pluck('id')->toArray();

                        $itemCottages = [];
                        foreach ($itemIds as $itemId) {
                            $itemCottages[$itemId] = [
                                'quantity' => $data['capacity'],
                            ];
                        }
                        $cottage->items()->sync($itemCottages);
                    }
                }
            }

            $data['cottage_type_id'] = $cottageType->id;
            $cottage->update($data);

            $cottage->images()->delete(); // Delete existing images
            $cottage->images()->createMany($data['images']); // Create new images

            EmployeeLogs::create([
                'action' => 'Updated cottage: ' . $cottage->name,
                'user_id' => $id,
                'type' => 'update'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated cottage ' . $cottage->name . '.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to update cottage ' . $cottage->name . '.'
            ]);
        }
    }

    public function getAllCottageTypes()
    {
        try {
            $cottageType = CottageType::withCount('cottages')->get();
            return new CottageTypesResponse($cottageType);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred.'
            ];
        }
    }

    public function updateCottagesByType(EditRoomsByType $request,)
    {
        try {
            $id = Auth::id();
            $data = $request->validated();
            $cottageType = CottageType::where('type', $data['origType'])->first();

            if (!$cottageType) {
                $newAttributeIds = [];
                foreach ($data['attributes'] as $attr) {
                    $newAttribute = CottageAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                    $newAttributeIds[] = $newAttribute->id;
                }
                $cottageType = CottageType::create($data);
                $cottageType->attributes()->attach($newAttributeIds);
            } else {
                if (isset($data['attributes'])) {
                    $cottageType->attributes()->detach();

                    CottageAttribute::where('type', $data['origType'])->delete();
                    $newAttributeIds = [];
                    // Delete existing attributes for the cottage's original type
                    foreach ($data['attributes'] as $attr) {
                        $newAttribute = CottageAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                        $newAttributeIds[] = $newAttribute->id;
                    }
                }
                $cottageType->update([
                    'type' => $data['type'],
                    'price' => $data['price'],
                    'capacity' => $data['capacity'],
                    'description' => $data['description'],
                ]);

                $cottageType->attributes()->attach($newAttributeIds);

                if ($cottageType->capacity !== $data['capacity']) {
                    $cottages = $cottageType->cottages;
                    foreach ($cottages as $cottage) {
                        $itemIds = Item::whereHas('categories', function ($query) {
                            $query->where('name', 'Cottage');
                        })->pluck('id')->toArray();

                        $itemCottages = [];
                        foreach ($itemIds as $itemId) {
                            $itemCottages[$itemId] = [
                                'quantity' => $data['capacity'],
                            ];
                        }
                        $cottage->items()->sync($itemCottages);
                    }
                }
            }

            EmployeeLogs::create([
                'action' => 'Updated cottage type: ' . $cottageType->type,
                'user_id' => $id,
                'type' => 'update'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated cottage type: ' . $cottageType->type . '.'
            ]);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred.'
            ];
        }
    }
}
