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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->nullable();
            $table->integer('sequencial')->nullable();
            $table->integer('ano');
            $table->string('destinatario');
            $table->string('remetente');
            $table->date('data_documento');
            $table->text('assunto');
            $table->text('mensagem');
            $table->boolean('finalizado')->default(false);
            $table->timestamp('data_finalizacao')->nullable();
            $table->unsignedBigInteger('categoria_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('finalizer_user_id')->nullable();
            $table->unsignedBigInteger('grupo_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->timestamps();

            $table->foreign('grupo_id')->references('id')->on('grupos');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('finalizer_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
