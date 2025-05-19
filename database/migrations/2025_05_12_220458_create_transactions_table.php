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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('account_id')->nullable()->constrained();
            $table->foreignId('card_id')->nullable()->constrained();
            $table->foreignId('category_id')->constrained();
            $table->enum('type', ['INCOME', 'EXPENSE']);
            $table->decimal('amount', 15, 2);
            $table->enum('method', ['CASH', 'ACCOUNT', 'CARD'])->nullable()->default(null);
            $table->date('date');
            $table->text('description')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->integer('recurrence_interval')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
