<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('localidades_entradas', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitud', 8, 5);
            $table->decimal('longitud', 8, 5);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('localidades_entradas');
    }
}; 