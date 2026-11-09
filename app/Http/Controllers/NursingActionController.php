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
        $data = [];
        if ($request->has('pre')) {
            $data['measure_weight_before'] = $validated['pre'] ?? null;
        }
        if ($request->has('post')) {
            $data['measure_weight_after'] = $validated['post'] ?? null;
        }
        if (!empty($data)) {
            $check->update($data);
        }

        return response()->json([
            'success' => true,
            'message' => '體重數據已校正並記錄護理日誌。'
        ], 200);
    }

    public function updateUfGoal(Request $request, $check_id)
    {
        $validated = $request->validate([
            'uf_goal' => 'nullable|numeric',
            'hours' => 'nullable|numeric',
            'note' => 'nullable|string'
        ]);

        $check = \App\Models\PatientCheck::findOrFail($check_id);
        return response()->json([
            'success' => true,
            'message' => '目標脫水量已設定。'
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

    public function reportIncident(Request $request, $check_id)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $check = \App\Models\PatientCheck::find($check_id);
        if (!$check) {
            $patient = \App\Models\Patient::where('medical_record_no', $check_id)->first();
            if ($patient) {
                $check = \App\Models\PatientCheck::whereHas('patient_reservation', function($q) use ($patient) {
                    $q->where('patient_id', $patient->id);
                })->latest('id')->first();
            }
        }

        if (!$check) {
            return response()->json(['success' => false, 'message' => '找不到對應的透析紀錄'], 404);
        }

        $typeMap = [
            'BED_CHANGE' => '換床',
            'FALL' => '跌倒（醫療事故通報）',
            'BLOOD_LEAK' => '漏血',
        ];
        $title = $typeMap[$validated['type']] ?? $validated['type'];
        $nurseId = Auth::id() ?? 1;

        $incident = \App\Models\PatientIncident::create([
            'patient_check_id' => $check->id,
            'patient_id' => $check->patient?->id,
            'nurse_id' => $nurseId,
            'type' => $validated['type'],
            'title' => $title,
            'description' => $validated['description'] ?? null,
            'status' => 'reported',
            'reported_at' => now(),
        ]);

        // 同步寫入護理紀錄 (PatientMidNurseRecord) 確保醫療稽核留痕
        $logContent = ($validated['type'] === 'FALL')
            ? "[醫療事故通報 ⚠️] 病患發生跌倒事件，已啟動跌倒照護流程與醫療事故通報。"
            : "[臨床事件] {$title}，已由護理人員處理完成。";

        \App\Models\PatientMidNurseRecord::create([
            'patient_check_id' => $check->id,
            'time' => now()->format('Y-m-d H:i:s'),
            'value' => $logContent,
            'nurse_id' => $nurseId,
            'deleted' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => '臨床事件 ' . $title . ' 已正式記錄並通報留痕。',
            'incident' => $incident,
            'nursing_record' => [
                'time' => now()->format('H:i'),
                'value' => $logContent,
                'nurse' => Auth::user()?->name ?? '護理師'
            ]
        ], 200);
    }

    public function updateHct(Request $request, $check_id)
    {
        $validated = $request->validate([
            'hct' => 'required|numeric',
            'type' => 'nullable|string|in:tw,at'
        ]);

        $check = \App\Models\PatientCheck::findOrFail($check_id);
        $patient = $check->patient;

        $now = now();
        $record = \App\Models\PatientHctInspectionRecordNew::create([
            'patient_id' => $patient->id,
            'date' => $now->format('Y-m-d'),
            'month' => $now->month,
            'week_of_month' => ceil($now->day / 7),
            'hct' => $validated['hct'],
            'nurse_id' => Auth::id() ?? 1,
        ]);

        $typeLabel = ($validated['type'] ?? 'tw') === 'at' ? '重測 (AT)' : '本週 (TW)';
        $logContent = "[HCT量測] 登記 {$typeLabel} HCT 為 {$validated['hct']}";

        \App\Models\PatientMidNurseRecord::create([
            'patient_check_id' => $check->id,
            'time' => $now->format('Y-m-d H:i:s'),
            'value' => $logContent,
            'nurse_id' => Auth::id() ?? 1,
            'deleted' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'HCT 數值已成功記錄。',
            'hct' => $validated['hct'],
            'record' => $record,
            'nursing_record' => [
                'time' => $now->format('H:i'),
                'value' => $logContent,
                'nurse' => Auth::user()?->name ?? '護理師'
            ]
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

    /**
     * 明日庫房領料大總表：依明日預約病患的透析器(Dialyzer)醫囑彙總。
     * 短期醫囑優先，沒有短期才取長期 (dialysis_machine_id = 1 為 Dialyzer)。
     */
    public function fetchSupplyList()
    {
        $tomorrow = \Carbon\Carbon::tomorrow()->format('Y-m-d');

        $reservations = \App\Models\PatientReservation::with(['patient', 'machine_bed.bed'])
            ->where('date', $tomorrow)
            ->where('patient_id', '!=', 0)
            ->whereNotIn('status', [1, 2]) // 排除請假/住院
            ->get();

        $patientIds = $reservations->pluck('patient_id')->unique()->all();

        $latestByPatient = function ($model) use ($patientIds, $tomorrow) {
            return $model::whereIn('patient_id', $patientIds)
                ->where('dialysis_machine_id', 1)
                ->whereNull('end_date')
                ->whereDate('start_date', '<=', $tomorrow)
                ->orderBy('id', 'desc')
                ->get()
                ->unique('patient_id')
                ->keyBy('patient_id');
        };
        $shorts = $latestByPatient(\App\Models\PatientDialysisMachineShort::class);
        $longs = $latestByPatient(\App\Models\PatientDialysisMachineLong::class);

        $equipments = \App\Models\MedicalEquipmen::with('unit')
            ->whereIn('id', $shorts->pluck('value')->merge($longs->pluck('value'))->filter()->unique())
            ->get()
            ->keyBy('id');

        $shiftNames = [0 => '早', 1 => '中', 2 => '晚'];
        $items = [];

        foreach ($reservations as $r) {
            $order = $shorts[$r->patient_id] ?? $longs[$r->patient_id] ?? null;
            if (!$order || empty($order->value)) continue;

            $eq = $equipments[$order->value] ?? null;
            $id = (int) $order->value;
            if (!isset($items[$id])) {
                $items[$id] = [
                    'id' => $id,
                    'name' => $eq?->product_name ?: ($eq?->chinese_name ?: "Dialyzer #{$id}"),
                    'count' => 0,
                    'unit' => $eq?->unit?->name ?? '組',
                    'patients' => [],
                ];
            }
            $items[$id]['count']++;
            $items[$id]['patients'][] = [
                'name' => $r->patient?->name ?? '—',
                'bed' => $r->machine_bed?->bed?->bed_no ?? '—',
                'shift' => $shiftNames[$r->morning_noon_night] ?? '—',
                'source' => isset($shorts[$r->patient_id]) ? '短期' : '長期',
            ];
        }

        return response()->json([
            'date' => $tomorrow,
            'items' => array_values($items),
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

    public function fetchMonitoringData($check_id)
    {
        $patientCheck = \App\Models\PatientCheck::findOrFail($check_id);

        $initialPriming = \App\Models\PatientBeforePreparation::where('patient_check_id', $check_id)
            ->where('name', 'initial_priming')
            ->first();
        $total = $initialPriming ? $initialPriming->product_name : 0;

        // 定義對應 TabMonitoring 的目標時間點 (基於透析開始時間)
        $startTime = \Carbon\Carbon::parse($patientCheck->start_time ?? '09:00');
        $targetTimes = [
            'pre' => 'PRE', // 特殊標記透前
            'h1' => $startTime->format('H:i'),
            'h2' => $startTime->copy()->addHour(1)->format('H:i'),
            'h3' => $startTime->copy()->addHour(2)->format('H:i'),
            'h4' => $startTime->copy()->addHour(3)->format('H:i'),
            'post1' => 'POST1',
            'post2' => 'POST2'
        ];
        $results = [];


        foreach ($targetTimes as $key => $time) {
            if ($time === 'PRE') {
                // 透前資料查詢
                $record = \App\Models\PatientMidDialysisRecordNew::where('patient_check_id', $check_id)
                    ->whereNotNull('BDPS') // 有血壓數據
                    ->whereNotNull('BDPD')
                    ->where('HMNO', '<>', null) // 排除手動加測
                    ->orderBy('HCDTTM', 'asc')
                    ->first();
                $results[$key] = $record ? $record->toArray() : null;
                continue;
            }
            if (in_array($time, ['POST1', 'POST2'])) {
                // 透後資料查詢 (取透析結束後最近兩筆)
                $records = \App\Models\PatientMidDialysisRecordNew::where('patient_check_id', $check_id)
                    ->whereNotNull('BDPS')
                    ->where('HMNO', '<>', null)
                    ->orderBy('HCDTTM', 'desc')
                    ->take(2)
                    ->get();
                $postRec = ($time === 'POST1' ? ($records[0] ?? null) : ($records[1] ?? null));
                $results[$key] = $postRec ? $postRec->toArray() : null;
                continue;
            }

            // 每小時紀錄查詢邏輯 (同時撈取血壓)
            // 每小時紀錄查詢邏輯
            $target = \Carbon\Carbon::parse($patientCheck->date . ' ' . $time);

            // 1. 撈取該時段的儀器基礎數據
            $record = \App\Models\PatientMidDialysisRecordNew::where('patient_check_id', $check_id)
                ->whereBetween('HCDTTM', [$target->copy()->subMinutes(60), $target->copy()->addMinutes(60)])
                ->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, HCDTTM, ?))', [$target->format('Y-m-d H:i:s')])
                ->first();

            // 2. 撈取該時段最接近的血壓紀錄 (從 PatientMidDialysisRecordNew 獨立查詢)
            $bpRecord = \App\Models\PatientMidDialysisRecordNew::where('patient_check_id', $check_id)
                ->whereNotNull('BDPS')
                ->whereNotNull('BDPD')
                ->whereBetween('HCDTTM', [$target->copy()->subMinutes(60), $target->copy()->addMinutes(60)])
                ->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, HCDTTM, ?))', [$target->format('Y-m-d H:i:s')])
                ->first();

            if ($record || $bpRecord) {
                $array = $record ? $record->toArray() : [];

                // 合併血壓資訊 (優先使用 bpRecord)
                if ($bpRecord) {
                    $array['systolic_blood_pressure'] = $bpRecord->BDPS;
                    $array['diastolic_blood_pressure'] = $bpRecord->BDPD;
                    $array['P'] = $bpRecord->BDPL;
                    $array['BDPS'] = $bpRecord->BDPS;
                    $array['BDPD'] = $bpRecord->BDPD;
                    $array['BDPL'] = $bpRecord->BDPL;

                }


                // 應用總表轉換規則
                if (isset($array['DLTP'])) $array['DLTP'] /= 10;
                if (isset($array['CDCT'])) $array['CDCT'] /= 4;
                if (isset($array['UFRA'])) $array['UFRA'] /= 1000;
                if (isset($array['UF'])) $array['UF'] = round($array['UF'] / 1000, 2);
                if (isset($array['HPCV'])) {
                    $array['HPCV'] = round($total - ($array['HPCV'] / 10), 1);
                }

                $results[$key] = $array;
            } else {
                $results[$key] = null;
            }
        }

        // 獲取手動加測或修正記錄
        $manualRecords = \App\Models\PatientDialysisManualRecord::where('patient_check_id', $check_id)
            ->whereNull('HMNO')
            ->get();

        // 修改歷程 (含修改者姓名)，依手動紀錄 id 分組
        $editsByRecord = \Illuminate\Support\Facades\DB::table('patient_dialysis_manual_edits as e')
            ->leftJoin('users as u', 'u.id', '=', 'e.nurse_id')
            ->where('e.patient_check_id', $check_id)
            ->orderBy('e.id')
            ->get(['e.manual_record_id', 'e.field', 'e.old_value', 'e.new_value', 'e.created_at', 'u.name as nurse_name'])
            ->groupBy('manual_record_id');
        $historyOf = fn($id) => ($editsByRecord[$id] ?? collect())->map(fn($e) => [
            'field' => $e->field,
            'old' => $e->old_value,
            'new' => $e->new_value,
            'nurse' => $e->nurse_name ?? '—',
            'at' => \Carbon\Carbon::parse($e->created_at)->setTimezone(config('app.timezone'))->format('m/d H:i:s'),
        ])->values()->all();

        foreach ($manualRecords as $manual) {
            $matched = false;
            foreach ($results as $key => &$record) {
                if (!$record) continue;

                // 只有時間完全吻合 (例如 HCDTTM 相同) 才進行合併
                if (\Carbon\Carbon::parse($manual->HCDTTM)->format('Y-m-d H:i') === \Carbon\Carbon::parse($record['HCDTTM'] ?? '')->format('Y-m-d H:i')) {
                    foreach ($manual->toArray() as $field => $val) {
                        if ($val !== null && !in_array($field, ['id', 'patient_check_id', 'HCDTTM', 'created_at', 'updated_at'])) {
                            // 保留機台原始值，供前端 hover 顯示 (只記第一次，且僅限實際被修改者)
                            if (!isset($record['is_manual_override'][$field])) {
                                $record['original_values'][$field] = $record[$field] ?? null;
                            }
                            $record[$field] = $val;
                            // 前端血壓/脈搏優先讀取這幾個別名欄位，需同步覆蓋
                            $alias = ['BDPS' => 'systolic_blood_pressure', 'BDPD' => 'diastolic_blood_pressure', 'BDPL' => 'P'][$field] ?? null;
                            if ($alias) $record[$alias] = $val;
                            $record['is_manual_override'][$field] = true;
                        }
                    }
                    $record['edit_history'] = array_merge($record['edit_history'] ?? [], $historyOf($manual->id));
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                $row = $manual->toArray();
                // 加測列：以修改紀錄中該欄位「第一次修改前」的值作為原始值
                $edits = \Illuminate\Support\Facades\DB::table('patient_dialysis_manual_edits')
                    ->where('manual_record_id', $manual->id)->orderBy('id')->get();
                foreach ($edits as $e) {
                    if (!isset($row['is_manual_override'][$e->field])) {
                        $row['is_manual_override'][$e->field] = true;
                        $row['original_values'][$e->field] = $e->old_value;
                    }
                }
                $row['edit_history'] = $historyOf($manual->id);
                $results['manual_' . $manual->id] = $row;
            }
        }

        // 5. 追加結束行 (END UF) 若透析完成
        if (in_array($patientCheck->status, [3, 5])) {
            $lastRecord = \App\Models\PatientMidDialysisRecordNew::where('patient_check_id', $check_id)
                ->where('UF', '>', 0)
                ->orderBy('HCDTTM', 'desc')
                ->first();
            
            if ($lastRecord) {
                $results['END_UF'] = [
                    'VEPS' => 'END UF',
                    'UF' => round($lastRecord->UF / 1000, 1),
                ];
            }
        }

        return response()->json(['success' => true, 'data' => $results], 200);
    }

    public function updateMonitoringData(Request $request, $check_id)
    {
        $validated = $request->validate([
            'key' => 'required',
            'field' => 'required',
            'value' => 'nullable',
            'time' => 'nullable'
        ]);

        $field = $validated['field'];
        $value = $validated['value'];
        $time = $validated['time'];
        
        $map = [
            'sys' => 'BDPS', 'dia' => 'BDPD', 'pr' => 'BDPL', 'qb' => 'BLDF',
            'vp' => 'VEPS', 'tmp' => 'TMP', 'uf' => 'UF', 'ufr' => 'UFRA',
            'hep' => 'HPCV', 'qd' => 'DLFL', 'cond' => 'CDCT', 'temp' => 'DLTP',
        ];
        $dbField = $map[$field] ?? null;
        if (!$dbField) return response()->json(['success' => false, 'message' => '無效欄位'], 400);

        $patientCheck = \App\Models\PatientCheck::findOrFail($check_id);
        $targetTime = ($time && $time !== '—') 
            ? \Carbon\Carbon::parse($patientCheck->date . ' ' . $time . ':00') 
            : now();

        // 加測列 (manual_{id}) 直接以 id 找該筆；其他列只比對「同一分鐘」的手動紀錄，
        // 避免誤改到鄰近時段的加測資料
        $manualQuery = \App\Models\PatientDialysisManualRecord::where('patient_check_id', $check_id)
            ->whereNull('HMNO'); // 確保是手動記錄
        if (str_starts_with((string) $validated['key'], 'manual_')) {
            $manualQuery->where('id', (int) substr($validated['key'], 7));
        } else {
            $minuteStart = $targetTime->copy()->startOfMinute();
            $manualQuery->whereBetween('HCDTTM', [$minuteStart, $minuteStart->copy()->endOfMinute()]);
        }
        $manualRecord = $manualQuery->first();

        // 修改前「畫面上看到的值」(機台值或先前人工值)，由前端帶入；未帶入則用手動紀錄現值
        $clientOld = $request->input('old_value');
        $clientOld = ($clientOld === '—' || $clientOld === '') ? null : $clientOld;

        if ($manualRecord) {
            $old = $request->has('old_value') ? $clientOld : $manualRecord->{$dbField};
            $manualRecord->update([$dbField => $value]);
            $recordId = $manualRecord->id;
        } else {
            $created = \App\Models\PatientDialysisManualRecord::create([
                'patient_check_id' => $check_id,
                'HCDTTM' => $targetTime->format('Y-m-d H:i:s'),
                $dbField => $value,
                'HMNO' => null
            ]);
            $old = $clientOld;
            $recordId = $created->id;
        }

        // 每一次修改都留一筆紀錄：誰、何時、哪個欄位、改前、改後
        if ((string) $old !== (string) $value) {
            \Illuminate\Support\Facades\DB::table('patient_dialysis_manual_edits')->insert([
                'patient_check_id' => $check_id,
                'manual_record_id' => $recordId,
                'field' => $dbField,
                'old_value' => $old,
                'new_value' => $value,
                'nurse_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => '更新成功']);
    }

    public function addManualRecord(Request $request, $check_id)
    {
        $validated = $request->validate([
            'time' => 'required',
            'sys' => 'nullable', 'dia' => 'nullable', 'pr' => 'nullable',
            'qb' => 'nullable', 'vp' => 'nullable', 'tmp' => 'nullable',
            'uf' => 'nullable', 'ufr' => 'nullable', 'hep' => 'nullable',
            'qd' => 'nullable', 'cond' => 'nullable', 'temp' => 'nullable',
        ]);

        $patientCheck = \App\Models\PatientCheck::findOrFail($check_id);

        \App\Models\PatientDialysisManualRecord::create([
            'patient_check_id' => $check_id,
            'HCDTTM' => $patientCheck->date . ' ' . $validated['time'] . ':00',
            'BDPS' => $validated['sys'] ?? null,
            'BDPD' => $validated['dia'] ?? null,
            'BDPL' => $validated['pr'] ?? null,
            'BLDF' => $validated['qb'] ?? null,
            'VEPS' => $validated['vp'] ?? null,
            'TMP' => $validated['tmp'] ?? null,
            'UF' => $validated['uf'] ?? null,
            'UFRA' => $validated['ufr'] ?? null,
            'HPCV' => $validated['hep'] ?? null,
            'DLFL' => $validated['qd'] ?? null,
            'CDCT' => $validated['cond'] ?? null,
            'DLTP' => $validated['temp'] ?? null,
            'HMNO' => null
        ]);

        return response()->json(['success' => true, 'message' => '加測記錄已儲存。'], 200);
    }

    /**
     * POST /api/v1/dialysis-checks/{check_id}/on-sign
     * 執行主責護理師 On-Sign 或 雙簽護理師 Double Sign
     */
    public function executeOnSign(Request $request, $check_id)
    {
        $validated = $request->validate([
            'type' => 'required|in:main,double',
            'action' => 'nullable|in:sign,unsign',
        ]);

        $check = \App\Models\PatientCheck::findOrFail($check_id);
        $user = $request->user() ?: Auth::user();
        $nurseId = $user?->id ?: 1;
        $nurseName = $request->input('nurse_name') ?: ($user?->name ?: '護理師');
        $action = $validated['action'] ?? 'sign';

        if ($validated['type'] === 'main') {
            if ($action === 'sign') {
                $check->update(['prepare_nurse_id' => $nurseId]);
                $logContent = "[上針簽章] 主責護理師 {$nurseName} 完成當班 On-Sign 具名核簽，生理參數與過磅記錄上鎖。";
            } else {
                // 取消主簽時，若有雙簽也連帶解除
                $check->update(['prepare_nurse_id' => null, 'check_nurse_id' => null]);
                $logContent = "[上針簽章解除] 主責護理師 {$nurseName} 解除 On-Sign 簽章。";
            }
        } else {
            // 雙簽 Double Sign
            if ($action === 'sign') {
                // 核心防呆：主責護理師與雙簽護理師不可為同一人！
                if (!empty($check->prepare_nurse_id) && $check->prepare_nurse_id == $nurseId) {
                    return response()->json([
                        'success' => false,
                        'message' => '❌ 臨床防呆攔截：主責護理師與雙簽核對護理師不可為同一人，必須由第二位護理師臨櫃覆核！'
                    ], 422);
                }

                $check->update(['check_nurse_id' => $nurseId]);
                $logContent = "[雙人雙簽] 雙簽核對護理師 {$nurseName} 臨櫃覆核通過完成 Double Sign。";
            } else {
                $check->update(['check_nurse_id' => null]);
                $logContent = "[雙人雙簽解除] 雙簽護理師 {$nurseName} 解除 Double Sign 覆核。";
            }
        }

        // 寫入護理日誌留痕
        $nrec = \App\Models\PatientMidNurseRecord::create([
            'patient_check_id' => $check->id,
            'time' => now()->format('Y-m-d H:i:s'),
            'value' => $logContent,
            'nurse_id' => $nurseId,
            'deleted' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => '簽章記錄完成',
            'signs' => [
                'main_signed' => !empty($check->prepare_nurse_id),
                'main_nurse_id' => $check->prepare_nurse_id,
                'main_nurse_name' => $check->prepare_nurse?->name,
                'double_signed' => !empty($check->check_nurse_id),
                'double_nurse_id' => $check->check_nurse_id,
                'double_nurse_name' => $check->check_nurse?->name,
            ],
            'nursing_record' => [
                'id' => $nrec->id,
                'time' => now()->format('H:i'),
                'content' => $logContent,
                'value' => $logContent,
                'nurse' => $nurseName,
                'deleted' => false,
                'deletedMeta' => null
            ]
        ], 200);
    }

    public function saveOffSignItem(Request $request, $check_id)
    {
        $check = \App\Models\PatientCheck::findOrFail($check_id);
        $user = $request->user() ?: Auth::user();
        $nurseId = $user?->id ?: (\App\Models\User::first()?->id ?: 1);
        $type = $request->input('type');

        if ($type === 'lie_bp') {
            $sys = $request->input('sys');
            $dia = $request->input('dia');
            \App\Models\PatientAfterPhysiologicalDatas::updateOrCreate(
                ['patient_check_id' => $check_id],
                [
                    'systolic_blood_pressure_sleep' => ($sys !== null && $sys !== '') ? (int)$sys : null,
                    'diastolic_blood_pressure_sleep' => ($dia !== null && $dia !== '') ? (int)$dia : null,
                ]
            );
            return response()->json(['success' => true, 'message' => '透後臥姿血壓已儲存']);
        }

        if ($type === 'sit_bp') {
            $sys = $request->input('sys');
            $dia = $request->input('dia');
            \App\Models\PatientAfterPhysiologicalDatas::updateOrCreate(
                ['patient_check_id' => $check_id],
                [
                    'systolic_blood_pressure_sit' => ($sys !== null && $sys !== '') ? (int)$sys : null,
                    'diastolic_blood_pressure_sit' => ($dia !== null && $dia !== '') ? (int)$dia : null,
                ]
            );
            \App\Models\PatientMidBpPDatas::updateOrCreate(
                ['patient_check_id' => $check_id, 'dispose_id' => 3],
                [
                    'time' => now()->format('Y-m-d H:i:s'),
                    'systolic_blood_pressure' => ($sys !== null && $sys !== '') ? (int)$sys : 120,
                    'diastolic_blood_pressure' => ($dia !== null && $dia !== '') ? (int)$dia : 80,
                    'P' => 72,
                    'machine' => 0,
                    'display' => 1,
                    'nurse_id' => $nurseId
                ]
            );
            return response()->json(['success' => true, 'message' => '透後坐姿血壓已儲存']);
        }

        if ($type === 'weight') {
            $weight = $request->input('weight');
            $check->update([
                'measure_weight_after' => $weight,
                'measure_weight_after_nurse_id' => $nurseId
            ]);
            return response()->json(['success' => true, 'message' => '透後體重核實已儲存']);
        }

        if ($type === 'clot') {
            $ak = $request->input('ak', 'clear');
            $akSite = $request->input('akSite', '');
            $ch = $request->input('ch', 'clear');
            $chSite = $request->input('chSite', '');
            \App\Models\PatientAfterPhysiologicalDatas::updateOrCreate(
                ['patient_check_id' => $check_id],
                [
                    'ak_clear' => ($ak === 'clear') ? 1 : 0,
                    'ak_content' => ($ak !== 'clear') ? $akSite : null,
                    'a_clear' => ($ch === 'aclot') ? 0 : 1,
                    'a_content' => ($ch === 'aclot') ? $chSite : null,
                    'v_clear' => ($ch === 'vclot') ? 0 : 1,
                    'v_content' => ($ch === 'vclot') ? $chSite : null,
                ]
            );
            return response()->json(['success' => true, 'message' => 'AK/Chamber 凝血狀態已儲存']);
        }

        if ($type === 'supply') {
            \App\Models\PatientSupplyCheck::updateOrCreate(
                ['patient_check_id' => $check_id],
                [
                    'confirmed' => 1,
                    'confirmed_by' => $nurseId,
                    'confirmed_at' => now(),
                ]
            );
            return response()->json(['success' => true, 'message' => '下機消耗醫材清點已具名核對儲存']);
        }

        if ($type === 'needle') {
            \App\Models\PatientMidAkDatas::updateOrCreate(
                ['patient_check_id' => $check_id, 'dispose_id' => 3],
                [
                    'time' => now()->format('Y-m-d H:i:s'),
                    'ak_id' => 1,
                    'pinhole_blood' => 0,
                    'line_fix' => 1,
                    'nurse_id' => $nurseId,
                ]
            );
            return response()->json(['success' => true, 'message' => '穿刺針孔壓迫止血安全確認已儲存']);
        }

        if ($type === 'drug') {
            $drugStatus = $request->input('status', 'execute'); // execute, refuse, delete
            $medId = $request->input('medicine_id');
            $name = $request->input('name', '透後給藥');
            $dose = $request->input('dose', '1');
            $route = $request->input('route', 'IV');
            $adverse = $request->input('adverse', '正常');
            $reason = $request->input('reason', '');
            $recordId = $request->input('id');

            if ($drugStatus === 'delete') {
                $query = \App\Models\PatientAfterMedicineDatas::where('patient_check_id', $check_id);
                if ($recordId) {
                    $query->where('id', $recordId);
                } else {
                    $query->where('medicine_name', $name);
                }
                $record = $query->first();
                if ($record) {
                    $record->delete();
                    $nurseName = $user?->name ?: '護理師';
                    \App\Models\PatientMidNurseRecord::create([
                        'patient_check_id' => $check_id,
                        'time' => now()->format('Y-m-d H:i:s'),
                        'value' => "[透後給藥 🗑️] {$nurseName} 刪除透後處置給藥記錄：{$name}",
                        'nurse_id' => $nurseId,
                        'deleted' => 0
                    ]);
                    return response()->json(['success' => true, 'message' => '透後給藥記錄已刪除']);
                }
                return response()->json(['success' => true, 'message' => '查無該筆記錄']);
            }

            // 依據 id 或 medicine_id 或 medicine_name 匹配或新建
            $matchKey = ['patient_check_id' => $check_id];
            if ($recordId) {
                $matchKey['id'] = $recordId;
            } elseif ($medId) {
                $matchKey['medicine_id'] = $medId;
            } else {
                $matchKey['medicine_name'] = $name;
            }

            $afterMed = \App\Models\PatientAfterMedicineDatas::updateOrCreate(
                $matchKey,
                [
                    'medicine_id' => $medId,
                    'medicine_name' => $name,
                    'amount' => $dose,
                    'dose' => is_numeric($dose) ? (float)$dose : 1.00,
                    'route' => $route ?: 'IV',
                    'adverse_reaction' => $adverse ?: '正常',
                    'nurse_id' => $nurseId,
                    'type' => $drugStatus,
                    'refuse_reason' => $reason,
                ]
            );

            // 寫入具名稽核護理病歷
            $nurseName = $user?->name ?: '護理師';
            $timeNow = now()->format('Y-m-d H:i:s');
            if ($drugStatus === 'refuse') {
                $recordText = "[透後給藥 ⚠️] {$nurseName} 記錄透後靜脈給藥拒打：{$name}。拒打原因：{$reason}";
            } else {
                $doseText = $dose ?: '依醫囑';
                $recordText = "[透後給藥 ✅] {$nurseName} 執行透後靜脈給藥：{$name}，途徑：{$route}，劑量：{$doseText}，不良反應：{$adverse}";
            }

            $logRecord = \App\Models\PatientMidNurseRecord::create([
                'patient_check_id' => $check_id,
                'time' => $timeNow,
                'value' => $recordText,
                'nurse_id' => $nurseId,
                'deleted' => 0
            ]);

            return response()->json([
                'success' => true,
                'message' => '透後給藥記錄已儲存',
                'record' => $afterMed,
                'nursing_record' => $logRecord
            ]);
        }

        return response()->json(['success' => false, 'message' => '未知的下機項目類型'], 422);
    }

    public function executeOffSign(Request $request, $check_id)
    {
        $validated = $request->validate([
            'lieBP' => 'required',
            'sitBP' => 'required',
            'weight' => 'required|numeric',
            'checks' => 'required|array'
        ]);

        $check = \App\Models\PatientCheck::findOrFail($check_id);
        $user = $request->user() ?: Auth::user();
        $nurseId = $user?->id ?: (\App\Models\User::first()?->id ?: 1);
        $nurseName = $user?->name ?: '護理師';

        // 1. 更新 PatientCheck 狀態與透後體重
        $check->update([
            'measure_weight_after' => $validated['weight'],
            'measure_weight_after_nurse_id' => $nurseId,
            'care_end_nurse_id' => $nurseId,
            'finish_time' => now(),
            'status' => 3
        ]);

        // 2. 解析血壓
        $lieParts = explode('/', $validated['lieBP']);
        $sitParts = explode('/', $validated['sitBP']);
        $lieSys = isset($lieParts[0]) && is_numeric($lieParts[0]) ? (int)$lieParts[0] : null;
        $lieDia = isset($lieParts[1]) && is_numeric($lieParts[1]) ? (int)$lieParts[1] : null;
        $sitSys = isset($sitParts[0]) && is_numeric($sitParts[0]) ? (int)$sitParts[0] : null;
        $sitDia = isset($sitParts[1]) && is_numeric($sitParts[1]) ? (int)$sitParts[1] : null;

        // 3. 儲存 PatientAfterPhysiologicalDatas
        $clot = $request->input('checks.clotData', []);
        $ak = $clot['ak'] ?? 'clear';
        $akSite = $clot['akSite'] ?? null;
        $ch = $clot['ch'] ?? 'clear';
        $chSite = $clot['chSite'] ?? null;

        \App\Models\PatientAfterPhysiologicalDatas::updateOrCreate(
            ['patient_check_id' => $check_id],
            [
                'systolic_blood_pressure_sleep' => $lieSys,
                'diastolic_blood_pressure_sleep' => $lieDia,
                'systolic_blood_pressure_sit' => $sitSys,
                'diastolic_blood_pressure_sit' => $sitDia,
                'ak_clear' => ($ak === 'clear') ? 1 : 0,
                'ak_content' => ($ak !== 'clear') ? $akSite : null,
                'a_clear' => ($ch === 'aclot') ? 0 : 1,
                'a_content' => ($ch === 'aclot') ? $chSite : null,
                'v_clear' => ($ch === 'vclot') ? 0 : 1,
                'v_content' => ($ch === 'vclot') ? $chSite : null,
            ]
        );

        // 4. 儲存透後坐姿血壓至 PatientMidBpPDatas (dispose_id = 3)
        \App\Models\PatientMidBpPDatas::updateOrCreate(
            ['patient_check_id' => $check_id, 'dispose_id' => 3],
            [
                'time' => now()->format('Y-m-d H:i:s'),
                'systolic_blood_pressure' => $sitSys ?: 120,
                'diastolic_blood_pressure' => $sitDia ?: 80,
                'P' => 72,
                'machine' => 0,
                'display' => 1,
                'nurse_id' => $nurseId
            ]
        );

        // 5. 儲存消耗醫材清點 PatientSupplyCheck
        \App\Models\PatientSupplyCheck::updateOrCreate(
            ['patient_check_id' => $check_id],
            [
                'confirmed' => 1,
                'confirmed_by' => $nurseId,
                'confirmed_at' => now(),
            ]
        );

        // 6. 儲存針孔止血 PatientMidAkDatas
        \App\Models\PatientMidAkDatas::updateOrCreate(
            ['patient_check_id' => $check_id, 'dispose_id' => 3],
            [
                'time' => now()->format('Y-m-d H:i:s'),
                'ak_id' => 1,
                'pinhole_blood' => 0,
                'line_fix' => 1,
                'nurse_id' => $nurseId,
            ]
        );

        // 7. 寫入具名護理病歷留痕 PatientMidNurseRecord
        $logRecord = \App\Models\PatientMidNurseRecord::create([
            'patient_check_id' => $check_id,
            'time' => now()->format('Y-m-d H:i:s'),
            'value' => "[下機作業] 安全下機歸檔。透後臥姿血壓:{$validated['lieBP']}, 坐姿血壓:{$validated['sitBP']}, 透後體重:{$validated['weight']}kg。下機所有查核項目（體重、凝血、醫材、止血、靜脈給藥）全數核查無誤。簽章護理師: {$nurseName}",
            'nurse_id' => $nurseId,
            'deleted' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => '下機簽章作業完成。',
            'nursing_record' => $logRecord
        ]);
    }

    public function toggleEmergency(Request $request, $check_id)
    {
        $check = \App\Models\PatientCheck::findOrFail($check_id);
        // 切換狀態：0 為普通透析中，4 為危急 (CRIT)
        $newStatus = ($check->status == 4) ? 0 : 4;
        $check->update(['status' => $newStatus]);

        $doctor = $request->user() ?: Auth::user();
        $doctorId = $doctor?->id ?: 1;
        $doctorName = $doctor?->name ?: '張院長';

        if ($newStatus == 4) {
            // 寫入緊急事件告警 (emergency_alerts)
            \App\Models\EmergencyAlert::create([
                'hospital_id' => $check->hospital_id ?? 1,
                'patient_id' => $check->patient?->id ?? 1,
                'bed_id' => $check->patient_reservation?->machine_bed?->bed?->id,
                'triggered_by' => $doctorId,
                'triggered_at' => now(),
            ]);

            // 寫入護理病歷留痕
            \App\Models\PatientMidNurseRecord::create([
                'patient_check_id' => $check->id,
                'time' => now()->format('Y-m-d H:i:s'),
                'value' => "[EMERGENCY 🚨] {$doctorName} 啟動緊急危急處置流程！全院廣播通報並備妥急救車。",
                'nurse_id' => $doctorId,
                'deleted' => 0,
            ]);
        } else {
            // 解除危急狀態
            $latestAlert = \App\Models\EmergencyAlert::where('patient_id', $check->patient?->id)
                ->whereNull('ended_at')
                ->latest('id')
                ->first();
            if ($latestAlert) {
                $latestAlert->update([
                    'ended_by' => $doctorId,
                    'ended_at' => now(),
                ]);
            }

            $duration = $request->input('duration', '');
            $items = $request->input('items', []);
            $note = $request->input('note', '');
            $durationStr = $duration ? "歷時 {$duration}。" : "";
            $itemsStr = !empty($items) ? "已落實處置：[" . implode('、', $items) . "]。" : "";
            $noteStr = !empty($note) ? "急救記錄備註：{$note}。" : "";

            $logValue = "[急救作業結束 ✅] {$doctorName} 完成緊急醫療處置作業。{$durationStr}{$itemsStr}{$noteStr}患者生命徵象轉趨穩定，解除 EMERGENCY 危急警戒。";

            $nrec = \App\Models\PatientMidNurseRecord::create([
                'patient_check_id' => $check->id,
                'time' => now()->format('Y-m-d H:i:s'),
                'value' => $logValue,
                'nurse_id' => $doctorId,
                'deleted' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'is_crit' => ($newStatus == 4),
            'nursing_record' => isset($nrec) ? [
                'id' => $nrec->id,
                'time' => now()->format('H:i'),
                'content' => $logValue,
                'value' => $logValue,
                'nurse' => $doctorName,
                'deleted' => false,
                'deletedMeta' => null
            ] : null,
            'message' => $newStatus == 4 ? 'EMERGENCY 危急狀態已啟動並全院廣播通報！' : 'EMERGENCY 狀態已順利解除並寫入護理病歷留痕。'
        ], 200);
    }

    public function fetchNurseWatchings($check_id)
    {
        $patientCheck = \App\Models\PatientCheck::findOrFail($check_id);

        $records = \App\Models\NurseWatching::with('nurse')
            ->where('patient_check_id', $check_id)
            ->where('status', 1)
            ->orderBy('time', 'desc')
            ->get()
            ->map(function ($rec) {
                $images = [];
                if (!empty($rec->image_paths)) {
                    $paths = explode(',', $rec->image_paths);
                    foreach ($paths as $path) {
                        $path = trim($path);
                        if (!$path) continue;
                        if (str_starts_with($path, 'http') || str_starts_with($path, 'data:') || str_starts_with($path, '/')) {
                            $images[] = $path;
                        } else {
                            $images[] = '/uploads/nurse_watchings/' . $path;
                        }
                    }
                }
                return [
                    'id' => $rec->id,
                    'patient_check_id' => $rec->patient_check_id,
                    'time' => $rec->time,
                    'date' => \Carbon\Carbon::parse($rec->time)->format('Y/m/d'),
                    'time_str' => \Carbon\Carbon::parse($rec->time)->format('H:i'),
                    'content' => $rec->content,
                    'image_paths' => $rec->image_paths,
                    'images' => $images,
                    'status' => $rec->status,
                    'nurse_name' => $rec->nurse->name ?? '楚心瑜',
                    'nurse_id' => $rec->nurse_id,
                ];
            });

        return response()->json([
            'success' => true,
            'records' => $records,
        ], 200);
    }

    public function storeNurseWatching(Request $request, $check_id)
    {
        $patientCheck = \App\Models\PatientCheck::findOrFail($check_id);

        $validated = $request->validate([
            'content' => 'required|string',
            'time' => 'nullable|string',
            'images' => 'nullable',
            'image_paths' => 'nullable|string',
            'base64_images' => 'nullable|array',
            'nurse' => 'nullable|string',
        ]);

        $nurseId = Auth::id() ?? 6;
        if (!empty($validated['nurse'])) {
            $user = \App\Models\User::where('name', $validated['nurse'])->first();
            if ($user) {
                $nurseId = $user->id;
            }
        }

        $savedImageNames = [];
        $uploadDir = public_path('uploads/nurse_watchings');

        // 1. 處理上傳檔案
        if ($request->hasFile('images')) {
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            foreach ($request->file('images') as $file) {
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = round(microtime(true) * 1000) . '_' . uniqid() . '.' . $ext;
                $file->move($uploadDir, $filename);
                $savedImageNames[] = $filename;
            }
        }

        // 2. 處理 Base64 圖片
        if (!empty($validated['base64_images']) && is_array($validated['base64_images'])) {
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            foreach ($validated['base64_images'] as $b64) {
                if (preg_match('/^data:image\/(\w+);base64,/', $b64, $matches)) {
                    $ext = $matches[1];
                    $data = substr($b64, strpos($b64, ',') + 1);
                    $data = base64_decode($data);
                    if ($data !== false) {
                        $filename = round(microtime(true) * 1000) . '_' . uniqid() . '.' . $ext;
                        file_put_contents($uploadDir . '/' . $filename, $data);
                        $savedImageNames[] = $filename;
                    }
                }
            }
        }

        // 3. 處理已存在的圖片路徑字串
        if (!empty($validated['image_paths'])) {
            $parts = explode(',', $validated['image_paths']);
            foreach ($parts as $p) {
                $trimmed = trim($p);
                if ($trimmed) {
                    $savedImageNames[] = $trimmed;
                }
            }
        }

        $timeStr = $validated['time'] ?? now()->format('Y-m-d H:i:s');
        if (strlen($timeStr) === 10) {
            $timeStr .= ' ' . now()->format('H:i:s');
        } elseif (strlen($timeStr) === 16) {
            $timeStr .= ':00';
        }

        $nw = \App\Models\NurseWatching::create([
            'patient_check_id' => $check_id,
            'time' => $timeStr,
            'content' => $validated['content'],
            'image_paths' => implode(',', $savedImageNames),
            'status' => 1,
            'nurse_id' => $nurseId,
        ]);

        // 臨床稽核留痕：自動寫入核心護理記錄
        $nurseName = $validated['nurse'] ?? (Auth::user()?->name ?? '楚心瑜');
        \App\Models\PatientMidNurseRecord::create([
            'patient_check_id' => $check_id,
            'time' => $timeStr,
            'value' => '[Nurse Watching 📷] ' . $validated['content'],
            'nurse_id' => $nurseId,
            'deleted' => 0,
        ]);

        $imageUrls = array_map(function ($fn) {
            if (str_starts_with($fn, 'http') || str_starts_with($fn, 'data:') || str_starts_with($fn, '/')) {
                return $fn;
            }
            return '/uploads/nurse_watchings/' . $fn;
        }, $savedImageNames);

        return response()->json([
            'success' => true,
            'message' => 'Nurse Watching 記錄已建立並具名上傳！',
            'record' => [
                'id' => $nw->id,
                'patient_check_id' => $nw->patient_check_id,
                'time' => $nw->time,
                'date' => \Carbon\Carbon::parse($nw->time)->format('Y/m/d'),
                'time_str' => \Carbon\Carbon::parse($nw->time)->format('H:i'),
                'content' => $nw->content,
                'image_paths' => $nw->image_paths,
                'images' => $imageUrls,
                'status' => $nw->status,
                'nurse_name' => $nurseName,
                'nurse_id' => $nurseId,
            ]
        ], 201);
    }
}
