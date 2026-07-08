<?php

namespace App\Http\Controllers;

use App\Models\BedBinding;
use App\Models\BedPatientCard;
use App\Models\Hospital;
use App\Models\PatientCard;
use App\Models\PatientReservation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    /** 同時回傳 roster + reservations */
    public function index(Request $req)
    {
        $start = Carbon::parse($req->query('start', Carbon::today()->toDateString()));
        $end   = Carbon::parse($req->query('end',   $start->copy()->toDateString()));
        if ($end->lt($start)) [$start, $end] = [$end, $start];

        $hospitalId = (int) $req->query('hospital_id', 1);

        $roster = $this->buildRoster($hospitalId, $start, $end);

        $reservations = PatientReservation::query()
            ->with(['patient','machine_bed'])
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            // ->where('status', 0)
            ->where('patient_id', '!=', 0)
            ->get();

        $beds = BedPatientCard::with(['bed', 'card'])->get();

        // 嘗試附加 zone / binding 資料（若相關 table 存在）
        $hasZones = $this->tableExists('zones');
        $hasBindings = $this->tableExists('bed_bindings');

        $beds->each(function ($bpc) use ($hasZones, $hasBindings) {
            $bpc->zone = null;
            $bpc->dialysis_device = null;
            $bpc->bp_device = null;

            if ($hasZones && $hasBindings && $bpc->bed) {
                $binding = BedBinding::with(['zone', 'dialysisDevice', 'bpDevice'])
                    ->where('bed_label', $bpc->bed->bed_no)
                    ->orWhere('bed_number', (string) $bpc->bed->bed_no)
                    ->first();

                if ($binding) {
                    $bpc->zone = $binding->zone;
                    $bpc->dialysis_device = $binding->dialysisDevice;
                    $bpc->bp_device = $binding->bpDevice;
                }
            }
        });

        return response()->json([
            'roster' => $roster,            // [{date:'YYYY-MM-DD', enabledShifts:['MORNING'..]}]
            'reservations' => $reservations, // [{ id, patient_id, date, morning_noon_night, machine_bed_id, ... }]
            'beds' => $beds,
        ]);
    }

    /** 只回 roster（若你要分開打） */
    public function roster(Request $req): JsonResponse
    {
        $start = Carbon::parse($req->query('start', Carbon::today()->toDateString()));
        $end   = Carbon::parse($req->query('end',   $start->copy()->toDateString()));
        if ($end->lt($start)) [$start, $end] = [$end, $start];

        $hospitalId = (int) $req->query('hospital_id', 1);
        return response()->json($this->buildRoster($hospitalId, $start, $end));
    }

    /** 將 Hospital 的 mon_1..sun_3 轉成前端要的 enabledShifts */
    /** 安全檢查 table 是否存在 */
    private function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function buildRoster(int $hospitalId, Carbon $start, Carbon $end): array
    {
        $hospital = Hospital::query()->find($hospitalId);
        if (!$hospital) return [];

        $shiftMap = [1=>'MORNING', 2=>'AFTERNOON', 3=>'NIGHT'];     // 1:早 2:午 3:晚
        $dowPref  = [1=>'mon',2=>'tue',3=>'wed',4=>'thu',5=>'fri',6=>'sat',7=>'sun'];

        $period = CarbonPeriod::create($start, '1 day', $end);
        $out = [];
        foreach ($period as $day) {
            $dow = (int) $day->isoFormat('E'); // 1..7
            $pre = $dowPref[$dow];
            $enabled = [];
            for ($k=1; $k<=3; $k++) {
                $col = "{$pre}_{$k}";
                if (!empty($hospital->$col)) $enabled[] = $shiftMap[$k];
            }
            $out[] = [
                'date' => $day->toDateString(),
                'enabledShifts' => $enabled,
                // 額外：把 time_1..3 一併回傳（前端若要顯示）
                'timeLabels' => [
                    'MORNING'   => $hospital->time_1 ?? null,
                    'AFTERNOON' => $hospital->time_2 ?? null,
                    'NIGHT'     => $hospital->time_3 ?? null,
                ]
            ];
        }
        return $out;
    }
}
