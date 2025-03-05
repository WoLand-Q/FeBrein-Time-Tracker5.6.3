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
        Schema::create('user_time_logs', function (Blueprint $table) {
            $table->id(); // Первичный ключ
            $table->unsignedBigInteger('user_id'); // Связь с таблицей users
            $table->timestamp('acted_at'); // Время события
            $table->unsignedInteger('event_id'); // ID события
            $table->timestamps(); // Стандартные поля created_at и updated_at

            // Индекс для ускорения запросов
            $table->index('user_id', 'user_time_logs_user_idx');

            // Внешний ключ для связи с таблицей users
            $table->foreign('user_id', 'user_time_logs_user_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade'); // При удалении пользователя удаляются связанные записи
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_time_logs');
    }
};
