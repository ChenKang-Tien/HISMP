<?php

namespace App\Http\Controllers;

use App\Models\DoctorApAnother;
use App\Models\DoctorApEquipments;
use App\Models\DoctorApLaboratory;
use App\Models\DoctorApMedicine;
use App\Models\DoctorApScience;
use App\Models\MedicalEquipmen;
use App\Models\Medicine;
use App\Models\PatientBeforeAdjustWeight;
use App\Models\PatientCheck;
use App\Models\PatientDialysisMachineLong;
use App\Models\PatientDialysisMachineShort;
use App\Models\PatientDialysisWeight;
use App\Models\PatientHctInspectionRecord;
use App\Models\PatientHctInspectionRecordNew;
use App\Models\PatientVascularAccessRecord;
use App\Models\TodayCarePatient;
use App\Models\WeightAdjustItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckinController extends Controller
{
    public function getTodayPatients(Request $request)
    {
        // 1. 取得登入醫護人員的角色，可用於後端過濾班別或組別
        $user = $request->user();
        $isDoctor = $user->role === 'doctor';

        // 2. 模擬從資料庫（或 XAMPP 舊 HCIS 資料庫）撈取當日透析病患排班
        // 臨床實際狀況會依據今天日期、早午晚班進行 query
        $rawPatients = [
            [
                'bed_no' => '01',
                'name' => '張林阿敏',
                'mr_no' => '54321',
                'gender' => '女',
                'age' => 72,
                'pre_weight' => '62.5',
                'target_uf' => '2.5',
                'is_critical' => false,
                'status_text' => '透析中 · 第 3 小時 / 脫水 1.25L',
                'progress_pct' => 65,
                'time_elapsed' => '02:30',
                'time_total' => '04:00',
                'order_count' => 2,
                'has_nw' => true,
                'nw_alert' => true,
                'lab_alert' => true,
                'lab_k' => '6.1',
                'latest_bp' => '158/82',
                'latest_pr' => '74',
                'hb_val' => '10.2',
                'hb_status' => 'text-normal',
                'bp_alert' => true,
                'shift' => '午班',
                'group' => 'A組'
            ],
            [
                'bed_no' => '02',
                'name' => '陳大同',
                'mr_no' => '98765',
                'gender' => '男',
                'age' => 58,
                'pre_weight' => '70.2',
                'target_uf' => '3.0',
                'is_critical' => true, // 🚨 觸發臨床危急閃爍特效！
                'status_text' => '⚠️ 血壓驟降 · 立即處置中',
                'progress_pct' => 40,
                'time_elapsed' => '01:40',
                'time_total' => '04:00',
                'order_count' => 0,
                'has_nw' => false,
                'nw_alert' => false,
                'lab_alert' => false,
                'lab_k' => '4.2',
                'latest_bp' => '88/50', // 🚨 偏低
                'latest_pr' => '102',
                'hb_val' => '9.5',
                'hb_status' => 'text-danger',
                'bp_alert' => true,
                'shift' => '午班',
                'group' => 'A組'
            ],
            [
                'bed_no' => '03',
                'name' => '李美玲',
                'mr_no' => '11223',
                'gender' => '女',
                'age' => 64,
                'pre_weight' => '55.0',
                'target_uf' => '1.8',
                'is_critical' => false,
                'status_text' => '透析中 · 第 1 小時 / 脫水 0.4L',
                'progress_pct' => 25,
                'time_elapsed' => '01:00',
                'time_total' => '04:00',
                'order_count' => 1,
                'has_nw' => true,
                'nw_alert' => false,
                'lab_alert' => false,
                'lab_k' => '4.8',
                'latest_bp' => '132/78',
                'latest_pr' => '68',
                'hb_val' => '11.0',
                'hb_status' => 'text-normal',
                'bp_alert' => false,
                'shift' => '午班',
                'group' => 'B組'
            ]
        ];

        // 3. 直接回傳乾淨的 JSON 數據給前端 Vue
        return response()->json([
            'success' => true,
            'data' => $rawPatients
        ]);
    }
    //
    public function getCheckins()
    {
        // dd("test");
        $today = date('Y-m-d');
        $nurseId = Auth::user()->id;

        // 🔹 先取已照護過的病人 ID（避免重複）
        $ids = TodayCarePatient::where('date', $today)
            ->where('nurse_id', $nurseId)
            ->pluck('patient_check_id');

        // 🔹 一次撈出所有病患檢查（含關聯）
        $patientChecks = PatientCheck::with([
            'patient_reservation.patient',
            'patient_reservation.machine_bed.bed',
            'patient_reservation.machine_bed.card',
        ])
            ->join('patient_reservations', 'patient_checks.patient_reservation_id', '=', 'patient_reservations.id')
            ->join('bed_patient_cards', 'patient_reservations.machine_bed_id', '=', 'bed_patient_cards.id')
            ->join('beds', 'bed_patient_cards.bed_id', '=', 'beds.id')
            ->where('patient_checks.date', $today)
            ->where('patient_checks.status', '!=', 5)
            ->whereHas('patient_reservation', function ($q) {
                $q->where('patient_id', '!=', 0)
                    ->whereNotIn('status', [1, 2]); // 排除請假/住院
            })
            ->whereNotIn('patient_checks.id', $ids)
            ->orderBy('patient_reservations.morning_noon_night')
            ->orderBy('beds.bed_no')
            ->select('patient_checks.*')
            ->get();

        if ($patientChecks->isEmpty()) {
            return response()->json(['status' => 200, 'patientChecks' => []]);
        }

        // 🔹 批次預取所有會用到的病人 ID
        $patientIds = $patientChecks->pluck('patient_reservation.patient_id')->unique()->toArray();

        $checkIds = $patientChecks->pluck('id')->toArray();

        // ===== 一次撈出相關資料，並用 groupBy 整理 =====

        $weights = PatientDialysisWeight::whereIn('patient_id', $patientIds)
            ->latest('id')
            ->get()
            ->groupBy('patient_id');

        $adjustWeights = PatientBeforeAdjustWeight::with('item')
            ->whereIn('patient_check_id', $checkIds)
            ->get()
            ->groupBy('patient_check_id');

        $vascular = PatientVascularAccessRecord::with('vascular_access_type')
            ->whereIn('patient_id', $patientIds)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('patient_id');

        $machinesShort = PatientDialysisMachineShort::whereIn('patient_id', $patientIds)
            ->whereNull('end_date')
            ->get()
            ->groupBy('patient_id');

        $machinesLong = PatientDialysisMachineLong::whereIn('patient_id', $patientIds)
            ->whereNull('end_date')
            ->get()
            ->groupBy('patient_id');

        $hctRecords = PatientHctInspectionRecordNew::whereIn('patient_id', $patientIds)
            ->whereBetween('date', [
                date('Y-m-d', strtotime('monday this week')),
                date('Y-m-d', strtotime('sunday this week')),
            ])
            ->get()
            ->groupBy('patient_id');

        // 快取設備與藥品對照表
        $equipmentMap = MedicalEquipmen::all()->keyBy('id');
        $medicineMap = Medicine::all()->keyBy('id');

        // ===== 開始組裝輸出資料 =====
        $CheckArray = [];

        foreach ($patientChecks as $check) {
            $r = $check->patient_reservation;
            $p = $r->patient;
            if (!$p) continue;

            $obj = (object)[];

            // 基本資料
            $obj->id = $check->id;
            $obj->patient_id = $p->id;
            $obj->name = $p->name;
            $obj->img = $p->image_path ?? null;

            // HCT
            $obj->hct = optional($hctRecords[$p->id] ?? collect())->first()?->hct ?? null;

            // 體重（含調整）
            $rows = $adjustWeights[$check->id] ?? collect();

            $adjustTotal = PatientBeforeAdjustWeight::where('patient_check_id', $check->id)->sum('weight');

            $obj->measure_weight_before = $check->measure_weight_before;
            $obj->adjust_total = $adjustTotal;
            $obj->weight = $check->measure_weight_before + $adjustTotal;

            $obj->adjust_items = $rows->map(function ($w) {
                return [
                    'id' => $w->id,
                    'item_id' => $w->item_id,
                    'item_name' => $w->item?->item,
                    'default_weight' => $w->item?->default_weight,
                    'way_add' => $w->way_add,
                    'weight' => $w->weight,
                ];
            })->values();

            $obj->status = $check->status;

            // 班別
            $shift = $r->morning_noon_night;
            $obj->morning_noon_night = ['早', '中', '晚'][$shift] ?? '—';

            // 床位與卡號
            $obj->bed_no = $r->machine_bed->bed->bed_no ?? '—';
            $obj->card_no = $r->machine_bed->card->no ?? '—';

            // 機器設定
            $shorts = $machinesShort[$p->id] ?? collect();
            $longs = $machinesLong[$p->id] ?? collect();

            // AK
            $ak = $shorts->where('dialysis_machine_id', 1)->first() ?? $longs->where('dialysis_machine_id', 1)->first();
            $obj->ak = $ak ? ($equipmentMap[$ak->value]->product_name ?? '無AK') : '無AK';

            // CA
            $na_k_ca = $shorts->where('dialysis_machine_id', 6)->first() ?? $longs->where('dialysis_machine_id', 6)->first();
            if ($na_k_ca) {
                $m = $medicineMap[$na_k_ca->value] ?? null;
                if ($m && isset($m->na_k_ca)) {
                    $parts = explode(",", $m->na_k_ca);
                    $ca = isset($parts[2]) ? explode(":", $parts[2])[1] : null;
                    $obj->ca = sprintf("%.1f", round(floatval($ca), 1));
                } else {
                    $obj->ca = "無ca";
                }
            } else {
                $obj->ca = "無ca";
            }

            // Heparin
            $hfi = $shorts->where('dialysis_machine_id', 9)->first() ?? $longs->where('dialysis_machine_id', 9)->first();
            if ($hfi) {
                $map = [1 => 'Heparin', 2 => 'Fragmin', 3 => 'Innohep'];
                $obj->heparin = $map[$hfi->value] ?? '未知';
                foreach ([10 => 'Initial', 11 => 'Priming', 12 => 'Maintain'] as $id => $label) {
                    $val = $shorts->where('dialysis_machine_id', $id)->first()?->value
                        ?? $longs->where('dialysis_machine_id', $id)->first()?->value;
                    if ($val) $obj->heparin .= "\n{$label}:{$val}";
                }
            } else {
                $obj->heparin = "無heparin";
            }

            // 血管通路
            $vas = optional($vascular[$p->id] ?? collect())->first();
            $obj->vascularAccess = $vas?->vascular_access_type?->name ?? "無血管通路";

            // 簽章狀態
            $obj->sign = $check->prepare_nurse_id ? 1 : 0;
            $obj->isDouble_check = ($check->check_nurse_id != null || $check->prepare_nurse_id == Auth::user()->id) ? 1 : 0;

            $CheckArray[] = $obj;
        }

        return response()->json([
            'status' => 200,
            'patientChecks' => $CheckArray,
        ]);
    }


    public function getCareCheckins()
    {
        $today = date('Y-m-d');
        $nurseId = Auth::user()->id;

        // 🔹 已照護的病人 patient_check_id
        $ids = TodayCarePatient::where('date', $today)
            ->where('nurse_id', $nurseId)
            ->pluck('patient_check_id');

        if ($ids->isEmpty()) {
            return response()->json(['patientChecks' => []]);
        }

        // 🔹 一次載入所有關聯資料
        $patientChecks = PatientCheck::with([
            'patient_reservation.patient',
            'patient_reservation.machine_bed.bed',
            'patient_reservation.machine_bed.card',
        ])
            ->where('patient_checks.date', $today)
            ->whereHas('patient_reservation', function ($q) {
                $q->where('patient_id', '!=', 0)
                ->whereNotIn('status', [1, 2]);
            })
            ->whereIn('patient_checks.id', $ids)
            ->get();

        // 🔹 預取所有病患 ID
        $patientIds = $patientChecks->pluck('patient_reservation.patient_id')->unique()->toArray();

        $checkIds = $patientChecks->pluck('id')->toArray();

        // ===== 批次預撈所有關聯資料 =====

        $weights = PatientDialysisWeight::whereIn('patient_id', $patientIds)
            ->latest('id')
            ->get()
            ->groupBy('patient_id');

        $adjustWeights = PatientBeforeAdjustWeight::with('item')
            ->whereIn('patient_check_id', $checkIds)
            ->get()
            ->groupBy('patient_check_id');

        $hctRecords = PatientHctInspectionRecordNew::whereIn('patient_id', $patientIds)
            ->whereBetween('date', [
                date('Y-m-d', strtotime('monday this week')),
                date('Y-m-d', strtotime('sunday this week')),
            ])
            ->get()
            ->groupBy('patient_id');

        $machinesShort = PatientDialysisMachineShort::whereIn('patient_id', $patientIds)
            ->whereNull('end_date')
            ->get()
            ->groupBy('patient_id');

        $machinesLong = PatientDialysisMachineLong::whereIn('patient_id', $patientIds)
            ->whereNull('end_date')
            ->get()
            ->groupBy('patient_id');

        $vascular = PatientVascularAccessRecord::with('vascular_access_type')
            ->whereIn('patient_id', $patientIds)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('patient_id');

        $equipmentMap = MedicalEquipmen::all()->keyBy('id');
        $medicineMap = Medicine::all()->keyBy('id');

        // ===== 批次撈醫囑未執行數（五種類型合併計算） =====
        $apCounts = collect();
        foreach ([
            DoctorApScience::class,
            DoctorApLaboratory::class,
            DoctorApEquipments::class,
            DoctorApMedicine::class,
            DoctorApAnother::class
        ] as $model) {
            $model::whereIn('patient_check_id', $ids)
                ->where('nurse_status', 0)
                ->selectRaw('patient_check_id, COUNT(*) as cnt')
                ->groupBy('patient_check_id')
                ->get()
                ->each(function ($row) use (&$apCounts) {
                    $apCounts[$row->patient_check_id] = ($apCounts[$row->patient_check_id] ?? 0) + $row->cnt;
                });
        }

        // ===== 組裝資料 =====
        $CheckArray = [];

        foreach ($patientChecks as $check) {
            $r = $check->patient_reservation;
            $p = $r->patient;
            if (!$p) continue;

            $obj = (object)[];
            $obj->id = $check->id;
            $obj->patient_id = $p->id;
            $obj->name = $p->name;
            $obj->img = $p->image_path;

            // HCT
            $obj->hct = optional($hctRecords[$p->id] ?? collect())->first()?->hct ?? null;

            // 調整前體重
            $rows = $adjustWeights[$check->id] ?? collect();

            $adjustTotal = PatientBeforeAdjustWeight::where('patient_check_id', $check->id)->sum('weight');

            $obj->measure_weight_before = $check->measure_weight_before;
            $obj->adjust_total = $adjustTotal;
            $obj->weight = $check->measure_weight_before + $adjustTotal;

            $obj->adjust_items = $rows->map(function ($w) {
                return [
                    'id' => $w->id,
                    'item_id' => $w->item_id,
                    'item_name' => $w->item?->item,
                    'default_weight' => $w->item?->default_weight,
                    'way_add' => $w->way_add,
                    'weight' => $w->weight,
                ];
            })->values();

            $obj->status = $check->status;

            // 乾體重 / 水重
            $dialysisWeight = optional($weights[$p->id] ?? collect())->first();
            if ($dialysisWeight) {
                $obj->dry_weight = $dialysisWeight->dry_weight;
                $obj->water = $obj->measure_weight_before ? $obj->measure_weight_before - $dialysisWeight->dry_weight : null;
            } else {
                $obj->dry_weight = null;
                $obj->water = null;
            }

            // 醫囑數
            $obj->ap_count = $apCounts[$check->id] ?? 0;

            // 班別
            $obj->morning_noon_night = ['早', '中', '晚'][$r->morning_noon_night] ?? '—';

            // 床位與卡號
            $obj->bed_no = $r->machine_bed->bed->bed_no ?? '—';
            $obj->card_no = $r->machine_bed->card->no ?? '—';

            // 機器設定
            $shorts = $machinesShort[$p->id] ?? collect();
            $longs = $machinesLong[$p->id] ?? collect();

            // AK
            $ak = $shorts->where('dialysis_machine_id', 1)->first() ?? $longs->where('dialysis_machine_id', 1)->first();
            $obj->ak = $ak ? ($equipmentMap[$ak->value]->product_name ?? '無ak') : '無ak';

            // CA
            $na_k_ca = $shorts->where('dialysis_machine_id', 6)->first() ?? $longs->where('dialysis_machine_id', 6)->first();
            if ($na_k_ca) {
                $m = $medicineMap[$na_k_ca->value] ?? null;
                if ($m && isset($m->na_k_ca)) {
                    $parts = explode(",", $m->na_k_ca);
                    $ca = isset($parts[2]) ? explode(":", $parts[2])[1] : null;
                    $obj->ca = sprintf("%.1f", round(floatval($ca), 1));
                } else {
                    $obj->ca = "無ca";
                }
            } else {
                $obj->ca = "無ca";
            }

            // Heparin
            $hfi = $shorts->where('dialysis_machine_id', 9)->first() ?? $longs->where('dialysis_machine_id', 9)->first();
            if ($hfi) {
                $map = [1 => 'Heparin', 2 => 'Fragmin', 3 => 'Innohep'];
                $obj->heparin = $map[$hfi->value] ?? '未知';
                foreach ([10 => 'Initial', 11 => 'Priming', 12 => 'Maintain'] as $id => $label) {
                    $val = $shorts->where('dialysis_machine_id', $id)->first()?->value
                        ?? $longs->where('dialysis_machine_id', $id)->first()?->value;
                    if ($val) $obj->heparin .= "\n{$label}:{$val}";
                }
            } else {
                $obj->heparin = "無heparin";
            }

            // 血管通路
            $vas = optional($vascular[$p->id] ?? collect())->first();
            $obj->vascularAccess = $vas?->vascular_access_type?->name ?? "無血管通路";

            // 簽章
            $obj->sign = $check->prepare_nurse_id ? 1 : 0;
            $obj->isDouble_check = ($check->check_nurse_id != null || $check->prepare_nurse_id == Auth::user()->id) ? 1 : 0;

            $CheckArray[] = $obj;
        }

        return response()->json([
            'patientChecks' => $CheckArray,
        ]);
    }
}
