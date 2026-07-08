<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBedBindingsTable extends Migration
{
    public function up()
    {
        Schema::create('bed_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->onDelete('cascade');
            $table->string('bed_number', 10)->comment('床號數字部分: 01, 02, 03...');
            $table->string('bed_label', 20)->comment('組合標籤: A-01, A-02...');
            $table->foreignId('dialysis_device_id')->nullable()->constrained('devices')->onDelete('set null');
            $table->foreignId('bp_device_id')->nullable()->constrained('devices')->onDelete('set null');
            $table->timestamps();

            $table->unique(['zone_id', 'bed_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bed_bindings');
    }
}
