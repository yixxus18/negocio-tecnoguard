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
        Schema::create('solicitud_cambio_cerrada', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
             $table->string('direccion',512);

            $table->foreignId('cerradaproveniente_id')
                  ->constrained('cerradas')
                  ->cascadeOnDelete();

            $table->foreignId('cerradadestino_id')
                  ->constrained('cerradas')
                  ->cascadeOnDelete();

            $table->boolean('proveniente')->default(false);
            $table->boolean('destino')->default(false);

            
            $table->foreignId('user_solicitud')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->enum('estado', ['Pendiente', 'Aprobado', 'Rechazado'])
                  ->default('Pendiente');

            $table->string('comentariodestino')->nullable();
            $table->string('comentarioproveniente', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_cambio_cerrada');
    }
};
