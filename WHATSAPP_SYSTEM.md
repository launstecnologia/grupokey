# Sistema de Atendimento WhatsApp

Sistema completo de atendimento WhatsApp multiatendente, multiusuário e multi-instância, integrado com Evolution API.

## 📋 Estrutura do Sistema

### Banco de Dados

Todas as tabelas são criadas pela migration `031_create_whatsapp_system_tables.sql`:

- **whatsapp_instances**: Instâncias do WhatsApp conectadas
- **whatsapp_contacts**: Contatos do WhatsApp
- **whatsapp_queues**: Filas de atendimento (setores)
- **whatsapp_queue_users**: Relacionamento filas ↔ usuários
- **whatsapp_conversations**: Conversas/chats
- **whatsapp_attendances**: Atendimentos (conversa + atendente)
- **whatsapp_messages**: Mensagens trocadas
- **whatsapp_user_sessions**: Sessões de usuários (online/offline)

### Models

- `WhatsAppInstance.php`: Gerenciamento de instâncias
- `WhatsAppContact.php`: Gerenciamento de contatos
- `WhatsAppConversation.php`: Gerenciamento de conversas
- `WhatsAppMessage.php`: Gerenciamento de mensagens
- `WhatsAppQueue.php`: Gerenciamento de filas
- `WhatsAppAttendance.php`: Gerenciamento de atendimentos

### Services

- `EvolutionApiService.php`: Integração completa com Evolution API

### Controllers

- `WhatsAppController.php`: Gerenciamento de instâncias (admin)
- `WhatsAppAttendanceController.php`: Atendimento (atendentes)
- `WhatsAppWebhookController.php`: Recebimento de webhooks (público)

## 🚀 Instalação

### 1. Executar Migration

```bash
php database/scripts/run_migrations.php
```

Ou execute manualmente o arquivo:
```sql
database/migrations/031_create_whatsapp_system_tables.sql
```

### 2. Configurar Evolution API

1. Acesse: **Configurações → Instâncias WhatsApp → Nova Instância**
2. Preencha:
   - **Nome**: Nome da instância (ex: "Atendimento Principal")
   - **Instance Key**: Chave única (ex: "atendimento-principal")
   - **URL da Evolution API**: URL da sua Evolution API
   - **API Key**: Sua chave de API da Evolution API
   - **Webhook URL**: URL base do seu sistema (ex: `https://seudominio.com.br`)

### 3. Conectar Instância

1. Após criar a instância, clique em **"Conectar"**
2. Um QR Code será gerado
3. Escaneie com o WhatsApp que deseja usar
4. Aguarde a conexão (status mudará para "CONNECTED")

## 📱 Uso do Sistema

### Para Administradores

#### Gerenciar Instâncias
- Acesse: **WhatsApp → Instâncias**
- Crie, conecte e desconecte instâncias
- Monitore status das conexões

#### Gerenciar Filas
- Crie filas de atendimento (ex: Suporte, Vendas, Financeiro)
- Adicione atendentes às filas
- Configure horários de atendimento

#### Configurar Usuários
- Atribua roles: ADMIN, SUPERVISOR ou ATTENDANT
- Defina máximo de chats simultâneos por usuário
- Ative/desative acesso ao WhatsApp

### Para Atendentes

#### Atender Conversas
1. Acesse: **WhatsApp → Atendimento**
2. Veja lista de conversas abertas
3. Clique em uma conversa para abrir
4. Envie mensagens normalmente
5. Feche o atendimento quando terminar

#### Transferir Atendimento
- Use o botão "Transferir" na conversa
- Selecione o atendente de destino
- Adicione motivo (opcional)

## 🔌 Integração com Evolution API

### Endpoints Utilizados

O sistema utiliza os seguintes endpoints da Evolution API:

- `POST /instance/create`: Criar instância
- `GET /instance/{key}/connect`: Conectar e gerar QR Code
- `GET /instance/{key}/qrcode`: Obter QR Code
- `GET /instance/{key}/connectionState`: Status da conexão
- `POST /instance/{key}/sendText`: Enviar mensagem de texto
- `POST /instance/{key}/sendMedia`: Enviar mídia
- `PUT /instance/{key}/chat/markMessageAsRead`: Marcar como lida
- `POST /instance/{key}/webhook/set`: Configurar webhook
- `DELETE /instance/{key}/logout`: Desconectar

### Webhook

O webhook recebe os seguintes eventos:

- **connection.update**: Atualização de status de conexão
- **qrcode.updated**: Atualização do QR Code
- **messages.upsert**: Novas mensagens recebidas/enviadas
- **messages.update**: Atualização de mensagens (lidas, deletadas)

**URL do Webhook**: `https://seudominio.com.br/whatsapp/webhook`

## 🔄 Fluxo de Atendimento

1. **Mensagem Recebida**
   - Webhook recebe mensagem da Evolution API
   - Sistema cria/atualiza contato
   - Sistema cria/atualiza conversa
   - Sistema salva mensagem no banco

2. **Distribuição Automática**
   - Se conversa não tem atendente, busca fila
   - Sistema busca próximo atendente disponível (round-robin)
   - Cria atendimento automaticamente

3. **Atendimento Manual**
   - Atendente seleciona conversa
   - Sistema cria/atualiza atendimento
   - Atendente envia mensagens
   - Sistema salva e envia via Evolution API

4. **Encerramento**
   - Atendente fecha atendimento
   - Sistema marca conversa como fechada
   - Opcional: coleta avaliação

## 🎨 Frontend

### Interface de Atendimento

- **Sidebar Esquerda**: Lista de conversas
  - Filtros por status, fila e busca
  - Indicador de mensagens não lidas
  - Preview da última mensagem

- **Área Central**: Chat
  - Mensagens em tempo real
  - Campo de envio
  - Indicadores de status

### Atualização em Tempo Real

O sistema usa **polling** a cada 3 segundos para buscar novas mensagens. Para melhor performance, considere implementar WebSockets no futuro.

## 🔐 Permissões

### Roles de Usuário

- **ADMIN**: Acesso total (instâncias, filas, atendimentos)
- **SUPERVISOR**: Pode ver todas as conversas e transferir
- **ATTENDANT**: Apenas suas próprias conversas

### Campos Adicionados na Tabela Users

- `whatsapp_role`: Papel no sistema (ADMIN, SUPERVISOR, ATTENDANT)
- `whatsapp_is_active`: Se pode usar o sistema
- `whatsapp_max_chats`: Máximo de chats simultâneos

## 📊 Melhorias Futuras

### Sugestões de Implementação

1. **WebSockets**
   - Substituir polling por WebSockets para atualização em tempo real
   - Melhor performance e menor carga no servidor

2. **Chatbot/IA**
   - Integração com IA para respostas automáticas
   - Respostas rápidas pré-configuradas
   - Detecção de intenção

3. **Relatórios**
   - Dashboard com métricas
   - Tempo médio de resposta
   - Taxa de resolução
   - Satisfação do cliente

4. **Notificações**
   - Notificações push para novos chats
   - Alertas de mensagens não lidas
   - Lembretes de atendimentos pendentes

5. **Tags e Categorização**
   - Sistema de tags para conversas
   - Categorização automática
   - Filtros avançados

6. **Histórico e Busca**
   - Busca avançada de mensagens
   - Exportação de conversas
   - Relatórios de atendimento

## 🛠️ Manutenção

### Logs

Os logs são salvos em:
- `storage/logs/whatsapp.log`: Logs gerais
- `storage/logs/whatsapp-webhook.log`: Logs de webhooks

### Troubleshooting

**Instância não conecta:**
1. Verifique se a Evolution API está acessível
2. Verifique se a API Key está correta
3. Verifique os logs em `storage/logs/whatsapp.log`

**Mensagens não chegam:**
1. Verifique se o webhook está configurado corretamente
2. Verifique se a URL do webhook está acessível publicamente
3. Verifique os logs em `storage/logs/whatsapp-webhook.log`

**Atendimento não distribui:**
1. Verifique se há atendentes na fila
2. Verifique se os atendentes estão online
3. Verifique se não excederam o limite de chats

## 📝 Notas Importantes

- O sistema suporta múltiplas instâncias simultâneas
- Cada instância pode ter seu próprio número de WhatsApp
- As mensagens são salvas permanentemente no banco
- O sistema funciona mesmo se a Evolution API estiver temporariamente offline (mensagens serão processadas quando voltar)

## 🔗 Links Úteis

- [Documentação Evolution API](https://doc.evolution-api.com/)
- [GitHub Evolution API](https://github.com/EvolutionAPI/evolution-api)

