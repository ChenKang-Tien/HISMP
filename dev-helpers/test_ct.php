<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$n = \App\Models\User::where('role_id',6)->first();
$nt = $n->createToken('t')->plainTextToken;
$b = 'http://localhost:8000/api';

function t($l, $u, $t) {
    $c = curl_init($u);
    curl_setopt_array($c, [CURLOPT_HTTPHEADER=>["Authorization: Bearer $t"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
    $r = curl_exec($c);
    $h = curl_getinfo($c, CURLINFO_HTTP_CODE);
    curl_close($c);
    echo str_pad(substr($l,0,54), 55) . " $h " . ($h == 200 ? "PASS" : ($h == 0 ? "NO_CONNECT" : "FAIL($h)")) . "\n";
    if ($h == 200) {
        $j = json_decode($r, true);
        echo "  Content-Type: " . (isset($j['status']) ? "application/json" : "WARNING") . "\n";
    }
    if ($h >= 500) {
        $j = json_decode($r, true);
        echo "  Error: " . substr($j['message'] ?? $r, 0, 80) . "\n";
    }
}

echo "=== Content-Type修正後 迴歸測試 ===\n\n";

echo "--- 批次A已確認 ---\n";
t("N-016 GET dry-weight", "$b/patients/1/dry-weight", $nt);
t("N-008 POST vital-signs", "$b/dialysis/1/vital-signs", $nt);

echo "\n--- 受影響Controllers 測試 ---\n";
t("NurseRecord GET records/1", "$b/app/records/1", $nt);
t("Patient GET patients/search", "$b/patients/search?name=test", $nt);
t("Inspection GET 1/inspections", "$b/patients/1/inspections", $nt);
t("Reservation GET reservations", "$b/reservations", $nt);
t("Monitoring GET 1/monitoring", "$b/dialysis/1/monitoring", $nt);

echo "\n--- Prepare endpoints ---\n";
t("Prepare GET dialysis/1/prepare", "$b/dialysis/1/prepare", $nt);

echo "\n--- Dialysis GET 1 基本查詢 ---\n";
t("Dialysis GET dialysis/1", "$b/dialysis/1", $nt);

echo "\n\n=== 測試完成 ===\n";
