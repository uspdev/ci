<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->nullable();
            $table->json('settings');
            $table->unsignedBigInteger('grupo_id');
            $table->timestamps();
            $table->foreign('grupo_id')->references('id')->on('grupos')->onDelete('cascade');
            $table->unique(['nome', 'grupo_id'], 'unique_categoria_grupo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
