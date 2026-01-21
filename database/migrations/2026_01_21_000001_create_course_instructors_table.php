<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Источник назначения (из Moodle или вручную)
            $table->string('source')->default('moodle'); // moodle|manual

            // Moodle shortname роли, по которой пользователь считается преподавателем (editingteacher|teacher|manager)
            $table->string('moodle_role_shortname')->nullable();

            // Флаг "основной преподаватель" среди списка (для UI). Основной преподаватель курса по-прежнему хранится в courses.instructor_id
            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            $table->unique(['course_id', 'user_id'], 'course_instructors_unique');
            $table->index(['course_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_instructors');
    }
};

