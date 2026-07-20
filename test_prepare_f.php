<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$n = \App\Models\User::where('role_id',6)->first();
$nt = $n->createToken('t')->plainTextToken;
$d = \App\Models\User::where('role_id',4)->first();
$dt = $d->createToken('t')->plainTextToken;
$b = 'http://localhost:8000/api';

function t($l, $u, $m, $t, $d, $e) {
    $c = curl_init($u);
    $opts = [CURLOPT_HTTPHEADER=>["Authorization: Bearer $t","Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5];
    if ($m == 'POST') $opts[CURLOPT_POST] = 1;
    if ($d) $opts[CURLOPT_POSTFIELDS] = json_encode($d);
    curl_setopt_array($c, $opts);
    $r = curl_exec($c); $h = curl_getinfo($c, CURLINFO_HTTP_CODE); $j = json_decode($r,true); curl_close($c);
    $pass = ($h == $e || ($e == 422 && $h == 422));
    echo str_pad(substr($l,0,45), 46) . " $h " . ($pass ? "PASS" : "FAIL($e)") . ($j ? " [{$j['reason'] ?? $j['message'] ?? 'OK'}]" : "") . "\n";
}

echo "=== PrepareController 修正後驗證 ===\n\n";
echo "--- 角色保護 ---\n";
t("Doctor->prepare GET", "$b/dialysis/1/prepare/", 'GET', $dt, null, 403);
t("Nurse->prepare GET", "$b/dialysis/1/prepare/", 'GET', $nt, null, 200);

echo "\n--- doublePrepare ---\n";
t("Same nurse double", "$b/dialysis/1/prepare/double_sign", 'POST', $nt, [], 422);
t("Doctor double", "$b/dialysis/1/prepare/double_sign", 'POST', $dt, [], 403);

echo "\n--- Content-Type ---\n";
$c = curl_init("$b/dialysis/1/prepare/");
curl_setopt_array($c,[CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt"],CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c); $h = curl_getinfo($c,CURLINFO_HTTP_CODE); curl_close($c);
$j = json_decode($r,true);
echo "Prepare GET: $h " . ($h==200?"PASS":"FAIL") . " CT: " . (isset($j['status'])?'application/json ✅':'WARNING') . "\n";
