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
            
            $table->foreignId('cerrada_id')
                  ->nullable()
                  ->constrained('cerradas');
            
            $table->foreignId('tecnico_id')
                  ->nullable()
                  ->constrained('users');
            
        
            $table->string('archivo_configuracion', 512);
            
            
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
