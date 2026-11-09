# 泰安診所 HISMP - 四小時監控總表 (Monitoring Grid) 資料撈取與計算規範

依據 `dev-helpers/總表撈資料.txt` 完整 435 行原始業務邏輯解析歸納如下：

---

## 1. 核心資料來源 (Database Models)

| 資料表 / Model | 用途 | 關鍵過濾條件 |
| :--- | :--- | :--- |
| `patient_checks` (`PatientCheck`) | 該次透析主紀錄 | `id = $check_id` |
| `patient_before_preparations` | 透前準備參數 | `initial_priming` (總量 $total), `duration` (透析總分鐘數 $uftm) |
| `patient_mid_dialysis_record_news` | 透析機自動傳輸之即時數據 | `patient_check_id`, `VEPS > 0`, `BLDF is not null` |
| `patient_dialysis_manual_records` | 手動加測與護理師抄表紀錄 | `patient_check_id`, `deleted = 0` |
| `patient_mid_bp_p_datas` | 透析三大血壓紀錄點 | `dispose_id = 1` (透前), `dispose_id = 2` (透中), `dispose_id = 3` (透後) |
| `patient_dialysis_machine_longs/shorts` | 醫囑機器常態/臨時設定 | 9: HFI, 10: Initial Heparin, 11: Priming, 12: Maintain Heparin |

---

## 2. 數值單位轉換規則 (Unit Transformation)

在讀取到機台原始數據後，必須依以下公式進行轉換：

1. **透液溫度 (`DLTP`)**：`DLTP = DLTP / 10` (例：365 → 36.5 °C)
2. **傳導度 (`CDCT`)**：`CDCT = CDCT / 4` (例：560 → 140 mS/cm)
3. **脫水速率 (`UFRA`)**：`UFRA = UFRA / 1000` (例：800 → 0.8 L/hr)
4. **已脫水量 (`UF`)**：`UF = round(UF / 1000, 2)` (例：1600 → 1.60 L)
5. **Heparin 殘量 (`HPCV`)**：`HPCV = round($total - (HPCV / 10), 1)`

---

## 3. 每小時時段判定與取樣邏輯

### (1) 時段分割（以透析時間 $uftm，通常為 240 分鐘 / 4 小時為例）
- 迴圈：`for ($i = $uftm; $i > 0; $i -= 60)`
- 條件：
  - `UFTM > 0`
  - `UFTM < $i` 且 `UFTM >= $i - 60`
  - 排序：`orderBy('HCDTTM', 'asc')->first()`

### (2) 血壓資料關聯（獨立最接近點）
- 機台記錄往往每分鐘或連續送出，血壓則是每小時手動或定時量測。
- 以該小時機台數據時間 `HCDTTM` 為基準，在前後 5 ~ 30 分鐘內尋找最接近且包含 `BDPS / BDPD` 的紀錄：
  ```sql
  WHERE patient_check_id = ?
    AND BDPS IS NOT NULL
    AND HCDTTM BETWEEN [HCDTTM - 5min] AND [HCDTTM + 5min]
  ORDER BY ABS(TIMESTAMPDIFF(SECOND, HCDTTM, target_time)) ASC
  ```

---

## 4. 機台臨床評估預設旗標 (Clinical Checks)

每筆抄表紀錄對應以下護理核對欄位：
- `dispose_id`：處置 ID（預設 0: 無特殊處置）
- `ak_id`：人工腎臟 AK 凝血狀態（預設 0: Clear / 正常）
- `ns_value`：沖生理食鹽水 N/S 劑量 (ml)
- `line_fix`：管路固定狀態（預設 1: 固定良好）
- `pinhole_blood`：穿刺針孔滲血狀態（預設 0: 無滲血）

---

## 5. 透析結束行 (END UF)

當 `patientCheck->status == 3` (透析完成) 或 `5` 時：
- 於表格最後追加一行 `END UF`。
- `VEPS = 'END UF'`
- `UF = round(最後最大UF / 1000, 1)`
- 其他數值留空。
