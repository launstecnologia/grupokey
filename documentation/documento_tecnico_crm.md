# DOCUMENTO TÉCNICO - SISTEMA CRM
**Projeto**: Sistema de Gestão de Clientes e Representantes  
**Versão**: 1.0  
**Data**: Dezembro 2024

---

## STACK TECNOLÓGICA

### Backend
- **PHP 8.1+** - Linguagem principal com orientação a objetos
- **MySQL 8.0+** - Banco de dados relacional
- **PDO** - Interface de banco de dados com prepared statements
- **Composer** - Gerenciador de dependências PHP

### Frontend
- **HTML5** - Estrutura das páginas
- **CSS3** - Estilização (Bootstrap ou CSS customizado)
- **JavaScript** - Interatividade e validações client-side
- **jQuery** - Biblioteca JavaScript para manipulação DOM

### Servidor Web
- **Apache 2.4+** ou **Nginx 1.18+**
- **Mod_rewrite** habilitado para URLs amigáveis
- **SSL/TLS** para conexões seguras

---

## DEPENDÊNCIAS DO COMPOSER

### composer.json
```json
{
    "name": "empresa/sistema-crm",
    "description": "Sistema de gestão de clientes e representantes",
    "type": "project",
    "require": {
        "php": ">=8.1",
        "phpmailer/phpmailer": "^6.8",
        "vlucas/phpdotenv": "^5.5",
        "ramsey/uuid": "^4.7"
    },
    "autoload": {
        "psr-4": {
            "App\\Controllers\\": "app/Controllers/",
            "App\\Models\\": "app/Models/",
            "App\\Core\\": "app/Core/"
        },
        "files": [
            "app/helpers.php"
        ]
    }
}
```

### Bibliotecas Principais

**PHPMailer (^6.8)**
- Envio de emails via SMTP
- Suporte a templates HTML
- Anexos de arquivos
- Configuração para Gmail, Outlook, servidores dedicados

**Dotenv (^5.5)**
- Gerenciamento de variáveis de ambiente
- Configurações sensíveis fora do controle de versão
- Diferentes ambientes (dev, staging, prod)

**UUID (^4.7)**
- Geração de identificadores únicos
- IDs seguros para registros críticos
- Prevenção de ataques de enumeração

---

## CONFIGURAÇÃO DO AMBIENTE

### Arquivo .env
```env
# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=sistema_crm
DB_USER=root
DB_PASS=

# Application
APP_NAME="Sistema CRM"
APP_URL=http://localhost/crm
APP_ENV=development
APP_DEBUG=true

# Mail
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=seu-email@gmail.com
MAIL_PASS=sua-senha-app
MAIL_FROM=seu-email@gmail.com
MAIL_NAME="Sistema CRM"

# Upload
MAX_FILE_SIZE=10485760
ALLOWED_EXTENSIONS=jpg,jpeg,png,pdf,doc,docx
```

### Estrutura de Diretórios
```
sistema-crm/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── EstablishmentController.php
│   │   └── UserController.php
│   ├── Models/
│   │   ├── Database.php
│   │   ├── User.php
│   │   ├── Representative.php
│   │   └── Establishment.php
│   ├── Core/
│   │   ├── Router.php
│   │   ├── Auth.php
│   │   ├── Mailer.php
│   │   └── FileUpload.php
│   └── Views/
│       ├── layouts/
│       ├── auth/
│       └── dashboard/
├── config/
│   ├── database.php
│   └── routes.php
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── storage/
│   ├── logs/
│   └── uploads/
├── vendor/
├── composer.json
└── .env
```

---

## BANCO DE DADOS MYSQL

### Configuração PDO
```php
// Estrutura conceitual da classe Database
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $dsn = "mysql:host=" . $_ENV['DB_HOST'] . 
               ";dbname=" . $_ENV['DB_NAME'] . 
               ";charset=utf8mb4";
        
        $this->pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }
}
```

### Tabelas Principais
- **users** - Usuários administrativos
- **representatives** - Representantes/vendedores
- **establishments** - Clientes/estabelecimentos
- **tickets** - Chamados de suporte
- **machines** - Controle de máquinas
- **documents** - Documentos enviados
- **audit_logs** - Log de auditoria

### Migrations SQL
Scripts organizados cronologicamente para criação e alteração das tabelas:
- `001_create_users_table.sql`
- `002_create_representatives_table.sql`
- `003_create_establishments_table.sql`

---

## SISTEMA DE ROTEAMENTO

### URLs Amigáveis com .htaccess
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Rotas do Sistema
```php
// Exemplo conceitual de rotas
$routes = [
    'GET /' => 'DashboardController@index',
    'GET /login' => 'AuthController@showLogin',
    'POST /login' => 'AuthController@login',
    'GET /logout' => 'AuthController@logout',
    'GET /dashboard' => 'DashboardController@index',
    'GET /estabelecimentos' => 'EstablishmentController@index',
    'POST /estabelecimentos' => 'EstablishmentController@store',
    'GET /representantes' => 'RepresentativeController@index'
];
```

---

## SISTEMA DE AUTENTICAÇÃO

### Login Unificado
- Tela única para admin e representantes
- Identificação automática do tipo de usuário
- Sessão PHP com dados do usuário logado
- Controle de tentativas (máximo 5)

### Controle de Acesso
```php
// Estrutura conceitual
class Auth {
    public static function check() {
        return isset($_SESSION['user_id']);
    }
    
    public static function isAdmin() {
        return $_SESSION['user_type'] === 'admin';
    }
    
    public static function isRepresentative() {
        return $_SESSION['user_type'] === 'representative';
    }
}
```

### Recuperação de Senha
- Envio de email com PHPMailer
- Token único com validade
- Formulário seguro de redefinição

---

## SISTEMA DE EMAIL (PHPMAILER)

### Configuração SMTP
```php
// Estrutura conceitual da classe Mailer
class Mailer {
    private $phpmailer;
    
    public function __construct() {
        $this->phpmailer = new PHPMailer(true);
        $this->phpmailer->isSMTP();
        $this->phpmailer->Host = $_ENV['MAIL_HOST'];
        $this->phpmailer->SMTPAuth = true;
        $this->phpmailer->Username = $_ENV['MAIL_USER'];
        $this->phpmailer->Password = $_ENV['MAIL_PASS'];
        $this->phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->phpmailer->Port = 587;
    }
}
```

### Templates de Email
- **Reset de senha** - Link seguro para redefinição
- **Boas-vindas** - Credenciais para novos representantes
- **Notificações** - Aprovação/reprovação de clientes
- **Alertas** - Notificações do sistema

---

## UPLOAD DE ARQUIVOS

### Validação e Segurança
```php
// Estrutura conceitual
class FileUpload {
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    private $maxSize = 10485760; // 10MB
    
    public function upload($file, $destination) {
        // Validar tipo e tamanho
        // Gerar nome único
        // Mover para diretório seguro
    }
}
```

### Organização de Arquivos
- Diretórios por cliente
- Nomes únicos com UUID
- Validação de MIME type
- Backup opcional no Google Drive

---

## SEGURANÇA

### Medidas Implementadas
- **Prepared Statements** - Prevenção de SQL Injection
- **CSRF Tokens** - Proteção contra ataques CSRF
- **XSS Protection** - Escape de output
- **Validação Server-side** - Todas entradas validadas
- **Hash de senhas** - password_hash() do PHP
- **Controle de sessão** - Regeneração de IDs

### Validação de Dados
```php
// Estrutura conceitual
class Validator {
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function cpf($cpf) {
        // Algoritmo de validação de CPF
    }
    
    public static function cnpj($cnpj) {
        // Algoritmo de validação de CNPJ
    }
}
```

---

## FUNCIONALIDADES PRINCIPAIS

### Dashboard Administrativo
- KPIs do sistema
- Gráficos de performance
- Ações rápidas
- Filtros por período e produto

### Dashboard Representante
- Clientes do representante
- Status de aprovação
- Chamados em aberto
- Relatórios individuais

### Gestão de Estabelecimentos
- CRUD completo
- Aprovação manual (exceto PagSeguro)
- Upload de documentos
- Histórico de alterações

### Sistema de Chamados
- Criação por admin ou representante
- Atribuição de técnicos
- Acompanhamento de status
- Comentários e atualizações

### Controle de Máquinas
- Cadastro de lotes
- Inserção massiva de S/N
- Vinculação a clientes
- Controle de estoque

---

## REQUISITOS DO SERVIDOR

### Especificações Mínimas
- **PHP 8.1+** com extensões: pdo_mysql, mbstring, openssl, curl
- **MySQL 8.0+** ou **MariaDB 10.6+**
- **Apache 2.4+** com mod_rewrite habilitado
- **SSL Certificate** para produção
- **Memória**: Mínimo 256MB, recomendado 512MB
- **Disco**: 5GB livres para sistema e uploads

### Configurações PHP
```ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 60
session.gc_maxlifetime = 7200
```

---

## DEPLOY E MANUTENÇÃO

### Ambiente de Produção
- Configurar .env com dados reais
- Executar migrations do banco
- Configurar cron jobs para limpeza
- Implementar backup automático
- Monitorar logs de erro

### Backup Strategy
- Backup diário do banco de dados
- Backup semanal dos uploads
- Rotação de logs
- Teste de restauração mensal

### Monitoramento
- Logs de erro em storage/logs/
- Log de auditoria no banco
- Monitoramento de performance
- Alertas de segurança

---

## CONCLUSÃO

Este documento técnico define a arquitetura completa do Sistema CRM desenvolvido em PHP com orientação a objetos simples, utilizando MySQL como banco de dados e Composer para gerenciamento de dependências. O sistema foi projetado para ser seguro, escalável e de fácil manutenção, seguindo as melhores práticas de desenvolvimento web.