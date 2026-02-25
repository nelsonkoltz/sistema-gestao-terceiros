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

## ▶ Como Executar o Projeto

1. Clonar o repositório
2. Executar `composer install`
3. Configurar o arquivo `.env`
4. Executar `php artisan migrate`
5. Executar `php artisan serve`

---

## 📷 Prints do Sistema

(Adicionar aqui imagens das principais telas)

---

## 📈 Melhorias Futuras

- Versão em API
- Integração com React
- Containerização com Docker
- Expansão de testes automatizados

---

## 📌 Autor

Desenvolvido por **Arinelson Koltz**  
🔗 LinkedIn: [www.linkedin.com/in/arinelsonkoltz](https://www.linkedin.com/in/arinelsonkoltz)
