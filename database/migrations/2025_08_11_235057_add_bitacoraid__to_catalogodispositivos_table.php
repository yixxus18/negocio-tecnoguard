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
            $table->unsignedBigInteger('bitacora_id')->nullable()->after('archivo_configuracion');

            $table->foreign('bitacora_id', 'catalogo_dispositivos_bitacora_id_fk')
                  ->references('id')->on('bitacora')
                  ->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('catalogo_dispositivos', function (Blueprint $table) {
    $table->dropForeign('catalogo_dispositivos_bitacora_id_fk');
    $table->dropColumn('bitacora_id');
        });
    }
};
