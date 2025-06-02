<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Categoria;
use App\Models\Setor;
use App\Models\Template;
use App\Models\Anexo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use \Spatie\Activitylog\Models\Activity;

class DocumentoController extends Controller
{
    /**
     * Gera código automático para documento
     * Formato: CATEGORIA Nº XXX/AAAA/SETOR-e
     */
    private function gerarCodigo(Categoria $categoria, int $setorId): string
    {
        $ano = date('Y');
        $setor = Setor::findOrFail($setorId);
        
        $ultimoDocumento = Documento::where('categoria_id', $categoria->id)
            ->where('setor_id', $setorId)
            ->whereYear('created_at', $ano)
            ->orderBy('id', 'desc')
            ->first();

        $proximoNumero = 1;
        if ($ultimoDocumento) {
            preg_match('/Nº (\d+)\//', $ultimoDocumento->codigo, $matches);
            $proximoNumero = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        }

        $categoriaAbrev = strtoupper(substr(iconv('UTF-8', 'ASCII//TRANSLIT', $categoria->nome), 0, 3));
        $numero = str_pad($proximoNumero, 3, '0', STR_PAD_LEFT);
        $setorNome = strtoupper($setor->name);
        
        return "{$categoriaAbrev} Nº {$numero}/{$ano}/{$setorNome}-e";
    }


    /**
     * Exibe a lista de documentos do setor ativo
     */
    public function index(Request $request)
    {
        $this->authorize('setorManager');

        $setorId = session('setor_id');
        
        if (!$setorId) {
            return redirect()->route('setor.index')->with('alert-warning', 'Selecione um setor primeiro.');
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $setorId)) {
            abort(403, 'Você não tem permissão para acessar este setor.');
        }

        $query = Documento::where('setor_id', $setorId)->with(['categoria', 'template']);

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $documentos = $query->get();

        $categorias = Categoria::where('setor_id', $setorId)->get();
        return view('documento.index', compact('documentos', 'categorias'));
    }

    /**
     * Exibe o formulário de criação de novo documento
     */
    public function create()
    {
        $this->authorize('setorManager');

        $setorId = session('setor_id');
        
        if (!$setorId) {
            return redirect()->route('setor.index')->with('alert-warning', 'Selecione um setor primeiro.');
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $setorId)) {
            abort(403, 'Você não tem permissão para criar documentos neste setor.');
        }

        $categorias = Categoria::where('setor_id', $setorId)->get();
        $templates = Template::all();

        return view('documento.create', compact('categorias', 'templates'));
    }

    /**
     * Armazena um novo documento no banco de dados
     */
    public function store(Request $request)
    {
        $this->authorize('setorManager');

        $setorId = session('setor_id');
        
        if (!$setorId) {
            return redirect()->route('setor.index')->with('alert-warning', 'Selecione um setor primeiro.');
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $setorId)) {
            abort(403, 'Você não tem permissão para criar documentos neste setor.');
        }

        $request->validate([
            'destinatario' => 'required|string|max:255',
            'remetente' => 'required|string|max:255',
            'data_documento' => 'required|date',
            'assunto' => 'required|string',
            'mensagem' => 'required|string',
            'categoria_id' => 'required|exists:categorias,id',
            'template_id' => 'nullable|exists:templates,id',
            'anexo_id' => 'nullable|exists:documentos,id',
        ]);

        $categoria = Categoria::findOrFail($request->categoria_id);
        if ($categoria->setor_id != $setorId) {
            abort(403, 'Categoria não pertence ao setor selecionado.');
        }
        $categoria = Categoria::find($request->categoria_id);
        $codigo = $this->gerarCodigo($categoria, $setorId);

        $documento = Documento::create([
            'codigo' => $codigo,
            'destinatario' => $request->destinatario,
            'remetente' => $request->remetente,
            'data_documento' => $request->data_documento,
            'assunto' => $request->assunto,
            'mensagem' => $request->mensagem,
            'categoria_id' => $request->categoria_id,
            'template_id' => $request->template_id,
            'anexo_id' => $request->anexo_id,
            'setor_id' => $setorId,
            'user_id' => Auth::id(),
        ]);

        session()->flash('alert-success', 'Documento criado com sucesso! Código: ' . $codigo);
        return redirect()->route('documento.show', $documento);
    }

    /**
     * Exibe um documento específico
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $documento = Documento::with(['categoria.setor', 'template', 'anexos'])->findOrFail($id);

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->setor_id)) {
            abort(403, 'Você não tem permissão para visualizar este documento.');
        }

        $activities =  Activity::orderBy('created_at', 'DESC')->where('subject_id', $id)->get();

        return view('documento.show', compact('documento', 'activities'));
    }

    /**
     * Exibe o formulário de edição de documento
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $documento = Documento::with(['categoria.setor', 'template', 'anexos'])->findOrFail($id);

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->setor_id)) {
            abort(403, 'Você não tem permissão para editar este documento.');
        }

        if ($documento->finalizado) {
            session()->flash('alert-warning', 'Este documento já foi finalizado e não pode ser editado.');
            return redirect()->route('documento.show', $id);
        }

        $setores = Gate::allows('manager') ? Setor::all() : Setor::listarSetoresPorUsuario();
        $categorias = Categoria::whereIn('setor_id', $setores->pluck('id'))->get();
        $templates = Template::all();

        return view('documento.create', compact('documento', 'setores', 'categorias', 'templates'));
    }

    /**
     * Atualiza os dados de um documento existente
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $documento = Documento::findOrFail($id);

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->setor_id)) {
            abort(403, 'Você não tem permissão para editar este documento.');
        }

        if ($documento->finalizado) {
            session()->flash('alert-danger', 'Este documento já foi finalizado e não pode ser editado.');
            return redirect()->route('documento.show', $id);
        }

        $request->validate([
            'destinatario' => 'required|string|max:255',
            'remetente' => 'required|string|max:255',
            'data_documento' => 'required|date',
            'assunto' => 'required|string',
            'mensagem' => 'required|string',
            'categoria_id' => 'required|exists:categorias,id',
            'template_id' => 'nullable|exists:templates,id',
        ]);

        $categoria = Categoria::findOrFail($request->categoria_id);

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $categoria->setor_id)) {
            abort(403, 'Você não tem permissão para mover este documento para esta categoria.');
        }

        $documento->update([
            'destinatario' => $request->destinatario,
            'remetente' => $request->remetente,
            'data_documento' => $request->data_documento,
            'assunto' => $request->assunto,
            'mensagem' => $request->mensagem,
            'categoria_id' => $request->categoria_id,
            'template_id' => $request->template_id,
            'setor_id' => $categoria->setor_id,
        ]);

        session()->flash('alert-success', 'Documento atualizado com sucesso!');
        return redirect()->route('documento.edit', $id);
    }

    /**
     * Finaliza um documento
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function finalizar($id)
    {
        $documento = Documento::findOrFail($id);

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->setor_id)) {
            abort(403, 'Você não tem permissão para finalizar este documento.');
        }

        if ($documento->finalizado) {
            session()->flash('alert-warning', 'Este documento já foi finalizado.');
            return redirect()->route('documento.show', $id);
        }

        $documento->update([
            'finalizado' => true,
            'data_finalizacao' => now(),
            'finalizer_user_id' => Auth::id(),
        ]);

        session()->flash('alert-success', 'Documento finalizado com sucesso!');
        return redirect()->route('documento.show', $id);
    }

    /**
     * Remove permanentemente um documento
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $documento = Documento::findOrFail($id);

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->setor_id)) {
            abort(403, 'Você não tem permissão para excluir este documento.');
        }

        if ($documento->finalizado) {
            session()->flash('alert-danger', 'Não é possível excluir um documento finalizado.');
            return redirect()->route('documento.show', $id);
        }

        foreach ($documento->anexos as $anexo) {
            Storage::delete($anexo->caminho);
        }

        $documento->delete();

        session()->flash('alert-success', 'Documento removido com sucesso!');
        return redirect()->route('documento.index');
    }

    /**
     * Upload de anexo para documento
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadAnexo(Request $request, $id)
    {
        $documento = Documento::findOrFail($id);

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->setor_id)) {
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
            'nome_arquivo' => $nomeArquivo,
            'caminho' => $caminho,
            'tipo_mime' => $arquivo->getMimeType(),
            'tamanho' => $arquivo->getSize(),
            'tipo_anexo' => 'upload',
            'user_id' => Auth::id(),
        ]);

        session()->flash('alert-success', 'Anexo adicionado com sucesso!');
        return redirect()->route('documento.edit', $id);
    }

    public function detalharAtividade($id)
    {
        $activity = Activity::findOrFail($id);
        $old = $activity->properties['old'] ?? [];
        $new = $activity->properties['attributes'] ?? [];
        return view('documento.atividade', compact('activity', 'old', 'new'));
    }
}
