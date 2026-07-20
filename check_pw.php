<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \App\Models\User::whereIn('role_id', [1,3])->get();
foreach ($users as $u) {
    $pw = '12345678';
    $ok = \Illuminate\Support\Facades\Hash::check($pw, $u->password);
    echo "{$u->email} (role_id={$u->role_id}) pw=$pw: " . ($ok ? 'OK' : 'FAIL') . "\n";
    if (!$ok) {
        $pw2 = 'Hcis_1110';
        $ok2 = \Illuminate\Support\Facades\Hash::check($pw2, $u->password);
        echo "  alt=$pw2: " . ($ok2 ? 'OK' : 'FAIL') . "\n";
    }
}
