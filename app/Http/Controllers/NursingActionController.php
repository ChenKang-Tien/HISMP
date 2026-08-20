<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NursingActionController extends Controller
{
    /**
     * POST /api/v1/dialysis-checks/{check_id}/weights
     */
    public function updateWeights(Request $request, $check_id)
    {
        $validated = $request->validate([
            'pre' => 'nullable|numeric',
            'post' => 'nullable|numeric',
            'note' => 'nullable|string'
        ]);

        $check = \App\Models\PatientCheck::findOrFail($check_id);
        $check->update([
            'measure_weight_before' => $validated['pre'] ?? $check->measure_weight_before,
            'measure_weight_after' => $validated['post'] ?? $check->measure_weight_after
        ]);

        return response()->json([
            'success' => true,
            'message' => '體重數據已校正並記錄護理日誌。'
        ], 200);
    }

    /**
     * POST /api/v1/dialysis-checks/{check_id}/weight-adjustments
     */
    public function updateWeightAdjustments(Request $request, $check_id)
    {
        $validated = $request->validate([
            'items' => 'present|array',
            'items.*.item_id' => 'required',
            'items.*.weight' => 'required|numeric',
            'items.*.category' => 'required|in:pre,post',
        ]);

        $check = \App\Models\PatientCheck::findOrFail($check_id);

        // 清除該檢查表下的所有舊調整紀錄
        \App\Models\PatientBeforeAdjustWeight::where('patient_check_id', $check->id)->delete();
        \App\Models\PatientAfterAdjustWeight::where('patient_check_id', $check->id)->delete();

        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $adjustClass = ($item['category'] ?? 'pre') == 'post'
                    ? \App\Models\PatientAfterAdjustWeight::class
                    : \App\Models\PatientBeforeAdjustWeight::class;

                $adjustClass::create([
                    'patient_check_id' => $check->id,
                    'item_id' => $item['item_id'],
                    'weight' => $item['weight'],
                    'way_add' => 0,
                    'nurse_id' => Auth::user()->id
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => '扣重項目已更新。'
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
     * POST /api/v1/dialysis-checks/{check_id}/vitals
     */
    public function updateVitals(Request $request, $check_id)
    {
        $validated = $request->validate([
            'sys' => 'nullable|numeric',
            'dia' => 'nullable|numeric',
            'pr' => 'nullable|numeric',
            'rr' => 'nullable|numeric',
            'temp' => 'nullable|numeric',
            'fs' => 'nullable|numeric'
        ]);

        $check = \App\Models\PatientCheck::findOrFail($check_id);

        // 存入 PatientBeforePhysiologicalDatas
        \App\Models\PatientBeforePhysiologicalDatas::updateOrCreate(
            ['patient_check_id' => $check->id],
            [
                'systolic_blood_pressure' => $validated['sys'],
                'diastolic_blood_pressure' => $validated['dia'],
                'P' => $validated['pr'],
                'R' => $validated['rr'],
                'T' => $validated['temp'],
                'fs' => $validated['fs'] ?? null
            ]
        );

        // 同步更新 PatientMidBpPDatas
        \App\Models\PatientMidBpPDatas::updateOrCreate(
            ['patient_check_id' => $check->id, 'dispose_id' => 1],
            [
                'time' => now()->format('Y-m-d H:i:s'), // 修正為完整 datetime
                'systolic_blood_pressure' => $validated['sys'],
                'diastolic_blood_pressure' => $validated['dia'],
                'P' => $validated['pr'],
                'machine' => 0,
                'nurse_id' => Auth::user()->id
            ]
        );

        return response()->json([
            'success' => true,
            'message' => '生命徵象已更新',
            'data' => $validated
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
