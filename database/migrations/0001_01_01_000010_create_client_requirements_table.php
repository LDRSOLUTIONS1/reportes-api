<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_visit_id')->constrained()->cascadeOnDelete();
            $table->string('modelo_interes')->nullable();
            $table->string('tipo_carroceria')->nullable();
            $table->string('proyeccion_compra')->nullable();
            $table->enum('financiamiento', [
                'credito_casa',
                'arrendamiento',
                'contado',
                'otro'
            ])->nullable();
            $table->string('tiempo_entrega')->nullable();
            $table->string('lugar_entrega')->nullable();
            $table->string('distribuidor')->nullable();
            $table->boolean('demo')->default(false);
            $table->string('otro')->nullable();

            $table->tinyInteger('estado')
                ->default(2)
                ->comment('0=Eliminado, 1=Inactivo, 2=Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_requirements');
    }
};
