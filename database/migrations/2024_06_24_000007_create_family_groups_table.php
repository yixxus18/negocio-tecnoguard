<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('family_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('membership_id');
            $table->unsignedBigInteger('cerrada_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('membership_id')
                ->references('id')->on('memberships')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('cerrada_id')
                ->references('id')->on('cerradas')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_groups');
    }
}; 