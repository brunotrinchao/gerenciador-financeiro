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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('bank_id')->constrained();
            $table->integer('type')->default(1)->comment('1 = Conta Corrente, 2 = Poupança');
            $table->integer('balance')->default(0);
            $table->char('balance_currency', 3)->default('BRL');
            $table->timestamps();


            $table->index(['balance', 'balance_currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
