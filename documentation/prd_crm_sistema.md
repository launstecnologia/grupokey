# PRD - Sistema de CRM Completo
**Versão Final** - Com Sistema de Login para Representantes

---

## MÓDULO 1: DASHBOARD

### 1.1 Dashboard Admin
**Acesso:** Usuários Admin/Gestor/Operador

**Componentes:**
- **KPI Cards:**
  - Faturamento último mês — PagSeguro
  - Faturamento último mês — FGTS  
  - Nº de cadastros — PagSeguro (último mês)
  - Nº de cadastros — FGTS (último mês)
  - Nº de Membros KEY (último mês)
  - Nº total de clientes de cada produto

- **Gráficos:**
  - Evolução mensal de cadastros por produto
  - Top 5 cidades (habilitados)

- **Filtros Globais:**
  - **Período:** Campo de data - Ex: 01/05/25 à 31/08/25 (sem data máxima)
  - **Produto:** Dropdown (PagSeguro, FGTS, etc.)

- **Ações Rápidas:**
  - Botão: "Ir para Estabelecimentos" (pré-filtrado)
  - Botão: "Exportar Indicadores" (Excel)

### 1.2 Dashboard Representante
**Acesso:** Representantes logados

**Componentes:**
- **Meus KPIs:**
  - Total de clientes cadastrados
  - Clientes aprovados este mês
  - Clientes pendentes
  - Chamados em aberto dos meus clientes

- **Ações Rápidas:**
  - Cadastrar Novo Cliente
  - Ver Meus Clientes
  - Meus Chamados
  - Material de Apoio

**Regras:**
- Período padrão = primeiro ao último dia do mês anterior
- Dados mostram apenas clientes do representante logado

---

## MÓDULO 2: AUTENTICAÇÃO UNIFICADA

### 2.1 Tela de Login (Única para todos)

**Campos:**
| Campo | Tipo | Obrigatório | Validação |
|-------|------|-------------|-----------|
| E-mail | Email | ✓ | Formato de email |
| Senha | Password | ✓ | Mínimo 6 caracteres |

**Fluxo de Autenticação:**
1. Sistema verifica se email existe (User ou Representative)
2. Valida credenciais conforme tipo de usuário
3. Controla tentativas de acesso (máximo 5)
4. Redireciona para dashboard específico

**Ações:**
- **Entrar:** Botão principal
- **Esqueci minha senha:** Link para recuperação

### 2.2 Recuperação de Senha (Unificada)

**Campos:**
| Campo | Tipo | Obrigatório |
|-------|------|-------------|
| E-mail | Email | ✓ |

**Funcionalidades:**
- Busca em ambas tabelas (User e Representative)
- Envia link de redefinição por email
- Link sem validade de expiração
- Processo único para ambos tipos de usuário

### 2.3 Primeiro Acesso (Representante)

**Fluxo:**
1. Representante recebe credenciais por email
2. No primeiro login, sistema força alteração de senha
3. Nova senha deve atender critérios de segurança
4. Após alteração, acesso liberado ao dashboard

---

## MÓDULO 3: ESTABELECIMENTOS/CLIENTES

### 3.1 Listagem Admin - Habilitados

**Campos da Tabela:**
| Campo | Tipo | Obrigatório |
|-------|------|-------------|
| CPF/CNPJ | Texto formatado | ✓ |
| Nome Completo | Texto | ✓ |
| Nome Fantasia | Texto | ✓ |
| Telefone | Texto formatado | ✓ |
| Cidade | Texto | ✓ |
| Produto | Badge | ✓ |
| Taxa | Decimal | - |
| Data Cadastro | Data | ✓ |
| Status | Badge | ✓ |
| Criado por | Badge (Admin/Representante) | ✓ |

**Filtros:**
- Cidade, Ordem Alfabética, Data, Faturamento, Produto
- **Novo:** Filtro por "Criado por" (Admin/Representante específico)

### 3.2 Listagem Representante - Meus Clientes

**Estrutura:** Similar ao Admin, mas:
- **Filtro automático:** Apenas clientes criados pelo representante logado
- **Sem filtro "Criado por"** (todos são seus)
- **Ações limitadas:** Visualizar, Editar próprios clientes

### 3.3 Formulário Cadastro (Admin e Representante)

**BLOCO A - Identificação (Comum)**

| Campo | Tipo | Validação | Obrigatório |
|-------|------|-----------|-------------|
| Tipo de Cadastro | Radio (PF/PJ) | - | ✓ |
| CPF | Input mascarado | Validação CPF | ✓ (se PF) |
| CNPJ | Input mascarado | Validação CNPJ + API Receita | ✓ (se PJ) |
| Razão Social | Texto | - | - (se PJ) |
| Nome Completo | Texto | - | ✓ |
| Nome Fantasia | Texto | - | ✓ |
| Segmento/Ramo | Dropdown | - | ✓ |
| Telefone Celular | Input mascarado | - | ✓ |
| E-mail | Email | Validação email | ✓ |
| Produto | Dropdown | **Representante:** Apenas produtos permitidos | ✓ |

**BLOCO B - Endereço (Comum)**

| Campo | Tipo | Validação | Obrigatório |
|-------|------|-----------|-------------|
| CEP | Input mascarado | Auto-preenchimento | ✓ |
| Logradouro | Texto | Auto-preenchido | ✓ |
| Número | Texto | - | ✓ |
| Complemento | Texto | - | - |
| Bairro | Texto | Auto-preenchido | ✓ |
| Cidade | Texto | Auto-preenchido | ✓ |
| UF | Dropdown | Auto-preenchido | ✓ |

**BLOCO C - Campos por Produto**

#### C.1 - PagSeguro/MP
| Campo | Tipo | Opções | Obrigatório |
|-------|------|--------|-------------|
| Data de Nascimento | Date | - | ✓ (se PF) |
| Previsão de Faturamento | Decimal | - | ✓ |
| Tabela de Taxa | Dropdown | 77299, D30, ELITE, MASTER, MP 1, MP 2, PERSONALIZADO | ✓ |
| Modelo de Máquina | Dropdown | CHIP 3, PRO 2, SMART, CDX, EVO | ✓ |
| Modelo Vendido/Utilizado | Texto | - | ✓ |
| Forma de Pagamento | Dropdown | À vista, Cartão, Isento, Criação | ✓ |
| Valor (R$) | Decimal | - | ✓ |

#### C.2 - Flamex
| Campo | Tipo | Obrigatório |
|-------|------|-------------|
| Favorecido | Texto | ✓ |
| CPF/CNPJ do Favorecido | Input mascarado | ✓ |
| Banco | Dropdown | ✓ |
| Agência | Texto | ✓ |
| Conta + Dígito | Texto | ✓ |
| Tipo de Conta | Radio | ✓ |
| Tipo de Chave PIX | Dropdown | ✓ |
| Chave PIX | Texto | ✓ |

#### C.3 - Diversos
| Campo | Tipo | Obrigatório |
|-------|------|-------------|
| Produto | Dropdown | ✓ |
| Forma de Pagamento | Dropdown | ✓ |
| Valor (R$) | Decimal | ✓ |

**BLOCO D - Documentos**
- Upload conforme tipo de pessoa e produto
- Sistema automaticamente cria pasta no Google Drive
- Notificação para representante sobre status

---

## MÓDULO 4: GESTÃO DE REPRESENTANTES (Admin)

### 4.1 Listagem de Representantes

**Campos da Tabela:**
| Campo | Tipo | Obrigatório |
|-------|------|-------------|
| Nome | Texto | ✓ |
| CPF | Texto formatado | ✓ |
| Email | Email | ✓ |
| Telefone | Texto formatado | ✓ |
| Produtos | Tags múltiplas | ✓ |
| Status | Badge | ✓ |
| Último Login | Data/Hora | - |
| Total Clientes | Número | - |

**Ações por Linha:**
- Visualizar, Editar, Redefinir Senha, Ativar/Desativar
- **Nova:** Bloquear/Desbloquear (controle de tentativas)

### 4.2 Formulário Representante

**Dados Pessoais:**
| Campo | Tipo | Obrigatório |
|-------|------|-------------|
| Nome Completo | Texto | ✓ |
| CPF | Input mascarado | ✓ |
| CNPJ | Input mascarado | - |
| Telefone | Input mascarado | ✓ |
| Email | Email | ✓ |
| Endereço Completo | Campos múltiplos | ✓ |

**Configuração de Acesso:**
- **Produtos permitidos:** Checkboxes múltiplos
- **Permissões:** Cadastro, Edição, Relatórios
- **Credenciais:** Sistema gera senha temporária
- **Envio:** Email automático com credenciais

---

## MÓDULO 5: CHAMADOS

### 5.1 Chamados Admin

**Funcionalidades:**
- Ver todos os chamados do sistema
- Atribuir chamados para técnicos
- Gerenciar status (Aberto → Andamento → Finalizado)

### 5.2 Chamados Representante

**Funcionalidades:**
- Ver apenas chamados dos seus clientes
- Criar novos chamados para seus clientes
- Acompanhar status (sem poder alterar)

**Formulário Representante:**
| Campo | Tipo | Obrigatório |
|-------|------|-------------|
| Cliente | Dropdown | Apenas meus clientes | ✓ |
| Assunto | Texto | ✓ |
| Descrição | Textarea | ✓ |

---

## MÓDULO 6: RELATÓRIOS E EXPORTAÇÕES

### 6.1 Relatórios Admin

**Tipos:**
- Todos os clientes (simplificado/detalhado)
- Por representante específico
- Por produto
- Relatórios financeiros

### 6.2 Relatórios Representante

**Tipos:**
- Meus clientes (simplificado/detalhado)
- Minha performance mensal
- Status dos meus cadastros

**Limitações:**
- Apenas dados dos próprios clientes
- Não tem acesso a dados de outros representantes

---

## MÓDULO 7: ESTOQUE DE MÁQUINAS (Admin)

### 7.1 Dashboard de Máquinas

**KPIs:**
- Total por modelo (CHIP 3, PLUS 2, PRO 2, SMART 2)
- Disponíveis vs Vendidas
- Máquinas por representante

### 7.2 Gestão de Lotes

**Funcionalidades:**
- Cadastro massivo por S/N
- Vinculação a clientes
- Controle de status

---

## MÓDULO 8: MATERIAL DE APOIO

### 8.1 Acesso Admin
- Gestão completa de materiais
- Controle de visibilidade
- Organização por produto

### 8.2 Acesso Representante
- Visualização apenas
- Materiais dos produtos que trabalha
- Links para Google Drive

---

## MÓDULO 9: AUDITORIA COMPLETA

### 9.1 Sistema de Logs

**Registros capturados:**
- **Usuário Admin:** Todas as ações administrativas
- **Representante:** Cadastros, alterações, login/logout
- **Dados:** IP, timestamp, valores antes/depois
- **Módulos:** Identificação de qual área foi alterada

### 9.2 Relatórios de Auditoria

**Filtros:**
- Por usuário (admin ou representante específico)
- Por período
- Por tipo de ação
- Por módulo do sistema

---

## PERMISSÕES E CONTROLE DE ACESSO

### Admin/Usuários:
- **ADMIN:** Acesso total
- **GESTOR:** Tudo exceto gestão de usuários
- **OPERADOR:** Cadastros e consultas
- **VISUALIZADOR:** Apenas consultas e relatórios

### Representantes:
- **Cadastro:** Apenas produtos permitidos
- **Visualização:** Apenas próprios clientes
- **Edição:** Apenas próprios clientes (se permitido)
- **Relatórios:** Apenas próprios dados
- **Chamados:** Apenas clientes próprios

---

## REGRAS DE NEGÓCIO

### Aprovação de Clientes:
- **PagSeguro/MP:** Aprovação automática
- **Demais produtos:** Aprovação manual pelo admin
- **Notificações:** Representante é informado sobre status

### Controle de Acesso:
- **5 tentativas máximas** de login
- **Bloqueio automático** após limite
- **Redefinição de senha** para desbloqueio

### Auditoria:
- **Todas as ações** são registradas
- **Identificação clara** entre admin e representante
- **Histórico completo** por registro

---

## FLUXOS CRÍTICOS

### 1. Cadastro pelo Representante:
Representante → Seleciona produto permitido → Preenche dados → Envia para aprovação → Admin aprova → Representante é notificado → Pasta Google Drive criada

### 2. Primeiro Login Representante:
Admin cria representante → Email com credenciais → Primeiro login → Força alteração senha → Acesso liberado

### 3. Controle de Permissões:
Sistema verifica produtos permitidos → Exibe apenas opções válidas → Bloqueia cadastro em produtos não autorizados

Este PRD contempla um sistema completo com dupla autenticação, controle granular de permissões e auditoria completa de todas as ações.