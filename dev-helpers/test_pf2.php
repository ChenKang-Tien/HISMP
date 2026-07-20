<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$n = \App\Models\User::where('role_id',6)->first(); $nt = $n->createToken('t')->plainTextToken;
$d = \App\Models\User::where('role_id',4)->first(); $dt = $d->createToken('t')->plainTextToken;
$b = 'http://localhost:8000/api';
function t($l,$u,$m,$t,$d,$e){$c=curl_init($u);$o=[CURLOPT_HTTPHEADER=>["Authorization: Bearer $t","Content-Type: application/json"],CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>5];if($m=='POST')$o[CURLOPT_POST]=1;if($d)$o[CURLOPT_POSTFIELDS]=json_encode($d);curl_setopt_array($c,$o);$r=curl_exec($c);$h=curl_getinfo($c,CURLINFO_HTTP_CODE);$j=json_decode($r,true);curl_close($c);$p=($h==$e);echo"$l $h ".($p?"PASS":"FAIL($e)")." ".($j['reason']??$j['message']??'OK')."\n";}
echo "Role:\n";t("Dr->prepare",$b.'/dialysis/1/prepare/','GET',$dt,null,403);
echo "Nurse->prepare:",$b.'/dialysis/1/prepare/','GET',$nt,null,200);
