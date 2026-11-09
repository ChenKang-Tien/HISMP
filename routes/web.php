<?php

use App\Models\Patient;
use Illuminate\Support\Facades\Route;
use App\Models\PatientReservation;
use App\Models\PatientCheck;
use App\Models\PatientMidDialysisRecordNew;
use Illuminate\Support\Facades\Auth;

Route::get('/test/random-schedule', function () {
    $shifts = [1, 2, 3]; // 1:早, 2:午, 3:晚
    $today = now()->format('Y-m-d');

    // 確保每班各產生一筆
    $today = now()->format('Y-m-d');

    // 清除當天的測試資料
    $reservations = PatientReservation::where('date', $today)->get();
    foreach ($reservations as $res) {
        $check = PatientCheck::where('patient_reservation_id', $res->id)->first();
        if ($check) {
            PatientMidDialysisRecordNew::where('patient_check_id', $check->id)->delete();
            $check->delete();
        }
        $res->delete();
    }

    foreach ($shifts as $shift) {
        $newData = PatientReservation::create([
            'hospital_id'        => 1, // 補上這個欄位
            'patient_id'         => rand(1, 10),
            'date'               => $today,
            'morning_noon_night' => $shift,
            'machine_bed_id'     => rand(1, 20),
            'status'             => 0,
        ]);

        $start_time = date('Y-m-d').' 06:00:00';
        $end_time = date('Y-m-d').' 12:00:00';
        if($shift == 1){
            $start_time = date('Y-m-d').' 06:00:00';
            $end_time = date('Y-m-d').' 12:00:00';
        }
        else if($shift == 2){
            $start_time = date('Y-m-d').' 13:00:00';
            $end_time = date('Y-m-d').' 17:00:00';
        }
        else if($shift == 3){
            $start_time = date('Y-m-d').' 18:00:00';
            $end_time = date('Y-m-d').' 22:00:00';
        }

        $patientCheck = PatientCheck::create([
            'patient_reservation_id' => $newData->id,
            'date'             => $today,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'status'                 => 0,
            'have_dialysis_data'     => 0,
        ]);

        addDialysisData($patientCheck->id);
    }

    return response()->json(['message' => '早、午、晚班測試資料各一已建立']);
});

Route::get('/test/generate-dialysis-data/{check_id}', function ($check_id) {
    try {
        addDialysisData($check_id);
        return response()->json(['success' => true, 'message' => "Dialysis data generated for check_id: {$check_id}"]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
});

function addDialysisData($check_id){


    $patientCheck = PatientCheck::findOrFail($check_id);
    $patientReservation = $patientCheck->patient_reservation;
    $patient = $patientReservation->patient;

    // 根據班別設定預設開始與結束時間
    if (!$patientCheck->start_time) {
        $shift = $patientReservation->morning_noon_night;
        $times = match($shift) {
            1 => ['06:00:00', '10:00:00'], // 早
            2 => ['13:00:00', '17:00:00'], // 午
            3 => ['18:00:00', '22:00:00'], // 晚
            default => ['06:00:00', '10:00:00']
        };
        $patientCheck->update([
            'start_time' => $patientCheck->date . ' ' . $times[0],
            'end_time'   => $patientCheck->date . ' ' . $times[1]
        ]);
    }

    $start_time = $patientCheck->start_time;

    $HMNO = $patientReservation->machine_bed->card->card_no;
    $total_UFTM = 240; // 透析時間
    $total_UF = 1600;
    $total_HPCV = 15;
    $add_HPCV = $total_HPCV/ $total_UFTM;
    $add_uf = $total_UF/ $total_UFTM;
    $BLDF = 280;
    $VEPS = 196;
    $TMP = 40;
    $HPDR = 5;
    $HPCV = 0;
    $BBADJ = 0;
    $UFVL = 0;
    $CDCT = 560;
    $DLTP = 356;
    $UFRA = 426;
    $DLFL = 500;

    PatientMidDialysisRecordNew::where('patient_check_id', $check_id)->delete();

    $index = 0;
    for ($UFTM=$total_UFTM; $UFTM > -1; $UFTM--) {
        # code...

        $HCDTTM = date('Y-m-d H:i:s', strtotime($start_time.' + '.$index.' minute'));
        $UF = round($index * $add_uf);
        $HPCV = round($index * $add_HPCV);
        $UFVL = $UF;
        PatientMidDialysisRecordNew::create([
            'HCDTTM' =>$HCDTTM,
            'HMNO' => $HMNO,
            'HMMN' => '4008B',
            'PBAF' => '0000',
            'UFEM' => $HMNO,
            'UFTM' => $UFTM,
            'BLDF' => $BLDF,
            'VEPS' => $VEPS,
            'TMP' => $TMP,
            'CDCT' => $CDCT,
            'DLTP' => $DLTP,
            'UFRA' => $UFRA,
            'DLFL' => $DLFL,
            'HPDR' => $HPDR,
            'HPCV' => $HPCV,
            'BBADJ' => $BBADJ,
            'UFVL' => $UFVL,
            'UF' => $UF,

            'patient_check_id' => $check_id
        ]);
        $index++;
    }

    // 產生血壓模擬數據
    $bpPoints = [
        0 => '-5 minutes', // 透前
        60 => '+60 minutes', // 第一小時
        120 => '+120 minutes', // 第二小時
        180 => '+180 minutes', // 第三小時
        240 => '+240 minutes', // 第四小時
        245 => '+245 minutes', // 透後一
        250 => '+250 minutes', // 透後二
    ];

    foreach ($bpPoints as $offset => $timeStr) {
        $time = date('Y-m-d H:i:s', strtotime($start_time . ' ' . $timeStr));
        $sys = rand(110, 150);
        $dia = rand(70, 90);
        $p = rand(60, 90);

        PatientMidDialysisRecordNew::create([
            'HCDTTM' => $time,
            'HMNO'   => $HMNO,
            'BDPS'   => $sys,
            'BDPD'   => $dia,
            'BDPL'   => $p, // 脈搏
            'MAP'    => round(($sys + 2 * $dia) / 3),
            'patient_check_id' => $check_id
        ]);

        // PatientMidBpPDatas::create([
        //     'patient_check_id' => $check_id,
        //     'time' => $time,
        //     'systolic_blood_pressure'=> $sys,
        //     'diastolic_blood_pressure'=> $dia,
        //     'P' => $p,
        //     'machine' => 1,
        //     'nurse_id' => 1,
        // ]);
    }

    //
}

// 🏥 前端萬用門戶：除了開頭是 /api 的請求，其他網址全部交給前端 Vue 3 處理
Route::get('/{any}', function () {
    return view('app'); // 指向你的 Vue 載入地基
})->where('any', '^(?!api).*$');
