<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationExecutionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',     // 病患 ID
        'rule_id',        // 關聯到 HealthEducationItem 的 ID
        'trigger_value',  // 觸發當時的檢驗數值 [cite: 55]
        'status',         // 狀態：1 (待推送), 2 (已發送), 3 (已讀), 4 (護理師已確認)
        'triggered_at',   // 邏輯觸發時間
        'check_time',        // 病患點擊閱讀時間
        'nurse_id',       // 執行確認的護理師 ID
        'nurse_note'      // 護理師介入後的備註
    ];

    /**
     * 關聯到衛教規則項目
     */
    public function educationItem()
    {
        return $this->belongsTo(HealthEducationItem::class, 'rule_id');
    }

    /**
     * 關聯到病患資料
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
