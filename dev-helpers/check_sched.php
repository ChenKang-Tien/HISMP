<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$n = \App\Models\User::where('role_id',6)->first();
$t = $n->createToken('t')->plainTextToken;

// Check today's schedules
$s = \App\Models\Schedule::where('date', date('Y-m-d'))->where('shift', '午班')->get();
echo "Schedules today (午班): " . $s->count() . "\n";
if ($s->count() > 0) {
    foreach ($s->take(3) as $sch) {
        echo "  patient_id={$sch->patient_id} bed={$sch->bed}\n";
    }
} else {
    // Try without shift filter
    $all = \App\Models\Schedule::where('date', date('Y-m-d'))->get();
    echo "All schedules today: " . $all->count() . "\n";
    if ($all->count() > 0) {
        echo "Shifts found: " . $all->pluck('shift')->unique()->implode(',') . "\n";
    }
}
