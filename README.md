# Sistema de Gestão de Terceiros (TerceiroCR)

Sistema web corporativo desenvolvido para gerenciar empresas terceirizadas, funcionários vinculados e solicitações de serviço dentro de um ambiente empresarial.

O sistema é utilizado em ambiente real na empresa onde atuo.

---

## 📌 Sobre o Projeto

O TerceiroCR foi desenvolvido para organizar e controlar o cadastro de empresas terceirizadas e seus funcionários, garantindo controle documental, gestão de serviços e regras de permissão por perfil de usuário.

O objetivo é centralizar informações e reduzir falhas operacionais no processo de gestão de terceiros.

---

## 🛠 Tecnologias Utilizadas

### Backend
- PHP
- Laravel
- MySQL
- Arquitetura REST

### Frontend
- Blade
- Bootstrap
- CSS

---

## 🚀 Principais Funcionalidades

- Cadastro e gerenciamento de empresas
- Cadastro de funcionários vinculados às empresas
- Upload e controle de documentos
- Controle de solicitações de serviço
- Sistema de permissões (Administrador, Usuário e Consulta)
- Regra automática de inativação de funcionários após 6 meses sem atualização documental

---

## 🔐 Regras de Negócio Implementadas

- Funcionários tornam-se inativos automaticamente se a documentação não for atualizada dentro do prazo.
- Apenas usuários responsáveis podem editar determinados registros.
- Controle de acesso baseado em níveis de permissão.

---

## 🏗 Arquitetura do Projeto

- Padrão MVC (Laravel)
- Validações com Form Requests
- Middleware para controle de acesso
- Relacionamentos One-to-Many no banco de dados
- Gerenciamento de arquivos via Storage

---

## ▶ Como Executar com Docker

Com o Docker Desktop iniciado, execute na pasta do projeto:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/start.ps1
```

Acesse **http://localhost:8090**. Na primeira instalação, o script gera as credenciais do administrador em `.local/acesso.txt` e as configurações em `.env.docker`. Esses arquivos são locais e não entram no Git.

O banco e os documentos ficam nos volumes `dbdata` e `app_storage`. Preserve os dois volumes ao realizar backups. Depois de alterar o código, execute novamente `scripts/start.ps1` para reconstruir as imagens.

### Testes isolados

```powershell
docker compose -p terceiroscr-tests -f docker-compose.test.yml up --build --abort-on-container-exit --exit-code-from tests
docker compose -p terceiroscr-tests -f docker-compose.test.yml down
```

A suíte usa um banco MySQL temporário separado e não acessa os dados da aplicação.

---

## 📷 Prints do Sistema

(Adicionar aqui imagens das principais telas)

---

## 📈 Melhorias Futuras

- Versão em API
- Integração com React
- Painel específico para operação da guarita
- Notificações de vencimento de documentos

---

## 📌 Autor

Desenvolvido por **Arinelson Koltz**  
🔗 LinkedIn: [www.linkedin.com/in/arinelsonkoltz](https://www.linkedin.com/in/arinelsonkoltz)
