<?php

namespace App\Http\Controllers;

use App\Models\BedBinding;
use App\Models\Device;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    /**
     * 取得設備列表（可依 type 過濾）
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Device::query();

        // 可選 type 過濾: dialysis / scale / bp
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $devices = $query->orderBy('code')->get();

        return response()->json([
            'devices' => $devices,
        ]);
    }

    /**
     * 取得單一設備詳細資料
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $device = Device::findOrFail($id);

        return response()->json([
            'device' => $device,
        ]);
    }

    // ============================
    // 區域 (Zone) CRUD
    // ============================

    /**
     * 取得區域列表（含綁定資料）
     */
    public function getZones()
    {
        $zones = Zone::with('bedBindings')->orderBy('code')->get();

        return response()->json([
            'zones' => $zones,
        ]);
    }

    /**
     * 新增區域
     */
    public function storeZone(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:zones,code',
            'name' => 'required|string|max:50',
            'bed_count' => 'required|integer|min:0|max:99',
        ]);

        $zone = Zone::create($validated);

        // 自動產生床號綁定（跳過含4的號碼）
        $this->generateBedBindings($zone);

        return response()->json([
            'zone' => $zone->load('bedBindings'),
        ], 201);
    }

    /**
     * 更新區域
     */
    public function updateZone(Request $request, $id)
    {
        $zone = Zone::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:zones,code,' . $id,
            'name' => 'required|string|max:50',
            'bed_count' => 'required|integer|min:0|max:99',
        ]);

        $zone->update($validated);

        // 重新產生床號綁定
        $this->generateBedBindings($zone);

        return response()->json([
            'zone' => $zone->load('bedBindings'),
        ]);
    }

    /**
     * 刪除區域
     */
    public function destroyZone($id)
    {
        $zone = Zone::findOrFail($id);
        $zone->delete();

        return response()->json([
            'message' => '區域已刪除',
        ]);
    }

    // ============================
    // 綁定 (Binding) API
    // ============================

    /**
     * 取得所有綁定資料
     */
    public function getBindings()
    {
        $bindings = BedBinding::with([
            'zone',
            'dialysisDevice',
            'bpDevice',
        ])->orderBy('bed_label')->get();

        return response()->json([
            'bindings' => $bindings,
        ]);
    }

    /**
     * 儲存綁定設定（批次更新）
     * 
     * 接受格式:
     * {
     *   bindings: [
     *     { id: 1, dialysis_device_id: null|int, bp_device_id: null|int },
     *     ...
     *   ]
     * }
     */
    public function saveBindings(Request $request)
    {
        $validated = $request->validate([
            'bindings' => 'required|array',
            'bindings.*.id' => 'required|exists:bed_bindings,id',
            'bindings.*.dialysis_device_id' => 'nullable|exists:devices,id',
            'bindings.*.bp_device_id' => 'nullable|exists:devices,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['bindings'] as $item) {
                BedBinding::where('id', $item['id'])->update([
                    'dialysis_device_id' => $item['dialysis_device_id'] ?? null,
                    'bp_device_id' => $item['bp_device_id'] ?? null,
                ]);
            }
        });

        return response()->json([
            'message' => '綁定設定已儲存',
        ]);
    }

    // ============================
    // 輔助方法
    // ============================

    /**
     * 依床數自動產生床號綁定記錄（跳過含4的號碼）
     */
    private function generateBedBindings(Zone $zone)
    {
        // 刪除舊的綁定
        BedBinding::where('zone_id', $zone->id)->delete();

        if ($zone->bed_count <= 0) {
            return;
        }

        $bindings = [];
        $count = 0;
        $num = 1;

        while ($count < $zone->bed_count) {
            $numStr = str_pad((string)$num, 2, '0', STR_PAD_LEFT);

            // 跳過含數字4的號碼
            if (strpos($numStr, '4') === false) {
                $bindings[] = [
                    'zone_id' => $zone->id,
                    'bed_number' => $numStr,
                    'bed_label' => $zone->code . '-' . $numStr,
                    'dialysis_device_id' => null,
                    'bp_device_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
            $num++;
        }

        BedBinding::insert($bindings);
    }
}
