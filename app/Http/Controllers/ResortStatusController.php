<?php

namespace App\Http\Controllers;

use App\Models\ResortStatus;

class ResortStatusController extends Controller
{

    public function getResortStatus()
    {
        try {
            $status = ResortStatus::find(1);
            return response()->json([
                'success' => true,
                'data' => $status->status
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'success' => false,
                'message' => 'Failed to update resort status.'
            ], 500);
        }
    }

    public function toggleResortStatus()
    {
        try {
            $requestStatus = request()->status;
            $status = ResortStatus::find(1);
            $status->update(['status' => $requestStatus]);
            return response()->json([
                'success' => true,
                'message' => 'Successfully updated resort status.',
                'data' => $status
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'success' => false,
                'message' => 'Failed to update resort status.'
            ], 500);
        }
    }
}
