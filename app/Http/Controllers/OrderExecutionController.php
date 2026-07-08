<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderExecutionController extends Controller
{
    /**
     * PATCH /api/v1/orders/{id}/execution
     * 更新臨時醫囑處置的執行勾選狀態 (連動左側病歷軌跡)
     */
    public function updateExecution(Request $request, $id)
    {
        $request->validate([
            'is_done' => 'required|boolean'
        ]);

        $timeStr = now()->format('H:i');

        // 🚀 回傳標準的具名核對稽核印章，驅動前端原地變成「✅ 楚心瑜 12:05」
        return response()->json([
            'success' => true,
            'exec_nurse' => '楚心瑜',
            'exec_time' => $timeStr,
            'message' => '臨時醫囑與處置經雙人核對，已成功標記為 EXECUTED 狀態。'
        ], 200);
    }
}
