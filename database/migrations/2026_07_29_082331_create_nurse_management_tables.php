<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. 群組表
        Schema::create('nurse_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 組別名稱 (如：A組)
            $table->string('color', 7)->default('#0f766e'); // 顏色標記
            $table->timestamps();
        });

        // 2. 護理師排班表 (某人/某組/某日期/某班別)
        Schema::create('nurse_shifts', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('shift_time'); // 0:早, 1:午, 2:晚
            $table->unsignedBigInteger('nurse_id'); // 指向護理師 (假設有 nurses 表)
            $table->unsignedBigInteger('group_id');
            $table->string('handover_notes')->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('nurse_groups');
        });

        // 3. 病患分組關係表
        Schema::create('patient_group_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('group_id');
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('nurse_groups')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_group_members');
        Schema::dropIfExists('nurse_shifts');
        Schema::dropIfExists('nurse_groups');
    }
};
