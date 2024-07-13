<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    // Display the visitor count
    public function show()
    {
        $count = Visitor::count();
        return response()->json(['data' => $count]);
    }

    // Store the visitor's IP address
    public function store(Request $request)
    {
        $ipAddress = $request->ip();
        $isRecent = Visitor::where('ip_address', $ipAddress)->first();
        if (!$isRecent) {
            Visitor::create(['ip_address' => $ipAddress]);
            return response()->json(['message' => 'IP address stored successfully']);
        }
        return response()->noContent();
    }
}
