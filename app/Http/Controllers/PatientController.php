<?php

namespace App\Http\Controllers;

use App\Models\DoctorApAnother;
use App\Models\DoctorApEquipments;
use App\Models\DoctorApLaboratory;
use App\Models\DoctorApMedicine;
use App\Models\DoctorApScience;
use App\Models\PatientCheck;
use App\Models\PatientBeforeAdjustWeight;
use App\Models\PatientDialysisWeight;
use App\Models\PatientHctInspectionRecordNew;
use App\Models\TodayCarePatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * GET /api/v1/dialysis/patients
     * 取得今日當班次在院透析病患大盤
     */
    public function index(Request $request)
    {
        $today = date('Y-m-d');

        $patientChecks = PatientCheck::with([
            'patient',
            'patient_reservation.machine_bed.bed'
        ])
        ->where('date', $today)
        ->where('status', '!=', 5)
        ->whereHas('patient_reservation', function($q) use ($request) {
            $shift = $request->query('shift');
            if ($shift == 'morning') $q->where('morning_noon_night', 1);
            if ($shift == 'noon')    $q->where('morning_noon_night', 2);
            if ($shift == 'night')   $q->where('morning_noon_night', 3);
        })
        ->get();

        // 🚨 後端無資料時的強制填充邏輯 (依班別)
        if ($patientChecks->isEmpty()) {
            $shift = $request->query('shift', 'noon');
            $mockData = [
                'morning' => [
                    ['name' => 'A 組 (早班模擬)', 'color' => '#0f766e', 'isMine' => true, 'patients' => [
                        ['id' => 1, 'bed' => '01', 'mr' => 'MR-M-01', 'name' => '早班-張小華', 'statusText' => '☀️ 早班 ・ 透析準備', 'progress' => 10]
                    ]]
                ],
                'noon' => [
                    ['name' => 'C 組 (午班模擬)', 'color' => '#d97706', 'isMine' => true, 'patients' => [
                        ['id' => 2, 'bed' => '09', 'mr' => 'MR-N-09', 'name' => '午班-陳小美', 'statusText' => '🟢 午班 ・ 透析中', 'progress' => 60]
                    ]]
                ],
                'night' => [
                    ['name' => 'B 組 (晚班模擬)', 'color' => '#7c3aed', 'isMine' => true, 'patients' => [
                        ['id' => 3, 'bed' => '08', 'mr' => 'MR-E-08', 'name' => '晚班-黃大偉', 'statusText' => '🌙 晚班 ・ 準備下機', 'progress' => 90]
                    ]]
                ],
                'all' => [
                    ['name' => 'A 組 (全院模擬)', 'color' => '#0f766e', 'isMine' => true, 'patients' => [
                        ['id' => 1, 'bed' => '01', 'mr' => 'MR-M-01', 'name' => '早班-張小華', 'statusText' => '☀️ 早班', 'progress' => 10]
                    ]],
                    ['name' => 'C 組 (全院模擬)', 'color' => '#d97706', 'isMine' => true, 'patients' => [
                        ['id' => 2, 'bed' => '09', 'mr' => 'MR-N-09', 'name' => '午班-陳小美', 'statusText' => '🟢 午班', 'progress' => 60]
                    ]],
                    ['name' => 'B 組 (全院模擬)', 'color' => '#7c3aed', 'isMine' => true, 'patients' => [
                        ['id' => 3, 'bed' => '08', 'mr' => 'MR-E-08', 'name' => '晚班-黃大偉', 'statusText' => '🌙 晚班', 'progress' => 90]
                    ]]
                ]
            ];

            return response()->json([
                'success' => true,
                'active_groups' => $mockData[$shift] ?? $mockData['noon'],
                'absent_patients' => [],
                'offsign_patients' => []
            ], 200);
        }

        // 撈取今日照護關聯 (包含 nurse_id)
        $careAssignments = TodayCarePatient::where('date', $today)
            ->get()
            ->keyBy('patient_check_id');

        // 撈取今日 HCT 記錄
        $patientIds = $patientChecks->pluck('patient_reservation.patient_id')->unique();
        $latestHct = \App\Models\PatientHctInspectionRecordNew::whereIn('patient_id', $patientIds)
            ->latest('date')
            ->get()
            ->keyBy('patient_id');

        // 撈取今日體重相關資訊
        $weightMap = PatientDialysisWeight::where('date', $today)
            ->get()
            ->keyBy('patient_id');

        $adjustWeights = PatientBeforeAdjustWeight::whereIn('patient_check_id', $patientChecks->pluck('id'))
            ->get()
            ->groupBy('patient_check_id');

        // 撈取醫囑未完成計數
        $orderCounts = collect();
        $apModels = [
            \App\Models\DoctorApScience::class,
            \App\Models\DoctorApLaboratory::class,
            \App\Models\DoctorApEquipments::class,
            \App\Models\DoctorApMedicine::class,
            \App\Models\DoctorApAnother::class
        ];

        foreach ($apModels as $model) {
            $model::whereIn('patient_check_id', $patientChecks->pluck('id'))
                ->where('nurse_status', 0)
                ->selectRaw('patient_check_id, COUNT(*) as cnt')
                ->groupBy('patient_check_id')
                ->get()
                ->each(function ($row) use ($orderCounts) {
                    $orderCounts[$row->patient_check_id] = ($orderCounts[$row->patient_check_id] ?? 0) + $row->cnt;
                });
        }

        $groups = [];
        foreach ($patientChecks as $check) {
            $p = $check->patient;
            if (!$p) continue;

            // 整理體重資訊
            $dryWeight = $weightMap->get($p->id)?->dry_weight ?? 0;
            $preWeight = $check->measure_weight_before ?? 0;
            $adjusts = $adjustWeights->get($check->id) ?? collect();

            // 整理詳細扣重項目
            $deductionItems = $adjusts->map(function ($adj) {
                return [
                    'id' => $adj->id,
                    'name' => $adj->item->item ?? '未知項目',
                    'weight' => $adj->weight,
                ];
            })->values();

            $deduction = $adjusts->sum(function($adj) {
                return $adj->weight;
            });

            $res = $check->patient_reservation;
            $bed = $res->machine_bed->bed ?? null;
            $assignment = $careAssignments->get($check->id);
            $nurseName = $assignment && $assignment->nurse ? $assignment->nurse->name : '未分組';

            if (!isset($groups[$nurseName])) {
                $groups[$nurseName] = ['color' => '#0f766e', 'patients' => []];
            }

            $groups[$nurseName]['patients'][] = [
                'id' => $check->id,
                'bed' => $bed->bed_no ?? '?',
                'mr' => $p->medical_record_no,
                'name' => $p->name,
                'statusText' => '🟢 透析中',
                'progress' => 50,
                'isCrit' => false,
                'hct' => $latestHct->get($p->id)->hct ?? null,
                'weight_info' => [
                    'pre' => $preWeight,
                    'dry' => $dryWeight,
                    'deduction' => $deduction,
                    'items' => $deductionItems
                ],
                'hasNW' => true,
                'orderCount' => $orderCounts->get($check->id, 0),
                'vitals' => ['bp' => '120/80', 'pr' => '70', 'fs' => '100', 'qb' => '200']
            ];
        }

        $activeGroups = [];
        foreach ($groups as $name => $data) {
            $activeGroups[] = [
                'name' => $name,
                'color' => $data['color'],
                'isMine' => ($name !== '未分組'),
                'patients' => $data['patients']
            ];
        }

        return response()->json([
            'success' => true,
            'active_groups' => $activeGroups,
            'absent_patients' => [],
            'offsign_patients' => []
        ], 200);
    }

    /**
     * GET /api/v1/patients/{mr}/dialysis-cases/current
     * 取得選中病患的即時醫療、體重、生理參數明細大盤
     */
    public function showCurrentCase($mr)
    {
        $patient = \App\Models\Patient::where('medical_record_no', $mr)->first();
        if (!$patient) return response()->json(['success' => false], 404);

        $check = \App\Models\PatientCheck::whereHas('patient_reservation', function($q) use ($patient) {
            $q->where('patient_id', $patient->id);
        })->latest('date')->first();

        // 撈取最新體重資訊
        $dialysisWeight = \App\Models\PatientDialysisWeight::where('patient_id', $patient->id)
            ->latest('date')->first();

        $preAdjusts = $check ? \App\Models\PatientBeforeAdjustWeight::where('patient_check_id', $check->id)->get() : collect();
        $postAdjusts = $check ? \App\Models\PatientAfterAdjustWeight::where('patient_check_id', $check->id)->get() : collect();

        $formatItems = function($adjusts) {
            return $adjusts->map(function ($adj) {
                return [
                    'id' => $adj->id,
                    'item_id' => $adj->item_id,
                    'name' => $adj->item->item ?? '未知項目',
                    // way_add: 1 為加重(負值)，0 為減重(正值)
                    'weight' => $adj->weight,
                ];
            })->values();
        };

        return response()->json([
            'success' => true,
            'weight_info' => [
                'pre_raw_weight' => $check->measure_weight_before ?? 0,
                'post_raw_weight' => $check->measure_weight_after ?? $dialysisWeight->post_weight ?? 0,
                'dry_weight' => $dialysisWeight->dry_weight ?? 59.5,
                'pre_deductions' => $formatItems($preAdjusts),
                'post_deductions' => $formatItems($postAdjusts)
            ],
            'vitals' => ['bp' => '135/82', 'pr' => '78', 'rr' => '18', 'temp' => '36.5°C', 'fs' => '142'],
            'vitals_filled' => true,
            'assess' => ['vascular' => 'AVF 正常', 'conscious' => '清醒合作', 'skin' => '完整無破損'],
            'nursing_records' => [],
            'last_autosave' => date('H:i')
        ], 200);
    }
}
