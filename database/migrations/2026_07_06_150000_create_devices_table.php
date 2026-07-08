<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('設備代碼');
            $table->string('type', 20)->comment('類型: dialysis/scale/bp');
            $table->string('vendor', 100)->comment('廠商');
            $table->string('model', 100)->comment('型號');
            $table->string('status', 20)->default('正常')->comment('狀態: 正常/維護中');
            $table->string('ip_address', 45)->nullable()->comment('IP 位址');
            $table->string('subnet_mask', 45)->nullable()->comment('子網路遮罩');
            $table->string('gateway', 45)->nullable()->comment('閘道器/DNS');
            $table->string('assigned_bed', 20)->nullable()->comment('對應床位');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
