<?php

namespace App\Http\Controllers;

use App\Models\PatientCareSign;
use Illuminate\Http\Request;

class CareSignController extends Controller
{
    // GET /dialysis/{id}/care-sign — 讀取該次透析所有時段的 Care Sign
    public function index($id)
    {
        // Role check: only nurse/head_nurse
        $user = request()->user();
        $userRole = $user->role ? $user->role->name : null;
        if (!in_array($userRole, ['護理師', '護理長'])) {
            return response()->json(['status' => 403, 'message' => 'Forbidden: nurse role required'], 403);
        }
        $records = PatientCareSign::where('patient_check_id', $id)
            ->orderBy('time_slot')->get();

        return response()->json(['status' => 200, 'care_signs' => $records]);
    }

    // POST /dialysis/{id}/care-sign — 寫入或更新某個時段的 Care Sign
    public function store($id, Request $request)
    {
        $validated = $request->validate([
            'time_slot' => ['required', 'string', function ($attr, $value, $fail) {
                $allowed = ['pre', 'h1', 'h2', 'h3', 'h4', 'post_lying', 'post_sitting'];
                if (!in_array($value, $allowed) && !preg_match('/^extra_\d+$/', $value)) {
                    $fail('time_slot 必須為 pre/h1~h4/post_lying/post_sitting/extra_*');
                }
            }],
            'fir_has' => 'nullable|boolean',
            'fir_minutes' => 'nullable|integer|min:0|max:999',
            'fir_reason' => 'nullable|string|max:255',
            'ak' => 'nullable|string|max:50',
            'bleed' => 'nullable|string|max:50',
            'tube' => 'nullable|string|max:50',
            'ns_ml' => 'nullable|integer|min:0|max:9999',
            'manual_bp_systolic' => 'nullable|integer|min:0|max:300',
            'manual_bp_diastolic' => 'nullable|integer|min:0|max:200',
            'manual_pr' => 'nullable|integer|min:0|max:300',
        ]);

        $record = PatientCareSign::updateOrCreate(
            ['patient_check_id' => $id, 'time_slot' => $validated['time_slot']],
            [
                'fir_has' => $validated['fir_has'] ?? null,
                'fir_minutes' => $validated['fir_minutes'] ?? null,
                'fir_reason' => $validated['fir_reason'] ?? null,
                'ak' => $validated['ak'] ?? null,
                'bleed' => $validated['bleed'] ?? null,
                'tube' => $validated['tube'] ?? null,
                'ns_ml' => $validated['ns_ml'] ?? null,
                'manual_bp_systolic' => $validated['manual_bp_systolic'] ?? null,
                'manual_bp_diastolic' => $validated['manual_bp_diastolic'] ?? null,
                'manual_pr' => $validated['manual_pr'] ?? null,
                'nurse_id' => $request->user()->id,
            ]
        );

        return response()->json(['status' => 200, 'care_sign' => $record]);
    }

    // POST /dialysis/{id}/finish/supply-check — N-021 醫材藥品核對
    public function supplyCheck($id, Request $request)
    {
        $validated = $request->validate([
            'confirmed' => 'required|boolean',
        ]);

        $check = \App\Models\PatientSupplyCheck::updateOrCreate(
            ['patient_check_id' => $id],
            [
                'confirmed' => $validated['confirmed'],
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]
        );

        return response()->json(['status' => 200, 'supply_check' => $check]);
    }
}
