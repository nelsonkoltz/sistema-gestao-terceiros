# Pendências e regras de negócio — TerceirosCR

Este documento reúne as funcionalidades que ainda precisam ser desenvolvidas para que o TerceirosCR controle a entrada de trabalhadores terceirizados com segurança, rastreabilidade e clareza para a guarita.

## 1. Objetivo do sistema

O sistema deve permitir que:

- a Segurança do Trabalho cadastre e mantenha as empresas terceirizadas e seus documentos;
- os colaboradores da empresa solicitem serviços que serão executados por terceiros;
- qualquer funcionário ativo e documentalmente regular da empresa solicitada possa ser autorizado;
- a guarita consulte rapidamente se uma pessoa está autorizada a entrar;
- todas as entradas, saídas, liberações e bloqueios fiquem registradas.

## 2. Perfis e responsabilidades

### Segurança do Trabalho

Responsável por:

- cadastrar e editar empresas terceirizadas;
- cadastrar e editar funcionários terceirizados;
- anexar, analisar, aprovar, rejeitar e renovar documentos;
- inativar empresas e funcionários;
- consultar documentos vencidos ou próximos do vencimento;
- registrar observações sobre pendências documentais.

### Solicitante do serviço

Cada colaborador que precisar de mão de obra terceirizada será responsável por:

- cadastrar a solicitação de serviço;
- selecionar a empresa terceirizada;
- informar setor, descrição, período e condições do serviço;
- acompanhar a situação da solicitação;
- cancelar a solicitação quando necessário.

O solicitante não poderá aprovar documentos nem ignorar bloqueios documentais.

### Guarita

Responsável por:

- pesquisar o terceirizado por CPF, nome, empresa, solicitação ou placa;
- consultar a decisão calculada pelo sistema;
- registrar entrada e saída;
- registrar observações ou ocorrências;
- solicitar autorização excepcional quando existir uma regra específica para isso.

A guarita não deverá alterar cadastros ou aprovar documentos.

### Administrador

Responsável por:

- cadastrar usuários;
- definir perfis e permissões;
- configurar setores e tipos de documentos;
- consultar auditoria e relatórios;
- administrar parâmetros gerais do sistema.

## 3. Regra obrigatória dos documentos

Todo documento cadastrado terá validade de **6 meses**.

Regras:

- a data de cadastro do documento será registrada automaticamente;
- a data de vencimento será calculada automaticamente: `data do cadastro + 6 meses`;
- o usuário não poderá aumentar manualmente esse prazo;
- após o vencimento, o documento ficará com a situação **Vencido**;
- documento vencido deverá bloquear a entrada quando for obrigatório;
- a renovação exigirá o envio de um novo arquivo;
- o documento anterior permanecerá no histórico e não poderá ser simplesmente substituído sem registro;
- o sistema deverá guardar quem cadastrou, aprovou ou rejeitou cada versão;
- a situação documental deverá ser recalculada automaticamente, sem depender de edição manual.

Situações possíveis:

- **Pendente:** enviado e aguardando análise;
- **Válido:** aprovado e dentro do período de seis meses;
- **Próximo do vencimento:** faltam 30 dias ou menos;
- **Vencido:** ultrapassou seis meses;
- **Rejeitado:** arquivo recusado pela Segurança do Trabalho;
- **Não enviado:** documento obrigatório ainda não cadastrado.

> Decisão funcional adotada: os seis meses começam na data em que o documento é cadastrado no sistema. Se futuramente a empresa decidir usar a data de emissão, essa regra deverá ser alterada e registrada.

## 4. Forma de cadastro dos documentos

Não existirão campos fixos nem cadastro de tipos de documento. A Segurança do Trabalho poderá anexar qualquer arquivo necessário diretamente à empresa ou ao funcionário. O nome original do arquivo será usado para identificá-lo, e todos seguirão a mesma validade obrigatória de seis meses.

## 5. Gestão documental

### Pendências

- [x] Armazenar data de cadastro e vencimento.
- [x] Armazenar situação da análise.
- [x] Armazenar responsável e data da aprovação ou rejeição.
- [x] Permitir observação ao rejeitar um documento.
- [x] Implementar renovação com histórico de versões.
- [x] Impedir exclusão definitiva do histórico documental no novo fluxo.
- [ ] Criar filtros por situação e vencimento.
- [ ] Criar painel de documentos vencidos.
- [ ] Criar painel de documentos próximos do vencimento.
- [x] Exibir pendências documentais nos detalhes da empresa e do funcionário.
- [x] Criar rotina diária para atualizar as situações.
- [ ] Gerar notificações a partir da rotina diária.

## 6. Empresas e funcionários

### Empresas

- [ ] Adicionar status ativo ou inativo.
- [ ] Restringir criação e alteração à Segurança do Trabalho e administradores autorizados.
- [ ] Exibir situação documental consolidada.
- [ ] Bloquear empresa com documento obrigatório vencido, rejeitado ou ausente.
- [ ] Manter histórico de alterações do cadastro.
- [ ] Evitar exclusão definitiva de empresas com histórico de serviços ou acessos.

### Funcionários terceirizados

- [ ] Definir se o cadastro será feito somente pela Segurança do Trabalho.
- [ ] Exibir situação documental consolidada.
- [ ] Bloquear funcionário inativo ou com documentação irregular.
- [ ] Manter histórico de mudanças de empresa e de situação.
- [ ] Evitar exclusão definitiva quando houver serviços ou acessos registrados.
- [ ] Permitir fotografia para facilitar a conferência na guarita, se aprovado pela empresa.

## 7. Solicitações de serviço

O cadastro atual precisa ser ampliado.

### Dados necessários

- empresa terceirizada;
- autorização para qualquer funcionário regular da empresa;
- solicitante;
- setor responsável;
- descrição do serviço;
- data e horário inicial;
- data e horário final;
- informação sobre almoço;
- veículo e placa, quando aplicável;
- observações para a guarita;
- status da solicitação.

### Pendências

- [ ] Adicionar horário inicial e final.
- [ ] Permitir serviços com mais de um dia.
- [ ] Adicionar veículo e placa como campos opcionais.
- [ ] Definir fluxo de aprovação da solicitação.
- [ ] Impedir aprovação da solicitação quando a empresa estiver documentalmente irregular.
- [ ] Restringir alteração ao solicitante ou a perfis autorizados.
- [ ] Registrar cancelamento, motivo, responsável e data.
- [ ] Manter histórico de alterações de status.

### Status sugeridos

- Rascunho;
- Pendente de documentação;
- Aguardando aprovação;
- Aprovado;
- Em andamento;
- Finalizado;
- Cancelado.

## 8. Tela operacional da guarita

Criar uma tela específica, rápida e adequada para consulta durante o atendimento.

### Formas de pesquisa

- CPF;
- nome do funcionário;
- empresa;
- número da solicitação;
- placa do veículo.

### Resultado da consulta

- **Verde — Entrada liberada**;
- **Vermelho — Entrada bloqueada**;
- **Amarelo — Necessita análise ou autorização**.

A tela deverá mostrar:

- foto, nome e CPF do funcionário;
- empresa;
- solicitação e setor de destino;
- período autorizado;
- situação da empresa;
- situação do funcionário;
- situação dos documentos;
- motivo detalhado da liberação ou do bloqueio;
- botão para registrar entrada ou saída.

## 9. Regras para liberação da entrada

A entrada somente será liberada quando todas as condições forem atendidas:

- empresa ativa;
- empresa sem documentos obrigatórios pendentes, rejeitados ou vencidos;
- funcionário ativo;
- funcionário sem documentos obrigatórios pendentes, rejeitados ou vencidos;
- funcionário pertencente à empresa de uma solicitação aprovada e vigente;
- solicitação dentro da data e do horário autorizado;
- solicitação não cancelada ou finalizada;
- nenhuma restrição manual ativa.

O sistema deve informar todos os motivos do bloqueio, e não apenas apresentar uma mensagem genérica.

## 10. Registro de acesso

- [ ] Criar registro de entrada.
- [ ] Registrar data e hora automaticamente.
- [ ] Registrar usuário da guarita responsável.
- [ ] Criar registro de saída.
- [ ] Impedir duas entradas abertas para a mesma pessoa.
- [ ] Permitir observações e ocorrências.
- [ ] Exibir quem está dentro da empresa naquele momento.
- [ ] Alertar permanência após o horário autorizado.
- [ ] Manter histórico pesquisável de acessos.

## 11. Alertas e notificações

- [ ] Alertar a Segurança do Trabalho 30 dias antes do vencimento.
- [ ] Destacar documentos vencidos no painel.
- [ ] Informar ao solicitante quando a documentação bloquear o serviço.
- [ ] Informar quando um documento for aprovado ou rejeitado.
- [ ] Definir se os avisos serão somente internos ou também enviados por e-mail.

## 12. Auditoria e segurança

- [ ] Registrar criação, alteração, aprovação, rejeição e renovação de documentos.
- [ ] Registrar mudanças em empresas, funcionários e solicitações.
- [ ] Registrar tentativas de entrada bloqueadas.
- [ ] Guardar usuário, data, hora e valores alterados.
- [ ] Proibir exclusão definitiva de registros com histórico operacional.
- [ ] Criar usuários ativos e inativos.
- [ ] Implementar redefinição segura de senha.
- [ ] Revisar permissões de cada perfil em todas as rotas.
- [ ] Definir tempo de expiração da sessão.
- [ ] Aplicar política de senha.
- [ ] Revisar e atualizar Laravel e dependências.

## 13. Relatórios

- [ ] Entradas e saídas por período.
- [ ] Pessoas que estão dentro da empresa.
- [ ] Tentativas de entrada bloqueadas e seus motivos.
- [ ] Documentos vencidos e próximos do vencimento.
- [ ] Empresas e funcionários bloqueados.
- [ ] Serviços por empresa, setor e solicitante.
- [ ] Tempo de permanência dos terceiros.
- [ ] Exportação para PDF ou planilha, se necessária.

## 14. Infraestrutura e operação

- [ ] Definir ambiente oficial de produção.
- [ ] Configurar HTTPS.
- [ ] Definir rotina automática de backup do banco e dos documentos.
- [ ] Documentar procedimento de restauração.
- [ ] Definir política de retenção dos arquivos.
- [ ] Monitorar espaço em disco e disponibilidade.
- [ ] Separar ambientes de desenvolvimento, homologação e produção.
- [ ] Definir responsável pelo suporte do sistema.

## 15. Testes necessários

- [ ] Documento vence exatamente após seis meses.
- [ ] Documento próximo do vencimento é identificado corretamente.
- [ ] Renovação mantém a versão anterior no histórico.
- [ ] Empresa irregular bloqueia todos os seus funcionários.
- [ ] Funcionário irregular não pode ser liberado.
- [ ] Funcionário regular sem solicitação é bloqueado.
- [ ] Solicitação fora do período não libera entrada.
- [ ] Guarita consegue registrar entrada e saída.
- [ ] Usuários sem permissão não aprovam documentos.
- [ ] Solicitante acessa apenas as ações permitidas.
- [ ] Histórico e auditoria são preservados.

## 16. Ordem recomendada de desenvolvimento

### Fase 1 — Base documental

1. Validade automática de seis meses.
2. Aprovação, rejeição e renovação.
3. Situação consolidada da empresa e do funcionário.
4. Painel da Segurança do Trabalho.

### Fase 2 — Solicitação completa

1. Período e horários autorizados.
2. Fluxo de aprovação.
3. Autorização para qualquer funcionário regular da empresa.
4. Bloqueio por irregularidade documental.

### Fase 3 — Guarita

1. Consulta rápida.
2. Decisão automática de entrada.
3. Registro de entrada e saída.
4. Tela de pessoas presentes.

### Fase 4 — Gestão

1. Alertas e notificações.
2. Auditoria.
3. Relatórios.
4. Backup, segurança e implantação em produção.

## 17. Critério para o sistema estar operacional

O sistema será considerado apto para uso completo quando a guarita conseguir consultar qualquer funcionário terceirizado e receber uma decisão confiável de entrada, baseada automaticamente em:

- validade documental de seis meses;
- aprovação dos documentos pela Segurança do Trabalho;
- situação da empresa e do funcionário;
- existência de uma solicitação aprovada e vigente;
- registro rastreável da entrada e da saída.
