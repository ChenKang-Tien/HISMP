<?php

// This script tests role middleware by making cross-role API calls

// Use artisan's built-in test capabilities
// Create tokens and curl from within the app

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get nurse and doctor tokens
$nurse = \App\Models\User::where('role_id', 6)->first();
$nurseToken = $nurse->createToken('role-test')->plainTextToken;

$doctor = \App\Models\User::where('role_id', 4)->first();
$doctorToken = $doctor->createToken('role-test')->plainTextToken;

// Get a valid patient_check_id for testing
$checkId = \App\Models\PatientCheck::first()->id;

echo "=== Role Middleware Test ===\n\n";

echo "Test 1: Nurse calls doctor endpoint (POST dryWeight)\n";
echo "Expect: HTTP 403 Forbidden\n";

$ch = curl_init('http://localhost/api/dialysis/notify/dryWeight/' . $checkId);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['dry_weight_a' => 60]),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $nurseToken,
        'Content-Type: application/json',
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "Result: HTTP $code " . ($code === 403 ? '✅ BLOCKED as expected' : '❌ UNEXPECTED') . "\n";
echo "Response: $resp\n\n";

echo "Test 2: Doctor calls nurse endpoint (PUT switchDryWeight)\n";
echo "Expect: HTTP 403 Forbidden\n";

$ch = curl_init('http://localhost/api/patients/' . $checkId . '/dry-weight');
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS => json_encode(['active' => 'B']),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $doctorToken,
        'Content-Type: application/json',
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "Result: HTTP $code " . ($code === 403 ? '✅ BLOCKED as expected' : '❌ UNEXPECTED') . "\n";
echo "Response: $resp\n\n";

echo "Test 3: Doctor calls doctor endpoint (should pass)\n";
echo "Expect: HTTP 200\n";

$ch = curl_init('http://localhost/api/dialysis/notify/dryWeight/' . $checkId);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['dry_weight_a' => 60]),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $doctorToken,
        'Content-Type: application/json',
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "Result: HTTP $code " . ($code === 200 ? '✅ ALLOWED as expected' : '❌ UNEXPECTED') . "\n";
echo "Response: $resp\n";
