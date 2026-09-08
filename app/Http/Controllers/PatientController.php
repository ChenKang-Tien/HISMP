<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DoctorApAnother;
use Illuminate\Support\Facades\DoctorApEquipments;
use Illuminate\Support\Facades\DoctorApLaboratory;
use Illuminate\Support\Facades\DoctorApMedicine;
use Illuminate\Support\Facades\DoctorApScience;
use App\Models\PatientCheck;
use App\Models\PatientBeforeAdjustWeight;
use App\Models\PatientDialysisWeight;
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
                if ($shift == 'morning') $q->where('morning_noon_night', 1);
                if ($shift == 'noon')    $q->where('morning_noon_night', 2);
                if ($shift == 'night')   $q->where('morning_noon_night', 3);
            })->get();

        if ($patientChecks->isEmpty()) {
            return response()->json(['success' => true, 'active_groups' => [], 'absent_patients' => [], 'offsign_patients' => []], 200);
        }

        $careAssignments = TodayCarePatient::where('date', $today)->get()->keyBy('patient_check_id');
        $patientIds = $patientChecks->pluck('patient_reservation.patient_id')->unique();
        $latestHct = PatientHctInspectionRecordNew::whereIn('patient_id', $patientIds)->latest('date')->get()->keyBy('patient_id');
        $weightMap = PatientDialysisWeight::where('date', $today)->get()->keyBy('patient_id');
        $adjustWeights = PatientBeforeAdjustWeight::whereIn('patient_check_id', $patientChecks->pluck('id'))->get()->groupBy('patient_check_id');

        $groups = [];
        foreach ($patientChecks as $check) {
            $p = $check->patient;
            if (!$p) continue;
            $nurseName = ($careAssignments->get($check->id)?->nurse?->name) ?? '未分組';
            if (!isset($groups[$nurseName])) $groups[$nurseName] = ['color' => '#0f766e', 'patients' => []];
            $groups[$nurseName]['patients'][] = [
                'id' => $check->id,
                'bed' => $check->patient_reservation->machine_bed->bed->bed_no ?? '?',
                'mr' => $p->medical_record_no,
                'name' => $p->name,
                'statusText' => '🟢 透析中',
                'progress' => 50,
                'hct' => $latestHct->get($p->id)->hct ?? null,
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
        $check = \App\Models\PatientCheck::with('patient')->findOrFail($check_id);
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

        return response()->json([
            'success' => true,
            'check_id' => $check->id,
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
            'nursing_records' => []
        ], 200);
    }

    public function showCurrentCase($mr)
    {
        $patient = \App\Models\Patient::where('medical_record_no', $mr)->first();
        if (!$patient) return response()->json(['success' => false], 404);

        return response()->json([
            'success' => true,
            'assess' => [
                'vascular_kind' => 'AVF',
                'vascular_pos_lr' => "L't",
                'vascular_pos_center' => 'SCVC',
                'vascular_eval' => ['0'],
                'skin' => '0'
            ]
        ], 200);
    }
}
