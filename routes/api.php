<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NursingActionController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\NursingRecordController;
use App\Http\Controllers\OrderExecutionController;
use App\Http\Controllers\WeightAdjustItemController;
use App\Http\Controllers\MedicineController;

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('weight-adjust-items', [WeightAdjustItemController::class, 'index']);
        Route::get('dialysis/patients', [PatientController::class, 'index']);
        Route::get('patients/{mr}/dialysis-cases/current', [PatientController::class, 'showCurrentCase']);
        Route::get('dialysis-checks/{check_id}', [PatientController::class, 'showByCheckId']);
        Route::get('dialysis-checks/{check_id}/drawer-details', [PatientController::class, 'fetchDrawerDetails']);
        Route::get('dialysis-checks/{check_id}/monitoring', [NursingActionController::class, 'fetchMonitoringData']);
        Route::post('dialysis-checks/{check_id}/manual-records', [NursingActionController::class, 'addManualRecord']);
        Route::put('dialysis-checks/{check_id}/monitoring-data', [NursingActionController::class, 'updateMonitoringData']);
        Route::post('dialysis-checks/{check_id}/off-sign', [NursingActionController::class, 'executeOffSign']);
        Route::post('dialysis-checks/{check_id}/off-sign-item', [NursingActionController::class, 'saveOffSignItem']);
        Route::post('dialysis-checks/{check_id}/on-sign', [NursingActionController::class, 'executeOnSign']);
        Route::post('dialysis-checks/{check_id}/emergency', [NursingActionController::class, 'toggleEmergency']);
        Route::post('dialysis-checks/{check_id}/weights', [NursingActionController::class, 'updateWeights']);
        Route::post('dialysis-checks/{check_id}/weight-adjustments', [NursingActionController::class, 'updateWeightAdjustments']);
        Route::post('dialysis-checks/{check_id}/vitals', [NursingActionController::class, 'updateVitals']);
        Route::post('dialysis-checks/{check_id}/assessments', [NursingActionController::class, 'updateAssessments']);
        Route::post('dialysis-checks/{check_id}/medicines/pre', [NursingActionController::class, 'savePreDialysisMedicines']);
        Route::get('medicines/search', [MedicineController::class, 'search']);
        Route::get('routes-of-administration', [\App\Http\Controllers\RouteOfAdministrationController::class, 'index']);
        Route::post('dialysis-checks/{check_id}/absence-leave', [PatientController::class, 'issueAbsenceLeave']);
        Route::post('dialysis-checks/{check_id}/uf-goal', [NursingActionController::class, 'updateUfGoal']);
        Route::post('dialysis-checks/{check_id}/incidents', [NursingActionController::class, 'reportIncident']);
        Route::post('dialysis-checks/{check_id}/hct', [NursingActionController::class, 'updateHct']);
        Route::get('dialysis-checks/{check_id}/nursing-records', [NursingRecordController::class, 'index']);
        Route::post('dialysis-checks/{check_id}/nursing-records', [NursingRecordController::class, 'store']);
        Route::get('dialysis-checks/{check_id}/nurse-watchings', [NursingActionController::class, 'fetchNurseWatchings']);
        Route::post('dialysis-checks/{check_id}/nurse-watchings', [NursingActionController::class, 'storeNurseWatching']);
        Route::put('nursing-records/{id}', [NursingRecordController::class, 'update']);
        Route::delete('nursing-records/{id}', [NursingRecordController::class, 'destroy']);
        Route::get('dialysis-checks/{check_id}/orders', [OrderExecutionController::class, 'index']);
        Route::post('dialysis-checks/{check_id}/orders', [OrderExecutionController::class, 'store']);
        Route::patch('orders/{id}/execution', [OrderExecutionController::class, 'updateExecution']);
        Route::post('orders/{id}/execution', [OrderExecutionController::class, 'updateExecution']);
        Route::post('orders/{table}/{id}/action', [OrderExecutionController::class, 'handleOrderAction']);
        Route::get('nursing/shift-options', [NursingActionController::class, 'fetchShiftOptions']);
        Route::post('nursing/shifts', [NursingActionController::class, 'saveShift']);
        Route::get('nursing/supply-tmr', [NursingActionController::class, 'fetchSupplyList']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

            Route::get('dialysis-checks/{check_id}/monitoring', [NursingActionController::class, 'fetchMonitoringData']);

});
