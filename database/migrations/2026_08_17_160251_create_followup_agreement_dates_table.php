<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('followup_agreement_dates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('followup_agreement_id')
                ->constrained('followup_agreements')
                ->cascadeOnDelete();

            $table->date('fecha_compromiso');

            $table->text('motivo_reprogramacion')->nullable();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->unsignedInteger('numero_reprogramacion')
                ->default(0);

            $table->tinyInteger('estado')
                ->default(2)
                ->comment('0=Eliminado, 1=Inactivo, 2=Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('followup_agreement_dates');
    }
};
