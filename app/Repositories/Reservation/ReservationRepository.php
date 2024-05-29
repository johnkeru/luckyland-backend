<?php

namespace App\Repositories\Reservation;


use App\Http\Responses\ReservationIndexResponse;
use App\Interfaces\Reservation\ReservationInterface;
use App\Models\Reservation;

class ReservationRepository implements ReservationInterface
{
    public function index()
    {
        try {
            $search = request()->query('search');

            $room = request()->query('room');
            $cottage = request()->query('cottage');
            $other = request()->query('other');

            $status = request()->query('status');
            $month = request()->query('month');

            $reservation = Reservation::search($search)
                ->latest()
                ->filterByRoom($room)
                ->filterByCottage($cottage)
                ->filterByOther($other)
                ->filterByStatus($status)
                ->filterByMonth($month)
                ->with(['rooms', 'cottages', 'others', 'customer', 'customer.address'])
                ->paginate(8);

            $counts = [
                'Approved' => Reservation::where('status', 'Approved')->count(),
                'Cancelled' => Reservation::where('status', 'Cancelled')->count(),
                'Departed' => Reservation::where('status', 'Departed')->count(),
                'In Resort' => Reservation::where('status', 'In Resort')->count(),
            ];
            return new ReservationIndexResponse($reservation, $counts);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred. Please try again later or contact support.'
            ], 500);
        }
    }
}
