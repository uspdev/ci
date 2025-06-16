<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Categoria;
use App\Models\Grupo;
use App\Models\Template;
use App\Models\Anexo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use \Spatie\Activitylog\Models\Activity;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Pdfgen;

class DocumentoController extends Controller
{
    /**
     * Gera código automático para documento
     * Formato: CATEGORIA Nº XXX/AAAA/GRUPO-e
     */
    private function gerarCodigo(Categoria $categoria, int $grupoId, int $sequencial, int $ano): string
    {
        $grupo = Grupo::findOrFail($grupoId);
        $categoriaPrefixo = iconv('UTF-8', 'ASCII//TRANSLIT', $categoria->prefixo);
        $numero = str_pad($sequencial, 3, '0', STR_PAD_LEFT);
        $grupoNome = strtoupper($grupo->name);

        return "{$categoriaPrefixo} Nº {$numero}/{$ano}/{$grupoNome}-e";
    }

    /**
     * Exibe a lista de documentos do grupo ativo
     */
    public function index(Request $request)
    {
        $this->authorize('grupoManager');
        \UspTheme::activeUrl('documentos');
        
        $grupoId = session('grupo_id');
        
        if (!$grupoId) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $grupoId)) {
            abort(403, 'Você não tem permissão para acessar este grupo.');
        }

        $query = Documento::where('grupo_id', $grupoId)->with(['categoria', 'template']);

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $documentos = $query->get();

        $categorias = Categoria::where('grupo_id', $grupoId)->get();
        return view('documento.index', compact('documentos', 'categorias'));
    }


    /**
     * Exibe o formulário de criação de novo documento
     */
    public function create($categoria)
    {
        $this->authorize('grupoManager');

        $grupoId = session('grupo_id');
        
        if (!$grupoId) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $grupoId)) {
            abort(403, 'Você não tem permissão para criar documentos neste grupo.');
        }

        $templates = Categoria::findOrFail($categoria)->templates;
        
        return view('documento.create', compact('templates', 'categoria'));
    }

    /**
     * Armazena um novo documento no banco de dados
     */
    public function store(Request $request, $categoria)
    {
        $this->authorize('grupoManager');
        $grupoId = session('grupo_id');
        if (!$grupoId) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }
        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $grupoId)) {
            abort(403, 'Você não tem permissão para criar documentos neste grupo.');
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
        if ($categoria->grupo_id != $grupoId) {
            abort(403, 'Categoria não pertence ao grupo selecionado.');
        }
        $categoria = Categoria::find($request->categoria_id);
        $codigo = $this->gerarCodigo($categoria, $grupoId);

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
            'grupo_id' => $grupoId,
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
        $documento = Documento::with(['categoria.grupo', 'template', 'anexos'])->findOrFail($id);

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->grupo_id)) {
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
    public function edit($categoria, $id)
    {
        $documento = Documento::with(['categoria.grupo', 'template', 'anexos'])->findOrFail($id);

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->grupo_id)) {
            abort(403, 'Você não tem permissão para editar este documento.');
        }

        if ($documento->finalizado) {
            session()->flash('alert-warning', 'Este documento já foi finalizado e não pode ser editado.');
            return redirect()->route('documento.show', $id);
        }

        $templates = Categoria::findOrFail($categoria)->templates;

        return view('documento.create', compact('documento', 'categoria', 'templates'));
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

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->grupo_id)) {
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

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $categoria->grupo_id)) {
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
            'grupo_id' => $categoria->grupo_id,
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

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->grupo_id)) {
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

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->grupo_id)) {
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

    public function print($template, $data)
    {
        if (!$template) {
            abort(404, 'Template não encontrado.');
        }

        $fieldMap = [];
        if ($template->variaveis) {
            $fieldMap = is_array($template->variaveis)
                ? $template->variaveis
                : json_decode($template->variaveis, true);
        }

        $pdf = new Pdfgen();
            $pdf->setTemplate(public_path('storage/' . $template->arquivo));


        $pdf->setData($data);

        $pdf->parse();

        $pdf->pdfBuild('D', ['paper'=>'a4', 'orientation' => 'portrait'], $fieldMap);
    }

    public function gerarPdf($id)
    {
        $documento = Documento::with('template', 'categoria', 'categoria.grupo')->findOrFail($id);

        $template = $documento->template;

        if (!$template) {
            return redirect()->back()->with('alert-danger', 'Documento não possui template associado.');
        }

        $variaveis = [
            'codigo'        => $documento->codigo,
            'remetente'     => $documento->remetente,
            'destinatario'  => $documento->destinatario,
            'data'          => $documento->data_documento->format('d/m/Y'),
            'assunto'       => $documento->assunto,
            'mensagem'      => $documento->mensagem,
        ];

        $docName = $documento->categoria->grupo->name . '_' . $documento->categoria->prefixo . '_';
        $codigo = '';
        if (preg_match('/Nº (\d+)\//', $documento->codigo, $matches)) {
            $codigo = $matches[1];
        }
        $docName .= $codigo . '.pdf';

        $pdfContent = null;
        $anexoHash = '';
        $caminhoAnexo = '';

        if ($template->arquivo) {
            $fieldMap = [];
            if ($template->variaveis) {
                $fieldMap = is_array($template->variaveis)
                    ? $template->variaveis
                    : json_decode($template->variaveis, true);
            }

            $pdfgen = new Pdfgen();
            $pdfgen->setTemplate(public_path('storage/' . $template->arquivo));
            $pdfgen->setData($variaveis);
            $pdfgen->parse();

            $anexoHash = $pdfgen->getHash($fieldMap);
            $caminhoAnexo = 'documentos/gerados/' . $anexoHash . '.pdf';
            $fullPath = Storage::disk('public')->path($caminhoAnexo);

            $anexoExistente = Anexo::where('documento_id', $documento->id)
                ->where('tipo_anexo', 'gerado')
                ->where('caminho', $caminhoAnexo)
                ->first();

            if ($anexoExistente && Storage::disk('public')->exists($caminhoAnexo)) {
                $pdfContent = Storage::disk('public')->get($caminhoAnexo);
            } else {
                $pdfgen->pdfBuild('F', ['paper'=>'a4', 'orientation' => 'portrait'], $fieldMap, $fullPath);
                
                $pdfContent = Storage::disk('public')->get($caminhoAnexo);
                Anexo::updateOrCreate(
                    [
                        'documento_id' => $documento->id,
                        'tipo_anexo' => 'gerado',
                        'nome_original' => $docName,
                        'caminho' => $caminhoAnexo,
                        'tipo_mime' => 'application/pdf',
                        'tamanho' => strlen($pdfContent),
                        'user_id' => auth()->id(),
                    ]
                );
            }
        } else {
            $conteudo = $template->conteudo_padrao;
            
            foreach ($variaveis as $chave => $valor) {
                $conteudo = str_replace('{{ '.$chave.' }}', $valor, $conteudo);
            }
            
            $anexoHash = md5($conteudo);
            $caminhoAnexo = 'documentos/gerados/' . $anexoHash . '.pdf';
            $fullPath = Storage::disk('public')->path($caminhoAnexo);

            $anexoExistente = Anexo::where('documento_id', $documento->id)
                ->where('tipo_anexo', 'gerado')
                ->where('caminho', $caminhoAnexo)
                ->first();

            if ($anexoExistente && Storage::disk('public')->exists($caminhoAnexo)) {
                $pdfContent = Storage::disk('public')->get($caminhoAnexo);
            } else {
                $pdf = Pdf::loadHTML($conteudo);
                $pdf->save($fullPath); 
                
                $pdfContent = Storage::disk('public')->get($caminhoAnexo);

                Anexo::updateOrCreate(
                    [
                        'documento_id' => $documento->id,
                        'tipo_anexo' => 'gerado',
                        'nome_original' => $docName,
                        'caminho' => $caminhoAnexo,
                        'tipo_mime' => 'application/pdf',
                        'tamanho' => strlen($pdfContent),
                        'user_id' => auth()->id(),
                    ]
                );
            }
        }

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$docName.'"');
    }
}
