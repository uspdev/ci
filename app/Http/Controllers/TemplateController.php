<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;

class TemplateController extends Controller
{
    public function index()
    {
        $this->authorize('grupoManager');
        \UspTheme::activeUrl('templates');

        if (Gate::allows('manager')) {
            $templates = Template::with('categorias')->get();
        } else {
            $templates = Template::where('user_id', Auth::id())->with('categorias')->get();
        }

        return view('template.index', compact('templates'));
    }

    public function create()
    {
        $this->authorize('grupoManager');
        $categorias = Categoria::all();

        return view('template.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $this->authorize('grupoManager');

        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'conteudo_padrao' => 'required_without:file|string',
            'variaveis' => 'nullable|json',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
            'arquivo' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('arquivo')) {
            $filePath = $request->file('arquivo')->store('templates', 'public');
        }

        $template = Template::create([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'conteudo_padrao' => $request->conteudo_padrao,
            'variaveis' => $request->variaveis ? json_decode($request->variaveis, true) : null,
            'user_id' => Auth::id(),
            'arquivo' => $filePath,
        ]);

        if ($request->has('categorias')) {
            $template->categorias()->sync($request->categorias);
        }

        session()->flash('alert-success', 'Template criado com sucesso!');
        return redirect()->route('template.edit', $template);
    }

    public function show(Template $template)
    {
        if (!Gate::allows('manager') && $template->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para visualizar este template.');
        }

        return view('template.show', compact('template'));
    }

    public function edit(Template $template)
    {
        if (!Gate::allows('manager') && $template->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para editar este template.');
        }

        $categorias = Categoria::all();

        return view('template.create', compact('template', 'categorias'));
    }

    public function update(Request $request, Template $template)
    {
        if (!Gate::allows('manager') && $template->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para editar este template.');
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'conteudo_padrao' => 'required|string',
            'variaveis' => 'nullable|json',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
            'arquivo' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('arquivo')) {
            $filePath = $request->file('arquivo')->store('templates', 'public');
        }

        $template->update([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'conteudo_padrao' => $request->conteudo_padrao,
            'variaveis' => $request->variaveis ? json_decode($request->variaveis, true) : null,
            'arquivo' => $filePath ?? $template->arquivo,
        ]);

        if ($request->has('categorias')) {
            $template->categorias()->sync($request->categorias);
        } else {
            $template->categorias()->detach();
        }

        session()->flash('alert-success', 'Template atualizado com sucesso!');
        return redirect()->route('template.edit', $template);
    }

    public function destroy(Template $template)
    {
        if (!Gate::allows('manager') && $template->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para excluir este template.');
        }

        $template->categorias()->detach();
        $template->delete();

        session()->flash('alert-success', 'Template removido com sucesso!');
        return redirect()->route('template.index');
    }

    public function gerarPdf(Template $template)
    {
        if($template->arquivo){
            return \Illuminate\Support\Facades\Storage::disk('public')->download($template->arquivo);
        } 
        
        $conteudo = $template->conteudo_padrao;
        if (is_array($template->variaveis)) {
            foreach ($template->variaveis as $chave => $tipo) {
                $valor = strtoupper($chave);
                $conteudo = str_replace('{{ ' . $chave . ' }}', $valor, $conteudo);
            }
        }

        $pdf = pdf::loadHTML($conteudo);
        
        return $pdf->download('template_' . $template->id . '.pdf');
    }
}
