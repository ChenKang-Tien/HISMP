<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientCheck;
use App\Models\PatientDialysisInspectionRecord;
use App\Models\PatientDialysisWeight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorPatientController extends Controller
{
    /**
     * GET /doctor/patients
     * 回傳今日班表 + 病患列表（含狀態/床號/班別/KPI 摘要）
     */
    public function index(Request $request)
    {
        $today = date('Y-m-d');
        $shift = $request->query('shift'); // 可選篩選：0=早,1=中,2=晚

        // 查詢今日有排程的病患 check
        $query = PatientCheck::with([
            'patient_reservation.patient.gender',
            'patient_reservation.machine_bed.bed',
            'patient_reservation.machine_bed.card',
        ])
            ->where('patient_checks.date', $today)
            ->where('patient_checks.status', '!=', 5)
            ->leftJoin('patient_reservations', 'patient_checks.patient_reservation_id', '=', 'patient_reservations.id')
            ->where('patient_reservations.patient_id', '!=', 0)
            ->whereNotIn('patient_reservations.status', [1, 2]);

        // 可選班別篩選
        if ($shift !== null && in_array((int)$shift, [0, 1, 2], true)) {
            $query->where('patient_reservations.morning_noon_night', (int)$shift);
        }

        $patientChecks = $query->orderBy('patient_reservations.morning_noon_night')
            ->orderByRaw('(SELECT bed_no FROM beds WHERE id = (SELECT bed_id FROM bed_patient_cards WHERE id = patient_reservations.machine_bed_id))')
            ->select('patient_checks.*')
            ->get();

        if ($patientChecks->isEmpty()) {
            return response()->json([
                'status' => 200,
                'patients' => [],
            ]);
        }

        // 收集 patient IDs 以便批次查詢 KPI 與乾體重
        $patientIds = $patientChecks->pluck('patient_reservation.patient_id')->unique()->toArray();

        // 最新檢驗記錄（KPI）
        $latestInspections = PatientDialysisInspectionRecord::whereIn('patient_id', $patientIds)
            ->where('deleted', 0)
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('patient_id')
            ->map->first();

        // 最新乾體重
        $latestWeights = PatientDialysisWeight::whereIn('patient_id', $patientIds)
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('patient_id')
            ->map->first();

        $patients = [];

        foreach ($patientChecks as $check) {
            $reservation = $check->patient_reservation;
            $patient = $reservation->patient;
            if (!$patient) continue;

            $inspection = $latestInspections->get($patient->id);
            $weight = $latestWeights->get($patient->id);

            // 計算年齡
            $age = null;
            if ($patient->birth_date) {
                $age = \Carbon\Carbon::parse($patient->birth_date)->age;
            }

            // 性別名稱
            $genderName = $patient->gender ? $patient->gender->name : null;

            // 班別標籤
            $shiftLabels = [0 => '早', 1 => '中', 2 => '晚'];
            $shiftLabel = $shiftLabels[$reservation->morning_noon_night ?? 0] ?? '早';

            // 狀態文字對應
            $statusMap = [
                0 => 'waiting',      // 等待
                3 => 'dialyzing',    // 透析中
                4 => 'interrupted',  // 中斷
                5 => 'completed',    // 已完成（已在 where 排除）
            ];

            // 若已上機（有 start_time）且未結束 → dialyzing
            $status = 'waiting';
            if ($check->start_time && !$check->end_time) {
                $status = 'dialyzing';
            } elseif ($check->end_time) {
                $status = 'completed';
            } elseif ($check->status == 4) {
                $status = 'interrupted';
            }

            // KPI 值（列表只帶 Kt/V 等摘要）
            $ktv = null;
            if ($inspection) {
                $ktv = $inspection->kt_v_gotch ?? $inspection->daugirdas ?? null;
                if ($ktv !== null) $ktv = round((float)$ktv, 2);
            }

            // is_critical 判斷：基於 KPI 臨床閾值
            $isCritical = false;
            if ($inspection) {
                $hct = $inspection->hct;
                $hb = $inspection->hb;
                $ca = $inspection->ca;
                $p = $inspection->p;
                $k = $inspection->k;
                $sugar_ac = $inspection->sugar_ac;

                // HCT < 25
                if ($hct !== null && (float)$hct < 25) $isCritical = true;
                // Hb < 8
                if ($hb !== null && (float)$hb < 8) $isCritical = true;
                // K > 5.5
                if ($k !== null && (float)$k > 5.5) $isCritical = true;
                // Ca > 10.5
                if ($ca !== null && (float)$ca > 10.5) $isCritical = true;
                // P > 7
                if ($p !== null && (float)$p > 7) $isCritical = true;
            }

            // 乾體重
            $dryWeight = $weight ? (float)$weight->dry_weight : null;

            $patients[] = [
                'id'           => $patient->id,
                'check_id'     => $check->id,
                'bed_no'       => ($reservation->machine_bed && $reservation->machine_bed->bed) ? $reservation->machine_bed->bed->bed_no : '—',
                'name'         => $patient->name,
                'chart_no'     => $patient->medical_record_no,
                'gender'       => $genderName,
                'age'          => $age,
                'shift'        => $reservation->morning_noon_night,
                'shift_label'  => $shiftLabel,
                'status'       => $status,
                'is_critical'  => $isCritical,
                'is_visited'   => false,
                'ktv'          => $ktv,
                'dry_weight'   => $dryWeight,
            ];
        }

        return response()->json([
            'status'   => 200,
            'patients' => $patients,
        ]);
    }

    /**
     * GET /doctor/patients/{id}
     * 回傳單一病患詳細資料（含完整 KPI: HCT/Hb/Ferritin/KtV/URR/CaP/乾體重）
     */
    public function show($id)
    {
        $patient = Patient::with('gender')->findOrFail($id);

        // 年齡
        $age = null;
        if ($patient->birth_date) {
            $age = \Carbon\Carbon::parse($patient->birth_date)->age;
        }

        // 性別名稱
        $genderName = $patient->gender ? $patient->gender->name : null;

        // 最新檢驗記錄
        $inspection = PatientDialysisInspectionRecord::where('patient_id', $id)
            ->where('deleted', 0)
            ->orderBy('date', 'desc')
            ->first();

        // 最新乾體重
        $weight = PatientDialysisWeight::where('patient_id', $id)
            ->orderBy('id', 'desc')
            ->first();

        $dryWeight = $weight ? (float)$weight->dry_weight : null;

        // KPI 組裝
        $kpi = null;
        if ($inspection) {
            $kpi = [
                'hct'        => $inspection->hct !== null ? (float)$inspection->hct : null,
                'hb'         => $inspection->hb !== null ? (float)$inspection->hb : null,
                'ferritin'   => $inspection->ferritin !== null ? (float)$inspection->ferritin : null,
                'ktv'        => $inspection->kt_v_gotch !== null ? round((float)$inspection->kt_v_gotch, 2)
                                    : ($inspection->daugirdas !== null ? round((float)$inspection->daugirdas, 2) : null),
                'urr'        => $inspection->urr !== null ? (float)$inspection->urr : null,
                'ca'         => $inspection->ca !== null ? (float)$inspection->ca : null,
                'p'          => $inspection->p !== null ? (float)$inspection->p : null,
                'dry_weight' => $dryWeight,
                'inspection_date' => $inspection->date,
            ];
        }

        return response()->json([
            'status' => 200,
            'patient' => [
                'id'              => $patient->id,
                'name'            => $patient->name,
                'chart_no'        => $patient->medical_record_no,
                'gender'          => $genderName,
                'gender_id'       => $patient->gender_id,
                'age'             => $age,
                'birth_date'      => $patient->birth_date,
                'phone'           => $patient->mobile_phone,
                'image_path'      => $patient->image_path,
                'blood_id'        => $patient->blood_id,
                'blood_rh_id'     => $patient->blood_rh_id,
                'medical_record_no' => $patient->medical_record_no,
                'kpi'             => $kpi,
            ],
        ]);
    }
}
