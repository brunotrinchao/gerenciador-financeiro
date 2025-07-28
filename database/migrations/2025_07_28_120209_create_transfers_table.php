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
            Schema::create('transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('source_transaction_id')->nullable()->constrained('transactions')->cascadeOnDelete();
                $table->foreignId('target_transaction_id')->constrained('transactions')->cascadeOnDelete();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
