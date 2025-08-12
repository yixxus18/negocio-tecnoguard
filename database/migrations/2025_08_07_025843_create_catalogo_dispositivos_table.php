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
       Schema::create('catalogo_dispositivos', function (Blueprint $table) {
            $table->id();

            // Todas las FKs nullable y con nullOnDelete
            $table->foreignId('cerrada_id')
                ->nullable()
                ->constrained('cerradas')
                ->nullOnDelete();

            $table->foreignId('tecnico_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('archivo_configuracion', 512)->nullable();
            $table->string('ssid', 512)->nullable();
            $table->string('password', 512)->nullable();

            $table->foreignId('bitacora_id')
                ->nullable()
                ->constrained('bitacora')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_dispositivos');
    }
};
