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
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');

            $table->unsignedInteger('installment_number')->default(1);
            $table->date('due_date')->comment('Data de vencimento');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date')->nullable()->comment('Data de pagamento');
            $table->enum('status', ['PAID', 'SCHEDULED', 'DEBIT', 'PENDING'])->default('PENDING');



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
