<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MedicalEquipmen;
use App\Models\Provider;
use App\Models\Unit;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    //
    // 取得所有設備及相關選項 (對應 fetchSupplies)
    public function index()
    {
        $equipments = MedicalEquipmen::with(['category', 'unit', 'provider'])
            ->where('deleted', 0)
            ->get();

        $categories = Category::where('type', 2)->get();
        $units = Unit::where('type', 2)->get();
        $providers = Provider::all();

        return response()->json([
            'equipments' => $equipments, // 配合前端變數名
            'categories' => $categories,
            'units' => $units,
            'providers' => $providers
        ]);
    }

    // 新增設備
    public function store(Request $request)
    {
        $request->validate([
            'short_name' => 'required',
            'chinese_name' => 'required',
            'product_name' => 'nullable',
            'category_id' => 'required',
            'unit_id' => 'required',
            'provider_id' => 'required',
            'inventory_control' => 'boolean',
        ]);

        $item = MedicalEquipmen::create([
            'encode_id' => "",
            'short_name' => $request->short_name, // 前端傳 code，後端存 short_name
            'chinese_name' => $request->chinese_name,
            'product_name' => $request->product_name,
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'inventory_control' => $request->inventory_control ? 1 : 0,
            'note' => $request->note,
            'provider_id' => $request->provider_id,
            'uses' => $request->uses,
            'deleted' => 0
        ]);

        return response()->json(['message' => '新增成功', 'data' => $item], 201);
    }

    // 更新設備
    public function update(Request $request, $id)
    {
        $item = MedicalEquipmen::findOrFail($id);
        
        $item->update([
            'short_name' => $request->short_name,
            'chinese_name' => $request->chinese_name,
            'product_name' => $request->product_name,
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'inventory_control' => $request->inventory_control ? 1 : 0,
            'note' => $request->note,
            'uses' => $request->uses,
            'provider_id' => $request->provider_id,
        ]);

        return response()->json(['message' => '更新成功']);
    }

    // 刪除設備 (軟刪除)
    public function destroy($id)
    {
        $item = MedicalEquipmen::findOrFail($id);
        $item->update(['deleted' => 1]);

        return response()->json(['message' => '已刪除']);
    }
}
