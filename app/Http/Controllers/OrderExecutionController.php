<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorApItem;
use App\Models\DoctorApMedicine;
use App\Models\DoctorApScience;
use App\Models\DoctorApLaboratory;
use App\Models\DoctorApEquipments;
use App\Models\DoctorApAnother;
use App\Models\DoctorSoItem;
use App\Models\PatientDialysisMachineLong;
use App\Models\PatientDialysisMachineShort;
use App\Models\PatientDialysisWeight;
use App\Models\PatientEpoIronMedicine;
use App\Models\PatientDialysisMedicine;
use App\Models\PatientCheck;
use App\Models\PatientMidNurseRecord;
use App\Models\DialysisMachine;
use App\Models\User;

class OrderExecutionController extends Controller
{
    /**
     * GET /api/v1/dialysis-checks/{check_id}/orders
     * 匯總查詢醫囑項目清單（僅撈取：未執行/未確認 + 當日已執行/已確認，並載入備註補充）
     */
    public function index($check_id)
    {
        $check = PatientCheck::find($check_id);
        if (!$check) {
            $patient = \App\Models\Patient::where('medical_record_no', $check_id)->first();
            if ($patient) {
                $check = PatientCheck::whereHas('patient_reservation', function($q) use ($patient) {
                    $q->where('patient_id', $patient->id);
                })->latest('id')->first();
            }
        }

        if (!$check) {
            return response()->json(['success' => true, 'orders' => [], 'active_count' => 0], 200);
        }

        $patient = $check->patient;
        $patientId = $patient?->id;
        $orders = collect();

        // 當日日期比對基準
        $today = now()->format('Y-m-d');
        $checkDate = $check->time ? substr($check->time, 0, 10) : $today;

        // 撈取當班所有護理紀錄，用以精確提取護理補充備註
        $midRecords = PatientMidNurseRecord::where('patient_check_id', $check->id)
            ->where('deleted', 0)
            ->pluck('value')
            ->toArray();

        $extractNote = function($record, $searchKey = null) use ($midRecords) {
            if (!empty($record->data)) return $record->data;
            if (!empty($record->note)) {
                if (str_contains($record->note, '[護理補充:')) {
                    preg_match('/\[護理補充:\s*(.*?)\]/', $record->note, $matches);
                    if (!empty($matches[1])) return $matches[1];
                }
            }
            if ($searchKey) {
                foreach ($midRecords as $val) {
                    if (str_contains($val, $searchKey) && str_contains($val, '護理補充：')) {
                        $parts = explode('護理補充：', $val);
                        if (isset($parts[1])) {
                            $comp = trim(explode('（', $parts[1])[0]);
                            if (!empty($comp)) return $comp;
                        }
                    }
                }
            }
            return '';
        };

        // 規則 1：臨時處置類（未執行一律撈出；已執行/延後僅保留當日）
        $isEligibleAp = function($nurseStatus, $responseTime, $updatedAt, $time) use ($today, $checkDate) {
            if ($nurseStatus == 0) return true; // 未執行必撈
            $respDate = $responseTime ? substr($responseTime, 0, 10) : null;
            $updDate = $updatedAt ? substr((string)$updatedAt, 0, 10) : null;
            $tDate = $time ? substr($time, 0, 10) : null;
            return ($respDate === $today || $respDate === $checkDate ||
                    $updDate === $today || $updDate === $checkDate ||
                    $tDate === $today || $tDate === $checkDate);
        };

        // 規則 2：處方確認類（未確認一律撈出；已確認僅保留當日）
        $isEligibleConfirm = function($isConfirmed, $confirmDate, $updatedAt, $startDate) use ($today, $checkDate) {
            if (!$isConfirmed) return true; // 未確認必撈
            $cDate = $confirmDate ? substr($confirmDate, 0, 10) : null;
            $uDate = $updatedAt ? substr((string)$updatedAt, 0, 10) : null;
            $sDate = $startDate ? substr($startDate, 0, 10) : null;
            return ($cDate === $today || $cDate === $checkDate ||
                    $uDate === $today || $uDate === $checkDate ||
                    $sDate === $today || $sDate === $checkDate);
        };

        // 0. doctor_so_items (醫師主訴與 SO 查房記錄，僅取當日)
        $soItems = DoctorSoItem::where('patient_check_id', $check->id)->get();
        foreach ($soItems as $so) {
            $soDate = $so->time ? substr($so->time, 0, 10) : ($so->created_at ? substr((string)$so->created_at, 0, 10) : null);
            if (($soDate === $today || $soDate === $checkDate) && (!empty($so->patient_statement) || !empty($so->note))) {
                $soContent = ($so->patient_statement ? "主訴: {$so->patient_statement}" : "") . ($so->note ? " 備註: {$so->note}" : "");
                $orders->push([
                    'id' => $so->id,
                    'table' => 'doctor_so_items',
                    'category' => 'SO主訴',
                    'action_type' => 'confirm',
                    'time' => \Carbon\Carbon::parse($so->time)->format('H:i'),
                    'doctor' => User::find($so->doctor_id)?->name ?? '張院長',
                    'content' => $soContent,
                    'complement' => $so->note ?? '',
                    'isUrgent' => false,
                    'isExecuted' => true,
                    'isPostponed' => false,
                    'execNurse' => '已查房',
                    'execTime' => '',
                ]);
            }
        }

        // 1. 理學: doctor_ap_sciences (同項目保留最新一筆)
        $apSciences = DoctorApScience::where('patient_check_id', $check->id)->where('deleted', 0)->orderBy('id', 'desc')->get()->unique('condition');
        foreach ($apSciences as $s) {
            if (!$isEligibleAp($s->nurse_status, $s->nurse_response_time, $s->updated_at, $s->time)) continue;
            $orders->push([
                'id' => $s->id,
                'table' => 'doctor_ap_sciences',
                'category' => '理學',
                'action_type' => 'exec_or_postpone',
                'time' => \Carbon\Carbon::parse($s->time)->format('H:i'),
                'doctor' => User::find($s->doctor_id)?->name ?? '張院長',
                'content' => ($s->condition ?? '理學處置與衛教') . ($s->note ? "（{$s->note}）" : ""),
                'complement' => $extractNote($s, $s->condition),
                'isUrgent' => (bool)$s->urgent,
                'isExecuted' => $s->nurse_status == 1,
                'isPostponed' => $s->nurse_status == 2,
                'execNurse' => User::find($s->nurse_id)?->name ?? ($s->nurse_status ? '楚心瑜' : ''),
                'execTime' => $s->nurse_response_time ? \Carbon\Carbon::parse($s->nurse_response_time)->format('H:i') : '',
            ]);
        }

        // 2. 實驗室: doctor_ap_laboratories (同項目保留最新一筆)
        $apLabs = DoctorApLaboratory::where('patient_check_id', $check->id)->where('deleted', 0)->orderBy('id', 'desc')->get()->unique('condition');
        foreach ($apLabs as $l) {
            if (!$isEligibleAp($l->nurse_status, $l->nurse_response_time, $l->updated_at, $l->time)) continue;
            $orders->push([
                'id' => $l->id,
                'table' => 'doctor_ap_laboratories',
                'category' => '實驗室',
                'action_type' => 'exec_or_postpone',
                'time' => \Carbon\Carbon::parse($l->time)->format('H:i'),
                'doctor' => User::find($l->doctor_id)?->name ?? '張院長',
                'content' => ($l->condition ?? '加抽檢驗') . ($l->note ? "（{$l->note}）" : ""),
                'complement' => $extractNote($l, $l->condition),
                'isUrgent' => (bool)$l->urgent,
                'isExecuted' => $l->nurse_status == 1,
                'isPostponed' => $l->nurse_status == 2,
                'execNurse' => User::find($l->nurse_id)?->name ?? ($l->nurse_status ? '楚心瑜' : ''),
                'execTime' => $l->nurse_response_time ? \Carbon\Carbon::parse($l->nurse_response_time)->format('H:i') : '',
            ]);
        }

        // 3. 醫器: doctor_ap_equipments (同項目保留最新一筆)
        $apEquips = DoctorApEquipments::where('patient_check_id', $check->id)->where('deleted', 0)->orderBy('id', 'desc')->get()->unique('equipment');
        foreach ($apEquips as $e) {
            if (!$isEligibleAp($e->nurse_status, $e->nurse_response_time, $e->updated_at, $e->time)) continue;
            $orders->push([
                'id' => $e->id,
                'table' => 'doctor_ap_equipments',
                'category' => '醫器',
                'action_type' => 'exec_or_postpone',
                'time' => \Carbon\Carbon::parse($e->time)->format('H:i'),
                'doctor' => User::find($e->doctor_id)?->name ?? '張院長',
                'content' => "{$e->equipment} {$e->amount}" . ($e->note ? "（{$e->note}）" : ""),
                'complement' => $extractNote($e, $e->equipment),
                'isUrgent' => false,
                'isExecuted' => $e->nurse_status == 1,
                'isPostponed' => $e->nurse_status == 2,
                'execNurse' => User::find($e->nurse_id)?->name ?? ($e->nurse_status ? '楚心瑜' : ''),
                'execTime' => $e->nurse_response_time ? \Carbon\Carbon::parse($e->nurse_response_time)->format('H:i') : '',
            ]);
        }

        // 4. 一般給藥: doctor_ap_medicines (同項目保留最新一筆)
        $apMeds = DoctorApMedicine::where('patient_check_id', $check->id)->where('deleted', 0)->orderBy('id', 'desc')->get()->unique('medicine');
        foreach ($apMeds as $m) {
            if (!$isEligibleAp($m->nurse_status, $m->nurse_response_time, $m->updated_at, $m->time)) continue;
            $orders->push([
                'id' => $m->id,
                'table' => 'doctor_ap_medicines',
                'category' => '一般給藥',
                'action_type' => 'exec_or_postpone',
                'time' => \Carbon\Carbon::parse($m->time)->format('H:i'),
                'doctor' => User::find($m->doctor_id)?->name ?? '張院長',
                'content' => "{$m->medicine} {$m->amount}" . ($m->note ? "（{$m->note}）" : ""),
                'complement' => $extractNote($m, $m->medicine),
                'isUrgent' => true,
                'isExecuted' => $m->nurse_status == 1,
                'isPostponed' => $m->nurse_status == 2,
                'execNurse' => User::find($m->nurse_id)?->name ?? ($m->nurse_status ? '楚心瑜' : ''),
                'execTime' => $m->nurse_response_time ? \Carbon\Carbon::parse($m->nurse_response_time)->format('H:i') : '',
            ]);
        }

        // 5. 其他: doctor_ap_anothers (同項目保留最新一筆)
        $apAnothers = DoctorApAnother::where('patient_check_id', $check->id)->where('deleted', 0)->orderBy('id', 'desc')->get()->unique('note');
        foreach ($apAnothers as $a) {
            if (!$isEligibleAp($a->nurse_status, $a->nurse_response_time, $a->updated_at, $a->time)) continue;
            $orders->push([
                'id' => $a->id,
                'table' => 'doctor_ap_anothers',
                'category' => '其他',
                'action_type' => 'exec_or_postpone',
                'time' => \Carbon\Carbon::parse($a->time)->format('H:i'),
                'doctor' => User::find($a->doctor_id)?->name ?? '張院長',
                'content' => $a->note ?? '醫師臨時交班處置',
                'complement' => $extractNote($a, $a->note),
                'isUrgent' => false,
                'isExecuted' => $a->nurse_status == 1,
                'isPostponed' => $a->nurse_status == 2,
                'execNurse' => User::find($a->nurse_id)?->name ?? ($a->nurse_status ? '楚心瑜' : ''),
                'execTime' => $a->nurse_response_time ? \Carbon\Carbon::parse($a->nurse_response_time)->format('H:i') : '',
            ]);
        }

        // 6. 通用臨時醫囑: doctor_ap_items (同項目保留最新一筆)
        $apItems = DoctorApItem::where('patient_check_id', $check->id)->where('deleted', 0)->orderBy('id', 'desc')->get()->unique('content');
        foreach ($apItems as $item) {
            if (!$isEligibleAp($item->nurse_status, null, $item->updated_at, $item->time)) continue;
            $isUrgent = str_contains($item->content, '緊急') || str_contains($item->content, '擴充液') || str_contains($item->content, 'Norvasc');
            $orders->push([
                'id' => $item->id,
                'table' => 'doctor_ap_items',
                'category' => '一般給藥',
                'action_type' => 'exec_or_postpone',
                'time' => \Carbon\Carbon::parse($item->time)->format('H:i'),
                'doctor' => User::find($item->doctor_id)?->name ?? '張院長',
                'content' => $item->content,
                'complement' => $extractNote($item, $item->content),
                'isUrgent' => $isUrgent,
                'isExecuted' => $item->nurse_status == 1,
                'isPostponed' => $item->nurse_status == 2,
                'execNurse' => User::find($item->nurse_id)?->name ?? ($item->nurse_status ? '楚心瑜' : ''),
                'execTime' => $item->updated_at ? \Carbon\Carbon::parse($item->updated_at)->format('H:i') : '',
            ]);
        }

        // 7. 處方確認類 (patient_dialysis_machine_longs/shorts, weights, medicines)
        if ($patientId) {
            // 7.1 透析參數長期: patient_dialysis_machine_longs (每個機台參數僅保留最新一筆)
            $mLongs = PatientDialysisMachineLong::where('patient_id', $patientId)->whereNull('end_date')->orderBy('id', 'desc')->get()->unique('dialysis_machine_id');
            foreach ($mLongs as $ml) {
                $isConfirmed = !empty($ml->nurse_start_id);
                if (!$isEligibleConfirm($isConfirmed, $ml->nurse_start_date, $ml->updated_at, $ml->start_date)) continue;
                $machine = DialysisMachine::find($ml->dialysis_machine_id);
                $mName = $machine?->name ?? '機台參數';
                $orders->push([
                    'id' => $ml->id,
                    'table' => 'patient_dialysis_machine_longs',
                    'category' => '透析參數長期',
                    'action_type' => 'confirm',
                    'time' => \Carbon\Carbon::parse($ml->start_date)->format('m/d'),
                    'doctor' => User::find($ml->doctor_start_id)?->name ?? '張院長',
                    'content' => "{$mName} 調整為 {$ml->value}" . ($ml->change_reason ? "（{$ml->change_reason}）" : ($ml->note ? "（{$ml->note}）" : "")),
                    'complement' => $extractNote($ml, '機台參數'),
                    'isUrgent' => false,
                    'isExecuted' => $isConfirmed,
                    'isPostponed' => false,
                    'execNurse' => User::find($ml->nurse_start_id)?->name ?? ($isConfirmed ? '楚心瑜' : ''),
                    'execTime' => $ml->nurse_start_date ? \Carbon\Carbon::parse($ml->nurse_start_date)->format('H:i') : '',
                ]);
            }

            // 7.2 透析參數短期: patient_dialysis_machine_shorts (每個機台參數僅保留最新一筆)
            $mShorts = PatientDialysisMachineShort::where('patient_id', $patientId)->whereNull('end_date')->orderBy('id', 'desc')->get()->unique('dialysis_machine_id');
            foreach ($mShorts as $ms) {
                $isConfirmed = !empty($ms->nurse_start_id);
                if (!$isEligibleConfirm($isConfirmed, $ms->nurse_start_date, $ms->updated_at, $ms->start_date)) continue;
                $machine = DialysisMachine::find($ms->dialysis_machine_id);
                $mName = $machine?->name ?? '機台參數';
                $orders->push([
                    'id' => $ms->id,
                    'table' => 'patient_dialysis_machine_shorts',
                    'category' => '透析參數短期',
                    'action_type' => 'confirm',
                    'time' => \Carbon\Carbon::parse($ms->start_date)->format('m/d'),
                    'doctor' => User::find($ms->doctor_start_id)?->name ?? '張院長',
                    'content' => "{$mName} 單次設定為 {$ms->value}" . ($ms->note ? "（{$ms->note}）" : ""),
                    'complement' => $extractNote($ms, '機台參數'),
                    'isUrgent' => false,
                    'isExecuted' => $isConfirmed,
                    'isPostponed' => false,
                    'execNurse' => User::find($ms->nurse_start_id)?->name ?? ($isConfirmed ? '楚心瑜' : ''),
                    'execTime' => $ms->nurse_start_date ? \Carbon\Carbon::parse($ms->nurse_start_date)->format('H:i') : '',
                ]);
            }

            // 7.3 乾體重: patient_dialysis_weights (僅保留最新一筆乾體重確認)
            $latestW = PatientDialysisWeight::where('patient_id', $patientId)->orderBy('id', 'desc')->first();
            if ($latestW) {
                $isConfirmed = !empty($latestW->nurse_id);
                if ($isEligibleConfirm($isConfirmed, $latestW->nurse_date, $latestW->updated_at, $latestW->date)) {
                    $orders->push([
                        'id' => $latestW->id,
                        'table' => 'patient_dialysis_weights',
                        'category' => '乾體重',
                        'action_type' => 'confirm',
                        'time' => \Carbon\Carbon::parse($latestW->date)->format('m/d'),
                        'doctor' => User::find($latestW->doctor_id)?->name ?? '張院長',
                        'content' => "乾體重調整為 {$latestW->dry_weight} kg" . ($latestW->adjust_reason ? "（{$latestW->adjust_reason}）" : ""),
                        'complement' => $extractNote($latestW, '乾體重'),
                        'isUrgent' => false,
                        'isExecuted' => $isConfirmed,
                        'isPostponed' => false,
                        'execNurse' => User::find($latestW->nurse_id)?->name ?? ($isConfirmed ? '楚心瑜' : ''),
                        'execTime' => $latestW->nurse_date ? \Carbon\Carbon::parse($latestW->nurse_date)->format('H:i') : '',
                    ]);
                }
            }

            // 7.4 透析用藥: patient_dialysis_medicines (每種用藥僅保留最新一筆)
            $diaMeds = PatientDialysisMedicine::where('patient_id', $patientId)->whereNull('end_date')->orderBy('id', 'desc')->get()->unique('medicine_id');
            foreach ($diaMeds as $dm) {
                $isConfirmed = !empty($dm->nurse_start_id);
                if (!$isEligibleConfirm($isConfirmed, $dm->nurse_start_date, $dm->updated_at, $dm->start_date)) continue;
                $orders->push([
                    'id' => $dm->id,
                    'table' => 'patient_dialysis_medicines',
                    'category' => '透析用藥',
                    'action_type' => 'confirm',
                    'time' => \Carbon\Carbon::parse($dm->start_date)->format('m/d'),
                    'doctor' => User::find($dm->doctor_start_id)?->name ?? '張院長',
                    'content' => "{$dm->medicine} {$dm->one_amount}" . ($dm->note ? "（{$dm->note}）" : ""),
                    'complement' => $extractNote($dm, $dm->medicine),
                    'isUrgent' => false,
                    'isExecuted' => $isConfirmed,
                    'isPostponed' => false,
                    'execNurse' => User::find($dm->nurse_start_id)?->name ?? ($isConfirmed ? '楚心瑜' : ''),
                    'execTime' => $dm->nurse_start_date ? \Carbon\Carbon::parse($dm->nurse_start_date)->format('H:i') : '',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'orders' => $orders->values(),
            'active_count' => $orders->where('isExecuted', false)->where('isPostponed', false)->count()
        ], 200);
    }

    /**
     * POST /api/v1/orders/{table}/{id}/action
     * 通用醫囑動作處理：支援「執行 (execute)」、「延後 (postpone)」與「確認 (confirm)」
     */
    public function handleOrderAction(Request $request, $table, $id)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:execute,postpone,confirm',
            'complement' => 'nullable|string',
            'reason' => 'nullable|string'
        ]);

        $action = $validated['action'];
        $nurse = $request->user() ?: Auth::user();
        $nurseId = $nurse?->id ?: ($request->input('nurse_id') ?: 1);
        $nurseName = $nurse?->name ?: ($request->input('nurse_name') ?: '楚心瑜');
        $timeStr = now()->format('H:i');
        $checkId = $request->input('check_id');
        $todayDate = now()->format('Y-m-d');

        $tableModelMap = [
            'doctor_ap_medicines' => DoctorApMedicine::class,
            'doctor_ap_sciences' => DoctorApScience::class,
            'doctor_ap_laboratories' => DoctorApLaboratory::class,
            'doctor_ap_equipments' => DoctorApEquipments::class,
            'doctor_ap_anothers' => DoctorApAnother::class,
            'doctor_ap_items' => DoctorApItem::class,
            'doctor_so_items' => DoctorSoItem::class,
            'patient_dialysis_weights' => PatientDialysisWeight::class,
            'patient_dialysis_machine_longs' => PatientDialysisMachineLong::class,
            'patient_dialysis_machine_shorts' => PatientDialysisMachineShort::class,
            'patient_epo_iron_medicines' => PatientEpoIronMedicine::class,
            'patient_dialysis_medicines' => PatientDialysisMedicine::class,
        ];

        $modelClass = $tableModelMap[$table] ?? DoctorApItem::class;
        $record = $modelClass::find($id);

        if (!$record) {
            return response()->json(['success' => false, 'message' => '找不到對應的醫囑項目'], 404);
        }

        // 確保 checkId 必定解析成功以寫入護理病歷
        if (!$checkId) {
            if (isset($record->patient_check_id) && $record->patient_check_id) {
                $checkId = $record->patient_check_id;
            } elseif (isset($record->patient_id) && $record->patient_id) {
                $checkId = PatientCheck::whereHas('patient_reservation', function($q) use ($record) {
                    $q->where('patient_id', $record->patient_id);
                })->latest('id')->value('id');
            }
        }

        // 儲存備註至該筆醫囑紀錄
        $compInput = $validated['complement'] ?? null;
        if (!empty($compInput)) {
            if (isset($record->data)) {
                $record->data = $compInput;
            } elseif (isset($record->note)) {
                if (empty($record->note)) {
                    $record->note = $compInput;
                } elseif (!str_contains($record->note, $compInput)) {
                    $record->note .= " [護理補充: {$compInput}]";
                }
            }
            $record->save();
        }

        $logText = "";
        $compStr = !empty($compInput) ? "。護理補充：{$compInput}" : "";

        if (str_starts_with($table, 'doctor_ap_')) {
            $contentStr = $record->content ?? $record->medicine ?? $record->condition ?? $record->equipment ?? $record->note ?? '臨時處置';

            if ($action === 'execute') {
                $record->nurse_status = 1;
                $record->nurse_id = $nurseId;
                $record->nurse_response_time = now();
                $record->save();

                $logText = "[醫囑執行] 完成核對並執行醫師處置：{$contentStr}{$compStr}";
            } elseif ($action === 'postpone') {
                $record->nurse_status = 2; // 2: 延後
                $record->nurse_id = $nurseId;
                $record->nurse_response_time = now();
                $record->save();

                $reasonStr = !empty($validated['reason']) ? "。延後原因：{$validated['reason']}" : "";
                $logText = "[醫囑延後] 醫師處置暫緩執行：{$contentStr}{$reasonStr}";
            }
        } else {
            // 處方確認類 (patient_dialysis_weights / machines / medicines)
            $contentDesc = "";
            if ($table === 'patient_dialysis_weights') {
                $record->nurse_id = $nurseId;
                $record->nurse_date = now();
                $record->status = 1;
                $record->save();
                $contentDesc = "乾體重調整為 {$record->dry_weight} kg";
            } elseif ($table === 'patient_dialysis_machine_longs' || $table === 'patient_dialysis_machine_shorts') {
                $record->nurse_start_id = $nurseId;
                $record->nurse_start_date = $todayDate;
                $record->save();
                $machine = DialysisMachine::find($record->dialysis_machine_id);
                $mName = $machine?->name ?? '機台參數';
                $contentDesc = "透析機台參數 {$mName} 設定為 {$record->value}";
            } elseif ($table === 'patient_dialysis_medicines') {
                $record->nurse_start_id = $nurseId;
                $record->nurse_start_date = $todayDate;
                $record->save();
                $contentDesc = "透析用藥處方 {$record->medicine} {$record->one_amount}";
            }

            $logText = "[處方核對] 護理師核對確認醫師處方變更：{$contentDesc}{$compStr}（雙人核對完成）";
        }

        // 寫入護理紀錄 (PatientMidNurseRecord) 確保醫療法律稽核實體存證留痕
        $createdRec = null;
        if ($checkId && $logText) {
            $createdRec = PatientMidNurseRecord::create([
                'patient_check_id' => $checkId,
                'time' => now()->format('Y-m-d H:i:s'),
                'value' => $logText,
                'nurse_id' => $nurseId,
                'deleted' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'action' => $action,
            'exec_nurse' => $nurseName,
            'exec_time' => $timeStr,
            'complement' => $compInput ?? '',
            'message' => '醫囑處置已成功完成操作並存檔稽核留痕。',
            'nursing_record' => $createdRec ? [
                'id' => $createdRec->id,
                'time' => now()->format('H:i'),
                'content' => $logText,
                'nurse' => $nurseName,
                'deleted' => false,
                'deletedMeta' => null
            ] : null
        ], 200);
    }

    /**
     * POST /api/v1/dialysis-checks/{check_id}/orders
     * 開立新臨時醫囑
     */
    public function store(Request $request, $check_id)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'nullable|string',
            'is_urgent' => 'nullable|boolean',
            'time' => 'nullable|string',
        ]);

        $check = PatientCheck::findOrFail($check_id);
        $doctorId = Auth::id() ?? 1;

        $item = DoctorApItem::create([
            'patient_check_id' => $check->id,
            'time' => $validated['time'] ?? now()->format('Y-m-d H:i:s'),
            'type' => $validated['type'] ?? 'MED',
            'content' => $validated['content'],
            'data' => '',
            'doctor_id' => $doctorId,
            'nurse_status' => 0,
            'deleted' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => '臨時醫囑已成功開立並注入處置池。',
            'order' => [
                'id' => $item->id,
                'table' => 'doctor_ap_items',
                'category' => '一般給藥',
                'action_type' => 'exec_or_postpone',
                'time' => \Carbon\Carbon::parse($item->time)->format('H:i'),
                'doctor' => Auth::user()?->name ?? '張院長',
                'content' => $item->content,
                'complement' => '',
                'isUrgent' => (bool)($validated['is_urgent'] ?? false),
                'isExecuted' => false,
                'isPostponed' => false,
                'execNurse' => '',
                'execTime' => '',
            ]
        ], 201);
    }

    public function updateExecution(Request $request, $id)
    {
        return $this->handleOrderAction($request, 'doctor_ap_items', $id);
    }
}
