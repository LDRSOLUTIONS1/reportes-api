<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('followup_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_report_id')->constrained()->cascadeOnDelete();
            $table->text('acuerdo');
            $table->string('responsable')->nullable();
            $table->date('fecha_compromiso')->nullable();

            $table->tinyInteger('estado')
                ->default(2)
                ->comment('0=Eliminado, 1=Inactivo, 2=Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('followup_agreements');
    }
};
