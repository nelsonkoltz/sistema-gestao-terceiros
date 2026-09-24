@php
    $vinculadas = collect(old('filiais', isset($usuario) ? $usuario->filiais->pluck('id')->all() : $filiais->take(1)->pluck('id')->all()))->map(fn($id)=>(int)$id);
    $principalAtual = (int) old('filial_principal_id', isset($usuario) ? optional($usuario->filiais->firstWhere('pivot.principal', true))->id : $vinculadas->first());
@endphp
<fieldset class="branch-access form-group full">
    <legend>Filiais permitidas</legend>
    <p>Marque todas as unidades em que este usuário poderá trabalhar.</p>
    <div class="branch-options">
        @forelse($filiais as $filial)
        <label class="branch-option">
            <input type="checkbox" name="filiais[]" value="{{ $filial->id }}" {{ $vinculadas->contains($filial->id) ? 'checked' : '' }}>
            <span><strong>{{ $filial->nome }}</strong><small>{{ $filial->codigo }}{{ $filial->ativa ? '' : ' · Inativa' }}</small></span>
        </label>
        @empty
        <span class="branch-empty">Cadastre uma filial antes de criar novos usuários.</span>
        @endforelse
    </div>
    <label class="principal-label">Filial principal
        <select name="filial_principal_id" class="form-control">
            @foreach($filiais as $filial)<option value="{{ $filial->id }}" {{ $principalAtual === $filial->id ? 'selected' : '' }}>{{ $filial->nome }}</option>@endforeach
        </select>
    </label>
    <small>A filial principal será selecionada automaticamente quando o usuário entrar no sistema.</small>
</fieldset>
