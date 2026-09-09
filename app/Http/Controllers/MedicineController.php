<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q', '');

        if (empty($query)) {
            return response()->json([]);
        }

        $medicines = Medicine::where('chinese_name', 'like', "%{$query}%")
            ->orWhere('product_name', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'chinese_name', 'product_name']);

        return response()->json($medicines);
    }
}
