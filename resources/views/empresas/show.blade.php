@extends('layouts.app')

@php
    use Illuminate\Support\Str;
@endphp

<head>
    <link rel="stylesheet" href="{{ asset('css/empresas/show.css') }}">
</head>

@section('content')
<div class="container details-container">
    <h1 class="text-center">Detalhes da Empresa</h1>

    <div class="card mt-4">
        <div class="card-header">
            <strong>{{ $empresa->nome }}</strong>
        </div>

        <div class="card-body">
            <!-- Dados da Empresa -->
            <div class="details-section">
                <p><strong>CNPJ:</strong> 
                    {{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $empresa->cnpj) }}
                </p>
                <p><strong>Email:</strong> {{ $empresa->email }}</p>
                <p><strong>Telefone:</strong> {{ $empresa->telefone }}</p>
                <p><strong>Endereço:</strong> 
                    {{ $empresa->endereco_rua }}, {{ $empresa->endereco_numero }} 
                    - {{ $empresa->endereco_bairro }},
                    {{ $empresa->endereco_cidade }} - {{ $empresa->endereco_estado }} 
                    - CEP: {{ $empresa->endereco_cep }}
                </p>
            </div>

            <!-- Lista de Documentos -->
            <div class="mt-3">
                <h5>Documentação:</h5>

                @if ($empresa->documentos->isNotEmpty())
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome do Arquivo</th>
                                <th>Última Modificação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($empresa->documentos as $documento)
                                @php
                                    // Corrige o caminho caso venha com "public/" no início
                                    $filePath = $documento->caminho_arquivo;
                                    if (Str::startsWith($filePath, 'public/')) {
                                        $filePath = Str::replaceFirst('public/', '', $filePath);
                                    }
                                @endphp

                                <tr>
                                    <td>
                                        <a href="{{ asset('storage/' . $filePath) }}" 
                                           target="_blank" 
                                           class="file-link text-decoration-none">
                                           📄 {{ $documento->nome_arquivo }}
                                        </a>
                                    </td>
                                    <td>{{ $documento->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>Nenhum documento anexado.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Botões -->
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('empresas.index') }}" class="btn btn-secondary">Voltar</a>
        <a href="{{ route('empresas.edit', $empresa->id) }}" class="btn btn-primary">Editar</a>
    </div>
</div>
@endsection
