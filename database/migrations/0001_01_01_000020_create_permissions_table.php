<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('slug');

            $table->enum('action', [
                'read',
                'create',
                'update',
                'delete',
                'export',
                'print',
                'approve'
            ]);

            $table->tinyInteger('estado')
                ->default(2);

            $table->timestamps();

            $table->unique([
                'module_id',
                'action'
            ]);

            $table->unique('slug');
        });
    }

    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn(['module_id', 'action']);
        });
    }
};
