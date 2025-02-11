# Desafio Revvo - Sistema de Cadastro de Usuários

## 🚀 Configuração do Banco de Dados

### Pré-requisitos
- XAMPP com MySQL
- PHP 8.0+
- Composer

### Passos para Configuração

1. **Iniciar Servidores**
   - Abra o XAMPP Control Panel
   - Inicie os serviços Apache e MySQL

2. **Criar Banco de Dados**
   ```bash
   # No terminal MySQL ou phpMyAdmin
   mysql -u root
   source c:\xampp\htdocs\desafio_revvo\database.sql
   ```

3. **Configurações de Conexão**
   - Edite `src/Database/Connection.php` com suas credenciais:
     ```php
     private $host = 'localhost';
     private $dbname = 'desafio_revvo';
     private $username = 'root';
     private $password = '';
     ```

### Credenciais Padrão
- **Banco de Dados**: 
  - Nome: `desafio_revvo`
  - Usuário: `root`
  - Senha: ``

- **Usuário Inicial**:
  - Email: `admin@revvo.com`
  - Senha: Não definida (primeiro acesso)

### Instalação de Dependências
```bash
composer install
```

## 📚 Sistema de Gerenciamento de Usuários

## Visão Geral
Este é um sistema web de gerenciamento de usuários desenvolvido em PHP, utilizando uma arquitetura moderna e modular. O sistema permite realizar operações CRUD (Create, Read, Update, Delete) de usuários com validações robustas e interface amigável.

## Arquitetura do Sistema

### Componentes Principais
1. **Frontend**
   - Localização: `public/`
   - Tecnologias: HTML, CSS, JavaScript
   - Biblioteca de UI: SweetAlert2
   - Arquivo principal: `index.php`
   - Script de interação: `js/script.js`

2. **Backend**
   - Localização: `src/`
   - Linguagem: PHP
   - Padrão de Arquitetura: Repositório

### Estrutura de Diretórios
```
desafio_revvo/
│
├── public/                 # Arquivos públicos e de acesso
│   ├── index.php           # Página inicial
│   ├── api.php             # API de gerenciamento de usuários
│   └── js/
│       └── script.js       # Lógica de frontend
│
└── src/                    # Código-fonte do backend
    ├── Models/             # Modelos de dados
    │   └── User.php        # Modelo de usuário
    │
    ├── Repositories/       # Camada de acesso a dados
    │   └── UserRepository.php  # Operações de banco de dados
    │
    └── Helpers/            # Classes auxiliares
        ├── Database.php    # Conexão com banco de dados
        └── Validation.php  # Validações de entrada
```

## Funcionalidades

### Usuário
- Cadastro de novos usuários
- Listagem de usuários
- Visualização de detalhes do usuário
- Edição de usuários
- Exclusão de usuários

### Validações
1. **Cadastro de Usuário**
   - Nome: Obrigatório, mínimo 2 caracteres
   - Email: 
     - Obrigatório
     - Formato válido
     - Único (não pode haver duplicatas)
   - Telefone: Opcional, formato (DD) 9XXXX-XXXX
   - Data de Nascimento: 
     - Idade mínima: 16 anos
     - Não pode ser no futuro

### Fluxo de Operações

#### Cadastro de Usuário
1. Usuário preenche formulário
2. Frontend valida campos básicos
3. Dados enviados para `api.php`
4. Backend realiza validações:
   - Verifica unicidade de email
   - Valida formato dos dados
5. Salva no banco de dados
6. Retorna mensagem de sucesso ou erro

#### Listagem de Usuários
1. Carrega todos os usuários do banco
2. Exibe em tabela com opções de:
   - Visualizar detalhes
   - Editar
   - Excluir

## Tecnologias Utilizadas

### Backend
- PHP 8.x
- PDO para acesso ao banco de dados
- Tratamento de exceções
- Validação de dados

### Frontend
- HTML5
- JavaScript moderno
- SweetAlert2 para notificações
- Fetch API para comunicação assíncrona

### Banco de Dados
- MySQL/MariaDB
- Tabela: `usuarios`
- Campos: 
  - `id` (chave primária)
  - `nome`
  - `email` (único)
  - `telefone`
  - `data_nascimento`
  - `created_at`

## Segurança

### Implementado
- Validação de entrada
- Prevenção de email duplicado
- Tratamento de exceções
- Sanitização de dados

### Recomendações
- Implementar autenticação
- Adicionar CSRF protection
- Usar prepared statements (já implementado)

## Melhorias Futuras
- Paginação de resultados
- Filtros de busca
- Autenticação de usuário
- Logs de auditoria

## Instalação

### Requisitos
- PHP 8.x
- MySQL/MariaDB
- Servidor web (Apache/Nginx)

### Passos
1. Clonar repositório
2. Configurar banco de dados
3. Executar scripts SQL
4. Configurar conexão no `Database.php`

## Contribuição
1. Faça fork do projeto
2. Crie branch de feature
3. Commit suas alterações
4. Abra um Pull Request

## Licença
[Especificar licença]

---

**Desenvolvido com ❤️ por Equipe de Desenvolvimento**

## � Configuração do Composer

### `composer.json`
```json
{
    "name": "desafio_revvo/user-management",
    "description": "Sistema de Gerenciamento de Usuários",
    "type": "project",
    "require": {
        "php": "^7.4 || ^8.0"
    },
    "autoload": {
        "psr-4": {
            "Repositories\\": "src/Repositories/",
            "Models\\": "src/Models/",
            "Database\\": "src/Database/",
            "Helpers\\": "src/Helpers/"
        }
    }
}
```

#### Namespaces
- `Repositories\\`: Repositórios de dados
- `Models\\`: Modelos de entidades
- `Database\\`: Configurações de conexão
- `Helpers\\`: Classes utilitárias e de validação

### Requisitos de Dependências
- PHP 7.4 ou superior
- Suporta PHP 8.0+

### Instalação de Dependências
```bash
# Instalar dependências do Composer
composer install

# Atualizar dependências
composer update
```

### Autoload
O projeto utiliza o autoload do Composer para carregar classes automaticamente, seguindo o padrão PSR-4.

## 🔍 Estrutura de Diretórios
```
desafio_revvo/
│
├── public/           # Arquivos públicos
│   ├── api.php       # API de gerenciamento
│   ├── index.php     # Página principal
│   └── js/           # Scripts JavaScript
│
├── src/              # Código-fonte
│   ├── Repositories/ # Repositórios de dados
│   ├── Models/       # Modelos de entidades
│   ├── Database/     # Configurações de banco
│   └── Helpers/      # Classes auxiliares
│
├── database.sql      # Script de criação do banco
└── composer.json     # Configurações do Composer
```

## �🛠 Solução de Problemas
- Verifique se o MySQL está rodando
- Confirme as permissões do usuário de banco
- Cheque as configurações de conexão

## 📝 Notas
- Projeto desenvolvido para o Desafio Revvo
- Sistema de Cadastro de Usuários com validações avançadas

## 🤝 Contribuição
Pull requests são bem-vindos!