<?php

namespace App\Http\Controllers;

use App\Models\Cottage;
use App\Models\Item;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Unavailable;
use DateTime;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function todayOverview()
    {
        $checkIn = now();
        $checkOut = now();

        $availableRooms = Room::where('active', true)
            ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                $query->whereIn('status', ['Approved', 'In Resort'])
                    ->where(function ($q) use ($checkIn, $checkOut) {
                        $q->where('checkIn', '<', $checkOut)
                            ->where('checkOut', '>', $checkIn);
                    });
            })
            ->count();

        $availableCottages = Cottage::where('active', true)
            ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                $query->whereIn('status', ['Approved', 'In Resort'])
                    ->where(function ($q) use ($checkIn, $checkOut) {
                        $q->where('checkIn', '<', $checkOut)
                            ->where('checkOut', '>', $checkIn);
                    });
            })
            ->count();

        $totalGuests = Reservation::where(function ($query) use ($checkIn) {
            $query->whereDate('checkIn', $checkIn)
                ->orWhereDate('actualCheckIn', $checkIn); // Include 'In Resort' reservations
        })
            ->whereIn('status', ['Approved', 'In Resort']) // Consider only 'Approved' and 'In Resort' reservations
            ->sum('guests');

        return [
            'guests' => $totalGuests,
            'rooms' => $availableRooms,
            'cottages' => $availableCottages,
        ];
    }

    public function monthOverview()
    {
        $currentMonth = now()->format('m');
        $currentYear = now()->format('Y');

        $upcomingCount = Reservation::whereYear('checkIn', '>=', $currentYear)
            ->whereMonth('checkIn', '>=', $currentMonth)
            ->whereIn('status', ['Approved'])
            ->count();

        $walkInCount = Reservation::whereYear('checkIn', '=', $currentYear)
            ->whereMonth('checkIn', '=', $currentMonth)
            ->where('isWalkIn', true)
            ->count();

        $onlineCount = Reservation::whereYear('checkIn', '=', $currentYear)
            ->whereMonth('checkIn', '=', $currentMonth)
            ->where('isWalkIn', false)
            ->count();

        $totalGuests = Reservation::whereYear('checkIn', '=', $currentYear)
            ->whereMonth('checkIn', '=', $currentMonth)
            ->whereIn('status', ['Approved'])
            ->sum('guests');

        return [
            'upcoming' => $upcomingCount,
            'walkin' => $walkInCount,
            'online' => $onlineCount,
            'guests' => $totalGuests
        ];
    }

    public function barMonthlyReservation()
    {
        $reservations = Reservation::select(
            DB::raw('MONTH(checkIn) as month'),
            DB::raw('SUM(CASE WHEN isWalkIn = 0 THEN 1 ELSE 0 END) as online'),
            DB::raw('SUM(CASE WHEN isWalkIn = 1 THEN 1 ELSE 0 END) as walkIn')
        )
            ->groupBy(DB::raw('MONTH(checkIn)'))
            ->orderBy(DB::raw('MONTH(checkIn)'))
            ->get();

        $data = [];

        foreach ($reservations as $reservation) {
            $data[] = [
                'month' => DateTime::createFromFormat('!m', $reservation->month)->format('M'),
                'online' => $reservation->online,
                'walkIn' => $reservation->walkIn
            ];
        }

        return $data;
    }

    public function pieReservation()
    {
        $statuses = ['Cancelled', 'Approved', 'In Resort'];
        // $statuses = ['Cancelled', 'Approved'];
        $reservations = Reservation::select(
            'status',
            DB::raw('COUNT(*) as count')
        )
            ->whereIn('status', $statuses)
            ->groupBy('status')
            ->get();

        $data = [];
        $colors = [
            'Cancelled' => '#ff6961',
            'Approved' => '#77aaff',
            'In Resort' => '#77dd77',
        ];

        $id = 0;
        foreach ($reservations as $reservation) {
            $data[] = [
                'id' => $id,
                'value' => $reservation->count,
                'label' => $reservation->status,
                'color' => $colors[$reservation->status],
            ];
            $id++;
        }
        return $data;
    }

    public function lineReservation()
    {
        $reservations = Reservation::select(
            DB::raw('DATE_FORMAT(checkIn, "%Y-%m-01") as month_year'),
            DB::raw('COUNT(*) as reservations')
        )
            ->groupBy('month_year')
            ->orderBy('month_year')
            ->get();

        $data = [];

        foreach ($reservations as $reservation) {
            $data[] = [
                'date' => $reservation->month_year,
                'reservations' => $reservation->reservations
            ];
        }
        return $data;
    }

    public function inventorySummary()
    {
        $inStockCount = Item::where('status', 'In Stock')->count();
        $lowStockCount = Item::where('status', 'Low Stock')->count();
        $outOfStockCount = Item::where('status', 'Out of Stock')->count();

        // Count items that belong to the 'Room' category
        $roomItemsCount = Item::whereHas('categories', function ($query) {
            $query->where('name', 'Room');
        })->count();

        // Return the counts
        return [
            'inStockCount' => $inStockCount,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'roomItemsCount' => $roomItemsCount,
        ];
    }

    public function roomStockLevel()
    {
        $roomStockItems = Item::where('status', 'In Stock')
            ->whereHas('categories', function ($query) {
                $query->where('name', 'Room');
            })
            ->select('name', 'currentQuantity as stockLevel')
            ->get();
        $inventoryData = $roomStockItems->map(function ($item) {
            return [
                'item' => $item->name,
                'stockLevel' => $item->stockLevel,
            ];
        });
        return $inventoryData;
    }

    public function pieInventory()
    {
        // Count items with different statuses
        $outOfStockCount = Item::where('status', 'Out of Stock')->count();
        $lowStockCount = Item::where('status', 'Low Stock')->count();
        $inStockCount = Item::where('status', 'In Stock')->count();

        // Count items with 'Reserved' reason in Unavailable model
        $reservedCount = Unavailable::where('reason', 'like', '%Reserved%')->count();

        // Calculate total count
        $totalCount = $outOfStockCount + $lowStockCount + $inStockCount + $reservedCount;

        // Calculate percentages
        $outOfStockPercentage = ($outOfStockCount / $totalCount) * 100;
        $lowStockPercentage = ($lowStockCount / $totalCount) * 100;
        $inStockPercentage = ($inStockCount / $totalCount) * 100;
        $reservedPercentage = ($reservedCount / $totalCount) * 100;

        // Format the data
        $inventorySummaryData = [
            ['id' => 0, 'value' => $outOfStockPercentage, 'label' => 'Out of Stock', 'color' => '#ff6961'],
            ['id' => 1, 'value' => $lowStockPercentage, 'label' => 'Low Stock', 'color' => '#ffa500'],
            ['id' => 2, 'value' => $inStockPercentage, 'label' => 'In Stock', 'color' => '#77dd77'],
            ['id' => 3, 'value' => $reservedPercentage, 'label' => 'Reserved', 'color' => '#77aaff'],
        ];

        return $inventorySummaryData;
    }
}
