<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить поле moodle_cohort_id в таблицу groups
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->unsignedBigInteger('moodle_cohort_id')->nullable()->after('id');
            $table->index('moodle_cohort_id');
        });
    }

    /**
     * Откатить миграцию - удалить поле moodle_cohort_id из таблицы groups
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['moodle_cohort_id']);
            $table->dropColumn('moodle_cohort_id');
        });
    }
};
