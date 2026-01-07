# Logs do Sistema

Este diretório contém os arquivos de log da aplicação.

## Arquivos de Log

### `photo_upload.log`
Logs detalhados de upload de fotos de perfil (usuários e representantes).

**Localização:** `storage/logs/photo_upload.log`

**Conteúdo:**
- Início e fim do processo de upload
- Informações do usuário (ID, tipo)
- Validações de arquivo (tipo, tamanho)
- Criação de diretórios
- Movimentação de arquivos
- Atualização no banco de dados
- Erros e exceções

### `excel_upload.log`
Logs detalhados de upload de planilhas Excel/CSV para relatórios de faturamento.

**Localização:** `storage/logs/excel_upload.log`

**Conteúdo:**
- Início e fim do processo de upload
- Informações do usuário
- Validações de arquivo (tipo, tamanho, extensão)
- Criação de diretórios
- Movimentação de arquivos
- Processamento do arquivo
- Erros e exceções

### `app.log`
Log geral da aplicação (quando usado com a função `write_log()` sem especificar arquivo).

## Como Visualizar os Logs

### No Servidor (Linux/Mac):
```bash
# Ver últimos 50 linhas do log de fotos
tail -n 50 storage/logs/photo_upload.log

# Acompanhar em tempo real
tail -f storage/logs/photo_upload.log

# Ver últimos 50 linhas do log de Excel
tail -n 50 storage/logs/excel_upload.log

# Acompanhar em tempo real
tail -f storage/logs/excel_upload.log
```

### No Windows (PowerShell):
```powershell
# Ver últimos 50 linhas
Get-Content storage/logs/photo_upload.log -Tail 50

# Acompanhar em tempo real
Get-Content storage/logs/photo_upload.log -Wait -Tail 20
```

### Via FTP/File Manager:
Acesse o diretório `storage/logs/` e abra os arquivos `.log` em um editor de texto.

## Logs do PHP (error_log)

Além dos logs customizados, o sistema também registra logs no arquivo padrão do PHP (`error_log()`).

**Localização típica:**
- **Apache:** `/var/log/apache2/error.log` ou `/var/log/httpd/error_log`
- **Nginx:** `/var/log/nginx/error.log`
- **LiteSpeed:** Verificar configuração do servidor
- **Windows:** Verificar configuração do PHP (`php.ini` - `error_log`)

## Formato dos Logs

Cada linha de log segue o formato:
```
[YYYY-MM-DD HH:MM:SS] Mensagem do log
```

Exemplo:
```
[2025-01-15 14:30:25] === INÍCIO UPLOAD DE FOTO ===
[2025-01-15 14:30:25] Usuário ID: 1 | Tipo: admin
[2025-01-15 14:30:25] Arquivo recebido: Nome=foto.jpg | Tamanho=245678 bytes | Tipo=image/jpeg
[2025-01-15 14:30:25] SUCESSO: Arquivo movido com sucesso!
```

## Limpeza de Logs

⚠️ **Atenção:** Os arquivos de log podem crescer bastante. Considere implementar rotação de logs ou limpeza periódica.

Para limpar os logs manualmente:
```bash
# Limpar log de fotos
> storage/logs/photo_upload.log

# Limpar log de Excel
> storage/logs/excel_upload.log
```

## Permissões

O diretório `storage/logs/` precisa ter permissão de escrita para o servidor web:
```bash
chmod 755 storage/logs/
chmod 644 storage/logs/*.log
```

