<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\Bed;
use App\Models\PatientReservation;
use App\Models\PatientCheck;
use App\Models\MachineBed;

class PatientCheckSeeder extends Seeder
{
    public function run()
    {
        $patients = [
            ['name' => '薛玉鳳', 'mr' => 'MR9876543', 'bed' => '01'],
            ['name' => '林*芳', 'mr' => 'MR223344', 'bed' => '02'],
            ['name' => '陳小美', 'mr' => 'MR-N-09', 'bed' => '09'],
            ['name' => '黃大偉', 'mr' => 'MR-E-08', 'bed' => '08']
        ];

        foreach ($patients as $p) {
            $patient = Patient::firstOrCreate(
                ['medical_record_no' => $p['mr']],
                ['name' => $p['name'], 'id_num' => 'A123456789']
            );

            $bed = Bed::firstOrCreate(['bed_no' => $p['bed']]);
            $machineBed = MachineBed::firstOrCreate(['bed_id' => $bed->id]);

            // 確保 reservation 關聯到正確的 patient_id，而非 0
            $res = PatientReservation::updateOrCreate(
                ['patient_id' => $patient->id, 'date' => date('Y-m-d')],
                ['machine_bed_id' => $machineBed->id, 'status' => 0, 'morning_noon_night' => 1]
            );

            PatientCheck::firstOrCreate(
                [
                    'patient_reservation_id' => $res->id,
                    'date' => date('Y-m-d')
                ],
                [
                    'status' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
