<?php
require_once '/app/vendor/autoload.php';
$a = require_once '/app/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$token = "4009|iahMxWmtt25XeH23EW7rxyOMHJxojm7J4b2QIZepac62ee70";
$id = explode("|", $token)[0];
$pt = DB::table("personal_access_tokens")->where("id", $id)->first();
if ($pt) {
    $u = \App\Models\User::find($pt->tokenable_id);
    echo "User: " . ($u->name ?? "?") . " role: " . ($u->role->name ?? "?") . " email: " . ($u->email ?? "?") . "\n";
    echo "Abilities: " . ($pt->abilities ? json_encode($pt->abilities) : "none") . "\n";
    echo "Created: " . $pt->created_at . "\n";
} else {
    echo "Token NOT FOUND in DB\n";
}
