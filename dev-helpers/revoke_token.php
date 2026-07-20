<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$r = \Illuminate\Support\Facades\DB::table('personal_access_tokens')->where('id', 4009)->delete();
echo "Deleted: $r row(s)\n";

$c = \Illuminate\Support\Facades\DB::table('personal_access_tokens')->where('id', 4009)->count();
echo "Remaining: $c\n";
