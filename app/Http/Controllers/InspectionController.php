<?php

namespace App\Http\Controllers;

use App\Models\PatientCheck;
use App\Models\PatientDialysisInspectionRecord;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    //
    function index($id){
        $patientDialysisInspectionRecords = $this->getPatientInspections($id);
        $months = $patientDialysisInspectionRecords->pluck('date');

        return response()->json([
            'status' => 200,
            'inspections' => $patientDialysisInspectionRecords,
            'months' => $months
        ]);
    }

    public function getPatientInspections($id)
    {
        # code...
        $patientDialysisInspectionRecords = PatientDialysisInspectionRecord::where('patient_id', $id)->where('type', 2)->orderBy('date', 'desc')->get();
        

        // $patientDialysisInspectionRecords = PatientDialysisInspectionRecord::where('patient_id', $patient->id)->orderBy('date', 'DESC')->paginate(6, ['*'], 'normal');
        return $patientDialysisInspectionRecords;

    }

    public function markRead($id)
    {
        PatientDialysisInspectionRecord::where('id', $id)->update(['status' => 1]);

        return response()->json(['status' => '200']);
    }
}
