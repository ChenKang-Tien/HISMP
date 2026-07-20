<?php

namespace App\Http\Controllers;

use App\Models\Dispose;
use App\Models\DoctorApEquipments;
use App\Models\DoctorApLaboratory;
use App\Models\DoctorApMedicine;
use App\Models\DoctorApScience;
use App\Models\MedicalEquipmen;
use App\Models\Medicine;
use App\Models\PatientAfterAdjustWeight;
use App\Models\PatientAfterPhysiologicalDatas;
use App\Models\PatientBeforeAdjustWeight;
use App\Models\PatientBeforeDialysisMedicine;
use App\Models\PatientBeforePreparation;
use App\Models\PatientCheck;
use App\Models\PatientDialysisInspectionRecord;
use App\Models\PatientDialysisMachineLong;
use App\Models\PatientDialysisMachineShort;
use App\Models\PatientDialysisWater;
use App\Models\PatientDialysisWeight;
use App\Models\PatientEpoIronMedicine;
use App\Models\PatientHctInspectionRecordNew;
use App\Models\PatientInterruptToiletAdjustWeight;
use App\Models\PatientInterruptToiletWeight;
use App\Models\PatientMidBpPDatas;
use App\Models\PatientMidInfraredTherapie;
use App\Models\TodayCarePatient;
use Illuminate\Http\Request;

class FinishController extends Controller
{
    //
    function index($id){
        # code...
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation?->patient;
        if (!$patient) {
            return response()->json(['status' => 404, 'message' => '病患資料不存在'], 404);
        }

        $patientBeforePreparations = PatientBeforePreparation::where('patient_check_id', $id)->get();

        $patientDialysisMachineLongs = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('end_date', null)->orderBy('dialysis_machine_id', 'asc')->get();
        $patientDialysisMachineShorts = PatientDialysisMachineShort::where('patient_id', $patient->id)->where('end_date', null)->orderBy('dialysis_machine_id', 'asc')->get();


        // $dialyzer = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('dialysis_machine_id', 1)->where('end_date', null)->first();
        // $duration = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('dialysis_machine_id', 3)->where('end_date', null)->first();
        // $bloodFlow = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('dialysis_machine_id', 4)->where('end_date', null)->first();
        // $dialysateFlow = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('dialysis_machine_id', 5)->where('end_date', null)->first();
        // $dialysateNaKCa = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('dialysis_machine_id', 6)->where('end_date', null)->first();
        // $dialysateConductivity = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('dialysis_machine_id', 7)->where('end_date', null)->first();
        // $heparin = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('dialysis_machine_id', 8)->where('end_date', null)->first();
        
        $dialyzer_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 1)->first();
        $frequency_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 2)->first();
        $duration_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 3)->first();
        $bloodFlow_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 4)->first();
        $dialysateFlow_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 5)->first();
        $dialysateNaKCa_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 6)->first();
        $dialysateConductivity_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 7)->first();
        $heparin_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 8)->first();
        $hfi_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 9)->first();
        $initial_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 10)->first();
        $priming_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 11)->first();
        $maintain_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 12)->first();

        $dialyzer_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 1)->first();
        $frequency_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 2)->first();
        $duration_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 3)->first();
        $bloodFlow_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 4)->first();
        $dialysateFlow_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 5)->first();
        $dialysateNaKCa_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 6)->first();
        $dialysateConductivity_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 7)->first();
        $heparin_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 8)->first();
        $hfi_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 9)->first();
        $initial_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 10)->first();
        $priming_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 11)->first();
        $maintain_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 12)->first();


        $dialyzer = $dialyzer_long;
        if($dialyzer_short != null){
            $dialyzer = $dialyzer_short;
        }

        $frequency = $frequency_long;
        if($frequency_short != null){
            $frequency = $frequency_short;
        }

        $duration = $duration_long;
        if($duration_short != null){
            $duration = $duration_short;
        }

        $bloodFlow = $bloodFlow_long;
        if($bloodFlow_short != null){
            $bloodFlow = $bloodFlow_short;
        }

        $dialysateFlow = $dialysateFlow_long;
        if($dialysateFlow_short != null){
            $dialysateFlow = $dialysateFlow_short;
        }

        $dialysateNaKCa = $dialysateNaKCa_long;
        if($dialysateNaKCa_short != null){
            $dialysateNaKCa = $dialysateNaKCa_short;
        }

        $dialysateConductivity = $dialysateConductivity_long;
        if($dialysateConductivity_short != null){
            $dialysateConductivity = $dialysateConductivity_short;
        }

        $heparin = $heparin_long;

        $hfi = $hfi_long;
        if($hfi_short != null){
            $hfi = $hfi_short;
        }

        $initial = $initial_long;
        if($initial_short != null){
            $initial = $initial_short;
        }

        $priming = $priming_long;
        if($priming_short != null){
            $priming = $priming_short;
        }

        $maintain = $maintain_long;
        if($maintain_short != null){
            $maintain = $maintain_short;
        }

        if($dialyzer != null){
            $dialyzer = $dialyzer->dialyzer->product_name;
        }
        else{
            $dialyzer = null;
        }

        if($duration != null){
            $duration = $duration->value;
        }
        else{
            $duration = null;
        }

        if($bloodFlow != null){
            $bloodFlow = $bloodFlow->value;
        }
        else{
            $bloodFlow = null;
        }

        // if($IVSet != null){
        //     $IVSet = $bloodFlow->value;
        // }
        // else{
        //     $IVSet = null;
        // }

        if($dialysateFlow != null){
            $dialysateFlow = $dialysateFlow->value;
        }
        else{
            $dialysateFlow = null;
        }

        if($dialysateConductivity != null){
            $dialysateConductivity = $dialysateConductivity->value;
        }
        else{
            $dialysateConductivity = null;
        }

        if($dialysateNaKCa != null){
            $dialysateNaKCa = $dialysateNaKCa->Na_K_Ca->product_name;
        }
        else{
            $dialysateNaKCa = null;
        }

        if($heparin != null){
            if($heparin->value == 1){
                $heparin = "有";
            }
            else{
                $heparin = "沒有";
            }
        }
        else{
            $heparin = null;
        }

        if($hfi != null){
            if($hfi->value == 1){
                $hfi = "Heparin";
            }
            else if($hfi->value == 2){
                $hfi = "Fragmin";
            }
            else{
                $hfi = "Innohep";
            }
        }
        else{
            $hfi = null;
        }

        if($initial != null){
            $initial = $initial->value;
        }
        else{
            $initial = null;
        }

        if($priming != null){
            $priming = $priming->value;
        }
        else{
            $priming = null;
        }

        if($maintain != null){
            $maintain = $maintain->value;
        }
        else{
            $maintain = null;
        }

        $dayOfWeek = (date('N', strtotime($patientCheck->date)) - 1);
        // $dialysisMedicines = PatientDialysisMedicine::where('patient_id', $patient->id)
        // ->where('end_date', null)
        // ->whereRaw("REGEXP_SUBSTR(frequency_id, '[^,]+', 1, ?) = '1'", [$dayOfWeek + 1])
        // ->get();
        $dialysisMedicines = PatientBeforeDialysisMedicine::where('patient_check_id', $id)->where('deleted', 0)->get();
        foreach($dialysisMedicines as $dialysisMedicine){
            $dialysisMedicine->route;
            $dialysisMedicine->nurse;
        }

        $currentDate = $patientCheck->date;

        $thisMonday = date('Y-m-d', strtotime('monday this week', strtotime($currentDate)));
        $thisSunday = date('Y-m-d',strtotime($thisMonday.' +6 day'));
        $nextMonday = date('Y-m-d', strtotime($thisMonday.' +7 day'));

        $firstDayOfMonth = date('Y-m-01', strtotime($currentDate));

        $firstDayOfWeek = date('N', strtotime($firstDayOfMonth));

        $dayOfMonth = date('j', strtotime($currentDate));
        $weekOfMonth = intdiv(($dayOfMonth + $firstDayOfWeek - 2), 7) + 1;

        $month = date('n', strtotime($currentDate));

        $patientHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->whereBetween('date', [$thisMonday, $thisSunday])->first();
        if($patientHctInspectionRecordNew == null){
            $patientHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->where('date', '<=', $currentDate)->orderBy('date', 'desc')->first();
            if($patientHctInspectionRecordNew != null){
                $lastHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->where('date', '<', $patientHctInspectionRecordNew->date)->orderBy('date', 'desc')->first();
            }
            else{
                $lastHctInspectionRecordNew = null;
            }
        }
        else{
            $lastHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->where('date', '<', $currentDate)->orderBy('date', 'desc')->first();
        }

        $epoMedicines = PatientEpoIronMedicine::where('patient_id', $patient->id)->where('end_date', null)->where('epo_iron', 0)->orderBy('range', 'asc');
        $ironMedicines = PatientEpoIronMedicine::where('patient_id', $patient->id)->where('end_date', null)->where('epo_iron', 1)->orderBy('range', 'asc');
        $epoIronMedicines = $ironMedicines->union($epoMedicines)->get();
        // foreach($dialysisMedicines as $dialysisMedicine){
        //     $dialysisMedicine->route;
        //     $dialysisMedicine->frequency;
        // }
        foreach($epoIronMedicines as $epoIronMedicine){
            $epoIronMedicine->route;
            $epoIronMedicine->frequency;
        }

        $ironPrepare = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'iron')->first();
        $epoPrepare = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'epo')->first();

        if($ironPrepare != null){
            $ironMedicine = Medicine::findOrFail($ironPrepare->medicine_equipment_id);
            $iron_amount = $ironPrepare->amount;
        }
        else{
            $ferritinInspectionRecord = PatientDialysisInspectionRecord::where('patient_id', $patient->id)->where('date', '<', $patientCheck->date)->whereNotNull('ferritin')->orderBy('date', 'desc')->first();
            
            //0:epo 1:鐵
            $irons = PatientEpoIronMedicine::where('epo_iron', 1)->where('patient_id', $patient->id)->where('end_date', null)->orderBy('range', 'asc')->get();
            
            $ironMedicine = null;
            $iron_amount = null;
            if($ferritinInspectionRecord != null){
                $value = $ferritinInspectionRecord->ferritin;
                if($value != null){
                    foreach($irons as $iron){
                        if($value >= $iron->range_1 && $value <= $iron->range_2){
                            $ironMedicine = $iron;
                            break;
                        }
                    }
                }
            }
        }
        if($epoPrepare != null){
            $epoMedicine = Medicine::findOrFail($epoPrepare->medicine_equipment_id);
            $epo_amount = $epoPrepare->amount;
        }
        else{
            

            $epos = PatientEpoIronMedicine::where('epo_iron', 0)->where('patient_id', $patient->id)->where('end_date', null)->orderBy('range', 'asc')->get();
            $epoMedicine = null;
            $epo_amount = null;

            if($patientHctInspectionRecordNew != null){
                if($patientHctInspectionRecordNew->hct_add != null){
                    $value = $patientHctInspectionRecordNew->hct_add;
                }
                else{
                    $value = $patientHctInspectionRecordNew->hct;
                }
                foreach($epos as $epo){
                    if($value >= $epo->range_1 && $value <= $epo->range_2){
                        $epoMedicine = $epo;
                        break;
                    }
                }
            }
        }
        
        
        $preparations = PatientBeforePreparation::where('patient_check_id', $id)->get();
        $obj = (object)[];

        if($patientHctInspectionRecordNew != null){
            $obj->hct = $patientHctInspectionRecordNew->hct;
            if($patientHctInspectionRecordNew->hct_add != null){
                $obj->hct_add = $patientHctInspectionRecordNew->hct_add;
            }
        }

        if($lastHctInspectionRecordNew != null){
            if($lastHctInspectionRecordNew->hct_add != null){
                $obj->hct_last = $lastHctInspectionRecordNew->hct_add;
            }
            else{
                $obj->hct_last = $lastHctInspectionRecordNew->hct;
            }
        }

        $dialyzerPrepare = $preparations->where('name', 'dialyzer')->first();
        if($dialyzerPrepare != null){
            $obj->dialyzer_id = $dialyzerPrepare->medicine_equipment_id;
            $obj->dialyzer_amount = $dialyzerPrepare->amount;
        }
        else{
            $obj->dialyzer_amount = 1;
        }
        $durationPrepare = $preparations->where('name', 'duration')->first();
        if($durationPrepare != null){
            $obj->duration = $durationPrepare->product_name;
            $obj->duration_amount = $durationPrepare->amount;
        }
        $blood_flowPrepare = $preparations->where('name', 'blood_flow')->first();
        if($blood_flowPrepare != null){
            $obj->bloodFlow = $blood_flowPrepare->product_name;
            $obj->bloodFlow_amount = $blood_flowPrepare->amount;
        }
        $epo = $preparations->where('name', 'epo')->first();
        if($epo != null){
            $obj->epo_id = $epo->medicine_equipment_id;
            $obj->epo_amount = $epo->amount;
            $obj->epo_location_id = $epo->location_id;
        }
        $iron = $preparations->where('name', 'iron')->first();
        if($iron != null){
            $obj->iron_id = $iron->medicine_equipment_id;
            $obj->iron_amount = $iron->amount;
            $obj->iron_location_id = $iron->location_id;
        }
        $dialysateNaKCaPrepare = $preparations->where('name', 'dialysateNaKCa')->first();
        if($dialysateNaKCaPrepare != null){
            
            $obj->dialysateNaKCa = $dialysateNaKCaPrepare->product_name;
            $obj->dialysateNaKCa_amount = $dialysateNaKCaPrepare->amount;
        }
        $iv_setPrepare = $preparations->where('name', 'iv_set')->first();
        if($iv_setPrepare != null){
            $obj->iv_set_id = $iv_setPrepare->medicine_equipment_id;
            $obj->iv_set_amount = $iv_setPrepare->amount;
        }
        $iv_tubePrepare = $preparations->where('name', 'iv_tube')->first();
        if($iv_tubePrepare != null){
            $obj->iv_tube_id = $iv_tubePrepare->medicine_equipment_id;
            $obj->iv_tube_amount = $iv_tubePrepare->amount;
        }
        $dialysate_flowPrepare = $preparations->where('name', 'dialysate_flow')->first();
        if($dialysate_flowPrepare != null){
            $obj->dialysateFlow = $dialysate_flowPrepare->product_name;
            $obj->dialysateFlow_amount = $dialysate_flowPrepare->amount;
        }
        $dialysate_conductivityPrepare = $preparations->where('name', 'dialysate_conductivity')->first();
        if($dialysate_conductivityPrepare != null){
            $obj->dialysateConductivity = $dialysate_conductivityPrepare->product_name;
            $obj->dialysateConductivity_amount = $dialysate_conductivityPrepare->amount;
        }
        $heparinPrepare = $preparations->where('name', 'heparin')->first();
        if($heparinPrepare != null){
            $obj->heparin = $heparinPrepare->product_name;
            $obj->heparin_amount = $heparinPrepare->amount;
        }
        else{
            $obj->heparin = $hfi;
        }

        //體重
        $before_real_weight = 0;
        $adjustWeight = 0;
        $patientBeforeAdjustWeights = PatientBeforeAdjustWeight::where('patient_check_id', $id)->get();
        foreach($patientBeforeAdjustWeights as $count => $patientBeforeAdjustWeight){
            $adjustWeight += $patientBeforeAdjustWeight->weight;
        }
        $before_real_weight = $patientCheck->measure_weight_before + $adjustWeight;
        $patientDialysisWeight = PatientDialysisWeight::where('patient_id', $patientCheck->patient_reservation?->patient_id ?? 0)->orderBy('id', 'desc')->first();
        $dryWeight = 0;
        if($patientDialysisWeight != null){
            $dryWeight = $patientDialysisWeight->dry_weight;
        }
        $waterWeight = $before_real_weight - $dryWeight;
        // $interrupt_real_Weight = 0;
        // $interruptAdjiustWeight = 0;
        // $patientInterruptToiletWeight = PatientInterruptToiletWeight::where('patient_check_id', $id)->orderBy('id', 'desc')->first();
        // if($patientInterruptToiletWeight != null){
        //     $patientInterruptToiletAdjustWeights = PatientInterruptToiletAdjustWeight::where('interrput_id', $patientInterruptToiletWeight->id)->get();
        //     foreach($patientInterruptToiletAdjustWeights as $count => $patientInterruptToiletAdjustWeight){
        //         if($patientInterruptToiletAdjustWeight->way_add == 1){
        //             $interruptAdjiustWeight += $patientInterruptToiletAdjustWeight->weight;
        //         }
        //         else{
        //             $interruptAdjiustWeight -= $patientInterruptToiletAdjustWeight->weight;
        //         }
        //     }
        //     $interrupt_real_Weight = $patientInterruptToiletWeight->measure_weight + $interruptAdjiustWeight;
        // }
        $after_real_weight = null;
        $afterAdjustWeight = 0;
        if($patientCheck->measure_weight_after != null){
            $patientAfterAdjustWeights = PatientAfterAdjustWeight::where('patient_check_id', $id)->get();
            foreach($patientAfterAdjustWeights as $count => $patientAfterAdjustWeight){
                $afterAdjustWeight += $patientAfterAdjustWeight->weight;
            }
            $after_real_weight = round($patientCheck->measure_weight_after + $afterAdjustWeight, 1);
        }
        $realWaterWeight = 0;
        if($after_real_weight != null){
            $realWaterWeight = round($before_real_weight - $after_real_weight, 1);
        }

        $paitentDialysisWater = PatientDialysisWater::where('patient_check_id', $id)->orderBy('id', 'DESC')->first();
        if($paitentDialysisWater != null){
            $waterWeight = $paitentDialysisWater->amount;
        }
        if($waterWeight != 0){
            $waterPersen = round($realWaterWeight/$waterWeight*100, 1);
        }
        else if($waterWeight == $realWaterWeight){
            $waterPersen = 100;
        }
        else{
            $waterPersen = 0;
        }

        $obj->realWaterWeight = $realWaterWeight;

        $obj->waterPersen = $waterPersen;

        $obj->measure_weight_after = $patientCheck->measure_weight_after;

        

        if($after_real_weight != null){
           $adjustWeights = PatientAfterAdjustWeight::with('item')
            ->where('patient_check_id', $id)
            ->get();
        }
        else{
            $adjustWeights = PatientBeforeAdjustWeight::with('item')
            ->where('patient_check_id', $id)
            ->get();
        }

        $obj->adjust_items = $adjustWeights->map(function ($w) {
                return [
                    'id' => $w->id,
                    'item_id' => $w->item_id,
                    'item_name' => $w->item?->item,
                    'default_weight' => $w->item?->default_weight,
                    'way_add' => $w->way_add,
                    'weight' => $w->weight,
                ];
            })->values();


    

        $patientMidBpPDatas = PatientMidBpPDatas::where('patient_check_id', $id)->orderBy('time', 'desc')->get();
        foreach ($patientMidBpPDatas as $patientMidBpPData) {
            # code...
            $patientMidBpPData->dispose;
            $patientMidBpPData->nurse;
        }

        $tableDialysisRecordBlood3 = PatientMidBpPDatas::where('patient_check_id', $patientCheck->id)->where('dispose_id', 3)->orderBy('id', 'desc')->first();

        if($tableDialysisRecordBlood3 != null){
            $obj->systolic_blood_pressure = $tableDialysisRecordBlood3->systolic_blood_pressure;
            $obj->diastolic_blood_pressure = $tableDialysisRecordBlood3->diastolic_blood_pressure;
            $obj->p = $tableDialysisRecordBlood3->P;
        }
        else{
            $obj->systolic_blood_pressure = "";   
            $obj->diastolic_blood_pressure = "";
            $obj->p = "";
        }

        $obj->dialysisMedicines = $dialysisMedicines;
        $obj->epoIronMedicines = $epoIronMedicines;
        
    
        $obj->weight = $after_real_weight;
        $obj->realWaterWeight = $realWaterWeight;
        $obj->waterPersen = $waterPersen;
        $obj->bpPDatas = $patientMidBpPDatas;
        $obj->hfi = $hfi;
        $obj->initial = $initial;
        $obj->priming = $priming;
        $obj->maintain = $maintain;
        $obj->care_end_nurse_id = $patientCheck->care_end_nurse_id;
        $obj->check_nurse_id = $patientCheck->check_nurse_id;
        

        $disposes = Dispose::all();

        $selectOptions = $this->getPrepareSelectOption();

        $patientAfterPhysiologicalData = PatientAfterPhysiologicalDatas::where('patient_check_id', $id)->first();

        return response()->json([
            'status' => 200,
            'dialysisFinish' => $obj,
            'disposes' => $disposes,
            'selectOption' => $selectOptions,
            'after' => $patientAfterPhysiologicalData,
            'test' => $preparations
        ]);
    }


    function updateBloodPressure($id, Request $request){
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation?->patient;
        if (!$patient) {
            return response()->json(['status' => 404, 'message' => '病患資料不存在'], 404);
        }

        $finish_systolic_blood_pressure = $request->input('systolic_blood_pressure');
        $finish_diastolic_blood_pressure = $request->input('diastolic_blood_pressure');
        $finish_p = $request->input('p');

        $time = $patientCheck->date.' '.date('H:i:s');

        if($finish_systolic_blood_pressure != null && $finish_diastolic_blood_pressure != null && $finish_p != null ){
            $tableDialysisRecordBlood3 = PatientMidBpPDatas::where('patient_check_id', $patientCheck->id)->where('dispose_id', 3)->orderBy('id', 'desc')->first();
            if($tableDialysisRecordBlood3 != null){
                $tableDialysisRecordBlood3->systolic_blood_pressure = $finish_systolic_blood_pressure;
                $tableDialysisRecordBlood3->diastolic_blood_pressure = $finish_diastolic_blood_pressure;
                $tableDialysisRecordBlood3->P = $finish_p;
                $tableDialysisRecordBlood3->save();
            }
            else{
                PatientMidBpPDatas::create([
                    'patient_check_id' => $id,
                    'time' => $time,
                    'systolic_blood_pressure' => $finish_systolic_blood_pressure,
                    'diastolic_blood_pressure' => $finish_diastolic_blood_pressure,
                    'P' => $finish_p,
                    'dispose_id' => 3,
                    'machine' => 0,
                    'nurse_id' => $request->user()->id,
                    'display' => 1
                ]);
            }
        }
        else{
            // array_push($error_messages, "\"透後坐血壓\"尚未輸入");
        }
    }

    function updateWeight($id, Request $request){
        try {
            $patientCheck = PatientCheck::with('patient_reservation')->findOrFail($id);

            // 1️⃣ 更新量測體重（透前）
            $measuredWeight = $request->input('measured_weight');

            if ($measuredWeight !== null) {
                $patientCheck->measure_weight_after = $measuredWeight;
                $patientCheck->save();
            }

            // 2️⃣ 加減項目（若有）
            $adjustItems = $request->input('adjust_items', []);

            // 👉 安全做法：先刪掉這次透析舊的加減項
            PatientAfterAdjustWeight::where('patient_check_id', $patientCheck->id)
                ->delete();

            foreach ($adjustItems as $row) {
                // 防呆
                if (
                    empty($row['item_id']) ||
                    // !isset($row['way_add']) ||
                    $row['weight'] === null
                ) {
                    continue;
                }

                PatientAfterAdjustWeight::create([
                    'patient_check_id' => $patientCheck->id,
                    'item_id'          => $row['item_id'],
                    'way_add'          => 1, // 1 = 加, 0 = 減
                    'weight'           => $row['weight'],
                    'nurse_id'         => auth()->id(), // 登入護理師
                ]);
            }

            // DB::commit();

            return response()->json([
                'status' => 200,
                'message' => '透前體重已更新'
            ]);

        } catch (\Throwable $e) {
            // DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => '更新失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getPrepareSelectOption(){
        # code...
        $iv_sets = MedicalEquipmen::where('category_id', 12)->get();
        $iv_tubes = MedicalEquipmen::where('category_id', 10)->get();
        $dialyzers = MedicalEquipmen::where('category_id', 11)->get();
        $epos = Medicine::where('category_id', 2)->where('deleted', 0)->get();
        $irons = Medicine::where('category_id', 3)->where('deleted', 0)->get();

        $obj = (object)[];
        $obj->iv_sets = $iv_sets;
        $obj->iv_tubes = $iv_tubes;
        $obj->dialyzers = $dialyzers;
        $obj->epos = $epos;
        $obj->irons = $irons;
        
        return $obj;
    }

    public function finish($id, Request $request)
    {
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation?->patient;
        if (!$patient) {
            return response()->json(['status' => 404, 'message' => '病患資料不存在'], 404);
        }

        $errors = [];

        // ================================
        //  1. 前置檢查（醫囑、整備、調補、體重…）
        // ================================
        if (DoctorApEquipments::where('patient_check_id', $id)->where('nurse_status', 0)->exists()) {
            $errors[] = '「醫囑處置」尚未執行完成';
        }
        if ($patientCheck->prepare_nurse_id == null || $patientCheck->check_nurse_id == null) {
            $errors[] = '「整備」尚未確認完成';
        }
        if ($patientCheck->care_nurse_id == null) {
            $errors[] = '「Care SIGN」尚未執行完成';
        }
        if ($patientCheck->measure_weight_after == null) {
            $errors[] = '「透後體重」尚未輸入';
        }
        if ($patientCheck->status == 4) {
            $errors[] = '「中斷處置」尚未完成';
        }

         if($patientCheck->doctor_id == null){
            $errors[] = '「醫生」尚未確認完成';
        }

        // ================================
        //  2. 透後血壓記錄
        // ================================
        $bp = [
            "systolic_blood_pressure" => $request->input('systolic_blood_pressure'),
            "diastolic_blood_pressure" => $request->input('diastolic_blood_pressure'),
            "P" => $request->input('p'),
        ];

        if (!$bp['systolic_blood_pressure'] || !$bp['diastolic_blood_pressure'] || !$bp['P']) {
            $errors[] = '「透後坐血壓」尚未輸入';
        }

        if (count($errors)) {
            return response()->json(['status' => 0, 'error_messages' => $errors]);
        }

        $time = $patientCheck->end_time
            ? date('Y-m-d H:i:s', strtotime($patientCheck->end_time . ' + 10 minute'))
            : date('Y-m-d H:i:s');

        // 建立或更新 dispose_id = 3 的紀錄
        PatientMidBpPDatas::updateOrCreate(
            ['patient_check_id' => $id, 'dispose_id' => 3],
            [
                'time' => $time,
                'systolic_blood_pressure' => $bp['systolic_blood_pressure'],
                'diastolic_blood_pressure' => $bp['diastolic_blood_pressure'],
                'P' => $bp['P'],
                'machine' => 0,
                'nurse_id' => $request->user()->id,
                'display' => 1,
            ]
        );

        // ================================
        //  3. 儲存 AK / A / V（Clot）
        // ================================
        $clotFields = [
            'ak' => ['clear', 'id', 'content'],
            'a'  => ['clear', 'id', 'content'],
            'v'  => ['clear', 'id', 'content'],
        ];

        $clotData = ['patient_check_id' => $id];

        foreach ($clotFields as $prefix => $fields) {
            foreach ($fields as $f) {
                $clotData["{$prefix}_{$f}"] = $request->input("{$prefix}_{$f}");
            }
        }

        PatientAfterPhysiologicalDatas::updateOrCreate(
            ['patient_check_id' => $id],
            $clotData
        );

        $patientCheck->care_end_nurse_id = $request->user()->id;
        $patientCheck->save();

        // ================================
        //  4. 儲存耗材用量（Preparation）
        // ================================
        $prepareMap = [
            'dialyzer_amount'            => 'dialyzer',
            'iv_set_amount'              => 'iv_set',
            'iv_tube_amount'             => 'iv_tube',
            'dialysateNaKCa_amount'      => 'dialysateNaKCa',
            'heparin_amount'             => 'heparin',
        ];

        foreach ($prepareMap as $inputKey => $prepName) {
            $amount = $request->input($inputKey);
            if ($amount === null) continue;

            PatientBeforePreparation::where('patient_check_id', $id)
                ->where('name', $prepName)
                ->update([
                    'amount' => $amount,
                    'check_nurse_id' => $request->user()->id,
                    'check_time' => date('Y-m-d H:i:s'),
                ]);
        }

        // ================================
        //  5. 結束透析＆產出 docx
        // ================================
        if ($patientCheck->finish_time == null) {
            $patientCheck->finish_time = date('Y-m-d H:i:s');
        }

        $patientCheck->status = 5;
        $patientCheck->save();
        

        // 產生 dialysis record
        // $this->getDialysisRecord($id);

        TodayCarePatient::where('patient_check_id', $id)->delete();

        return response()->json(['status' => 200]);
    }



}
