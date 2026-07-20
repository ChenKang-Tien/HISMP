<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Hospital;
use App\Models\PatientCheck;
use App\Models\PatientReservation;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * GET /hospital/dashboard
     * 回傳今日透析儀表板統計 JSON
     */
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // ── 1. 問候資訊 ──
        $hospital = Hospital::first();
        $now = Carbon::now();
        $hour = (int) $now->format('H');

        // 判斷班別：早班(0) 中班(1) 晚班(2)
        if ($hour < 11) {
            $shiftCode = 0;
            $shiftLabel = '早班 08:00–12:00';
        } elseif ($hour < 17) {
            $shiftCode = 1;
            $shiftLabel = '午班 11:00–17:00';
        } else {
            $shiftCode = 2;
            $shiftLabel = '晚班 17:00–22:00';
        }

        $weekdayNames = ['日', '一', '二', '三', '四', '五', '六'];
        $dateStr = $now->format('Y/m/d') . '（週' . $weekdayNames[(int)$now->format('w')] . '）';
        $hospitalName = $hospital?->name ?? '—';

        // ── 2. 即時數據（從 patient_checks + patient_reservations） ──
        $todayReservations = PatientReservation::where('date', $today)
            ->where('status', 1) // 有效預約
            ->count();

        $todayChecks = PatientCheck::where('date', $today)->get();

        $dialyzingCount = 0;
        $waitingCount   = 0;
        $completedCount = 0;

        foreach ($todayChecks as $check) {
            // status 5 = 已完成（來自 FinishController）
            if ($check->status == 5) {
                $completedCount++;
                continue;
            }
            // status 1, 2 = 請假/住院（排除）
            if (in_array($check->status, [1, 2])) {
                continue;
            }
            // 其餘 = 活躍中
            if ($check->start_time !== null) {
                $dialyzingCount++;
            } else {
                $waitingCount++;
            }
        }

        $realtime = [
            'dialysis' => $dialyzingCount,
            'waiting'   => $waitingCount,
            'alert'     => 0,          // 暫時無異常警示數據來源
            'completed' => $completedCount,
            'total'     => max($todayReservations, $completedCount + $dialyzingCount + $waitingCount),
        ];

        // ── 3. 今日透析量（依班別，前端 am/pm/ev 物件格式） ──
        $shiftMap = [
            0 => 'am',
            1 => 'pm',
            2 => 'ev',
        ];

        $dialysis = [];

        foreach ($shiftMap as $code => $key) {
            $scheduled = PatientReservation::where('date', $today)
                ->where('morning_noon_night', $code)
                ->where('status', 1)
                ->count();

            $completedShift = PatientCheck::where('date', $today)
                ->where('status', 5)
                ->whereHas('patient_reservation', function ($q) use ($code) {
                    $q->where('morning_noon_night', $code);
                })
                ->count();

            // 各時段的進行中/等待中細分
            $shiftChecks = PatientCheck::where('date', $today)
                ->whereHas('patient_reservation', function ($q) use ($code) {
                    $q->where('morning_noon_night', $code);
                })
                ->get();

            $shiftInProgress = 0;
            $shiftWaiting = 0;
            foreach ($shiftChecks as $check) {
                if (in_array($check->status, [1, 2, 5])) {
                    continue;
                }
                if ($check->start_time !== null) {
                    $shiftInProgress++;
                } else {
                    $shiftWaiting++;
                }
            }

            // ── 各時段前端格式 ──
            if ($key === 'am') {
                $dialysis['am'] = [
                    'count' => $scheduled,
                    'done'  => $completedShift,
                    'total' => $scheduled,
                ];
            } elseif ($key === 'pm') {
                $dialysis['pm'] = [
                    'count'       => $scheduled,
                    'in_progress' => $shiftInProgress,
                    'waiting'     => $shiftWaiting,
                ];
            } else {
                // ev — 依目前時段決定狀態文字
                if ($code < $shiftCode) {
                    $statusText = $completedShift > 0 ? '已完成' : '已結束';
                } elseif ($code == $shiftCode) {
                    $statusText = '目前進行中';
                } else {
                    $statusText = '預排 ・ 尚未開始';
                }
                $dialysis['ev'] = [
                    'count'  => $scheduled,
                    'status' => $statusText,
                ];
            }
        }

        // 床位數：取 Hospital.bed 或 Bed 表總數
        $totalBeds = $hospital?->bed ?? 0;
        if ($totalBeds <= 0) {
            $totalBeds = Bed::where('deleted', 0)->count();
        }
        $inUseBeds = max($dialyzingCount, 0);
        $bedUsageRate = $totalBeds > 0
            ? round(($inUseBeds / $totalBeds) * 100, 1)
            : 0;

        // ── 4. 護理人力（靜態佔位 ─ 日後可對接出勤系統） ──
        $nursingStaff = [
            'on_duty'      => 0,
            'on_duty_note' => '—',
            'leave'        => 0,
            'leave_note'   => '—',
            'ratio_label'  => '—',
            'ratio_note'   => '—',
        ];

        // ── 5. 設備狀態（靜態佔位 ─ 可搭配 Equipment 表擴充） ──
        $equipmentStatus = [
            'active'          => 0,
            'active_note'     => '台透析機',
            'maintenance'     => 0,
            'maintenance_note' => '—',
            'pending_service'  => 0,
            'pending_note'    => '—',
        ];

        // ── 6. 品質指標（靜態佔位） ──
        $qualityIndicators = [
            'infection'   => 0,
            'fall'        => 0,
            'near_miss'   => 0,
            'chart_signed' => 0,
        ];

        // ── 7. 待辦事項（靜態佔位） ──
        $todos = [];

        // ── 組合回應 ──
        return response()->json([
            'hospital_name'             => $hospitalName,
            'shift_label'               => $shiftLabel,
            'greeting'                  => [
                'clinic_name' => $hospitalName,
                'shift'       => $shiftLabel,
                'date'        => $dateStr,
            ],
            'realtime'                  => $realtime,
            'dialysis'                  => $dialysis,
            'bed_usage_rate'            => $bedUsageRate,
            'nurse'                     => $nursingStaff,
            'equipment'                 => $equipmentStatus,
            'quality_indicators'        => $qualityIndicators,
            'todos'                     => $todos,
        ]);
    }
}
