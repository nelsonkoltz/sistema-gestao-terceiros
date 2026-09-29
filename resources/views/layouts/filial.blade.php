@php($filiaisDisponiveis = request()->attributes->get('filiais_disponiveis', collect()))
<div class="filial-toolbar">
    @if($filiaisDisponiveis->isNotEmpty())
        <form method="POST" action="{{ route('filial.selecionar') }}">
            @csrf
            <label for="filial-ativa">Filial operacional</label>
            <select id="filial-ativa" name="filial_id">
                @foreach($filiaisDisponiveis as $filialOpcao)
                    <option value="{{ $filialOpcao->id }}" @selected($filialOpcao->id === request()->attributes->get('filial_ativa_id'))>{{ $filialOpcao->nome }}{{ $filialOpcao->ativa ? '' : ' (inativa — somente consulta)' }}</option>
                @endforeach
            </select>
            <button type="submit">Selecionar</button>
        </form>
    @else
        <span>Nenhuma filial ativa vinculada. Solicite acesso ao administrador.</span>
    @endif
    @if(auth()->user()->permissao === 'Administrador')
        <a href="{{ route('home', ['consolidado' => 1]) }}">Painel de todas as filiais</a>
    @endif
</div>
