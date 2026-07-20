<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$n = \App\Models\User::where('role_id',6)->first(); $nt = $n->createToken('t')->plainTextToken;
$d = \App\Models\User::where('role_id',4)->first(); $dt = $d->createToken('t')->plainTextToken;
$b = 'http://localhost:8000/api';
function t($l,$u,$t,$e){$c=curl_init($u);curl_setopt_array($c,[CURLOPT_HTTPHEADER=>["Authorization: Bearer $t"],CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>5]);$r=curl_exec($c);$h=curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);echo"$l: $h ".($h===$e?"PASS":"FAIL($h)")."\n";}
t("Nurse->doctor patients","$b/doctor/patients",$nt,403);
t("Doctor->doctor patients","$b/doctor/patients",$dt,200);
t("Nurse->admin users","$b/hospital/users",$nt,403);
