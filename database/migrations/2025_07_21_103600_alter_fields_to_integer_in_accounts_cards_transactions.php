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
        // Alterar tabela accounts
        Schema::table('accounts', function (Blueprint $table) {
            $table->integer('balance')->default(0)->change();
        });

        // Alterar tabela cards
        Schema::table('cards', function (Blueprint $table) {
            $table->integer('limit')->default(0)->change();
        });

        // Alterar tabela transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('amount')->change();
            $table->enum('method', ['CASH', 'ACCOUNT', 'CARD'])->default(null)->change();
            $table->text('description')->nullable(false)->change();
            $table->integer('recurrence_interval')->default(1)->change();
        });


        // Altera a tabela transaction_items
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->integer('amount')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
