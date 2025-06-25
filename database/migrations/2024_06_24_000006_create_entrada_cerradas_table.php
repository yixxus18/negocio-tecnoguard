<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('entrada_cerradas', function (Blueprint $table) {
            $table->unsignedBigInteger('cerrada_id');
            $table->unsignedBigInteger('entrada_id');

            $table->foreign('cerrada_id')
                ->references('id')->on('cerradas')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('entrada_id')
                ->references('id')->on('localidades_entradas')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrada_cerradas');
    }
}; 