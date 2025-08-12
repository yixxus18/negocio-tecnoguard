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
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_asignacion');
            $table->date('fecha_finalizacion')->nullable();
              $table->foreignId('tiposervicio_id')
                  ->constrained('tipo_Servicio');
                  $table->foreignId('tecnico_id')->constrained('users');
                  $table->foreignId('cerrada_id')->constrained('cerradas');
             $table->enum('status',['Asignado','En Proceso','Concluido','No concluido'])->default('En proceso');
             $table->string('comentario')->nullable();
              $table->string('prioridad', 20)
                ->default('Sin prioridad');

            $table->string('descripcion',512)
                ->nullable();

                $table->date('fecha_programada')->nullable();
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
