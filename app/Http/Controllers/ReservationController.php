<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\PatientCheck;
use App\Models\PatientReservation;
use Carbon\Carbon;
use DateTime;
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function store(Request $req)
    {
        $this->normalizeStatus($req);
        $data = $this->validatePayload($req);
        if (Carbon::parse($data['date'])->lt(Carbon::today())) {
            return response()->json(['message' => '過去日期不可新增'], 422);
        }
        if (!$this->isShiftEnabled($req->query('hospital_id',1), $data['date'], $data['morning_noon_night'])) {
            return response()->json(['message' => '該日未開此班別'], 422);
        }
        // 同一天同一病患不可重複
        $existsSamePatientSameDay = PatientReservation::where('date', $data['date'])
            ->where('patient_id', $data['patient_id'])
            ->exists();
        if ($existsSamePatientSameDay) {
            return response()->json(['message' => '該病患此日期已經有預約，不能重複建立'], 422);
        }

        $exists = PatientReservation::where('date',$data['date'])
            ->where('morning_noon_night',$data['morning_noon_night'])
            ->where('machine_bed_id',$data['machine_bed_id'])
            ->where('patient_id', '!=', 0)
            ->where('status', 0)
            ->exists();
        if ($exists) return response()->json(['message'=>'此床位該班別已有預約'], 422);

        $row = PatientReservation::create($data);
        PatientCheck::create([
            'patient_reservation_id' => $row->id,
            'date' => $row->date,
            'status' => 0,
            'have_dialysis_data' => 0,
        ]);
        return response()->json($row->load(['patient','machine_bed']), 201);
    }

    public function update(Request $req, $id)
    {
        $row = PatientReservation::findOrFail($id);
        if ($row->date->lt(Carbon::today())) {
            return response()->json(['message' => '過去日期不可修改'], 422);
        }
        $this->normalizeStatus($req);
        $data = $this->validatePayload($req, $id);
        $newDate  = $data['date'] ?? $row->date->toDateString();
        $newShift = $data['morning_noon_night'] ?? $row->morning_noon_night;
        $newBed   = $data['machine_bed_id'] ?? $row->machine_bed_id;

        if (!$this->isShiftEnabled($req->query('hospital_id',1), $newDate, $newShift)) {
            return response()->json(['message' => '該日未開此班別'], 422);
        }
        $exists = PatientReservation::where('date',$newDate)
            ->where('morning_noon_night',$newShift)
            ->where('machine_bed_id',$newBed)
            ->where('id','!=',$row->id)
            ->where('patient_id', '!=', 0)
            ->exists();
        if ($exists) return response()->json(['message'=>'此床位該班別已有預約'], 422);

        $newPatientId = $data['patient_id']  ?? $row->patient_id;

        // 同一天同一病患不可重複（排除自己）
        $existsSamePatientSameDay = PatientReservation::where('date', $newDate)
            ->where('patient_id', $newPatientId)
            ->where('id', '!=', $row->id)
            ->exists();
        if ($existsSamePatientSameDay) {
            return response()->json(['message' => '該病患此日期已經有預約，不能重複建立'], 422);
        }
            

        $row->fill($data)->save();
        $patientCheck = PatientCheck::where('patient_reservation_id', $row->id)->first();
        $patientCheck->date = $row->date;
        $patientCheck->save();
        return response()->json($row->load(['patient','machine_bed']));
    }

    public function destroy($id)
    {
        $row = PatientReservation::findOrFail($id);
        if ($row->date->lt(Carbon::today())) {
            return response()->json(['message' => '過去日期不可刪除'], 422);
        }
        $patientCheck = PatientCheck::where('patient_reservation_id', $row->id)->first();
        $patientCheck->delete();
        $row->delete();
        return response()->json(['ok'=>true]);
    }

    private function validatePayload(Request $req, $id = null): array
    {
        return $req->validate([
            'patient_id'         => ['required','integer','exists:patients,id'],
            'date'               => ['required','date_format:Y-m-d'],
            'status'             => ['nullable','integer', Rule::in([0,1,2])], // 0=無、1=請假、2=住院
            'morning_noon_night' => ['required','integer', Rule::in([0,1,2])], // ✅ 0=早 1=午 2=晚
            'machine_bed_id'     => ['required','integer','exists:bed_patient_cards,id'],
        ]);
    }

    /** 用 Hospital 檢查該日是否開該班 */
    private function isShiftEnabled(int $hospitalId, string $date, int $shift): bool
    {
        $h = Hospital::find($hospitalId);
        if (!$h) return false;

        $dow = (int) \Carbon\Carbon::parse($date)->isoFormat('E'); // 1..7
        $pre = [1=>'mon',2=>'tue',3=>'wed',4=>'thu',5=>'fri',6=>'sat',7=>'sun'][$dow];

        $colIndex = $shift + 1;          // 0→1(早), 1→2(午), 2→3(晚)
        $col = "{$pre}_{$colIndex}";

        return !empty($h->$col);
    }

    // 建議放在 Controller 開頭定義
    private const STATUS_MAP = [
        'PENDING'   => 0,
        'CONFIRMED' => 1,
        'CANCELLED' => 2,
        'NOSHOW'    => 3,
        'DONE'      => 4,
    ];

    // store / update 一開頭就做：
    private function normalizeStatus(\Illuminate\Http\Request $req): void {
        $raw = $req->input('status');
        if (is_string($raw)) {
            $map = self::STATUS_MAP;
            if (isset($map[$raw])) {
                $req->merge(['status' => $map[$raw]]);
            }
        }
    }

    function applyReserve(Request $request){
        $date = $request->input('date');

        $datetime = new DateTime($date); // 你要查找的日期
        $datetime->modify('Monday this week');  // 調整到該週的星期一
        $firstDayOnWeek = $datetime->format('Y-m-d');  // 格式化並輸出日期
        $lastDayOnWeek = date('Y-m-d', strtotime($firstDayOnWeek.' +5 day')); // 改成+5天 (星期六)
        $patientReservations = PatientReservation::whereBetween('date', [$firstDayOnWeek, $lastDayOnWeek])->where('status', 0)->where('patient_id', '!=', 0)->get();

        foreach ($patientReservations as $key => $patientReservation) {
            # code...
            $dayOnNextWeek = date('Y-m-d', strtotime($patientReservation->date.' +7 day'));
            $patientReservation_nextWeek_same_patient = PatientReservation::where('date', $dayOnNextWeek)->where('patient_id', $patientReservation->patient_id)->first();
            if($patientReservation_nextWeek_same_patient == null){
                $patientReservation_nextWeek = PatientReservation::where('date', $dayOnNextWeek)->where('morning_noon_night', $patientReservation->morning_noon_night)->where('machine_bed_id', $patientReservation->machine_bed_id)->where('patient_id', '!=', 0)->first();
                if($patientReservation_nextWeek == null){
                    $data = PatientReservation::create([
                        'patient_id' => $patientReservation->patient_id,
                        'date' => $dayOnNextWeek,
                        'morning_noon_night' => $patientReservation->morning_noon_night,
                        'machine_bed_id' => $patientReservation->machine_bed_id,
                        'status' => 0,
                    ]);
                    PatientCheck::create([
                        'patient_reservation_id' => $data->id,
                        'date' => $dayOnNextWeek,
                        'status' => 0,
                        'have_dialysis_data' => 0,
                    ]);
                } 
            }
        }

        return json_encode([
            'status' => 1
        ]);
    }
}
