<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Importante: esta migración NO debe correrse dentro de una transacción
    public $withinTransaction = false;

    public function up(): void
    {
        // 1) Normaliza datos: cualquier 'pendiente' pasa a 'revision'
        DB::table('membership_details')
            ->where('estatus', 'pendiente')
            ->update(['estatus' => 'revision']);

        // 2) Cambia el ENUM: quita 'pendiente', agrega 'rechazado', default 'revision'
        DB::statement("
            ALTER TABLE membership_details
            MODIFY COLUMN estatus ENUM('revision','validado','rechazado')
            NOT NULL DEFAULT 'revision'
        ");
    }

    public function down(): void
    {
        // 1) Cualquier 'rechazado' vuelve a 'pendiente' para poder restaurar el ENUM original
        DB::table('membership_details')
            ->where('estatus', 'rechazado')
            ->update(['estatus' => 'pendiente']);

        // 2) Restaura el ENUM original con default 'pendiente'
        DB::statement("
            ALTER TABLE membership_details
            MODIFY COLUMN estatus ENUM('pendiente','revision','validado')
            NOT NULL DEFAULT 'pendiente'
        ");
    }
};
