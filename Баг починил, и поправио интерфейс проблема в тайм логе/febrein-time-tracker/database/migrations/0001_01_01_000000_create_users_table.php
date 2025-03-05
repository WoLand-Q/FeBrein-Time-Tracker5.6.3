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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('login');
            $table->string('password');
            $table->Integer('role_id');
            $table->rememberToken();
            $table->timestamps();

            //$table->index('group_id','users_group_idx');
            //$table->foreign('group_id','users_group_fk')->on('groups')->references('id')->onDelete('cascade');

            //$table->string('email')->unique();
            //$table->timestamp('email_verified_at')->nullable();


        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // Уникальный идентификатор сессии
            $table->foreignId('user_id')->nullable()->index(); // ID пользователя (связывается с таблицей users)
            $table->string('ip_address', 45)->nullable(); // IP-адрес пользователя
            $table->text('user_agent')->nullable(); // Информация об устройстве пользователя
            $table->longText('payload'); // Данные сессии
            $table->integer('last_activity')->index(); // Время последней активности (timestamp)
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
