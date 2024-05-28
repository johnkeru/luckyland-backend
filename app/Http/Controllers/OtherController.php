<?php

namespace App\Http\Controllers;

use App\Http\Responses\ReservationAvailableCottagesResponse;
use App\Models\Item;
use App\Models\Other;
use Illuminate\Http\Request;

class OtherController extends Controller
{
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
}
