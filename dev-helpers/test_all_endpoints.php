<?php
require_once 'vendor/autoload.php';
$a = require_once 'bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$n = \App\Models\User::where('role_id',6)->first()->createToken('t')->plainTextToken;
$d = \App\Models\User::where('role_id',4)->first()->createToken('t')->plainTextToken;
$id = \App\Models\PatientCheck::first()->id;
$base = 'http://localhost:8000/api';

function t($l, $u, $tkn, $opts = []) {
    $c = curl_init($u);
    $d = [CURLOPT_HTTPHEADER=>["Authorization: Bearer $tkn"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5];
    curl_setopt_array($c, $d + $opts);
    $b = curl_exec($c);
    $ct = curl_getinfo($c, CURLINFO_CONTENT_TYPE);
    $co = curl_getinfo($c, CURLINFO_HTTP_CODE);
    curl_close($c);
    echo "$l: HTTP $co | Content-Type: $ct\n";
    $j = json_decode($b, true);
    if ($j && isset($j['errors'])) {
        echo "  Validation errors: " . json_encode($j['errors'], JSON_UNESCAPED_UNICODE) . "\n";
    } elseif ($j && isset($j['status']) && $j['status'] == 200) {
        echo "  ✅ Status 200\n";
        if (isset($j['dry_weight_a'])) echo "  dry_weight_a: " . $j['dry_weight_a'] . ", active: " . ($j['active']??'') . "\n";
        if (isset($j['vital_signs']['id'])) echo "  vital_signs.id: " . $j['vital_signs']['id'] . "\n";
        if (isset($j['vascular']['vessel'])) echo "  vascular: " . json_encode($j['vascular'], JSON_UNESCAPED_UNICODE) . "\n";
        if (isset($j['consciousness']['state'])) echo "  consciousness: " . json_encode($j['consciousness'], JSON_UNESCAPED_UNICODE) . "\n";
        if (isset($j['skin']['intact'])) echo "  skin: " . json_encode($j['skin'], JSON_UNESCAPED_UNICODE) . "\n";
        if (isset($j['deduction']['id'])) echo "  deduction.id: " . $j['deduction']['id'] . "\n";
    } elseif ($j && isset($j['monitoring'])) {
        echo "  monitoring slots: " . count($j['monitoring']) . "\n";
    } else {
        echo "  Response: " . json_encode($j, JSON_UNESCAPED_UNICODE) . "\n";
    }
    echo "\n";
}

echo "=== 1. N-016 GET dry-weight ===\n";
t('GET', "$base/patients/$id/dry-weight", $n);

echo "=== 2. N-008 POST vital-signs ===\n";
t('POST vital-signs', "$base/dialysis/$id/vital-signs", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['bp_systolic'=>120,'bp_diastolic'=>80,'pulse'=>72,'resp'=>18,'temp'=>36.5,'blood_sugar'=>100])]);

echo "=== 3. N-007 POST vascular ===\n";
t('POST vascular', "$base/dialysis/$id/assessments/vascular", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['access_type'=>'AVF','position'=>"R't",'assess_result'=>'正常','note'=>'測試'])]);

echo "=== 4. N-007 POST consciousness ===\n";
t('POST consciousness', "$base/dialysis/$id/assessments/consciousness", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['state'=>'清醒','note'=>''])]);

echo "=== 5. N-007 POST skin ===\n";
t('POST skin', "$base/dialysis/$id/assessments/skin", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['intact'=>'完整','note'=>''])]);

echo "=== 6. N-007 GET assessments (verify fields) ===\n";
t('GET assessments', "$base/dialysis/$id/assessments", $n);

echo "=== 7. N-010 POST deduction ===\n";
t('POST deduction', "$base/dialysis/$id/deductions", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['name'=>'點滴','weight'=>0.5])]);

echo "=== 8. N-011 GET monitoring ===\n";
t('GET monitoring', "$base/dialysis/$id/monitoring", $n);

echo "=== 9. N-016 POST updateDryWeight (doctor) ===\n";
t('POST doctor', "$base/dialysis/notify/dryWeight/$id", $d, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['dry_weight_a'=>60])]);

echo "=== 10. N-014 POST execute (nurse) ===\n";
t('POST execute', "$base/dialysis/$id/post-drugs/execute", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['drug_name'=>'Epogin','dose'=>4000,'route'=>'SC','adverse_reaction'=>'無'])]);

echo "=== 11. N-014 POST refuse (nurse) ===\n";
t('POST refuse', "$base/dialysis/$id/post-drugs/refuse", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['drug_name'=>'Epogin','reason'=>'病患拒絕','detail'=>''])]);

echo "=== 12. N-011 POST monitoring (nurse, dispose_id=2) ===\n";
t('POST monitoring h1', "$base/dialysis/$id/monitoring", $n, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode(['dispose_id'=>2,'HCDTTM'=>'2026-07-10 09:00:00','BDPS'=>142,'BDPD'=>86,'BDPL'=>72,'note'=>''])]);
