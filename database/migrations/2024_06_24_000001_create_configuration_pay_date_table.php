<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('configuration_pay_date', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_configuracion', 127);
            $table->tinyInteger('Fecha_Corte');
            $table->integer('pay');
            $table->tinyInteger('tiempo_prorroga');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuration_pay_date');
    }
}; 