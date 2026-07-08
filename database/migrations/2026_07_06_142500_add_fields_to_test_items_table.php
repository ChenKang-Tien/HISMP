<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToTestItemsTable extends Migration
{
    public function up()
    {
        Schema::table('test_items', function (Blueprint $table) {
            $table->string('frequency', 20)->default('mo')->comment('月檢mo/季檢qt/年檢yr/其他ot');
            $table->decimal('range_lower', 10, 2)->nullable()->comment('合理區間下限');
            $table->decimal('range_upper', 10, 2)->nullable()->comment('合理區間上限');
            $table->string('unit', 50)->nullable()->comment('單位');
            $table->text('education_summary')->nullable()->comment('衛教摘要');
        });
    }

    public function down()
    {
        Schema::table('test_items', function (Blueprint $table) {
            $table->dropColumn(['frequency', 'range_lower', 'range_upper', 'unit', 'education_summary']);
        });
    }
}
