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
        Schema::table('catalogo_dispositivos', function (Blueprint $table) {
            $table->string('ssid',512)
                ->nullable()
                ->after('archivo_configuracion');

                $table->string('password',512)
                ->nullable()
                ->after('tecnico_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogo_dispositivos', function (Blueprint $table) {
            //
        });
    }
};
