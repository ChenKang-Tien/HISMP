<?php

namespace App\Http\Controllers;

use App\Models\DrugTransaction;
use App\Models\Medicine;
use App\Models\MedicalEquipmen;
use App\Models\SupplyTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * 藥品進銷存列表
     */
    public function index()
    {
        $transactions = DrugTransaction::with(['medicine', 'operator'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $medicines = Medicine::where('deleted', 0)->orderBy('chinese_name')->get(['id', 'short_name', 'chinese_name', 'product_name']);

        return response()->json([
            'transactions' => $transactions,
            'medicines' => $medicines,
        ]);
    }

    /**
     * 新增異動
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'medicine_id' => 'required|exists:medicines,id',
            'type' => 'required|in:進貨,退貨,報廢',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        // 根據類型決定數量正負號
        // 進貨 = 正數（入庫），退貨/報廢 = 負數（出庫）
        $quantity = $request->type === '進貨' ? $request->quantity : -$request->quantity;

        $transaction = DrugTransaction::create([
            'date' => $request->date,
            'medicine_id' => $request->medicine_id,
            'type' => $request->type,
            'quantity' => $quantity,
            'operator_id' => Auth::id(),
            'notes' => $request->notes,
        ]);

        // 重新載入關聯
        $transaction->load(['medicine', 'operator']);

        return response()->json(['message' => '新增成功', 'data' => $transaction], 201);
    }

    /**
     * 更新異動
     */
    public function update(Request $request, $id)
    {
        $transaction = DrugTransaction::findOrFail($id);

        $request->validate([
            'date' => 'required|date',
            'medicine_id' => 'required|exists:medicines,id',
            'type' => 'required|in:進貨,退貨,報廢',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $quantity = $request->type === '進貨' ? $request->quantity : -$request->quantity;

        $transaction->update([
            'date' => $request->date,
            'medicine_id' => $request->medicine_id,
            'type' => $request->type,
            'quantity' => $quantity,
            'notes' => $request->notes,
        ]);

        $transaction->load(['medicine', 'operator']);

        return response()->json(['message' => '更新成功', 'data' => $transaction]);
    }

    /**
     * 刪除異動
     */
    public function destroy($id)
    {
        $transaction = DrugTransaction::findOrFail($id);
        $transaction->delete();

        return response()->json(['message' => '已刪除']);
    }

    // ========== 醫材進銷存 ==========

    /**
     * 醫材進銷存列表
     */
    public function supplyIndex()
    {
        $transactions = SupplyTransaction::with(['equipment', 'operator'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $equipments = MedicalEquipmen::where('deleted', 0)
            ->orderBy('chinese_name')
            ->get(['id', 'short_name', 'chinese_name', 'product_name']);

        return response()->json([
            'transactions' => $transactions,
            'equipments' => $equipments,
        ]);
    }

    /**
     * 新增醫材異動
     */
    public function supplyStore(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'equipment_id' => 'required|exists:medical_equipmen,id',
            'type' => 'required|in:進貨,退貨,報廢',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $quantity = $request->type === '進貨' ? $request->quantity : -$request->quantity;

        $transaction = SupplyTransaction::create([
            'date' => $request->date,
            'equipment_id' => $request->equipment_id,
            'type' => $request->type,
            'quantity' => $quantity,
            'operator_id' => Auth::id(),
            'notes' => $request->notes,
        ]);

        $transaction->load(['equipment', 'operator']);

        return response()->json(['message' => '新增成功', 'data' => $transaction], 201);
    }

    /**
     * 更新醫材異動
     */
    public function supplyUpdate(Request $request, $id)
    {
        $transaction = SupplyTransaction::findOrFail($id);

        $request->validate([
            'date' => 'required|date',
            'equipment_id' => 'required|exists:medical_equipmen,id',
            'type' => 'required|in:進貨,退貨,報廢',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $quantity = $request->type === '進貨' ? $request->quantity : -$request->quantity;

        $transaction->update([
            'date' => $request->date,
            'equipment_id' => $request->equipment_id,
            'type' => $request->type,
            'quantity' => $quantity,
            'notes' => $request->notes,
        ]);

        $transaction->load(['equipment', 'operator']);

        return response()->json(['message' => '更新成功', 'data' => $transaction]);
    }

    /**
     * 刪除醫材異動
     */
    public function supplyDestroy($id)
    {
        $transaction = SupplyTransaction::findOrFail($id);
        $transaction->delete();

        return response()->json(['message' => '已刪除']);
    }
}
