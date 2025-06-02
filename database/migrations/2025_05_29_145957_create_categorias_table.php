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
            $table->string('abreviacao');
            $table->unsignedBigInteger('setor_id');
            $table->timestamps();
            $table->foreign('setor_id')->references('id')->on('setors')->onDelete('cascade');
            $table->unique(['nome', 'setor_id'], 'unique_categoria_setor');
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
