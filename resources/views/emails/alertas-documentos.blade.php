<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Alertas documentais</title></head>
<body style="margin:0;padding:0;background-color:#eef3f7;font-family:Arial,Helvetica,sans-serif;color:#24384b">
<div style="display:none;max-height:0;overflow:hidden;opacity:0">Resumo de validade dos documentos no TerceirosCR.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;background-color:#eef3f7">
<tr><td align="center" style="padding:32px 12px">
 <table role="presentation" width="680" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:680px;background-color:#ffffff;border-collapse:separate;border-spacing:0;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(8,49,81,.10)">
  <tr><td style="padding:28px 32px;background-color:#083f6b">
   <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
    <td><div style="font-size:11px;font-weight:bold;letter-spacing:1.5px;color:#a9cbe2;text-transform:uppercase">TerceirosCR</div><h1 style="margin:7px 0 0;font-size:25px;line-height:1.2;color:#ffffff">Alertas documentais</h1></td>
    <td align="right" style="font-size:12px;color:#d6e7f2">{{ now()->format('d/m/Y') }}</td>
   </tr></table>
  </td></tr>
  <tr><td style="padding:28px 32px 18px">
   <p style="margin:0 0 8px;font-size:16px;font-weight:bold;color:#153a57">Olá, {{ $destinatario->name }}.</p>
   <p style="margin:0;font-size:13px;line-height:1.6;color:#607286">Este é o resumo automático da situação documental de empresas e funcionários terceirizados.</p>
  </td></tr>
  <tr><td style="padding:0 32px 24px">
   <table role="presentation" width="100%" cellpadding="0" cellspacing="8" style="border-collapse:separate">
    <tr>
     <td align="center" style="padding:14px 6px;border-radius:10px;background:#fff0f1"><strong style="display:block;font-size:21px;color:#a72c36">{{ $vencidos }}</strong><span style="font-size:10px;color:#7b5960">Vencidos</span></td>
     <td align="center" style="padding:14px 6px;border-radius:10px;background:#fff8e5"><strong style="display:block;font-size:21px;color:#80600b">{{ $vencendo7 + $vencendo15 + $vencendo30 }}</strong><span style="font-size:10px;color:#796c49">Próximos</span></td>
     <td align="center" style="padding:14px 6px;border-radius:10px;background:#f0f4f7"><strong style="display:block;font-size:21px;color:#466078">{{ $semDocumentos }}</strong><span style="font-size:10px;color:#687b8c">Sem documentos</span></td>
    </tr>
   </table>
  </td></tr>
  @if($itens->isEmpty())
  <tr><td style="padding:0 32px 28px"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:25px;border:1px solid #bde4cc;border-radius:12px;background:#eaf8ef">
   <div style="margin-bottom:8px;font-size:26px;color:#19734a">✓</div><strong style="display:block;font-size:16px;color:#176b43">Documentação regular</strong><span style="display:block;margin-top:6px;font-size:12px;line-height:1.5;color:#4f7964">Nenhuma pendência documental foi encontrada nesta verificação.</span>
  </td></tr></table></td></tr>
  @else
  <tr><td style="padding:0 32px 12px"><h2 style="margin:0;font-size:14px;color:#153e5e">Documentos que precisam de atenção</h2><p style="margin:5px 0 0;font-size:11px;color:#7a8997">{{ $itens->count() }} registro(s) encontrado(s)</p></td></tr>
  <tr><td style="padding:0 32px 25px">
   <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;border:1px solid #dfe7ed;border-radius:11px;border-collapse:separate;overflow:hidden">
    <tr style="background:#f4f7f9"><th align="left" style="padding:11px 12px;border-bottom:1px solid #dfe7ed;font-size:9px;letter-spacing:.6px;color:#627487;text-transform:uppercase">Cadastro</th><th align="left" style="padding:11px 12px;border-bottom:1px solid #dfe7ed;font-size:9px;letter-spacing:.6px;color:#627487;text-transform:uppercase">Documento</th><th align="left" style="padding:11px 12px;border-bottom:1px solid #dfe7ed;font-size:9px;letter-spacing:.6px;color:#627487;text-transform:uppercase">Situação</th><th align="left" style="padding:11px 12px;border-bottom:1px solid #dfe7ed;font-size:9px;letter-spacing:.6px;color:#627487;text-transform:uppercase">Validade</th></tr>
    @foreach($itens->take(100) as $item)
    @php($vencido=$item['situacao']==='Vencido')
    <tr>
     <td style="padding:13px 12px;border-bottom:1px solid #edf1f4;font-size:11px;color:#263d51"><strong>{{ $item['nome'] }}</strong><span style="display:block;margin-top:3px;font-size:9px;color:#7b8998">{{ $item['empresa'] }}</span></td>
     <td style="padding:13px 12px;border-bottom:1px solid #edf1f4;font-size:10px;color:#42566a">{{ $item['arquivo'] }}</td>
     <td style="padding:13px 12px;border-bottom:1px solid #edf1f4"><span style="display:inline-block;padding:5px 8px;border-radius:20px;background:{{ $vencido?'#fde7e9':'#fff3cf' }};font-size:9px;font-weight:bold;color:{{ $vencido?'#a82b35':'#775b0d' }}">{{ $item['situacao'] }}</span></td>
     <td style="padding:13px 12px;border-bottom:1px solid #edf1f4;font-size:10px;color:#42566a;white-space:nowrap">{{ $item['validade'] ? $item['validade']->format('d/m/Y') : '—' }}</td>
    </tr>
    @endforeach
   </table>
  </td></tr>
  @endif
  <tr><td align="center" style="padding:4px 32px 30px">
   <a href="{{ route('alertas-documentos.index') }}" style="display:inline-block;padding:13px 20px;border-radius:9px;background:#083f6b;color:#ffffff;font-size:12px;font-weight:bold;text-decoration:none">Abrir central de alertas&nbsp; →</a>
  </td></tr>
  <tr><td align="center" style="padding:18px 24px;background:#f6f8fa;border-top:1px solid #e4eaef;font-size:10px;line-height:1.5;color:#7a8997">Mensagem automática enviada pelo TerceirosCR.<br>Não responda a este e-mail.</td></tr>
 </table>
</td></tr></table>
</body>
</html>
