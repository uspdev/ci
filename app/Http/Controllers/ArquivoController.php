<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Documento;
use App\Models\Arquivo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ArquivoController extends Controller
{
    /**
     * Upload de arquivo para documento
     * 
     * @param Request $request
     * @param Documento $documento
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request, Documento $documento)
    {
        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->grupo_id)) {
            abort(403, 'Você não tem permissão para adicionar arquivos a este documento.');
        }

        $request->validate([
            'arquivo' => 'required|file|max:10240',
        ]);

        $arquivo = $request->file('arquivo');
        $nomeOriginal = $arquivo->getClientOriginalName();
        $nomeArquivo = time() . '_' . $nomeOriginal;
        $caminho = $arquivo->storeAs('documentos/arquivos', $nomeArquivo);

        Arquivo::create([
            'documento_id' => $documento->id,
            'nome_original' => $nomeOriginal,
            'caminho' => $caminho,
            'tipo_mime' => $arquivo->getMimeType(),
            'tamanho' => $arquivo->getSize(),
            'tipo_arquivo' => 'upload',
            'user_id' => Auth::id(),
        ]);

        session()->flash('alert-success', 'Arquivo adicionado com sucesso!');
        return redirect()->route('documento.edit', $documento);
    }

    public function download(Arquivo $arquivo)
    {
        $caminho = $arquivo->caminho;

        if (!Storage::exists($caminho)) {
            abort(404);
        }

        $nomeDownload = $arquivo->nome_original ?? basename($caminho);
        $nomeDownload = str_replace(['/', '\\'], '-', $nomeDownload);

        return Storage::download($caminho, $nomeDownload);
    }

    public function destroy(Arquivo $arquivo)
    {
        $documento = $arquivo->documento;

        $nome = $arquivo->nome_original;
        $id = $arquivo->id;
        $arquivo->delete();

        activity()
            ->performedOn($documento)
            ->causedBy(auth()->user())
            ->withProperties(['id' => $id, 'arquivo' => $nome])
            ->log("Arquivo excluído: {$nome}");

        session()->flash('alert-success', 'Arquivo excluído com sucesso e ação registrada no histórico.');
        return redirect()->back();
    }
}
