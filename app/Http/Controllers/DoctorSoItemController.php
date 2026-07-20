<?php

namespace App\Http\Controllers;

use App\Models\DoctorSoItem;
use App\Models\Patient;
use App\Models\PatientCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DoctorSoItemController extends Controller
{
    /**
     * GET /doctor/patients/{patientId}/soap
     * 取得某病患的所有 SOAP 記錄
     */
    public function index($patientId)
    {
        $patient = Patient::findOrFail($patientId);

        // 透過 patient_check 關聯取得所有 SOAP 記錄
        $patientCheckIds = PatientCheck::whereHas('patient_reservation', function ($q) use ($patientId) {
            $q->where('patient_id', $patientId);
        })->pluck('id');

        $items = DoctorSoItem::whereIn('patient_check_id', $patientCheckIds)
            ->with('doctor')
            ->orderBy('time', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id'                => $item->id,
                    'patient_check_id'  => $item->patient_check_id,
                    'time'              => $item->time,
                    'subjective'        => $item->patient_statement,   // S
                    'objective'         => $item->data,                // O
                    'assessment'        => $item->note,                // A
                    'plan'              => $item->file_string,         // P
                    'doctor_name'       => $item->doctor->name ?? '—',
                    'doctor_id'         => $item->doctor_id,
                    'created_at'        => $item->created_at,
                    'updated_at'        => $item->updated_at,
                ];
            });

        return response()->json([
            'status'  => 200,
            'data'    => $items,
        ]);
    }

    /**
     * POST /doctor/patients/{patientId}/soap
     * 新增 SOAP 記錄（需指定 patient_check_id 或自動取最新 check）
     */
    public function store(Request $request, $patientId)
    {
        $patient = Patient::findOrFail($patientId);

        $validator = Validator::make($request->all(), [
            'patient_check_id' => 'nullable|integer|exists:patient_checks,id',
            'subjective'       => 'nullable|string|max:500',
            'objective'        => 'nullable|string|max:1000',
            'assessment'       => 'nullable|string|max:1000',
            'plan'             => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => '驗證失敗',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 若未指定 patient_check_id，自動取該病患最新一筆 check
        $patientCheckId = $request->input('patient_check_id');
        if (!$patientCheckId) {
            $latestCheck = PatientCheck::whereHas('patient_reservation', function ($q) use ($patientId) {
                $q->where('patient_id', $patientId);
            })->orderBy('date', 'desc')->orderBy('id', 'desc')->first();

            if (!$latestCheck) {
                return response()->json([
                    'status'  => 400,
                    'message' => '該病患無任何透析記錄',
                ], 400);
            }
            $patientCheckId = $latestCheck->id;
        }

        $item = DoctorSoItem::create([
            'patient_check_id'  => $patientCheckId,
            'time'              => now(),
            'patient_statement' => $request->input('subjective', ''),
            'data'              => $request->input('objective', ''),
            'note'              => $request->input('assessment', ''),
            'file_string'       => $request->input('plan', ''),
            'doctor_id'         => $request->user()->id,
        ]);

        $item->load('doctor');

        return response()->json([
            'status'  => 200,
            'message' => 'SOAP 記錄已建立',
            'data'    => [
                'id'                => $item->id,
                'patient_check_id'  => $item->patient_check_id,
                'time'              => $item->time,
                'subjective'        => $item->patient_statement,
                'objective'         => $item->data,
                'assessment'        => $item->note,
                'plan'              => $item->file_string,
                'doctor_name'       => $item->doctor->name ?? Auth::user()->name ?? '—',
                'doctor_id'         => $item->doctor_id,
                'created_at'        => $item->created_at,
                'updated_at'        => $item->updated_at,
            ],
        ]);
    }

    /**
     * PUT /doctor/soap/{id}
     * 更新 SOAP 記錄
     */
    public function update(Request $request, $id)
    {
        $item = DoctorSoItem::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'subjective' => 'nullable|string|max:500',
            'objective'  => 'nullable|string|max:1000',
            'assessment' => 'nullable|string|max:1000',
            'plan'       => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => '驗證失敗',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if ($request->has('subjective')) {
            $item->patient_statement = $request->input('subjective');
        }
        if ($request->has('objective')) {
            $item->data = $request->input('objective');
        }
        if ($request->has('assessment')) {
            $item->note = $request->input('assessment');
        }
        if ($request->has('plan')) {
            $item->file_string = $request->input('plan');
        }
        $item->save();
        $item->load('doctor');

        return response()->json([
            'status'  => 200,
            'message' => 'SOAP 記錄已更新',
            'data'    => [
                'id'                => $item->id,
                'patient_check_id'  => $item->patient_check_id,
                'time'              => $item->time,
                'subjective'        => $item->patient_statement,
                'objective'         => $item->data,
                'assessment'        => $item->note,
                'plan'              => $item->file_string,
                'doctor_name'       => $item->doctor->name ?? '—',
                'doctor_id'         => $item->doctor_id,
                'created_at'        => $item->created_at,
                'updated_at'        => $item->updated_at,
            ],
        ]);
    }

    /**
     * DELETE /doctor/soap/{id}
     * 刪除 SOAP 記錄
     */
    public function destroy($id)
    {
        $item = DoctorSoItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'SOAP 記錄已刪除',
        ]);
    }
}
