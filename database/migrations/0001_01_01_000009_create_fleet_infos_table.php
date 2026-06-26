<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_visit_id')->constrained()->cascadeOnDelete();
            $table->string('marca');
            $table->string('modelo')->nullable();
            $table->decimal('capacidad_carga', 8, 2)->nullable();
            $table->integer('cantidad')->nullable();
            $table->decimal('porcentaje_flota', 5, 2)->nullable();
            $table->string('comentarios_aplicacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_infos');
    }
};
