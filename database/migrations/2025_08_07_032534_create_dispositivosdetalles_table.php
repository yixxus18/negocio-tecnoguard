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
        Schema::create('dispositivosdetalles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
             
            $table->string('uid', 64);
        
             $table->foreignId('catalogo_id')
                  ->constrained('catalogo_dispositivos');
            $table->string('nombre_dispositivo')->nullable();
            $table->integer('pin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispositivosdetalles');
    }
};
