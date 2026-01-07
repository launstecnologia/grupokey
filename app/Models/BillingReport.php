<?php

namespace App\Models;

use App\Core\Database;

class BillingReport
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Processa arquivo Excel de faturamento usando método simples
     */
    public function processExcelFile($filePath, $userId = null, $reportTitle = null, $companyCode = null)
    {
        try {
            error_log("=== INÍCIO PROCESSAMENTO ===");
            error_log("Arquivo: " . $filePath);
            error_log("Usuário: " . $userId);
            error_log("Título: " . $reportTitle);
            error_log("Código da empresa: " . $companyCode);
            
            // Detectar tipo de arquivo
            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            error_log("Extensão: " . $fileExtension);
            
            if ($fileExtension === 'csv') {
                error_log("Processando como CSV");
                return $this->processCsvFile($filePath, $userId, $reportTitle, $companyCode);
            } elseif ($fileExtension === 'xlsx') {
                error_log("Processando como XLSX");
                // Verificar se podemos processar XLSX
                if (!function_exists('shell_exec')) {
                    error_log("AVISO: shell_exec não está disponível. Tentando processar XLSX diretamente...");
                    // Tentar processar como CSV se possível, ou lançar erro
                    throw new \Exception('Processamento de arquivos XLSX requer shell_exec que não está disponível no servidor. Por favor, converta o arquivo para CSV antes de fazer upload.');
                }
                return $this->processXlsxFileSimple($filePath, $userId, $reportTitle, $companyCode);
            } else {
                throw new \Exception('Formato de arquivo não suportado. Use CSV ou XLSX.');
            }
            
        } catch (\Exception $e) {
            error_log('Erro ao processar arquivo Excel: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Processa arquivo CSV
     */
    private function processCsvFile($filePath, $userId, $reportTitle = null, $companyCode = null)
    {
        error_log("=== PROCESSANDO CSV ===");
        error_log("Arquivo CSV: " . $filePath);
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            error_log("ERRO: Não foi possível abrir o arquivo CSV");
            throw new \Exception('Não foi possível abrir o arquivo CSV.');
        }
        
        $rows = [];
        $rowNumber = 0;
        
        // Tentar detectar o separador (vírgula ou ponto e vírgula)
        $separator = ',';
        $firstLine = fgets($handle);
        rewind($handle);
        
        // Contar vírgulas e ponto e vírgulas na primeira linha
        $commaCount = substr_count($firstLine, ',');
        $semicolonCount = substr_count($firstLine, ';');
        
        // Usar o separador que aparecer mais vezes
        if ($semicolonCount > $commaCount) {
            $separator = ';';
            error_log("Separador detectado: ponto e vírgula (;)");
        } else {
            error_log("Separador detectado: vírgula (,)");
        }
        
        while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
            $rowNumber++;
            error_log("Linha $rowNumber: " . json_encode($data));
            
            // Pular linhas vazias
            if (empty(array_filter($data))) {
                error_log("Linha $rowNumber vazia, pulando");
                continue;
            }
            
            // Detectar cabeçalho - verificar se a primeira coluna contém "Nome" ou se todas as colunas parecem ser cabeçalho
            if ($rowNumber === 1) {
                $firstCell = strtoupper(trim($data[0] ?? ''));
                $isHeader = (
                    stripos($firstCell, 'NOME') !== false || 
                    stripos($firstCell, 'REPRESENTANTE') !== false ||
                    stripos($firstCell, 'CNPJ') !== false ||
                    stripos($firstCell, 'CPF') !== false ||
                    stripos($firstCell, 'TPV') !== false ||
                    stripos($firstCell, 'MARKUP') !== false
                );
                
                if ($isHeader) {
                    error_log("Linha $rowNumber é cabeçalho, pulando");
                    continue; // Pular linha de cabeçalho
                }
            }
            
            $rows[] = $data;
        }
        
        fclose($handle);
        
        error_log("Total de linhas processadas: " . count($rows));
        
        return $this->processDataRows($rows, $userId, $reportTitle, $companyCode);
    }
    
    /**
     * Processa arquivo XLSX de forma simples (sem ZipArchive)
     */
    private function processXlsxFileSimple($filePath, $userId, $reportTitle = null, $companyCode = null)
    {
        error_log("=== PROCESSANDO XLSX ===");
        error_log("Arquivo XLSX: " . $filePath);
        
        // Tentar converter para CSV usando método simples
        $csvPath = $this->convertXlsxToCsvSimple($filePath);
        
        if (!$csvPath) {
            throw new \Exception('Não foi possível processar o arquivo XLSX. Tente salvar como CSV.');
        }
        
        error_log("Arquivo convertido para CSV: " . $csvPath);
        
        $result = $this->processCsvFile($csvPath, $userId, $reportTitle, $companyCode);
        
        // Limpar arquivo temporário
        if (file_exists($csvPath)) {
            unlink($csvPath);
        }
        
        return $result;
    }
    
    /**
     * Converte XLSX para CSV usando método simples (sem ZipArchive)
     */
    private function convertXlsxToCsvSimple($xlsxPath)
    {
        error_log("=== CONVERTENDO XLSX PARA CSV ===");
        
        // Método 1: Tentar usar Python se disponível
        $csvPath = $xlsxPath . '.csv';
        
        // Criar script Python simples
        $pythonScript = __DIR__ . '/../../scripts/xlsx_to_csv.py';
        $scriptDir = dirname($pythonScript);
        
        if (!is_dir($scriptDir)) {
            mkdir($scriptDir, 0755, true);
        }
        
        $pythonCode = '#!/usr/bin/env python3
import sys
import csv
import json

try:
    # Tentar importar openpyxl
    from openpyxl import load_workbook
    
    # Carregar arquivo Excel
    wb = load_workbook(sys.argv[1])
    ws = wb.active
    
    # Converter para CSV
    with open(sys.argv[2], "w", newline="", encoding="utf-8") as csvfile:
        writer = csv.writer(csvfile)
        for row in ws.iter_rows(values_only=True):
            writer.writerow(row)
    
    print("SUCCESS")
    
except ImportError:
    print("ERROR: openpyxl not installed")
except Exception as e:
    print("ERROR: " + str(e))
';
        
        file_put_contents($pythonScript, $pythonCode);
        
        // Executar conversão
        $command = "python \"$pythonScript\" \"$xlsxPath\" \"$csvPath\" 2>&1";
        error_log("Executando comando: " . $command);
        
        // Verificar se shell_exec está disponível
        if (!function_exists('shell_exec')) {
            error_log("ERRO: shell_exec não está disponível no servidor");
            return false;
        }
        
        // Usar \ para garantir que está chamando a função global
        $output = \shell_exec($command);
        error_log("Saída do Python: " . ($output ?? 'Nenhuma saída'));
        
        // Limpar script temporário
        if (file_exists($pythonScript)) {
            unlink($pythonScript);
        }
        
        if (strpos($output, 'SUCCESS') !== false && file_exists($csvPath)) {
            error_log("Conversão bem-sucedida");
            return $csvPath;
        }
        
        error_log("Conversão falhou, tentando método alternativo");
        
        // Método 2: Tentar usar PowerShell (Windows)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return $this->convertXlsxToCsvPowerShell($xlsxPath);
        }
        
        return false;
    }
    
    /**
     * Converte XLSX para CSV usando PowerShell (Windows)
     */
    private function convertXlsxToCsvPowerShell($xlsxPath)
    {
        error_log("=== TENTANDO CONVERSÃO COM POWERSHELL ===");
        
        $csvPath = $xlsxPath . '.csv';
        
        // Script PowerShell para converter XLSX para CSV
        $psScript = '
$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false

try {
    $workbook = $excel.Workbooks.Open("' . str_replace('\\', '\\\\', $xlsxPath) . '")
    $worksheet = $workbook.Worksheets.Item(1)
    
    $worksheet.SaveAs("' . str_replace('\\', '\\\\', $csvPath) . '", 6) # 6 = CSV format
    
    $workbook.Close($false)
    $excel.Quit()
    
    Write-Output "SUCCESS"
} catch {
    Write-Output "ERROR: $($_.Exception.Message)"
} finally {
    [System.Runtime.Interopservices.Marshal]::ReleaseComObject($excel) | Out-Null
}
';
        
        $psScriptPath = tempnam(sys_get_temp_dir(), 'xlsx_convert_') . '.ps1';
        file_put_contents($psScriptPath, $psScript);
        
        $command = "powershell -ExecutionPolicy Bypass -File \"$psScriptPath\" 2>&1";
        error_log("Executando PowerShell: " . $command);
        
        // Verificar se shell_exec está disponível
        if (!function_exists('shell_exec')) {
            error_log("ERRO: shell_exec não está disponível no servidor");
            return false;
        }
        
        // Usar \ para garantir que está chamando a função global
        $output = \shell_exec($command);
        error_log("Saída do PowerShell: " . ($output ?? 'Nenhuma saída'));
        
        // Limpar script temporário
        if (file_exists($psScriptPath)) {
            unlink($psScriptPath);
        }
        
        if (strpos($output, 'SUCCESS') !== false && file_exists($csvPath)) {
            error_log("Conversão PowerShell bem-sucedida");
            return $csvPath;
        }
        
        return false;
    }
    
    /**
     * Processa as linhas de dados
     */
    private function processDataRows($rows, $userId, $reportTitle = null, $companyCode = null)
    {
        error_log("=== PROCESSANDO LINHAS DE DADOS ===");
        error_log("Total de linhas: " . count($rows));
        
        // Usar dados do formulário ou extrair do arquivo
        $headerData = [
            'title' => $reportTitle ?: $this->extractHeaderData($rows)['title'] ?: 'Relatório de Faturamento',
            'company_code' => $companyCode ?: $this->extractHeaderData($rows)['company_code']
        ];
        error_log("Dados do cabeçalho: " . json_encode($headerData));
        
        // Criar registro do relatório
        $reportId = $this->createReport($headerData, $userId);
        error_log("Relatório criado com ID: " . $reportId);
        
        $processedRows = [];
        
        // Processar cada linha de dados
        foreach ($rows as $index => $row) {
            try {
                // Pular linhas que são claramente inválidas antes de processar
                if (!is_array($row) || count($row) < 3) {
                    error_log("Linha " . ($index + 1) . " ignorada: não é array válido ou tem menos de 3 colunas");
                    continue;
                }
                
                // Verificar se a primeira coluna parece ser uma data (formato YYYY-MM-DD)
                $firstCell = trim($row[0] ?? '');
                if (preg_match('/^\d{4}-\d{2}-\d{2}/', $firstCell)) {
                    error_log("Linha " . ($index + 1) . " ignorada: primeira coluna é uma data: " . $firstCell);
                    continue;
                }
                
                error_log("Processando linha " . ($index + 1) . ": " . json_encode($row));
                
                $rowData = $this->extractRowData($row);
                error_log("Dados extraídos: " . json_encode($rowData));
                
                if ($this->isValidDataRow($rowData)) {
                    error_log("Linha válida, salvando no banco");
                    $billingId = $this->saveBillingData($reportId, $rowData);
                    $processedRows[] = [
                        'billing_id' => $billingId,
                        'row_data' => $rowData
                    ];
                    error_log("Dados salvos com ID: " . $billingId);
                } else {
                    error_log("Linha inválida, pulando - Nome: '" . ($rowData['nome'] ?? 'vazio') . "', CNPJ/CPF: '" . ($rowData['cnpj_cpf'] ?? 'vazio') . "'");
                }
            } catch (\Exception $e) {
                error_log("ERRO ao processar linha " . ($index + 1) . ": " . $e->getMessage());
                // Continuar processando outras linhas mesmo se uma falhar
            }
        }
        
        // Calcular totais (sempre, mesmo se não houver linhas processadas)
        try {
            $this->calculateTotals($reportId, $processedRows);
            error_log("Totais calculados");
        } catch (\Exception $e) {
            error_log("ERRO ao calcular totais: " . $e->getMessage());
            // Atualizar status para ERROR se houver problema
            $this->db->query("UPDATE billing_reports SET status = 'ERROR', processed_at = NOW() WHERE id = ?", [$reportId]);
            throw $e;
        }
        
        return [
            'success' => true,
            'report_id' => $reportId,
            'processed_rows' => count($processedRows),
            'header_data' => $headerData
        ];
    }
    
    /**
     * Extrai dados do cabeçalho
     */
    private function extractHeaderData($rows)
    {
        $headerData = [];
        
        // Procurar pelo título "APURAÇÃO" nas primeiras linhas
        for ($i = 0; $i < min(5, count($rows)); $i++) {
            $row = $rows[$i];
            if (is_array($row) && count($row) > 0) {
                $firstCell = $row[0] ?? '';
                if (strpos($firstCell, 'APURAÇÃO') !== false) {
                    $headerData['title'] = $firstCell;
                    
                    // Procurar pelo código da empresa na próxima linha
                    if (isset($rows[$i + 1]) && is_array($rows[$i + 1])) {
                        $nextRow = $rows[$i + 1];
                        if (count($nextRow) > 0 && !empty($nextRow[0])) {
                            $headerData['company_code'] = $nextRow[0];
                        }
                    }
                    break;
                }
            }
        }
        
        return $headerData;
    }
    
    /**
     * Extrai dados de uma linha específica
     */
    private function extractRowData($row)
    {
        // Estrutura real: Nome, CNPJ/CPF, REPRESENTANTE, TPV Total, Markup
        // Garantir que temos pelo menos 3 colunas
        if (!is_array($row) || count($row) < 3) {
            error_log("Linha inválida: menos de 3 colunas. Dados: " . json_encode($row));
            return [
                'nome' => '',
                'cnpj_cpf' => '',
                'conta' => null,
                'representante' => '',
                'tpv_total' => 0.0,
                'markup' => 0.0
            ];
        }
        
        $nome = $this->cleanValue($row[0] ?? '');
        $cnpjCpf = $this->cleanValue($row[1] ?? '');
        $representante = $this->cleanValue($row[2] ?? '');
        $tpvTotal = $this->parseCurrency($row[3] ?? '');
        $markup = $this->parseCurrency($row[4] ?? '');
        
        error_log("Extraindo dados - Nome: '$nome', CNPJ/CPF: '$cnpjCpf', Representante: '$representante'");
        
        return [
            'nome' => $nome,
            'cnpj_cpf' => $cnpjCpf,
            'conta' => null, // Campo mantido para compatibilidade, mas não usado
            'representante' => $representante, // Coluna REPRESENTANTE
            'tpv_total' => $tpvTotal,
            'markup' => $markup
        ];
    }
    
    /**
     * Limpa e normaliza valores
     */
    private function cleanValue($value)
    {
        if (is_null($value)) {
            return null;
        }
        
        $value = trim($value);
        return empty($value) ? null : $value;
    }
    
    /**
     * Converte valores monetários para float (formato brasileiro)
     */
    private function parseCurrency($value)
    {
        if (is_null($value) || $value === '-' || $value === '' || trim($value) === '') {
            return 0.0;
        }
        
        // Converter para string e limpar
        $value = trim((string)$value);
        
        // Remover R$ e espaços
        $value = str_replace(['R$', 'R$ ', 'R$  ', ' '], '', $value);
        
        // Remover caracteres não numéricos exceto vírgula, ponto e sinal de menos
        $value = preg_replace('/[^\d,.-]/', '', $value);
        
        // Se estiver vazio após limpeza, retornar 0
        if (empty($value) || $value === '-' || $value === ',') {
            return 0.0;
        }
        
        // Detectar se é negativo
        $isNegative = (strpos($value, '-') !== false);
        $value = str_replace('-', '', $value);
        
        // Formato brasileiro: ponto para milhares, vírgula para decimais
        // Exemplos: 1.234,56 ou 1234,56 ou 1.234.567,89
        
        // Se tem vírgula, é formato brasileiro
        if (strpos($value, ',') !== false) {
            // Se tem ponto também, o ponto é separador de milhares
            if (strpos($value, '.') !== false) {
                // Formato: 1.234,56 ou 1.234.567,89
                // Contar quantos pontos existem antes da vírgula
                $parts = explode(',', $value);
                $integerPart = $parts[0];
                $decimalPart = $parts[1] ?? '00';
                
                // Remover todos os pontos (são separadores de milhares)
                $integerPart = str_replace('.', '', $integerPart);
                
                // Montar valor: inteiro.decimal
                $value = $integerPart . '.' . $decimalPart;
            } else {
                // Apenas vírgula: 1234,56
                $value = str_replace(',', '.', $value);
            }
        } elseif (strpos($value, '.') !== false) {
            // Apenas ponto: pode ser formato americano (1234.56) ou brasileiro sem decimais (1.234)
            // Se tem mais de um ponto, provavelmente é formato brasileiro sem decimais
            $pointCount = substr_count($value, '.');
            if ($pointCount > 1) {
                // Formato brasileiro sem decimais: 1.234.567
                $value = str_replace('.', '', $value);
            }
            // Se tem apenas um ponto, assumir que é decimal (formato americano)
        }
        
        $result = floatval($value);
        
        // Aplicar sinal negativo se necessário
        if ($isNegative) {
            $result = -$result;
        }
        
        error_log("parseCurrency: '$value' (original) -> " . $result);
        
        return $result;
    }
    
    /**
     * Verifica se a linha contém dados válidos
     */
    private function isValidDataRow($rowData)
    {
        // Verificar se tem nome válido (não pode ser uma data)
        $nome = trim($rowData['nome'] ?? '');
        if (empty($nome)) {
            return false;
        }
        
        // Verificar se o nome não é uma data (formato YYYY-MM-DD ou similar)
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $nome)) {
            error_log("Linha rejeitada: nome parece ser uma data: " . $nome);
            return false;
        }
        
        // Verificar se o nome não é apenas números (pode ser um ID, mas sem CNPJ/CPF não é válido)
        if (preg_match('/^\d+$/', $nome) && empty($rowData['cnpj_cpf'])) {
            error_log("Linha rejeitada: nome é apenas números sem CNPJ/CPF: " . $nome);
            return false;
        }
        
        // Deve ter pelo menos nome válido (não vazio, não é data)
        // CNPJ/CPF é opcional, mas se tiver deve ter formato válido
        $cnpjCpf = trim($rowData['cnpj_cpf'] ?? '');
        if (!empty($cnpjCpf)) {
            // Verificar se tem formato básico de CPF/CNPJ (pelo menos alguns números)
            $cleanDoc = preg_replace('/[^\d]/', '', $cnpjCpf);
            if (strlen($cleanDoc) < 11) {
                error_log("Linha rejeitada: CNPJ/CPF muito curto: " . $cnpjCpf);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Cria registro do relatório no banco
     */
    private function createReport($headerData, $userId)
    {
        $sql = "INSERT INTO billing_reports (title, company_code, uploaded_by, uploaded_at, status) 
                VALUES (?, ?, ?, NOW(), 'PROCESSING')";
        
        $this->db->query($sql, [
            $headerData['title'] ?? 'Relatório de Faturamento',
            $headerData['company_code'] ?? null,
            $userId
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Salva dados de faturamento no banco
     */
    private function saveBillingData($reportId, $rowData)
    {
        // Tentar encontrar estabelecimento pelo CPF/CNPJ
        $establishmentId = $this->findEstablishmentByDocument($rowData['cnpj_cpf']);
        
        $sql = "INSERT INTO billing_data (report_id, establishment_id, nome, cnpj_cpf, conta, representante, tpv_total, markup, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $this->db->query($sql, [
            $reportId,
            $establishmentId,
            $rowData['nome'],
            $rowData['cnpj_cpf'],
            $rowData['conta'],
            $rowData['representante'],
            $rowData['tpv_total'],
            $rowData['markup']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Busca estabelecimento pelo CPF/CNPJ
     */
    private function findEstablishmentByDocument($document)
    {
        if (empty($document)) {
            return null;
        }
        
        // Limpar documento (remover pontos, traços, barras)
        $cleanDocument = preg_replace('/[^\d]/', '', $document);
        
        $sql = "SELECT id FROM establishments WHERE 
                REPLACE(REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', ''), ' ', '') = ? OR
                REPLACE(REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '-', ''), '/', ''), ' ', '') = ?";
        
        $result = $this->db->fetch($sql, [$cleanDocument, $cleanDocument]);
        
        return $result ? $result['id'] : null;
    }
    
    /**
     * Calcula totais do relatório
     */
    private function calculateTotals($reportId, $processedRows)
    {
        error_log("=== CALCULANDO TOTAIS ===");
        error_log("Total de linhas processadas: " . count($processedRows));
        
        // Buscar totais diretamente do banco (mais confiável)
        $sqlCheck = "SELECT SUM(tpv_total) as sum_tpv, SUM(markup) as sum_markup, COUNT(*) as total_count 
                     FROM billing_data WHERE report_id = ?";
        $dbTotals = $this->db->fetch($sqlCheck, [$reportId]);
        
        $totalTpv = 0;
        $totalMarkup = 0;
        $totalRecords = 0;
        
        if ($dbTotals) {
            $totalTpv = floatval($dbTotals['sum_tpv'] ?? 0);
            $totalMarkup = floatval($dbTotals['sum_markup'] ?? 0);
            $totalRecords = intval($dbTotals['total_count'] ?? 0);
            
            error_log("Totais do banco: TPV=" . $totalTpv . ", Markup=" . $totalMarkup . ", Count=" . $totalRecords);
        }
        
        // Se não houver dados no banco ou se processedRows tiver mais registros, calcular dos dados processados
        if ($totalRecords == 0 && count($processedRows) > 0) {
            error_log("Calculando totais dos dados processados...");
            foreach ($processedRows as $index => $row) {
                $tpv = floatval($row['row_data']['tpv_total'] ?? 0);
                $markup = floatval($row['row_data']['markup'] ?? 0);
                
                $totalTpv += $tpv;
                $totalMarkup += $markup;
                
                error_log("Linha " . ($index + 1) . ": TPV=" . $tpv . ", Markup=" . $markup);
            }
            $totalRecords = count($processedRows);
        }
        
        error_log("Totais finais: TPV=" . $totalTpv . ", Markup=" . $totalMarkup . ", Records=" . $totalRecords);
        
        $sql = "UPDATE billing_reports SET 
                total_tpv = ?, 
                total_markup = ?, 
                total_records = ?, 
                status = 'COMPLETED',
                processed_at = NOW()
                WHERE id = ?";
        
        error_log("Atualizando relatório ID $reportId com status COMPLETED");
        error_log("SQL: " . $sql);
        error_log("Parâmetros: " . json_encode([$totalTpv, $totalMarkup, $totalRecords, $reportId]));
        
        $this->db->query($sql, [
            $totalTpv,
            $totalMarkup,
            $totalRecords,
            $reportId
        ]);
        
        error_log("Relatório atualizado com sucesso!");
    }
    
    /**
     * Lista todos os relatórios
     */
    public function getAllReports($filters = [])
    {
        $sql = "SELECT br.*, u.name as uploaded_by_name 
                FROM billing_reports br
                LEFT JOIN users u ON br.uploaded_by = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['status'])) {
            $sql .= " AND br.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND br.uploaded_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND br.uploaded_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY br.uploaded_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Busca relatório por ID
     */
    public function findById($id)
    {
        $sql = "SELECT br.*, u.name as uploaded_by_name 
                FROM billing_reports br
                LEFT JOIN users u ON br.uploaded_by = u.id
                WHERE br.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Busca dados de faturamento de um relatório
     */
    public function getBillingData($reportId)
    {
        $sql = "SELECT bd.*, e.nome_fantasia, e.razao_social, e.cidade, e.uf
                FROM billing_data bd
                LEFT JOIN establishments e ON bd.establishment_id = e.id
                WHERE bd.report_id = ?
                ORDER BY bd.nome";
        
        return $this->db->fetchAll($sql, [$reportId]);
    }
    
    /**
     * Busca dados não vinculados (sem estabelecimento)
     */
    public function getUnlinkedData($reportId)
    {
        $sql = "SELECT * FROM billing_data 
                WHERE report_id = ? AND establishment_id IS NULL
                ORDER BY nome";
        
        return $this->db->fetchAll($sql, [$reportId]);
    }
    
    /**
     * Vincula dados a um estabelecimento
     */
    public function linkToEstablishment($billingDataId, $establishmentId)
    {
        $sql = "UPDATE billing_data SET establishment_id = ? WHERE id = ?";
        return $this->db->query($sql, [$establishmentId, $billingDataId]);
    }
    
    /**
     * Remove relatório e seus dados
     */
    public function deleteReport($reportId)
    {
        $this->db->beginTransaction();
        
        try {
            // Deletar dados de faturamento
            $sql = "DELETE FROM billing_data WHERE report_id = ?";
            $this->db->query($sql, [$reportId]);
            
            // Deletar relatório
            $sql = "DELETE FROM billing_reports WHERE id = ?";
            $this->db->query($sql, [$reportId]);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Busca estabelecimentos para vinculação
     */
    public function searchEstablishments($query)
    {
        $sql = "SELECT id, nome_fantasia, razao_social, cpf, cnpj, cidade, uf
                FROM establishments 
                WHERE (nome_fantasia LIKE ? OR razao_social LIKE ? OR cpf LIKE ? OR cnpj LIKE ?)
                AND status = 'APPROVED'
                ORDER BY nome_fantasia";
        
        $searchTerm = '%' . $query . '%';
        
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
}