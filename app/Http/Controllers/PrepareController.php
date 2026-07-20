<?php

namespace App\Http\Controllers;

use App\Models\MedicalEquipmen;
use App\Models\Medicine;
use App\Models\NurseRecordPhrase;
use App\Models\PatientAfterBindingRecord;
use App\Models\PatientBeforeAdjustWeight;
use App\Models\PatientBeforeDialysisMedicine;
use App\Models\PatientBeforePhysiologicalDatas;
use App\Models\PatientBeforePreparation;
use App\Models\PatientCheck;
use App\Models\PatientDialysisInspectionRecord;
use App\Models\PatientDialysisMachineLong;
use App\Models\PatientDialysisMachineShort;
use App\Models\PatientDialysisMedicine;
use App\Models\PatientDialysisWater;
use App\Models\PatientDialysisWeight;
use App\Models\PatientEpoIronMedicine;
use App\Models\PatientHctInspectionRecordNew;
use App\Models\PatientMidBpPDatas;
use App\Models\PatientMidNurseRecord;
use App\Models\PatientNurseRecordMedicine;
use App\Models\PatientVascularAccessRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrepareController extends Controller
{
    function getPrepares($id)
    {
        # code...
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation->patient;

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
        $fs_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 13)->first();

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
        $fs_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 13)->first();

        $dialyzer = $dialyzer_long;
        if ($dialyzer_short != null) {
            $dialyzer = $dialyzer_short;
        }

        $frequency = $frequency_long;
        if ($frequency_short != null) {
            $frequency = $frequency_short;
        }

        $duration = $duration_long;
        if ($duration_short != null) {
            $duration = $duration_short;
        }

        $bloodFlow = $bloodFlow_long;
        if ($bloodFlow_short != null) {
            $bloodFlow = $bloodFlow_short;
        }

        $dialysateFlow = $dialysateFlow_long;
        if ($dialysateFlow_short != null) {
            $dialysateFlow = $dialysateFlow_short;
        }

        $dialysateNaKCa = $dialysateNaKCa_long;
        if ($dialysateNaKCa_short != null) {
            $dialysateNaKCa = $dialysateNaKCa_short;
        }

        $dialysateConductivity = $dialysateConductivity_long;
        if ($dialysateConductivity_short != null) {
            $dialysateConductivity = $dialysateConductivity_short;
        }

        $fs = $fs_long;
        if ($fs_short != null) {
            $fs = $fs_short;
        }

        $heparin = $heparin_long;

        $hfi = $hfi_long;
        if ($hfi_short != null) {
            $hfi = $hfi_short;
        }

        $initial = $initial_long;
        if ($initial_short != null) {
            $initial = $initial_short;
        }

        $priming = $priming_long;
        if ($priming_short != null) {
            $priming = $priming_short;
        }

        $maintain = $maintain_long;
        if ($maintain_short != null) {
            $maintain = $maintain_short;
        }

        if ($dialyzer != null) {
            $dialyzer = $dialyzer->value;
        } else {
            $dialyzer = null;
        }

        if ($duration != null) {
            $duration = $duration->value;
        } else {
            $duration = null;
        }

        if ($bloodFlow != null) {
            $bloodFlow = $bloodFlow->value;
        } else {
            $bloodFlow = null;
        }

        // if($IVSet != null){
        //     $IVSet = $bloodFlow->value;
        // }
        // else{
        //     $IVSet = null;
        // }

        if ($dialysateFlow != null) {
            $dialysateFlow = $dialysateFlow->value;
        } else {
            $dialysateFlow = null;
        }

        if ($dialysateConductivity != null) {
            $dialysateConductivity = $dialysateConductivity->value;
        } else {
            $dialysateConductivity = null;
        }

        if ($dialysateNaKCa != null) {
            $dialysateNaKCa = $dialysateNaKCa->Na_K_Ca->product_name;
        } else {
            $dialysateNaKCa = null;
        }


        if ($heparin != null) {
            if ($heparin->value == 1) {
                $heparin = "有";
            } else {
                $heparin = "沒有";
            }
        } else {
            $heparin = null;
        }

        if ($hfi != null) {
            if ($hfi->value == 1) {
                $hfi = "Heparin";
            } else if ($hfi->value == 2) {
                $hfi = "Fragmin";
            } else {
                $hfi = "Innohep";
            }
        } else {
            $hfi = null;
        }

        if ($initial != null) {
            $initial = $initial->value;
        } else {
            $initial = null;
        }

        if ($priming != null) {
            $priming = $priming->value;
        } else {
            $priming = null;
        }

        if ($maintain != null) {
            $maintain = $maintain->value;
        } else {
            $maintain = null;
        }

        $dayOfWeek = (date('N', strtotime($patientCheck->date)) - 1); // 0=週一, 6=週日
        $occurrence = (int)($dayOfWeek + 1);

        $dialysisMedicines = PatientBeforeDialysisMedicine::where('patient_check_id', $patientCheck->id)->get();
        if ($patientCheck->prepare_nurse_id == null) {

            // 長期處方 (依星期判斷)
            if (count($dialysisMedicines) > 0) {
                $dialysisMedicines = $dialysisMedicines->where('deleted', 0);
                foreach ($dialysisMedicines as $dialysisMedicine) {
                    $dialysisMedicine->route;
                }
            } else {
                $dialysisMedicines = PatientDialysisMedicine::where('patient_id', $patient->id)
                    ->whereNull('end_date')
                    ->get()
                    ->filter(function ($med) use ($dayOfWeek) {
                        $parts = explode(',', $med->frequency_id);
                        return isset($parts[$dayOfWeek]) && $parts[$dayOfWeek] == '1';
                    });

                foreach ($dialysisMedicines as $dialysisMedicine) {
                    // 觸發 Eloquent 關聯
                    $dialysisMedicine->route;
                    $dialysisMedicine->frequency;
                }
            }
        } else {
            // 臨時處方
            // dd($dialysisMedicines);
            $dialysisMedicines = $dialysisMedicines->where('deleted', 0);
            foreach ($dialysisMedicines as $dialysisMedicine) {
                $dialysisMedicine->route;
            }
        }




        $epoMedicines = PatientEpoIronMedicine::where('patient_id', $patient->id)->where('end_date', null)->where('epo_iron', 0)->orderBy('range', 'asc');
        $ironMedicines = PatientEpoIronMedicine::where('patient_id', $patient->id)->where('end_date', null)->where('epo_iron', 1)->orderBy('range', 'asc');
        $epoIronMedicines = $ironMedicines->union($epoMedicines)->get();

        foreach ($epoIronMedicines as $epoIronMedicine) {
            $epoIronMedicine->route;
            $epoIronMedicine->frequency;
        }

        $ironPrepare = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'iron')->first();
        $epoPrepare = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'epo')->first();

        if ($ironPrepare != null) {
            $ironMedicine = Medicine::findOrFail($ironPrepare->medicine_equipment_id);
            $iron_amount = $ironPrepare->amount;
        } else {
            $ferritinInspectionRecord = PatientDialysisInspectionRecord::where('patient_id', $patient->id)->where('date', '<', $patientCheck->date)->whereNotNull('ferritin')->orderBy('date', 'desc')->first();

            //0:epo 1:鐵
            $irons = PatientEpoIronMedicine::where('epo_iron', 1)->where('patient_id', $patient->id)->where('end_date', null)->orderBy('range', 'asc')->get();

            $ironMedicine = null;
            $iron_amount = null;
            if ($ferritinInspectionRecord != null) {
                $value = $ferritinInspectionRecord->ferritin;
                if ($value != null) {
                    foreach ($irons as $iron) {
                        if ($value >= $iron->range_1 && $value <= $iron->range_2) {
                            $ironMedicine = $iron;
                            break;
                        }
                    }
                }
            }
        }
        if ($epoPrepare != null) {
            $epoMedicine = Medicine::findOrFail($epoPrepare->medicine_equipment_id);
            $epo_amount = $epoPrepare->amount;
        } else {
            $currentDate = $patientCheck->date;

            $thisMonday = date('Y-m-d', strtotime('monday this week', strtotime($currentDate)));
            $thisSunday = date('Y-m-d', strtotime($thisMonday . ' +6 day'));
            $nextMonday = date('Y-m-d', strtotime($thisMonday . ' +7 day'));

            $firstDayOfMonth = date('Y-m-01', strtotime($currentDate));

            $firstDayOfWeek = date('N', strtotime($firstDayOfMonth));

            $dayOfMonth = date('j', strtotime($currentDate));
            $weekOfMonth = intdiv(($dayOfMonth + $firstDayOfWeek - 2), 7) + 1;

            $month = date('n', strtotime($currentDate));

            $patientHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->whereBetween('date', [$thisMonday, $thisSunday])->first();

            $epos = PatientEpoIronMedicine::where('epo_iron', 0)->where('patient_id', $patient->id)->where('end_date', null)->orderBy('range', 'asc')->get();

            $todayIndex = date('N', strtotime($currentDate)) - 1;

            $eposForToday = $epos->filter(function ($medicine) use ($todayIndex) {
                $freqArray = explode(',', $medicine->frequency_id);
                return isset($freqArray[$todayIndex]) && $freqArray[$todayIndex] == '1';
            });

            $epoMedicine = null;
            $epo_amount = null;



            if ($patientHctInspectionRecordNew != null) {
                if ($patientHctInspectionRecordNew->hct_add != null) {
                    $value = $patientHctInspectionRecordNew->hct_add;
                } else {
                    $value = $patientHctInspectionRecordNew->hct;
                }
                foreach ($eposForToday as $epo) {
                    if ($value >= $epo->range_1 && $value <= $epo->range_2) {
                        $epoMedicine = $epo;
                        $epo_amount = $epo->one_amount;
                        break;
                    }
                }
            }
        }



        $preparations = PatientBeforePreparation::where('patient_check_id', $id)->get();

        $dialyzerPrepare = $preparations->where('name', 'dialyzer')->first();
        $durationPrepare = $preparations->where('name', 'duration')->first();
        $blood_flowPrepare = $preparations->where('name', 'blood_flow')->first();
        $epo = $preparations->where('name', 'epo')->first();
        $iron = $preparations->where('name', 'iron')->first();
        $dialysateNaKCaPrepare = $preparations->where('name', 'dialysateNaKCa')->first();
        $iv_setPrepare = $preparations->where('name', 'iv_set')->first();
        $iv_tubePrepare = $preparations->where('name', 'iv_tube')->first();
        $dialysate_flowPrepare = $preparations->where('name', 'dialysate_flow')->first();
        $dialysate_conductivityPrepare = $preparations->where('name', 'dialysate_conductivity')->first();
        $heparinPrepare = $preparations->where('name', 'heparin')->first();
        $initial_primingPrepare = $preparations->where('name', 'initial_priming')->first();


        $patientAfterBindingRecord = PatientAfterBindingRecord::where('patient_check_id', $patientCheck->id)->first();

        $currentDate = $patientCheck->date;

        $thisMonday = date('Y-m-d', strtotime('monday this week', strtotime($currentDate)));
        $thisSunday = date('Y-m-d', strtotime($thisMonday . ' +6 day'));
        $nextMonday = date('Y-m-d', strtotime($thisMonday . ' +7 day'));

        $firstDayOfMonth = date('Y-m-01', strtotime($currentDate));

        $firstDayOfWeek = date('N', strtotime($firstDayOfMonth));

        $dayOfMonth = date('j', strtotime($currentDate));
        $weekOfMonth = intdiv(($dayOfMonth + $firstDayOfWeek - 2), 7) + 1;

        $month = date('n', strtotime($currentDate));



        $patientMidBpPData = PatientMidBpPDatas::where('patient_check_id', $id)->where('dispose_id', 1)->first();
        if ($patientMidBpPData != null) {
            $systolic_blood_pressure = $patientMidBpPData->systolic_blood_pressure;
            $diastolic_blood_pressure = $patientMidBpPData->diastolic_blood_pressure;
            $p = $patientMidBpPData->P;
        } else {
            $systolic_blood_pressure = null;
            $diastolic_blood_pressure = null;
            $p = null;
        }

        $paitentDialysisWater = PatientDialysisWater::where('patient_check_id', $id)->orderBy('id', 'DESC')->first();


        $obj = (object)[];

        if ($paitentDialysisWater != null) {
            $obj->water = $paitentDialysisWater->amount;
            $obj->patientAsk = $paitentDialysisWater->patient_ask;
        } else {
            $before_real_weight = 0;
            $adjustWeight = 0;
            $adjustWeight = PatientBeforeAdjustWeight::where('patient_check_id', $id)->sum('weight');

            $before_real_weight = round($patientCheck->measure_weight_before + $adjustWeight, 1);
            $patientDialysisWeight = PatientDialysisWeight::where('patient_id', $patientCheck->patient_reservation->patient_id)->orderBy('id', 'desc')->first();
            $dryWeight = 0;
            if ($patientDialysisWeight != null) {
                $dryWeight = $patientDialysisWeight->dry_weight;
            }
            $waterWeight = round($before_real_weight - $dryWeight, 1);
            $obj->water = $waterWeight;
        }



        $obj->systolic_blood_pressure = $systolic_blood_pressure;
        $obj->diastolic_blood_pressure = $diastolic_blood_pressure;
        $obj->p = $p;

        $patientHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->whereBetween('date', [$thisMonday, $thisSunday])->first();
        if ($patientHctInspectionRecordNew != null) {
            $lastHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->where('date', '<', $patientHctInspectionRecordNew->date)->orderBy('date', 'desc')->first();
        } else {
            $lastHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->where('date', '<', $currentDate)->orderBy('date', 'desc')->first();
        }

        if ($patientHctInspectionRecordNew != null) {
            $obj->hct = $patientHctInspectionRecordNew->hct;
            if ($patientHctInspectionRecordNew->hct_add != null) {
                $obj->hct_add = $patientHctInspectionRecordNew->hct_add;
            }
        }

        if ($lastHctInspectionRecordNew != null) {
            if ($lastHctInspectionRecordNew->hct_add != null) {
                $obj->hct_last = $lastHctInspectionRecordNew->hct_add;
            } else {
                $obj->hct_last = $lastHctInspectionRecordNew->hct;
            }
        }


        $obj->patientAfterBindingRecord = $patientAfterBindingRecord;

        $obj->dialysisMedicines = $dialysisMedicines;
        $obj->epoIronMedicines = $epoIronMedicines;
        $obj->duration_default = $duration;


        if ($fs != null && $fs->value == 1) {
            $obj->fs_default = "請測量";
        } else {
            $obj->fs_default = "";
        }


        // if(count($preparations) > 1){ //初始餘量和duration例外 所以有可能會有2
        // if($dialyzerPrepare != null){
        //     $obj->dialyzer_id = $dialyzerPrepare->medicine_equipment_id;
        //     $obj->dialyzer_amount = $dialyzerPrepare->amount;
        // }

        // if($durationPrepare != null){
        //     $obj->duration = $durationPrepare->product_name;
        //     $obj->duration_amount = $durationPrepare->amount;

        // }

        // if($blood_flowPrepare != null){
        //     $obj->bloodFlow = $blood_flowPrepare->product_name;
        //     $obj->bloodFlow_amount = $blood_flowPrepare->amount;
        // }

        // if($dialysate_flowPrepare != null){
        //     $obj->dialysateFlow = $dialysate_flowPrepare->product_name;
        //     $obj->dialysateFlow_amount = $dialysate_flowPrepare->amount;
        // }

        // if($dialysate_conductivityPrepare != null){
        //     $obj->dialysateConductivity = $dialysate_conductivityPrepare->product_name;
        //     $obj->dialysateConductivity_amount = $dialysate_conductivityPrepare->amount;
        // }

        // if($dialysateNaKCaPrepare != null){
        //     $obj->dialysateNaKCa = $dialysateNaKCaPrepare->product_name;
        //     $obj->dialysateNaKCa_amount = $dialysateNaKCaPrepare->amount;
        // }

        // if($initial_primingPrepare != null){
        //     $obj->initial_priming = $initial_primingPrepare->product_name;
        // }

        // if($heparinPrepare != null){
        //     $obj->heparin = $heparinPrepare->product_name;
        //     $obj->heparin_amount = $heparinPrepare->amount;
        // }
        // else{
        //     $obj->heparin = $heparin;
        // }

        // $currentDate = date('Y-m-d');

        // $thisMonday = date('Y-m-d', strtotime('monday this week', strtotime($currentDate)));
        // $lastMonday = date('Y-m-d', strtotime($thisMonday.' -7 day'));



        // $firstDayOfMonth = date('Y-m-01', strtotime($currentDate));

        // $firstDayOfWeek = date('N', strtotime($firstDayOfMonth));

        // $dayOfMonth = date('j', strtotime($currentDate));
        // $weekOfMonth = intdiv(($dayOfMonth + $firstDayOfWeek - 2), 7) + 1;
        // if($weekOfMonth > 5){
        //     $weekOfMonth = 5;
        // }

        // $patientHctInspectionRecord = PatientHctInspectionRecord::where('patient_id', $patient->id)->where('date', $firstDayOfMonth)->whereNotNull('day_'.$weekOfMonth)->first();

        if ($epo != null) {
            $obj->epo_id = $epo->medicine_equipment_id;
            $obj->epo_amount = $epo->amount;
            $obj->epo_location_id = $epo->location_id;
        }
        if ($iron != null) {
            $obj->iron_id = $iron->medicine_equipment_id;
            $obj->iron_amount = $iron->amount;
            $obj->iron_location_id = $iron->location_id;
        }

        if ($iv_setPrepare != null) {
            $obj->iv_set_id = $iv_setPrepare->medicine_equipment_id;
            $obj->iv_set_amount = $iv_setPrepare->amount;
        }

        if ($iv_tubePrepare != null) {
            $obj->iv_tube_id = $iv_tubePrepare->medicine_equipment_id;
            $obj->iv_tube_amount = $iv_tubePrepare->amount;
        }



        // $obj->epo = $epoMedicine;
        // $obj->epo_amount = $epo_amount;
        // $obj->iron = $ironMedicine;
        // $obj->iron_amount = $iron_amount;
        $obj->hfi = $hfi;
        $obj->initial = $initial;
        $obj->priming = $priming;
        $obj->maintain = $maintain;
        // }
        // else{
        if ($dialyzerPrepare != null) {
            $obj->dialyzer_id = $dialyzerPrepare->medicine_equipment_id;
            $obj->dialyzer_amount = $dialyzerPrepare->amount;
        } else {
            $obj->dialyzer_id = $dialyzer;
            if ($durationPrepare != null) {
                $obj->duration = $durationPrepare->product_name;
            } else {
                $obj->duration = "";
            }
        }

        if ($durationPrepare != null) {
            $obj->duration = $durationPrepare->product_name;
            $obj->duration_amount = $durationPrepare->amount;
        } else {
            $obj->duration_default = $duration;
        }

        if ($blood_flowPrepare != null) {
            $obj->bloodFlow = $blood_flowPrepare->product_name;
            $obj->bloodFlow_amount = $blood_flowPrepare->amount;
        } else {
            $obj->bloodFlow = $bloodFlow;
        }

        if ($dialysate_flowPrepare != null) {
            $obj->dialysateFlow = $dialysate_flowPrepare->product_name;
            $obj->dialysateFlow_amount = $dialysate_flowPrepare->amount;
        } else {
            $obj->dialysateFlow = $dialysateFlow;
        }

        if ($dialysate_conductivityPrepare != null) {
            $obj->dialysateConductivity = $dialysate_conductivityPrepare->product_name;
            $obj->dialysateConductivity_amount = $dialysate_conductivityPrepare->amount;
        } else {
            $obj->dialysateConductivity = $dialysateConductivity;
        }

        if ($dialysateNaKCaPrepare != null) {
            $obj->dialysateNaKCa = $dialysateNaKCaPrepare->product_name;
            $obj->dialysateNaKCa_amount = $dialysateNaKCaPrepare->amount;
        } else {
            $obj->dialysateNaKCa = $dialysateNaKCa;
        }

        if ($initial_primingPrepare != null) {
            $obj->initial_priming = $initial_primingPrepare->product_name;
        }

        if ($heparinPrepare != null) {
            $obj->heparin = $heparinPrepare->product_name;
            $obj->heparin_amount = $heparinPrepare->amount;
        } else {
            $obj->heparin = $hfi;
        }





        if ($epo == null) {
            $obj->epo_location_id = 1;
            if ($epoMedicine != null) {
                $obj->epo_id = $epoMedicine->medicine_id;
                $obj->epo_amount = $epoMedicine->one_amount;
            }
            $obj->epo_amount = $epo_amount;
        }

        if ($iron == null) {
            $obj->iron_location_id = 1;
            if ($ironMedicine != null) {
                $obj->iron_id = $ironMedicine->medicine_id;
                $obj->iron_amount = $ironMedicine->one_amount;
            }
            $obj->iron_amount = $iron_amount;
        }




        $obj->hfi = $hfi;
        $obj->initial = $initial;
        $obj->priming = $priming;
        $obj->maintain = $maintain;
        if ($initial_primingPrepare != null) {
            $obj->initial_priming = $initial_primingPrepare->product_name;
        } else {
            $obj->initial_priming = 0;
        }

        // }



        if ($patientCheck->check_nurse_id != null) {
            $obj->status = 2;
        } else if ($patientCheck->prepare_nurse_id != null) {
            $obj->status = 1;
        } else {
            $obj->status = 0;
        }

        $patientBeforePhysiologicalData = PatientBeforePhysiologicalDatas::where('patient_check_id', $id)->first();

        if ($patientBeforePhysiologicalData != null) {
            $obj->t = $patientBeforePhysiologicalData->T;
            $obj->r = $patientBeforePhysiologicalData->R;
            $obj->fs = $patientBeforePhysiologicalData->fs;
            $obj->needle_location = $patientBeforePhysiologicalData->needle_location;
            $obj->needle_number = $patientBeforePhysiologicalData->needle_number;

            // 1. 血管通路種類 (最後一格是 size)
            $vat_parts = explode(',', $patientBeforePhysiologicalData->vascular_access_type);
            $obj->vascular_access_type_size = array_pop($vat_parts); // 取出最後一個元素 (Size)
            // 過濾掉空字串，並重新排列索引 (確保 JSON 是 ["3"] 而不是 {"2":"3"})
            $obj->vascular_access_type = array_values(array_filter($vat_parts, function ($value) {
                return $value !== '' && $value !== null;
            }));

            // 2. 血管通路位置 (最後一格是 note)
            $val_parts = explode(',', $patientBeforePhysiologicalData->vascular_access_location);
            $obj->vascular_access_location_note = array_pop($val_parts);
            $obj->vascular_access_location = array_values(array_filter($val_parts, function ($value) {
                return $value !== '' && $value !== null;
            }));

            // dd($obj->vascular_access_location_note);

            // 3. 血管通路 (最後一格是 note)
            $va_parts = explode(',', $patientBeforePhysiologicalData->vascular_access);
            $obj->vascular_access_note = array_pop($va_parts);
            $obj->vascular_access = array_values(array_filter($va_parts, function ($value) {
                return $value !== '' && $value !== null;
            }));

            // 4. 意識狀態 (最後一格是 note)
            $cons_parts = explode(',', $patientBeforePhysiologicalData->consciousness);
            $obj->consciousness_note = array_pop($cons_parts);
            $obj->consciousness = array_values(array_filter($cons_parts, function ($value) {
                return $value !== '' && $value !== null;
            }));

            // 5. 皮膚 (倒數第一格是 size，倒數第二格是 location)
            $skin_parts = explode(',', $patientBeforePhysiologicalData->skin);
            // 注意 pop 的順序，先 pop 出來的是最後面的 size
            $obj->skins_size = array_pop($skin_parts);
            $obj->skins_location = array_pop($skin_parts);
            $obj->skins = array_values(array_filter($skin_parts, function ($value) {
                return $value !== '' && $value !== null;
            }));
            // 注意: 前端如果是接 obj.skins (複數)，這裡命名要對應
        } else {
            // 如果是預設值，也要記得把 note/size 欄位初始化為空字串，避免前端 undefined
            $patientVascularAccessRecord = PatientVascularAccessRecord::where('patient_id', $patient->id)->where('date', '<=', $patientCheck->date)->orderBy('date', 'desc')->orderBy('id', 'desc')->first();

            if ($patientVascularAccessRecord != null) {
                $obj->vascular_access_type = [
                    (string) $patientVascularAccessRecord->vascular_access_type_id // 轉成字串比較保險
                ];
                $obj->vascular_access_type_size = ""; // 初始化文字欄位

                $obj->vascular_access_location = [
                    $patientVascularAccessRecord->left_right == "左" ? "1" : "2"
                ];
            } else {
                // 如果連預設紀錄都沒有，全部給空值
                $obj->vascular_access_type = [];
                $obj->vascular_access_type_size = "";
                $obj->vascular_access_location = [];
            }
            $obj->vascular_access_location_note = ""; // 初始化文字欄位
            $obj->vascular_access = ["1"];
            $obj->vascular_access_note = "";

            $obj->consciousness = ["1"];
            $obj->consciousness_note = "";

            $obj->skins = ["1"]; // 前端變數名通常是 skins
            $obj->skins_location = "";
            $obj->skins_size = "";
        }

        $obj->prepare_nurse_id = $patientCheck->prepare_nurse_id;
        $obj->check_nurse_id = $patientCheck->check_nurse_id;

        $obj->measure_weight_before = $patientCheck->measure_weight_before;

        $obj->id = $id;

        $prepareCheck = $obj;


        //護理記錄
        $patientMidNurseRecords = PatientMidNurseRecord::where('patient_check_id', $id)->orderBy('time', 'desc')->get();

        $nurseRecords_orderByTime = [];
        $now = "";

        foreach ($patientMidNurseRecords as $patientMidNurseRecord) {
            # code...
            if ($now != $patientMidNurseRecord->time) {
                $nurseRecords = PatientMidNurseRecord::where('patient_check_id', $id)->where('time', $patientMidNurseRecord->time)->get();
                foreach ($nurseRecords as $nurseRecord) {
                    $nurseRecord->nurse;
                    $nurseRecord->nurse_continue = $patientMidNurseRecord->continue;
                }
                $obj = (object)[];
                $obj->time = $patientMidNurseRecord->time;
                $obj->nurseRecords = $nurseRecords;
                array_push($nurseRecords_orderByTime, $obj);
            }
            $now = $patientMidNurseRecord->time;
        }

        //透前用藥紀錄
        $patientNurseRecordMedicines = PatientNurseRecordMedicine::where('patient_check_id', $id)->orderBy('time', 'desc')->get();
        $medicines = [];
        foreach ($patientNurseRecordMedicines as $key => $patientNurseRecordMedicine) {
            # code...
            $patientNurseRecordMedicine->nurse;
            $obj = (object)[];
            $obj->id = $patientNurseRecordMedicine->id;
            $obj->time = $patientNurseRecordMedicine->time;
            $obj->name = $patientNurseRecordMedicine->content;
            array_push($medicines, $obj);
        }

        $nurseRecordPhrases = NurseRecordPhrase::select('name')->where('deleted', 0)->orderBy('name', 'asc')->get();



        return response()->json([
            "status" => 200,
            "prepareCheck" => $prepareCheck,
            'nurseRecords' => $nurseRecords_orderByTime,
            'nurseRecordPhrases' => $nurseRecordPhrases,
            'patientNurseRecordMedicines' => $medicines,
            'preparations' => $preparations
        ]);
    }
    //
    function updatePrepareEquipment($id, Request $request)
    {
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation->patient;

        $dialyzer_id = $request->input('dialyzer_id');
        if ($dialyzer_id != null) {
            $dialyzer = MedicalEquipmen::findOrFail($dialyzer_id);
            if ($dialyzer != null) {
                $prepare_dialyzer = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'dialyzer')->first();
                if ($prepare_dialyzer != null) {
                    $prepare_dialyzer->medicine_equipment_id = $dialyzer->id;
                    $prepare_dialyzer->product_name = $dialyzer->product_name;
                    $prepare_dialyzer->nurse_id = $request->user()->id;
                    $prepare_dialyzer->save();
                } else {
                    PatientBeforePreparation::create([
                        'patient_check_id' => $id,
                        'name' => 'dialyzer',
                        'medicine_equipment' => 2,
                        'medicine_equipment_id' => $dialyzer->id,
                        'product_name' => $dialyzer->product_name,
                        'amount' => 1,
                        'number' => 1,
                        'nurse_id' => $request->user()->id,
                        // 'check_time' => date('Y-m-d H:i:s'),
                        // 'check_nurse_id' => $request->user()->id
                    ]);
                }
                $dialyzer = $dialyzer->id;
            } else {
                $dialyzer = null;
            }
        }


        $duration = $request->input('duration');
        if ($duration != null) {
            $prepare_duration = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'duration')->first();
            if ($prepare_duration != null) {
                $prepare_duration->product_name = $duration;
                $prepare_duration->nurse_id = $request->user()->id;
                $prepare_duration->save();
            } else {
                PatientBeforePreparation::create([
                    'patient_check_id' => $id,
                    'name' => 'duration',
                    'medicine_equipment' => 1,
                    'medicine_equipment_id' => 0,
                    'product_name' => $duration,
                    'amount' => 1,
                    'number' => 1,
                    'nurse_id' => $request->user()->id,
                    // 'check_time' => date('Y-m-d H:i:s'),
                    // 'check_nurse_id' => $request->user()->id
                ]);
            }
        }

        $blood_flow = $request->input('blood_flow');
        if ($blood_flow != null) {
            $prepare_blood_flow = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'blood_flow')->first();
            if ($prepare_blood_flow != null) {
                $prepare_blood_flow->product_name = $blood_flow;
                $prepare_blood_flow->nurse_id = $request->user()->id;
                $prepare_blood_flow->save();
            } else {
                PatientBeforePreparation::create([
                    'patient_check_id' => $id,
                    'name' => 'blood_flow',
                    'medicine_equipment' => 2,
                    'medicine_equipment_id' => 0,
                    'product_name' => $blood_flow,
                    'amount' => 1,
                    'number' => 1,
                    'nurse_id' => $request->user()->id,
                    // 'check_time' => date('Y-m-d H:i:s'),
                    // 'check_nurse_id' => $request->user()->id
                ]);
            }
        }

        $iv_set_id = $request->input('iv_set_id');
        if ($iv_set_id != null) {
            $iv_set = MedicalEquipmen::findOrFail($iv_set_id);
            $prepare_iv_set = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'iv_set')->first();
            if ($prepare_iv_set != null) {
                $prepare_iv_set->medicine_equipment_id = $iv_set->id;
                $prepare_iv_set->product_name = $iv_set->product_name;
                $prepare_iv_set->save();
            } else {
                PatientBeforePreparation::create([
                    'patient_check_id' => $id,
                    'name' => 'iv_set',
                    'medicine_equipment' => 2,
                    'medicine_equipment_id' => $iv_set_id,
                    'product_name' => $iv_set->product_name,
                    'amount' => 1,
                    'number' => 1,
                    'nurse_id' => $request->user()->id,
                    // 'check_time' => date('Y-m-d H:i:s'),
                    // 'check_nurse_id' => $request->user()->id
                ]);
            }
        }

        $iv_tube_id = $request->input('iv_tube_id');
        if ($iv_tube_id != null) {
            $iv_tube = MedicalEquipmen::findOrFail($iv_tube_id);
            $prepare_iv_tube = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'iv_tube')->first();
            if ($prepare_iv_tube != null) {
                $prepare_iv_tube->medicine_equipment_id = $iv_tube->id;
                $prepare_iv_tube->product_name = $iv_tube->product_name;
                $prepare_iv_tube->save();
            } else {
                PatientBeforePreparation::create([
                    'patient_check_id' => $id,
                    'name' => 'iv_tube',
                    'medicine_equipment' => 2,
                    'medicine_equipment_id' => $iv_tube_id,
                    'product_name' => $iv_tube->product_name,
                    'amount' => 1,
                    'number' => 1,
                    'nurse_id' => $request->user()->id,
                    // 'check_time' => date('Y-m-d H:i:s'),
                    // 'check_nurse_id' => $request->user()->id
                ]);
            }
        }

        return response()->json([
            'status' => 200,
        ]);
    }

    function updatePrepareSupplies($id, Request $request)
    {
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation->patient;

        $patientDialysisMachineLongs = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('end_date', null)->orderBy('dialysis_machine_id', 'asc')->get();
        $patientDialysisMachineShorts = PatientDialysisMachineShort::where('patient_id', $patient->id)->where('end_date', null)->orderBy('dialysis_machine_id', 'asc')->get();


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
        $fs_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 13)->first();

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
        $fs_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 13)->first();

        $dialysate_flow = $request->input('dialysate_flow');
        if ($dialysate_flow != null) {
            $prepare_dialysate_flow = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'dialysate_flow')->first();
            if ($prepare_dialysate_flow != null) {
                $prepare_dialysate_flow->product_name = $dialysate_flow;
                $prepare_dialysate_flow->nurse_id = $request->user()->id;
                $prepare_dialysate_flow->save();
            } else {
                PatientBeforePreparation::create([
                    'patient_check_id' => $id,
                    'name' => 'dialysate_flow',
                    'medicine_equipment' => 1,
                    'medicine_equipment_id' => 0,
                    'product_name' => $dialysate_flow,
                    'amount' => 1,
                    'number' => 1,
                    'nurse_id' => $request->user()->id,
                    // 'check_time' => date('Y-m-d H:i:s'),
                    // 'check_nurse_id' => $request->user()->id
                ]);
            }
        }

        $dialysateNaKCa = $dialysateNaKCa_long;
        if ($dialysateNaKCa_short != null) {
            $dialysateNaKCa = $dialysateNaKCa_short;
        }

        if ($dialysateNaKCa != null) {
            $prepare_dialysateNaKCa = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'dialysateNaKCa')->first();
            if ($prepare_dialysateNaKCa != null) {
                $prepare_dialysateNaKCa->medicine_equipment_id = $dialysateNaKCa->Na_K_Ca->id;
                $prepare_dialysateNaKCa->product_name = $dialysateNaKCa->Na_K_Ca->product_name;
                $prepare_dialysateNaKCa->save();
            } else {
                PatientBeforePreparation::create([
                    'patient_check_id' => $id,
                    'name' => 'dialysateNaKCa',
                    'medicine_equipment' => 1,
                    'medicine_equipment_id' => $dialysateNaKCa->Na_K_Ca->id,
                    'product_name' => $dialysateNaKCa->Na_K_Ca->product_name,
                    'amount' => 1,
                    'number' => 1,
                    'nurse_id' => $request->user()->id,
                    // 'check_time' => date('Y-m-d H:i:s'),
                    // 'check_nurse_id' => $request->user()->id
                ]);
            }
            $dialysateNaKCa = $dialysateNaKCa->Na_K_Ca->product_name;
        } else {
            $dialysateNaKCa = null;
        }

        return response()->json([
            'status' => 200,
        ]);
    }

    function updatePrepareEpoIron($id, Request $request)
    {
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation->patient;

        $epo_id = $request->input('epo_id');
        if ($epo_id != null) {

            $epo = Medicine::findOrFail($epo_id);
            $prepare_epo = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'epo')->first();
            if ($prepare_epo != null) {
                $prepare_epo->medicine_equipment_id = $epo->id;
                $prepare_epo->product_name = $epo->product_name;
                $prepare_epo->amount = $request->input('epo_amount');
                $prepare_epo->location_id = $request->input('epo_location_id');
                $prepare_epo->save();
            } else {
                PatientBeforePreparation::create([
                    'patient_check_id' => $id,
                    'name' => 'epo',
                    'medicine_equipment' => 1,
                    'medicine_equipment_id' => $epo->id,
                    'product_name' => $epo->product_name,
                    'amount' => $request->input('epo_amount'),
                    'number' => 1,
                    'location_id' => $request->input('epo_location_id'),
                    'nurse_id' => $request->user()->id,
                    // 'check_time' => date('Y-m-d H:i:s'),
                    // 'check_nurse_id' => $request->user()->id
                ]);
            }
        }

        $iron_id = $request->input('iron_id');
        if ($iron_id != null) {

            $iron = Medicine::findOrFail($iron_id);
            $prepare_iron = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'iron')->first();
            if ($prepare_iron != null) {
                $prepare_iron->medicine_equipment_id = $iron->id;
                $prepare_iron->product_name = $iron->product_name;
                $prepare_iron->amount = $request->input('iron_amount');
                $prepare_iron->location_id = $request->input('iron_location_id');
                $prepare_iron->save();
            } else {
                PatientBeforePreparation::create([
                    'patient_check_id' => $id,
                    'name' => 'iron',
                    'medicine_equipment' => 1,
                    'medicine_equipment_id' => $iron->id,
                    'product_name' => $iron->product_name,
                    'amount' => $request->input('iron_amount'),
                    'number' => 1,
                    'location_id' => $request->input('iron_location_id'),
                    'nurse_id' => $request->user()->id,
                    // 'check_time' => date('Y-m-d H:i:s'),
                    // 'check_nurse_id' => $request->user()->id
                ]);
            }
        }
    }

    function updateEvaluate($id, Request $request)
    {
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation->patient;

        $initial_priming = $request->input('initial_priming');
        if ($initial_priming != null) {
            $prepare_initial_priming = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'initial_priming')->first();
            if ($prepare_initial_priming != null) {
                $prepare_initial_priming->product_name = $initial_priming;
                $prepare_initial_priming->nurse_id = $request->user()->id;
                $prepare_initial_priming->save();
            } else {
                PatientBeforePreparation::create([
                    'patient_check_id' => $id,
                    'name' => 'initial_priming',
                    'medicine_equipment' => 1,
                    'medicine_equipment_id' => 0,
                    'product_name' => $initial_priming,
                    'amount' => 1,
                    'number' => 1,
                    'nurse_id' => $request->user()->id,
                    // 'check_time' => date('Y-m-d H:i:s'),
                    // 'check_nurse_id' => $request->user()->id
                ]);
            }
        }

        $prepare_water = $request->input('water');
        if ($prepare_water != null) {
            if ($request->input('patientAsk')) $patientAsk = 1;
            else $patientAsk = 0;

            $paitentDialysisWater = PatientDialysisWater::where('patient_check_id', $id)->orderBy('id', 'DESC')->first();
            if ($paitentDialysisWater != null) {
                if ($paitentDialysisWater->amount != $prepare_water) {
                    PatientDialysisWater::create([
                        'patient_check_id' => $id,
                        'amount' => $prepare_water,
                        'patient_ask' => $patientAsk
                    ]);
                } else {
                    $paitentDialysisWater->patient_ask = $patientAsk;
                    $paitentDialysisWater->save();
                }
            } else {
                PatientDialysisWater::create([
                    'patient_check_id' => $id,
                    'amount' => $prepare_water,
                    'patient_ask' => $patientAsk
                ]);
            }
        }


        $prepare_systolic_blood_pressure = $request->input('systolic_blood_pressure');
        $prepare_diastolic_blood_pressure = $request->input('diastolic_blood_pressure');
        $prepare_p = $request->input('p');
        if ($patientCheck->start_time != null) {
            $bp_time = date('Y-m-d H:i:s', strtotime($patientCheck->start_time . ' -10 minute'));
        } else {
            $bp_time = date('Y-m-d H:i:s');
        }
        if ($prepare_systolic_blood_pressure != null && $prepare_diastolic_blood_pressure != null && $prepare_p != null) {
            $patientMidBpPData = PatientMidBpPDatas::where('patient_check_id', $id)->where('dispose_id', 1)->first();
            if ($patientMidBpPData != null) {
                $patientMidBpPData->systolic_blood_pressure = $prepare_systolic_blood_pressure;
                $patientMidBpPData->diastolic_blood_pressure = $prepare_diastolic_blood_pressure;
                $patientMidBpPData->P = $prepare_p;
                $patientMidBpPData->save();
            } else {
                PatientMidBpPDatas::create([
                    'patient_check_id' => $id,
                    'time' => $bp_time,
                    'systolic_blood_pressure' => $prepare_systolic_blood_pressure,
                    'diastolic_blood_pressure' => $prepare_diastolic_blood_pressure,
                    'P' => $prepare_p,
                    'dispose_id' => 1,
                    'machine' => 0,
                    'nurse_id' => $request->user()->id,
                    'display' => 1
                ]);
            }
        }

        $item1 = $request->input('item1') ? 1 : 0;
        $item2 = $request->input('item2') ? 1 : 0;
        $item3 = $request->input('item3') ? 1 : 0;
        $item4 = $request->input('item4');

        $nurse_id = $request->user()->id;

        $patientAfterBindingRecord = PatientAfterBindingRecord::where('patient_check_id', $id)->first();
        if ($patientAfterBindingRecord != null) {
            $patientAfterBindingRecord->item1 = $item1;
            $patientAfterBindingRecord->nurse1_id = $nurse_id;
            $patientAfterBindingRecord->item2 = $item2;
            $patientAfterBindingRecord->nurse2_id = $nurse_id;
            $patientAfterBindingRecord->item3 = $item3;
            $patientAfterBindingRecord->nurse3_id = $nurse_id;
            $patientAfterBindingRecord->item4 = $item4;
            $patientAfterBindingRecord->nurse4_id = $nurse_id;
            $patientAfterBindingRecord->save();
        } else {
            $patientAfterBindingRecord = PatientAfterBindingRecord::create([
                'patient_check_id' => $id,
                'item1' => $item1,
                'nurse1_id' => $nurse_id,
                'item2' => $item2,
                'nurse2_id' => $nurse_id,
                'item3' => $item3,
                'nurse3_id' => $nurse_id,
                'item4' => $item4,
                'nurse4_id' => $nurse_id,
            ]);
        }

        // 1. 血管通路種類 (總共 4 格 + 1 格大小)
        $vascular_access_type = $request->input('vascular_access_type', []); // 前端傳來 ["3"]
        $vascular_access_type_size = $request->input('vascular_access_type_size', "");
        $vascular_access_type_fixed = [];

        // 迴圈 1~4，檢查有沒有在前端傳來的陣列中
        for ($i = 1; $i <= 4; $i++) {
            // 如果 $i 在陣列裡 (代表有勾選)，就存入 $i，否則存空字串
            $vascular_access_type_fixed[] = in_array((string)$i, $vascular_access_type) ? $i : "";
        }
        // 最後補上大小文字
        array_push($vascular_access_type_fixed, $vascular_access_type_size);
        // 轉成字串: ",,3,,size"
        $vascular_access_type_string = implode(',', $vascular_access_type_fixed);


        // 2. 血管通路位置 (總共 5 格 + 1 格備註)
        $vascular_access_location = $request->input('vascular_access_location', []);
        $vascular_access_location_note = $request->input('vascular_access_location_note', "");
        $vascular_access_location_fixed = [];
        for ($i = 1; $i <= 5; $i++) {
            $vascular_access_location_fixed[] = in_array((string)$i, $vascular_access_location) ? $i : "";
        }
        array_push($vascular_access_location_fixed, $vascular_access_location_note);
        $vascular_access_location_string = implode(',', $vascular_access_location_fixed);


        // 3. 意識狀態 (總共 7 格 + 1 格備註)
        $consciousness = $request->input('consciousness', []);
        $consciousness_note = $request->input('consciousness_note', "");
        $consciousness_fixed = [];
        for ($i = 1; $i <= 7; $i++) {
            $consciousness_fixed[] = in_array((string)$i, $consciousness) ? $i : "";
        }
        array_push($consciousness_fixed, $consciousness_note);
        $consciousness_string = implode(',', $consciousness_fixed);


        // 4. 血管通路 (總共 7 格 + 1 格備註)
        $vascular_access = $request->input('vascular_access', []);
        $vascular_access_note = $request->input('vascular_access_note', "");
        $vascular_access_fixed = [];
        for ($i = 1; $i <= 7; $i++) {
            $vascular_access_fixed[] = in_array((string)$i, $vascular_access) ? $i : "";
        }
        array_push($vascular_access_fixed, $vascular_access_note);
        $vascular_access_string = implode(',', $vascular_access_fixed);


        // 5. 皮膚狀況 (總共 2 格 + 1 格位置 + 1 格大小)
        $skins = $request->input('skins', []);
        $skins_location = $request->input('skins_location', "");
        $skins_size = $request->input('skins_size', "");
        $skins_fixed = [];
        for ($i = 1; $i <= 2; $i++) {
            $skins_fixed[] = in_array((string)$i, $skins) ? $i : "";
        }
        array_push($skins_fixed, $skins_location);
        array_push($skins_fixed, $skins_size);
        $skins_string = implode(',', $skins_fixed);


        $t = $request->input('t', null);
        $r = $request->input('r', null);
        $fs = $request->input('fs', null);
        $needle_location = $request->input('needle_location', null);
        $needle_number = $request->input('needle_number', null);

        $patientBeforePhysiologicalData = PatientBeforePhysiologicalDatas::where('patient_check_id', $id)->first();
        if ($patientBeforePhysiologicalData != null) {
            $patientBeforePhysiologicalData->T = $t;
            $patientBeforePhysiologicalData->R = $r;
            $patientBeforePhysiologicalData->fs = $fs;
            $patientBeforePhysiologicalData->needle_location = $needle_location;
            $patientBeforePhysiologicalData->needle_number = $needle_number;
            $patientBeforePhysiologicalData->vascular_access_type = $vascular_access_type_string;
            $patientBeforePhysiologicalData->vascular_access_location = $vascular_access_location_string;
            $patientBeforePhysiologicalData->vascular_access = $vascular_access_string;
            $patientBeforePhysiologicalData->consciousness = $consciousness_string;
            $patientBeforePhysiologicalData->skin = $skins_string;
            $patientBeforePhysiologicalData->save();
        } else {
            PatientBeforePhysiologicalDatas::create([
                'patient_check_id' => $id,
                'T' => $t,
                'R' => $r,
                'fs' => $fs,
                'needle_location' => $needle_location,
                'needle_number' => $needle_number,
                'vascular_access_type' => $vascular_access_type_string,
                'vascular_access_location' => $vascular_access_location_string,
                'vascular_access' => $vascular_access_string,
                'consciousness' => $consciousness_string,
                'skin' => $skins_string
            ]);
        }

        return response()->json([
            'status' => 200
        ]);
    }

    function createPreMedicine($id, Request $request)
    {
        $time = $request->input("time");
        $content = $request->input("name");
        $patientNurseRecordMedicine = PatientNurseRecordMedicine::create([
            'patient_check_id' => $id,
            'time' => $time,
            'content' => $content,
            'nurse_id' => $request->user()->id
        ]);

        return response()->json([
            'status' => 200,
            'medicine' => [
                'id' => $patientNurseRecordMedicine->id,
                'time' => $patientNurseRecordMedicine->time,
                'name' => $patientNurseRecordMedicine->content
            ]
        ]);
    }

    function updatePreMedicine($id, Request $request)
    {
        $record_id = $id;
        $time = date('Y-m-d H:i:s');
        $content = $request->input('name');
        $nurse_id = $request->user()->id;
        if ($record_id != 0) {
            $patientNurseRecordMedicine = PatientNurseRecordMedicine::findOrFail($record_id);
            $patientNurseRecordMedicine->time = $time;
            $patientNurseRecordMedicine->content = $content;
            $patientNurseRecordMedicine->nurse_id = $nurse_id;
            $patientNurseRecordMedicine->save();
        }

        return response()->json([
            'status' => 200
        ]);
    }

    function deletePreMedicine($id, Request $request)
    {
        $patientNurseRecordMedicine = PatientNurseRecordMedicine::findOrFail($id);
        $patientNurseRecordMedicine->delete();

        return response()->json([
            'status' => 200
        ]);
    }

    public function updateDialysisMedicine($id, Request $request)
    {
        // 🔥 步驟 1：先把該次透析原本「所有」藥物標記為已刪除 (軟刪除)
        PatientBeforeDialysisMedicine::where('patient_check_id', $id)
            ->update(['deleted' => 1]);

        // 如果前端沒傳東西，代表全刪，直接結束
        if (!$request->has('dialysisMedicines')) {
            return response()->json(['message' => '已清除所有藥物']);
        }

        // 🔥 步驟 2：處理前端傳來的新名單
        foreach ($request->dialysisMedicines as $item) {
            // 使用 updateOrCreate：
            // 如果 已經存在該筆藥物紀錄 (依據 check_id 和 medicine_id 判斷) -> 把 deleted 改回 0 並更新數據
            // 如果 不存在 -> 直接建立一筆新的，且 deleted 為 0
            PatientBeforeDialysisMedicine::updateOrCreate(
                [
                    'patient_check_id' => $id,
                    'medicine_id'      => $item['medicine_id'],
                ],
                [
                    'medicine'  => $item['medicine'],
                    'amount'    => $item['amount'],
                    'route_id'  => $item['route_id'] ?? null,
                    'note'      => $item['note'] ?? null,
                    'nurse_id'  => $request->user()->id,
                    'deleted'   => 0, // 🔥 關鍵：確保狀態是「未刪除」
                ]
            );
        }

        return response()->json(['message' => '更新成功']);
    }

    function resetDialysisMedicine($id)
    {
        PatientBeforeDialysisMedicine::where('patient_check_id', $id)->delete();
    }

    function signPrepare($id, Request $request)
    {
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation->patient;

        $patientDialysisMachineLongs = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('end_date', null)->orderBy('dialysis_machine_id', 'asc')->get();
        $patientDialysisMachineShorts = PatientDialysisMachineShort::where('patient_id', $patient->id)->where('end_date', null)->orderBy('dialysis_machine_id', 'asc')->get();


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
        $fs_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 13)->first();

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
        $fs_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 13)->first();

        if ($patientCheck->prepare_nurse_id == null) {
            $patientCheck->prepare_nurse_id = $request->user()->id;

            $dialyzer_id = $request->input('dialyzer_id');
            if ($dialyzer_id != null) {
                $dialyzer = MedicalEquipmen::findOrFail($dialyzer_id);
                if ($dialyzer != null) {
                    $prepare_dialyzer = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'dialyzer')->first();
                    if ($prepare_dialyzer != null) {
                        $prepare_dialyzer->medicine_equipment_id = $dialyzer->id;
                        $prepare_dialyzer->product_name = $dialyzer->product_name;
                        $prepare_dialyzer->nurse_id = $request->user()->id;
                        $prepare_dialyzer->save();
                    } else {
                        PatientBeforePreparation::create([
                            'patient_check_id' => $id,
                            'name' => 'dialyzer',
                            'medicine_equipment' => 2,
                            'medicine_equipment_id' => $dialyzer->id,
                            'product_name' => $dialyzer->product_name,
                            'amount' => 1,
                            'number' => 1,
                            'nurse_id' => $request->user()->id,
                            // 'check_time' => date('Y-m-d H:i:s'),
                            // 'check_nurse_id' => $request->user()->id
                        ]);
                    }
                    $dialyzer = $dialyzer->id;
                } else {
                    $dialyzer = null;
                }
            }


            $duration = $request->input('duration');
            if ($duration != null) {
                $prepare_duration = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'duration')->first();
                if ($prepare_duration != null) {
                    $prepare_duration->product_name = $duration;
                    $prepare_duration->nurse_id = $request->user()->id;
                    $prepare_duration->save();
                } else {
                    PatientBeforePreparation::create([
                        'patient_check_id' => $id,
                        'name' => 'duration',
                        'medicine_equipment' => 1,
                        'medicine_equipment_id' => 0,
                        'product_name' => $duration,
                        'amount' => 1,
                        'number' => 1,
                        'nurse_id' => $request->user()->id,
                        // 'check_time' => date('Y-m-d H:i:s'),
                        // 'check_nurse_id' => $request->user()->id
                    ]);
                }
            }

            $blood_flow = $request->input('blood_flow');
            if ($blood_flow != null) {
                $prepare_blood_flow = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'blood_flow')->first();
                if ($prepare_blood_flow != null) {
                    $prepare_blood_flow->product_name = $blood_flow;
                    $prepare_blood_flow->nurse_id = $request->user()->id;
                    $prepare_blood_flow->save();
                } else {
                    PatientBeforePreparation::create([
                        'patient_check_id' => $id,
                        'name' => 'blood_flow',
                        'medicine_equipment' => 2,
                        'medicine_equipment_id' => 0,
                        'product_name' => $blood_flow,
                        'amount' => 1,
                        'number' => 1,
                        'nurse_id' => $request->user()->id,
                        // 'check_time' => date('Y-m-d H:i:s'),
                        // 'check_nurse_id' => $request->user()->id
                    ]);
                }
            }

            $iv_set_id = $request->input('iv_set_id');
            if ($iv_set_id != null) {
                $iv_set = MedicalEquipmen::findOrFail($iv_set_id);
                $prepare_iv_set = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'iv_set')->first();
                if ($prepare_iv_set != null) {
                    $prepare_iv_set->medicine_equipment_id = $iv_set->id;
                    $prepare_iv_set->product_name = $iv_set->product_name;
                    $prepare_iv_set->save();
                } else {
                    PatientBeforePreparation::create([
                        'patient_check_id' => $id,
                        'name' => 'iv_set',
                        'medicine_equipment' => 2,
                        'medicine_equipment_id' => $iv_set_id,
                        'product_name' => $iv_set->product_name,
                        'amount' => 1,
                        'number' => 1,
                        'nurse_id' => $request->user()->id,
                        // 'check_time' => date('Y-m-d H:i:s'),
                        // 'check_nurse_id' => $request->user()->id
                    ]);
                }
            }

            $iv_tube_id = $request->input('iv_tube_id');
            if ($iv_tube_id != null) {
                $iv_tube = MedicalEquipmen::findOrFail($iv_tube_id);
                $prepare_iv_tube = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'iv_tube')->first();
                if ($prepare_iv_tube != null) {
                    $prepare_iv_tube->medicine_equipment_id = $iv_tube->id;
                    $prepare_iv_tube->product_name = $iv_tube->product_name;
                    $prepare_iv_tube->save();
                } else {
                    PatientBeforePreparation::create([
                        'patient_check_id' => $id,
                        'name' => 'iv_tube',
                        'medicine_equipment' => 2,
                        'medicine_equipment_id' => $iv_tube_id,
                        'product_name' => $iv_tube->product_name,
                        'amount' => 1,
                        'number' => 1,
                        'nurse_id' => $request->user()->id,
                        // 'check_time' => date('Y-m-d H:i:s'),
                        // 'check_nurse_id' => $request->user()->id
                    ]);
                }
            }

            $dialysate_flow = $request->input('dialysate_flow');
            if ($dialysate_flow != null) {
                $prepare_dialysate_flow = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'dialysate_flow')->first();
                if ($prepare_dialysate_flow != null) {
                    $prepare_dialysate_flow->product_name = $dialysate_flow;
                    $prepare_dialysate_flow->nurse_id = $request->user()->id;
                    $prepare_dialysate_flow->save();
                } else {
                    PatientBeforePreparation::create([
                        'patient_check_id' => $id,
                        'name' => 'dialysate_flow',
                        'medicine_equipment' => 1,
                        'medicine_equipment_id' => 0,
                        'product_name' => $dialysate_flow,
                        'amount' => 1,
                        'number' => 1,
                        'nurse_id' => $request->user()->id,
                        // 'check_time' => date('Y-m-d H:i:s'),
                        // 'check_nurse_id' => $request->user()->id
                    ]);
                }
            }

            $epo_id = $request->input('epo_id');
            if ($epo_id != null) {

                $epo = Medicine::findOrFail($epo_id);
                $prepare_epo = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'epo')->first();
                if ($prepare_epo != null) {
                    $prepare_epo->medicine_equipment_id = $epo->id;
                    $prepare_epo->product_name = $epo->product_name;
                    $prepare_epo->amount = $request->input('epo_amount');
                    $prepare_epo->location_id = $request->input('epo_location_id');
                    $prepare_epo->save();
                } else {
                    PatientBeforePreparation::create([
                        'patient_check_id' => $id,
                        'name' => 'epo',
                        'medicine_equipment' => 1,
                        'medicine_equipment_id' => $epo->id,
                        'product_name' => $epo->product_name,
                        'amount' => $request->input('epo_amount'),
                        'number' => 1,
                        'location_id' => $request->input('epo_location_id'),
                        'nurse_id' => $request->user()->id,
                        // 'check_time' => date('Y-m-d H:i:s'),
                        // 'check_nurse_id' => $request->user()->id
                    ]);
                }
            }

            $iron_id = $request->input('iron_id');
            if ($iron_id != null) {

                $iron = Medicine::findOrFail($iron_id);
                $prepare_iron = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'iron')->first();
                if ($prepare_iron != null) {
                    $prepare_iron->medicine_equipment_id = $iron->id;
                    $prepare_iron->product_name = $iron->product_name;
                    $prepare_iron->amount = $request->input('iron_amount');
                    $prepare_iron->location_id = $request->input('iron_location_id');
                    $prepare_iron->save();
                } else {
                    PatientBeforePreparation::create([
                        'patient_check_id' => $id,
                        'name' => 'iron',
                        'medicine_equipment' => 1,
                        'medicine_equipment_id' => $iron->id,
                        'product_name' => $iron->product_name,
                        'amount' => $request->input('iron_amount'),
                        'number' => 1,
                        'location_id' => $request->input('iron_location_id'),
                        'nurse_id' => $request->user()->id,
                        // 'check_time' => date('Y-m-d H:i:s'),
                        // 'check_nurse_id' => $request->user()->id
                    ]);
                }
            }

            $initial_priming = $request->input('initial_priming');
            if ($initial_priming != null) {
                $prepare_initial_priming = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'initial_priming')->first();
                if ($prepare_initial_priming != null) {
                    $prepare_initial_priming->product_name = $initial_priming;
                    $prepare_initial_priming->nurse_id = $request->user()->id;
                    $prepare_initial_priming->save();
                } else {
                    PatientBeforePreparation::create([
                        'patient_check_id' => $id,
                        'name' => 'initial_priming',
                        'medicine_equipment' => 1,
                        'medicine_equipment_id' => 0,
                        'product_name' => $initial_priming,
                        'amount' => 1,
                        'number' => 1,
                        'nurse_id' => $request->user()->id,
                        // 'check_time' => date('Y-m-d H:i:s'),
                        // 'check_nurse_id' => $request->user()->id
                    ]);
                }
            }

            $prepare_water = $request->input('water');
            if ($prepare_water != null) {
                if ($request->input('patientAsk')) $patientAsk = 1;
                else $patientAsk = 0;

                $paitentDialysisWater = PatientDialysisWater::where('patient_check_id', $id)->orderBy('id', 'DESC')->first();
                if ($paitentDialysisWater != null) {
                    if ($paitentDialysisWater->amount != $prepare_water) {
                        PatientDialysisWater::create([
                            'patient_check_id' => $id,
                            'amount' => $prepare_water,
                            'patient_ask' => $patientAsk
                        ]);
                    } else {
                        $paitentDialysisWater->patient_ask = $patientAsk;
                        $paitentDialysisWater->save();
                    }
                } else {
                    PatientDialysisWater::create([
                        'patient_check_id' => $id,
                        'amount' => $prepare_water,
                        'patient_ask' => $patientAsk
                    ]);
                }
            }

            $hfi = $hfi_long;
            if ($hfi_short != null) {
                $hfi = $hfi_short;
            }

            if ($hfi != null) {
                if ($hfi->value == 1) {
                    $heparin = "Heparin";
                } else if ($hfi->value == 2) {
                    $heparin = "Fragmin";
                } else {
                    $heparin = "Innohep";
                }
                $prepare_heparin = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'heparin')->first();
                if ($prepare_heparin != null) {
                    $prepare_heparin->medicine_equipment_id = 0;
                    $prepare_heparin->product_name = $heparin;
                    $prepare_heparin->save();
                } else {
                    PatientBeforePreparation::create([
                        'patient_check_id' => $id,
                        'name' => 'heparin',
                        'medicine_equipment' => 1,
                        'medicine_equipment_id' => 0,
                        'product_name' => $heparin,
                        'amount' => 1,
                        'number' => 1,
                        'nurse_id' => $request->user()->id,
                        // 'check_time' => date('Y-m-d H:i:s'),
                        // 'check_nurse_id' => $request->user()->id
                    ]);
                }
            } else {
                $heparin = null;
            }

            $dialysateNaKCa = $dialysateNaKCa_long;
            if ($dialysateNaKCa_short != null) {
                $dialysateNaKCa = $dialysateNaKCa_short;
            }

            if ($dialysateNaKCa != null) {
                $prepare_dialysateNaKCa = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'dialysateNaKCa')->first();
                if ($prepare_dialysateNaKCa != null) {
                    $prepare_dialysateNaKCa->medicine_equipment_id = $dialysateNaKCa->Na_K_Ca->id;
                    $prepare_dialysateNaKCa->product_name = $dialysateNaKCa->Na_K_Ca->product_name;
                    $prepare_dialysateNaKCa->save();
                } else {
                    PatientBeforePreparation::create([
                        'patient_check_id' => $id,
                        'name' => 'dialysateNaKCa',
                        'medicine_equipment' => 1,
                        'medicine_equipment_id' => $dialysateNaKCa->Na_K_Ca->id,
                        'product_name' => $dialysateNaKCa->Na_K_Ca->product_name,
                        'amount' => 1,
                        'number' => 1,
                        'nurse_id' => $request->user()->id,
                        // 'check_time' => date('Y-m-d H:i:s'),
                        // 'check_nurse_id' => $request->user()->id
                    ]);
                }
                $dialysateNaKCa = $dialysateNaKCa->Na_K_Ca->product_name;
            } else {
                $dialysateNaKCa = null;
            }


            $prepare_systolic_blood_pressure = $request->input('systolic_blood_pressure');
            $prepare_diastolic_blood_pressure = $request->input('diastolic_blood_pressure');
            $prepare_p = $request->input('p');
            if ($patientCheck->start_time != null) {
                $bp_time = date('Y-m-d H:i:s', strtotime($patientCheck->start_time . ' -10 minute'));
            } else {
                $bp_time = date('Y-m-d H:i:s');
            }
            if ($prepare_systolic_blood_pressure != null && $prepare_diastolic_blood_pressure != null && $prepare_p != null) {
                $patientMidBpPData = PatientMidBpPDatas::where('patient_check_id', $id)->where('dispose_id', 1)->first();
                if ($patientMidBpPData != null) {
                    $patientMidBpPData->systolic_blood_pressure = $prepare_systolic_blood_pressure;
                    $patientMidBpPData->diastolic_blood_pressure = $prepare_diastolic_blood_pressure;
                    $patientMidBpPData->P = $prepare_p;
                    $patientMidBpPData->save();
                } else {
                    PatientMidBpPDatas::create([
                        'patient_check_id' => $id,
                        'time' => $bp_time,
                        'systolic_blood_pressure' => $prepare_systolic_blood_pressure,
                        'diastolic_blood_pressure' => $prepare_diastolic_blood_pressure,
                        'P' => $prepare_p,
                        'dispose_id' => 1,
                        'machine' => 0,
                        'nurse_id' => $request->user()->id,
                        'display' => 1
                    ]);
                }
            }

            $item1 = $request->input('item1') ? 1 : 0;
            $item2 = $request->input('item2') ? 1 : 0;
            $item3 = $request->input('item3') ? 1 : 0;
            $item4 = $request->input('item4');

            $nurse_id = $request->user()->id;

            $patientAfterBindingRecord = PatientAfterBindingRecord::where('patient_check_id', $id)->first();
            if ($patientAfterBindingRecord != null) {
                $patientAfterBindingRecord->item1 = $item1;
                $patientAfterBindingRecord->nurse1_id = $nurse_id;
                $patientAfterBindingRecord->item2 = $item2;
                $patientAfterBindingRecord->nurse2_id = $nurse_id;
                $patientAfterBindingRecord->item3 = $item3;
                $patientAfterBindingRecord->nurse3_id = $nurse_id;
                $patientAfterBindingRecord->item4 = $item4;
                $patientAfterBindingRecord->nurse4_id = $nurse_id;
                $patientAfterBindingRecord->save();
            } else {
                $patientAfterBindingRecord = PatientAfterBindingRecord::create([
                    'patient_check_id' => $id,
                    'item1' => $item1,
                    'nurse1_id' => $nurse_id,
                    'item2' => $item2,
                    'nurse2_id' => $nurse_id,
                    'item3' => $item3,
                    'nurse3_id' => $nurse_id,
                    'item4' => $item4,
                    'nurse4_id' => $nurse_id,
                ]);
            }


            // 1. 血管通路種類 (總共 4 格 + 1 格大小)
            $vascular_access_type = $request->input('vascular_access_type', []); // 前端傳來 ["3"]
            $vascular_access_type_size = $request->input('vascular_access_type_size', "");
            $vascular_access_type_fixed = [];

            // 迴圈 1~4，檢查有沒有在前端傳來的陣列中
            for ($i = 1; $i <= 4; $i++) {
                // 如果 $i 在陣列裡 (代表有勾選)，就存入 $i，否則存空字串
                $vascular_access_type_fixed[] = in_array((string)$i, $vascular_access_type) ? $i : "";
            }
            // 最後補上大小文字
            array_push($vascular_access_type_fixed, $vascular_access_type_size);
            // 轉成字串: ",,3,,size"
            $vascular_access_type_string = implode(',', $vascular_access_type_fixed);


            // 2. 血管通路位置 (總共 5 格 + 1 格備註)
            $vascular_access_location = $request->input('vascular_access_location', []);
            $vascular_access_location_note = $request->input('vascular_access_location_note', "");
            $vascular_access_location_fixed = [];
            for ($i = 1; $i <= 5; $i++) {
                $vascular_access_location_fixed[] = in_array((string)$i, $vascular_access_location) ? $i : "";
            }
            array_push($vascular_access_location_fixed, $vascular_access_location_note);
            $vascular_access_location_string = implode(',', $vascular_access_location_fixed);


            // 3. 意識狀態 (總共 7 格 + 1 格備註)
            $consciousness = $request->input('consciousness', []);
            $consciousness_note = $request->input('consciousness_note', "");
            $consciousness_fixed = [];
            for ($i = 1; $i <= 7; $i++) {
                $consciousness_fixed[] = in_array((string)$i, $consciousness) ? $i : "";
            }
            array_push($consciousness_fixed, $consciousness_note);
            $consciousness_string = implode(',', $consciousness_fixed);


            // 4. 血管通路 (總共 7 格 + 1 格備註)
            $vascular_access = $request->input('vascular_access', []);
            $vascular_access_note = $request->input('vascular_access_note', "");
            $vascular_access_fixed = [];
            for ($i = 1; $i <= 7; $i++) {
                $vascular_access_fixed[] = in_array((string)$i, $vascular_access) ? $i : "";
            }
            array_push($vascular_access_fixed, $vascular_access_note);
            $vascular_access_string = implode(',', $vascular_access_fixed);


            // 5. 皮膚狀況 (總共 2 格 + 1 格位置 + 1 格大小)
            $skins = $request->input('skins', []);
            $skins_location = $request->input('skins_location', "");
            $skins_size = $request->input('skins_size', "");
            $skins_fixed = [];
            for ($i = 1; $i <= 2; $i++) {
                $skins_fixed[] = in_array((string)$i, $skins) ? $i : "";
            }
            array_push($skins_fixed, $skins_location);
            array_push($skins_fixed, $skins_size);
            $skins_string = implode(',', $skins_fixed);


            $t = $request->input('t', null);
            $r = $request->input('r', null);
            $fs = $request->input('fs', null);
            $needle_location = $request->input('needle_location', null);
            $needle_number = $request->input('needle_number', null);

            $type = $request->input('type', 1);

            $patientBeforePhysiologicalData = PatientBeforePhysiologicalDatas::where('patient_check_id', $id)->first();
            if ($patientBeforePhysiologicalData != null) {
                $patientBeforePhysiologicalData->T = $t;
                $patientBeforePhysiologicalData->R = $r;
                $patientBeforePhysiologicalData->fs = $fs;
                $patientBeforePhysiologicalData->needle_location = $needle_location;
                $patientBeforePhysiologicalData->needle_number = $needle_number;
                $patientBeforePhysiologicalData->vascular_access_type = $vascular_access_type_string;
                $patientBeforePhysiologicalData->vascular_access_location = $vascular_access_location_string;
                $patientBeforePhysiologicalData->vascular_access = $vascular_access_string;
                $patientBeforePhysiologicalData->consciousness = $consciousness_string;
                $patientBeforePhysiologicalData->skin = $skins_string;
                $patientBeforePhysiologicalData->type = $type;
                $patientBeforePhysiologicalData->save();
            } else {
                PatientBeforePhysiologicalDatas::create([
                    'patient_check_id' => $id,
                    'type' => $type,
                    'T' => $t,
                    'R' => $r,
                    'fs' => $fs,
                    'needle_location' => $needle_location,
                    'needle_number' => $needle_number,
                    'vascular_access_type' => $vascular_access_type_string,
                    'vascular_access_location' => $vascular_access_location_string,
                    'vascular_access' => $vascular_access_string,
                    'consciousness' => $consciousness_string,
                    'skin' => $skins_string
                ]);
            }

            PatientBeforeDialysisMedicine::where('patient_check_id', $id)->delete();

            // 🔥 步驟 2：迴圈寫入前端傳來的新名單
            if ($request->has('dialysisMedicines')) {
                foreach ($request->dialysisMedicines as $item) {
                    PatientBeforeDialysisMedicine::create([
                        'patient_check_id' => $id,
                        // 注意：前端傳來的 JSON 在 PHP 裡通常是陣列，要用 ['key'] 取值
                        // 除非你有轉成 Object，不然 $item->medicine_id 可能會報錯
                        'medicine_id' => $item['medicine_id'], // 視你前端送什麼欄位
                        'medicine'    => $item['medicine'],
                        'amount'      => $item['amount'],
                        'route_id'    => $item['route_id'] ?? null, // 假設有這個欄位
                        'note'        => $item['note'] ?? null,
                        'nurse_id'    => $request->user()->id
                    ]);
                }
            }

            /////
        } else {
            $patientCheck->check_nurse_id = $request->user()->id;
        }
        $patientCheck->save();

        return response()->json([
            'status' => 200
        ]);
    }

    function doublePrepare($id, Request $request)
    {
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation->patient;

        $nurse_id = $request->user()->id;

        if ($patientCheck->prepare_nurse_id == $nurse_id) {
            return response()->json([
                'status' => 422,
                'message' => '不能由同一人進行 Double SIGN',
                'reason' => 'same_nurse',
            ], 422);
        }

        if ($patientCheck->prepare_nurse_id != null) {
            $patientCheck->check_nurse_id = $nurse_id;
        } else {
            return response()->json([
                'status' => 422,
                'message' => '整備已退回，請照護護理 SIGN 後再執行 Double SIGN',
                'reason' => 'prepare_not_signed',
            ], 422);
        }
        $patientCheck->save();

        return response()->json([
            'status' => 200
        ]);
    }

    function backPrepare($id, Request $request)
    {
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation->patient;

        $patientCheck->prepare_nurse_id = null;
        $patientCheck->check_nurse_id = null;
        $patientCheck->save();

        return response()->json([
            'status' => 200
        ]);
    }

    public function getPrepareSelectOptions()
    {
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

        return response()->json([
            'status' => 200,
            'options' => $obj
        ]);
    }
}
