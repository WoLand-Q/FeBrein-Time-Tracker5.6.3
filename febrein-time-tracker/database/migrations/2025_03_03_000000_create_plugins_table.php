<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // Уникальный идентификатор
            $table->string('name');           // Название плагина
            $table->text('description')->nullable();
            $table->string('version')->nullable(); // Версия
            $table->string('author')->nullable(); // Автор
            $table->boolean('enabled')->default(false);
            $table->enum('type', ['backend', 'frontend', 'fullstack'])->default('frontend');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
