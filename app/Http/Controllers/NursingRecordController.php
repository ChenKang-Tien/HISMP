<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NursingRecordController extends Controller
{
    /**
     * GET /api/v1/patients/{mr}/nursing-records
     * 獲取特定病患當班所有病歷紀錄時間軸
     */
    public function index($mr)
    {
        // 🚀 依據病歷號動態返回對應的歷史紀錄 (以薛玉鳳 mr 為基準模擬)
        $records = [
            [
                'id' => 1,
                'time' => '09:20',
                'content' => '低血壓 BP 92/54，快衝 N/S 100ml，UFR 調降，15分後追蹤。',
                'nurse' => '楚心瑜',
                'isDeleted' => false
            ],
            [
                'id' => 2,
                'time' => '08:05',
                'content' => '透析上針順利，廔管穿刺無滲血，意識清楚，配合度佳。',
                'nurse' => '楚心瑜',
                'isDeleted' => false
            ]
        ];

        return response()->json([
            'success' => true,
            'records' => $records,
            'last_autosave' => '11:58'
        ], 200);
    }

    /**
     * POST /api/v1/patients/{mr}/nursing-records
     * 建立一筆新病歷 (持久化成功回傳)
     */
    public function store(Request $request, $mr)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $timeStr = now()->format('H:i');

        // 🚀 模擬寫入資料庫後生成的帶有實體自增 ID 的真實紀錄
        return response()->json([
            'success' => true,
            'message' => '病歷成功具名持久化儲存 (Audit Trail 稽核鏈已啟動)',
            'record' => [
                'id' => rand(10000, 99999),
                'time' => $timeStr,
                'content' => $request->content,
                'nurse' => '楚心瑜',
                'isDeleted' => false
            ]
        ], 201); // 201 Created
    }

    /**
     * PUT /api/v1/nursing-records/{id}
     * 修改病歷 (加線留痕覆寫)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        return response()->json([
            'success' => true,
            'record' => [
                'id' => (int)$id,
                'time' => now()->format('H:i'),
                'content' => $request->content . '（修正留痕：' . now()->format('H:i') . ' 由楚心瑜覆寫更新）',
                'nurse' => '楚心瑜',
                'isDeleted' => false
            ]
        ], 200);
    }

    /**
     * DELETE /api/v1/nursing-records/{id}
     * 註銷病歷 (法律加線留痕標記)
     */
    public function destroy($id)
    {
        $timeStr = now()->format('H:i');

        // 🚀 醫療法規：回傳 deleted_meta 註記給前端
        return response()->json([
            'success' => true,
            'message' => '病歷已依法執行劃線註銷軌跡，物理數據予以留存。',
            'deleted_meta' => "〈楚心瑜 {$timeStr} 註銷刪除〉"
        ], 200);
    }
}
