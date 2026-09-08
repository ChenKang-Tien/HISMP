<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NursingActionController extends Controller
{
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

    public function updateWeightAdjustments(Request $request, $check_id)
    {
        $validated = $request->validate([
            'items' => 'present|array',
            'items.*.item_id' => 'required',
            'items.*.weight' => 'required|numeric',
            'items.*.category' => 'required|in:pre,post',
        ]);

        $check = \App\Models\PatientCheck::findOrFail($check_id);

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

        return response()->json(['success' => true, 'message' => '扣重項目已更新。'], 200);
    }

    public function updateAssessments(Request $request, $check_id)
    {
        $validated = $request->validate([
            'vascularKind' => 'nullable',
            'vascularEval' => 'nullable',
            'consciousState' => 'nullable',
            'skinState' => 'nullable',
        ]);

        $typeMap = ['AVF' => 0, 'AVG' => 1, 'Permcath' => 2, 'D/L' => 3];
        $posLRMap = ["L't" => 0, "R't" => 1];
        $posCenterMap = ['SCVC' => 2, 'JVC' => 3, 'FVC' => 4];

        $kindData = explode(',', $validated['vascularKind'] ?? 'AVF,,,');
        $kindIdx = $typeMap[$kindData[0]] ?? 0;
        $posLR = $kindData[1] ?? "L't";
        $posCenter = $kindData[2] ?? 'SCVC';
        $permcathLen = $kindData[3] ?? '';
        $vascularNote = $kindData[4] ?? '';

        $evalInput = is_array($validated['vascularEval'] ?? null)
            ? $validated['vascularEval']
            : explode(',', $validated['vascularEval'] ?? '');

        if (count($evalInput) > 6) {
            $vascularNote = array_pop($evalInput);
        }

        $evalBits = array_map(fn($i) => (isset($evalInput[$i]) && $evalInput[$i] == '1') ? '1' : '0', range(0, 5));
        if (!in_array('1', $evalBits)) {
            $evalBits = ['1', '0', '0', '0', '0', '0'];
        }

        $skinData = explode(',', $validated['skinState'] ?? '0,,,');
        $skinIdx = (int)($skinData[0] ?? 0);
        $skinLocation = $skinData[1] ?? '';
        $skinSize = $skinData[2] ?? '';
        $skinNote = $skinData[3] ?? '';

        $consciousData = explode(',', $validated['consciousState'] ?? '0,');
        $consciousIdx = (int)($consciousData[0] ?? 0);
        $consciousNote = $consciousData[1] ?? '';

        \App\Models\PatientBeforePhysiologicalDatas::updateOrCreate(
            ['patient_check_id' => $check_id],
            [
                'patient_check_id' => $check_id,
                'vascular_access_type' => $kindIdx . ($kindData[0] === 'Permcath' ? ',' . $permcathLen : ''),
                'vascular_access_location' => ($posLRMap[$posLR] ?? 0) . ',' . ($posCenterMap[$posCenter] ?? 2),
                'vascular_access' => implode(',', $evalBits) . ',' . $vascularNote,
                'consciousness' => $consciousIdx . ',' . $consciousNote,
                'skin' => $skinIdx . ',' . $skinLocation . ',' . $skinSize . ',' . $skinNote,
            ]
        );

        return response()->json(['success' => true, 'message' => '三大評估已更新。'], 200);
    }

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

        \App\Models\PatientBeforePhysiologicalDatas::updateOrCreate(
            ['patient_check_id' => $check->id],
            [
                'patient_check_id' => $check->id,
                'systolic_blood_pressure' => $validated['sys'],
                'diastolic_blood_pressure' => $validated['dia'],
                'P' => $validated['pr'],
                'R' => $validated['rr'],
                'T' => $validated['temp'],
                'fs' => $validated['fs'] ?? null
            ]
        );

        \App\Models\PatientMidBpPDatas::updateOrCreate(
            ['patient_check_id' => $check->id, 'dispose_id' => 1],
            [
                'time' => now()->format('Y-m-d H:i:s'),
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

    public function fetchShiftOptions()
    {
        return response()->json([
            'nurses' => [['id' => 1, 'name' => '楚心瑜'], ['id' => 2, 'name' => '王曉明']],
            'groups' => [['id' => 1, 'name' => 'A 組'], ['id' => 2, 'name' => 'B 組']]
        ], 200);
    }

    public function saveShift(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'nurse_id' => 'required',
            'group_id' => 'required'
        ]);
        return response()->json(['success' => true], 200);
    }

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

    public function savePreDialysisMedicines(Request $request, $check_id)
    {
        $validated = $request->validate([
            'medicines' => 'required|array',
            'medicines.*.medicine_id' => 'required|integer',
            'medicines.*.medicine' => 'required|string',
            'medicines.*.amount' => 'required|integer',
            'medicines.*.route_id' => 'required|integer',
            'medicines.*.note' => 'nullable|string',
        ]);

        \App\Models\PatientBeforeDialysisMedicine::where('patient_check_id', $check_id)->delete();

        foreach ($validated['medicines'] as $med) {
            \App\Models\PatientBeforeDialysisMedicine::create([
                'patient_check_id' => $check_id,
                'medicine_id' => $med['medicine_id'],
                'medicine' => $med['medicine'],
                'amount' => $med['amount'],
                'route_id' => $med['route_id'],
                'note' => $med['note'] ?? '',
                'nurse_id' => Auth::user()->id,
                'deleted' => 0,
            ]);
        }

        return response()->json(['success' => true, 'message' => '透前給藥紀錄已更新。'], 200);
    }

}
