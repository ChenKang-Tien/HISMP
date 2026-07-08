<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\BedBinding;
use App\Models\BedPatientCard;
use App\Models\Patient;
use App\Models\PatientCard;
use App\Models\PatientCheck;
use App\Models\PatientReservation;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    // ========================================================================
    //  2. 病患排班列表
    //  GET /schedules/patients
    // ========================================================================
    public function patients(Request $req)
    {
        $query = Patient::query()
            ->where('deleted', 0)
            ->where('dead', 0);

        if ($q = $req->query('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")
                    ->orWhere('medical_record_no', 'like', "%{$q}%");
            });
        }

        $patients = $query->orderBy('name')->get(['id', 'name', 'medical_record_no']);

        $patients->each(function ($p) {
            $mostUsedShift = PatientReservation::where('patient_id', $p->id)
                ->where('status', 0)
                ->selectRaw('morning_noon_night, COUNT(*) as cnt')
                ->groupBy('morning_noon_night')
                ->orderByDesc('cnt')
                ->first();

            $p->default_shift = $mostUsedShift ? (int) $mostUsedShift->morning_noon_night : null;
        });

        return response()->json([
            'patients' => $patients,
        ]);
    }

    // ========================================================================
    //  3. 班表 CRUD
    // ========================================================================

    public function store(Request $req)
    {
        $data = $this->validateSchedulePayload($req);
        $data['status'] = 0;

        if (Carbon::parse($data['date'])->lt(Carbon::today())) {
            return response()->json(['message' => '過去日期不可新增排班'], 422);
        }

        if (!$this->isShiftEnabled($req, $data['date'], $data['morning_noon_night'])) {
            return response()->json(['message' => '該日未開此班別'], 422);
        }

        $existsSame = PatientReservation::where('date', $data['date'])
            ->where('patient_id', $data['patient_id'])
            ->exists();
        if ($existsSame) {
            return response()->json(['message' => '該病患此日期已經有排班，不能重複'], 422);
        }

        $existsBed = PatientReservation::where('date', $data['date'])
            ->where('morning_noon_night', $data['morning_noon_night'])
            ->where('machine_bed_id', $data['machine_bed_id'])
            ->where('patient_id', '!=', 0)
            ->exists();
        if ($existsBed) {
            return response()->json(['message' => '此床位該班別已有排班'], 422);
        }

        $row = PatientReservation::create($data);

        PatientCheck::create([
            'patient_reservation_id' => $row->id,
            'date' => $row->date,
            'status' => 0,
            'have_dialysis_data' => 0,
        ]);

        return response()->json($row->load(['patient', 'machine_bed']), 201);
    }

    public function update(Request $req, $id)
    {
        $row = PatientReservation::findOrFail($id);
        if ($row->date->lt(Carbon::today())) {
            return response()->json(['message' => '過去日期不可修改排班'], 422);
        }

        $data = $this->validateSchedulePayload($req, $id);

        $newDate  = $data['date'] ?? $row->date->toDateString();
        $newShift = $data['morning_noon_night'] ?? $row->morning_noon_night;

        if (!$this->isShiftEnabled($req, $newDate, $newShift)) {
            return response()->json(['message' => '該日未開此班別'], 422);
        }

        $existsSame = PatientReservation::where('date', $newDate)
            ->where('patient_id', $data['patient_id'])
            ->where('id', '!=', $row->id)
            ->exists();
        if ($existsSame) {
            return response()->json(['message' => '該病患此日期已經有排班，不能重複'], 422);
        }

        $newBed = $data['machine_bed_id'] ?? $row->machine_bed_id;

        $existsBed = PatientReservation::where('date', $newDate)
            ->where('morning_noon_night', $newShift)
            ->where('machine_bed_id', $newBed)
            ->where('id', '!=', $row->id)
            ->where('patient_id', '!=', 0)
            ->exists();
        if ($existsBed) {
            return response()->json(['message' => '此床位該班別已有排班'], 422);
        }

        $row->fill($data)->save();

        PatientCheck::where('patient_reservation_id', $row->id)
            ->update(['date' => $row->date]);

        return response()->json($row->load(['patient', 'machine_bed']));
    }

    public function destroy($id)
    {
        $row = PatientReservation::findOrFail($id);
        if ($row->date->lt(Carbon::today())) {
            return response()->json(['message' => '過去日期不可刪除排班'], 422);
        }

        PatientCheck::where('patient_reservation_id', $row->id)->delete();
        $row->delete();

        return response()->json(['ok' => true]);
    }

    // ========================================================================
    //  4. 請假/住院 API
    // ========================================================================

    public function storeLeave(Request $req)
    {
        $data = $req->validate([
            'patient_id'         => ['required', 'integer', 'exists:patients,id'],
            'date'               => ['required', 'date_format:Y-m-d'],
            'morning_noon_night' => ['nullable', 'integer', Rule::in([0, 1, 2])],
            'type'               => ['required', 'integer', Rule::in([1, 2])],
        ]);

        $query = PatientReservation::where('patient_id', $data['patient_id'])
            ->where('date', $data['date']);

        if (array_key_exists('morning_noon_night', $data) && $data['morning_noon_night'] !== null) {
            $query->where('morning_noon_night', $data['morning_noon_night']);
        }

        $reservation = $query->first();

        if (!$reservation) {
            return response()->json(['message' => '該時段無對應排班記錄，無法設定請假/住院'], 404);
        }

        if ($reservation->date->lt(Carbon::today())) {
            return response()->json(['message' => '過去日期不可修改狀態'], 422);
        }

        $reservation->status = $data['type'];
        $reservation->save();

        return response()->json($reservation->load('patient'), 200);
    }

    public function listLeaves(Request $req)
    {
        $query = PatientReservation::with('patient')
            ->whereIn('status', [1, 2])
            ->where('patient_id', '!=', 0)
            ->orderBy('date', 'desc');

        if ($start = $req->query('start')) {
            $query->where('date', '>=', $start);
        }
        if ($end = $req->query('end')) {
            $query->where('date', '<=', $end);
        }

        if ($q = $req->query('q')) {
            $query->whereHas('patient', function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")
                    ->orWhere('medical_record_no', 'like', "%{$q}%");
            });
        }

        $leaves = $query->paginate($req->query('per_page', 50));

        $leaves->getCollection()->transform(function ($item) {
            $item->type_label = $item->status == 1 ? '請假' : '住院';
            return $item;
        });

        return response()->json($leaves);
    }

    public function destroyLeave($id)
    {
        $row = PatientReservation::findOrFail($id);

        if (!in_array($row->status, [1, 2])) {
            return response()->json(['message' => '該記錄目前不是請假或住院狀態'], 422);
        }

        if ($row->date->lt(Carbon::today())) {
            return response()->json(['message' => '過去日期不可修改狀態'], 422);
        }

        $row->status = 0;
        $row->save();

        return response()->json(['ok' => true]);
    }

    // ========================================================================
    //  5. 床位綁定資料
    //  GET /schedules/beds
    // ========================================================================
    public function beds()
    {
        $collection = BedPatientCard::with(['bed', 'card'])->get();

        $hasZones = $this->tableExists('zones');
        $hasBindings = $this->tableExists('bed_bindings');

        $collection->each(function ($bpc) use ($hasZones, $hasBindings) {
            $bpc->zone = null;
            $bpc->dialysis_device = null;
            $bpc->bp_device = null;

            if ($hasZones && $hasBindings && $bpc->bed) {
                $binding = BedBinding::with(['zone', 'dialysisDevice', 'bpDevice'])
                    ->where('bed_label', $bpc->bed->bed_no)
                    ->orWhere(function ($q) use ($bpc) {
                        if ($bpc->bed) {
                            $q->where('bed_number', (string) $bpc->bed->bed_no);
                        }
                    })
                    ->first();

                if ($binding) {
                    $bpc->zone = $binding->zone;
                    $bpc->dialysis_device = $binding->dialysisDevice;
                    $bpc->bp_device = $binding->bpDevice;
                }
            }
        });

        return response()->json([
            'beds' => $collection,
        ]);
    }

    // ========================================================================
    //  輔助方法
    // ========================================================================

    private function validateSchedulePayload(Request $req, $id = null): array
    {
        return $req->validate([
            'patient_id'         => ['required', 'integer', 'exists:patients,id'],
            'date'               => ['required', 'date_format:Y-m-d'],
            'morning_noon_night' => ['required', 'integer', Rule::in([0, 1, 2])],
            'machine_bed_id'     => ['required', 'integer', 'exists:bed_patient_cards,id'],
        ]);
    }

    private function isShiftEnabled(Request $req, string $date, int $shift): bool
    {
        $hospitalId = (int) $req->query('hospital_id', 1);
        $h = \App\Models\Hospital::find($hospitalId);
        if (!$h) return false;

        $dow = (int) Carbon::parse($date)->isoFormat('E');
        $pre = [1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat', 7 => 'sun'][$dow];

        $colIndex = $shift + 1;
        $col = "{$pre}_{$colIndex}";

        return !empty($h->$col);
    }

    private function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
