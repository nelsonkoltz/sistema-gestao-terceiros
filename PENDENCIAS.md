# Situação atual e pendências — TerceirosCR

Atualizado em 18/09/2026.

## 1. Objetivo e fluxo vigente

O TerceirosCR controla o acesso de trabalhadores terceirizados à empresa:

1. A Segurança do Trabalho cadastra empresas, funcionários e documentos.
2. O Solicitante cadastra o serviço de uma empresa terceirizada.
3. Qualquer funcionário ativo e documentalmente regular dessa empresa pode executar o serviço.
4. A Guarita consulta o funcionário e recebe a decisão automática de acesso.
5. A Guarita registra entrada e saída, preservando o histórico.

## 2. Decisões de negócio

- Não existe aprovação manual de documentos ou solicitações.
- Documento novo ou renovado fica ativo imediatamente.
- A validade começa no cadastro ou renovação e pode ser configurada em dias ou meses.
- A configuração inicial recomendada é de seis meses.
- O Administrador pode recalcular documentos existentes ao alterar o prazo.
- Solicitações novas começam como **Agendado**.
- A autorização vale para qualquer funcionário regular vinculado à empresa do serviço.
- A entrada exige funcionário ativo, documentação regular e serviço vigente.
- Fotografias ficam reservadas para um projeto futuro.

## 3. Perfis implementados

### Administrador

- acesso completo;
- usuários, configurações e auditoria;
- solicitações, cadastros e portaria.

### Solicitante

- cria, consulta, edita e exclui somente as próprias solicitações;
- não acessa cadastros documentais, configurações ou portaria.

### Segurança do Trabalho

- mantém empresas, funcionários e documentos;
- acompanha alertas documentais;
- consulta serviços, sem criar, editar ou excluir solicitações.

### Guarita

- consulta funcionário, decisão de acesso e serviço relacionado;
- registra entrada e saída;
- consulta e exporta o histórico da portaria.

As permissões são aplicadas nas rotas do servidor, inclusive para URLs digitadas manualmente.

## 4. Funcionalidades concluídas

### Estrutura multi-filial

- [x] Cadastro administrativo de filiais ativas ou inativas.
- [x] Criação automática da Matriz para preservar os dados existentes.
- [x] Vínculo de usuários com uma ou várias filiais e definição da filial principal.
- [ ] Seleção da filial operacional no cabeçalho.
- [ ] Separação de solicitações, portaria, ocorrências e relatórios por filial.
- [ ] Painel consolidado de todas as filiais para o Administrador.

### Autenticação e usuários

- [x] Login único e redirecionamento conforme o perfil.
- [x] Cadastro de usuário, setor, e-mail, senha e permissão.
- [x] Limitação de tentativas de login.
- [x] Matriz de permissões e testes por perfil.
- [x] Solicitante limitado às próprias solicitações.

### Empresas, funcionários e documentos

- [x] Cadastro, edição, consulta e pesquisa de empresas e funcionários.
- [x] Funcionário vinculado a uma empresa e com status ativo/inativo.
- [x] Gestão restrita ao Administrador e à Segurança do Trabalho.
- [x] Documentos livres, sem tipos ou campos fixos.
- [x] Upload de PDF, JPG e PNG para empresa ou funcionário.
- [x] Ativação imediata e validade automática configurável.
- [x] Renovação preservando a versão anterior.
- [x] Identificação de documentos vencidos, próximos do vencimento e ausentes.
- [x] Job automático configurável e execução manual.

### Alertas

- [x] Painel com filtros e níveis de antecedência configuráveis.
- [x] Resumo por e-mail manual e automático, mesmo sem pendências.
- [x] Envio para Administradores e Segurança do Trabalho com e-mail.
- [x] Configuração de SMTP interno e senha criptografada.
- [x] Modelo de mensagem compatível com Outlook.

### Solicitações

- [x] Empresa, setor, solicitante, descrição, data e horários.
- [x] Informação sobre uso do refeitório.
- [x] Status Agendado, Em Andamento e Finalizado sincronizado com a portaria.
- [x] Autorização para qualquer funcionário regular da empresa.
- [x] Consulta dos serviços pela Segurança do Trabalho.
- [x] Exibição do serviço para a Guarita.

### Portaria

- [x] Tela operacional e pesquisa por nome ou CPF.
- [x] Decisão automática de entrada e motivos do bloqueio.
- [x] Registro de entrada, saída, data, hora e operador.
- [x] Prevenção de duas entradas abertas para a mesma pessoa.
- [x] Pessoas atualmente dentro da empresa.
- [x] Histórico pesquisável e exportação CSV.

### Auditoria

- [x] Criação, alteração e exclusão dos principais cadastros.
- [x] Responsável, data, hora, IP e navegador.
- [x] Valores anteriores e novos, com proteção de dados sensíveis.
- [x] Consulta e detalhes exclusivos do Administrador.

## 5. Pendências prioritárias

### Empresa ativa ou inativa

- [x] Adicionar status ativo/inativo à empresa.
- [x] Bloquear todos os funcionários quando a empresa estiver inativa.
- [x] Exibir motivo, responsável e data da inativação.

### Preservação do histórico

- [x] Impedir exclusão definitiva de empresas com serviços ou acessos.
- [x] Impedir exclusão definitiva de funcionários com acessos.
- [x] Impedir exclusão definitiva de serviços com acessos.
- [x] Usar inativação, cancelamento ou exclusão lógica nesses casos.
- [x] Garantir acesso ao histórico de versões documentais.

### Tentativas bloqueadas e ocorrências

- [x] Registrar cada tentativa de entrada bloqueada.
- [x] Guardar pessoa, empresa, serviço, motivos, operador, data, hora e IP.
- [x] Permitir observação na entrada e na saída.
- [x] Permitir ocorrência sem liberar a entrada.
- [x] Criar consulta e relatório de ocorrências.

### Cancelamento de solicitações

- [x] Criar ação própria de cancelamento.
- [x] Exigir motivo e registrar responsável, data e hora.
- [x] Bloquear novas entradas sem apagar o histórico.

### Contas de usuário

- [x] Adicionar status ativo/inativo e bloquear login de conta inativa.
- [x] Permitir troca de senha pelo usuário.
- [x] Implementar redefinição segura de senha.
- [x] Exigir troca da senha provisória no primeiro acesso.
- [x] Definir tempo de sessão configurável (a política mínima de senha já foi implementada).

## 6. Melhorias operacionais

### Serviços

- [ ] Permitir serviços com data inicial e final.
- [ ] Adicionar veículo e placa opcionais.
- [ ] Adicionar observações específicas para a Guarita.
- [ ] Avaliar serviços recorrentes.

### Portaria

- [ ] Pesquisar por empresa, solicitação e placa.
- [ ] Alertar permanência após o horário autorizado.
- [ ] Destacar registros sem saída após a troca de dia.
- [ ] Corrigir entrada ou saída somente com justificativa e auditoria.

### Relatórios

- [ ] Entradas e saídas por período.
- [ ] Tentativas bloqueadas e motivos.
- [ ] Tempo de permanência.
- [ ] Serviços por empresa, setor e solicitante.
- [ ] Cadastros irregulares e documentos próximos do vencimento.
- [ ] Exportação em Excel e PDF, se necessária.

### Auditoria e segurança

- [x] Registrar login, logout e falhas de autenticação.
- [ ] Registrar consultas e bloqueios da Guarita.
- [ ] Registrar execuções manuais e automáticas dos jobs.
- [x] Filtrar auditoria por usuário, IP e período.
- [ ] Definir prazo de retenção da auditoria.
- [ ] Revisar e atualizar Laravel e dependências.

## 7. Infraestrutura antes da produção

- [ ] Definir servidor e endereço oficial na rede interna.
- [ ] Configurar HTTPS.
- [ ] Separar desenvolvimento, homologação e produção.
- [ ] Fazer backup automático do banco e dos documentos.
- [ ] Definir retenção externa e testar restauração.
- [ ] Monitorar sistema, jobs, SMTP e espaço em disco.
- [ ] Centralizar logs e proteger os segredos do ambiente.
- [ ] Definir responsáveis por suporte e incidentes.

## 8. Testes pendentes

- [x] Perfis não acessam ações não autorizadas.
- [x] Solicitante acessa somente as próprias solicitações.
- [x] Segurança consulta serviços sem alterá-los.
- [x] Guarita registra entrada e saída.
- [x] Entrada e saída atualizam o serviço.
- [x] Serviço permite qualquer funcionário regular da empresa.
- [x] Empresa inativa bloqueia todos os funcionários.
- [ ] Documento bloqueia exatamente na data limite.
- [ ] Renovação preserva corretamente a versão anterior.
- [ ] Limites de data e horário do serviço são respeitados.
- [ ] Concorrência impede duas entradas simultâneas.
- [ ] Falha no SMTP não interrompe a validade documental.
- [ ] Backup completo pode ser restaurado em ambiente limpo.

## 9. Projeto futuro

- [ ] Fotografia do funcionário para conferência na Guarita.
- [ ] QR Code ou crachá temporário.
- [ ] Integração com catraca ou controle físico de acesso.
- [ ] Integração com diretório corporativo.
- [ ] Portal externo para envio de documentos pelas terceirizadas.

## 10. Próxima entrega recomendada

Implementar em conjunto:

1. status ativo/inativo da empresa;
2. bloqueio de funcionários de empresa inativa;
3. preservação de registros com histórico;
4. registro das tentativas de entrada bloqueadas.

Essa entrega impede acessos de empresas suspensas sem perder as evidências da decisão tomada pela portaria.
