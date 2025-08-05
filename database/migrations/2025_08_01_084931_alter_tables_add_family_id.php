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
        $tables = [
            'users',
            'accounts',
            'cards',
            'transactions',
            'categories',
            'notifications',
            'action_logs',
            'imports',
            'exports',
            'transfers',
            'banks'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                if (in_array($table, ['categories', 'banks'])) {
                    $tableBlueprint->unsignedBigInteger('family_id')->nullable();
                } else {
                    $tableBlueprint->unsignedBigInteger('family_id')->default(1);
                }

                $tableBlueprint->foreign('family_id')->references('id')->on('family')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users',
            'accounts',
            'cards',
            'transactions',
            'job',
            'category',
            'notifications',
            'action_logs',
            'imports',
            'exports',
            'transfers',
            'banks'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign([$table->getTable().'_family_id_foreign']);
                $table->dropColumn('family_id');
            });
        }
    }
};
