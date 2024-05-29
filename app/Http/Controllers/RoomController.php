<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddRoomRequest;
use App\Http\Requests\EditRoomsByType;
use App\Http\Responses\AllRoomResponse;
use App\Http\Responses\ReservationAvailableRoomsResponse;
use App\Http\Responses\RoomTypesResponse;
use App\Models\CottageType;
use App\Models\EmployeeLogs;
use App\Models\Item;
use App\Models\OtherType;
use App\Models\Room;
use App\Models\RoomAttribute;
use App\Models\RoomImage;
use App\Models\RoomType;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    // for room management
    public function getAllRooms()
    {
        try {
            $rooms = Room::with(['images', 'roomType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Room');
                });
            }])
                ->latest() // Retrieve the latest rooms based on their creation timestamp
                ->get();

            return new AllRoomResponse($rooms);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function landingAccommodations()
    {
        try {

            $roomTypes = RoomType::has('rooms')->with(['rooms.images', 'attributes'])->get();
            $roomTypes->each(function ($roomType) {
                $roomType->setRelation('rooms', $roomType->rooms->take(1));
            });


            $cottageTypes = CottageType::has('cottages')->with(['cottages.images', 'attributes'])->get();
            $cottageTypes->each(function ($cottageType) {
                $cottageType->setRelation('cottages', $cottageType->cottages->take(1));
            });

            $otherTypes = OtherType::has('others')->with(['others.images', 'attributes'])->get();
            $otherTypes->each(function ($cottageType) {
                $cottageType->setRelation('others', $cottageType->others->take(1));
            });

            return response()->json(['data' => ['rooms' => $roomTypes, 'cottages' => $cottageTypes, 'others' => $otherTypes]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // for landing page
    public function getLandingPageRooms()
    {
        try {
            // Calculate available rooms
            $availableRooms = Room::with(['images', 'roomType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Room');
                });
            }])
                ->where('active', true)
                ->get();


            $addOns = Item::whereHas('categories', function ($query) {
                $query->where('name', 'Room Add Ons');
            })->get();

            return new ReservationAvailableRoomsResponse($availableRooms, $addOns);
        } catch (\Exception $e) {
            // Return error response if an exception occurs
            return response()->json(['message' => 'Failed to get available rooms', 'error' => $e->getMessage(), 'success' => false], 500);
        }
    }




    public function addRoom(AddRoomRequest $request)
    {
        try {
            $id = Auth::id();
            $data = $request->validated();
            $roomType = RoomType::where('type', $data['origType'])->first(); // orig type because the type could change when editing.
            if (!$roomType) {
                $data = $request->validated();
                $data['minCapacity'] = $data['capacity'];
                $data['maxCapacity'] = $data['capacity'] + 2;

                $newAttributeIds = [];
                foreach ($data['attributes'] as $attr) {
                    $newAttribute = RoomAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                    $newAttributeIds[] = $newAttribute->id;
                }

                $roomType = RoomType::create($data);
                $roomType->attributes()->attach($newAttributeIds);
            } else { // updated new type
                if (isset($data['attributes'])) {
                    $roomType->attributes()->detach();

                    RoomAttribute::where('type', $data['origType'])->delete();
                    $newAttributeIds = [];
                    // Delete existing attributes for the room's original type
                    foreach ($data['attributes'] as $attr) {
                        $newAttribute = RoomAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                        $newAttributeIds[] = $newAttribute->id;
                    }
                }

                $roomType->update([
                    'type' => $data['type'],
                    'price' => $data['price'],
                    'minCapacity' => $data['capacity'],
                    'maxCapacity' => $data['capacity'] + 2,
                    'description' => $data['description'],
                ]);

                $roomType->attributes()->attach($newAttributeIds);

                if ($roomType->minCapacity !== $data['capacity']) {
                    $rooms = $roomType->rooms;
                    foreach ($rooms as $room) {
                        $itemIds = Item::whereHas('categories', function ($query) {
                            $query->where('name', 'Room');
                        })->pluck('id')->toArray();

                        $itemRooms = [];
                        foreach ($itemIds as $itemId) {
                            $itemRooms[$itemId] = [
                                'minQuantity' => $data['capacity'],
                                'maxQuantity' => $data['capacity'] + 2,
                            ];
                        }
                        $room->items()->sync($itemRooms);
                    }
                }
            }


            $data['room_type_id'] = $roomType->id;
            $room = Room::create($data);

            $itemIds = Item::whereHas('categories', function ($query) {
                $query->where('name', 'Room');
            })->pluck('id')->toArray();

            $itemRooms = [];
            foreach ($itemIds as $itemId) {
                $itemRooms[$itemId] = [
                    'minQuantity' => $roomType->minCapacity,
                    'maxQuantity' => $roomType->maxCapacity,
                ];
            }
            $room->items()->attach($itemRooms);

            foreach ($data['images'] as $img) {
                $image = [
                    'url' => $img['url'],
                    'room_id' => $room->id
                ];
                RoomImage::create($image);
            }

            EmployeeLogs::create([
                'action' => 'Added a new room: ' . $data['name'],
                'user_id' => $id,
                'type' => 'add'
            ]);

            return response()->json(['success' => true, 'message' => 'Successfully added the new room.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured.', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateRoom(AddRoomRequest $request, Room $room)
    {
        try {
            $id = Auth::id();
            $data = $request->validated();
            $roomType = RoomType::where('type', $data['origType'])->first(); // orig type because the type could change when editing.
            if (!$roomType) {
                $data = $request->validated();
                $data['minCapacity'] = $data['capacity'];
                $data['maxCapacity'] = $data['capacity'] + 2;

                $newAttributeIds = [];
                foreach ($data['attributes'] as $attr) {
                    $newAttribute = RoomAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                    $newAttributeIds[] = $newAttribute->id;
                }

                $roomType = RoomType::create($data);
                $roomType->attributes()->attach($newAttributeIds);
            } else { // updated new type
                if (isset($data['attributes'])) {
                    $roomType->attributes()->detach();

                    RoomAttribute::where('type', $data['origType'])->delete();
                    $newAttributeIds = [];
                    // Delete existing attributes for the room's original type
                    foreach ($data['attributes'] as $attr) {
                        $newAttribute = RoomAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                        $newAttributeIds[] = $newAttribute->id;
                    }
                }

                $roomType->update([
                    'type' => $data['type'],
                    'price' => $data['price'],
                    'minCapacity' => $data['capacity'],
                    'maxCapacity' => $data['capacity'] + 2,
                    'description' => $data['description'],
                ]);

                $roomType->attributes()->attach($newAttributeIds);

                if ($roomType->minCapacity !== $data['capacity']) {
                    $rooms = $roomType->rooms;
                    foreach ($rooms as $room) {
                        $itemIds = Item::whereHas('categories', function ($query) {
                            $query->where('name', 'Room');
                        })->pluck('id')->toArray();

                        $itemRooms = [];
                        foreach ($itemIds as $itemId) {
                            $itemRooms[$itemId] = [
                                'minQuantity' => $data['capacity'],
                                'maxQuantity' => $data['capacity'] + 2,
                            ];
                        }
                        $room->items()->sync($itemRooms);
                    }
                }
            }


            $data['room_type_id'] = $roomType->id;
            $room->update($data);
            $room->images()->delete(); // Delete existing images
            $room->images()->createMany($data['images']); // Create new images

            EmployeeLogs::create([
                'action' => 'Updated room: ' . $room->name,
                'user_id' => $id,
                'type' => 'update'
            ]);

            return response()->json(['success' => true, 'message' => 'Successfully updated room ' . $room->name . '.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured.', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAllRoomTypes()
    {
        try {
            $roomTypes = RoomType::withCount('rooms')->get();
            return new RoomTypesResponse($roomTypes);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured.', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateRoomsByType(EditRoomsByType $request)
    {
        try {
            $id = Auth::id();
            $data = $request->validated();
            $roomType = RoomType::where('type', $data['origType'])->first(); // orig type because the type could change when editing.
            $newAttributeIds = [];

            if (!$roomType) {
                $data = $request->validated();
                $data['minCapacity'] = $data['capacity'];
                $data['maxCapacity'] = $data['capacity'] + 2;

                foreach ($data['attributes'] as $attr) {
                    $newAttribute = RoomAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                    $newAttributeIds[] = $newAttribute->id;
                }

                $roomType = RoomType::create($data);
                $roomType->attributes()->attach($newAttributeIds);
            } else { // updated new type
                if (isset($data['attributes'])) {
                    $roomType->attributes()->detach();

                    RoomAttribute::where('type', $data['origType'])->delete();
                    // Delete existing attributes for the room's original type
                    foreach ($data['attributes'] as $attr) {
                        $newAttribute = RoomAttribute::create(['type' => $data['type'], 'name' => $attr['name']]);
                        $newAttributeIds[] = $newAttribute->id;
                    }
                }

                $roomType->update([
                    'type' => $data['type'],
                    'price' => $data['price'],
                    'minCapacity' => $data['capacity'],
                    'maxCapacity' => $data['capacity'] + 2,
                    'description' => $data['description'],
                ]);

                $roomType->attributes()->attach($newAttributeIds);

                if ($roomType->minCapacity !== $data['capacity']) {
                    $rooms = $roomType->rooms;
                    foreach ($rooms as $room) {
                        $itemIds = Item::whereHas('categories', function ($query) {
                            $query->where('name', 'Room');
                        })->pluck('id')->toArray();

                        $itemRooms = [];
                        foreach ($itemIds as $itemId) {
                            $itemRooms[$itemId] = [
                                'minQuantity' => $data['capacity'],
                                'maxQuantity' => $data['capacity'] + 2,
                            ];
                        }
                        $room->items()->sync($itemRooms);
                    }
                }
            }

            EmployeeLogs::create([
                'action' => 'Updated cottage type: ' . $roomType->type,
                'user_id' => $id,
                'type' => 'update'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated room type: ' . $roomType->type . '.'
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
