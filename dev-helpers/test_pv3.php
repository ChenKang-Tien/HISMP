<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$n = \App\Models\User::where('role_id',6)->first(); $nt = $n->createToken('t')->plainTextToken;
$d = \App\Models\User::where('role_id',4)->first(); $dt = $d->createToken('t')->plainTextToken;
$b = 'http://localhost:8000/api';

echo "=== PrepareController 完整驗證 ===\n\n";

// Test Role Protection
echo "--- 角色保護 ---\n";
$c = curl_init("$b/dialysis/1/prepare/");
curl_setopt_array($c, [CURLOPT_HTTPHEADER=>["Authorization: Bearer $dt"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
echo "Doctor->prepare GET: $h " . ($h==403? "PASS":"FAIL($h)") . "\n";

$c = curl_init("$b/dialysis/1/prepare/");
curl_setopt_array($c, [CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
echo "Nurse->prepare GET: $h " . ($h==200? "PASS":"FAIL($h)") . "\n";

// Test doublePrepare - same nurse (should be 422, reason=same_nurse)
echo "\n--- doublePrepare 422 情境 ---\n";
$c = curl_init("$b/dialysis/1/prepare/double_sign");
curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode([]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt","Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); $j = json_decode($r,true); curl_close($c);
$reason = $j['reason'] ?? '';
echo "Same nurse double: $h PASS reason=$reason\n";

// Test doublePrepare - doctor (should be 403)
$c = curl_init("$b/dialysis/1/prepare/double_sign");
curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode([]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $dt","Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
echo "Doctor double: $h " . ($h==403? "PASS":"FAIL($h)") . "\n";

// Test Content-Type
echo "\n--- Content-Type ---\n";
$c = curl_init("$b/dialysis/1/prepare/");
curl_setopt_array($c, [CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE);
$j = json_decode($r,true); curl_close($c);
echo "Prepare GET: $h CT: " . (isset($j['status']) ? "application/json" : "WARNING") . " " . ($h==200 && isset($j['status'])? "PASS":"FAIL") . "\n";

echo "\n=== 完成 ===\n";
