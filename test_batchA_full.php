<?php
require_once 'vendor/autoload.php';
$a = require_once 'bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$n = \App\Models\User::where('role_id',6)->first()->createToken('t')->plainTextToken;
$d = \App\Models\User::where('role_id',4)->first()->createToken('t')->plainTextToken;
$id = \App\Models\PatientCheck::first()->id;
$base = 'http://localhost:8000/api';

function t($l, $u, $t, $o = []) {
    $c = curl_init($u);
    $d = [CURLOPT_HTTPHEADER=>["Authorization: Bearer $t"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5];
    curl_setopt_array($c, $d + $o);
    $b = curl_exec($c);
    $ct = curl_getinfo($c, CURLINFO_CONTENT_TYPE);
    $co = curl_getinfo($c, CURLINFO_HTTP_CODE);
    curl_close($c);
    echo "$l: HTTP $co | Content-Type: $ct\n";
    $j = json_decode($b, true);
    if ($j) {
        $k = implode(', ', array_keys($j));
        echo "  Keys: $k\n";
        foreach (['dry_weight_a','active','vital_signs'=>null,'deduction'=>null] as $fk => $fv) {
            $key = is_string($fk) ? $fk : $fv;
            if (isset($j[$key])) echo "  $key: " . json_encode($j[$key], JSON_UNESCAPED_UNICODE) . "\n";
            if ($key === 'vital_signs' && isset($j['vital_signs'])) echo "  vs id: " . ($j['vital_signs']['id']??'null') . "\n";
        }
        if (isset($j['vascular']['vessel'])) echo "  vascular.vessel: " . json_encode($j['vascular']['vessel'], JSON_UNESCAPED_UNICODE) . "\n";
        if (isset($j['skin']['location'])) echo "  skin.location: " . json_encode($j['skin']['location'], JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "  RAW: " . substr($b, 0, 80) . "\n";
    }
    echo "\n";
}

echo "=== 1. N-016 GET dry-weight ===\n";
t('GET', "$base/patients/$id/dry-weight", $n);

echo "=== 2. N-016 PUT switchDryWeight (nurse) ===\n";
t('PUT nurse', "$base/patients/$id/dry-weight", $n, [CURLOPT_CUSTOMREQUEST=>'PUT', CURLOPT_POSTFIELDS=>json_encode(['active'=>'B'])]);

echo "=== 3. N-016 POST updateDryWeight (doctor) ===\n";
t('POST doctor', "$base/dialysis/notify/dryWeight/$id", $d, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['dry_weight_a'=>60])]);

echo "=== 4. N-008 POST vital-signs ===\n";
t('POST', "$base/dialysis/$id/vital-signs", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['bp_systolic'=>120,'bp_diastolic'=>80,'pulse'=>72,'resp'=>18,'temp'=>36.5,'blood_sugar'=>100])]);

echo "=== 5. N-007 POST vascular ===\n";
t('POST vascular', "$base/dialysis/$id/assessments/vascular", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['access_type'=>'AVF','permcath_length'=>null,'position'=>"R't",'assess_result'=>'正常','note'=>''])]);

echo "=== 6. N-007 POST consciousness ===\n";
t('POST consciousness', "$base/dialysis/$id/assessments/consciousness", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['state'=>'清醒','note'=>''])]);

echo "=== 7. N-007 POST skin ===\n";
t('POST skin', "$base/dialysis/$id/assessments/skin", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['intact'=>'完整','location'=>null,'size'=>null,'note'=>''])]);

echo "=== 8. N-007 GET assessments ===\n";
t('GET', "$base/dialysis/$id/assessments", $n);

echo "=== 9. N-010 POST deduction ===\n";
t('POST deduction', "$base/dialysis/$id/deductions", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['name'=>'點滴','weight'=>0.5])]);
