<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_dialysis_manual_edits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_check_id')->index();
            $table->unsignedBigInteger('manual_record_id')->index();
            $table->string('field', 20);
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->unsignedBigInteger('nurse_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_dialysis_manual_edits');
    }
};
