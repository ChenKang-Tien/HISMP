<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$n = \App\Models\User::where('role_id',6)->first();
$nt = $n->createToken('t')->plainTextToken;
$d = \App\Models\User::where('role_id',4)->first();
$dt = $d->createToken('t')->plainTextToken;
$id = \App\Models\PatientCheck::first()->id;
$url = 'http://localhost:8000/api';

echo "=== Role Test Results ===\n\n";

$ch = curl_init("$url/dialysis/notify/dryWeight/$id");
curl_setopt_array($ch, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['dry_weight_a'=>60]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt", 'Content-Type: application/json'], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($ch);
$h = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "Test 1 - Nurse calls doctor endpoint: HTTP $h " . ($h === 403 ? '✅ BLOCKED' : "❌ ($h)") . "\n";

$ch = curl_init("$url/patients/$id/dry-weight");
curl_setopt_array($ch, [CURLOPT_CUSTOMREQUEST=>'PUT', CURLOPT_POSTFIELDS=>json_encode(['active'=>'B']), CURLOPT_HTTPHEADER=>["Authorization: Bearer $dt", 'Content-Type: application/json'], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($ch);
$h = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "Test 2 - Doctor calls nurse endpoint: HTTP $h " . ($h === 403 ? '✅ BLOCKED' : "❌ ($h)") . "\n";

$ch = curl_init("$url/dialysis/notify/dryWeight/$id");
curl_setopt_array($ch, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['dry_weight_a'=>60]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $dt", 'Content-Type: application/json'], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
$r = curl_exec($ch);
$h = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "Test 3 - Doctor calls doctor endpoint: HTTP $h " . ($h === 200 ? '✅ ALLOWED' : "❌ ($h)") . "\n\n";

echo "=== Response bodies ===\n";
$ch = curl_init("$url/dialysis/notify/dryWeight/$id");
curl_setopt_array($ch, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['dry_weight_a'=>60]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $nt", 'Content-Type: application/json'], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
echo "Nurse->doctor: " . curl_exec($ch) . "\n"; curl_close($ch);

$ch = curl_init("$url/dialysis/notify/dryWeight/$id");
curl_setopt_array($ch, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['dry_weight_a'=>60]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $dt", 'Content-Type: application/json'], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
echo "Doctor->doctor: " . curl_exec($ch) . "\n"; curl_close($ch);
