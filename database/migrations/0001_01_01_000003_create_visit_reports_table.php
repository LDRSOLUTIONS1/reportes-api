<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('visit_type', ['cliente_directo', 'distribuidor']);
            $table->enum('tipo_visita', [
                'presentacion_comercial',
                'capacitacion_operativa',
                'capacitacion_producto',
                'acompanamiento_comercial',
                'operativa',
                'otro'
            ]);
            $table->string('objetivo')->nullable();
            $table->text('logros_estrategia')->nullable();
            $table->string('segmento')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->enum('status', ['borrador', 'enviado', 'revisado'])->default('borrador');

            $table->tinyInteger('estado')
                ->default(2)
                ->comment('0=Eliminado, 1=Inactivo, 2=Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_reports');
    }
};
