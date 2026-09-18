@extends('layouts.app')
@section('title','Configurações')
@push('styles')<link rel="stylesheet" href="{{ asset('css/settings.css') }}?v={{ filemtime(public_path('css/settings.css')) }}"><link rel="stylesheet" href="{{ asset('css/settings-mail.css') }}?v={{ filemtime(public_path('css/settings-mail.css')) }}"><link rel="stylesheet" href="{{ asset('css/settings-validity.css') }}?v={{ filemtime(public_path('css/settings-validity.css')) }}"><link rel="stylesheet" href="{{ asset('css/settings-recalc.css') }}?v={{ filemtime(public_path('css/settings-recalc.css')) }}">@endpush
@section('content')
<div class="settings-page">
 <header class="settings-header"><div><span class="eyebrow">Administração</span><h1>Configurações</h1><p>Controle as rotinas documentais e a integração de e-mail.</p></div></header>
 @if(session('success'))<div class="settings-alert success">{{ session('success') }}</div>@endif
 @if($errors->any())<div class="settings-alert danger">{{ $errors->first() }}</div>@endif

 <section class="settings-card job-card">
  <div class="job-copy"><span class="settings-icon"><i class="bi bi-arrow-repeat"></i></span><div><h2>Automação documental</h2><p>Atualiza validades às 00:10 e envia alertas por e-mail às 07:00.</p><span class="job-state {{ $jobAtivo?'on':'off' }}"><i class="bi bi-circle-fill"></i>{{ $jobAtivo?'Job ativo':'Job desativado' }}</span></div></div>
  <div class="job-actions"><form method="POST" action="{{ route('configuracoes.job') }}">@csrf @method('PUT')<input type="hidden" name="ativo" value="{{ $jobAtivo?0:1 }}"><button class="button {{ $jobAtivo?'secondary':'primary' }}">{{ $jobAtivo?'Desativar job':'Ativar job' }}</button></form><form method="POST" action="{{ route('configuracoes.job.executar') }}">@csrf<button class="button ghost"><i class="bi bi-play-fill"></i> Executar agora</button></form></div>
 </section>

 <div class="configuration-grid">
  <section class="settings-card">
   <div class="card-heading"><h2><i class="bi bi-envelope"></i> Servidor de e-mail</h2><p>Servidor SMTP interno usado para enviar os alertas documentais.</p></div>
   <form class="settings-form" method="POST" action="{{ route('configuracoes.email') }}">@csrf @method('PUT')
    <label class="toggle full"><input type="hidden" name="ativo" value="0"><input type="checkbox" name="ativo" value="1" {{ old('ativo',$email['ativo'])?'checked':'' }}><span>Ativar envio de e-mails</span></label>
    <label>Servidor SMTP<input name="host" value="{{ old('host',$email['host']) }}" required placeholder="smtp.office365.com"></label>
    <label>Porta<input type="number" name="porta" value="{{ old('porta',$email['porta']) }}" required></label>
    <label>Criptografia<select name="criptografia" required><option value="tls" {{ old('criptografia',$email['criptografia'])==='tls'?'selected':'' }}>TLS</option><option value="ssl" {{ old('criptografia',$email['criptografia'])==='ssl'?'selected':'' }}>SSL</option></select></label>
    <label>Conta de e-mail<input type="email" name="usuario" value="{{ old('usuario',$email['usuario']) }}" required placeholder="usuario@empresa.com.br"></label>
    <label class="full">Senha ou senha de aplicativo<input type="password" name="senha" autocomplete="new-password" placeholder="{{ $email['possui_senha']?'Senha já configurada — deixe vazio para manter':'Informe a senha' }}"></label>
    <label>E-mail remetente<input type="email" name="remetente_email" value="{{ old('remetente_email',$email['remetente_email']) }}" required></label>
    <label>Nome do remetente<input name="remetente_nome" value="{{ old('remetente_nome',$email['remetente_nome']) }}" required></label>
    <div class="form-note full"><i class="bi bi-shield-lock"></i>A senha é criptografada antes de ser armazenada. Confirme com a TI a porta, a criptografia e se o servidor SMTP interno exige autenticação.</div>
    <div class="settings-actions full"><button class="button primary"><i class="bi bi-check-lg"></i> Salvar servidor</button></div>
   </form>
   <form class="test-mail" method="POST" action="{{ route('configuracoes.email.testar') }}">@csrf<button class="button secondary"><i class="bi bi-send"></i> Enviar teste para meu e-mail</button></form>
  </section>

  <section class="settings-card">
   <div class="card-heading"><h2><i class="bi bi-bell"></i> Antecedência dos alertas</h2><p>Defina quando um documento deve aparecer em cada nível de aviso.</p></div>
   <form class="settings-form alert-days" method="POST" action="{{ route('configuracoes.alertas') }}">@csrf @method('PUT')
    <label>Alerta crítico<input type="number" min="1" max="365" name="critico" value="{{ old('critico',$alertas['critico']) }}" required><small>dias antes</small></label>
    <label>Alerta de atenção<input type="number" min="1" max="365" name="atencao" value="{{ old('atencao',$alertas['atencao']) }}" required><small>dias antes</small></label>
    <label>Alerta preventivo<input type="number" min="1" max="365" name="preventivo" value="{{ old('preventivo',$alertas['preventivo']) }}" required><small>dias antes</small></label>
    <div class="form-note full"><i class="bi bi-info-circle"></i>Estes campos alteram somente a antecedência dos avisos, não a data de validade já registrada.</div>
    <div class="settings-actions full"><button class="button primary"><i class="bi bi-check-lg"></i> Salvar prazos</button></div>
   </form>
  </section>
 </div>
 <section class="settings-card validity-card">
  <div class="card-heading"><h2><i class="bi bi-calendar-range"></i> Validade dos documentos</h2><p>Defina por quantos dias ou meses um documento novo ou renovado permanecerá válido.</p></div>
  <form class="settings-form validity-form" method="POST" action="{{ route('configuracoes.validade') }}">@csrf @method('PUT')
   <label>Quantidade<input type="number" min="1" max="3650" name="quantidade" value="{{ old('quantidade',$validade['quantidade']) }}" required></label>
   <label>Unidade<select name="unidade" required><option value="meses" {{ old('unidade',$validade['unidade'])==='meses'?'selected':'' }}>Meses</option><option value="dias" {{ old('unidade',$validade['unidade'])==='dias'?'selected':'' }}>Dias</option></select></label>
   <label class="toggle recalculate"><input type="hidden" name="recalcular_existentes" value="0"><input type="checkbox" name="recalcular_existentes" value="1" checked><span>Recalcular documentos existentes</span></label>
   <div class="form-note"><i class="bi bi-info-circle"></i>Com a opção marcada, a validade será recalculada desde a data de cadastro de cada documento e os já vencidos serão bloqueados imediatamente.</div>
   <div class="settings-actions"><button class="button primary"><i class="bi bi-check-lg"></i> Salvar validade</button></div>
  </form>
 </section>
</div>
@endsection
