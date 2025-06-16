<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anexo;

class AnexoController extends Controller
{
    /**
     * Upload de anexo para documento
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request, $id)
    {
        $documento = Documento::findOrFail($id);

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->grupo_id)) {
            abort(403, 'Você não tem permissão para adicionar anexos a este documento.');
        }

        $request->validate([
            'arquivo' => 'required|file|max:10240',
        ]);

        $arquivo = $request->file('arquivo');
        $nomeOriginal = $arquivo->getClientOriginalName();
        $nomeArquivo = time() . '_' . $nomeOriginal;
        $caminho = $arquivo->storeAs('documentos/anexos', $nomeArquivo, 'public');

        Anexo::create([
            'documento_id' => $documento->id,
            'nome_original' => $nomeOriginal,
            'caminho' => $caminho,
            'tipo_mime' => $arquivo->getMimeType(),
            'tamanho' => $arquivo->getSize(),
            'tipo_anexo' => 'upload',
            'user_id' => Auth::id(),
        ]);

        session()->flash('alert-success', 'Anexo adicionado com sucesso!');
        return redirect()->route('documento.edit', $id);
    }

    public function destroy($id)
    {
        $anexo = Anexo::findOrFail($id);
        $documento = $anexo->documento;

        if ($anexo->caminho) {
            \Storage::disk('public')->delete($anexo->caminho);
        }

        $nome = $anexo->nome_original;

        $anexo->delete();

        activity()
            ->performedOn($documento)
            ->causedBy(auth()->user())
            ->withProperties(['anexo' => $nome])
            ->log("Anexo excluído: {$nome}");

        session()->flash('alert-success', 'Anexo excluído com sucesso e ação registrada no histórico.');
        return redirect()->back();
    }
}
