<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientBeforePreparation;
use App\Models\PatientCheck;
use App\Models\PatientHctInspectionRecord;
use App\Models\PatientHctInspectionRecordNew;
use Illuminate\Http\Request;

class AnemiaController extends Controller
{
    //
    function getHcts($id){
         $patient = Patient::findOrFail($id);

        // 最新在上（DESC 排序）
        $records = PatientHctInspectionRecordNew::where('patient_id', $id)
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($item, $idx) {

                return [
                    'id' => $item->id,
                    'date' => $item->date,
                    'month' => $item->month,
                    'week_of_month' => $item->week_of_month,
                    'hct' => (float) $item->hct,
                    'hct_add' => $item->hct_add ? (float) $item->hct_add : null,
                    'nurse_name' => $item->nurse?->name ?? null,
                    'can_edit_hct' => ($idx == 0)
                ];
            });

        return response()->json([
            'items' => $records
        ]);
    }

    function updateHct($hct_id, Request $request){
        $patientHctInspectionRecordNew = PatientHctInspectionRecordNew::findOrFail($hct_id);

        $patientHctInspectionRecordNew->hct = $request->input('hct');
        $patientHctInspectionRecordNew->hct_add = $request->input('hct_add');

        $patientHctInspectionRecordNew->save();

        return response()->json([
            'status' => 200
        ]);
    }

    public function getEpoNow($id)
    {
        # code...
        $patient = Patient::findOrFail($id);


        ////

        $patientBeforePreparation = PatientBeforePreparation::select('*')
        ->selectSub(
            PatientBeforePreparation::select('product_name')
                ->where('name', 'epo')
                ->whereHas('patient_check', function($q) use($patient){
                    $q->whereHas('patient_reservation', function($q2) use($patient){
                        $q2->where('patient_id', $patient->id);
                    });
                })
                ->orderByDesc('id')
                ->limit(1),
            'previous_value'
        )
        ->where('name', 'epo')
        ->whereNotNull('product_name')
        ->whereHas('patient_check', function($q) use($patient){
            $q->whereHas('patient_reservation', function($q2) use($patient){
                $q2->where('patient_id', $patient->id);
            });
            $q->where('date', '<', date('Y-m-d'));
        })
        ->havingRaw('product_name <> previous_value OR previous_value IS NULL')
        ->orderByDesc('id')
        ->first();

        
        if($patientBeforePreparation != null){
            $product_name = $patientBeforePreparation->product_name;
            $medicine_equipment_id = $patientBeforePreparation->medicine_equipment_id;
            if($patientBeforePreparation->location_id == 1){
                $location = '靜脈';
            }
            else{
                $location = '皮下';
            }
            $start_date = $patientBeforePreparation->patient_check->date;
        }
        else{
            $product_name = "";
            $medicine_equipment_id = "";
            $location = "";
            $start_date = "";
        }

        $epos = Medicine::where('category_id', 2)->where('deleted', 0)->get();

        return response()->json([
            'status' => 200,
            'date' => $start_date,
            'medicine' => $product_name,
            'medicine_id' => $medicine_equipment_id,
            'location' => $location,
            'epos' => $epos
        ]);
    }

    public function getEpoPeriod($id, Request $request)
    {
        $patient = Patient::findOrFail($id);

        $startDate = $request->input('startDate');
        $endDate   = $request->input('endDate');
        $medicine_id = $request->input('medicine_id');

        /** 🔥 期間（使用 Carbon） */
        $start = date('Y-m-01', strtotime($startDate));   // 當月 1 號
        $end   = date('Y-m-t',  strtotime($endDate));     // 當月最後一天

        /** 🔥 查詢病患在期間內所有檢查（一次查完 ID） */
        $patientCheckIds = PatientCheck::whereHas('patient_reservation', function($q) use($patient) {
                $q->where('patient_id', $patient->id);
            })
            ->whereBetween('date', [$start, $end])
            ->pluck('id');     // ← 不要 get()

        /** 🔥 查詢 epo 用量（一次處理） */
        $query = PatientBeforePreparation::whereIn('patient_check_id', $patientCheckIds)
            ->where('name', 'epo');

        /** 若指定藥品 ID */
        if($medicine_id != 0){
            $query->where('medicine_equipment_id', $medicine_id);
        }

        /** 🔢 計算總用量 */
        $total = $query->sum('amount');  // ← DB 直接 SUM，不用 foreach

        return response()->json([
            'status' => 200,
            'total'  => $total,
        ]);
    }

    public function getEpoMonth($id, Request $request)
    {
        $patient = Patient::findOrFail($id);
        $date = $request->input('date');   // e.g. "2025-02"

        /** 🔥 月份起訖 */
        $start = date('Y-m-01', strtotime($date));
        $end   = date('Y-m-t',  strtotime($date));

        /** 🔥 查當月全部 epo 記錄（一次查完） */
        $records = PatientBeforePreparation::with(['patient_check', 'nurse'])
            ->where('name', 'epo')
            ->whereHas('patient_check', function($q) use($patient, $start, $end){
                $q->whereHas('patient_reservation', function($q2) use($patient){
                    $q2->where('patient_id', $patient->id);
                })
                ->whereBetween('date', [$start, $end]);
            })
            ->get()
            ->sortBy('patient_check.date');

        /** 🔥 各藥品合計 */
        $medicineMonths = collect($records)
            ->groupBy('product_name')
            ->map(function($items, $medicine){
                return [
                    'medicine' => $medicine,
                    'amount'   => $items->sum('amount')
                ];
            })
            ->values();

        /** 🔥 月份明細：每次處方紀錄 */
        $epoMonths = $records->map(function($r){
            return [
                'date'       => date('d', strtotime($r->patient_check->date)), // 只取日
                'medicine'   => $r->product_name,
                'amount'     => $r->amount,
                'nurse_name' => $r->nurse?->name ?? null
            ];
        });

        return response()->json([
            'status'         => 200,
            'epoMonths'      => $epoMonths,
            'medicineMonths' => $medicineMonths,
        ]);
    }

    public function getIronNow($id)
    {
        # code...
        $patient = Patient::findOrFail($id);


        ////

        $patientBeforePreparation = PatientBeforePreparation::select('*')
        ->selectSub(
            PatientBeforePreparation::select('product_name')
                ->where('name', 'iron')
                ->whereHas('patient_check', function($q) use($patient){
                    $q->whereHas('patient_reservation', function($q2) use($patient){
                        $q2->where('patient_id', $patient->id);
                    });
                })
                ->orderByDesc('id')
                ->limit(1),
            'previous_value'
        )
        ->where('name', 'iron')
        ->whereNotNull('product_name')
        ->whereHas('patient_check', function($q) use($patient){
            $q->whereHas('patient_reservation', function($q2) use($patient){
                $q2->where('patient_id', $patient->id);
            });
            $q->where('date', '<', date('Y-m-d'));
        })
        ->havingRaw('product_name <> previous_value OR previous_value IS NULL')
        ->orderByDesc('id')
        ->first();

        
        if($patientBeforePreparation != null){
            $product_name = $patientBeforePreparation->product_name;
            $medicine_equipment_id = $patientBeforePreparation->medicine_equipment_id;
            if($patientBeforePreparation->location_id == 1){
                $location = '靜脈';
            }
            else{
                $location = '皮下';
            }
            $start_date = $patientBeforePreparation->patient_check->date;
        }
        else{
            $product_name = "";
            $medicine_equipment_id = "";
            $location = "";
            $start_date = "";
        }

        $irons = Medicine::where('category_id', 3)->where('deleted', 0)->get();

        return response()->json([
            'status' => 200,
            'date' => $start_date,
            'medicine' => $product_name,
            'medicine_id' => $medicine_equipment_id,
            'location' => $location,
            'irons' => $irons
        ]);
    }

    public function getIronPeriod($id, Request $request)
    {
        $patient = Patient::findOrFail($id);

        $startDate = $request->input('startDate');
        $endDate   = $request->input('endDate');
        $medicine_id = $request->input('medicine_id');

        /** 🔥 期間（使用 Carbon） */
        $start = date('Y-m-01', strtotime($startDate));   // 當月 1 號
        $end   = date('Y-m-t',  strtotime($endDate));     // 當月最後一天

        /** 🔥 查詢病患在期間內所有檢查（一次查完 ID） */
        $patientCheckIds = PatientCheck::whereHas('patient_reservation', function($q) use($patient) {
                $q->where('patient_id', $patient->id);
            })
            ->whereBetween('date', [$start, $end])
            ->pluck('id');     // ← 不要 get()

        /** 🔥 查詢 iron 用量（一次處理） */
        $query = PatientBeforePreparation::whereIn('patient_check_id', $patientCheckIds)
            ->where('name', 'iron');

        /** 若指定藥品 ID */
        if($medicine_id != 0){
            $query->where('medicine_equipment_id', $medicine_id);
        }

        /** 🔢 計算總用量 */
        $total = $query->sum('amount');  // ← DB 直接 SUM，不用 foreach

        return response()->json([
            'status' => 200,
            'total'  => $total,
        ]);
    }

    public function getIronMonth($id, Request $request)
    {
        $patient = Patient::findOrFail($id);
        $date = $request->input('date');   // e.g. "2025-02"

        /** 🔥 月份起訖 */
        $start = date('Y-m-01', strtotime($date));
        $end   = date('Y-m-t',  strtotime($date));

        /** 🔥 查當月全部 iron 記錄（一次查完） */
        $records = PatientBeforePreparation::with(['patient_check', 'nurse'])
            ->where('name', 'iron')
            ->whereHas('patient_check', function($q) use($patient, $start, $end){
                $q->whereHas('patient_reservation', function($q2) use($patient){
                    $q2->where('patient_id', $patient->id);
                })
                ->whereBetween('date', [$start, $end]);
            })
            ->get()
            ->sortBy('patient_check.date');

        /** 🔥 各藥品合計 */
        $medicineMonths = collect($records)
            ->groupBy('product_name')
            ->map(function($items, $medicine){
                return [
                    'medicine' => $medicine,
                    'amount'   => $items->sum('amount')
                ];
            })
            ->values();

        /** 🔥 月份明細：每次處方紀錄 */
        $ironMonths = $records->map(function($r){
            return [
                'date'       => date('d', strtotime($r->patient_check->date)), // 只取日
                'medicine'   => $r->product_name,
                'amount'     => $r->amount,
                'nurse_name' => $r->nurse?->name ?? null
            ];
        });

        return response()->json([
            'status'         => 200,
            'ironMonths'      => $ironMonths,
            'medicineMonths' => $medicineMonths,
        ]);
    }
}
