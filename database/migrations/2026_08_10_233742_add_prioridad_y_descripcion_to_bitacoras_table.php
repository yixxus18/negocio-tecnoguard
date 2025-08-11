<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bitacora', function (Blueprint $table) {
            // agrega después de 'estado' (solo MySQL soporta ->after())
            $table->string('prioridad', 20)
                ->default('Sin prioridad')
                ->after('status');

            $table->string('descripcion',512)
                ->nullable()
                ->after('prioridad');

                $table->date('fecha_programada')->nullable();

        });
    }

   public function down(): void
    {
        Schema::table('bitacoras', function (Blueprint $table) {
            $table->dropColumn(['prioridad', 'descripcion', 'fecha_programada']);
        });
    }
};
