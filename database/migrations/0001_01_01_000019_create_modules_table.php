<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('slug')->unique();

            $table->string('description')->nullable();

            $table->string('icon')->nullable();

            $table->string('route')->nullable();

            $table->unsignedInteger('order')->default(0);

            $table->boolean('show_menu')->default(true);

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('modules')
                ->nullOnDelete();

            $table->tinyInteger('estado')
                ->default(2);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
