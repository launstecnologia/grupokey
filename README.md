# Sistema CRM - Totalmente Automático

Sistema de CRM que detecta automaticamente o ambiente e configura tudo sozinho.

## 🚀 Instalação Super Simples

### 1. Clone o Projeto
```bash
git clone [url-do-repositorio]
cd grupokey
```

### 2. Instale as Dependências
```bash
composer install
```

### 3. Pronto! 🎉
**Não precisa configurar nada!** O sistema detecta automaticamente:
- **Localhost:** `http://localhost/grupokey`
- **Produção:** `https://grupokey.com.br`

## 🤖 Como Funciona

### **Detecção Automática:**
- **localhost** → Modo desenvolvimento
- **Qualquer outro domínio** → Modo produção

### **Configurações Automáticas:**

#### **Desenvolvimento (localhost):**
- FOLDER: `/grupokey`
- URL: `http://localhost/grupokey`
- DEBUG: `true`

#### **Produção (grupokey.com.br):**
- FOLDER: `/`
- URL: `https://grupokey.com.br`
- DEBUG: `false`

## 🗄️ Banco de Dados

**Configuração única para ambos os ambientes:**
- Host: `72.60.158.222`
- Banco: `grup_platform`
- Usuário: `grup_platform`
- Senha: `117910Campi!25`

## 📁 Estrutura do Projeto

```
grupokey/
├── index.php              # Ponto de entrada
├── .htaccess              # Configuração automática
├── app/
│   ├── Core/
│   │   ├── AutoConfig.php # Sistema automático
│   │   └── Database.php   # Conexão com banco
│   └── ...
├── database/
│   └── schema.sql         # Schema do banco
└── ...
```

## 🚀 Deploy

### **1. Upload dos Arquivos:**
Faça upload de todos os arquivos para o servidor.

### **2. Pronto! 🎉**
**Não precisa executar comandos!** O sistema detecta automaticamente que está em produção.

### **3. Acessar:**
- **Desenvolvimento:** `http://localhost/grupokey`
- **Produção:** `https://grupokey.com.br`

## 👤 Login Padrão

- **Email:** `admin@sistema.com`
- **Senha:** `password`

## 🎯 Vantagens

### **✅ Zero Configuração:**
- Não precisa executar comandos
- Não precisa editar arquivos
- Detecta ambiente automaticamente

### **✅ Funciona em Qualquer Lugar:**
- Localhost
- Servidor de produção
- Qualquer domínio

### **✅ Sem Complicação:**
- Upload e pronto
- Sem scripts manuais
- Sem troca de ambiente

## 🔍 Teste

### **Local:**
```bash
php test_auto.php
```

### **Browser:**
- Acesse: `http://localhost/grupokey`
- Deve aparecer a página de login

## 🆘 Se Der Problema

### **Verificar Logs:**
```bash
# Apache
tail -f /var/log/apache2/error.log

# PHP
tail -f /var/log/php_errors.log
```

### **Testar Configuração:**
```bash
php test_auto.php
```

## 📞 Suporte

### **Logs do Sistema:**
- **Aplicação:** `storage/logs/`
- **Servidor:** `/var/log/apache2/error.log`

### **Debug:**
- **Desenvolvimento:** Debug automático
- **Produção:** Debug desativado automaticamente

---

**Sistema totalmente automático - Zero configuração necessária!** 🚀
