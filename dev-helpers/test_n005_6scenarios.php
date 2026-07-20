<?php
require_once 'vendor/autoload.php';
$a = require_once 'bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PatientCheck;
use App\Models\PatientCareSign;
use App\Models\PatientSupplyCheck;
use App\Models\PatientMidNurseRecord;
use App\Models\DoctorApMedicine;
use App\Models\User;

$nurse = User::where('role_id',6)->first();
$nt = $nurse->createToken('t')->plainTextToken;
$checkId = PatientCheck::first()->id;
$base = 'http://localhost:8000/api';
$now = date('Y-m-d H:i:s');

function callOffSign($label, $checkId, $token, $base) {
    $c = curl_init("$base/dialysis/$checkId/adjustment/offSign");
    curl_setopt_array($c, [CURLOPT_POST=>1, CURLOPT_POSTFIELDS=>json_encode([]), CURLOPT_HTTPHEADER=>["Authorization: Bearer $token", "Content-Type: application/json"], CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>5]);
    $r = curl_exec($c);
    $h = curl_getinfo($c, CURLINFO_HTTP_CODE);
    curl_close($c);
    $j = json_decode($r, true);
    $failed = isset($j['incomplete_items']) ? count($j['incomplete_items']) : 0;
    $passed = isset($j['off_signed']) && $j['off_signed'] === true;
    $status = $passed ? "200 ALL PASSED" : ($h == 422 ? "422 ($failed items)" : "HTTP $h");
    echo str_pad($label, 40) . " $status\n";
    if ($failed > 0) {
        foreach ($j['incomplete_items'] as $item) {
            echo "  - [{$item['condition']}] {$item['label']}" . ($item['detail'] ? " (".implode(', ', $item['detail']).")" : "") . "\n";
        }
    }
    if ($passed) echo "  off_signed_at: {$j['off_signed_at']}\n";
    echo "\n";
}

// Clean up
PatientCareSign::where('patient_check_id', $checkId)->delete();
PatientSupplyCheck::where('patient_check_id', $checkId)->delete();
PatientMidNurseRecord::where('patient_check_id', $checkId)->delete();
DoctorApMedicine::where('patient_check_id', $checkId)->delete();

echo "=== N-005 OffSign: 6-Scenario Test ===\n\n";

// Helper: set all 5 conditions to pass
function allPass($checkId, $nurse, $now) {
    $required = ['pre','h1','h2','h3','h4','post_lying','post_sitting'];
    foreach ($required as $slot) {
        PatientCareSign::updateOrCreate(['patient_check_id'=>$checkId,'time_slot'=>$slot,'nurse_id'=>$nurse->id]);
    }
    PatientSupplyCheck::updateOrCreate(['patient_check_id'=>$checkId,'confirmed'=>true,'confirmed_by'=>$nurse->id,'confirmed_at'=>$now]);
    PatientMidNurseRecord::updateOrCreate(['patient_check_id'=>$checkId,'time'=>$now,'nurse_id'=>$nurse->id,'nurse_record_auxiliary_str'=>'test']);
    DoctorApMedicine::where('patient_check_id', $checkId)->delete();
    $pc = PatientCheck::find($checkId);
    $pc->prepare_nurse_id = $nurse->id;
    $pc->check_nurse_id = $nurse->id;
    $pc->save();
}

// Scenario 1: Only prepare fails
allPass($checkId, $nurse, $now);
$pc = PatientCheck::find($checkId);
$pc->prepare_nurse_id = null;
$pc->check_nurse_id = null;
$pc->save();
echo "S1: Only prepare fails\n";
callOffSign("1. Only prepare", $checkId, $nt, $base);

// Scenario 2: Only care sign fails
allPass($checkId, $nurse, $now);
PatientCareSign::where('patient_check_id', $checkId)->delete();
echo "S2: Only care sign fails\n";
callOffSign("2. Only care_sign", $checkId, $nt, $base);

// Scenario 3: Only drug execution fails (nurse_status=0 pending)
allPass($checkId, $nurse, $now);
$order = DoctorApMedicine::create([
    'patient_check_id' => $checkId,
    'doctor_id' => User::where('role_id',4)->first()->id,
    'nurse_status' => 0,
    'doctor_status' => 1,
    'time' => $now,
    'doctor_so_item_id' => 1,
'medicine_id' => 1,    'isLong' => 0,    'medicine' => 'Test',    'route_id' => '1',    'frequency_id' => 1,    'amount' => 1,    'days' => 1,    'total' => 1,    'deleted' => 0,
]);
echo "S3: Only drug execution fails (nurse_status=0)\n";
callOffSign("3. Only drug_exec", $checkId, $nt, $base);
$order->delete();

// Scenario 4: Only supply check fails
allPass($checkId, $nurse, $now);
PatientSupplyCheck::where('patient_check_id', $checkId)->delete();
echo "S4: Only supply check fails\n";
callOffSign("4. Only supply_check", $checkId, $nt, $base);

// Scenario 5: Only nurse record fails
allPass($checkId, $nurse, $now);
PatientMidNurseRecord::where('patient_check_id', $checkId)->delete();
echo "S5: Only nurse record fails\n";
callOffSign("5. Only nurse_record", $checkId, $nt, $base);

// Scenario 6: All pass
allPass($checkId, $nurse, $now);
$pc = PatientCheck::find($checkId);
$oldEnd = $pc->care_end_nurse_id;
$pc->care_end_nurse_id = null;
$pc->save();
echo "S6: All 5 conditions satisfied\n";
callOffSign("6. ALL PASS", $checkId, $nt, $base);

$after = PatientCheck::find($checkId);
echo "DB: care_end_nurse_id = " . ($after->care_end_nurse_id ?? 'null') . " " . ($after->care_end_nurse_id == $nurse->id ? "✅" : "❌") . "\n";
$after->care_end_nurse_id = $oldEnd;
$after->save();

echo "\n=== N-005 OffSign: ALL 6 SCENARIOS COMPLETE ===\n";
