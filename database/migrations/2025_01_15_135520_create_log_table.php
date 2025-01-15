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
        Schema::create('log', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('date')->useCurrent();
            $table->text('action');
            $table->integer('fonctionnalite_id')->index('fk_fonctionnalite_id');
            $table->unsignedBigInteger('id_user')->index('logs_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log');
    }
};
