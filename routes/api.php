<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\NursingActionController;
use App\Http\Controllers\NursingRecordController;
use App\Http\Controllers\OrderExecutionController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\WeightAdjustItemController;

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('weight-adjust-items', [WeightAdjustItemController::class, 'index']); // 新增路由
        Route::get('dialysis/patients', [PatientController::class, 'index']);
        Route::get('patients/{mr}/dialysis-cases/current', [PatientController::class, 'showCurrentCase']);
        Route::post('patients/{mr}/absence-leave', [PatientController::class, 'issueAbsenceLeave']);
        Route::post('patients/{mr}/weights', [NursingActionController::class, 'updateWeights']);
        Route::post('patients/{mr}/weight-adjustments', [NursingActionController::class, 'updateWeightAdjustments']);
        Route::post('patients/{mr}/uf-goal', [NursingActionController::class, 'updateUfGoal']);
        Route::post('patients/{mr}/incidents', [NursingActionController::class, 'reportIncident']);

        Route::get('patients/{mr}/nursing-records', [NursingRecordController::class, 'index']);
        Route::post('patients/{mr}/nursing-records', [NursingRecordController::class, 'store']);
        Route::put('nursing-records/{id}', [NursingRecordController::class, 'update']);
        Route::delete('nursing-records/{id}', [NursingRecordController::class, 'destroy']);

        Route::patch('orders/{id}/execution', [OrderExecutionController::class, 'updateExecution']);

        Route::get('nursing/shift-options', [NursingActionController::class, 'fetchShiftOptions']);
        Route::post('nursing/shifts', [NursingActionController::class, 'saveShift']);
        Route::get('nursing/supply-tmr', [NursingActionController::class, 'fetchSupplyList']);
        Route::post('nursing/supply-tmr/lock', [NursingActionController::class, 'lockSupplyList']);

        Route::post('logout', [AuthController::class, 'logout']);
    });
});
