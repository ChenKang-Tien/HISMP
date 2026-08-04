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

        // 🚀 根據班別篩選的動態資料集
        $shift = $request->query('shift', 'noon');
        
        $groupsMap = [
            'morning' => [
                ['name' => 'A 組・早班專用護理師', 'color' => '#0f766e', 'isMine' => true, 'patients' => [
                    ['bed' => '01', 'mr' => 'MR-M-01', 'name' => '早班-張小華', 'statusText' => '☀️ 早班 ・ 透析準備', 'orderCount' => 0, 'hasNW' => false, 'progress' => 10, 'isCrit' => false, 'vitals' => ['bp' => '120/80', 'pr' => '70', 'fs' => '100', 'qb' => '200']],
                    ['bed' => '02', 'mr' => 'MR-M-02', 'name' => '早班-李大明', 'statusText' => '☀️ 早班 ・ 透析中', 'orderCount' => 1, 'hasNW' => false, 'progress' => 40, 'isCrit' => false, 'vitals' => ['bp' => '130/85', 'pr' => '75', 'fs' => '110', 'qb' => '220']]
                ]]
            ],
            'noon' => [
                ['name' => 'A 組・楚心瑜護理師', 'color' => '#0f766e', 'isMine' => true, 'patients' => [
                    ['bed' => '01', 'mr' => 'MR9876543', 'name' => '薛玉鳳', 'statusText' => '🟢 午班 ・ 透析中 ・ 已透 2h 24m', 'orderCount' => 2, 'hasNW' => true, 'progress' => 60, 'isCrit' => false, 'vitals' => ['bp' => '135/82', 'pr' => '78', 'fs' => '142', 'qb' => '250']],
                    ['bed' => '02', 'mr' => 'MR223344', 'name' => '林*芳', 'statusText' => '🔴 午班 ・ 血壓偏高', 'orderCount' => 1, 'hasNW' => false, 'progress' => 40, 'isCrit' => true, 'vitals' => ['bp' => '190/110', 'pr' => '88', 'fs' => '118', 'qb' => '230']]
                ]],
                ['name' => 'C 組・午班實習護理師', 'color' => '#d97706', 'isMine' => false, 'patients' => [
                    ['bed' => '09', 'mr' => 'MR-N-09', 'name' => '午班-陳小美', 'statusText' => '🟢 午班 ・ 脫水中', 'orderCount' => 0, 'hasNW' => false, 'progress' => 50, 'isCrit' => false, 'vitals' => ['bp' => '115/75', 'pr' => '72', 'fs' => '95', 'qb' => '200']]
                ]]
            ],
            'night' => [
                ['name' => 'B 組・晚班輪值護理師', 'color' => '#7c3aed', 'isMine' => true, 'patients' => [
                    ['bed' => '08', 'mr' => 'MR-E-08', 'name' => '晚班-黃大偉', 'statusText' => '🌙 晚班 ・ 準備下機', 'orderCount' => 0, 'hasNW' => false, 'progress' => 90, 'isCrit' => false, 'vitals' => ['bp' => '125/78', 'pr' => '70', 'fs' => '98', 'qb' => '210']],
                    ['bed' => '10', 'mr' => 'MR-E-10', 'name' => '晚班-趙小莉', 'statusText' => '🌙 晚班 ・ 透析開始', 'orderCount' => 1, 'hasNW' => false, 'progress' => 5, 'isCrit' => false, 'vitals' => ['bp' => '120/75', 'pr' => '74', 'fs' => '102', 'qb' => '200']]
                ]]
            ],
            'all' => [
                ['name' => '全院監控', 'color' => '#64748b', 'isMine' => true, 'patients' => [
                    ['bed' => 'All', 'mr' => 'SYS', 'name' => '全院綜整', 'statusText' => '🌐 系統全班別連線中', 'orderCount' => 5, 'hasNW' => true, 'progress' => 0, 'isCrit' => false, 'vitals' => ['bp' => '--', 'pr' => '--', 'fs' => '--', 'qb' => '--']]
                ]]
            ]
        ];

        $activeGroups = $groupsMap[$shift] ?? $groupsMap['noon'];

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
