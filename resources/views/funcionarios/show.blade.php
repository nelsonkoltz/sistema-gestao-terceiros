@extends('layouts.app')
@section('title','Detalhes do Funcionário')

<head>
  <link rel="stylesheet" href="{{ asset('css/funcionarios/show.css') }}">
</head>

@section('content')
<div class="container show-container">
    <h1>Detalhes do Funcionário</h1>

    @includeWhen(View::exists('partials.flash'), 'partials.flash')

    <div class="card mt-3">
        <div class="card-body">
            {{-- Cabeçalho / Resumo --}}
            <div class="top-row">
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="title-block">
                    <div class="name">{{ $funcionario->nome }}</div>
                    <div class="sub">
                        CPF:
                        <strong>
                            {{ method_exists($funcionario,'getCpfFormatadoAttribute') ? $funcionario->cpf_formatado : $funcionario->cpf }}
                        </strong>
                        &nbsp;&middot;&nbsp;
                        Empresa:
                        <strong>
                            @if($funcionario->empresa)
                                @if(Route::has('empresas.show'))
                                    <a href="{{ route('empresas.show', $funcionario->empresa_id) }}">
                                        {{ $funcionario->empresa->nome }}
                                    </a>
                                @else
                                    {{ $funcionario->empresa->nome }}
                                @endif
                            @else
                                —
                            @endif
                        </strong>
                    </div>
                </div>
                <div class="status">
                    @if($funcionario->ativo)
                        <span class="badge badge--ok">Ativo</span>
                    @else
                        <span class="badge badge--muted">Inativo</span>
                    @endif
                </div>
            </div>

            {{-- Grid de detalhes --}}
            <div class="details-grid mt-3">
                <div class="detail">
                    <div class="label">ID</div>
                    <div class="value">{{ $funcionario->id }}</div>
                </div>
                <div class="detail">
                    <div class="label">Empresa</div>
                    <div class="value">
                        {{ $funcionario->empresa->nome ?? '—' }}
                    </div>
                </div>
                <div class="detail">
                    <div class="label">Criado em</div>
                    <div class="value">{{ optional($funcionario->created_at)->format('d/m/Y H:i') }}</div>
                </div>
                <div class="detail">
                    <div class="label">Atualizado em</div>
                    <div class="value">{{ optional($funcionario->updated_at)->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            {{-- Documentos --}}
            <h2 class="h6 mt-4 mb-2">Documentos</h2>
            @if($funcionario->documentos->isEmpty())
                <div class="doc-empty">Nenhum documento enviado.</div>
            @else
                <ul class="doc-list list-group">
                    @foreach($funcionario->documentos as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center doc-item">
                            <span class="doc-left">
                                <i class="far fa-file mr-2"></i>
                                <a class="doc-link" href="{{ asset('storage/' . ($doc->path ?? $doc->caminho_arquivo)) }}" target="_blank">
                                    {{ $doc->nome_original ?? basename($doc->nome_arquivo ?? 'arquivo') }}
                                </a>
                                <small class="doc-meta">
                                    {{ $doc->mime ?? '—' }} •
                                    {{ $doc->tamanho ? number_format($doc->tamanho/1024,1,',','.') . ' KB' : '—' }}
                                </small>
                            </span>
                            <span class="doc-actions">
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- Ações --}}
            <div class="actions mt-4">
                <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary">Voltar</a>
                <a href="{{ route('funcionarios.edit', $funcionario->id) }}" class="btn btn-primary">Editar</a>
            </div>
        </div>
    </div>
</div>
@endsection
