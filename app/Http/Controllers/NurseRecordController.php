<?php

namespace App\Http\Controllers;

use App\Models\NurseRecordPhrase;
use App\Models\PatientBeforePhysiologicalDatas;
use App\Models\PatientMidInfraredTherapie;
use App\Models\PatientMidNurseRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class NurseRecordController extends Controller
{
    function getNurseRecords($id){
        $patientMidNurseRecords = PatientMidNurseRecord::where('patient_check_id', $id)->orderBy('time', 'asc')->get();
        foreach($patientMidNurseRecords as $patientMidNurseRecord){
            $patientMidNurseRecord->nurse_name = $patientMidNurseRecord->nurse->name;
        }

        $patientMidInfraredTherapie = PatientMidInfraredTherapie::where('patient_check_id', $id)->first();
        $fir = (object)[];
        if($patientMidInfraredTherapie != null){
            $fir->flag = $patientMidInfraredTherapie->flag;
            $fir->content = $patientMidInfraredTherapie->content;
        }
        else{
            $patientBeforePhysiologicalData = PatientBeforePhysiologicalDatas::where('patient_check_id', $id)->first();
            if($patientBeforePhysiologicalData != null){
                $vascular_access_type = explode(',', $patientBeforePhysiologicalData->vascular_access_type);
                if(isset($vascular_access_type[2]) && $vascular_access_type[2] == ""){
                    $fir->flag = 1;
                    $fir->content = 40;
                }
                else{
                    $fir->flag = 0;
                    $fir->content = "Premcath";
                }
            }
            else{
                $fir->flag = 1;
                $fir->content = 40;
            }
        }


        return response()->json([
            'status' => 200,
            'nurseRecords'=> $patientMidNurseRecords,
            'fir' => $fir
        ]);
    }

    function getNurseRecordPhrases(){
        $nurseRecordPhrases = NurseRecordPhrase::select('name')->where('deleted', 0)->orderBy('name', 'asc')->pluck('name');

        return response()->json([
            'status' => 200,
            'phrases'=> $nurseRecordPhrases
        ]);
    }
    //
    function createNurseRecord($id, Request $request){
        $validated = $request->validate([
            'time' => 'required|date_format:Y-m-d H:i:s',
            'patient_ask' => 'nullable|integer|in:0,1',
            'content' => 'nullable|string|max:1000',
        ]);
        $time = $validated['time'];
        $patient_ask = $validated['patient_ask'] ?? '';
        $content = $validated['content'] ?? '';
        $nurse_id = $request->user()->id;

        $patientMidNurseRecord = PatientMidNurseRecord::create([
            'patient_check_id' => $id,
            'time' => $time,
            'patient_statement' => $patient_ask,
            'nurse_record_auxiliary_str' => $content,
            'nurse_id' => $nurse_id,
        ]);

        $patientMidNurseRecord->nurse_name = $request->user()->name;

        return response()->json([
            'status' => 200,
            'nurseRecord' => $patientMidNurseRecord
        ]);
    }

    function updateNurseRecord($id, Request $request){
        $validated = $request->validate([
            'time' => 'required|date_format:Y-m-d H:i:s',
            'patient_ask' => 'nullable|integer|in:0,1',
            'content' => 'nullable|string|max:1000',
        ]);
        $time = $validated['time'];
        $patient_ask = $validated['patient_ask'] ?? '';
        $content = $validated['content'] ?? '';
        $nurse_id = $request->user()->id;

        $patientMidNurseRecord = PatientMidNurseRecord::findOrFail($id);
        $patientMidNurseRecord->time = $time;
        $patientMidNurseRecord->patient_statement = $patient_ask;
        $patientMidNurseRecord->nurse_record_auxiliary_str = $content;
        $patientMidNurseRecord->nurse_id = $nurse_id;
        $patientMidNurseRecord->save();

        return response()->json([
            'status' => 200,
        ]);
    }

    function deleteNurseRecord($id, Request $request){
        $patientMidNurseRecord = PatientMidNurseRecord::findOrFail($id);
        $patientMidNurseRecord->delete();

        return response()->json([
            'status' => 200,
        ]);
    }

}
