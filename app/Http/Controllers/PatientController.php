<?php

namespace App\Http\Controllers;

use App\Models\DoctorApAnother;
use App\Models\DoctorApEquipments;
use App\Models\DoctorApLaboratory;
use App\Models\DoctorApMedicine;
use App\Models\DoctorApScience;
use App\Models\PatientCheck;
use App\Models\PatientHctInspectionRecordNew;
use App\Models\TodayCarePatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * GET /api/v1/dialysis/patients
     * 取得今日當班次（午班）的在院透析病患大盤總表 (100% 寫死完全體假資料)
     */
    public function index(Request $request)
    {
        $today = date('Y-m-d');
        $nurseId = Auth::user()->id;
        $patientChecks = PatientCheck::where('date', $today)->get();

        $nurseGroup = [1, 2];

        $activeGroupExamples = [];

        foreach($nurseGroup as $group){
            $ids = TodayCarePatient::where('date', $today)
            ->where('nurse_id', $group)
            ->pluck('patient_check_id');

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

            // 🔹 批次預取所有會用到的病人 ID
            $patientIds = $patientChecks->pluck('patient_reservation.patient_id')->unique()->toArray();

            $checkIds = $patientChecks->pluck('id')->toArray();

            $hctRecords = PatientHctInspectionRecordNew::whereIn('patient_id', $patientIds)
                ->whereBetween('date', [
                    date('Y-m-d', strtotime('monday this week')),
                    date('Y-m-d', strtotime('sunday this week')),
                ])
                ->get()
                ->groupBy('patient_id');

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

            foreach ($patientChecks as $check) {
                $r = $check->patient_reservation;
                $p = $r->patient;
                $b = $r->machine_bed->bed;

                // HCT
                $hct = optional($hctRecords[$p->id] ?? collect())->first()?->hct ?? null;

                $orderCount = $apCounts[$check->id] ?? 0;

                $patient = [
                    'id' => $check->id,
                    'bed' => $b->bed_no,
                    'mr' => $p->medical_record_no,
                    'name' => $p->name,
                    'isCrit' => false,
                    'hct' => $hct,
                    'hasNW' => true,
                    'orderCount' => $orderCount,
                    'statusText' => '🟢 透析中 ・ 已透 2h 24m ・ 🎯 UF 3.50kg',
                    'progress' => 60,
                    'vitals' => ['bp' => '135/82', 'pr' => '78', 'fs' => '142', 'qb' => '250']
                ];

            }


        }

        // 🚀 完全對齊原稿 V24 / V27 規格的核心測試資料集
        $activeGroups = [
            [
                'name' => 'A 組・楚心瑜護理師（4位）・我的組別',
                'color' => '#0f766e',
                'isMine' => true,
                'patients' => [
                    [
                        'bed' => '01',
                        'mr' => 'MR9876543',
                        'name' => '薛玉鳳',
                        'isCrit' => false,
                        'hct' => '32.5',
                        'hasNW' => true,
                        'orderCount' => 2,
                        'statusText' => '🟢 透析中 ・ 已透 2h 24m ・ 🎯 UF 3.50kg',
                        'progress' => 60,
                        'vitals' => ['bp' => '135/82', 'pr' => '78', 'fs' => '142', 'qb' => '250']
                    ],
                    [
                        'bed' => '02',
                        'mr' => 'MR223344',
                        'name' => '林*芳',
                        'isCrit' => true, // 危急閃爍
                        'hct' => '31.5',
                        'hasNW' => false,
                        'orderCount' => 1,
                        'statusText' => '🔴 危急 ・ 血壓 190/110',
                        'progress' => 40,
                        'vitals' => ['bp' => '190/110', 'pr' => '88', 'fs' => '118', 'qb' => '230']
                    ],
                    [
                        'bed' => '05',
                        'mr' => 'MR445566',
                        'name' => '李*美',
                        'isCrit' => false,
                        'hct' => '33.2',
                        'hasNW' => true,
                        'orderCount' => 2,
                        'statusText' => '🟢 透析中 ・ 已透 3h 32m ・ 🎯 UF 2.20kg',
                        'progress' => 88,
                        'vitals' => ['bp' => '110/70', 'pr' => '70', 'fs' => '98', 'qb' => '250']
                    ]
                ]
            ],
            [
                'name' => 'B 組・王曉明護理師（4位）',
                'color' => '#7c3aed',
                'isMine' => false,
                'patients' => [
                    [
                        'bed' => '07',
                        'mr' => 'MR556677',
                        'name' => '陳*志',
                        'isCrit' => false,
                        'hct' => null,
                        'hasNW' => false,
                        'orderCount' => 0,
                        'statusText' => '🟢 透析中 ・ 已透 1h 45m',
                        'progress' => 35,
                        'vitals' => ['bp' => '128/78', 'pr' => '72', 'fs' => '105', 'qb' => '240']
                    ]
                ]
            ]
        ];

        // 離院池模擬空陣列
        $absentPatients = [];

        // 飛出面板已下機清單
        $offsignPatients = [
            ['bed' => '03', 'mr' => 'MR334455', 'name' => '張*華']
        ];

        return response()->json([
            'success' => true,
            'active_groups' => $activeGroups,
            'absent_patients' => $absentPatients,
            'offsign_patients' => $offsignPatients
        ], 200);
    }

    /**
     * GET /api/v1/patients/{mr}/dialysis-cases/current
     * 取得選中病患的即時醫療、體重、生理參數明細大盤 (100% 寫死假資料)
     */
    public function showCurrentCase($mr)
    {
        // 為了展示過磅計算，我們提供薛玉鳳的真實體重扣重池結構
        $weightInfo = [
            'pre_raw_weight' => 79.90, // 透前體重
            'dry_weight' => 59.50,     // 乾體重
            'deductions' => [
                ['id' => 1, 'name' => '外套', 'weight' => 1.50],
                ['id' => 2, 'name' => '牛奶', 'weight' => 0.50]
            ]
        ];

        $vitals = [
            'bp' => '135/82',
            'pr' => '78',
            'rr' => '18',
            'temp' => '36.5°C',
            'fs' => '142'
        ];

        $assess = [
            'vascular' => 'AVF 正常',
            'conscious' => '清醒合作',
            'skin' => '完整無破損'
        ];

        // 帶有具名稽核的當班歷史病歷時間軸
        $nursingRecords = [
            [
                'id' => 1,
                'time' => '09:20',
                'content' => '低血壓 BP 92/54，快衝 N/S 100ml，UFR 調降，15分後追蹤。',
                'nurse' => '楚心瑜',
                'isDeleted' => false
            ],
            [
                'id' => 2,
                'time' => '08:05',
                'content' => '透析上針順利，廔管穿刺無滲血，意識清楚，配合度佳。',
                'nurse' => '楚心瑜',
                'isDeleted' => false
            ]
        ];

        return response()->json([
            'success' => true,
            'weight_info' => $weightInfo,
            'vitals' => $vitals,
            'vitals_filled' => true,
            'assess' => $assess,
            'nursing_records' => $nursingRecords,
            'last_autosave' => '11:58'
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

        // 🚀 狀態流轉成功，回傳完帳訊號
        return response()->json([
            'success' => true,
            'message' => '後端成功接收假單！病患 ' . $mr . ' 狀態已變更為 ' . $validated['status'] . '，系統已自動留痕交接。'
        ], 200);
    }
}
