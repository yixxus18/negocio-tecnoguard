<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Agregar FK a cerradas.guard_id -> users.id
        Schema::table('cerradas', function (Blueprint $table) {
            $table->foreign('guard_id')
                ->references('id')->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // Agregar FK a users.family_id -> family_groups.id
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('family_id')
                ->references('id')->on('family_groups')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('cerradas', function (Blueprint $table) {
            $table->dropForeign(['guard_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['family_id']);
        });
    }
}; 