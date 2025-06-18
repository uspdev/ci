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
        Schema::table('documentos', function (Blueprint $table) {
            $table->unsignedBigInteger('arquivo_id')->nullable();
            
            $table->foreign('categoria_id')->references('id')->on('categorias');
            $table->foreign('template_id')->references('id')->on('templates');
            $table->foreign('arquivo_id')->references('id')->on('arquivos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropForeign(['template_id']);
            $table->dropForeign(['arquivo_id']);
            $table->dropColumn('arquivo_id');
        });
    }
};
