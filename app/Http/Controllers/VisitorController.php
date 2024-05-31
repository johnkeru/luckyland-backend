<?php

namespace App\Http\Controllers;

use App\Models\Visitor;

class VisitorController extends Controller
{
    // Display the visitor count
    public function show()
    {
        $visitor = Visitor::first();
        if (!$visitor) {
            $visitor = Visitor::create(['count' => 0]);
        }
        return response()->json(['data' => $visitor->count]);
    }

    // Increment the visitor count
    public function increment()
    {
        $visitor = Visitor::first();
        if (!$visitor) {
            $visitor = Visitor::create(['count' => 0]);
        }
        $visitor->increment('count');
        return response()->json(['count' => $visitor->count]);
    }
}
