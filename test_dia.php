<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$n = \App\Models\User::where('role_id',6)->first(); $nt = $n->createToken('t')->plainTextToken;
$d = \App\Models\User::where('role_id',4)->first(); $dt = $d->createToken('t')->plainTextToken;
$b = 'http://localhost:8000/api';

echo "=== DialysisController 修正驗證 ===\n\n";

// Test 1: getDialysis with check 34 (valid data)
echo "--- getDialysis(34) ---\n";
$c = curl_init("$b/dialysis/34");
curl_setopt_array($c, [CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); $j = json_decode($r, true); curl_close($c);
echo "Nurse->dialysis(34): $h " . ($h==200?"PASS":"FAIL($h)") . "\n";
echo "  CT: " . (isset($j['status'])?'application/json ✅':'WARNING') . "\n";
echo "  needAddHct: " . ($j['patientCheck']['needAddHct']??'?') . "\n";

// Test 2: Doctor access (should also pass)
echo "\n--- Doctor access ---\n";
$c = curl_init("$b/dialysis/34");
curl_setopt_array($c, [CURLOPT_HTTPHEADER=>["Authorization: Bearer $dt"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
echo "Doctor->dialysis(34): $h " . ($h==200?"PASS":"FAIL($h)") . "\n";

// Test 3: prepare/options (should require auth now)
echo "\n--- prepare/options (public access test) ---\n";
$c = curl_init("$b/dialysis/prepare/options");
curl_setopt_array($c, [CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
echo "No auth->options: $h " . ($h==401||$h==403?"PASS (blocked)":"FAIL($h)") . "\n";

echo "\n=== 完成 ===\n";
