<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$n = \App\Models\User::where('role_id',6)->first();
$nt = $n->createToken('t')->plainTextToken;
$b = 'http://localhost:8000/api';

// Test 1: doublePrepare same nurse → expect 422
$c = curl_init("$b/dialysis/1/prepare/double_sign");
curl_setopt_array($c,[CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>json_encode([]),CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt","Content-Type: application/json"],CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c);$h = curl_getinfo($c,CURLINFO_HTTP_CODE);$j = json_decode($r,true);curl_close($c);
echo "Test 1 - same nurse double: $h " . ($h==422?"PASS":"FAIL($h)") . "\n";
echo "  reason: " . ($j['reason']??'N/A') . "\n";
echo "  Content-Type: " . (isset($j['status'])?'application/json ✅':'WARNING') . "\n";

// Test 2: Nurse calls prepare GET with valid check → should work
$pc = \App\Models\PatientBeforePreparation::select('patient_check_id')->first();
if ($pc) {
    $cid = $pc->patient_check_id;
    $c = curl_init("$b/dialysis/$cid/prepare/");
    curl_setopt_array($c,[CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt"],CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>5]);
    $r = curl_exec($c);$h = curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);
    echo "Test 2 - prepare GET (valid): $h " . ($h==200?"PASS":"FAIL($h)") . "\n";
}

// Test 3: Doctor calls prepare → expect 403
$d = \App\Models\User::where('role_id',4)->first();
$dt = $d->createToken('t')->plainTextToken;
$c = curl_init("$b/dialysis/1/prepare/");
curl_setopt_array($c,[CURLOPT_HTTPHEADER=>["Authorization: Bearer $dt"],CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c);$h = curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);
echo "Test 3 - doctor calls prepare: $h " . ($h==403?"PASS":"FAIL($h)") . "\n";
