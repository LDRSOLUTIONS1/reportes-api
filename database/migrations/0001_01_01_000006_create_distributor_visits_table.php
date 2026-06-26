<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_report_id')->constrained()->cascadeOnDelete();
            $table->string('distribuidor');
            $table->string('plaza')->nullable();
            $table->string('grupo')->nullable();
            $table->json('temas_revisados')->nullable();
            $table->json('participantes')->nullable();
            $table->text('comentarios_adicionales')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_visits');
    }
};
