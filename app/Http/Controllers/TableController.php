/* @deprecated - 舊架構遺留，dialysis_N 個別病患表已不存在，請改用統一 schema patient_mid_dialysis_record_news */
<?php

namespace App\Http\Controllers;

use App\Models\DialysisDispose;
use App\Models\HeparinRatioSetting;
use App\Models\PatientBeforePreparation;
use App\Models\PatientCheck;
use App\Models\PatientDialysisMachineLong;
use App\Models\PatientDialysisMachineShort;
use App\Models\PatientDialysisManualRecord;
use App\Models\PatientMidBpPDatas;
use App\Models\PatientMidDialysisRecordNew;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TableController extends Controller
{
    //
    public function index($id)
    {
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation->patient;
        # code...
        $tableDialysisRecords = [];
        $tableDialysisRecordBloods = [];
        $tableDialysisRecordBloodAnothers = [];

        $initial_primingPrepare = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'initial_priming')->first();
        if($initial_primingPrepare != null){
            $total = $initial_primingPrepare->product_name;
        }
        else{
            $total = 0;
        }
        
        $prepare_duration = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'duration')->first();
        if($prepare_duration != null && $prepare_duration->product_name != null){
            $uftm = $prepare_duration->product_name;
            for($i=$uftm; $i>0; $i-=60){
                if(count($tableDialysisRecords) > 0){
                    $patientMidDialysisRecord = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->where('HCDTTM', '>=', end($tableDialysisRecords)->HCDTTM)->where('UFTM', '>', 0)->where('UFTM', '<', $i)->where('UFTM', '>=', $i-60)->where('VEPS', '>', 0)->whereNotNull('BLDF')->orderBy('HCDTTM', 'asc')->first();
                }
                else{
                    $patientMidDialysisRecord = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->where('HCDTTM', '>=', $patientCheck->date)->where('UFTM', '>', 0)->where('UFTM', '<', $i)->where('UFTM', '>=', $i-60)->where('VEPS', '>', 0)->whereNotNull('BLDF')->orderBy('HCDTTM', 'asc')->first();
                    
                }
                
                if($patientMidDialysisRecord != null){
                    if($i == $uftm){
                        $patientMidDialysisRecordBloodBefore = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->whereNotNull('BDPS')->orderBy('HCDTTM', 'asc')->first();
                        if($patientMidDialysisRecordBloodBefore != null){
                            array_push($tableDialysisRecordBloodAnothers, $patientMidDialysisRecordBloodBefore);
                        }
                    }
                    $patientMidDialysisRecordBlood = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->where('HCDTTM', '>=', $patientCheck->date)->whereRaw('HCDTTM BETWEEN \''.date('Y-m-d H:i:s', strtotime($patientMidDialysisRecord->HCDTTM.' -5 minutes')).'\' AND \''.date('Y-m-d H:i:s', strtotime($patientMidDialysisRecord->HCDTTM.' +5 minutes')).'\'')->whereNotNull('BDPS')->orderBy('HCDTTM', 'asc')->first();
                    if($patientMidDialysisRecordBlood != null){
                        array_push($tableDialysisRecordBloods, $patientMidDialysisRecordBlood);
                    }
                    $datetime = Carbon::parse($patientMidDialysisRecord->HCDTTM);
                    $start = $datetime->copy()->format('Y-m-d H:i');
                    $end = $datetime->copy()->addMinutes(5)->format('Y-m-d H:i:s');
                    $formattedDatetime = $datetime->format('Y-m-d H:i');
                    $patientMidDialysisRecordReal = DB::table($patient->table_name)->where('patient_check_id', $id)->where('VEPS', '>', 0)->whereNotNull('BLDF')->whereBetween('HCDTTM', [$start, $end])->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, HCDTTM, ?))', [$formattedDatetime])->first();
                    if($patientMidDialysisRecordReal != null){
                        $patientDialysisManualRecord = PatientDialysisManualRecord::where('patient_check_id', $id)->whereNotNull('HMNO')->whereBetween('HCDTTM', [$start, $end])->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, HCDTTM, ?))', [$formattedDatetime])->first();
                        if($patientDialysisManualRecord == null){
                            $recordArray = (array) $patientMidDialysisRecordReal;
                            // 移除不需要的屬性
                            unset($recordArray['id']);
                            unset($recordArray['created_at']);
                            unset($recordArray['updated_at']);

                            
                            foreach($recordArray as $key => $value){
                                // if($key == 'HCDTTM'){
                                //     $recordArray[$key] = date('H:i', strtotime($value));
                                // }
                                if($key == 'DLTP'){
                                    $recordArray[$key] /= 10;
                                }
                                else if($key == 'CDCT'){
                                    $recordArray[$key] /= 4;
                                }
                                else if($key == 'UFRA'){
                                    $recordArray[$key] /= 1000;
                                }
                                else if($key == 'UF'){
                                    $recordArray[$key] = round($value/1000, 2);
                                }
                                else if($key == 'HPCV'){
                                    $recordArray[$key] = round($total - ($value/10), 1);
                                }
                            }

                            $patientDialysisManualRecord = PatientDialysisManualRecord::create($recordArray);
                        }
                        array_push($tableDialysisRecords, $patientDialysisManualRecord);
                    }

                    if($i<=60){
                        if($patientCheck->end_time != null){
                            if(count($tableDialysisRecords) > 0){
                                $patientMidDialysisRecordLatest = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->where('HCDTTM', '>=', end($tableDialysisRecords)->HCDTTM)->where('VEPS', '>', 0)->whereNotNull('BLDF')->orderBy('UF', 'desc')->first();
                            }
                            else{
                                $patientMidDialysisRecordLatest = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->where('HCDTTM', '>=', $patientCheck->date)->where('VEPS', '>', 0)->whereNotNull('BLDF')->orderBy('UF', 'desc')->first();
                            }
    
                            if($patientMidDialysisRecordLatest != null){
                                $datetime = Carbon::parse($patientMidDialysisRecordLatest->HCDTTM);
                                $start = $datetime->copy()->format('Y-m-d H:i');
                                $end = $datetime->copy()->addMinutes(5)->format('Y-m-d H:i:s');
                                $formattedDatetime = $datetime->format('Y-m-d H:i');
                                $patientMidDialysisRecordReal = DB::table($patient->table_name)->where('patient_check_id', $id)->where('VEPS', '>', 0)->whereNotNull('BLDF')->whereBetween('HCDTTM', [$start, $end])->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, HCDTTM, ?))', [$formattedDatetime])->first();
                                if($patientMidDialysisRecordReal != null){
                                    $patientDialysisManualRecord = PatientDialysisManualRecord::where('patient_check_id', $id)->whereNotNull('HMNO')->whereBetween('HCDTTM', [$start, $end])->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, HCDTTM, ?))', [$formattedDatetime])->first();
                                    if($patientDialysisManualRecord == null){
                                        $recordArray = (array) $patientMidDialysisRecordReal;
                                        // 移除不需要的屬性
                                        unset($recordArray['id']);
                                        unset($recordArray['created_at']);
                                        unset($recordArray['updated_at']);
    
                                        
                                        foreach($recordArray as $key => $value){
                                            // if($key == 'HCDTTM'){
                                            //     $recordArray[$key] = date('H:i', strtotime($value));
                                            // }
                                            if($key == 'DLTP'){
                                                $recordArray[$key] /= 10;
                                            }
                                            else if($key == 'CDCT'){
                                                $recordArray[$key] /= 4;
                                            }
                                            else if($key == 'UFRA'){
                                                $recordArray[$key] /= 1000;
                                            }
                                            else if($key == 'UF'){
                                                $recordArray[$key] = round($value/1000, 2);
                                            }
                                            else if($key == 'HPCV'){
                                                $recordArray[$key] = round($total - ($value/10), 1);
                                            }
                                        }
    
                                        $patientDialysisManualRecord = PatientDialysisManualRecord::create($recordArray);
                                    }
                                    array_push($tableDialysisRecords, $patientDialysisManualRecord);
                                }
                            }
                        }
                    }
                }
                else if($i<60){
                    if($patientCheck->end_time != null){
                        if(count($tableDialysisRecords) > 0){
                            $patientMidDialysisRecordLatest = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->where('HCDTTM', '>=', end($tableDialysisRecords)->HCDTTM)->where('VEPS', '>', 0)->whereNotNull('BLDF')->orderBy('UF', 'desc')->first();
                        }
                        else{
                            $patientMidDialysisRecordLatest = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->where('HCDTTM', '>=', $patientCheck->date)->where('VEPS', '>', 0)->whereNotNull('BLDF')->orderBy('UF', 'desc')->first();
                        }

                        if($patientMidDialysisRecordLatest != null){
                            $datetime = Carbon::parse($patientMidDialysisRecordLatest->HCDTTM);
                            $start = $datetime->copy()->format('Y-m-d H:i');
                            $end = $datetime->copy()->addMinutes(5)->format('Y-m-d H:i:s');
                            $formattedDatetime = $datetime->format('Y-m-d H:i');
                            $patientMidDialysisRecordReal = DB::table($patient->table_name)->where('patient_check_id', $id)->where('VEPS', '>', 0)->whereNotNull('BLDF')->whereBetween('HCDTTM', [$start, $end])->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, HCDTTM, ?))', [$formattedDatetime])->first();
                            if($patientMidDialysisRecordReal != null){
                                $patientDialysisManualRecord = PatientDialysisManualRecord::where('patient_check_id', $id)->whereNotNull('HMNO')->whereBetween('HCDTTM', [$start, $end])->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, HCDTTM, ?))', [$formattedDatetime])->first();
                                if($patientDialysisManualRecord == null){
                                    $recordArray = (array) $patientMidDialysisRecordReal;
                                    // 移除不需要的屬性
                                    unset($recordArray['id']);
                                    unset($recordArray['created_at']);
                                    unset($recordArray['updated_at']);

                                    
                                    foreach($recordArray as $key => $value){
                                        // if($key == 'HCDTTM'){
                                        //     $recordArray[$key] = date('H:i', strtotime($value));
                                        // }
                                        if($key == 'DLTP'){
                                            $recordArray[$key] /= 10;
                                        }
                                        else if($key == 'CDCT'){
                                            $recordArray[$key] /= 4;
                                        }
                                        else if($key == 'UFRA'){
                                            $recordArray[$key] /= 1000;
                                        }
                                        else if($key == 'UF'){
                                            $recordArray[$key] = round($value/1000, 2);
                                        }
                                        else if($key == 'HPCV'){
                                            $recordArray[$key] = round($total - ($value/10), 1);
                                        }
                                    }

                                    $patientDialysisManualRecord = PatientDialysisManualRecord::create($recordArray);
                                }
                                array_push($tableDialysisRecords, $patientDialysisManualRecord);
                            }
                        }
                    }
                }
            }
        }

        $tableDialysisRecordBlood1 = PatientMidBpPDatas::where('patient_check_id', $patientCheck->id)->where('dispose_id', 1)->orderBy('id', 'desc')->first();
        $tableDialysisRecordBlood2 = PatientMidBpPDatas::where('patient_check_id', $patientCheck->id)->where('dispose_id', 2)->orderBy('id', 'desc')->first();
        $tableDialysisRecordBlood3 = PatientMidBpPDatas::where('patient_check_id', $patientCheck->id)->where('dispose_id', 3)->orderBy('id', 'desc')->first();
        

        $initial_primingPrepare = PatientBeforePreparation::where('patient_check_id', $id)->where('name', 'initial_priming')->first();
        if($initial_primingPrepare != null){
            $total = $initial_primingPrepare->product_name;
        }
        else{
            $total = 0;
        }

        $patientDialysisManualRecords = PatientDialysisManualRecord::select('*', DB::raw('DATE_FORMAT(HCDTTM, "%Y-%m-%d %H:%i") as formatted_HCDTTM'))
        ->where('patient_check_id', $patientCheck->id)
        ->where('deleted', 0)
        ->orderBy('formatted_HCDTTM')
        ->orderByDesc('id')
        ->get()
        ->unique('formatted_HCDTTM');
        // dd($patientDialysisManualRecords);

        $machineDialysisRecords = [];
        $tableDialysisRecords = [];
        foreach($patientDialysisManualRecords as $count => $tableDialysisRecord){
            $datetime = Carbon::parse(date('Y-m-d H:i', strtotime($tableDialysisRecord->HCDTTM)));
            $start = $datetime->copy()->format('Y-m-d H:i');
            $end = $datetime->copy()->addMinutes(1)->subSeconds(1)->format('Y-m-d H:i:s');
            $formattedDatetime = $datetime->format('Y-m-d H:i');

            $patientDialysisManualRecord = PatientDialysisManualRecord::where('patient_check_id', $patientCheck->id)->whereBetween('HCDTTM', [$start, $end])->where('HMNO', null)->orderByRaw('ABS(TIMESTAMPDIFF(MINUTE, HCDTTM, ?))', [$formattedDatetime])->orderBy('id', 'desc')->first();
            $machineDialysisRecord = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->where('VEPS', '>', 0)->whereNotNull('BLDF')->whereBetween('HCDTTM', [$start, $end])->orderByRaw('ABS(TIMESTAMPDIFF(MINUTE, HCDTTM, ?))', [$formattedDatetime])->first();            
            
            $machineDialysisRecord_array = (array)$machineDialysisRecord;
            foreach($machineDialysisRecord_array as $key => $value){
                if($key == 'HCDTTM'){
                    $machineDialysisRecord->$key = date('H:i', strtotime($value));
                }
                if($key == 'DLTP'){
                    $machineDialysisRecord->$key /= 10;
                }
                else if($key == 'CDCT'){
                    $machineDialysisRecord->$key /= 4;
                }
                else if($key == 'UFRA'){
                    $machineDialysisRecord->$key /= 1000;
                }
                else if($key == 'UF'){
                    $machineDialysisRecord->$key = round($machineDialysisRecord->$key/1000, 2);
                }
                else if($key == 'HPCV'){
                    
                    $machineDialysisRecord->$key = round($total - ($machineDialysisRecord->$key/10), 1);
                    // dd($count);
                }
            }

            if($machineDialysisRecord != null){
                $machineDialysisRecord->dispose_id = 0;
                $machineDialysisRecord->ak_id = 0;
                $machineDialysisRecord->ns_value = 0;
                $machineDialysisRecord->line_fix = 1;
                $machineDialysisRecord->pinhole_blood = 0;
            }
            
            if($tableDialysisRecord->dispose_id == null){
                $tableDialysisRecord->dispose_id = 0;
            }

            if($tableDialysisRecord->ak_id == null){
                $tableDialysisRecord->ak_id = 0;
            }

            if($tableDialysisRecord->ns_value == null){
                $tableDialysisRecord->ns_value = 0;
            }

            if($tableDialysisRecord->line_fix == null){
                $tableDialysisRecord->line_fix = 1;
            }

            if($tableDialysisRecord->pinhole_blood == null){
                $tableDialysisRecord->pinhole_blood = 0;
            }

            

            array_push($machineDialysisRecords, $machineDialysisRecord);

            if($machineDialysisRecord != null){
                $machineDialysisRecord->id = $tableDialysisRecord->id;
            }
            array_push($tableDialysisRecords, $tableDialysisRecord);
            
            
        }

        if($patientCheck->status == 3 || $patientCheck->status == 5){
            if(count($tableDialysisRecords) > 0){
                if(count($tableDialysisRecords) < 9){
                    $lastUF = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->orderBy('UF', 'DESC')->first();
                    $lastObject = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->orderBy('UF', 'DESC')->first();
                    if($lastObject != null){
                        $lastKeys = array_keys(get_object_vars($lastObject));
                        $tableEndUF = new \stdClass();
                        foreach ($lastKeys as $key) {
                            if($key == 'VEPS'){
                                $tableEndUF->$key = 'END UF'; 
                            }
                            else if($key == 'UF'){
                                $tableEndUF->$key = round($lastUF->UF/1000, 1);
                            }
                            else{
                                $tableEndUF->$key = ''; 
                            }
                        }
                        array_push($tableDialysisRecords, $tableEndUF);
                    }
                }
                else{
                    $lastUF = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->orderBy('UF', 'DESC')->first();
                    $tableEndUF = DB::table($patient->table_name)->where('patient_check_id', $patientCheck->id)->orderBy('UF', 'DESC')->first();
                    if($tableEndUF != null){
                        $lastKeys = array_keys(get_object_vars($tableEndUF));
                        foreach ($lastKeys as $key) {
                            if($key == 'VEPS'){
                                $tableEndUF->$key = 'END UF'; 
                            }
                            else if($key == 'UF'){
                                $tableEndUF->$key = round($lastUF->UF/1000, 1);
                            }
                            else{
                                $tableEndUF->$key = ''; 
                            }
                        }
                    }
                }
            }
        }

        $patientDialysisMachineLongs = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('end_date', null)->orderBy('dialysis_machine_id', 'asc')->get();
        $patientDialysisMachineShorts = PatientDialysisMachineShort::where('patient_id', $patient->id)->where('end_date', null)->orderBy('dialysis_machine_id', 'asc')->get();

        $hfi_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 9)->first();
        $initial_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 10)->first();
        $priming_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 11)->first();
        $maintain_long = $patientDialysisMachineLongs->where('dialysis_machine_id', 12)->first();
        $hfi_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 9)->first();
        $initial_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 10)->first();
        $priming_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 11)->first();
        $maintain_short = $patientDialysisMachineShorts->where('dialysis_machine_id', 12)->first();


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
        
        $heparinRatioSetting = HeparinRatioSetting::first();
        if($heparinRatioSetting != null){
            $radio = $heparinRatioSetting->ratio;
        }
        else{
            $radio = 1;
        }
        
        if($hfi != null){
            $hfi_value = $hfi->value;
        }
        else{
            $hfi_value = null;
        }

        if($initial != null){
            $initial_value = $initial->value;
        }
        else{
            $initial_value = 0;
        }

        if($maintain != null){
            $maintain_value = $maintain->value/$radio;
        }
        else{
            $maintain_value = 0;
        }

        if($priming != null){
            $priming_value = $priming->value;
        }
        else{
            $priming_value = 0;
        }

        if($patientCheck->start_time != null){
            $bp_start_time = date('H:i', strtotime($patientCheck->start_time.' -10 minute'));
        }
        else{
            $bp_start_time = date('H:i');
        }

        if($patientCheck->end_time != null){
            $bp_end_time = date('H:i', strtotime($patientCheck->end_time.' +15 minute'));
        }
        else{
            $bp_end_time = date('H:i');
        }

        $dialysisDispose = DialysisDispose::where('deleted', 0)->get();


        

        return json_encode([
            'status' => 200,
            'patientMidDialysisRecords' => $tableDialysisRecords,
            'tableDialysisRecordBlood1' => $tableDialysisRecordBlood1,
            'tableDialysisRecordBlood2' => $tableDialysisRecordBlood2,
            'tableDialysisRecordBlood3' => $tableDialysisRecordBlood3,
            'total' => $total,
            'radio' => $radio,
            'maintain' => $maintain_value,
            'machineDialysisRecords' => $machineDialysisRecords,
            'bp_start_time' => $bp_start_time,
            'bp_end_time' => $bp_end_time,
            'dialysisDispose' => $dialysisDispose,
            'hfi_value' => $hfi_value
        ]);
    }

    function store(Request $request, $id) {
        # code...
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation->patient;

        if($patient->table_name == null){
            $patient->table_name = 'dialysis_'.$patient->id;
            Schema::create($patient->table_name, function (Blueprint $table) {
                $table->id();
                $table->timestamp('HCDTTM')->nullable();
                $table->integer('BDPS')->nullable();
                $table->integer('BDPD')->nullable();
                $table->integer('BDPL')->nullable();
                $table->integer('BLDF')->nullable();
                $table->integer('VEPS')->nullable();
                $table->integer('TMP')->nullable();
                $table->integer('UF')->nullable();
                $table->string('HPMG')->nullable();
                $table->integer('DLTP')->nullable();
                $table->integer('PBTP')->nullable();
                $table->integer('CDCT')->nullable();
                $table->string('HPST')->nullable();
                $table->integer('DLFL')->nullable();
                $table->string('HMMN')->nullable();
                $table->string('HMNO')->nullable();
                $table->integer('MAP')->nullable();
                $table->string('KTV')->nullable();
                $table->string('HCT')->nullable();
                $table->string('PKTV')->nullable();
                $table->integer('HPDR')->nullable();
                $table->integer('HPCV')->nullable();
                $table->integer('HPBV')->nullable();
                $table->integer('BBADJ')->nullable();
                $table->integer('HMOT')->nullable();
                $table->string('PBAF')->nullable();
                $table->integer('UFRA')->nullable();
                $table->integer('UFTM')->nullable();
                $table->integer('UFVL')->nullable();
                $table->string('ADPKID')->nullable();
                $table->integer('patient_check_id')->nullable();
                $table->timestamps();
            });
            $patient->save();
        }
        else{
            if (!Schema::hasTable($patient->table_name)) {
                Schema::create($patient->table_name, function (Blueprint $table) {
                    $table->id();
                    $table->timestamp('HCDTTM')->nullable();
                    $table->integer('BDPS')->nullable();
                    $table->integer('BDPD')->nullable();
                    $table->integer('BDPL')->nullable();
                    $table->integer('BLDF')->nullable();
                    $table->integer('VEPS')->nullable();
                    $table->integer('TMP')->nullable();
                    $table->integer('UF')->nullable();
                    $table->string('HPMG')->nullable();
                    $table->integer('DLTP')->nullable();
                    $table->integer('PBTP')->nullable();
                    $table->integer('CDCT')->nullable();
                    $table->string('HPST')->nullable();
                    $table->integer('DLFL')->nullable();
                    $table->string('HMMN')->nullable();
                    $table->string('HMNO')->nullable();
                    $table->integer('MAP')->nullable();
                    $table->string('KTV')->nullable();
                    $table->string('HCT')->nullable();
                    $table->string('PKTV')->nullable();
                    $table->integer('HPDR')->nullable();
                    $table->integer('HPCV')->nullable();
                    $table->integer('HPBV')->nullable();
                    $table->integer('BBADJ')->nullable();
                    $table->integer('HMOT')->nullable();
                    $table->string('PBAF')->nullable();
                    $table->integer('UFRA')->nullable();
                    $table->integer('UFTM')->nullable();
                    $table->integer('UFVL')->nullable();
                    $table->string('ADPKID')->nullable();
                    $table->integer('patient_check_id')->nullable();
                    $table->timestamps();
                });
            }
        }
        
        $record_id = $request->input('record_id');
        $HCDTTM = $patientCheck->date.' '.$request->input('HCDTTM');
        $BDPS = $request->input('BDPS');
        $BDPD = $request->input('BDPD');
        $BDPL = $request->input('BDPL');
        $BLDF = $request->input('BLDF');
        $VEPS = $request->input('VEPS');
        $TMP = $request->input('TMP');
        $DLTP = $request->input('DLTP');
        $CDCT = $request->input('CDCT');
        $DLFL = $request->input('DLFL');
        $UFRA = $request->input('UFRA');
        $HPCV = $request->input('HPCV');
        $UF = $request->input('UF');
        $dispose_id = $request->input('dispose_id');
        $note = $request->input('note');
        $isSpecialDispose = $request->input('isSpecialDispose');
        $data_type = $request->input('data_type');
        $ns_value = $request->input('ns_value');
        $ak_id = $request->input('ak_id');
        $line_fix = $request->input('line_fix');
        $pinhole_blood = $request->input('pinhole_blood');
        
        // return $record_id;

        
        if($record_id == 0){
            //create

            
            
            if($data_type != 0){
                if($BDPS != '' && $BDPD != '' && $BDPL != ''){
                    $patientMidBpPData = PatientMidBpPDatas::create([
                        'patient_check_id' => $id,
                        'time' => date('Y-m-d').' '.$request->input('HCDTTM'),
                        'systolic_blood_pressure' => $BDPS,
                        'diastolic_blood_pressure' => $BDPD,
                        'P' => $BDPL,
                        'dispose_id' => $data_type,
                        'machine' => 0,
                        'nurse_id' => $request->user()->id,
                        'display' => 1
                    ]);
                }
                else{
                    return json_encode([
                        'status' => 0,
                        'message' => "資料不完整"
                    ]);
                }
            }
            else{
                PatientDialysisManualRecord::create([
                    'patient_check_id' => $id,
                    'HCDTTM' => $HCDTTM,
                    'BDPS' => $BDPS,
                    'BDPD' => $BDPD,
                    'BDPL' => $BDPL,
                    'BLDF' => $BLDF,
                    'VEPS' => $VEPS,
                    'TMP' => $TMP,
                    'DLTP' => $DLTP,
                    'CDCT' => $CDCT,
                    'DLFL' => $DLFL,
                    'UFRA' => $UFRA,
                    'HPCV' => $HPCV,
                    'UF' => $UF,
                    'dispose_id' => $dispose_id,
                    'note' => $note,
                    'ns_value' => $ns_value,
                    'ak_id' => $ak_id,
                    'line_fix' => $line_fix,
                    'pinhole_blood' => $pinhole_blood
                ]);
            }
        }
        else{
            //update
            $patientDialysisManualRecord = PatientDialysisManualRecord::findOrFail($record_id);
            $patientDialysisManualRecord->HCDTTM = $HCDTTM;
            $patientDialysisManualRecord->BDPS = $BDPS;
            $patientDialysisManualRecord->BDPD = $BDPD;
            $patientDialysisManualRecord->BDPL = $BDPL;
            $patientDialysisManualRecord->BLDF = $BLDF;
            $patientDialysisManualRecord->VEPS = $VEPS;
            $patientDialysisManualRecord->TMP = $TMP;
            $patientDialysisManualRecord->DLTP = $DLTP;
            $patientDialysisManualRecord->CDCT = $CDCT;
            $patientDialysisManualRecord->DLFL = $DLFL;
            $patientDialysisManualRecord->UFRA = $UFRA;
            $patientDialysisManualRecord->HPCV = $HPCV;
            $patientDialysisManualRecord->UF = $UF;
            $patientDialysisManualRecord->dispose_id = $dispose_id;
            $patientDialysisManualRecord->note = $note;
            $patientDialysisManualRecord->ns_value = $ns_value;
            $patientDialysisManualRecord->ak_id = $ak_id;
            $patientDialysisManualRecord->line_fix = $line_fix;
            $patientDialysisManualRecord->pinhole_blood = $pinhole_blood;
            
            $patientDialysisManualRecord->save();
            
        }

        if($isSpecialDispose == 1){
            if($patientCheck->status != 4){
                $patientCheck->status = 4;
            }
            else{
                if($patientCheck->end_time != null){
                    $patientCheck->status = 3;
                }
                else{
                    $patientCheck->status = 2;
                }
            }
            
            $patientCheck->save();
        }
        return json_encode([
            'status' => 200
        ]);
    }

    function delete($record_id){
        if($record_id != 0){
            $patientDialysisManualRecord = PatientDialysisManualRecord::findOrFail($record_id);
            $patientDialysisManualRecord->deleted = 1;
            $patientDialysisManualRecord->save();
        }

        return json_encode([
            'status' => 200
        ]);
    }


    public function getRecordByTime($id, Request $request)
    {
        // 1. 取得參數
        $time = $request->input('time'); // 格式 HH:mm
        
        if (!$time) {
            return response()->json(null); // 前端判斷 if(data) 會是 false
        }

        // 2. 撈取病人資料與機器編號 (使用 optional 避免中間 null 報錯)
        $patientCheck = PatientCheck::with(['patient_reservation.machine_bed.card', 'patient_reservation.patient'])
            ->findOrFail($id);

        // 取得機器編號 (如果中間有斷層，回傳 null)
        $machine_no = optional(optional(optional($patientCheck->patient_reservation)->machine_bed)->card)->card_no;

        if (!$machine_no) {
            return response()->json(['error' => '找不到機器編號'], 404);
        }

        // 3. 設定搜尋時間範圍
        // 解析日期與時間
        $targetTime = Carbon::createFromFormat('Y-m-d H:i', $patientCheck->date . ' ' . $time);
        
        // 設定範圍：搜尋該時間點 "往後 5 分鐘內" 的資料 (依據你原本的邏輯)
        $start = $targetTime->format('Y-m-d H:i:00');
        $end = $targetTime->copy()->addMinutes(5)->format('Y-m-d H:i:59');

        // 4. 撈取機器資料
        // 假設 table_name 有值才查 (或是直接查，因為 Model 已經綁定了)
        $query = PatientMidDialysisRecordNew::where('HMNO', $machine_no)
            ->whereBetween('HCDTTM', [$start, $end])
            ->where('VEPS', '>', 0); // 過濾掉靜脈壓為 0 的無效數據

        // 找出 "時間最接近" targetTime 的那一筆
        // TIMESTAMPDIFF(SECOND, ...) 算出秒數差，ABS 取絕對值
        $record = $query->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, HCDTTM, ?))', [$targetTime->format('Y-m-d H:i:s')])
            ->first();

        // 5. 如果有撈到資料，進行數值計算與格式化
        if ($record) {
            // --- 計算 HPCV (抗凝劑餘量) ---
            // 取得預充量 (Initial Priming)
            $initialPriming = PatientBeforePreparation::where('patient_check_id', $id)
                ->where('name', 'initial_priming')
                ->first();

            // 轉成浮點數避免錯誤，預設 0
            $total = $initialPriming ? (float)$initialPriming->product_name : 0; 

            if ($record->HPCV !== null) {
                // 邏輯：總量 - (機器讀值 / 10) = 餘量
                $record->HPCV = round($total - ($record->HPCV / 10), 1);
            }

            // --- 其它數值單位換算 ---
            
            // 時間只留 HH:mm
            if ($record->HCDTTM) {
                $record->HCDTTM = Carbon::parse($record->HCDTTM)->format('H:i');
            }

            // 溫度 / 10
            if ($record->DLTP !== null) {
                $record->DLTP = $record->DLTP / 10;
            }

            // 傳導度 / 4 (依據你的原始碼)
            if ($record->CDCT !== null) {
                $record->CDCT = $record->CDCT / 4;
            }

            // 脫水速率 / 1000
            if ($record->UFRA !== null) {
                $record->UFRA = $record->UFRA / 1000;
            }

            // 脫水量 / 1000 並取小數 2 位
            if ($record->UF !== null) {
                $record->UF = round($record->UF / 1000, 2);
            }

            // 回傳處理後的資料物件
            // 前端 axios 收到後，data 就會是這個物件
            return response()->json($record);
        }

        // 6. 沒撈到資料
        return response()->json(null); // 或回傳空物件 []
    }
}
