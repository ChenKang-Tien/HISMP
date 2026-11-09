<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patient_incidents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_check_id')->index();
            $table->unsignedBigInteger('patient_id')->nullable()->index();
            $table->unsignedBigInteger('nurse_id')->nullable();
            $table->string('type'); // BED_CHANGE, FALL, BLOOD_LEAK
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('reported');
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_incidents');
    }
};
