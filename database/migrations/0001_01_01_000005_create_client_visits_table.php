<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_report_id')->constrained()->cascadeOnDelete();
            $table->string('razon_social');
            $table->string('ubicaciones')->nullable();
            $table->string('tamanio_flota')->nullable();
            $table->string('giro')->nullable();
            $table->string('rutas')->nullable();
            $table->string('cobertura')->nullable();
            $table->enum('tipo_cliente', ['conquista', 'retencion', 'desarrollo'])->nullable();
            $table->integer('edad_promedio_flota')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_visits');
    }
};
