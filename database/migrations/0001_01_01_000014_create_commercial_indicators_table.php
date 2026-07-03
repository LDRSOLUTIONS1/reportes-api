<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commercial_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_visit_id')->constrained()->cascadeOnDelete();
            $table->string('modelo');
            $table->decimal('bp_2025', 10, 2)->nullable();
            $table->decimal('whole_ytd', 10, 2)->nullable();
            $table->decimal('porcentaje_avance', 5, 2)->nullable();
            $table->decimal('retail_ytd', 10, 2)->nullable();
            $table->integer('inventario')->nullable();
            $table->integer('back_order')->nullable();
            
            $table->tinyInteger('estado')
                ->default(2)
                ->comment('0=Eliminado, 1=Inactivo, 2=Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_indicators');
    }
};
