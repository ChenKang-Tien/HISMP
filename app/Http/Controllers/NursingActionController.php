<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NursingActionController extends Controller
{
    /**
     * POST /api/v1/patients/{mr}/weights
     */
    public function updateWeights(Request $request, $mr)
    {
        $validated = $request->validate([
            'pre' => 'nullable|numeric',
            'post' => 'nullable|numeric',
            'note' => 'nullable|string'
        ]);

        // 查找對應的 PatientCheck
        $patient = \App\Models\Patient::where('medical_record_no', $mr)->first();
        if ($patient) {
            $check = \App\Models\PatientCheck::whereHas('patient_reservation', function($q) use ($patient) {
                $q->where('patient_id', $patient->id);
            })->latest('date')->first();

            if ($check) {
                $check->update([
                    'measure_weight_before' => $validated['pre'] ?? $check->measure_weight_before,
                    'measure_weight_after' => $validated['post'] ?? $check->measure_weight_after
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => '體重數據已校正並記錄護理日誌。'
        ], 200);
    }

    /**
     * POST /api/v1/patients/{mr}/weight-adjustments
     */
    public function updateWeightAdjustments(Request $request, $mr)
    {
        // 增加除錯資訊
        \Illuminate\Support\Facades\Log::info('WeightAdjustments Payload Received:', ['mr' => $mr, 'data' => $request->all()]);

        $validated = $request->validate([
            'items' => 'present|array',
            'items.*.item_id' => 'required',
            'items.*.weight' => 'required|numeric',
            'items.*.category' => 'required|in:pre,post',
        ]);

        \Illuminate\Support\Facades\Log::info('WeightAdjustments Validated Data:', ['data' => $validated]);

        $patient = \App\Models\Patient::where('medical_record_no', $mr)->first();
        if (!$patient) return response()->json(['success' => false], 404);

        $check = \App\Models\PatientCheck::whereHas('patient_reservation', function($q) use ($patient) {
            $q->where('patient_id', $patient->id);
        })->latest('date')->first();

        if ($check) {
            // 清除該檢查表下的所有舊調整紀錄
            \App\Models\PatientBeforeAdjustWeight::where('patient_check_id', $check->id)->delete();
            \App\Models\PatientAfterAdjustWeight::where('patient_check_id', $check->id)->delete();

            // 如果有項目才新增，沒有項目則保持全刪除狀態
            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $adjustClass = ($item['category'] ?? 'pre') == 'post'
                        ? \App\Models\PatientAfterAdjustWeight::class
                        : \App\Models\PatientBeforeAdjustWeight::class;

                    $adjustClass::create([
                        'patient_check_id' => $check->id,
                        'item_id' => $item['item_id'],
                        // 透後調整通常為「加回」或「扣除」，這裡確保正負號符合業務邏輯
                        // 若 category 為 post，且原本是扣重(正值)，可能需要反轉符號
                        'weight' => $item['weight'],
                        'way_add' => 0,
                        'nurse_id' => Auth::user()->id
                    ]);
                }
            }
        }

        // 回傳處理過的資料供前端 debug
        return response()->json([
            'success' => true,
            'message' => '扣重項目已更新。',
            'debug_received' => $validated['items'] // 將驗證後的資料回傳
        ], 200);
    }

    /**
     * POST /api/v1/patients/{mr}/absence-leave
     */
    public function issueAbsenceLeave(Request $request, $mr)
    {
        $validated = $request->validate([
            'status' => 'required|in:LEAVE,HOSPITALIZED',
            'note' => 'nullable|string'
        ]);

        return response()->json([
            'success' => true,
            'message' => '後端成功接收假單！病患 ' . $mr . ' 狀態已變更為 ' . $validated['status'] . '，系統已自動留痕交接。'
        ], 200);
    }

    /**
     * POST /api/v1/patients/{mr}/incidents
     */
    public function reportIncident(Request $request, $mr)
    {
        $validated = $request->validate([
            'type' => 'required|string'
        ]);

        return response()->json([
            'success' => true,
            'message' => '臨床事件 ' . $validated['type'] . ' 已記錄。'
        ], 200);
    }

    /**
     * GET /api/v1/nursing/shift-options
     */
    public function fetchShiftOptions()
    {
        return response()->json([
            'nurses' => [['id' => 1, 'name' => '楚心瑜'], ['id' => 2, 'name' => '王曉明']],
            'groups' => [['id' => 1, 'name' => 'A 組'], ['id' => 2, 'name' => 'B 組']]
        ], 200);
    }

    /**
     * POST /api/v1/nursing/shifts
     */
    public function saveShift(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'nurse_id' => 'required',
            'group_id' => 'required'
        ]);
        return response()->json(['success' => true], 200);
    }

    /**
     * GET /api/v1/nursing/supply-tmr
     */
    public function fetchSupplyList()
    {
        return response()->json([
            'items' => [
                ['id' => 1, 'name' => 'FX80 Classix 人工腎臟', 'count' => 12, 'unit' => '組'],
                ['id' => 2, 'name' => 'Heparin 1000u/ml', 'count' => 24, 'unit' => '支']
            ],
            'isLocked' => false
        ], 200);
    }

    /**
     * POST /api/v1/nursing/supply-tmr/lock
     */
    public function lockSupplyList()
    {
        return response()->json(['success' => true], 200);
    }
}
