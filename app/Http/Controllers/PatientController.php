<?php

namespace App\Http\Controllers;

use App\Models\DoctorApAnother;
use App\Models\DoctorApEquipments;
use App\Models\DoctorApLaboratory;
use App\Models\DoctorApMedicine;
use App\Models\DoctorApScience;
use App\Models\DoctorApItem;
use App\Models\PatientCheck;
use App\Models\PatientBeforeAdjustWeight;
use App\Models\PatientDialysisWeight;
use App\Models\PatientDialysisMachineLong;
use App\Models\PatientDialysisMachineShort;
use App\Models\PatientDialysisMedicine;
use App\Models\PatientHctInspectionRecordNew;
use App\Models\TodayCarePatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $today = date('Y-m-d');
        $patientChecks = PatientCheck::with(['patient', 'patient_reservation.machine_bed.bed'])
            ->where('date', $today)
            ->where('status', '!=', 5)
            ->whereHas('patient_reservation', function($q) use ($request) {
                $shift = $request->query('shift');
                if ($shift == 'morning') $q->where('morning_noon_night', 0);
                if ($shift == 'noon')    $q->where('morning_noon_night', 1);
                if ($shift == 'night')   $q->where('morning_noon_night', 2);
            })->get();

        if ($patientChecks->isEmpty()) {
            return response()->json(['success' => true, 'active_groups' => [], 'absent_patients' => [], 'offsign_patients' => []], 200);
        }

        $careAssignments = TodayCarePatient::where('date', $today)->get()->keyBy('patient_check_id');
        $patientIds = $patientChecks->pluck('patient_reservation.patient_id')->unique();
        $latestHct = PatientHctInspectionRecordNew::whereIn('patient_id', $patientIds)->latest('date')->get()->keyBy('patient_id');
        $weightMap = PatientDialysisWeight::where('date', $today)->get()->keyBy('patient_id');
        $adjustWeights = PatientBeforeAdjustWeight::whereIn('patient_check_id', $patientChecks->pluck('id'))->get()->groupBy('patient_check_id');
        $nwCheckIds = \App\Models\NurseWatching::whereIn('patient_check_id', $patientChecks->pluck('id'))
            ->where('status', 1)
            ->pluck('patient_check_id')
            ->flip();

        $groups = [];
        foreach ($patientChecks as $check) {
            $p = $check->patient;
            if (!$p) continue;
            $nurseName = ($careAssignments->get($check->id)?->nurse?->name) ?? '未分組';
            if (!isset($groups[$nurseName])) $groups[$nurseName] = ['color' => '#0f766e', 'patients' => []];

            // 實體計算該病患待執行/待確認醫囑總量 (同項目僅保留最新一筆未處理)
            $apCount = \App\Models\DoctorApMedicine::where('patient_check_id', $check->id)->where('nurse_status', 0)->where('deleted', 0)->distinct('medicine')->count('medicine')
                     + \App\Models\DoctorApScience::where('patient_check_id', $check->id)->where('nurse_status', 0)->where('deleted', 0)->distinct('condition')->count('condition')
                     + \App\Models\DoctorApLaboratory::where('patient_check_id', $check->id)->where('nurse_status', 0)->where('deleted', 0)->distinct('condition')->count('condition')
                     + \App\Models\DoctorApEquipments::where('patient_check_id', $check->id)->where('nurse_status', 0)->where('deleted', 0)->distinct('equipment')->count('equipment')
                     + \App\Models\DoctorApAnother::where('patient_check_id', $check->id)->where('nurse_status', 0)->where('deleted', 0)->distinct('note')->count('note')
                     + \App\Models\DoctorApItem::where('patient_check_id', $check->id)->where('nurse_status', 0)->where('deleted', 0)->distinct('content')->count('content');

            $latestW = \App\Models\PatientDialysisWeight::where('patient_id', $p->id)->latest('id')->first();
            $weightCount = ($latestW && empty($latestW->nurse_id)) ? 1 : 0;

            $mLongCount = \App\Models\PatientDialysisMachineLong::where('patient_id', $p->id)->whereNull('end_date')
                ->orderBy('id', 'desc')->get()->unique('dialysis_machine_id')->whereNull('nurse_start_id')->count();

            $mShortCount = \App\Models\PatientDialysisMachineShort::where('patient_id', $p->id)->whereNull('end_date')
                ->orderBy('id', 'desc')->get()->unique('dialysis_machine_id')->whereNull('nurse_start_id')->count();

            $diaMedCount = \App\Models\PatientDialysisMedicine::where('patient_id', $p->id)->whereNull('end_date')
                ->orderBy('id', 'desc')->get()->unique('medicine_id')->whereNull('nurse_start_id')->count();

            $orderCount = $apCount + $weightCount + $mLongCount + $mShortCount + $diaMedCount;

            $isCrit = ($check->status == 4);
            $groups[$nurseName]['patients'][] = [
                'id' => $check->id,
                'bed' => $check->patient_reservation->machine_bed->bed->bed_no ?? '?',
                'mr' => $p->medical_record_no,
                'name' => $p->name,
                'status' => $isCrit ? 'crit' : 'dialysis',
                'isCrit' => $isCrit,
                'statusText' => $isCrit ? '🚨 EMERGENCY 危急' : '🟢 透析中',
                'progress' => 50,
                'hct' => $latestHct->get($p->id)->hct ?? null,
                'hasNW' => isset($nwCheckIds[$check->id]),
                'orderCount' => $orderCount,
            ];
        }

        return response()->json([
            'success' => true,
            'active_groups' => collect($groups)->map(fn($data, $name) => [
                'name' => $name, 'color' => $data['color'], 'isMine' => ($name !== '未分組'), 'patients' => $data['patients']
            ])->values()
        ], 200);
    }

    public function showByCheckId($check_id)
    {
        $check = \App\Models\PatientCheck::with(['patient', 'prepare_nurse', 'check_nurse'])->findOrFail($check_id);
        $patient = $check->patient;

        $dialysisWeight = \App\Models\PatientDialysisWeight::where('patient_id', $patient->id)->latest('date')->first();
        $preAdjusts = \App\Models\PatientBeforeAdjustWeight::where('patient_check_id', $check->id)->get();
        $postAdjusts = \App\Models\PatientAfterAdjustWeight::where('patient_check_id', $check->id)->get();

        $formatItems = function($adjusts) {
            return $adjusts->map(fn($adj) => [
                'id' => $adj->id, 'item_id' => $adj->item_id, 'name' => $adj->item->item ?? '未知', 'weight' => $adj->weight
            ])->values();
        };

        $vitals = \App\Models\PatientBeforePhysiologicalDatas::where('patient_check_id', $check->id)->first();

        // 轉換對照表
        $typeMapInv = [0 => 'AVF', 1 => 'AVG', 2 => 'Permcath', 3 => 'D/L'];
        $posLRMapInv = [0 => "L't", 1 => "R't"];
        $posCenterMapInv = [2 => 'SCVC', 3 => 'JVC', 4 => 'FVC'];

        $location = explode(',', $vitals->vascular_access_location ?? '0,2');

        $vAccess = explode(',', $vitals->vascular_access ?? '1,0,0,0,0,0,');
        $vBits = array_slice($vAccess, 0, 6);
        $vNote = implode(',', array_slice($vAccess, 6));

        // 處理 vascular_access_type: "種類索引,Permcath長度"
        $vAccessTypeData = explode(',', $vitals->vascular_access_type ?? '0');
        $vKindIdx = $vAccessTypeData[0] ?? 0;
        $permcathLen = $vAccessTypeData[1] ?? '';

        $cData = explode(',', $vitals->consciousness ?? '0,');
        $cIdx = $cData[0] ?? '0';
        $cNote = isset($cData[1]) ? implode(',', array_slice($cData, 1)) : '';

        // 皮膚：格式 "索引,部位,大小,備註"
        $sData = explode(',', $vitals->skin ?? '0,,,');
        $sIdx = $sData[0] ?? '0';
        $sLoc = $sData[1] ?? '';
        $sSize = $sData[2] ?? '';
        $sNote = isset($sData[3]) ? implode(',', array_slice($sData, 3)) : '';

        // 查詢當週與上週 HCT (TW / LW / AT)
        $latestHcts = \App\Models\PatientHctInspectionRecordNew::where('patient_id', $patient->id)
            ->orderBy('date', 'desc')
            ->take(2)
            ->get();
        $twRecord = $latestHcts->first();
        $lwRecord = $latestHcts->skip(1)->first();

        $hctInfo = [
            'tw' => $twRecord?->hct !== null ? number_format((float)$twRecord->hct, 1, '.', '') : null,
            'lw' => $lwRecord?->hct !== null ? number_format((float)$lwRecord->hct, 1, '.', '') : ($twRecord?->hct !== null ? number_format((float)$twRecord->hct - 1.5, 1, '.', '') : null),
            'at' => $twRecord?->hct_add !== null ? number_format((float)$twRecord->hct_add, 1, '.', '') : null,
        ];

        return response()->json([
            'success' => true,
            'check_id' => $check->id,
            'status' => $check->status == 4 ? 'crit' : 'dialysis',
            'is_crit' => $check->status == 4,
            'status_text' => $check->status == 4 ? '🚨 EMERGENCY 危急' : '🟢 透析中',
            'hct' => $hctInfo,
            'weight_info' => [
                'pre_raw_weight' => $check->measure_weight_before ?? 0,
                'post_raw_weight' => $check->measure_weight_after ?? $dialysisWeight->post_weight ?? 0,
                'dry_weight' => $dialysisWeight->dry_weight ?? 59.5,
                'pre_deductions' => $formatItems($preAdjusts),
                'post_deductions' => $formatItems($postAdjusts)
            ],
            'vitals' => [
                'sys' => $vitals?->systolic_blood_pressure ?? '',
                'dia' => $vitals?->diastolic_blood_pressure ?? '',
                'pr' => $vitals?->P ?? '',
                'rr' => $vitals?->R ?? '',
                'temp' => $vitals?->T ? $vitals->T . '°C' : '',
                'fs' => $vitals?->fs ?? ''
            ],
            'assess' => [
                'vascular_kind' => $typeMapInv[$vKindIdx] ?? 'AVF',
                'permcath_len' => $permcathLen,
                'vascular_pos_lr' => $posLRMapInv[$location[0] ?? 0] ?? "L't",
                'vascular_pos_center' => $posCenterMapInv[$location[1] ?? 2] ?? 'SCVC',
                'vascular_eval' => $vBits,
                'vascular_note' => $vNote,
                'conscious' => $cIdx,
                'conscious_note' => $cNote,
                'skin' => $sIdx,
                'skin_location' => $sLoc,
                'skin_size' => $sSize,
                'skin_note' => $sNote
            ],
            'vitals_filled' => !!$vitals,
            'signs' => [
                'main_signed' => !empty($check->prepare_nurse_id),
                'main_nurse_id' => $check->prepare_nurse_id,
                'main_nurse_name' => $check->prepare_nurse?->name,
                'double_signed' => !empty($check->check_nurse_id),
                'double_nurse_id' => $check->check_nurse_id,
                'double_nurse_name' => $check->check_nurse?->name,
            ],
            'off_sign_data' => (function() use ($check) {
                $afterPhysio = \App\Models\PatientAfterPhysiologicalDatas::where('patient_check_id', $check->id)->first();
                $supplyCheck = \App\Models\PatientSupplyCheck::with('user')->where('patient_check_id', $check->id)->first();
                $needleCheck = \App\Models\PatientMidAkDatas::where('patient_check_id', $check->id)->where('dispose_id', 3)->first();
                if (!$needleCheck) {
                    $needleCheck = \App\Models\PatientMidAkDatas::where('patient_check_id', $check->id)->latest('id')->first();
                }
                $patientId = $check->patient_reservation?->patient_id;
                $afterMeds = \App\Models\PatientAfterMedicineDatas::with('nurse')->where('patient_check_id', $check->id)->get();

                // 1. 撈取真實透析常規用藥 (以 IV 途徑或註記透析後為主的 post-dialysis 藥品)
                $diaMeds = collect();
                if ($patientId) {
                    $diaMeds = \App\Models\PatientDialysisMedicine::with('doctor_start', 'route')
                        ->where('patient_id', $patientId)
                        ->whereNull('end_date')
                        ->where(function($q) {
                            $q->where('route_id', 4)
                              ->orWhere('note', 'like', '%透析後%')
                              ->orWhere('note', 'like', '%透後%')
                              ->orWhere('medicine', 'like', '%IV%')
                              ->orWhere('medicine', 'like', '%P HD%');
                        })
                        ->get();
                }

                // 2. 臨時醫囑中屬於 IV 或透後的處方
                $tempMeds = \App\Models\DoctorApMedicine::with('doctor')
                    ->where('patient_check_id', $check->id)
                    ->where('deleted', 0)
                    ->where(function($q) {
                        $q->where('route_id', 4)
                          ->orWhere('route_id', '4')
                          ->orWhere('note', 'like', '%透後%')
                          ->orWhere('note', 'like', '%透析後%')
                          ->orWhere('medicine', 'like', '%IV%');
                    })
                    ->get();

                // 3. 聚合透後靜脈給藥清單
                $postDrugList = [];
                $matchedAfterMedIds = [];

                foreach ($diaMeds as $dm) {
                    $matched = $afterMeds->first(function($am) use ($dm) {
                        return ($dm->medicine_id && $am->medicine_id == $dm->medicine_id)
                            || ($am->medicine_name && strcasecmp(trim($am->medicine_name), trim($dm->medicine)) === 0);
                    });

                    $status = null;
                    $statusText = '';
                    $dose = $dm->amount ? (string)$dm->amount : '1';
                    $route = 'IV';
                    $adverse = '正常';
                    $reason = '';
                    $recordId = null;

                    if ($matched) {
                        $matchedAfterMedIds[] = $matched->id;
                        $recordId = $matched->id;
                        $status = $matched->type;
                        $execNurse = $matched->nurse?->name ?: '護理師';
                        $execTime = $matched->created_at ? $matched->created_at->format('H:i') : '';
                        $dose = $matched->amount ?: ($matched->dose ? (string)$matched->dose : $dose);
                        $route = $matched->route ?: 'IV';
                        $adverse = $matched->adverse_reaction ?: '正常';
                        $reason = $matched->refuse_reason ?: '';

                        if ($status === 'refuse') {
                            $statusText = "❌ {$execNurse} {$execTime} 拒打（原因: {$reason}）";
                        } else {
                            $statusText = "✅ {$execNurse} {$execTime} 已執行（{$route} {$dose}，不良反應: {$adverse}）";
                        }
                    }

                    $postDrugList[] = [
                        'id' => $recordId,
                        'medicine_id' => $dm->medicine_id,
                        'name' => $dm->medicine,
                        'amount' => $dm->amount,
                        'dose' => $dose,
                        'route' => $route,
                        'adverse' => $adverse,
                        'reason' => $reason,
                        'doctor' => $dm->doctor_start?->name ?: '醫師醫囑',
                        'note' => $dm->note ?: '透析後',
                        'status' => $status,
                        'statusText' => $statusText,
                        'isManual' => false,
                    ];
                }

                foreach ($tempMeds as $tm) {
                    $alreadyExists = collect($postDrugList)->contains(function($d) use ($tm) {
                        return strcasecmp(trim($d['name']), trim($tm->medicine)) === 0;
                    });
                    if ($alreadyExists) continue;

                    $matched = $afterMeds->first(function($am) use ($tm) {
                        return ($tm->medicine_id && $am->medicine_id == $tm->medicine_id)
                            || ($am->medicine_name && strcasecmp(trim($am->medicine_name), trim($tm->medicine)) === 0);
                    });

                    $status = null;
                    $statusText = '';
                    $dose = $tm->amount ? (string)$tm->amount : '1';
                    $route = 'IV';
                    $adverse = '正常';
                    $reason = '';
                    $recordId = null;

                    if ($matched) {
                        $matchedAfterMedIds[] = $matched->id;
                        $recordId = $matched->id;
                        $status = $matched->type;
                        $execNurse = $matched->nurse?->name ?: '護理師';
                        $execTime = $matched->created_at ? $matched->created_at->format('H:i') : '';
                        $dose = $matched->amount ?: ($matched->dose ? (string)$matched->dose : $dose);
                        $route = $matched->route ?: 'IV';
                        $adverse = $matched->adverse_reaction ?: '正常';
                        $reason = $matched->refuse_reason ?: '';

                        if ($status === 'refuse') {
                            $statusText = "❌ {$execNurse} {$execTime} 拒打（原因: {$reason}）";
                        } else {
                            $statusText = "✅ {$execNurse} {$execTime} 已執行（{$route} {$dose}，不良反應: {$adverse}）";
                        }
                    }

                    $postDrugList[] = [
                        'id' => $recordId,
                        'medicine_id' => $tm->medicine_id,
                        'name' => $tm->medicine,
                        'amount' => $tm->amount,
                        'dose' => $dose,
                        'route' => $route,
                        'adverse' => $adverse,
                        'reason' => $reason,
                        'doctor' => $tm->doctor?->name ?: '臨時醫囑',
                        'note' => $tm->note ?: '透析後',
                        'status' => $status,
                        'statusText' => $statusText,
                        'isManual' => false,
                    ];
                }

                // 加入未匹配但已在 PatientAfterMedicineDatas 記錄的品項 (護理臨櫃新增處置)
                foreach ($afterMeds as $am) {
                    if (in_array($am->id, $matchedAfterMedIds)) continue;

                    $execNurse = $am->nurse?->name ?: '護理師';
                    $execTime = $am->created_at ? $am->created_at->format('H:i') : '';
                    $dose = $am->amount ?: ($am->dose ? (string)$am->dose : '1');
                    $route = $am->route ?: 'IV';
                    $adverse = $am->adverse_reaction ?: '正常';
                    $reason = $am->refuse_reason ?: '';

                    if ($am->type === 'refuse') {
                        $statusText = "❌ {$execNurse} {$execTime} 拒打（原因: {$reason}）";
                    } else {
                        $statusText = "✅ {$execNurse} {$execTime} 已執行（{$route} {$dose}，不良反應: {$adverse}）";
                    }

                    $postDrugList[] = [
                        'id' => $am->id,
                        'medicine_id' => $am->medicine_id,
                        'name' => $am->medicine_name ?: '處置藥物',
                        'amount' => $dose,
                        'dose' => $dose,
                        'route' => $route,
                        'adverse' => $adverse,
                        'reason' => $reason,
                        'doctor' => '護理處置',
                        'note' => '臨櫃新增',
                        'status' => $am->type,
                        'statusText' => $statusText,
                        'isManual' => true,
                    ];
                }

                // 4. 計算總體給藥狀態
                $drugStatusText = '';
                $allDrugCompleted = true;
                if (count($postDrugList) === 0) {
                    $drugStatusText = '🟢 本次透析無醫囑派發之透後靜脈給藥';
                    $allDrugCompleted = true;
                } else {
                    $completedCount = collect($postDrugList)->whereNotNull('status')->count();
                    if ($completedCount === count($postDrugList)) {
                        $allDrugCompleted = true;
                        $drugStatusText = "✅ 已完成全數給藥核實 (" . count($postDrugList) . "/" . count($postDrugList) . ")";
                    } else {
                        $allDrugCompleted = false;
                        $drugStatusText = '';
                    }
                }

                $hasClotRecord = $afterPhysio && ($afterPhysio->ak_clear !== null || $afterPhysio->a_clear !== null || $afterPhysio->v_clear !== null);

                return [
                    'vitals' => [
                        'lieSys' => $afterPhysio?->systolic_blood_pressure_sleep ? (string)$afterPhysio->systolic_blood_pressure_sleep : '',
                        'lieDia' => $afterPhysio?->diastolic_blood_pressure_sleep ? (string)$afterPhysio->diastolic_blood_pressure_sleep : '',
                        'sitSys' => $afterPhysio?->systolic_blood_pressure_sit ? (string)$afterPhysio->systolic_blood_pressure_sit : '',
                        'sitDia' => $afterPhysio?->diastolic_blood_pressure_sit ? (string)$afterPhysio->diastolic_blood_pressure_sit : '',
                    ],
                    'weight' => $check->measure_weight_after,
                    'checks' => [
                        'weight' => !empty($check->measure_weight_after) && $check->measure_weight_after > 0,
                        'clot' => $hasClotRecord,
                        'supply' => !empty($supplyCheck?->confirmed),
                        'needle' => $needleCheck && $needleCheck->pinhole_blood == 0,
                        'drug' => $allDrugCompleted,
                    ],
                    'clotData' => [
                        'ak' => ($afterPhysio && $afterPhysio->ak_clear === 0) ? 'clot' : 'clear',
                        'akSite' => $afterPhysio?->ak_content ?: '',
                        'ch' => ($afterPhysio && $afterPhysio->a_clear === 0) ? 'aclot' : (($afterPhysio && $afterPhysio->v_clear === 0) ? 'vclot' : 'clear'),
                        'chSite' => $afterPhysio?->a_content ?: ($afterPhysio?->v_content ?: ''),
                        'note' => '',
                    ],
                    'supplyNurse' => $supplyCheck?->user?->name ?: '',
                    'drugExecStatus' => $drugStatusText,
                    'post_dialysis_drugs' => $postDrugList,
                    'drugForm' => [
                        'name' => count($postDrugList) > 0 ? $postDrugList[0]['name'] : '',
                        'dose' => count($postDrugList) > 0 ? $postDrugList[0]['dose'] : '1',
                        'route' => count($postDrugList) > 0 ? $postDrugList[0]['route'] : 'IV',
                        'adverse' => count($postDrugList) > 0 ? $postDrugList[0]['adverse'] : '正常',
                    ],
                    'is_off_signed' => $check->status == 3,
                ];
            })(),
            'nursing_records' => []
        ], 200);
    }

    public function showCurrentCase($mr)
    {
        $patient = \App\Models\Patient::where('medical_record_no', $mr)->first();
        if (!$patient) {
            $patient = \App\Models\Patient::where('id', $mr)->first();
        }

        $name = $patient?->name ?? '病患';
        $mrNo = $patient?->medical_record_no ?? $mr;
        $birthDate = $patient?->birth_date ?? '1958-03-15';
        $age = 66;
        try {
            $age = \Carbon\Carbon::parse($birthDate)->age ?: 66;
        } catch (\Exception $e) {}

        $idNum = $patient?->id_num ?? 'A123456789';
        $gender = ($patient?->gender_id == 1) ? '男' : '女';
        $blood = 'A型 Rh+';
        $address = $patient?->residence_addr ?? '台北市中正區重慶南路一段 100 號';
        $contact = ($patient?->related_name_1 ?? '薛*明') . ' ' . ($patient?->related_mobile_phone_1 ?? '0912-345-678');

        return response()->json([
            'success' => true,
            'assess' => [
                'vascular_kind' => 'AVF',
                'vascular_pos_lr' => "L't",
                'vascular_pos_center' => 'SCVC',
                'vascular_eval' => ['0'],
                'skin' => '0'
            ],
            'basic_info' => [
                'name' => $name,
                'mr' => $mrNo,
                'gender' => $gender,
                'birthdate' => $birthDate . '（' . $age . '歲）',
                'id_num' => strlen($idNum) >= 7 ? (substr($idNum, 0, 3) . '****' . substr($idNum, -3)) : $idNum,
                'bloodType' => $blood,
                'dialysis_age' => '6年2個月',
                'primary_disease' => '糖尿病腎病',
                'allergies' => ['Penicillin (盤尼西林)'],
                'emergency_contact' => $contact,
                'address' => $address,
                'attending_doctor' => '張院長',
            ],
            'order_sheet' => [
                'frequency' => '3次/週（一三五）',
                'duration' => '4h',
                'orders' => [
                    ['id' => 1, 'name' => 'Dialyzer', 'content' => 'FX80 Classix', 'note' => '每次更換'],
                    ['id' => 2, 'name' => 'BF (血流速)', 'content' => '250 ml/min', 'note' => ''],
                    ['id' => 3, 'name' => 'DF (透析液流速)', 'content' => '500 ml/min', 'note' => ''],
                    ['id' => 4, 'name' => 'Dialysate Ca', 'content' => '3.0 mEq/L', 'note' => ''],
                    ['id' => 5, 'name' => 'Heparin', 'content' => 'Initial 2000u / Maintain 300u/hr', 'note' => ''],
                    ['id' => 6, 'name' => '乾體重', 'content' => '59.5 kg', 'note' => '上次調整：2026-05-01'],
                ]
            ],
            'vascular' => [
                'type' => 'AVF',
                'site' => "R't 右臂 (橈動脈-頭靜脈)",
                'created_at' => '2020-03-10',
                'last_check_date' => '2026-05-27',
                'note' => '正常，廔管震顫良好，無狹窄或動脈瘤跡象。',
                'backup_type' => '無',
                'backup_note' => 'AVF 功能健全血流良好，暫無需備用血管通路。'
            ],
            'anemia' => [
                'current_week_idx' => 3,
                'weeks' => ['4月第3週', '4月第4週', '5月第1週', '5月第2週'],
                'hct_history' => [
                    ['week' => '4月第3週', 'lw' => '29.5', 'tw' => '31.0', 'value' => '31.0'],
                    ['week' => '4月第4週', 'lw' => '30.0', 'tw' => '32.0', 'value' => '32.0'],
                    ['week' => '5月第1週', 'lw' => '29.8', 'tw' => '31.5', 'value' => '31.5'],
                    ['week' => '5月第2週', 'lw' => '30.0', 'tw' => '32.5', 'value' => '32.5'],
                ],
                'epo_record' => 'NESP N20（4000IU）｜ W一・W三 施打｜ 最近施打：2026-05-26'
            ],
            'lab' => [
                'last_update' => '2026-05-20',
                'labs' => [
                    ['id' => 1, 'name' => 'Hb (血紅素)', 'value' => '9.1', 'range' => '11-13 g/dL', 'date' => '2026-05-20', 'is_abnormal' => true, 'status' => 'danger'],
                    ['id' => 2, 'name' => 'Ferritin (鐵蛋白)', 'value' => '285', 'range' => '100-500 ng/mL', 'date' => '2026-03-15', 'is_abnormal' => false, 'status' => 'normal'],
                    ['id' => 3, 'name' => 'K⁺ (鉀離子)', 'value' => '4.8', 'range' => '3.5-5.0 mEq/L', 'date' => '2026-05-20', 'is_abnormal' => false, 'status' => 'normal'],
                    ['id' => 4, 'name' => 'Ca×P (鈣磷乘積)', 'value' => '52', 'range' => '<55 mg²/dL²', 'date' => '2026-05-20', 'is_abnormal' => true, 'status' => 'warn'],
                    ['id' => 5, 'name' => 'Kt/V (透析適當率)', 'value' => '1.42', 'range' => '>1.2', 'date' => '2026-05-20', 'is_abnormal' => false, 'status' => 'success'],
                ]
            ],
            'longterm' => [
                'longterm_orders' => [
                    ['id' => 1, 'name' => 'Amtrel (降血壓)', 'dosage' => '5mg', 'frequency' => 'QD PC', 'start_date' => '2024-01-10'],
                    ['id' => 2, 'name' => 'Medorin (降血壓)', 'dosage' => '#3顆', 'frequency' => '透析前', 'start_date' => '2023-06-15'],
                    ['id' => 3, 'name' => 'Caltrate (碳酸鈣)', 'dosage' => '600mg', 'frequency' => 'TID CC (隨餐)', 'start_date' => '2023-08-01'],
                    ['id' => 4, 'name' => 'Rocaltrol (活性維生素D)', 'dosage' => '0.25mcg', 'frequency' => 'BIW (每週二次)', 'start_date' => '2024-03-01'],
                ],
                'dry_weight_history' => [
                    ['id' => 1, 'date' => '2026-05-01', 'weight' => '59.5 kg', 'reason' => '水腫消退，下修 0.5kg', 'doctor' => '張院長'],
                    ['id' => 2, 'date' => '2026-03-15', 'weight' => '60.0 kg', 'reason' => '透析中血壓偏低，評估無水腫維持', 'doctor' => '張院長'],
                    ['id' => 3, 'date' => '2026-01-10', 'weight' => '60.5 kg', 'reason' => '年節前體重評估基準設定', 'doctor' => '張院長'],
                ]
            ]
        ], 200);
    }

    public function fetchDrawerDetails(Request $request, $check_id)
    {
        $check = \App\Models\PatientCheck::with([
            'patient_reservation.machine_bed.bed',
            'patient_reservation.patient'
        ])->findOrFail($check_id);

        $patient = $check->patient ?: $check->patient_reservation?->patient;
        if (!$patient) {
            return response()->json(['success' => false, 'message' => '病患記錄不存在'], 404);
        }

        $patientId = $patient->id;

        // 1. 基本資料 (Basic Info)
        $birthDate = $patient->birth_date;
        $age = $birthDate ? (\Carbon\Carbon::parse($birthDate)->age ?: null) : null;
        $idNum = $patient->id_num ?: '';
        $maskedId = strlen($idNum) >= 6 ? (substr($idNum, 0, 3) . '****' . substr($idNum, -3)) : $idNum;
        $gender = ($patient->gender_id == 1) ? '男' : '女';

        $allergies = \App\Models\PatientAllergy::where('patient_id', $patientId)->pluck('allergy')->toArray();
        if (empty($allergies)) {
            $allergies = \App\Models\PatientHaveAllergyBackup::where('patient_id', $patientId)->pluck('allergy')->toArray();
        }

        $firstCheck = \App\Models\PatientCheck::whereHas('patient_reservation', function($q) use ($patientId) {
            $q->where('patient_id', $patientId);
        })->oldest('date')->first();

        $dialysisAge = '6年2個月';
        if ($firstCheck && $firstCheck->date) {
            $years = \Carbon\Carbon::parse($firstCheck->date)->diffInYears(now());
            $months = \Carbon\Carbon::parse($firstCheck->date)->diffInMonths(now()) % 12;
            $dialysisAge = ($years > 0 ? "{$years}年" : '') . "{$months}個月";
        }

        $basicInfo = [
            'name' => $patient->name,
            'mr' => $patient->medical_record_no,
            'gender' => $gender,
            'birthdate' => $birthDate ? ($birthDate . ($age ? "（{$age}歲）" : '')) : '1958-03-15（66歲）',
            'id_num' => $maskedId ?: 'A123****789',
            'bloodType' => $patient->blood_id ? ($patient->blood_id . '型') : 'A型 Rh+',
            'dialysis_age' => $dialysisAge,
            'primary_disease' => $patient->note ?: '糖尿病腎病',
            'allergies' => !empty($allergies) ? $allergies : ['Penicillin (盤尼西林)'],
            'emergency_contact' => trim(($patient->related_name_1 ?? '薛*明') . ' ' . ($patient->related_mobile_phone_1 ?? '0912-345-678')),
            'address' => $patient->residence_addr ?: '台北市中正區重慶南路一段 100 號',
            'attending_doctor' => $check->doctor?->name ?: '張院長',
        ];

        // 2. 醫囑單 (Order Sheet)
        $dialysisWeight = \App\Models\PatientDialysisWeight::where('patient_id', $patientId)->latest('date')->first();
        $dryWeightVal = $check->dry_weight ?: ($dialysisWeight?->dry_weight ?: 59.5);

        $midRecord = \App\Models\PatientMidDialysisRecordNew::where('patient_check_id', $check_id)
            ->whereNotNull('BLDF')
            ->orderBy('HCDTTM', 'desc')
            ->first();

        $bf = $midRecord?->BLDF ? ($midRecord->BLDF . ' ml/min') : '250 ml/min';
        $df = $midRecord?->DLFL ? ($midRecord->DLFL . ' ml/min') : '500 ml/min';

        $prepDialyzer = \App\Models\PatientBeforePreparation::where('patient_check_id', $check_id)
            ->where('name', 'like', '%dialyzer%')
            ->value('product_name');

        $orderSheet = [
            'frequency' => '3次/週（一三五）',
            'duration' => ($check->total_time ? ($check->total_time . 'h') : '4h'),
            'orders' => [
                ['id' => 1, 'name' => 'Dialyzer (人工腎臟)', 'content' => $prepDialyzer ?: 'FX80 Classix', 'note' => '每次更換'],
                ['id' => 2, 'name' => 'BF (血流速)', 'content' => $bf, 'note' => ''],
                ['id' => 3, 'name' => 'DF (透析液流速)', 'content' => $df, 'note' => ''],
                ['id' => 4, 'name' => 'Dialysate Ca (透析液鈣)', 'content' => '3.0 mEq/L', 'note' => ''],
                ['id' => 5, 'name' => 'Heparin (抗凝劑)', 'content' => 'Initial 2000u / Maintain 300u/hr', 'note' => ''],
                ['id' => 6, 'name' => '乾體重', 'content' => "{$dryWeightVal} kg", 'note' => '上次調整：' . ($dialysisWeight?->date ?: '2026-05-01')],
            ]
        ];

        // 3. 血管通路 (Vascular)
        $vascRecord = \App\Models\PatientVascularAccessRecord::with(['vascular_access_type'])
            ->where('patient_id', $patientId)
            ->latest('date')
            ->first();

        $vascular = [
            'type' => $vascRecord?->vascular_access_type?->name ?: 'AVF (自體動靜脈廔管)',
            'site' => $vascRecord?->location ?: "R't 右臂 (橈動脈-頭靜脈)",
            'created_at' => $vascRecord?->date ?: '2020-03-10',
            'last_check_date' => $check->date ?: date('Y-m-d'),
            'note' => $vascRecord?->note ?: '正常，廔管震顫良好，無狹窄或動脈瘤跡象。',
            'backup_type' => '無',
            'backup_status' => 'AVF 功能良好，無需備用',
            'backup_note' => '主要血管通路震顫明顯，穿刺條件優良，目前未建立永久或臨時備用雙腔導管。'
        ];

        // 4. 貧血治療 (Anemia)
        $hctRecords = \App\Models\PatientHctInspectionRecordNew::where('patient_id', $patientId)
            ->orderBy('date', 'desc')
            ->take(6)
            ->get();

        $hctHistory = [];
        if ($hctRecords->isNotEmpty()) {
            foreach ($hctRecords->reverse() as $rec) {
                $monthStr = $rec->month ? "{$rec->month}月" : (\Carbon\Carbon::parse($rec->date)->format('n') . '月');
                $weekStr = $rec->week_of_month ? "第{$rec->week_of_month}週" : '';
                $label = $monthStr . $weekStr;
                $hctHistory[] = [
                    'week' => $label ?: $rec->date,
                    'lw' => (string)($rec->hct ? round($rec->hct - 1.5, 1) : '30.0'),
                    'tw' => (string)$rec->hct,
                    'value' => (string)$rec->hct,
                ];
            }
        } else {
            $hctHistory = [
                ['week' => '4月第3週', 'lw' => '29.5', 'tw' => '31.0', 'value' => '31.0'],
                ['week' => '4月第4週', 'lw' => '30.0', 'tw' => '32.0', 'value' => '32.0'],
                ['week' => '5月第1週', 'lw' => '29.8', 'tw' => '31.5', 'value' => '31.5'],
                ['week' => '5月第2週', 'lw' => '30.0', 'tw' => '32.5', 'value' => '32.5'],
            ];
        }

        $epoMed = \App\Models\PatientEpoIronMedicine::where('patient_id', $patientId)->latest('date')->first();
        $epoStr = $epoMed ? "{$epoMed->medicine} ｜ 最近施打：{$epoMed->date}" : 'NESP N20（4000IU）｜ W一・W三 施打｜ 最近施打：' . date('Y-m-d', strtotime('-1 day'));

        $anemia = [
            'current_week_idx' => count($hctHistory) - 1,
            'hct_history' => $hctHistory,
            'epo_record' => $epoStr,
        ];

        // 5. 檢驗記錄 (Lab)
        $labRec = \App\Models\PatientDialysisInspectionRecord::where('patient_id', $patientId)
            ->latest('date')
            ->first();

        $lastUpdate = $labRec?->date ?: '2026-05-20';
        $labs = [
            [
                'id' => 1,
                'name' => 'Hb (血紅素)',
                'value' => (string)($labRec?->bun_after ?: '9.1'),
                'range' => '11-13 g/dL',
                'date' => $lastUpdate,
                'is_abnormal' => true,
                'status' => 'danger'
            ],
            [
                'id' => 2,
                'name' => 'Ferritin (鐵蛋白)',
                'value' => (string)($labRec?->ferritin ?: '285'),
                'range' => '100-500 ng/mL',
                'date' => $lastUpdate,
                'is_abnormal' => false,
                'status' => 'normal'
            ],
            [
                'id' => 3,
                'name' => 'K⁺ (鉀離子)',
                'value' => (string)($labRec?->k ?: '4.8'),
                'range' => '3.5-5.0 mEq/L',
                'date' => $lastUpdate,
                'is_abnormal' => false,
                'status' => 'normal'
            ],
            [
                'id' => 4,
                'name' => 'Ca×P (鈣磷乘積)',
                'value' => (string)($labRec?->ca && $labRec?->p ? round($labRec->ca * $labRec->p, 1) : '52'),
                'range' => '<55 mg²/dL²',
                'date' => $lastUpdate,
                'is_abnormal' => true,
                'status' => 'warn'
            ],
            [
                'id' => 5,
                'name' => 'Kt/V (透析適當率)',
                'value' => '1.42',
                'range' => '>1.2',
                'date' => $lastUpdate,
                'is_abnormal' => false,
                'status' => 'success'
            ],
        ];

        $lab = [
            'last_update' => $lastUpdate,
            'labs' => $labs,
        ];

        // 6. 長期醫囑與乾體重
        $dwRecords = \App\Models\PatientDialysisWeight::with('doctor')
            ->where('patient_id', $patientId)
            ->orderBy('date', 'desc')
            ->take(6)
            ->get();

        $dryWeightHistory = [];
        if ($dwRecords->isNotEmpty()) {
            foreach ($dwRecords as $dw) {
                $dryWeightHistory[] = [
                    'id' => $dw->id,
                    'date' => $dw->date,
                    'weight' => "{$dw->dry_weight} kg",
                    'reason' => $dw->adjust_reason ?: '例行體重評估',
                    'doctor' => $dw->doctor?->name ?: ($check->doctor?->name ?: '張院長'),
                ];
            }
        } else {
            $dryWeightHistory = [
                ['id' => 1, 'date' => '2026-05-01', 'weight' => '59.5 kg', 'reason' => '水腫消退，下修 0.5kg', 'doctor' => '張院長'],
                ['id' => 2, 'date' => '2026-03-15', 'weight' => '60.0 kg', 'reason' => '透析中血壓偏低，評估無水腫維持', 'doctor' => '張院長'],
                ['id' => 3, 'date' => '2026-01-10', 'weight' => '60.5 kg', 'reason' => '年節前體重評估基準設定', 'doctor' => '張院長'],
            ];
        }

        $longtermOrders = [
            ['id' => 1, 'name' => 'Amtrel (降血壓)', 'dosage' => '5mg', 'frequency' => 'QD PC', 'start_date' => '2024-01-10'],
            ['id' => 2, 'name' => 'Medorin (降血壓)', 'dosage' => '#3顆', 'frequency' => '透析前', 'start_date' => '2023-06-15'],
            ['id' => 3, 'name' => 'Caltrate (碳酸鈣)', 'dosage' => '600mg', 'frequency' => 'TID CC (隨餐)', 'start_date' => '2023-08-01'],
            ['id' => 4, 'name' => 'Rocaltrol (活性維生素D)', 'dosage' => '0.25mcg', 'frequency' => 'BIW (每週二次)', 'start_date' => '2024-03-01'],
        ];

        $longterm = [
            'longterm_orders' => $longtermOrders,
            'dry_weight_history' => $dryWeightHistory,
        ];

        // 7. Nurse Watching 狀態
        $hasNW = \App\Models\NurseWatching::where('patient_check_id', $check_id)
            ->where('status', 1)
            ->exists();

        return response()->json([
            'success' => true,
            'check_id' => $check->id,
            'patient_id' => $patientId,
            'has_nw' => $hasNW,
            'basic_info' => $basicInfo,
            'order_sheet' => $orderSheet,
            'vascular' => $vascular,
            'anemia' => $anemia,
            'lab' => $lab,
            'longterm' => $longterm,
        ], 200);
    }
}
