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
        Schema::create('user_work_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Связь с таблицей users
            $table->date('date'); // Дата работы
            $table->integer('work_minutes')->default(0); // Количество минут работы
            $table->integer('break_minutes')->default(0); // Количество минут на перерыв
            $table->timestamp('work_started_at')->nullable(); // Время начала работы
            $table->timestamp('work_ended_at')->nullable(); // Время окончания работы
            $table->timestamp('break_started_at')->nullable(); // Время начала перерыва
            $table->timestamp('break_ended_at')->nullable(); // Время окончания перерыва
            $table->timestamps(); // Стандартные поля created_at и updated_at


            $table->index('user_id','user_work_days_user_idx');
            // Добавление внешнего ключа для связи с таблицей users
            $table->foreign('user_id', 'user_work_days_user_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade'); // При удалении пользователя удаляются записи
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_work_days');
    }
};
