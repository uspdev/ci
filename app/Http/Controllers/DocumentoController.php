<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Categoria;
use App\Models\Grupo;
use App\Models\Template;
use App\Models\Arquivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use \Spatie\Activitylog\Models\Activity;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Pdfgen;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentCreated;
use App\Mail\DocumentUpdated;

class DocumentoController extends Controller
{
    private function verifyGrupo()
    {
        $grupoId = session('grupo_id');

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $grupoId)) {
            abort(403, 'Você não tem permissão para acessar este grupo.');
        }
    }
    
    /**
     * Gera código automático para documento
     * Formato: CATEGORIA Nº XXX/AAAA/GRUPO-e
     */
    private function gerarCodigo(string $prefixo, int $sequencial, int $ano): string
    {
        $numero = str_pad($sequencial, 3, '0', STR_PAD_LEFT);
        return "{$prefixo}{$numero}/{$ano}";
    }

    /**
     * Exibe a lista de documentos do grupo ativo
     */
    public function index(Categoria $categoria, $ano = null)
    {
        $this->authorize('grupoManager');
        \UspTheme::activeUrl('categorias');
        session(['grupo_id' => $categoria->grupo_id]);
        
        $this->verifyGrupo();

        if (!$ano){
            $ano = date('Y');
        }

        $query = Documento::where('grupo_id', $categoria->grupo_id)
            ->where('categoria_id', $categoria->id)
            ->where('ano', $ano)
            ->orderBy('sequencial', 'desc')
            ->with(['categoria', 'template']);

        
        $anos = \App\Models\Documento::whereNotNull('ano')
            ->distinct()
            ->orderBy('ano', 'desc')
            ->pluck('ano');

        $documentos = $query->get();

        return view('documento.index', compact('documentos', 'categoria', 'anos', 'ano'));
    }


    /**
     * Exibe o formulário de criação de novo documento
     */
    public function create(Categoria $categoria)
    {
        $this->authorize('grupoManager');

        $grupoId = session('grupo_id');
        if (!$grupoId) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }
        $this->verifyGrupo();

        $templates = $categoria->templates;
        
        return view('documento.create', compact('templates', 'categoria'));
    }

    /**
     * Armazena um novo documento no banco de dados
     */
    public function store(Request $request, Categoria $categoria)
    {
        $this->authorize('grupoManager');
        $grupoId = session('grupo_id');
        if (!$grupoId) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }
        $this->verifyGrupo();
        if ($categoria->grupo_id != $grupoId) {
            abort(403, 'Categoria não pertence ao grupo selecionado.');
        }

        $request->validate([
            'sequencial' => 'integer|nullable',
            'ano' => 'integer|nullable',
            'codigo' => 'string|max:255|nullable',
            'destinatario' => 'required|string|max:255',
            'remetente' => 'required|string|max:255',
            'data_documento' => 'required|date',
            'assunto' => 'required|string',
            'mensagem' => 'required|string',
            'template_id' => 'nullable|exists:templates,id',
            'arquivo_id' => 'nullable|exists:documentos,id',
        ]);

        $codigo = null;

        $docData = [
            'destinatario' => $request->destinatario,
            'remetente' => $request->remetente,
            'data_documento' => $request->data_documento,
            'assunto' => $request->assunto,
            'mensagem' => $request->mensagem,
            'categoria_id' => $categoria->id,
            'template_id' => $request->template_id,
            'arquivo_id' => $request->arquivo_id,
            'grupo_id' => $grupoId,
            'user_id' => Auth::id(),
        ];

        if($categoria->settings['controlar_sequencial']){
            $ano = date('Y');
            $ultimoDocumento = Documento::where('categoria_id', $categoria->id)
                ->where('grupo_id', $grupoId)
                ->where('ano', $ano)
                ->orderByDesc('id')
                ->first();

            $ultimoSequencial = $ultimoDocumento ? $ultimoDocumento->sequencial : null;

            $sequencial = $ultimoSequencial ? $ultimoSequencial + 1 : 1;
            $codigo = $this->gerarCodigo($categoria->prefixo, $sequencial, $ano);

            $docData['codigo'] = $codigo;
            $docData['sequencial'] = $sequencial;
            $docData['ano'] = $ano;
        } else {
            if($request->ano){
                $docData['ano'] = $request->ano;
            } else {
                $docData['ano'] = date('Y');
            }
            if($request->sequencial){
                $docData['sequencial'] = $request->sequencial;
            }
            if($request->codigo){
                $codigoExists = Documento::where('categoria_id', $categoria->id)
                    ->where('grupo_id', $grupoId)->where('codigo', $request->codigo)->exists();
                if(!$codigoExists){
                    $docData['codigo'] = $request->codigo;
                    $codigo = $request->codigo;
                }
            } elseif($request->ano && $request->sequencial){
                $codigo = $this->gerarCodigo($categoria->prefixo, $request->sequencial, $request->ano);
                $codigoExists = Documento::where('categoria_id', $categoria->id)
                    ->where('grupo_id', $grupoId)->where('codigo', $codigo)->exists();
                if(!$codigoExists){
                    $docData['codigo'] = $codigo;
                } else {
                    $codigo = null;
                }
            }
        }

        $documento = Documento::create($docData);

        if ($request->hasFile('arquivo')) {
            $file = $request->file('arquivo');
            $path = $file->store('documentos/anexos');
            $arquivo = $documento->arquivos()->create([
                'nome_original' => $file->getClientOriginalName(),
                'tamanho' => $file->getSize(),
                'tipo_mime' => $file->getClientMimeType(),
                'tipo_arquivo' => 'upload',
                'caminho' => $path,
                'user_id' => Auth::id(),
            ]);
            
            $documento->arquivo_id = $arquivo->id ?? null;
        }
        
        
        if(isset($categoria->email))
            Mail::to($categoria->email)->send(new DocumentCreated($documento));

        session()->flash('alert-success', 'Documento criado com sucesso! Código ' . ($codigo ?? $documento->codigo ?? 'não definido'));
        return redirect()->route('documento.show', $documento);
    }
 
    /**
     * Exibe um documento específico
     *
     * @param Documento $documento
     * @return \Illuminate\View\View
     */
    public function show(Documento $documento)
    {
        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        $this->verifyGrupo();
        if ($documento->categoria->grupo_id != session('grupo_id')) {
            abort(403, 'Documento não pertence ao grupo selecionado.');
        }

        $activities =  Activity::orderBy('created_at', 'DESC')->where('subject_id', $documento->id)->get();

        return view('documento.show', compact('documento', 'activities'));
    }

    /**
     * Exibe o formulário de edição de documento
     * 
     * @param Documento $documento
     * @return \Illuminate\View\View
     */
    public function edit(Documento $documento)
    {
        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        $this->verifyGrupo();
        if ($documento->categoria->grupo_id != session('grupo_id')) {
            abort(403, 'Documento não pertence ao grupo selecionado.');
        }

        if ($documento->finalizado) {
            session()->flash('alert-warning', 'Este documento já foi finalizado e não pode ser editado.');
            return redirect()->route('documento.show', $documento);
        }

        $categoria = $documento->categoria;
        $templates = $documento->categoria->templates;

        return view('documento.create', compact('documento', 'categoria', 'templates'));
    }

    /**
     * Atualiza os dados de um documento existente
     * 
     * @param Request $request
     * @param Documento $documento
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Documento $documento)
    {
        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        $this->verifyGrupo();
        if ($documento->categoria->grupo_id != session('grupo_id')) {
            abort(403, 'Documento não pertence ao grupo selecionado.');
        }

        if ($documento->finalizado) {
            session()->flash('alert-danger', 'Este documento já foi finalizado e não pode ser editado.');
            return redirect()->route('documento.show', $documento);
        }

        $request->validate([
            'prefixo' => 'string|max:255|nullable',
            'sequencial' => 'integer|nullable',
            'ano' => 'integer|nullable',
            'codigo' => 'string|max:255|nullable',
            'destinatario' => 'required|string|max:255',
            'remetente' => 'required|string|max:255',
            'data_documento' => 'required|date',
            'assunto' => 'required|string',
            'mensagem' => 'required|string',
            'template_id' => 'nullable|exists:templates,id',
        ]);
        
        $categoria = $documento->categoria;
        $original = $documento;

        $updateData = [
            'destinatario' => $request->destinatario,
            'remetente' => $request->remetente,
            'data_documento' => $request->data_documento,
            'assunto' => $request->assunto,
            'mensagem' => $request->mensagem,
            'template_id' => $request->template_id,
            'grupo_id' => $categoria->grupo_id,
        ];

        $codigo = null;

        if($categoria->settings['controlar_sequencial']){
            $ano = $documento->ano ?? date('Y');
            $sequencial = $documento->sequencial;

            if (!$sequencial) {
                $ultimoDocumento = Documento::where('categoria_id', $categoria->id)
                    ->where('grupo_id', $categoria->grupo_id)
                    ->where('ano', $ano)
                    ->orderByDesc('id')
                    ->first();

                $ultimoSequencial = $ultimoDocumento ? $ultimoDocumento->sequencial : null;

                $sequencial = $ultimoSequencial ? $ultimoSequencial + 1 : 1;
            }

            $codigo = $this->gerarCodigo($categoria->prefixo, $sequencial, $ano);

            $codigoExists = Documento::where('categoria_id', $categoria->id)
                ->where('grupo_id', $categoria->grupo_id)
                ->where('codigo', $codigo)
                ->where('id', '!=', $documento->id)
                ->exists();

            if ($codigoExists) {
                session()->flash('alert-danger', 'Já existe um documento com este código.');
                return redirect()->back()->withInput();
            }

            $updateData['sequencial'] = $sequencial;
            $updateData['ano'] = $ano;
            $updateData['codigo'] = $codigo;
        } else {
            if ($request->ano) {
                $updateData['ano'] = $request->ano;
            } else {
                $updateData['ano'] = date('Y');
            }
            if ($request->sequencial) {
                $updateData['sequencial'] = $request->sequencial;
            }
            if ($request->codigo) {
                $codigoExists = Documento::where('categoria_id', $categoria->id)
                    ->where('grupo_id', $categoria->grupo_id)
                    ->where('codigo', $request->codigo)
                    ->where('id', '!=', $documento->id)
                    ->exists();
                if ($codigoExists) {
                    session()->flash('alert-danger', 'Já existe um documento com este código.');
                    return redirect()->back()->withInput();
                }
                $updateData['codigo'] = $request->codigo;
                $codigo = $request->codigo;
            } elseif ($request->ano && $request->sequencial) {
                $codigo = $this->gerarCodigo($categoria->prefixo, $request->sequencial, $request->ano);
                $codigoExists = Documento::where('categoria_id', $categoria->id)
                    ->where('grupo_id', $categoria->grupo_id)
                    ->where('codigo', $codigo)
                    ->where('id', '!=', $documento->id)
                    ->exists();
                if ($codigoExists) {
                    session()->flash('alert-danger', 'Já existe um documento com este código.');
                    return redirect()->back()->withInput();
                }
                $updateData['codigo'] = $codigo;
            }
        }

        $documento->update($updateData);
        
        if(isset($categoria->email))
            Mail::to($categoria->email)->send(new DocumentUpdated($original, $documento));

        session()->flash('alert-success', 'Documento atualizado com sucesso! Código ' . ($codigo ?? $documento->codigo ?? 'não definido'));
        return redirect()->route('documento.show', $documento);
    }

    /**
     * Finaliza um documento
     * 
     * @param Documento $documento
     * @return \Illuminate\Http\RedirectResponse
     */
    public function finalizar(Documento $documento)
    {
        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        $this->verifyGrupo();
        if ($documento->categoria->grupo_id != session('grupo_id')) {
            abort(403, 'Documento não pertence ao grupo selecionado.');
        }

        if ($documento->finalizado) {
            session()->flash('alert-warning', 'Este documento já foi finalizado.');
            return redirect()->route('documento.show', $documento);
        }

        $documento->update([
            'finalizado' => true,
            'data_finalizacao' => now(),
            'finalizer_user_id' => Auth::id(),
        ]);

        session()->flash('alert-success', 'Documento finalizado com sucesso!');
        return redirect()->route('documento.show', $documento);
    }

    /**
     * Remove permanentemente um documento
     * 
     * @param Documento $documento
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Documento $documento)
    {
        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        $this->verifyGrupo();
        if ($documento->categoria->grupo_id != session('grupo_id')) {
            abort(403, 'Documento não pertence ao grupo selecionado.');
        }

        if ($documento->finalizado) {
            session()->flash('alert-danger', 'Não é possível excluir um documento finalizado.');
            return redirect()->route('documento.show', $documento);
        }

        foreach ($documento->arquivos as $arquivo) {
            Storage::delete($arquivo->caminho);
        }
        $categoria = $documento->categoria_id;
        $documento->delete();

        session()->flash('alert-success', 'Documento removido com sucesso!');
        return redirect()->route('categoria.show', $categoria);
    }

    public function detalharAtividade(Activity $activity)
    {
        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        $this->verifyGrupo();
        $documento = Documento::findOrFail($activity->subject_id);
        if ($documento->categoria->grupo_id != session('grupo_id')) {
            abort(403, 'Documento não pertence ao grupo selecionado.');
        }

        if(isset($activity->properties["arquivo"])){
            $arquivo = Arquivo::withTrashed()->where([
            'id' => $activity->properties['id']
            ])->first();
            $caminho = $arquivo->caminho;

            if (!Storage::exists($caminho)) {
                abort(404);
            }

            $nomeDownload = $arquivo->nome_original ?? basename($caminho);
            $nomeDownload = preg_replace('/[\x00-\x1F\x7F\/\\\\]/', '-', $nomeDownload);

            return Storage::download($caminho, $nomeDownload);
        }
        
        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $documento->categoria->grupo_id)) {
            abort(403, 'Você não tem permissão para visualizar este documento.');
        }

        $old = $activity->properties['old'] ?? [];
        $new = $activity->properties['attributes'] ?? [];

        if($old['mensagem'] !== $new['mensagem']){
            $texts = get_decorated_diff($old['mensagem'], $new['mensagem']);
            $old['mensagem'] = $texts['old'];
            $new['mensagem'] = $texts['new'];
        }

        return view('documento.atividade', compact('activity', 'old', 'new'));
    }

    public function gerarPdf(Documento $documento)
    {
        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        $this->verifyGrupo();
        if ($documento->categoria->grupo_id != session('grupo_id')) {
            abort(403, 'Documento não pertence ao grupo selecionado.');
        }

        $template = $documento->template;

        if (!$template) {
            return redirect()->back()->with('alert-danger', 'Documento não possui template associado.');
        }

        $variaveis = $documento->toArray();
        unset($variaveis['template']);
        $variaveis['data_documento'] = $documento->data_documento->format('d/m/Y');
        $variaveis['controle'] = 'Criado em ' . $documento->data_documento->format('d/m/Y') 
            . ' | Atualizado em ' . $documento->updated_at->format('d/m/Y H:i:s') . ' | Grupo: ' . $documento->grupo->name;

        $docName = $documento->categoria->grupo->name . '_' . $documento->codigo . '.pdf';

        $pdfContent = null;
        $arquivoHash = '';
        $caminhoArquivo = '';

        if ($template->arquivo) {
            $pdfgen = new Pdfgen();
            $pdfgen->setTemplate(storage_path('app/' . $template->arquivo));
            $pdfgen->setData($variaveis);
            $pdfgen->parse();
            $arquivoHash = $pdfgen->getHash($variaveis);
            $caminhoArquivo = 'documentos/gerados/' . $arquivoHash . '.pdf';
            $fullPath = Storage::path($caminhoArquivo);

            $arquivoExistente = Arquivo::where('documento_id', $documento->id)
                ->where('tipo_arquivo', 'gerado')
                ->where('caminho', $caminhoArquivo)
                ->first();

            if ($arquivoExistente && Storage::exists($caminhoArquivo)) {
                $pdfContent = Storage::get($caminhoArquivo);
            } else {
                $pdfgen->pdfBuild('F', ['paper'=>'a4', 'orientation' => 'portrait'], $fullPath);
                
                $pdfContent = Storage::get($caminhoArquivo);
                Arquivo::updateOrCreate(
                    [
                        'documento_id' => $documento->id,
                        'tipo_arquivo' => 'gerado',
                        'nome_original' => $docName,
                        'caminho' => $caminhoArquivo,
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
            
            $arquivoHash = md5($conteudo);
            $caminhoArquivo = 'documentos/gerados/' . $arquivoHash . '.pdf';
            $fullPath = Storage::path($caminhoArquivo);

            $arquivoExistente = Arquivo::where('documento_id', $documento->id)
                ->where('tipo_arquivo', 'gerado')
                ->where('caminho', $caminhoArquivo)
                ->first();

            if ($arquivoExistente && Storage::exists($caminhoArquivo)) {
                $pdfContent = Storage::get($caminhoArquivo);
            } else {
                $pdf = Pdf::loadHTML($conteudo);
                $pdf->save($fullPath); 
                
                $pdfContent = Storage::get($caminhoArquivo);

                Arquivo::updateOrCreate(
                    [
                        'documento_id' => $documento->id,
                        'tipo_arquivo' => 'gerado',
                        'nome_original' => $docName,
                        'caminho' => $caminhoArquivo,
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

    public function copy(Documento $documento)
    {
        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        $this->verifyGrupo();
        if ($documento->categoria->grupo_id != session('grupo_id')) {
            abort(403, 'Documento não pertence ao grupo selecionado.');
        }

        $categoria = $documento->categoria;
        $grupoId = $documento->grupo_id;

        $novoDocumento = $documento->replicate(['sequencial', 'ano', 'codigo', 'created_at', 'updated_at']);
        $novoDocumento->finalizado = false;
        $novoDocumento->data_finalizacao = null;
        $novoDocumento->finalizer_user_id = null;

        $ano = date('Y');
        $ultimoDocumento = Documento::where('categoria_id', $categoria->id)
            ->where('grupo_id', $grupoId)
            ->where('ano', $ano)
            ->orderByDesc('sequencial')
            ->first();

        $ultimoSequencial = $ultimoDocumento ? $ultimoDocumento->sequencial : null;

        $sequencial = $ultimoSequencial ? $ultimoSequencial + 1 : 1;
        $novoDocumento->ano = $ano;
        $novoDocumento->sequencial = $sequencial;
        $novoDocumento->codigo = $this->gerarCodigo($categoria->prefixo, $sequencial, $ano);

        $novoDocumento->save();

        if(isset($categoria->email))
            Mail::to($categoria->email)->send(new DocumentCreated($novoDocumento));

        session()->flash('alert-success', 'Documento copiado com sucesso! Novo código: ' . ($novoDocumento->codigo ?? 'não definido'));
        return redirect()->route('documento.edit', ['documento' => $novoDocumento]);
    }

}
