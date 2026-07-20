<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$n = \App\Models\User::where('role_id',6)->first(); $nt = $n->createToken('t')->plainTextToken;
$d = \App\Models\User::where('role_id',4)->first(); $dt = $d->createToken('t')->plainTextToken;
$b = 'http://localhost:8000/api';

echo "=== doublePrepare 三種情境完整測試 ===\n\n";

// Check current state of check_id=1
$pc = \App\Models\PatientCheck::find(1);
echo "Check 1: prepare_nurse_id=" . ($pc->prepare_nurse_id ?? 'null') . " check_nurse_id=" . ($pc->check_nurse_id ?? 'null') . "\n\n";

// Scenario A: Same nurse double = 422 same_nurse
echo "--- A: 同一人雙簽 (prepare_nurse_id = nurse_id) ---\n";
$c = curl_init("$b/dialysis/1/prepare/double_sign");
curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode([]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt","Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); $j = json_decode($r,true); curl_close($c);
echo "HTTP $h | status=" . ($j['status']??'?') . " reason=" . ($j['reason']??'?') . " msg=" . ($j['message']??'?') . " " . ($h==422&&$j['reason']=='same_nurse'?'PASS':'FAIL') . "\n\n";

// Scenario B: prepare_nurse_id = null = 422 prepare_not_signed
echo "--- B: 整備未簽 (prepare_nurse_id = null) ---\n";
$pc2 = \App\Models\PatientCheck::find(1);
$old_prepare = $pc2->prepare_nurse_id;
$pc2->prepare_nurse_id = null;
$pc2->check_nurse_id = null;
$pc2->save();

$c = curl_init("$b/dialysis/1/prepare/double_sign");
curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode([]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt","Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); $j = json_decode($r,true); curl_close($c);
echo "HTTP $h | status=" . ($j['status']??'?') . " reason=" . ($j['reason']??'?') . " msg=" . ($j['message']??'?') . " " . ($h==422&&$j['reason']=='prepare_not_signed'?'PASS':'FAIL') . "\n\n";

// Restore prepare_nurse_id for scenario C
$pc2->prepare_nurse_id = $old_prepare;
$pc2->save();

// Scenario C: Different nurse double = 200 success
echo "--- C: 不同護理師雙簽 (成功情境) ---\n";
// Need a different nurse (different from the one who signed prepare_nurse_id=2)
$n2 = \App\Models\User::where('role_id',6)->where('id','!=',$old_prepare)->first();
if ($n2) {
    $n2t = $n2->createToken('t')->plainTextToken;
    echo "Using nurse id=" . $n2->id . " (different from prepare_nurse_id=" . $old_prepare . ")\n";
    $c = curl_init("$b/dialysis/1/prepare/double_sign");
    curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode([]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $n2t","Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
    $r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); $j = json_decode($r,true); curl_close($c);
    $db = \App\Models\PatientCheck::find(1);
    echo "HTTP $h | status=" . ($j['status']??'?') . " check_nurse_id now=" . ($db->check_nurse_id??'null') . " " . ($h==200&&$db->check_nurse_id==$n2->id?'PASS':'FAIL') . "\n";
} else {
    echo "No second nurse found\n";
}

echo "\n=== 完成 ===\n";
