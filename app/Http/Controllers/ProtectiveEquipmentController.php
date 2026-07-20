<?php

namespace App\Http\Controllers;

use App\Models\PatientCheck;
use App\Models\PatientProtectiveEquipment;
use App\Models\PatientRestraintCheck;
use Illuminate\Http\Request;

class ProtectiveEquipmentController extends Controller
{
    public function store(Request $request, $id)
    {
        $patientCheck = PatientCheck::findOrFail($id);

        $data = $request->validate([
            'equipment' => 'required|array',
            'equipment.*' => 'string|max:50',
            'other_name' => 'nullable|string|max:100',
        ]);

        $record = PatientProtectiveEquipment::create([
            'patient_check_id' => $patientCheck->id,
            'equipment' => $data['equipment'],
            'other_name' => $data['other_name'] ?? null,
            'nurse_id' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 200,
            'id' => $record->id,
        ]);
    }

    public function restraintCheck(Request $request, $id, $equipmentId)
    {
        $patientCheck = PatientCheck::findOrFail($id);
        $equipment = PatientProtectiveEquipment::where('id', $equipmentId)
            ->where('patient_check_id', $patientCheck->id)
            ->firstOrFail();

        $data = $request->validate([
            'result' => 'required|string|in:ok,note',
            'note' => 'nullable|string|max:1000',
        ]);

        $check = PatientRestraintCheck::create([
            'protective_equipment_id' => $equipment->id,
            'patient_check_id' => $patientCheck->id,
            'result' => $data['result'],
            'note' => $data['note'] ?? null,
            'nurse_id' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 200,
            'id' => $check->id,
        ]);
    }
}
