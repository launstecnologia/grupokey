# Sistema de Faturamento - Documentação

## Visão Geral

O sistema de faturamento permite fazer upload de relatórios Excel e processar dados de faturamento, vinculando automaticamente com estabelecimentos já cadastrados através de CPF/CNPJ.

## Funcionalidades

### 1. Upload de Relatórios Excel
- **Localização**: Menu lateral > Faturamento (apenas para administradores)
- **Formatos aceitos**: .xls, .xlsx
- **Tamanho máximo**: 10MB
- **Processamento automático**: Vinculação por CPF/CNPJ, cálculo de totais

### 2. Estrutura Esperada do Excel

O sistema espera um arquivo Excel com a seguinte estrutura:

```
APURAÇÃO
008 - KEY SOLUÇÕES - MG

Nome                | CNPJ/CPF        | CONTA     | REPRESENTANTE              | TPV Total    | Markup
Carlos Eduardo      | 409.323.115-04  | 186951716 | Gestor: 008 GESTOR        | R$ 3.510,00  | R$ 10,52
EVANDRO CORREA      | 33.779.942/0001-79| 794433955| Gestor: 008 GESTOR        | R$ -         | R$ -
```

### 3. Processamento Automático

- **Detecção de cabeçalho**: Busca automaticamente pelo título "APURAÇÃO"
- **Vinculação**: Compara CPF/CNPJ com estabelecimentos cadastrados
- **Cálculo de totais**: Soma TPV Total e Markup automaticamente
- **Status**: PROCESSING → COMPLETED ou ERROR

### 4. Gestão de Relatórios

#### Lista de Relatórios
- Visualização de todos os relatórios processados
- Filtros por status, data de upload
- Informações resumidas (total de registros, valores)

#### Detalhes do Relatório
- Lista completa dos dados processados
- Identificação de registros vinculados/não vinculados
- Totais calculados automaticamente
- Exportação para Excel

### 5. Vinculação Manual

Para registros não vinculados automaticamente:
- Busca por estabelecimento (nome, CNPJ, CPF)
- Vinculação manual através de interface
- Possibilidade de desvincular

## Como Usar

### Passo 1: Acessar o Sistema
1. Faça login como administrador
2. No menu lateral, clique em "Faturamento"

### Passo 2: Upload do Relatório
1. Clique em "Upload de Relatório"
2. Selecione o arquivo Excel
3. Clique em "Processar Arquivo"
4. Aguarde o processamento

### Passo 3: Revisar Resultados
1. Visualize o relatório processado
2. Verifique registros não vinculados
3. Faça vinculações manuais se necessário

### Passo 4: Exportar (Opcional)
1. Clique em "Exportar Excel" para baixar relatório processado
2. Arquivo inclui status de vinculação

## Estrutura do Banco de Dados

### Tabela: billing_reports
- Armazena informações dos relatórios
- Status: PROCESSING, COMPLETED, ERROR
- Totais calculados automaticamente

### Tabela: billing_data
- Armazena dados individuais de cada linha
- Vinculação opcional com estabelecimentos
- Valores monetários em DECIMAL(15,2)

## Limitações e Considerações

### Formatos de CPF/CNPJ
- Sistema remove automaticamente pontos, traços e barras para comparação
- Suporta formatos: 123.456.789-00, 12.345.678/0001-90

### Valores Monetários
- Converte vírgula para ponto (formato brasileiro)
- Valores vazios ou "-" são tratados como 0.00

### Performance
- Índices criados para otimizar consultas
- Processamento em lotes para arquivos grandes

## Solução de Problemas

### Erro de Upload
- Verificar formato do arquivo (.xls, .xlsx)
- Verificar tamanho (máximo 10MB)
- Verificar estrutura do Excel

### Vinculação Não Funciona
- Verificar se CPF/CNPJ está correto no Excel
- Verificar se estabelecimento está cadastrado e aprovado
- Usar vinculação manual se necessário

### Processamento Lento
- Arquivos muito grandes podem demorar
- Verificar logs do sistema para erros específicos

## Segurança

- Apenas administradores podem acessar
- Arquivos temporários são removidos após processamento
- Validação de tipos de arquivo
- Sanitização de dados de entrada
