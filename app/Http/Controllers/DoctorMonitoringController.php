<?php

namespace App\Http\Controllers;

use App\Models\DoctorMonitoringData;
use App\Models\PatientCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DoctorMonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            $userRole = $user && $user->role ? $user->role->name : null;
            if (!in_array($userRole, ['醫生', '院長'])) {
                return response()->json(['status' => 403, 'message' => 'Forbidden: doctor role required'], 403);
            }
            return $next($request);
        });
    }
    /**
     * GET /doctor/monitoring/{checkId}
     * 取得某次透析的所有監控數據（依時間排序）
     */
    public function index($checkId)
    {
        $patientCheck = PatientCheck::find($checkId);

        if (!$patientCheck) {
            return response()->json([
                'status'  => 404,
                'message' => '透析紀錄不存在',
            ], 404);
        }

        $records = DoctorMonitoringData::where('patient_check_id', $checkId)
            ->orderBy('time_label')
            ->get();

        return response()->json([
            'status'  => 200,
            'data'    => $records,
        ]);
    }

    /**
     * PUT /doctor/monitoring/{checkId}
     * 更新（或新增）某時間點的監控數值
     * 透過 time_label 辨識時間點
     */
    public function update(Request $request, $checkId)
    {
        $patientCheck = PatientCheck::find($checkId);

        if (!$patientCheck) {
            return response()->json([
                'status'  => 404,
                'message' => '透析紀錄不存在',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'time_label'       => 'required|string|max:10',
            'bp_systolic'      => 'nullable|integer',
            'bp_diastolic'     => 'nullable|integer',
            'pulse'            => 'nullable|integer',
            'qb'               => 'nullable|integer',
            'vp'               => 'nullable|integer',
            'tmp_dp'           => 'nullable|integer',
            'uf_volume'        => 'nullable|numeric',
            'ufr'              => 'nullable|numeric',
            'heparin_setting'  => 'nullable|string|max:50',
            'heparin_remaining'=> 'nullable|string|max:50',
            'qd'               => 'nullable|integer',
            'conductivity'     => 'nullable|numeric',
            'temperature'      => 'nullable|numeric',
            'disposal_note'    => 'nullable|string',
            'ak_status'        => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => '驗證失敗',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Upsert：同 patient_check_id + time_label 則更新，否則新增
        $data = array_merge(
            ['patient_check_id' => (int) $checkId],
            $request->only([
                'time_label',
                'bp_systolic', 'bp_diastolic', 'pulse',
                'qb', 'vp', 'tmp_dp',
                'uf_volume', 'ufr',
                'heparin_setting', 'heparin_remaining',
                'qd', 'conductivity', 'temperature',
                'disposal_note', 'ak_status',
            ])
        );

        $record = DoctorMonitoringData::updateOrCreate(
            [
                'patient_check_id' => (int) $checkId,
                'time_label'       => $data['time_label'],
            ],
            $data
        );

        return response()->json([
            'status'  => 200,
            'message' => '更新成功',
            'data'    => $record->fresh(),
        ]);
    }
}
