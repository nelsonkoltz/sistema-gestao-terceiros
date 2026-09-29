# Revisão técnica — 29/09/2026

Alterações preparadas no código e verificadas com banco MySQL temporário. A aplicação em uso e seus volumes não foram atualizados. HTTPS, backups, restauração, monitoramento e demais tarefas de infraestrutura permanecem fora desta entrega.

Atualização pontual posterior: a remoção dos horários previstos foi aplicada também aos containers em execução, com recompilação das telas. O ambiente continua no Laravel 8, sem execução das novas migrações. Essa aplicação pontual permanece no container; a recriação a partir de uma imagem antiga pode revertê-la. O código-fonte já contém a correção para a próxima reconstrução. A atualização maior descrita abaixo continua pendente de implantação.

## Segurança e dependências

- Laravel 8.83.29 atualizado para 12.69.3, compatível com o PHP 8.2 já utilizado.
- Dependências PHP atualizadas no `composer.lock`; CORS passou para o middleware nativo e SwiftMailer foi substituído por Symfony Mailer.
- PHPUnit atualizado para 11.5. A estrutura existente do Laravel foi preservada.
- Sessões antigas são recusadas na próxima requisição após troca de senha, redefinição por link ou alteração administrativa. A sessão que troca a própria senha permanece válida.
- O login registra o hash de sessão imediatamente. Tokens de recuperação são consumidos sob bloqueio transacional, e a redefinição invalida todos os links anteriores do usuário.
- Senhas não são copiadas para os dados de formulário da sessão em erros de validação.
- Exportações CSV neutralizam campos que poderiam ser interpretados como fórmulas por planilhas.
- Removido o esqueleto npm/Mix sem uso (`package.json`, `webpack.mix.js` e `resources/js`). Nenhuma tela carregava esse bundle, e a configuração referenciava um CSS inexistente. As telas continuam usando Blade, scripts próprios e CSS em `public/css`; não há etapa npm. As bibliotecas de ícones/máscara e fontes externas usadas nas telas permanecem como antes.

## Operação por filial

- Seleção da filial operacional no cabeçalho.
- Solicitantes, Guarita e Segurança do Trabalho só selecionam filiais ativas vinculadas à conta. A autorização é verificada novamente em cada requisição.
- Solicitações, entradas, saídas, ocorrências, indicadores operacionais e exportações respeitam a filial selecionada, incluindo URLs digitadas manualmente.
- O Administrador pode selecionar qualquer filial; filiais inativas permitem apenas leitura operacional. O painel consolidado é exclusivo do Administrador e não muda a filial usada para novas operações.
- Empresas, funcionários e documentos continuam compartilhados entre filiais. A autorização de entrada exige um serviço da filial operacional.
- A nova migração associa o histórico existente à Matriz. Não tenta deduzir a unidade de registros antigos. A filial de código `MATRIZ` deve existir antes da migração.
- Exclusões respeitam vínculos históricos de todas as filiais. Uma filial com pessoas presentes não pode ser inativada.
- Cadastro/edição de usuário e vínculos de filial são gravados na mesma transação.

## Consistência dos registros

- Entrada e saída usam transações e bloqueios no banco; uma pessoa não pode manter duas entradas abertas, inclusive entre filiais.
- Uma restrição no MySQL também impede duplicidade fora do controlador. Se já houver duplicatas antigas, a migração interrompe com os IDs dos funcionários, sem apagar ou encerrar registros automaticamente.
- Cancelamento e alterações de serviços são serializados com as operações da portaria. A última saída não transforma um serviço cancelado em finalizado, e atualizações são auditadas.
- Renovação de documentos preserva o arquivo anterior, impede duas substituições da mesma versão e remove o arquivo novo se a gravação falhar.
- A data de validade é o primeiro dia de bloqueio, a partir de 00:00 no fuso da aplicação. Alertas, consulta e job seguem a mesma regra.
- A solicitação exige somente a data do serviço, sem horários previstos de entrada e saída. O acesso pode ser liberado em qualquer horário dessa data, respeitando status, filial e documentação. Horários antigos permanecem armazenados, mas não restringem a entrada. A guarita continua registrando os horários reais. Agendamento por horário fica para projeto futuro.
- A configuração de e-mail exige TLS/SSL conforme a opção selecionada e recria o transporte após alterações. Falhas de envio retornam erro no comando sem depender do job de validade.

## Validação e revisão manual

Resultado desta revisão: **52 testes aprovados, 402 verificações**. `composer audit --locked` retornou zero avisos de segurança e zero pacotes abandonados, incluindo dependências de desenvolvimento. `composer validate --strict`, compilação de todas as views Blade e cache de rotas também passaram.

Executar a suíte isolada:

```powershell
docker compose -p terceiroscr-tests -f docker-compose.test.yml up --build --abort-on-container-exit --exit-code-from tests
docker compose -p terceiroscr-tests -f docker-compose.test.yml down
```

Os testes cobrem regressões dos fluxos existentes, isolamento entre filiais, sessões, recuperação de senha, renovação, limites de validade/data do serviço, cadastro e edição sem horários, CSV, configuração/falha de SMTP e duas requisições concorrentes reais em processos separados.

Para revisar com calma:

1. Confira os vínculos de usuários e alterne entre duas filiais.
2. Confira solicitações, portaria, ocorrências e exportações de cada uma.
3. Abra o painel consolidado como Administrador.
4. Troque a senha em um navegador e confirme a saída de uma sessão aberta em outro.
5. Valide o layout em desktop/celular e o envio pelo SMTP real em homologação.

Não foi enviado e-mail real nesta revisão. Não foram executados testes de carga ou restauração de backup. As migrações novas foram executadas apenas no banco temporário; a atualização do ambiente em uso fica para a revisão posterior.
