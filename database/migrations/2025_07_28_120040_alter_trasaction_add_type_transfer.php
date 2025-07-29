<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE transactions MODIFY type ENUM('INCOME', 'EXPENSE', 'TRANSFER') NOT NULL");
        } else {
            // SQLite não suporta MODIFY nem ENUM. Alternativa segura:
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('type')->default('TRANSFER')->change(); // enum lógico via app
            });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
