<?php
/**
 * Script de importação de dados do CSV converted_register.csv
 * Valida e importa estabelecimentos/clientes evitando CPF/CNPJ duplicados
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/Core/AutoConfig.php';
require_once __DIR__ . '/../../app/Core/Database.php';
require_once __DIR__ . '/../../app/Models/Establishment.php';

use App\Core\AutoConfig;
use App\Core\Database;
use App\Models\Establishment;

// Inicializar configuração automática
AutoConfig::init();

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Caminho do arquivo CSV
$csvFile = __DIR__ . '/../../converted_register.csv';

if (!file_exists($csvFile)) {
    die("ERRO: Arquivo CSV não encontrado: $csvFile\n");
}

echo "=== INICIANDO IMPORTAÇÃO DO CSV ===\n";
echo "Arquivo: $csvFile\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance();
    $establishmentModel = new Establishment();
    
    // Estatísticas
    $stats = [
        'total' => 0,
        'success' => 0,
        'skipped_duplicate_cpf' => 0,
        'skipped_duplicate_cnpj' => 0,
        'errors' => 0,
        'errors_list' => []
    ];
    
    // Abrir arquivo CSV
    $handle = fopen($csvFile, 'r');
    if (!$handle) {
        throw new Exception("Não foi possível abrir o arquivo CSV");
    }
    
    // Ler primeira linha (cabeçalho) - pode ter formato estranho com aspas
    $headerLine = fgets($handle);
    if (!$headerLine) {
        throw new Exception("Arquivo CSV vazio ou inválido");
    }
    
    // Processar cabeçalho - remover aspas e separar por ponto e vírgula
    $headerLine = trim($headerLine);
    // Remover aspas externas se existirem
    if (preg_match('/^"(.*)"$/', $headerLine, $matches)) {
        $headerLine = $matches[1];
    }
    
    // Separar por ponto e vírgula e limpar cada campo
    $headerFields = explode(';', $headerLine);
    $headerFields = array_map(function($field) {
        // Remover aspas duplas aninhadas
        $field = str_replace('""', '', $field);
        $field = trim($field, ' "');
        return $field;
    }, $headerFields);
    
    // Mapear índices das colunas
    $columnMap = [];
    foreach ($headerFields as $index => $field) {
        $fieldUpper = mb_strtoupper(trim($field));
        if (stripos($fieldUpper, 'CNPJ') !== false) {
            $columnMap['cnpj'] = $index;
        } elseif (stripos($fieldUpper, 'CPF') !== false) {
            $columnMap['cpf'] = $index;
        } elseif (stripos($fieldUpper, 'CADASTRO') !== false) {
            $columnMap['cadastro'] = $index;
        } elseif (stripos($fieldUpper, 'SEGMENTO') !== false) {
            $columnMap['segmento'] = $index;
        } elseif (stripos($fieldUpper, 'RAZÃO SOCIAL') !== false || stripos($fieldUpper, 'RAZAO SOCIAL') !== false) {
            $columnMap['razao_social'] = $index;
        } elseif (stripos($fieldUpper, 'NOME FANTASIA') !== false) {
            $columnMap['nome_fantasia'] = $index;
        } elseif (stripos($fieldUpper, 'NOME COMPLETO') !== false) {
            $columnMap['nome_completo'] = $index;
        } elseif (stripos($fieldUpper, 'DATA DE NASCIMENTO') !== false || stripos($fieldUpper, 'NASCIMENTO') !== false) {
            $columnMap['data_nascimento'] = $index;
        } elseif (stripos($fieldUpper, 'TELEFONE') !== false) {
            $columnMap['telefone'] = $index;
        } elseif (stripos($fieldUpper, 'ENDEREÇO') !== false || stripos($fieldUpper, 'ENDERECO') !== false) {
            $columnMap['endereco'] = $index;
        } elseif (stripos($fieldUpper, 'BAIRRO') !== false) {
            $columnMap['bairro'] = $index;
        } elseif (stripos($fieldUpper, 'CIDADE') !== false) {
            $columnMap['cidade'] = $index;
        } elseif (stripos($fieldUpper, 'UF') !== false) {
            $columnMap['uf'] = $index;
        } elseif (stripos($fieldUpper, 'CEP') !== false) {
            $columnMap['cep'] = $index;
        } elseif (stripos($fieldUpper, 'E-MAIL') !== false || stripos($fieldUpper, 'EMAIL') !== false) {
            $columnMap['email'] = $index;
        }
    }
    
    echo "Mapeamento de colunas encontrado:\n";
    print_r($columnMap);
    echo "\n";
    
    $lineNumber = 1; // Já lemos o cabeçalho
    
    // Processar cada linha
    while (($line = fgets($handle)) !== false) {
        $lineNumber++;
        $stats['total']++;
        
        // Pular linhas vazias
        $line = trim($line);
        if (empty($line)) {
            continue;
        }
        
        try {
            // Processar linha - remover aspas externas se existirem
            if (preg_match('/^"(.*)"$/', $line, $matches)) {
                $line = $matches[1];
            }
            
            // Separar por ponto e vírgula
            $fields = explode(';', $line);
            $fields = array_map(function($field) {
                // Remover aspas duplas aninhadas
                $field = str_replace('""', '', $field);
                $field = trim($field, ' "');
                return $field;
            }, $fields);
            
            // Extrair dados usando o mapeamento
            $getField = function($key) use ($fields, $columnMap) {
                if (!isset($columnMap[$key])) {
                    return null;
                }
                $index = $columnMap[$key];
                return isset($fields[$index]) ? trim($fields[$index]) : null;
            };
            
            $cnpj = $getField('cnpj');
            $cpf = $getField('cpf');
            $razaoSocial = $getField('razao_social');
            $nomeFantasia = $getField('nome_fantasia');
            $nomeCompleto = $getField('nome_completo');
            $segmento = $getField('segmento');
            $telefone = $getField('telefone');
            $email = $getField('email');
            $endereco = $getField('endereco');
            $bairro = $getField('bairro');
            $cidade = $getField('cidade');
            $uf = $getField('uf');
            $cep = $getField('cep');
            $dataNascimento = $getField('data_nascimento');
            
            // Limpar e normalizar CPF/CNPJ (remover pontos, traços, barras)
            $cpfClean = $cpf ? preg_replace('/[^0-9]/', '', $cpf) : null;
            $cnpjClean = $cnpj ? preg_replace('/[^0-9]/', '', $cnpj) : null;
            
            // Verificar se CPF já existe
            if ($cpfClean && strlen($cpfClean) >= 11) {
                $existing = $establishmentModel->findByCpf($cpfClean);
                if ($existing) {
                    echo "Linha $lineNumber: CPF já existe no banco (CPF: $cpfClean, ID: {$existing['id']})\n";
                    $stats['skipped_duplicate_cpf']++;
                    continue;
                }
            }
            
            // Verificar se CNPJ já existe
            if ($cnpjClean && strlen($cnpjClean) >= 14) {
                $existing = $establishmentModel->findByCnpj($cnpjClean);
                if ($existing) {
                    echo "Linha $lineNumber: CNPJ já existe no banco (CNPJ: $cnpjClean, ID: {$existing['id']})\n";
                    $stats['skipped_duplicate_cnpj']++;
                    continue;
                }
            }
            
            // Determinar tipo de registro (PF ou PJ)
            $registrationType = ($cnpjClean && strlen($cnpjClean) >= 14) ? 'PJ' : 'PF';
            
            // Preparar dados para inserção
            // Usar valores reais ou null, nunca valores padrão genéricos
            $establishmentData = [
                'registration_type' => $registrationType,
                'cpf' => $cpfClean ?: null,
                'cnpj' => $cnpjClean ?: null,
                'razao_social' => $razaoSocial ?: null,
                'nome_completo' => $nomeCompleto ?: $razaoSocial ?: null,
                'nome_fantasia' => $nomeFantasia ?: $nomeCompleto ?: $razaoSocial ?: null,
                'segmento' => $segmento ?: '0',
                'telefone' => $telefone ?: '',
                'email' => $email ?: '',
                'produto' => null, // Não temos informação de produto no CSV
                'cep' => $cep ?: '',
                'logradouro' => $endereco ?: '',
                'numero' => '',
                'complemento' => null,
                'bairro' => $bairro ?: '',
                'cidade' => $cidade ?: '',
                'uf' => $uf ?: '',
                'banco' => null,
                'agencia' => null,
                'conta' => null,
                'tipo_conta' => null,
                'chave_pix' => null,
                'observacoes' => null,
                'status' => 'PENDING',
                'created_by_user_id' => null,
                'created_by_representative_id' => null
            ];
            
            // Inserir estabelecimento
            $establishmentId = $establishmentModel->create($establishmentData);
            
            if ($establishmentId) {
                echo "Linha $lineNumber: ✓ Importado com sucesso (ID: $establishmentId";
                if ($cpfClean) echo ", CPF: $cpfClean";
                if ($cnpjClean) echo ", CNPJ: $cnpjClean";
                echo ")\n";
                $stats['success']++;
            } else {
                throw new Exception('Falha ao inserir estabelecimento');
            }
            
        } catch (\Exception $e) {
            $stats['errors']++;
            $stats['errors_list'][] = [
                'line' => $lineNumber,
                'error' => $e->getMessage(),
                'data' => $nomeCompleto ?? $razaoSocial ?? 'N/A'
            ];
            echo "Linha $lineNumber: ✗ ERRO - {$e->getMessage()}\n";
        }
    }
    
    fclose($handle);
    
    // Exibir estatísticas finais
    echo "\n=== IMPORTAÇÃO CONCLUÍDA ===\n";
    echo "Total de linhas processadas: {$stats['total']}\n";
    echo "Importados com sucesso: {$stats['success']}\n";
    echo "Ignorados (CPF duplicado): {$stats['skipped_duplicate_cpf']}\n";
    echo "Ignorados (CNPJ duplicado): {$stats['skipped_duplicate_cnpj']}\n";
    echo "Erros: {$stats['errors']}\n";
    
    if ($stats['errors'] > 0 && count($stats['errors_list']) > 0) {
        echo "\n=== LISTA DE ERROS ===\n";
        foreach ($stats['errors_list'] as $error) {
            echo "Linha {$error['line']}: {$error['error']} (Dados: {$error['data']})\n";
        }
    }
    
    echo "\n";
    
} catch (\Exception $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

