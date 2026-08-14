<?php

namespace App\Http\Controllers;

use App\Models\WeightAdjustItem;
use Illuminate\Http\Request;

class WeightAdjustItemController extends Controller
{
    /**
     * GET /api/v1/weight-adjust-items
     */
    public function index()
    {
        return response()->json(WeightAdjustItem::all(), 200);
    }

    /**
     * POST /api/v1/weight-adjust-items
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item' => 'required|string',
            'default_weight' => 'required|numeric',
        ]);

        $item = WeightAdjustItem::create($validated);
        return response()->json($item, 201);
    }
}
