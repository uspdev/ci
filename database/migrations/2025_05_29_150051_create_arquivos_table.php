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
        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documento_id');
            $table->string('nome_original');
            $table->string('caminho', 500);
            $table->string('tipo_mime', 100);
            $table->bigInteger('tamanho');
            $table->enum('tipo_arquivo', ['upload', 'gerado']);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            
            $table->foreign('documento_id')->references('id')->on('documentos')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arquivos');
    }
};
