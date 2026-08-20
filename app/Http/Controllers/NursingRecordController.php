<?php

namespace App\Http\Controllers;

use App\Models\PatientMidNurseRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NursingRecordController extends Controller
{
    /**
     * GET /api/v1/dialysis-checks/{check_id}/nursing-records
     */
    public function index($check_id)
    {
        $check = \App\Models\PatientCheck::findOrFail($check_id);

        $records = PatientMidNurseRecord::where('patient_check_id', $check->id)
            ->with('nurse')
            ->orderBy('time', 'desc')
            ->get()
            ->map(function ($rec) {
                // 明確判斷刪除狀態 (支援 deleted 與 is_deleted)
                $isDeleted = ($rec->deleted == 1 || $rec->is_deleted == 1);

                return [
                    'id' => $rec->id,
                    'time' => $rec->time,
                    'content' => $rec->value ?? $rec->patient_statement,
                    'nurse' => $rec->nurse->name ?? '未知護理師',
                    'deleted' => $isDeleted,
                    'deletedMeta' => $isDeleted ? '已註銷' : null
                ];
            });

        return response()->json([
            'success' => true,
            'records' => $records,
            'last_autosave' => now()->format('H:i')
        ], 200);
    }

    /**
     * POST /api/v1/dialysis-checks/{check_id}/nursing-records
     */
    public function store(Request $request, $check_id)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'time' => 'nullable|string'
        ]);

        $check = \App\Models\PatientCheck::findOrFail($check_id);

        $record = PatientMidNurseRecord::create([
            'patient_check_id' => $check->id,
            'time' => now()->format('Y-m-d H:i:s'), // 確保包含完整日期時間
            'value' => $validated['content'],
            'nurse_id' => Auth::user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => '病歷成功具名持久化儲存',
            'record' => [
                'id' => $record->id,
                'time' => $record->time,
                'content' => $record->value,
                'nurse' => Auth::user()->name,
                'isDeleted' => false
            ]
        ], 201);
    }

    /**
     * PUT /api/v1/nursing-records/{id}
     * 修改病歷 (加線留痕覆寫)
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        $record = PatientMidNurseRecord::findOrFail($id);

        $record->update([
            'value' => $validated['content'] . " (修正: " . now()->format('H:i') . " 由 " . Auth::user()->name . " 更新)",
        ]);

        return response()->json([
            'success' => true,
            'record' => [
                'id' => $record->id,
                'time' => $record->time,
                'content' => $record->value,
                'nurse' => $record->nurse->name ?? Auth::user()->name,
                'isDeleted' => false
            ]
        ], 200);
    }

    /**
     * DELETE /api/v1/nursing-records/{id}
     */
    public function destroy($id)
    {
        $record = PatientMidNurseRecord::findOrFail($id);

        // 假刪除標記
        $record->update(['deleted' => 1]);

        return response()->json([
            'success' => true,
            'message' => '病歷已依法執行劃線註銷。'
        ], 200);
    }
}
