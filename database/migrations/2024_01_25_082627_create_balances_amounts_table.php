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
        // Создаем таблицу для хранения балансов пользователей
        Schema::create('balances_amounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('balance_id')->index();
            $table->unsignedDecimal('amount', 13, 2);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances_amounts');
    }
};
