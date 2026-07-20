<?php

namespace App\Http\Controllers;

use App\Models\DoctorApAnother;
use App\Models\DoctorApEquipments;
use App\Models\DoctorApLaboratory;
use App\Models\DoctorApMedicine;
use App\Models\DoctorApScience;
use App\Models\FollowUps;
use App\Models\HeparinRatioSetting;
use App\Models\MedicalEquipmen;
use App\Models\Medicine;
use App\Models\PatientAfterAdjustWeight;
use App\Models\PatientBeforeAdjustWeight;
use App\Models\PatientBeforePreparation;
use App\Models\PatientChangeBedRecord;
use App\Models\PatientCheck;
use App\Models\PatientDialysisInspectionRecord;
use App\Models\PatientDialysisMachineLong;
use App\Models\PatientDialysisMachineShort;
use App\Models\PatientDialysisManualRecord;
use App\Models\PatientDialysisWater;
use App\Models\PatientDialysisWeight;
use App\Models\PatientHctInspectionRecordNew;
use App\Models\PatientInterruptToiletAdjustWeight;
use App\Models\PatientInterruptToiletWeight;
use App\Models\PatientMidDialysisRecordNew;
use App\Models\PatientNotice;
use App\Models\PatientVascularAccessRecord;
use App\Models\TodayCarePatient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DialysisController extends Controller
{
    //
    public function getDialysis($id){
        // dd($id);
        # code...
        // return TodayCarePatient::where('nurse_id', $request->user()->id)->where('date', date('Y-m-d'))->pluck('patient_check_id');
        $array = [];
        // return $nurse_care_ids;
        $patientCheck = PatientCheck::findOrfail($id);
        
        $patient = $patientCheck->patient_reservation?->patient;
        if (!$patient) {
            return response()->json(['status' => 404, 'message' => '病患資料不存在'], 404);
        }
        if($patient->table_name == null){
            $patient->table_name = 'dialysis_'.$patient->id;
            $patient->save();
        }
        $patientMidDialysisRecord = PatientMidDialysisRecordNew::where('patient_check_id', $patientCheck->id)->whereNotNull('BLDF')->orderBy('HCDTTM', 'desc')->first();
        $patientMidDialysisRecordBP = PatientMidDialysisRecordNew::where('patient_check_id', $patientCheck->id)->whereNotNull('BDPS')->orderBy('HCDTTM', 'desc')->first();
        $patientDialysisWeight = PatientDialysisWeight::where('patient_id', $patient->id)->orderBy('id', 'desc')->first();
        $doctorApSciences_count = DoctorApScience::where('patient_check_id', $id)->where('nurse_status', 0)->count();
        $doctorApLaboratory_count = DoctorApLaboratory::where('patient_check_id', $id)->where('nurse_status', 0)->count();
        $doctorApEquipments_count = DoctorApEquipments::where('patient_check_id', $id)->where('nurse_status', 0)->count();
        $doctorApMedicine_count = DoctorApMedicine::where('patient_check_id', $id)->where('nurse_status', 0)->count();
        $doctorApAnother_count = DoctorApAnother::where('patient_check_id', $patientCheck->id)->where('nurse_status', 0)->count();
        $doctorApCount = $doctorApSciences_count+$doctorApLaboratory_count+$doctorApEquipments_count+$doctorApMedicine_count+$doctorApAnother_count;

        $doctorApSciences_count = DoctorApScience::where('patient_check_id', $id)->where('nurse_status', 2)->count();
        $doctorApLaboratory_count = DoctorApLaboratory::where('patient_check_id', $id)->where('nurse_status', 2)->count();
        $doctorApEquipments_count = DoctorApEquipments::where('patient_check_id', $id)->where('nurse_status', 2)->count();
        $doctorApMedicine_count = DoctorApMedicine::where('patient_check_id', $id)->where('nurse_status', 2)->count();
        $doctorApAnother_count = DoctorApAnother::where('patient_check_id', $patientCheck->id)->where('nurse_status', 2)->count();
        $doctorApFinishCount = $doctorApSciences_count+$doctorApLaboratory_count+$doctorApEquipments_count+$doctorApMedicine_count+$doctorApAnother_count;

        $update_time = "";
        $isTimeout = 0;
        
        
        $now = date('Y-m-d H:i:s');
        if($patientMidDialysisRecord != null){
            if($patientCheck->end_time == null){
                if($patientMidDialysisRecord->HCDTTM < date('Y-m-d H:i:s', strtotime($now.' - 10 minute'))){
                    $isTimeout = 1;
                    $patientDialysisManualRecord = PatientDialysisManualRecord::where('patient_check_id', $id)->where('HCDTTM', 'desc')->first();
                    if($patientDialysisManualRecord != null){
                        if($patientDialysisManualRecord->HCDTTM < date('Y-m-d H:i:s', strtotime($now.' - 10 minute'))){
                            $isTimeout = 1;
                        }
                        else{
                            $isTimeout = 0;
                        }
                        
                    }
                    
                }
            }
            $update_time = date('H:i', strtotime($patientMidDialysisRecord->HCDTTM));
        }

        if($patientCheck->end_time != null){
            $isEnd = 1;
        }
        else{
            $isEnd = 0;
        }

        $isSpecialDispose = 0;
        $interrupt_time = null;
        if($patientCheck->status == 4){
            $isSpecialDispose = 1;
            $interrupt_time = $patientCheck->interrupt_time;
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
        if($patientHctInspectionRecordNew != null){
            $lastHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->where('date', '<', $patientHctInspectionRecordNew->date)->orderBy('date', 'desc')->first();
        }
        else{
            $lastHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->where('date', '<', $currentDate)->orderBy('date', 'desc')->first();
        }

        $hct_threshold = 0;
        $needAddHct = 0;

        if($patientHctInspectionRecordNew != null && $lastHctInspectionRecordNew != null){
            if($patientHctInspectionRecordNew->hct_add == null){
                if($lastHctInspectionRecordNew->hct_add == null){
                    $hct_threshold = $lastHctInspectionRecordNew->hct - $patientHctInspectionRecordNew->hct;
                }
                else{
                    $hct_threshold = $lastHctInspectionRecordNew->hct_add - $patientHctInspectionRecordNew->hct;
                }
            }
        }

        if($hct_threshold >= 3 || $hct_threshold <= -2){
            $needAddHct = 1;
        }


        $obj = (object)[];
        $obj->isSpecialDispose = $isSpecialDispose;
        $obj->interrupt_time = $interrupt_time;
        $obj->needAddHct = $needAddHct;
        $obj->id = $patientCheck->id;
        if($patientCheck->start_time != null){
            $obj->start_time = date('H:i', strtotime($patientCheck->start_time));
        }
        else{
            $obj->start_time = null;
        }
        if($patientCheck->end_time != null){
            $obj->end_time = date('H:i', strtotime($patientCheck->end_time));
        }
        else{
            $obj->end_time = null;
        }
        $obj->patient_id = $patient->id;
        $obj->name = $patient->name;
        $obj->dialysisRecord = $patientMidDialysisRecord;
        $obj->dialysisBPRecord = $patientMidDialysisRecordBP;
        $obj->ap_count = $doctorApCount;
        $obj->ap_finish_count = $doctorApFinishCount;
        if($patientCheck->patient_reservation->morning_noon_night == 0){
            $obj->morning_noon_night = '早';
        }
        else if($patientCheck->patient_reservation->morning_noon_night == 1){
            $obj->morning_noon_night = '中';
        }
        else{
            $obj->morning_noon_night = '晚';
        }
        $week = [
            1 => '一',
            2 => '二',
            3 => '三',
            4 => '四',
            5 => '五',
            6 => '六',
            7 => '日',
        ];
        $obj->date = $patientCheck->date.'('.$week[date('N', strtotime($patientCheck->date))].')'.$obj->morning_noon_night;
        $obj->img = $patient->image_path;
        $obj->isTimeout = $isTimeout;
        $obj->update_time = $update_time;

        $obj->isEnd = $isEnd;

        $obj->bed_no = $patientCheck->patient_reservation?->machine_bed?->bed?->bed_no ?? '—';
        // $patientChangeBedRecord = PatientChangeBedRecord::where('patient_check_id', $patientCheck->id)->orderBy('time', 'DESC')->first();
        // if($patientChangeBedRecord != null){
        //     $machine_no = $patientChangeBedRecord->machine_no;
        // }
        // else{
            $machine_no = $patientCheck->patient_reservation?->machine_bed?->card?->no ?? '—';
        // }

        $obj->machine_no = $machine_no;
        
        

        //體重
        $before_real_weight = 0;
        $adjustWeight = 0;
        $adjustWeight = PatientBeforeAdjustWeight::where('patient_check_id', $id)->sum('weight');

        $before_real_weight = round($patientCheck->measure_weight_before + $adjustWeight, 1);
        $patientDialysisWeight = PatientDialysisWeight::where('patient_id', $patientCheck->patient_reservation->patient_id)->orderBy('id', 'desc')->first();
        $dryWeight = 0;
        if($patientDialysisWeight != null){
            $dryWeight = round($patientDialysisWeight->dry_weight, 1);
        }
        $waterWeight = round($before_real_weight - $dryWeight, 1);
        $waterWeightStr = $waterWeight;
        $interrupt_real_Weight = 0;
        $interruptAdjiustWeight = 0;
        $patientInterruptToiletWeight = PatientInterruptToiletWeight::where('patient_check_id', $id)->orderBy('id', 'desc')->first();
        if($patientInterruptToiletWeight != null){
            $patientInterruptToiletAdjustWeights = PatientInterruptToiletAdjustWeight::where('interrput_id', $patientInterruptToiletWeight->id)->get();
            foreach($patientInterruptToiletAdjustWeights as $count => $patientInterruptToiletAdjustWeight){
                $interruptAdjiustWeight += $patientInterruptToiletAdjustWeight->weight;
            }
            $interrupt_real_Weight = $patientInterruptToiletWeight->measure_weight + $interruptAdjiustWeight;
        }
        $after_real_weight = 0;
        $afterAdjustWeight = 0;
        if($patientCheck->measure_weight_after != null){
            $patientAfterAdjustWeights = PatientAfterAdjustWeight::where('patient_check_id', $id)->get();
            foreach($patientAfterAdjustWeights as $count => $patientAfterAdjustWeight){
                $afterAdjustWeight += $patientAfterAdjustWeight->weight;
            }
            $after_real_weight = $patientCheck->measure_weight_after + $afterAdjustWeight;
        }
        $realWaterWeight = 0;
        if($after_real_weight != 0){
            $realWaterWeight = round($before_real_weight - $after_real_weight, 1);
        }
        $paitentDialysisWater = PatientDialysisWater::where('patient_check_id', $id)->orderBy('id', 'DESC')->first();
        if($paitentDialysisWater != null){
            if($paitentDialysisWater->patient_ask == 1 && $waterWeight != $paitentDialysisWater->amount){
                $waterWeightStr = $waterWeight.'->PA'.$paitentDialysisWater->amount;
            }
            else{
                $waterWeightStr = $paitentDialysisWater->amount;
            }
            $waterWeight = $paitentDialysisWater->amount;
        }
        if($waterWeight != 0){
            $waterPersen = round($realWaterWeight/$waterWeight, 2)*100;
        }
        else if($waterWeight == $realWaterWeight){
            $waterPersen = 100;
        }
        else{
            $waterPersen = 0;
        }

        $obj->weight = $dryWeight;
        $obj->before = $before_real_weight;
        $obj->water = $waterWeightStr;
        $obj->after = $after_real_weight;
        $obj->waterPersen = $waterPersen;
        $obj->adjustWeight = $adjustWeight;

        $obj->measure_weight_before = $patientCheck->measure_weight_before;

        $adjustWeights = PatientBeforeAdjustWeight::with('item')
            ->where('patient_check_id', $id)
            ->get();

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

        

        if($patientCheck->prepare_nurse_id != null){
            $obj->prepare_nurse = $patientCheck->prepare_nurse->name;
        }
        if($patientCheck->check_nurse_id != null){
            $obj->check_nurse = $patientCheck->check_nurse->name;
        }
        if($patientCheck->care_nurse_id != null){
            // $nurses = User::select('name')->whereIn(explode(",", $patientCheck->care_nurse_id))->get();
            $nurses = User::whereIn('id', explode(",", $patientCheck->care_nurse_id))->pluck('name')->toArray();
            $obj->care_nurse = implode("、", $nurses);
            // $obj->care_nurse = $patientCheck->care_nurse->name;
        }
        if($patientCheck->care_end_nurse_id != null){
            $nurses = User::whereIn('id', explode(",", $patientCheck->care_end_nurse_id))->pluck('name')->toArray();
            $obj->off_nurse = implode("、", $nurses);
            // $obj->off_nurse = $patientCheck->care_end_nurse->name;
        }

        $patientNotice = PatientNotice::where('patient_check_id', $patientCheck->id)->first();
        $obj->note1 = "";
        $obj->note2 = "";
        $obj->note3 = "";
        $obj->note4 = "";
        if($patientNotice != null){
            if($patientNotice->note_1 != null){
                $obj->note1 = $patientNotice->note_1;
            }
            if($patientNotice->note_2 != null){
                $obj->note2 = $patientNotice->note_2;
            }
            if($patientNotice->note_3 != null){
                $obj->note3 = $patientNotice->note_3;
            }
            if($patientNotice->note_4 != null){
                $obj->note4 = $patientNotice->note_4;
            }
        }

        $patientDialysisMachineLongs = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('end_date', null)->orderBy('dialysis_machine_id', 'asc')->get();
        $patientDialysisMachineShorts = PatientDialysisMachineShort::where('patient_id', $patient->id)->where('end_date', null)->orderBy('dialysis_machine_id', 'asc')->get();
        
        $initial_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 10)->first();
        $priming_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 11)->first();
        $maintain_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 12)->first();
        $initial_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 10)->first();
        $priming_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 11)->first();
        $maintain_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 12)->first();

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
        
        
        $heparinRatioSetting = HeparinRatioSetting::first();
        if($heparinRatioSetting != null){
            $radio = $heparinRatioSetting->ratio;
        }
        else{
            $radio = 1;
        }

        if($initial != null){
            $initial_value = $initial->value;
        }
        else{
            $initial_value = 0;
        }

        if($maintain != null){
            $maintain_value = $maintain->value;
        }
        else{
            $maintain_value = 0;
        }

        $durationPrepare = PatientBeforePreparation::where('patient_check_id', $patientCheck->id)->where('name', 'duration')->first();
        if($durationPrepare != null){
            $obj->durationPrepare = 1;
        }
        else{
            $obj->durationPrepare = 0;
        }

        
        $initial_primingPrepare = PatientBeforePreparation::where('patient_check_id', $patientCheck->id)->where('name', 'initial_priming')->first();
        if($initial_primingPrepare != null){
            $total = $initial_primingPrepare->product_name;
            $obj->initial_primingPrepare = 1;
        }
        else{
            if($priming != null){
                $obj->initial_primingPrepare = 1;
            }
            else{
                $obj->initial_primingPrepare = 0;
            }
            
            $total = 0;
        }
        if($patientMidDialysisRecord != null && $patientMidDialysisRecord->HPCV != null){
            $hpcv = ($patientMidDialysisRecord->HPCV/10);    
        }
        else{
            $hpcv = 0;
        }
        $residual = ($total - $hpcv);

        $obj->residual = $residual;

        $patientDialysisInspectionRecords = PatientDialysisInspectionRecord::where('patient_id', $patient->id)->where('status', 0)->get();
        if(count($patientDialysisInspectionRecords) > 0){
            $obj->haveNewInspectionData = 1;
        }
        else{
            $obj->haveNewInspectionData = 0;
        }
        

        

        return response()->json([
            'status' => 200,
            'patientCheck' => $obj
        ]);
    }

    public function getPrepareSelectOptions(){
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
