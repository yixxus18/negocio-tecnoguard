<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('membership_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('membership_id');
            $table->decimal('amount', 6, 2);
            $table->date('date_pay');
            $table->date('date_finalization');
            $table->string('ticket', 255);
            $table->enum('estatus', ['pendiente', 'revision', 'validado'])->default('pendiente');
            $table->timestamps();

            $table->foreign('membership_id')
                ->references('id')->on('memberships')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_details');
    }
}; 