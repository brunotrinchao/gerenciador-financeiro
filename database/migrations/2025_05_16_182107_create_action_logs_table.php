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
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable(); // quem fez a ação (se logado)
            $table->string('action'); // ação executada (create, update, delete, etc)
            $table->string('model_type')->nullable(); // tipo do modelo afetado
            $table->unsignedBigInteger('model_id')->nullable(); // id do modelo afetado
            $table->json('old_values')->nullable(); // valores antigos (para update)
            $table->json('new_values')->nullable(); // valores novos (para create/update)
            $table->text('description')->nullable(); // descrição da ação
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_logs');
    }
};
