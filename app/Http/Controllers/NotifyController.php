<?php

namespace App\Http\Controllers;

use App\Models\DoctorApAnother;
use App\Models\DoctorApEquipments;
use App\Models\DoctorApLaboratory;
use App\Models\DoctorApMedicine;
use App\Models\DoctorApScience;
use App\Models\PatientCheck;
use App\Models\PatientDialysisInspectionRecord;
use App\Models\PatientDialysisMachineLong;
use App\Models\PatientDialysisMedicine;
use App\Models\PatientDialysisWeight;
use App\Models\PatientHctInspectionRecordNew;
use App\Models\PatientNotice;
use App\Models\EducationExecutionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NotifyController extends Controller
{
    //
    public function getNotify($id)
    {
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation->patient;

        // 取得各類醫囑資料
        $doctorApSciences = DoctorApScience::where('patient_check_id', $id)->where('nurse_status', 0)->orderBy('urgent', 'desc')->get();
        $doctorApLaboratorys = DoctorApLaboratory::where('patient_check_id', $id)->where('nurse_status', 0)->orderBy('urgent', 'desc')->get();
        $doctorApEquipments = DoctorApEquipments::where('patient_check_id', $id)->where('nurse_status', 0)->get();
        $doctorApMedicines = DoctorApMedicine::where('patient_check_id', $id)->where('nurse_status', 0)->get();
        $doctorApAnothers = DoctorApAnother::where('patient_check_id', $id)->where('nurse_status', 0)->get();

        $patientDialysisMedicines = PatientDialysisMedicine::where('patient_id', $patient->id)->where('end_date', null)->where('nurse_start_date', null)->get();
        $patientDialysisMachineLongs = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('end_date', null)->where('nurse_start_id', null)->orderBy('dialysis_machine_id', 'asc')->get();

        $apItems = [];

        // 1. 檢查項目 (Science)
        foreach ($doctorApSciences as $doctorApScience) {
            $obj = (object)[];
            $obj->id = $doctorApScience->id;
            $obj->type = 1;

            $parts = [];
            $parts[] = '速度:'.($doctorApScience->urgent == 1) ? "急件" : "非急件";
            $parts[] = '項目:'.($doctorApScience->science_id == 1) ? "EKG" : "心臟超音波";
            $parts[] = '條件:'.$doctorApScience->condition;
            if($doctorApScience->note != null)
                $parts[] = '備註:'.$doctorApScience->note;

            // 過濾空值並用空白連接
            $obj->content = implode(' ', array_filter($parts, fn($value) => !is_null($value) && $value !== ''));
            $obj->status = $doctorApScience->nurse_status;
            array_push($apItems, $obj);
        }

        // 2. 檢驗項目 (Laboratory)
        foreach ($doctorApLaboratorys as $doctorApLaboratory) {
            $obj = (object)[];
            $obj->id = $doctorApLaboratory->id;
            $obj->type = 2;

            $parts = [];
            $parts[] = '速度:'.($doctorApLaboratory->urgent == 1) ? "急件" : "非急件";
            $parts[] = '項目:'.$doctorApLaboratory->laboratory->name ?? '';
            $parts[] = '條件:'.$doctorApLaboratory->condition;
            if($doctorApLaboratory->note != null)
                $parts[] = '備註:'.$doctorApLaboratory->note;

            $obj->content = implode(' ', array_filter($parts, fn($value) => !is_null($value) && $value !== ''));
            $obj->status = $doctorApLaboratory->nurse_status;
            array_push($apItems, $obj);
        }

        // 3. 設備/耗材 (Equipment)
        foreach ($doctorApEquipments as $doctorApEquipment) {
            $obj = (object)[];
            $obj->id = $doctorApEquipment->id;
            $obj->type = 3;

            $parts = [];
            $parts[] = '品名:'.$doctorApEquipment->equipment;
            $parts[] = '用量:'.$doctorApEquipment->amount;
            if($doctorApEquipment->note != null)
                $parts[] = '備註:'.$doctorApEquipment->note;

            $obj->content = implode(' ', array_filter($parts, fn($value) => !is_null($value) && $value !== ''));
            $obj->status = $doctorApEquipment->nurse_status;
            array_push($apItems, $obj);
        }

        // 4. 用藥 (Medicine)
        foreach ($doctorApMedicines as $doctorApMedicine) {
            $obj = (object)[];
            $obj->id = $doctorApMedicine->id;
            $obj->type = 4;

            $parts = [];
            $parts[] = ($doctorApMedicine->isLong == 1) ? "(長期)藥名:" . $doctorApMedicine->medicine : "藥名:".$doctorApMedicine->medicine;
            $parts[] = '途徑:'.$doctorApMedicine->route->name ?? '';
            $parts[] = '頻率:'.$doctorApMedicine->frequency->name ?? '';
            $parts[] = $doctorApMedicine->amount.'PC';
            $parts[] = '共'.$doctorApMedicine->days . '天'.$doctorApMedicine->total.'顆';
            if($doctorApMedicine->note != null)
                $parts[] = '備註:'.$doctorApMedicine->note;

            $obj->content = implode(' ', array_filter($parts, fn($value) => !is_null($value) && $value !== ''));
            $obj->status = $doctorApMedicine->nurse_status;
            array_push($apItems, $obj);
        }

        // 7. 其他 (Another)
        foreach ($doctorApAnothers as $doctorApAnother) {
            $obj = (object)[];
            $obj->id = $doctorApAnother->id;
            $obj->type = 7;
            
            // 其他項目通常只有 note
            $obj->content = '其他:'.$doctorApAnother->note ?? ''; 
            $obj->status = $doctorApAnother->nurse_status;
            array_push($apItems, $obj);
        }

        // 5. 透析用藥 (Dialysis Medicine - Long term)
        $weeks = ['一', '二', '三', '四', '五', '六', '七'];
        foreach ($patientDialysisMedicines as $patientDialysisMedicineLong) {
            $obj = (object)[];
            $obj->id = $patientDialysisMedicineLong->id;
            $obj->type = 5;

            // 處理頻率字串
            $frequencys = explode(',', $patientDialysisMedicineLong->frequency_id);
            $frequency_str = '';
            foreach ($frequencys as $count => $frequency) {
                if ($frequency == 1 && isset($weeks[$count])) {
                    $frequency_str .= $weeks[$count];
                }
            }

            $parts = [];
            $parts[] = '透析用藥:'.$patientDialysisMedicineLong->medicine;
            $parts[] = '途徑:'.$patientDialysisMedicineLong->route->name ?? '';
            $parts[] = '頻率:'.$frequency_str;
            $parts[] = '用量:'.$patientDialysisMedicineLong->amount;
            if($patientDialysisMedicineLong->note != null)
                $parts[] = '備註:'.$patientDialysisMedicineLong->note;

            $obj->content = implode(' ', array_filter($parts, fn($value) => !is_null($value) && $value !== ''));
            $obj->status = $patientDialysisMedicineLong->nurse_status;
            array_push($apItems, $obj);
        }

        // 6. 透析機器參數 (Dialysis Machine)
        foreach ($patientDialysisMachineLongs as $patientDialysisMachineLong) {
            $obj = (object)[];
            $obj->id = $patientDialysisMachineLong->id;
            $obj->type = 6;
            
            $value = '';
            switch ($patientDialysisMachineLong->dialysis_machine_id) {
                case (1):
                    $value = $patientDialysisMachineLong->dialyzer->product_name ?? '';
                    break;
                case (6):
                    $value = $patientDialysisMachineLong->Na_K_Ca->na_k_ca ?? '';
                    break;
                case (8):
                    $value = ($patientDialysisMachineLong->value == 1) ? '有' : '沒有';
                    break;
                case (13):
                    $value = ($patientDialysisMachineLong->value == 1) ? '要測' : '不測';
                    break;
                case (9):
                    if ($patientDialysisMachineLong->value == 1) {
                        $value = 'Heparin';
                    } elseif ($patientDialysisMachineLong->value == 2) {
                        $value = 'Fragmin';
                    } else {
                        $value = 'Innohep';
                    }
                    break;
                default:
                    $value = $patientDialysisMachineLong->value;
            }

            $machineName = $patientDialysisMachineLong->dialysis_machine->name ?? '';
            // 合併成 "機器名稱: 數值" 的格式
            $obj->content = '透析參數:'.$machineName . ' 值:' . $value;
            
            array_push($apItems, $obj);
        }

        // 8. 體重 (Weight)
        $patientDialysisWeight = PatientDialysisWeight::where('patient_id', $patientCheck->patient_reservation->patient_id)
            ->where('nurse_id', null)
            ->orderBy('id', 'desc')
            ->first();

        if ($patientDialysisWeight != null) {
            if ($patientDialysisWeight->nurse_id == null) {
                $obj = (object)[];
                $obj->id = $patientDialysisWeight->id;
                $obj->type = 8;
                $obj->content = "新乾體重: " . $patientDialysisWeight->dry_weight. 'kg';
                array_push($apItems, $obj);
            }
        }


        $eduItems = [];
    
    // 撈取該病患狀態小於 4 (尚未由護理師確認完成) 的紀錄
    $triggeredLogs = EducationExecutionLog::with('educationItem')
        ->where('patient_id', $patient->id)
        ->where('status', '<', 4) 
        ->orderBy('triggered_at', 'desc')
        ->get();

    foreach ($triggeredLogs as $log) {
        $eduObj = (object)[];
        $eduObj->id = $log->id; // Log 的 ID，用於後續更新狀態
        $eduObj->type = 'education';
        $eduObj->rule_id = $log->rule_id;
        
        // 取得衛教項目名稱與觸發數值
        $itemName = $log->educationItem->item ?? '未知項目';
        $eduObj->message = "{$itemName} 數值異常 ({$log->trigger_value})，需進行衛教";
        
        // 指導內容
        $eduObj->content = $log->educationItem->content ?? '';
        $eduObj->status = $log->status;
        
        array_push($eduItems, $eduObj);
    }

        // ---------------------------------------------------------
        // 以下為 System Logic (維持原樣)
        // ---------------------------------------------------------

        $machine_obj = (object)[];

        $patientHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)
            ->whereBetween('date', [
                date('Y-m-d', strtotime('monday this week')),
                date('Y-m-d', strtotime('sunday this week')),
            ])
            ->first();

        if ($patientHctInspectionRecordNew != null) {
            $lastHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->where('date', '<', $patientHctInspectionRecordNew->date)->orderBy('date', 'desc')->first();
        } else {
            $lastHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->where('date', '<', $patientCheck->date)->orderBy('date', 'desc')->first();
        }

        $hct_threshold = 0;

        if ($patientHctInspectionRecordNew != null && $lastHctInspectionRecordNew != null) {
            if ($patientHctInspectionRecordNew->hct_add == null) {
                if ($lastHctInspectionRecordNew->hct_add == null) {
                    $hct_threshold = $lastHctInspectionRecordNew->hct - $patientHctInspectionRecordNew->hct;
                } else {
                    $hct_threshold = $lastHctInspectionRecordNew->hct_add - $patientHctInspectionRecordNew->hct;
                }
            }
        }

        if ($hct_threshold >= 3 || $hct_threshold <= -2) {
            $machine_obj->hct_threshold = 1;
        } else {
            $machine_obj->hct_threshold = 0;
        }

        $patientDialysisInspectionRecords = PatientDialysisInspectionRecord::where('patient_id', $patient->id)->where('status', 0)->get();
        if (count($patientDialysisInspectionRecords) > 0) {
            $machine_obj->haveNewInspectionData = 1;
        } else {
            $machine_obj->haveNewInspectionData = 0;
        }

        $patientNotice = PatientNotice::where('patient_check_id', $id)->first();
        $obj = (object)[];

        if ($patientNotice != null) {
            $obj->note_1 = $patientNotice->note_1;
            $obj->note_2 = $patientNotice->note_2;
            $obj->note_3 = $patientNotice->note_3;
            $obj->note_4 = $patientNotice->note_4;
        } else {
            $obj->note_1 = null;
            $obj->note_2 = null;
            $obj->note_3 = null;
            $obj->note_4 = null;
        }

        return response()->json([
            'status' => 200,
            'apItems' => $apItems,
            'note' => $obj,
            'system' => $machine_obj,
            'eduItems' => $eduItems
        ]);
    }

    function updateNote($id, Request $request){
        
        $note_1 = $request->input('note_1');
        $note_2 = $request->input('note_2');
        $note_3 = $request->input('note_3');
        $note_4 = $request->input('note_4');

        $patientNotice = PatientNotice::where('patient_check_id', $id)->first();
        if($patientNotice != null){
            $patientNotice->note_1 = $note_1;
            $patientNotice->note_2 = $note_2;
            $patientNotice->note_3 = $note_3;
            $patientNotice->note_4 = $note_4;
            $patientNotice->save();
        }
        else{
            PatientNotice::create([
                'patient_check_id' => $id,
                'note_1' => $note_1,
                'note_2' => $note_2,
                'note_3' => $note_3,
                'note_4' => $note_4
            ]);
        }

        return response()->json([
            'status' => 200
        ]);
    }

    function updateAp($id, Request $request){
        $ap_status = $request->input('status');
        $ap_type = $request->input('type');

        $filePath = null;
        $imageData = $request->input('image');
        if($imageData != null){
            $fileName = uniqid().'.jpg';

            // 将 Base64 数据解码为二进制数据
            $imageData = base64_decode($imageData);
    
            // 保存图像文件到 storage/app/public 目录下
            Storage::disk('shared_storage')->put('ap/'.$fileName, $imageData);
    
            // 返回保存的图像文件路径或 URL
            $filePath = $fileName;
        }

        switch ($ap_type) {
            case 1:
                $doctorApItem = DoctorApScience::findOrFail($id);
                if($filePath != null){
                    $doctorApItem->img_string = $filePath;
                }
                
            break;

            case 2:
                $doctorApItem = DoctorApLaboratory::findOrFail($id);
            break;

            case 3:
                $doctorApItem = DoctorApEquipments::findOrFail($id);
            break;

            case 4:
                $doctorApItem = DoctorApMedicine::findOrFail($id);
            break;

            case 7:
                $doctorApItem = DoctorApAnother::findOrFail($id);
            break;
        }
        $time = date('Y-m-d H:i:s');
        $doctorApItem->nurse_status = $request->input('status');
        $doctorApItem->nurse_id = $request->user()->id;
        $doctorApItem->doctor_status_note = $request->input('note');
        $doctorApItem->nurse_response_time = $time;
        if($request->input('status') == 1){
            // $doctorApItem->doctor_status_id = $doctorApItem->doctor_id;
            // $doctorApItem->doctor_response_time = $time;
            $doctorApItem->doctor_status = 1;
        }
        
        
        // $doctorApItem->nurse_id = 7;
        


        $doctorApItem->save();
        return response()->json([
            'status' => 200
        ]);
    }
    
    function updateDialysisMedicine($id, Request $request){
        $patientDialysisMedicine = PatientDialysisMedicine::findOrFail($id);

        $patientDialysisMedicine->nurse_start_id = $request->user()->id;
        $patientDialysisMedicine->nurse_start_date = date('Y-m-d H:i:s');

        $patientDialysisMedicine->save();

        return response()->json([
            'status' => 200
        ]);
    }

    function updateDialysisMachine($id, Request $request){
        $patientDialysisMachineLong = PatientDialysisMachineLong::findOrFail($id);

        $patientDialysisMachineLong->nurse_start_id = $request->user()->id;
        $patientDialysisMachineLong->nurse_start_date = date('Y-m-d H:i:s');

        $patientDialysisMachineLong->save();

        return response()->json([
            'status' => 200
        ]);
    }

    
        function getDryWeight($id){
        $patient = \App\Models\Patient::findOrFail($id);
        $weight = \App\Models\PatientDialysisWeight::where('patient_id', $id)->orderBy('id', 'desc')->first();
        if($weight){
            return response()->json([
                'status' => 200,
                'dry_weight_a' => $weight->dry_weight_a,
                'dry_weight_b' => $weight->dry_weight_b,
                'active' => $weight->active_dry_weight,
            ]);
        }
        return response()->json(['status' => 200, 'dry_weight_a' => null, 'dry_weight_b' => null, 'active' => 'A']);
    }

        function switchDryWeight($id, Request $request){
        // Role check: only nurse/head_nurse roles allowed
        $user = $request->user();
        $userRole = $user->role ? $user->role->name : null;
        if (!in_array($userRole, ['護理師', '護理長'])) {
            return response()->json(['status' => 403, 'message' => 'Forbidden: nurse role required'], 403);
        }
        $patientDialysisWeight = PatientDialysisWeight::where('patient_id', $id)->orderBy('id', 'desc')->firstOrFail();

        // Nursing side: only allowed to toggle active (A/B), NOT change weight values
        $validated = $request->validate([
            'active' => 'required|in:A,B',
        ]);
        $patientDialysisWeight->active_dry_weight = $validated['active'];
        $patientDialysisWeight->nurse_id = Auth::user()->id;
        $patientDialysisWeight->save();

        return response()->json([
            'status' => 200,
            'dry_weight_a' => $patientDialysisWeight->dry_weight_a,
            'dry_weight_b' => $patientDialysisWeight->dry_weight_b,
            'active' => $patientDialysisWeight->active_dry_weight,
        ]);
    }

    function updateDryWeight($id, Request $request){
        // Role check: only doctor role allowed
        $user = $request->user();
        $userRole = $user->role ? $user->role->name : null;
        if (!in_array($userRole, ['醫生', '院長'])) {
            return response()->json(['status' => 403, 'message' => 'Forbidden: doctor role required'], 403);
        }
        $validated = $request->validate([
            'dry_weight_a' => 'nullable|numeric|min:20|max:150',
            'dry_weight_b' => 'nullable|numeric|min:20|max:150',
            'active' => 'nullable|in:A,B',
        ]);
        $patientDialysisWeight = PatientDialysisWeight::where('patient_id', $id)->orderBy('id', 'desc')->firstOrFail();
        $patientDialysisWeight->nurse_id = Auth::user()->id;

        // Support A/B dry weight (DL-106)
        if ($request->has('dry_weight_a')) $patientDialysisWeight->dry_weight_a = $validated['dry_weight_a'];
        if ($request->has('dry_weight_b')) $patientDialysisWeight->dry_weight_b = $validated['dry_weight_b'];
        if ($request->has('active')) $patientDialysisWeight->active_dry_weight = $validated['active'];

        $patientDialysisWeight->save();

        return response()->json([
            'status' => 200
        ]);
    }

    function getNotifyCount($id){
        $patientCheck = PatientCheck::findOrFail($id);
        $patient = $patientCheck->patient_reservation->patient;

        $doctorAps_count = DoctorApScience::where('patient_check_id', $id)->where('nurse_status', 0)->count();
        $patientDialysisMedicines_count = PatientDialysisMedicine::where('patient_id', $patient->id)->where('end_date', null)->where('nurse_start_date', null)->count();
        $patientDialysisMachineLongs_count = PatientDialysisMachineLong::where('patient_id', $patient->id)->where('end_date', null)->where('nurse_start_id', null)->orderBy('dialysis_machine_id', 'asc')->count();
        $patientDialysisWeight = PatientDialysisWeight::where('patient_id', $patientCheck->patient_reservation->patient_id)->where('nurse_id', null)->orderBy('id', 'desc')->first();
        if($patientDialysisWeight != null){
            $patientDialysisWeight_count = 1;
        }
        else{
            $patientDialysisWeight_count = 0;
        }


        $system_count = 0;

        $patientDialysisInspectionRecords = PatientDialysisInspectionRecord::where('patient_id', $patient->id)->where('status', 0)->get();
        if(count($patientDialysisInspectionRecords) > 0){
            $system_count+=1;
        }

        $patientHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)
            ->whereBetween('date', [
                date('Y-m-d', strtotime('monday this week')),
                date('Y-m-d', strtotime('sunday this week')),
            ])
            ->first();
            
        if($patientHctInspectionRecordNew != null){
            $lastHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->where('date', '<', $patientHctInspectionRecordNew->date)->orderBy('date', 'desc')->first();
        }
        else{
            $lastHctInspectionRecordNew = PatientHctInspectionRecordNew::where('patient_id', $patient->id)->where('date', '<', $patientCheck->date)->orderBy('date', 'desc')->first();
        }

        $hct_threshold = 0;

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
            $system_count += 1;
        }


        $ap_count = $doctorAps_count + $patientDialysisMedicines_count + $patientDialysisMachineLongs_count + $patientDialysisWeight_count;
        return response()->json([
            'status' => 200,
            'ap_count' => $ap_count,
            'system_count' => $system_count
        ]);

    }
}
