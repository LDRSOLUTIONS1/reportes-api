<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('module_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('action', [
                'created',
                'updated',
                'deleted',
                'viewed',
                'login',
                'logout',
                'export',
                'print'
            ]);

            $table->string('model_type')->nullable();

            $table->unsignedBigInteger('model_id')->nullable();

            $table->json('old_values')->nullable();

            $table->json('new_values')->nullable();

            $table->text('description')->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->text('user_agent')->nullable();

            $table->string('url')->nullable();

            $table->string('method', 10)->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['model_type', 'model_id']);

            $table->index('user_id');

            $table->index('module_id');

            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
