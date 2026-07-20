<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Create a patient check record for today's test
$today = date('Y-m-d');
$pc = \App\Models\PatientCheck::where('date', $today)->first();
if (!$pc) {
    // Find first active patient
    $pt = \App\Models\Patient::whereNull('deleted')->orWhere('deleted', 0)->where('id', '!=', 0)->first();
    if ($pt) {
        // Create reservation for today
        $res = \App\Models\PatientReservation::where('patient_id', $pt->id)->where('date', $today)->first();
        if (!$res) {
            $res = \App\Models\PatientReservation::create([
                'patient_id' => $pt->id, 'date' => $today,
                'morning_noon_night' => '午班', 'status' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        // Create check record
        $pc = \App\Models\PatientCheck::create([
            'patient_reservation_id' => $res->id,
            'date' => $today, 'status' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
echo "Done: " . ($pc ? "check_id={$pc->id}" : "FAILED") . "\n";
echo "Patient: " . ($pc->patient_reservation->patient->name ?? 'N/A') . "\n";
