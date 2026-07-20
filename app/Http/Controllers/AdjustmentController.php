<?php

namespace App\Http\Controllers;

use App\Models\PatientAfterPhysiologicalDatas;
use App\Models\PatientCheck;
use App\Models\PatientMidInfraredTherapie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdjustmentController extends Controller
{
    //
    function getFir($id){
        $patientMidInfraredTherapie = PatientMidInfraredTherapie::where('patient_check_id', $id)->first();

        return json_encode([
            'status' => 200,
            'fir'=> $patientMidInfraredTherapie
        ]);
    }

    function updateFir($id, Request $request){

        $flag = $request->input('fir_id');
        $content = $request->input('fir_content');

        $patientMidInfraredTherapie = PatientMidInfraredTherapie::where('patient_check_id', $id)->first();
        if($patientMidInfraredTherapie != null){
            $patientMidInfraredTherapie->flag = $flag;
            $patientMidInfraredTherapie->content = $content;
            $patientMidInfraredTherapie->save();
        }
        else{
            PatientMidInfraredTherapie::create([
                'patient_check_id' => $id,
                'flag' => $flag,
                'content' => $content,
                'nurse_id' => Auth::user()->id,
            ]);
        }

        $patientCheck = PatientCheck::findOrFail($id);
        $patientCheck->care_nurse_id = Auth::user()->id;
        $patientCheck->save();

        return json_encode([
            'status'=> 200
        ]);
    }

    function getAfter($id){
        $patientAfterPhysiologicalData = PatientAfterPhysiologicalDatas::where('patient_check_id', $id)->first();

        return json_encode([
            'status' => 200,
            'after' => $patientAfterPhysiologicalData
        ]);

    }

    function updateAfter($id, Request $request){
        $patientAfterPhysiologicalData = PatientAfterPhysiologicalDatas::where('patient_check_id', $id)->first();
        $ak_clear = $request->input('ak_clear');
        $ak_id = $request->input('ak_id');
        $ak_content = $request->input('ak_content');
        $a_clear = $request->input('a_clear');
        $a_id = $request->input('a_id');
        $a_content = $request->input('a_content');
        $v_clear = $request->input('v_clear');
        $v_id = $request->input('v_id');
        $v_content = $request->input('v_content');

        if($patientAfterPhysiologicalData != null){
            $patientAfterPhysiologicalData->ak_clear = $ak_clear;
            $patientAfterPhysiologicalData->ak_id = $ak_id;
            $patientAfterPhysiologicalData->ak_content = $ak_content;
            $patientAfterPhysiologicalData->a_clear = $a_clear;
            $patientAfterPhysiologicalData->a_id = $a_id;
            $patientAfterPhysiologicalData->a_content = $a_content;
            $patientAfterPhysiologicalData->v_clear = $v_clear;
            $patientAfterPhysiologicalData->v_id = $v_id;
            $patientAfterPhysiologicalData->v_content = $v_content;
            $patientAfterPhysiologicalData->save();
        }
        else{
            PatientAfterPhysiologicalDatas::create([
                'patient_check_id' => $id,
                'ak_clear' => $ak_clear,
                'ak_id' => $ak_id,
                'ak_content' => $ak_content,
                'a_clear' => $a_clear,
                'a_id' => $a_id,
                'a_content' => $a_content,
                'v_clear' => $v_clear,
                'v_id' => $v_id,
                'v_content' => $v_content,
            ]);
        }

        $patientCheck = PatientCheck::findOrFail($id);
        $patientCheck->care_end_nurse_id = Auth::user()->id;
        $patientCheck->save();

        return json_encode([
            'status' => 200
        ]);
    }

    function careSign($id, Request $request){
        $patientCheck = PatientCheck::findOrFail($id);
        $patientCheck->care_nurse_id = Auth::user()->id;
        $patientCheck->save();

        return json_encode([
            'status' => 200
        ]);
    }

    function offSign($id, Request $request){
        // Role check: only nurse/head_nurse roles allowed
        $user = $request->user();
        $userRole = $user->role ? $user->role->name : null;
        if (!in_array($userRole, ['護理師', '護理長'])) {
            return response()->json(['status' => 403, 'message' => 'Forbidden: nurse role required'], 403);
        }

        $incomplete = [];

        // 條件①：前置整備與上針雙簽章已完成
        $patientCheck = PatientCheck::findOrFail($id);
        if ($patientCheck->prepare_nurse_id == null || $patientCheck->check_nurse_id == null) {
            $incomplete[] = [
                'condition' => 'prepare',
                'label' => '前置整備雙簽章未完成',
                'detail' => [],
            ];
        }

        // 條件②：所有固定時段皆有 Care Sign 記錄
        $requiredSlots = ['pre', 'h1', 'h2', 'h3', 'h4', 'post_lying', 'post_sitting'];
        $missingSlots = [];
        foreach ($requiredSlots as $slot) {
            $exists = \App\Models\PatientCareSign::where('patient_check_id', $id)
                ->where('time_slot', $slot)->exists();
            if (!$exists) {
                $missingSlots[] = $slot;
            }
        }
        if (count($missingSlots) > 0) {
            $incomplete[] = [
                'condition' => 'care_sign',
                'label' => '尚有時段未完成 Care Sign',
                'detail' => $missingSlots,
            ];
        }

        // 條件③：醫囑執行與用藥處置池核對完成
        $pendingOrders = \App\Models\DoctorApMedicine::where('patient_check_id', $id)
            ->whereIn('nurse_status', [0, 2])->count();
        if ($pendingOrders > 0) {
            $incomplete[] = [
                'condition' => 'drug_execution',
                'label' => '醫囑執行池尚有未處理項目',
                'detail' => ["{$pendingOrders} 筆待處理"],
            ];
        }

        // 條件④：今日醫材藥品下機核對完成
        $supplyCheck = \App\Models\PatientSupplyCheck::where('patient_check_id', $id)
            ->where('confirmed', true)->exists();
        if (!$supplyCheck) {
            $incomplete[] = [
                'condition' => 'supply_check',
                'label' => '今日醫材藥品尚未核對',
                'detail' => [],
            ];
        }

        // 條件⑤：當班護理記錄已儲存
        $hasRecord = \App\Models\PatientMidNurseRecord::where('patient_check_id', $id)->exists();
        if (!$hasRecord) {
            $incomplete[] = [
                'condition' => 'nurse_record',
                'label' => '當班護理記錄尚未儲存',
                'detail' => [],
            ];
        }

        // 任一條件不滿足 → 回傳 422
        if (count($incomplete) > 0) {
            return response()->json([
                'status' => 422,
                'message' => '尚有 ' . count($incomplete) . ' 項未完成，無法 Off-Sign',
                'incomplete_items' => $incomplete,
            ], 422);
        }

        // 全部通過 → 寫入 Off-Sign 記錄
        $patientCheck->care_end_nurse_id = Auth::user()->id;
        $patientCheck->save();

        return response()->json([
            'status' => 200,
            'off_signed' => true,
            'off_signed_at' => now()->toDateTimeString(),
        ]);
    }
}
