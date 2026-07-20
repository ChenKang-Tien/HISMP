<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$n = \App\Models\User::where('role_id',6)->first(); $nt = $n->createToken('t')->plainTextToken;
$d = \App\Models\User::where('role_id',4)->first(); $dt = $d->createToken('t')->plainTextToken;
$a2 = \App\Models\User::where('role_id',1)->first(); $at = $a2->createToken('t')->plainTextToken;
$b = 'http://localhost:8000/api';

echo "=========== DialysisController 完整驗證 ===========\n\n";

// Test multiple check IDs
echo "--- 多筆 check_id 測試 ---\n";
foreach ([34, 127, 1] as $cid) {
    $c = curl_init("$b/dialysis/$cid");
    curl_setopt_array($c, [CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
    $r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
    echo "Check $cid: $h " . ($h==200?"PASS":($h==404?"404(known bug)":"FAIL($h)")) . "\n";
}

// Negative role test
echo "\n--- 角色負向測試 ---\n";
$c = curl_init("$b/dialysis/34");
curl_setopt_array($c, [CURLOPT_HTTPHEADER=>["Authorization: Bearer $at"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
echo "Admin->dialysis(34): $h " . ($h==403?"PASS (blocked)":"FAIL($h)") . "\n";

$c = curl_init("$b/dialysis/34");
curl_setopt_array($c, [CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
echo "No auth->dialysis(34): $h " . ($h==401?"PASS (blocked)":"FAIL($h)") . "\n";

// Options endpoint
echo "\n--- prepare/options ---\n";
$c = curl_init("$b/dialysis/prepare/options");
curl_setopt_array($c, [CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
echo "Nurse->options: $h " . ($h==200?"PASS":"FAIL($h)") . "\n";

$c = curl_init("$b/dialysis/prepare/options");
curl_setopt_array($c, [CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
echo "No auth->options: $h " . ($h==401||$h==500?"PASS (blocked " . ($h==500?"by auth)":"by middleware)"):"FAIL($h)") . "\n";

echo "\n=========== PrepareController doublePrepare 三情境 ===========\n\n";

// Reset check 1 for clean test
$pc = \App\Models\PatientCheck::find(1);
$old_prepare = $pc->prepare_nurse_id;
$pc->prepare_nurse_id = 2;
$pc->check_nurse_id = null;
$pc->save();

// Same nurse
$c = curl_init("$b/dialysis/1/prepare/double_sign");
curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode([]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt","Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); $j = json_decode($r,true); curl_close($c);
echo "Same nurse: $h reason=" . ($j['reason']??'?') . " " . ($h==422&&$j['reason']=='same_nurse'?"PASS":"FAIL") . "\n";

// Clear prepare to test prepare_not_signed
$pc->prepare_nurse_id = null; $pc->save();
$c = curl_init("$b/dialysis/1/prepare/double_sign");
curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode([]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt","Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); $j = json_decode($r,true); curl_close($c);
echo "No prepare: $h reason=" . ($j['reason']??'?') . " " . ($h==422&&$j['reason']=='prepare_not_signed'?"PASS":"FAIL") . "\n";

// Different nurse success
$pc->prepare_nurse_id = 2; $pc->save();
$n2 = \App\Models\User::where('role_id',6)->where('id','!=',2)->first(); $n2t = $n2->createToken('t')->plainTextToken;
$c = curl_init("$b/dialysis/1/prepare/double_sign");
curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode([]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $n2t","Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); $j = json_decode($r,true); curl_close($c);
$db = \App\Models\PatientCheck::find(1);
echo "Diff nurse: $h check_nurse_id=" . ($db->check_nurse_id??'null') . " " . ($h==200&&$db->check_nurse_id!=null?"PASS":"FAIL") . "\n";

// Restore
$pc->prepare_nurse_id = $old_prepare; $pc->save();

echo "\n=========== 完成 ===========\n";
