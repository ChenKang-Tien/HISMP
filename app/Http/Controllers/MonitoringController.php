<?php

namespace App\Http\Controllers;

use App\Models\PatientCheck;
use App\Models\PatientMidBpPDatas;
use App\Models\PatientMidDialysisRecordNew;
use App\Models\PatientDialysisManualRecord;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    // GET /dialysis/{id}/monitoring - 三表組合讀取
    public function index($id)
    {
        $check = PatientCheck::findOrFail($id);

        // 取得治療開始時間（On-Sign 時間）作為 h1~h4 計算基準
        $startTime = $check->start_time ? strtotime($check->start_time) : null;

        $result = [];

        // —— 透前 (dispose_id=1) ——
        $preBP = PatientMidBpPDatas::where('patient_check_id', $id)->where('display', 1)->where('dispose_id', 1)->first();
        $preManual = PatientDialysisManualRecord::where('patient_check_id', $id)->where('dispose_id', 1)->first();
        $result[] = [
            'time_slot' => 'pre', 'dispose_id' => 1,
            'measured_at' => $preBP->time ?? $preManual->HCDTTM ?? null,
            'bp_systolic' => $preManual->BDPS ?? $preBP->systolic_blood_pressure ?? null,
            'bp_diastolic' => $preManual->BDPD ?? $preBP->diastolic_blood_pressure ?? null,
            'pulse' => $preManual->BDPL ?? $preBP->P ?? null,
            'qb' => null, 'vp' => null, 'tmp' => null, 'uf_done' => null,
            'ufr' => null, 'heparin' => null, 'qd' => null, 'cond' => null, 'dial_temp' => null,
            'notes' => $preManual->note ?? null,
            'source' => $preManual ? 'manual' : ($preBP ? 'machine_bp' : null),
        ];

        // —— 透析中 (dispose_id=2) 依 start_time 分配到 h1~h4 ——
        $midManuals = PatientDialysisManualRecord::where('patient_check_id', $id)
            ->where('dispose_id', 2)->orderBy('HCDTTM')->get();
        $midBPs = PatientMidBpPDatas::where('patient_check_id', $id)
            ->where('display', 1)->where('dispose_id', 2)->orderBy('time')->get();
        $midMachine = PatientMidDialysisRecordNew::where('patient_check_id', $id)
            ->orderBy('HCDTTM')->get();

        // 分配函式：給定 dispose_id 與時間戳，回傳時段標籤
        // dispose_id >= 11 為加測時段，以 extra_開頭+數字，前端可獨立顯示
        $allocateSlot = function($ts, $dispId) use ($startTime) {
            if ($dispId >= 11) return 'extra_' . $dispId;  // 加測，獨立一列
            if (!$startTime || !$ts) return 'unknown';
            $diffMins = (strtotime($ts) - $startTime) / 60;
            if ($diffMins < 0) return 'pre';
            if ($diffMins < 60) return 'h1';
            if ($diffMins < 120) return 'h2';
            if ($diffMins < 180) return 'h3';
            if ($diffMins < 240) return 'h4';
            return 'post';
        };

        // 收集 dispose_id=2 的所有記錄，依時間排序後分組
        $allMidRecords = collect();
        foreach ($midManuals as $m) {
            $allMidRecords->push(['type' => 'manual', 'time' => $m->HCDTTM, 'data' => $m]);
        }
        foreach ($midBPs as $b) {
            $allMidRecords->push(['type' => 'bp', 'time' => $b->time, 'data' => $b]);
        }
        $sorted = $allMidRecords->sortBy('time')->values();

        // 依時間分配到 h1~h4
        $slots = ['h1' => [], 'h2' => [], 'h3' => [], 'h4' => []];
        foreach ($sorted as $rec) {
            $slot = $allocateSlot($rec['time']);
            if (isset($slots[$slot])) {
                $slots[$slot][] = $rec;
            }
        }

        // 產出 h1~h4 + extra_* 各時段資料
        $allSlotNames = array_merge(['h1', 'h2', 'h3', 'h4'], array_keys(array_filter($slots, function($k) { return strpos($k, 'extra_') === 0; }, ARRAY_FILTER_USE_KEY)));
        foreach ($allSlotNames as $slotName) {
            if (!isset($slots[$slotName])) {
                $slots[$slotName] = [];
            }
            $recs = $slots[$slotName];
            $dispId = strpos($slotName, 'extra_') === 0 ? (int)substr($slotName, 6) : 2;
            if (count($recs) == 0) {
                $result[] = [
                    'time_slot' => $slotName, 'dispose_id' => $dispId,
                    'measured_at' => null, 'start_time' => $check->start_time,
                    'bp_systolic' => null, 'bp_diastolic' => null, 'pulse' => null,
                    'qb' => null, 'vp' => null, 'tmp' => null, 'uf_done' => null,
                    'ufr' => null, 'heparin' => null, 'qd' => null, 'cond' => null,
                    'dial_temp' => null, 'notes' => null, 'source' => null,
                ];
                continue;
            }
            $last = $recs[count($recs) - 1];
            $m = $last['type'] === 'manual' ? $last['data'] : null;
            $b = $last['type'] === 'bp' ? $last['data'] : null;
            $mach = null;
            foreach ($midMachine as $mm) {
                if ($mm->HCDTTM <= $last['time']) { $mach = $mm; }
            }
            $result[] = [
                'time_slot' => $slotName, 'dispose_id' => $dispId,
                'measured_at' => $last['time'], 'start_time' => $check->start_time,
                'bp_systolic' => $m->BDPS ?? $b->systolic_blood_pressure ?? null,
                'bp_diastolic' => $m->BDPD ?? $b->diastolic_blood_pressure ?? null,
                'pulse' => $m->BDPL ?? $b->P ?? null,
                'qb' => $m->BLDF ?? $mach->BLDF ?? null,
                'vp' => $m->VEPS ?? $mach->VEPS ?? null,
                'tmp' => $m->TMP ?? $mach->TMP ?? null,
                'uf_done' => $m->UF ?? $mach->UF ?? null,
                'ufr' => $m->UFRA ?? $mach->UFRA ?? null,
                'heparin' => $m->HPMG ?? $mach->HPMG ?? null,
                'qd' => $m->DLFL ?? $mach->DLFL ?? null,
                'cond' => $m->CDCT ?? $mach->CDCT ?? null,
                'dial_temp' => $m->DLTP ?? $mach->DLTP ?? null,
                'notes' => $m->note ?? null,
                'source' => $m ? 'manual' : ($b ? 'machine_bp' : 'machine'),
            ];
        }

        // —— 透後 (dispose_id=3) ——
        $postBP = PatientMidBpPDatas::where('patient_check_id', $id)->where('display', 1)->where('dispose_id', 3)->first();
        $postManual = PatientDialysisManualRecord::where('patient_check_id', $id)->where('dispose_id', 3)->first();
        $result[] = [
            'time_slot' => 'post', 'dispose_id' => 3,
            'measured_at' => $postBP->time ?? $postManual->HCDTTM ?? null,
            'start_time' => $check->start_time,
            'bp_systolic' => $postManual->BDPS ?? $postBP->systolic_blood_pressure ?? null,
            'bp_diastolic' => $postManual->BDPD ?? $postBP->diastolic_blood_pressure ?? null,
            'pulse' => $postManual->BDPL ?? $postBP->P ?? null,
            'qb' => null, 'vp' => null, 'tmp' => null, 'uf_done' => null,
            'ufr' => null, 'heparin' => null, 'qd' => null, 'cond' => null, 'dial_temp' => null,
            'notes' => $postManual->note ?? null,
            'source' => $postManual ? 'manual' : ($postBP ? 'machine_bp' : null),
        ];

        return response()->json([
            'status' => 200,
            'start_time' => $check->start_time,
            'monitoring' => $result,
        ]);
    }

    // POST /dialysis/{id}/monitoring - 寫入 PatientDialysisManualRecord
    public function store($id, Request $request)
    {
        $validated = $request->validate([
            'dispose_id' => ['required', 'integer', function ($attr, $value, $fail) {
                if ($value == 1) { $fail('透前(dispose_id=1)不由此 endpoint 寫入，請使用 N-008'); }
                if ($value == 3) { $fail('透後(dispose_id=3)不由此 endpoint 寫入，請使用 N-017'); }
            }],
            'HCDTTM' => 'nullable|date_format:Y-m-d H:i:s',
            'BDPS' => 'nullable|integer|min:0|max:300',
            'BDPD' => 'nullable|integer|min:0|max:200',
            'BDPL' => 'nullable|integer|min:0|max:300',
            'BLDF' => 'nullable|integer|min:0|max:600',
            'VEPS' => 'nullable|integer|min:0|max:300',
            'TMP' => 'nullable|integer|min:0|max:300',
            'UF' => 'nullable|numeric|min:0|max:10',
            'UFRA' => 'nullable|numeric|min:0|max:5',
            'HPMG' => 'nullable|string',
            'DLFL' => 'nullable|integer|min:0|max:800',
            'CDCT' => 'nullable|numeric|min:0|max:20',
            'DLTP' => 'nullable|numeric|min:0|max:50',
            'note' => 'nullable|string',
        ]);

        $data = PatientDialysisManualRecord::updateOrCreate(
            ['patient_check_id' => $id, 'dispose_id' => $validated['dispose_id'], 'HCDTTM' => $validated['HCDTTM'] ?? now()],
            [
                                'BDPS' => $validated['BDPS'] ?? null,
                'BDPD' => $validated['BDPD'] ?? null,
                'BDPL' => $validated['BDPL'] ?? null,
                'BLDF' => $validated['BLDF'] ?? null,
                'VEPS' => $validated['VEPS'] ?? null,
                'TMP' => $validated['TMP'] ?? null,
                'UF' => $validated['UF'] ?? null,
                'UFRA' => $validated['UFRA'] ?? null,
                'HPMG' => $validated['HPMG'] ?? null,
                'DLFL' => $validated['DLFL'] ?? null,
                'CDCT' => $validated['CDCT'] ?? null,
                'DLTP' => $validated['DLTP'] ?? null,
                'note' => $validated['note'] ?? null,
            ]
        );

        return response()->json(['status' => 200, 'record' => $data]);
    }

    // POST /dialysis/{id}/monitoring/extra - 加測時段
    public function extra($id, Request $request)
    {
        $validated = $request->validate([
            'after_dispose_id' => 'required|integer|min:1|max:7',
        ]);

        // Create a new manual record as an extra slot
        $data = PatientDialysisManualRecord::create([
            'patient_check_id' => $id,
            'dispose_id' => $validated['after_dispose_id'] + 10, // extras in 11-17 range
            'HCDTTM' => now(),
            'note' => '⚡加測',
        ]);

        return response()->json(['status' => 200, 'extra_dispose_id' => $data->dispose_id]);
    }
}
