<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$d = \App\Models\User::where('role_id',4)->first(); $dt = $d->createToken('t')->plainTextToken;
$c = curl_init('http://localhost:8000/api/doctor/patients');
curl_setopt_array($c,[CURLOPT_HTTPHEADER=>["Authorization: Bearer $dt"],CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c);$h = curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);
echo "Test 1 - Doctor->patients (all shifts): $h\n";
if ($h === 200) {
    $j = json_decode($r,true);
    echo "  Patients: " . count($j['patients']??[]) . "\n";
    if (count($j['patients']??[]) > 0) {
        $p = $j['patients'][0];
        echo "  First: name={$p['name']} shift={$p['shift']} bed={$p['bed_no']} status={$p['status']}\n";
    }
} else {
    echo "  Error: " . (json_decode($r,true)['message']??substr($r,0,100)) . "\n";
}
// Test with shift filter
$c = curl_init('http://localhost:8000/api/doctor/patients?shift=0');
curl_setopt_array($c,[CURLOPT_HTTPHEADER=>["Authorization: Bearer $dt"],CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c);$h = curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);
echo "\nTest 2 - Doctor->patients (shift=0): $h\n";
if ($h === 200) {
    $j = json_decode($r,true); echo "  Patients: " . count($j['patients']??[]) . "\n";
} else { echo "  Error: " . (json_decode($r,true)['message']??substr($r,0,100)) . "\n"; }
// Test show
$c = curl_init('http://localhost:8000/api/doctor/patients/1');
curl_setopt_array($c,[CURLOPT_HTTPHEADER=>["Authorization: Bearer $dt"],CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>5]);
$r = curl_exec($c);$h = curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);
echo "\nTest 3 - Doctor->patients/1 (show): $h\n";
if ($h===200) { $j=json_decode($r,true); echo "  Name: {$j['patient']['name']}\n"; }
else { echo "  Error: " . (json_decode($r,true)['message']??substr($r,0,100)) . "\n"; }
