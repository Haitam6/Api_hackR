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
        Schema::table('log', function (Blueprint $table) {
            $table->foreign(['fonctionnalite_id'], 'fk_fonctionnalite_id')->references(['id'])->on('fonctionnalites')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['id_user'], 'logs_1')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log', function (Blueprint $table) {
            $table->dropForeign('fk_fonctionnalite_id');
            $table->dropForeign('logs_1');
        });
    }
};
