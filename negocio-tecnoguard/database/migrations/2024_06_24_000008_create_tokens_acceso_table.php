<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tokens_acceso', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->enum('tipo_token', ['servicio', 'visita', 'residente sin acceso']);
            $table->dateTime('fecha_expiracion')->nullable();
            $table->tinyInteger('usos')->unsigned()->nullable();
            $table->string('valor', 256)->nullable();
            $table->enum('puerta', ['peatonal', 'automovil']);
            $table->timestamps();

            $table->foreign('usuario_id')
                ->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens_acceso');
    }
}; 