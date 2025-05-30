# SICUC – (Sistema Integrado da Central Única de Contadores)

## 📋 Sobre o Sistema
Sistema desenvolvido em PHP para gerenciamento de processos da Central Única de Contadores. Utiliza uma arquitetura MVC personalizada e interface moderna com Bootstrap 5.

## 🚀 Funcionalidades Principais

### Módulos e Submódulos
- Cadastro de módulos principais e submódulos
- Gerenciamento de status (ativo/inativo)
- Controle hierárquico de módulos
- Personalização de ícones (FontAwesome)
- Definição de rotas personalizadas

### Usuários
- Cadastro e gerenciamento de usuários
- Níveis de acesso (admin/usuário)
- Sistema de autenticação seguro
- Gerenciamento de permissões por módulo
- Perfis de acesso personalizáveis

### Permissões
- Atribuição granular de permissões
- Herança de permissões entre módulos e submódulos
- Interface intuitiva para gerenciamento
- Validação em tempo real
- Proteção contra acessos não autorizados

## 🛠️ Tecnologias Utilizadas
- PHP 7.4+
- MySQL/MariaDB
- Bootstrap 5
- FontAwesome 5
- JavaScript
- HTML5/CSS3

## 🔧 Instalação
1. Clone o repositório
2. Configure o banco de dados em `config/config.php`
3. Importe o arquivo SQL da estrutura do banco
4. Configure o servidor web (Apache/Nginx)
5. Acesse o sistema via navegador

## 👥 Usuário Padrão Administrador
- **Login**: admin@tjgo.jus.br
- **Senha**: 123456

## 👥 Usuário Padrão Analista
- **Login**: analista@tjgo.jus.br
- **Senha**: 123456

## 👥 Usuário Padrão Usuário
- **Login**: usuario@tjgo.jus.br
- **Senha**: 123456

## 📦 Estrutura do Projeto
├── app/
│ ├── Controllers/
│ ├── Models/
│ ├── Views/
│ └── Helpers/
├── public/
│ ├── css/
│ ├── js/
│ └── img/
├── index.php
├── .htaccess
└── README.md

## 🔐 Segurança
- Proteção contra SQL Injection
- Sanitização de inputs
- Validação de dados
- Controle de sessão
- Hash seguro de senhas

## 📝 Licença
Este projeto está sob a licença MIT.


