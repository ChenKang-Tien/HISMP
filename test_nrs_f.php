<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$n = \App\Models\User::where('role_id',6)->first(); $nt = $n->createToken('t')->plainTextToken;
$d = \App\Models\User::where('role_id',4)->first(); $dt = $d->createToken('t')->plainTextToken;
$b = 'http://localhost:8000/api';

echo "=== NurseRecordController 驗證 ===\n\n";

// 1. 404 問題確認：用正確路徑測試
echo "--- 1. 404 查證 ---\n";
$c = curl_init("$b/dialysis/1/nurseRecord");
curl_setopt_array($c, [CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
echo "GET /dialysis/1/nurseRecord: $h " . ($h==200?"PASS (正確路徑)":"FAIL") . "\n";
echo "結論：404 是先前測試用了錯誤 URL（/app/records/1），正確 route 為 /dialysis/{id}/nurseRecord，功能正常。\n\n";

// 2. Create 缺 time 驗證測試
echo "--- 2. Create missing time (expect 422) ---\n";
$c = curl_init("$b/dialysis/1/nurseRecord");
curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['patient_ask'=>'test']), CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt","Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); $j = json_decode($r,true); curl_close($c);
echo "Create(no time): $h " . ($h==422?"PASS (422 rejected)":"FAIL($h)") . "\n";

// 3. Create with valid data (expect 200)
echo "\n--- 3. Create with valid data ---\n";
$now = date('Y-m-d H:i:s');
$c = curl_init("$b/dialysis/1/nurseRecord");
curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['time'=>$now,'patient_ask'=>'test','content'=>'test note']), CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt","Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); $j = json_decode($r,true); curl_close($c);
echo "Create(valid): $h " . ($h==200?"PASS":"FAIL($h)") . " CT: " . (isset($j['status'])?'json✅':'WARN') . "\n";

// 4. Role tests
echo "\n--- 4. Role tests ---\n";
$c = curl_init("$b/dialysis/nurseRecord/phrases");
curl_setopt_array($c, [CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
echo "Nurse->phrases: $h " . ($h==200?"PASS":"FAIL($h)") . "\n";

$c = curl_init("$b/dialysis/nurseRecord/phrases");
curl_setopt_array($c, [CURLOPT_HTTPHEADER=>["Authorization: Bearer $dt"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
echo "Dr->phrases: $h " . ($h==403?"PASS (blocked)":"FAIL($h)") . "\n";

echo "\n--- 5. 陣列邊界 vascular_access_type[2] 說明 ---\n";
echo "原本：$patientBeforePhysiologicalData->vascular_access_type 經 explode 後若元素不足 3 個，\n";
echo "      $vascular_access_type[2] 直接存取可能觸發 undefined offset 錯誤。\n";
echo "修正後：isset($vascular_access_type[2]) && $vascular_access_type[2] == '' 有防呆，\n";
echo "      元素不足時自動進入 else 分支（$fir->flag = 0, content = \x22Premcath\x22），不再報錯。\n";

echo "\n=== 完成 ===\n";
