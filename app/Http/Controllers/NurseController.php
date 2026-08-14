<?php

namespace App\Http\Controllers;

use App\Models\DialysisDispose;
use App\Models\NurseRecordPhrase;
use App\Models\TestItem;
use App\Models\WeightAdjustItem;
use Illuminate\Http\Request;

class NurseController extends Controller
{
    public function getPhrase()
    {
        // 重量調整 (假設是硬刪除，直接抓全部)
        $weights = WeightAdjustItem::all();
        
        // 護理紀錄 (過濾掉 deleted = 1 的)
        $nursePhrases = NurseRecordPhrase::where('deleted', 0)->get();
        
        // 檢驗項目
        $testItems = TestItem::all();
        
        // 中斷處置 (過濾掉 deleted = false/0 的)
        $dialysisDisposes = DialysisDispose::where('deleted', false)->get();

        return response()->json([
            'weights' => $weights,
            'nursePhrases' => $nursePhrases,
            'testItems' => $testItems,
            'dialysisDisposes' => $dialysisDisposes
        ]);
    }

    // ==========================================
    // 1. 重量調整項目 (WeightAdjustItem)
    // ==========================================
    
    public function storeWeight(Request $request)
    {
        $validated = $request->validate([
            'item' => 'required|string',
            'default_weight' => 'required|numeric',
        ]);

        WeightAdjustItem::create($validated);
        return response()->json(['message' => 'Created successfully']);
    }

    public function updateWeight(Request $request, $id)
    {
        $item = WeightAdjustItem::findOrFail($id);
        $item->update($request->only(['item', 'default_weight']));
        return response()->json(['message' => 'Updated successfully']);
    }

    public function deleteWeight($id)
    {
        $item = WeightAdjustItem::findOrFail($id);
        
        // 清理所有引用此 item_id 的扣重記錄 (防止同步時觸發驗證錯誤)
        \App\Models\PatientBeforeAdjustWeight::where('item_id', $id)->delete();
        \App\Models\PatientAfterAdjustWeight::where('item_id', $id)->delete();
        
        $item->delete();
        return response()->json(['message' => 'Deleted successfully and related records cleared']);
    }

    // ==========================================
    // 2. 護理紀錄輔助 (NurseRecordPhrase)
    // ==========================================

    public function storePhrase(Request $request)
    {
        $request->validate(['name' => 'required|string']);

        NurseRecordPhrase::create([
            'name' => $request->name,
            'category' => $request->category ?? 'nurse_phrase',
            'deleted' => 0 
        ]);
        return response()->json(['message' => 'Created successfully']);
    }

    public function updatePhrase(Request $request, $id)
    {
        $phrase = NurseRecordPhrase::findOrFail($id);
        $phrase->update(['name' => $request->name]);
        return response()->json(['message' => 'Updated successfully']);
    }

    public function deletePhrase($id)
    {
        $phrase = NurseRecordPhrase::findOrFail($id);
        $phrase->update(['deleted' => 1]);
        return response()->json(['message' => 'Deleted successfully']);
    }

    // ==========================================
    // 3. 檢驗項目 (TestItem)
    // ==========================================

    /**
     * GET /nurse/test-items - 檢驗項目列表
     */
    public function getTestItems()
    {
        $testItems = TestItem::orderBy('name')->get();
        return response()->json(['testItems' => $testItems]);
    }

    /**
     * POST /nurse/test-item - 新增檢驗項目
     */
    public function storeTestItem(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'frequency' => 'required|in:mo,qt,yr,ot',
            'range_lower' => 'nullable|numeric',
            'range_upper' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'education_summary' => 'nullable|string',
        ]);

        TestItem::create($validated);
        return response()->json(['message' => 'Created successfully']);
    }

    /**
     * PUT /nurse/test-item/{id} - 更新檢驗項目
     */
    public function updateTestItem(Request $request, $id)
    {
        $item = TestItem::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'frequency' => 'required|in:mo,qt,yr,ot',
            'range_lower' => 'nullable|numeric',
            'range_upper' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'education_summary' => 'nullable|string',
        ]);

        $item->update($validated);
        return response()->json(['message' => 'Updated successfully']);
    }

    /**
     * DELETE /nurse/test-item/{id} - 刪除檢驗項目
     */
    public function deleteTestItem($id)
    {
        $item = TestItem::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    // ==========================================
    // 4. 中斷處置項目 (DialysisDispose)
    // ==========================================

    public function storeDispose(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        DialysisDispose::create([
            'name' => $request->name,
            'deleted' => 0
        ]);
        return response()->json(['message' => 'Created successfully']);
    }

    public function updateDispose(Request $request, $id)
    {
        $item = DialysisDispose::findOrFail($id);
        $item->update(['name' => $request->name]);
        return response()->json(['message' => 'Updated successfully']);
    }

    public function deleteDispose($id)
    {
        $item = DialysisDispose::findOrFail($id);
        $item->update(['deleted' => 1]);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
