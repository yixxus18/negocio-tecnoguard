<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cerradas', function (Blueprint $table) {
            $table->id();
            $table->string('group_name', 127);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('configuration_pay_date')->nullable();
            $table->unsignedBigInteger('guard_id')->nullable();
            $table->timestamps();

            $table->foreign('configuration_pay_date')
                ->references('id')->on('configuration_pay_date')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cerradas');
    }
}; 