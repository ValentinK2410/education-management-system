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
        Schema::table('courses', function (Blueprint $table) {
            // Добавляем поля для синхронизации с WordPress и Moodle
            if (!Schema::hasColumn('courses', 'wordpress_course_id')) {
                $table->unsignedBigInteger('wordpress_course_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('courses', 'moodle_course_id')) {
                $table->unsignedBigInteger('moodle_course_id')->nullable()->after('wordpress_course_id');
            }
            if (!Schema::hasColumn('courses', 'short_description')) {
                $table->text('short_description')->nullable()->after('description');
            }
            if (!Schema::hasColumn('courses', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('program_id');
            }
            if (!Schema::hasColumn('courses', 'category_name')) {
                $table->string('category_name')->nullable()->after('category_id');
            }
            if (!Schema::hasColumn('courses', 'start_date')) {
                $table->date('start_date')->nullable()->after('duration');
            }
            if (!Schema::hasColumn('courses', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('courses', 'capacity')) {
                $table->integer('capacity')->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('courses', 'enrolled')) {
                $table->integer('enrolled')->default(0)->after('capacity');
            }
            if (!Schema::hasColumn('courses', 'meta')) {
                $table->json('meta')->nullable()->after('enrolled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'wordpress_course_id',
                'moodle_course_id',
                'short_description',
                'category_id',
                'category_name',
                'start_date',
                'end_date',
                'capacity',
                'enrolled',
                'meta'
            ]);
        });
    }
};

