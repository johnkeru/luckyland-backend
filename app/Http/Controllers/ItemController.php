<?php

namespace App\Http\Controllers;

use App\Http\Responses\ItemSearchResponse;
use App\Models\Item;

class ItemController extends Controller
{

    public function searchItemsInInventory()
    {
        $search = request()->query('search');
        $items = Item::with(['category'])
            ->search($search)
            ->paginate(5);
        return new ItemSearchResponse($items);
    }
}
