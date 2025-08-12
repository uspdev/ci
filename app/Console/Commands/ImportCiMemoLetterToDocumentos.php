<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportCiMemoLetterToDocumentos extends Command
{
    protected $signature = 'import:ci_memo_letter';

    protected $description = 'Importa registros da tabela ci_memo e ci_letter para a nova tabela documentos';

    public function handle()
    {
        $this->info('Iniciando importação...');

        $memos = DB::table('ci_memo')->get();

        $total = 0;

        foreach ($memos as $memo) {
            DB::table('documentos')->insert([
                'codigo'          => sprintf('%d/%03d', date('Y', strtotime($memo->date)), $memo->code),
                'sequencial'      => $memo->code,
                'ano'             => date('Y', strtotime($memo->date)),
                'destinatario'    => $memo->receiver,
                'remetente'       => $memo->sender,
                'data_documento'  => $memo->date,
                'assunto'         => $memo->subject,
                'mensagem'        => $memo->text,
                'finalizado'      => $memo->archieved,
                'data_finalizacao'=> null,
                'categoria_id'    => $memo->category_id == 1 
                            ? 8 
                            : ($memo->category_id == 3 ? 10 : $memo->category_id),
                'user_id'         => 6,
                'grupo_id'       =>  3,
                'created_at'      => Carbon::parse($memo->date)->format('Y-m-d H:i:s'),
                'updated_at'      => Carbon::parse($memo->date)->format('Y-m-d H:i:s'),
                'arquivo_id'      => null
            ]);

            $total++;
        }

        $letters = DB::table('ci_letter')->get();

        foreach ($letters as $letter) {
            DB::table('documentos')->insert([
                'codigo'          => sprintf('%d/%03d', date('Y', strtotime($letter->date)), $letter->code),
                'sequencial'      => $letter->code,
                'ano'             => date('Y', strtotime($letter->date)),
                'destinatario'    => $letter->receiver,
                'remetente'       => $letter->sender,
                'data_documento'  => $letter->date,
                'assunto'         => $letter->subject,
                'mensagem'        => '',
                'finalizado'      => $letter->archieved,
                'data_finalizacao'=> null,
                'categoria_id'    => $letter->category_id == 1 
                            ? 9 
                            : ($letter->category_id == 3 ? 11 : $letter->category_id),
                'user_id'         => 6,
                'grupo_id'       =>  3,
                'created_at'      => Carbon::parse($letter->date)->format('Y-m-d H:i:s'),
                'updated_at'      => Carbon::parse($letter->date)->format('Y-m-d H:i:s'),
                'arquivo_id'      => null
            ]);

            $total++;
        }

        $this->info("Importação concluída. Registros inseridos: {$total}");

        return 0;
    }
}
