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
        Schema::table('droits', function (Blueprint $table) {
            $table->foreign(['fonctionnalite_id'], 'droits_ibfk_1')->references(['id'])->on('fonctionnalites')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['role_id'], 'droits_ibfk_2')->references(['id'])->on('roles')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('droits', function (Blueprint $table) {
            $table->dropForeign('droits_ibfk_1');
            $table->dropForeign('droits_ibfk_2');
        });
    }
};
