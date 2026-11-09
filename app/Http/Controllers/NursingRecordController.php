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
                $content = $rec->value ?? '';
                $deletedMeta = null;

                if ($isDeleted) {
                    // 若內容中帶有〈... 註銷〉後綴，擷取作為 deletedMeta 並將原內容剝離乾淨
                    if (preg_match('/(〈[^〉]+註銷〉)$/u', $content, $matches)) {
                        $deletedMeta = $matches[1];
                        $content = trim(str_replace($deletedMeta, '', $content));
                    } else {
                        $deletedMeta = '〈已註銷〉';
                    }
                }

                return [
                    'id' => $rec->id,
                    'time' => $rec->time ? (strlen($rec->time) > 10 ? substr($rec->time, 11, 5) : $rec->time) : '—',
                    'content' => $content,
                    'nurse' => $rec->nurse->name ?? ($rec->nurse_id ? '護理師' . $rec->nurse_id : '楚心瑜'),
                    'deleted' => $isDeleted,
                    'deletedMeta' => $deletedMeta
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
            'time' => 'nullable|string',
            'nurse' => 'nullable|string'
        ]);

        $check = \App\Models\PatientCheck::findOrFail($check_id);

        $nurseName = $validated['nurse'] ?? (Auth::user()->name ?? '楚心瑜');
        $nurseUser = \App\Models\User::where('name', $nurseName)->first();
        $nurseId = $nurseUser ? $nurseUser->id : (Auth::user()->id ?? 1);

        $time = !empty($validated['time'])
            ? (strlen($validated['time']) <= 5 ? (now()->format('Y-m-d') . ' ' . $validated['time'] . ':00') : $validated['time'])
            : now()->format('Y-m-d H:i:s');

        $record = PatientMidNurseRecord::create([
            'patient_check_id' => $check->id,
            'time' => $time,
            'value' => $validated['content'],
            'nurse_id' => $nurseId
        ]);

        return response()->json([
            'success' => true,
            'message' => '病歷成功具名持久化儲存',
            'record' => [
                'id' => $record->id,
                'time' => strlen($record->time) > 10 ? substr($record->time, 11, 5) : $record->time,
                'content' => $record->value,
                'nurse' => $nurseName,
                'isDeleted' => false,
                'deleted' => false
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
    public function destroy(Request $request, $id)
    {
        $record = PatientMidNurseRecord::findOrFail($id);

        $user = $request->user() ?: Auth::user();
        $nurseName = $request->input('nurse') ?: ($user?->name ?: '護理師');
        $cancelTime = now()->format('H:i');
        $deletedMeta = "〈{$nurseName} {$cancelTime} 註銷〉";

        // 若尚未附帶註銷標記，將其記錄於 value 字尾以供持久化
        $currentValue = $record->value ?: '';
        if (!str_contains($currentValue, ' 註銷〉')) {
            $currentValue .= " {$deletedMeta}";
        }

        $record->update([
            'value' => $currentValue,
            'deleted' => 1,
            'is_deleted' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => '病歷已依法執行劃線註銷。',
            'deletedMeta' => $deletedMeta,
            'cancel_nurse' => $nurseName,
            'cancel_time' => $cancelTime
        ], 200);
    }
}
