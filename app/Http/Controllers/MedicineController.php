<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DosageForm;
use App\Models\Medicine;
use App\Models\Provider;
use App\Models\Unit;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    //
    public function index()
    {
        // 預先載入關聯，避免 N+1 問題
        $medicines = Medicine::with(['category', 'unit', 'provider', 'dosage_form'])
            ->where('deleted', 0)
            ->orderBy('id', 'desc')
            ->get();

        $categories = Category::where('type', 1)->get();
        $providers = Provider::all();
        $units = Unit::where('type', 1)->get();
        $dosageForms = DosageForm::where('deleted', 0)->get();

        return response()->json([
            'medicines' => $medicines,
            'categories' => $categories,
            'providers' => $providers,
            'units' => $units,
            'dosageForms' => $dosageForms
        ]);
    }

    public function store(Request $request)
    {
        // 1. 定義驗證規則
        $rules = [
            'short_name'        => 'required|string|max:50',
            'chinese_name'      => 'required|string|max:255',
            'product_name'      => 'required|string|max:255', // 你改成的必填
            'scientific_name'   => 'required|string|max:255', // 你改成的必填
            'category_id'       => 'required|exists:categories,id', // 你改成的必填
            'unit_id'           => 'required|exists:units,id',      // 你改成的必填
            'provider_id'       => 'required|exists:providers,id',  // 你改成的必填
            'dosage_form_id'    => 'required|exists:dosage_forms,id', // 你改成的必填
            'inventory_control' => 'boolean',
            'need_packing'      => 'boolean',
            'immportant'        => 'boolean',
            'uses'              => 'nullable|string',
            'note'              => 'nullable|string',

            // 🔥 A液特殊驗證：雖然寫法比較長，但這是最安全的做法
            'na_k_ca' => [
                'nullable', // 先允許空值 (給非 A 液的藥品通過)
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->category_id) {
                        $category = Category::find($request->category_id);
                        // 如果分類名稱包含 'A液' 且欄位是空的 -> 報錯
                        if ($category && str_contains($category->name, 'A液') && empty($value)) {
                            $fail('當分類為 A 液時，「透析液成份 (Na/K/Ca)」為必填欄位。');
                        }
                    }
                },
            ],
        ];

        // 2. 定義中文錯誤訊息
        $messages = [
            // 必填 (Required) 錯誤
            'short_name.required'     => '「藥品簡碼」為必填欄位。',
            'chinese_name.required'   => '「中文名稱」為必填欄位。',
            'product_name.required'   => '「商品名/學名」為必填欄位。',
            'scientific_name'         => '「成分名/常用名」為必填欄位。',
            'category_id.required'    => '請選擇「分類」。',
            'unit_id.required'        => '請選擇「單位」。',
            'provider_id.required'    => '請選擇「廠牌/代理商」。',
            'dosage_form_id.required' => '請選擇「劑型」。',

            // 選項無效 (Exists) 錯誤 - 防止前端送不存在的 ID
            'category_id.exists'      => '所選的「分類」無效。',
            'unit_id.exists'          => '所選的「單位」無效。',
            'provider_id.exists'      => '所選的「廠牌/代理商」無效。',
            'dosage_form_id.exists'   => '所選的「劑型」無效。',

            // 格式錯誤
            'short_name.max'          => '「藥品簡碼」長度不能超過 50 字元。',
            'boolean'                 => '欄位格式錯誤 (必須為布林值)。',
        ];

        // 3. 執行驗證
        $validated = $request->validate($rules, $messages);

        $data = array_merge($validated, [
            'deleted'   => 0,                      // 預設未刪除
        ]);

        // 2. 寫入資料
        $medicine = Medicine::create($data);

        return response()->json([
            'status' => 'success',
            'message' => '藥品新增成功',
            'data' => $medicine
        ]);
    }

    public function update(Request $request, $id)
    {
        $medicine = Medicine::findOrFail($id);

        // 驗證 (跟 store 類似，但通常 ID 不給改)
        // 1. 定義驗證規則
        $rules = [
            'short_name'        => 'required|string|max:50',
            'chinese_name'      => 'required|string|max:255',
            'product_name'      => 'required|string|max:255', // 你改成的必填
            'scientific_name'   => 'required|string|max:255', // 你改成的必填
            'category_id'       => 'required|exists:categories,id', // 你改成的必填
            'unit_id'           => 'required|exists:units,id',      // 你改成的必填
            'provider_id'       => 'required|exists:providers,id',  // 你改成的必填
            'dosage_form_id'    => 'required|exists:dosage_forms,id', // 你改成的必填
            'inventory_control' => 'boolean',
            'need_packing'      => 'boolean',
            'immportant'        => 'boolean',
            'uses'              => 'nullable|string',
            'note'              => 'nullable|string',

            // 🔥 A液特殊驗證：雖然寫法比較長，但這是最安全的做法
            'na_k_ca' => [
                'nullable', // 先允許空值 (給非 A 液的藥品通過)
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->category_id) {
                        $category = Category::find($request->category_id);
                        // 如果分類名稱包含 'A液' 且欄位是空的 -> 報錯
                        if ($category && str_contains($category->name, 'A液') && empty($value)) {
                            $fail('當分類為 A 液時，「透析液成份 (Na/K/Ca)」為必填欄位。');
                        }
                    }
                },
            ],
        ];

        // 2. 定義中文錯誤訊息
        $messages = [
            // 必填 (Required) 錯誤
            'short_name.required'     => '「藥品簡碼」為必填欄位。',
            'chinese_name.required'   => '「中文名稱」為必填欄位。',
            'product_name.required'   => '「商品名/學名」為必填欄位。',
            'scientific_name'         => '「成分名/常用名」為必填欄位。',
            'category_id.required'    => '請選擇「分類」。',
            'unit_id.required'        => '請選擇「單位」。',
            'provider_id.required'    => '請選擇「廠牌/代理商」。',
            'dosage_form_id.required' => '請選擇「劑型」。',

            // 選項無效 (Exists) 錯誤 - 防止前端送不存在的 ID
            'category_id.exists'      => '所選的「分類」無效。',
            'unit_id.exists'          => '所選的「單位」無效。',
            'provider_id.exists'      => '所選的「廠牌/代理商」無效。',
            'dosage_form_id.exists'   => '所選的「劑型」無效。',

            // 格式錯誤
            'short_name.max'          => '「藥品簡碼」長度不能超過 50 字元。',
            'boolean'                 => '欄位格式錯誤 (必須為布林值)。',
        ];

        // 3. 執行驗證
        $validated = $request->validate($rules, $messages);

        $data = array_merge($validated, [
            'deleted'   => 0,                      // 預設未刪除
        ]);

        $medicine->update($data);

        return response()->json([
            'status' => 'success',
            'message' => '藥品更新成功',
            'data' => $medicine
        ]);
    }

    // 刪除設備 (軟刪除)
    public function destroy($id)
    {
        $item = Medicine::findOrFail($id);
        $item->update(['deleted' => 1]);

        return response()->json(['message' => '已刪除']);
    }
}
