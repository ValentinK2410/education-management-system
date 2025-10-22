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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('institution_id')->constrained()->onDelete('cascade');
            $table->string('duration')->nullable(); // например, "4 года", "2 семестра"
            $table->string('degree_level')->nullable(); // бакалавр, магистр, доктор
            $table->decimal('tuition_fee', 10, 2)->nullable();
            $table->string('language')->default('ru'); // язык обучения
            $table->json('requirements')->nullable(); // требования для поступления
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
