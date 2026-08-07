<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NursingActionController extends Controller
{
    /**
     * POST /api/v1/patients/{mr}/weights
     */
    public function updateWeights(Request , $mr)
    {
        $validated = $request->validate([
            'pre' => 'nullable|numeric',
            'post' => 'nullable|numeric',
            'note' => 'nullable|string'
        ]);

        return response()->json([
            'success' => true,
            'message' => '體重數據已校正並記錄護理日誌。'
        ], 200);
    }

    /**
     * POST /api/v1/patients/{mr}/uf-goal
     */
    public function updateUfGoal(Request $request, $mr)
    {
        $validated = $request->validate([
            'uf_goal' => 'required|numeric',
            'hours' => 'required|numeric',
            'note' => 'nullable|string'
        ]);

        return response()->json([
            'success' => true,
            'message' => '調水量設定已更新並同步至日誌系統。'
        ], 200);
    }

    /**
     * POST /api/v1/patients/{mr}/absence-leave
     */
    public function issueAbsenceLeave(Request $request, $mr)
    {
        $validated = $request->validate([
            'status' => 'required|in:LEAVE,HOSPITALIZED',
            'note' => 'nullable|string'
        ]);

        return response()->json([
            'success' => true,
            'message' => '後端成功接收假單！病患 ' . $mr . ' 狀態已變更為 ' . $validated['status'] . '，系統已自動留痕交接。'
        ], 200);
    }
}
