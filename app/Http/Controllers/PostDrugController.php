<?php

namespace App\Http\Controllers;

use App\Models\PatientAfterMedicineDatas;
use Illuminate\Http\Request;

class PostDrugController extends Controller
{
    // POST /dialysis/{id}/post-drugs/execute
    public function execute($id, Request $request)
    {
        // Role check: only nurse/head_nurse roles allowed
        $user = $request->user();
        $userRole = $user->role ? $user->role->name : null;
        if (!in_array($userRole, ['護理師', '護理長'])) {
            return response()->json(['status' => 403, 'message' => 'Forbidden: nurse role required'], 403);
        }
        $validated = $request->validate([
            'drug_name' => 'required|string|max:255',
            'dose' => 'required|numeric|min:0',
            'route' => 'required|string|max:50',
            'adverse_reaction' => 'nullable|string|max:255',
        ]);

        $record = PatientAfterMedicineDatas::create([
            'patient_check_id' => $id,
            'medicine_name' => $validated['drug_name'],
            'dose' => $validated['dose'],
            'route' => $validated['route'],
            'adverse_reaction' => $validated['adverse_reaction'] ?? '無',
            'nurse_id' => $request->user()->id,
            'type' => 'execute',
        ]);

        return response()->json(['status' => 200, 'record' => $record]);
    }

    // POST /dialysis/{id}/post-drugs/refuse
    public function refuse($id, Request $request)
    {
        // Role check: only nurse/head_nurse roles allowed
        $user = $request->user();
        $userRole = $user->role ? $user->role->name : null;
        if (!in_array($userRole, ['護理師', '護理長'])) {
            return response()->json(['status' => 403, 'message' => 'Forbidden: nurse role required'], 403);
        }
        $validated = $request->validate([
            'drug_name' => 'required|string|max:255',
            'reason' => 'required|string|max:255',
            'detail' => 'nullable|string|max:500',
        ]);

        $record = PatientAfterMedicineDatas::create([
            'patient_check_id' => $id,
            'medicine_name' => $validated['drug_name'],
            'refuse_reason' => $validated['reason'],
            'refuse_detail' => $validated['detail'] ?? null,
            'nurse_id' => $request->user()->id,
            'type' => 'refuse',
        ]);

        return response()->json(['status' => 200, 'record' => $record]);
    }
}
