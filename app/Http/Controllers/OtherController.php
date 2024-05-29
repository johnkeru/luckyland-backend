<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCottageRequest;
use App\Http\Requests\EditRoomsByType;
use App\Http\Responses\AllCottageResponse;
use App\Http\Responses\CottageTypesResponse;
use App\Http\Responses\ReservationAvailableCottagesResponse;
use App\Models\EmployeeLogs;
use App\Models\Item;
use App\Models\Other;
use App\Models\OtherAttribute;
use App\Models\OtherImage;
use App\Models\OtherType;
use Illuminate\Support\Facades\Auth;

class OtherController extends Controller
{
    // for others management
    public function getAllOthers()
    {
        try {
            $others = Other::with(['images', 'otherType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Other');
                });
            }])
                ->latest() // Retrieve the latest others based on their creation timestamp
                ->get();

            return new AllCottageResponse($others, true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred.'
            ], 500);
        }
    }

    // for landing page;
    public function getLandingPageOthers()
    {
        try {
            $availableOthers = Other::with(['images', 'otherType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Other');
                });
            }])
                ->get();

            // Retrieve additional data
            $addOns = Item::whereHas('categories', function ($query) {
                $query->where('name', 'Other Add Ons');
            })->get();

            return new ReservationAvailableCottagesResponse($availableOthers, $addOns, true);
        } catch (\Exception $e) {
            // Return error response if an exception occurs
            return response()->json([
                'message' => 'Failed to get available others',
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    public function addOther(AddCottageRequest $request)
    {
        try {
            $id = Auth::id();
            $data = $request->validated();
            $otherType = OtherType::where('type', $data['origType'])->first();

            if (!$otherType) {
                $newAttributeIds = [];
                foreach ($data['attributes'] as $attr) {
                    $newAttribute = OtherAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                    $newAttributeIds[] = $newAttribute->id;
                }
                $otherType = OtherType::create($data);
                $otherType->attributes()->attach($newAttributeIds);
            } else {
                if (isset($data['attributes'])) {
                    $otherType->attributes()->detach();

                    OtherAttribute::where('type', $data['origType'])->delete();
                    $newAttributeIds = [];
                    // Delete existing attributes for the other's original type
                    foreach ($data['attributes'] as $attr) {
                        $newAttribute = OtherAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                        $newAttributeIds[] = $newAttribute->id;
                    }
                }
                $otherType->update([
                    'type' => $data['type'],
                    'price' => $data['price'],
                    'capacity' => $data['capacity'],
                    'description' => $data['description'],
                ]);

                $otherType->attributes()->attach($newAttributeIds);

                if ($otherType->capacity !== $data['capacity']) {
                    $others = $otherType->others;
                    foreach ($others as $other) {
                        $itemIds = Item::whereHas('categories', function ($query) {
                            $query->where('name', 'Other');
                        })->pluck('id')->toArray();

                        $itemOthers = [];
                        foreach ($itemIds as $itemId) {
                            $itemOthers[$itemId] = [
                                'quantity' => $data['capacity'],
                            ];
                        }
                        $other->items()->sync($itemOthers);
                    }
                }
            }

            $data['other_type_id'] = $otherType->id;
            $other = Other::create($data);

            $itemIds = Item::whereHas('categories', function ($query) {
                $query->where('name', 'Other');
            })->pluck('id')->toArray();

            $itemOthers = [];
            foreach ($itemIds as $itemId) {
                $itemOthers[$itemId] = [
                    'quantity' => $otherType->capacity,
                ];
            }
            $other->items()->attach($itemOthers);

            foreach ($data['images'] as $img) {
                $image = [
                    'url' => $img['url'],
                    'other_id' => $other->id
                ];
                OtherImage::create($image);
            }

            EmployeeLogs::create([
                'action' => 'Added a new other: ' . $data['name'],
                'user_id' => $id,
                'type' => 'add'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully added the new other.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to create a new other.'
            ]);
        }
    }

    public function updateOther(AddCottageRequest $request, Other $other)
    {
        try {
            $id = Auth::id();
            $data = $request->validated();
            $otherType = OtherType::where('type', $data['origType'])->first();

            if (!$otherType) {
                $newAttributeIds = [];
                foreach ($data['attributes'] as $attr) {
                    $newAttribute = OtherAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                    $newAttributeIds[] = $newAttribute->id;
                }
                $otherType = OtherType::create($data);
                $otherType->attributes()->attach($newAttributeIds);
            } else {
                if (isset($data['attributes'])) {
                    $otherType->attributes()->detach();

                    OtherAttribute::where('type', $data['origType'])->delete();
                    $newAttributeIds = [];
                    // Delete existing attributes for the other's original type
                    foreach ($data['attributes'] as $attr) {
                        $newAttribute = OtherAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                        $newAttributeIds[] = $newAttribute->id;
                    }
                }
                $otherType->update([
                    'type' => $data['type'],
                    'price' => $data['price'],
                    'capacity' => $data['capacity'],
                    'description' => $data['description'],
                ]);

                $otherType->attributes()->attach($newAttributeIds);

                if ($otherType->capacity !== $data['capacity']) {
                    $others = $otherType->others;
                    foreach ($others as $other) {
                        $itemIds = Item::whereHas('categories', function ($query) {
                            $query->where('name', 'Other');
                        })->pluck('id')->toArray();

                        $itemOthers = [];
                        foreach ($itemIds as $itemId) {
                            $itemOthers[$itemId] = [
                                'quantity' => $data['capacity'],
                            ];
                        }
                        $other->items()->sync($itemOthers);
                    }
                }
            }

            $data['other_type_id'] = $otherType->id;
            $other->update($data);

            $other->images()->delete(); // Delete existing images
            $other->images()->createMany($data['images']); // Create new images

            EmployeeLogs::create([
                'action' => 'Updated other: ' . $other->name,
                'user_id' => $id,
                'type' => 'update'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated other ' . $other->name . '.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to update other ' . $other->name . '.'
            ]);
        }
    }

    public function getAllOtherTypes()
    {
        try {
            $otherType = OtherType::withCount('others')->get();
            return new CottageTypesResponse($otherType);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred.'
            ];
        }
    }

    public function updateOthersByType(EditRoomsByType $request,)
    {
        try {
            $id = Auth::id();
            $data = $request->validated();
            $otherType = OtherType::where('type', $data['origType'])->first();
            $newAttributeIds = [];

            if (!$otherType) {
                foreach ($data['attributes'] as $attr) {
                    $newAttribute = OtherAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                    $newAttributeIds[] = $newAttribute->id;
                }
                $otherType = OtherType::create($data);
                $otherType->attributes()->attach($newAttributeIds);
            } else {
                if (isset($data['attributes'])) {
                    $otherType->attributes()->detach();

                    OtherAttribute::where('type', $data['origType'])->delete();
                    // Delete existing attributes for the other's original type
                    foreach ($data['attributes'] as $attr) {
                        $newAttribute = OtherAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                        $newAttributeIds[] = $newAttribute->id;
                    }
                }
                $otherType->update([
                    'type' => $data['type'],
                    'price' => $data['price'],
                    'capacity' => $data['capacity'],
                    'description' => $data['description'],
                ]);

                $otherType->attributes()->attach($newAttributeIds);

                if ($otherType->capacity !== $data['capacity']) {
                    $others = $otherType->others;
                    foreach ($others as $other) {
                        $itemIds = Item::whereHas('categories', function ($query) {
                            $query->where('name', 'Other');
                        })->pluck('id')->toArray();

                        $itemOthers = [];
                        foreach ($itemIds as $itemId) {
                            $itemOthers[$itemId] = [
                                'quantity' => $data['capacity'],
                            ];
                        }
                        $other->items()->sync($itemOthers);
                    }
                }
            }

            EmployeeLogs::create([
                'action' => 'Updated other type: ' . $otherType->type,
                'user_id' => $id,
                'type' => 'update'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated other type: ' . $otherType->type . '.'
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
