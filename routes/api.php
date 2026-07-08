<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\NursingRecordController;
use App\Http\Controllers\OrderExecutionController;
use App\Http\Controllers\DialysisClinicalController;
use App\Http\Controllers\AuthController; // 🌟 補上身分驗證大腦

/*
|--------------------------------------------------------------------------
| HISMP V1.2 - 泰安診所智慧血液透析系統 API 路由總線大門 (含 Auth & Login 防線)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ════════════════════════════════════════════════════════════
    // 🔓 區塊零：免登入安全外環（身分驗證連通道）
    // ════════════════════════════════════════════════════════════
    /**
     * [POST] /api/v1/login
     * 醫護人員帳密登入驗證，成功後核發 Sanctum 法律稽核 Token
     * 觸發入口：登入頁面點擊「登入系統」
     */
    Route::post('login', [AuthController::class, 'login']);


    // ════════════════════════════════════════════════════════════
    // 🔒 核心認證防護罩：底下所有醫療行為、病歷 CRUD 必須通過 Token 驗證
    // ════════════════════════════════════════════════════════════
    Route::middleware('auth:sanctum')->group(function () {

        // 🟢 區塊一：病患狀態看板、假單流轉大盤總表 (Patient 核心資源)
        Route::get('dialysis/patients', [PatientController::class, 'index']);
        Route::get('patients/{mr}/dialysis-cases/current', [PatientController::class, 'showCurrentCase']);
        Route::post('patients/{mr}/absence-leave', [PatientController::class, 'issueAbsenceLeave']);

        // 🟢 區塊二：護理病歷時間軸子資源 (NursingRecord 增刪查改)
        Route::get('patients/{mr}/nursing-records', [NursingRecordController::class, 'index']);
        Route::post('patients/{mr}/nursing-records', [NursingRecordController::class, 'store']);
        Route::put('nursing-records/{id}', [NursingRecordController::class, 'update']);
        Route::delete('nursing-records/{id}', [NursingRecordController::class, 'destroy']);

        // 🟢 區塊三：右下角臨時醫囑處置勾選核對 (Order 部分異動)
        Route::patch('orders/{id}/execution', [OrderExecutionController::class, 'updateExecution']);

        // 🟢 區塊四：三大分頁中央看盤・臨床核心數據交互 (Clinical 資源)
        Route::post('patients/{mr}/vitals', [DialysisClinicalController::class, 'storeVitals']);
        Route::post('patients/{mr}/assessments', [DialysisClinicalController::class, 'storeAssessments']);
        Route::post('monitoring-grid', [DialysisClinicalController::class, 'storeGridRow']);

        /**
         * [POST] /api/v1/logout
         * 具名登出，撤銷當前帳號的 Token 防線
         */
        Route::post('logout', [AuthController::class, 'logout']);
    });

});
