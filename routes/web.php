<?php

use Illuminate\Support\Facades\Route;
use App\Models\PatientReservation;
use App\Models\PatientCheck;

Route::get('/test/random-schedule', function () {
    $shifts = [1, 2, 3]; // 1:早, 2:午, 3:晚
    $today = now()->format('Y-m-d');

    // 確保每班各產生一筆
    foreach ($shifts as $shift) {
        $newData = PatientReservation::create([
            'hospital_id'        => 1, // 補上這個欄位
            'patient_id'         => rand(1, 10),
            'date'               => $today,
            'morning_noon_night' => $shift,
            'machine_bed_id'     => rand(1, 20),
            'status'             => 0,
        ]);

        PatientCheck::create([
            'patient_reservation_id' => $newData->id,
            'date'             => $today,
            'status'                 => 0,
            'have_dialysis_data'     => 0,
        ]);
    }

    return response()->json(['message' => '早、午、晚班測試資料各一已建立']);
});

// 🏥 前端萬用門戶：除了開頭是 /api 的請求，其他網址全部交給前端 Vue 3 處理
Route::get('/{any}', function () {
    return view('app'); // 指向你的 Vue 載入地基
})->where('any', '^(?!api).*$');
