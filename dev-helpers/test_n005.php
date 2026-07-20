<?php
require_once 'vendor/autoload.php';
$a = require_once 'bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$n = \App\Models\User::where('role_id',6)->first();
$nt = $n->createToken('t')->plainTextToken;
$id = \App\Models\PatientCheck::first()->id;
$base = 'http://localhost:8000/api';

function callOffSign($label, $url, $token) {
    $c = curl_init($url);
    curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode([]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $token", "Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
    $r = curl_exec($c);
    $h = curl_getinfo($c, CURLINFO_HTTP_CODE);
    curl_close($c);
    $j = json_decode($r, true);
    $failedCount = isset($j['incomplete_items']) ? count($j['incomplete_items']) : 0;
    $passed = isset($j['off_signed']) && $j['off_signed'] === true;
    echo "$label: HTTP $h | " . ($passed ? "✅ ALL PASSED" : "❌ $failedCount conditions failed") . "\n";
    if ($failedCount > 0) {
        foreach ($j['incomplete_items'] as $item) {
            echo "   - [{$item['condition']}] {$item['label']}" . ($item['detail'] ? " (" . implode(', ', $item['detail']) . ")" : "") . "\n";
        }
    }
    echo "\n";
}

echo "═══════════════════════════════════════\n";
echo "   N-005 Off-Sign Validation Test Suite\n";
echo "═══════════════════════════════════════\n\n";

echo "=== Test 1: Real data (should fail ~4 conditions) ===\n";
callOffSign("Test 1", "$base/dialysis/$id/adjustment/offSign", $nt);

echo "=== Test 2: Doctor calls offSign (expect 403) ===\n";
$d = \App\Models\User::where('role_id',4)->first();
$dt = $d->createToken('t')->plainTextToken;
$c = curl_init("$base/dialysis/$id/adjustment/offSign");
curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode([]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $dt", "Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c);
$h = curl_getinfo($c, CURLINFO_HTTP_CODE);
curl_close($c);
echo "Doctor calls offSign: HTTP $h " . ($h === 403 ? "✅ BLOCKED" : "❌") . "\n\n";

echo "=== N-005 offSign() 完整程式碼請見 AdjustmentController.php ===\n";
echo "commit: 805e11c\n";
