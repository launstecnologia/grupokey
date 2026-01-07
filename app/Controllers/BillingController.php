<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\BillingReport;
use App\Models\Establishment;
use App\Core\FileUpload;

class BillingController
{
    private $billingReport;
    private $establishment;
    private $fileUpload;
    
    public function __construct()
    {
        $this->billingReport = new BillingReport();
        $this->establishment = new Establishment();
        $this->fileUpload = new FileUpload();
        
        // Verificar autenticação
        if (!Auth::check()) {
            redirect(url('login'));
        }
    }
    
    /**
     * Lista todos os relatórios de faturamento
     */
    public function index()
    {
        $filters = [
            'limit' => 20
        ];
        
        // Aplicar filtros se fornecidos
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        
        if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        
        if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }
        
        $reports = $this->billingReport->getAllReports($filters);
        
        // Calcular estatísticas
        $allReports = $this->billingReport->getAllReports([]); // Sem filtros para estatísticas
        $stats = [
            'total' => count($allReports),
            'processando' => 0,
            'concluido' => 0,
            'erro' => 0
        ];
        
        foreach ($allReports as $report) {
            switch ($report['status']) {
                case 'PROCESSING':
                    $stats['processando']++;
                    break;
                case 'COMPLETED':
                    $stats['concluido']++;
                    break;
                case 'ERROR':
                    $stats['erro']++;
                    break;
            }
        }
        
        $data = [
            'reports' => $reports,
            'filters' => $filters,
            'stats' => $stats
        ];
        
        $this->render('billing/index', $data);
    }
    
    /**
     * Exibe formulário de upload
     */
    public function create()
    {
        $data = [
            'title' => 'Upload de Relatório de Faturamento'
        ];
        
        $this->render('billing/create', $data);
    }
    
    /**
     * Processa upload do arquivo Excel
     */
    public function store()
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            write_log("=== INÍCIO UPLOAD DE RELATÓRIO ===", 'excel_upload.log');
            write_log("Usuário ID: {$userId}", 'excel_upload.log');
            write_log("REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'), 'excel_upload.log');
            write_log("CONTENT_TYPE: " . ($_SERVER['CONTENT_TYPE'] ?? 'N/A'), 'excel_upload.log');
            write_log("CONTENT_LENGTH: " . (isset($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : 'N/A'), 'excel_upload.log');
            write_log("POST recebido: " . json_encode($_POST), 'excel_upload.log');
            write_log("FILES recebido: " . json_encode($_FILES), 'excel_upload.log');
            write_log("FILES count: " . count($_FILES), 'excel_upload.log');
            
            // Verificar se é multipart/form-data
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'multipart/form-data') === false) {
                write_log("AVISO: Content-Type não é multipart/form-data. Content-Type: {$contentType}", 'excel_upload.log');
            }
            
            // IMPORTANTE: php://input está vazio para multipart/form-data (comportamento normal do PHP)
            // O PHP processa multipart/form-data automaticamente e popula $_POST e $_FILES
            // Se $_FILES está vazio, pode ser que o arquivo não esteja sendo enviado corretamente
            
            // Verificar todas as variáveis globais
            write_log("_GET: " . json_encode($_GET), 'excel_upload.log');
            write_log("_REQUEST: " . json_encode($_REQUEST), 'excel_upload.log');
            
            // Verificar configurações de upload
            $uploadTmpDir = ini_get('upload_tmp_dir');
            $fileUploadsEnabled = ini_get('file_uploads');
            write_log("upload_tmp_dir: " . ($uploadTmpDir ?: 'padrão do sistema'), 'excel_upload.log');
            write_log("file_uploads: " . ($fileUploadsEnabled ? 'ON' : 'OFF'), 'excel_upload.log');
            
            // PROBLEMA CRÍTICO: file_uploads está desabilitado!
            if (!$fileUploadsEnabled) {
                write_log("ERRO CRÍTICO: file_uploads está DESABILITADO no PHP!", 'excel_upload.log');
                write_log("Isso impede o processamento de uploads de arquivos.", 'excel_upload.log');
                write_log("Solução: Ativar file_uploads no php.ini ou .htaccess", 'excel_upload.log');
                throw new \Exception('Upload de arquivos está desabilitado no servidor. Entre em contato com o administrador do sistema para ativar a diretiva "file_uploads" no PHP.');
            }
            
            // Verificar se há algum problema com o processamento do multipart
            // Se o Content-Type está correto mas $_FILES está vazio, pode ser problema no servidor
            if (empty($_FILES) && strpos($contentType, 'multipart/form-data') !== false) {
                write_log("PROBLEMA DETECTADO: Content-Type é multipart/form-data mas \$_FILES está vazio!", 'excel_upload.log');
                write_log("Isso pode indicar:", 'excel_upload.log');
                write_log("1. O arquivo não está sendo enviado no FormData", 'excel_upload.log');
                write_log("2. Problema com o servidor web processando multipart", 'excel_upload.log');
                write_log("3. O nome do campo não corresponde ao esperado", 'excel_upload.log');
                
                // Verificar se há dados no POST que podem indicar o problema
                if (!empty($_POST)) {
                    write_log("POST tem dados, mas FILES está vazio - arquivo pode não estar sendo enviado", 'excel_upload.log');
                }
            }
            
            error_log('=== UPLOAD DE RELATÓRIO ===');
            error_log('POST recebido: ' . json_encode($_POST));
            error_log('FILES recebido: ' . json_encode($_FILES));
            error_log('Tamanho do POST: ' . (isset($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : 'N/A'));
            
            // Verificar configurações do PHP
            $uploadMaxSize = ini_get('upload_max_filesize');
            $postMaxSize = ini_get('post_max_size');
            $maxFileUploads = ini_get('max_file_uploads');
            
            write_log("Configurações PHP - upload_max_filesize: {$uploadMaxSize} | post_max_size: {$postMaxSize} | max_file_uploads: {$maxFileUploads}", 'excel_upload.log');
            
            // Verificar se o POST foi recebido (pode ter sido truncado por post_max_size)
            if (empty($_POST) && empty($_FILES) && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                write_log("ERRO: POST vazio - arquivo muito grande. Tamanho máximo: {$postMaxSize}", 'excel_upload.log');
                throw new \Exception("Arquivo muito grande ou dados não recebidos. Tamanho máximo permitido pelo servidor: {$postMaxSize}. Tente um arquivo menor.");
            }
            
            // Verificar se arquivo foi enviado
            if (!isset($_FILES['excel_file'])) {
                write_log("ERRO: Campo excel_file não encontrado em \$_FILES", 'excel_upload.log');
                write_log("Chaves disponíveis em \$_FILES: " . implode(', ', array_keys($_FILES)), 'excel_upload.log');
                write_log("Conteúdo completo de \$_FILES: " . json_encode($_FILES), 'excel_upload.log');
                
                // Verificar se o POST foi truncado
                $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
                $postMaxSizeBytes = self::convertToBytes($postMaxSize);
                
                if ($contentLength > $postMaxSizeBytes) {
                    write_log("ERRO: Tamanho do conteúdo ({$contentLength} bytes) excede post_max_size ({$postMaxSizeBytes} bytes)", 'excel_upload.log');
                    throw new \Exception("Arquivo muito grande. Tamanho máximo permitido pelo servidor: {$postMaxSize}. Tamanho do arquivo enviado excede o limite.");
                }
                
                throw new \Exception('Nenhum arquivo foi selecionado. Por favor, escolha um arquivo Excel.');
            }
            
            $file = $_FILES['excel_file'];
            
            // Verificar erros específicos do upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'Arquivo excede o tamanho máximo permitido pelo PHP (upload_max_filesize).',
                    UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o tamanho máximo do formulário (MAX_FILE_SIZE).',
                    UPLOAD_ERR_PARTIAL => 'Upload parcial do arquivo. Tente novamente.',
                    UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário não encontrado no servidor.',
                    UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever arquivo no disco. Verifique permissões.',
                    UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão do PHP.'
                ];
                
                $errorMsg = $uploadErrors[$file['error']] ?? 'Erro desconhecido no upload (código: ' . $file['error'] . ')';
                write_log("ERRO no upload: {$errorMsg} (código: {$file['error']})", 'excel_upload.log');
                error_log('Erro no upload: ' . $errorMsg);
                throw new \Exception($errorMsg);
            }
            
            $fileSizeMB = round($file['size'] / 1024 / 1024, 2);
            write_log("Arquivo recebido: Nome={$file['name']} | Tamanho={$file['size']} bytes ({$fileSizeMB}MB) | Tipo={$file['type']}", 'excel_upload.log');
            error_log('Arquivo recebido: ' . $file['name'] . ' (' . $file['size'] . ' bytes)');
            
            // Validar tipo de arquivo
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['xls', 'xlsx', 'csv'];
            
            write_log("Extensão do arquivo: {$fileExtension}", 'excel_upload.log');
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                write_log("ERRO: Tipo de arquivo não permitido: {$fileExtension}", 'excel_upload.log');
                throw new \Exception('Tipo de arquivo não permitido. Apenas arquivos Excel (.xls, .xlsx) ou CSV são aceitos.');
            }
            
            // Validar tamanho do arquivo (máximo 10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                write_log("ERRO: Arquivo muito grande: {$fileSizeMB}MB (máximo: 10MB)", 'excel_upload.log');
                throw new \Exception('Arquivo muito grande. Tamanho máximo permitido: 10MB. Tamanho do arquivo: ' . $fileSizeMB . 'MB.');
            }
            
            // Verificar se o arquivo temporário existe
            if (!file_exists($file['tmp_name'])) {
                write_log("ERRO: Arquivo temporário não existe: {$file['tmp_name']}", 'excel_upload.log');
                error_log('ERRO: Arquivo temporário não existe: ' . $file['tmp_name']);
                throw new \Exception('Arquivo temporário não encontrado. O upload pode ter falhado.');
            }
            
            // Criar diretório de upload se não existir
            $uploadDir = __DIR__ . '/../../storage/uploads/billing/';
            write_log("Diretório de upload: {$uploadDir}", 'excel_upload.log');
            error_log('Diretório de upload: ' . $uploadDir);
            
            if (!is_dir($uploadDir)) {
                $created = mkdir($uploadDir, 0755, true);
                write_log("Diretório criado? " . ($created ? 'SIM' : 'NÃO'), 'excel_upload.log');
                error_log('Diretório criado? ' . ($created ? 'SIM' : 'NÃO'));
                if (!$created) {
                    write_log("ERRO: Falha ao criar diretório de upload", 'excel_upload.log');
                    throw new \Exception('Erro ao criar diretório de upload. Verifique as permissões do servidor.');
                }
            }
            
            // Verificar se o diretório é gravável
            if (!is_writable($uploadDir)) {
                write_log("ERRO: Diretório não é gravável: {$uploadDir}", 'excel_upload.log');
                error_log('ERRO: Diretório não é gravável: ' . $uploadDir);
                throw new \Exception('Diretório de upload não tem permissão de escrita. Verifique as permissões do servidor.');
            }
            
            // Gerar nome único para o arquivo
            $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            write_log("Nome do arquivo gerado: {$fileName}", 'excel_upload.log');
            write_log("Caminho completo: {$filePath}", 'excel_upload.log');
            
            // Mover arquivo para diretório de upload
            write_log("Tentando mover arquivo de: {$file['tmp_name']} para: {$filePath}", 'excel_upload.log');
            error_log('Tentando mover arquivo de: ' . $file['tmp_name'] . ' para: ' . $filePath);
            
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                $lastError = error_get_last();
                $errorMsg = $lastError['message'] ?? 'Erro desconhecido';
                write_log("ERRO ao mover arquivo: {$errorMsg}", 'excel_upload.log');
                error_log('ERRO ao mover arquivo: ' . $errorMsg);
                throw new \Exception('Erro ao salvar arquivo no servidor. Verifique as permissões do diretório de upload.');
            }
            
            $savedSize = filesize($filePath);
            write_log("SUCESSO: Arquivo salvo: {$filePath}", 'excel_upload.log');
            write_log("Arquivo existe? " . (file_exists($filePath) ? 'SIM' : 'NÃO'), 'excel_upload.log');
            write_log("Tamanho do arquivo salvo: {$savedSize} bytes", 'excel_upload.log');
            
            error_log('Arquivo salvo com sucesso: ' . $filePath);
            error_log('Arquivo existe? ' . (file_exists($filePath) ? 'SIM' : 'NÃO'));
            error_log('Tamanho do arquivo salvo: ' . $savedSize);
            
            // Obter dados do formulário
            $reportTitle = $_POST['report_title'] ?? 'Relatório de Faturamento';
            $companyCode = $_POST['company_code'] ?? null;
            
            write_log("Título do relatório: {$reportTitle}", 'excel_upload.log');
            write_log("Código da empresa: " . ($companyCode ?? 'N/A'), 'excel_upload.log');
            
            // Processar arquivo Excel
            write_log("Iniciando processamento do arquivo Excel...", 'excel_upload.log');
            $result = $this->billingReport->processExcelFile($filePath, $userId, $reportTitle, $companyCode);
            
            if (!$result['success']) {
                write_log("ERRO no processamento: {$result['error']}", 'excel_upload.log');
                // Remover arquivo se houve erro no processamento
                if (file_exists($filePath)) {
                    unlink($filePath);
                    write_log("Arquivo removido após erro no processamento", 'excel_upload.log');
                }
                throw new \Exception($result['error']);
            }
            
            write_log("SUCESSO: Processamento concluído. Registros processados: " . ($result['processed_rows'] ?? 0), 'excel_upload.log');
            write_log("ID do relatório criado: " . ($result['report_id'] ?? 'N/A'), 'excel_upload.log');
            
            // Remover arquivo temporário após processamento
            if (file_exists($filePath)) {
                unlink($filePath);
                write_log("Arquivo temporário removido após processamento", 'excel_upload.log');
            }
            
            write_log("=== FIM UPLOAD DE RELATÓRIO - SUCESSO ===", 'excel_upload.log');
            
            // Redirecionar para visualização do relatório
            $_SESSION['success_message'] = "Relatório processado com sucesso! {$result['processed_rows']} registros foram importados.";
            redirect(url('billing/' . $result['report_id']));
            
        } catch (\Exception $e) {
            write_log("ERRO GERAL: " . $e->getMessage(), 'excel_upload.log');
            write_log("Stack trace: " . $e->getTraceAsString(), 'excel_upload.log');
            write_log("=== FIM UPLOAD DE RELATÓRIO - ERRO ===", 'excel_upload.log');
            $_SESSION['error_message'] = 'Erro ao processar arquivo: ' . $e->getMessage();
            redirect(url('billing/create'));
        }
    }
    
    
    /**
     * Converte tamanho de string (ex: "10M") para bytes
     */
    private static function convertToBytes($size)
    {
        if (empty($size)) {
            return 0;
        }
        
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int)$size;
        
        switch ($last) {
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }
        
        return $size;
    }
    
    /**
     * Exibe detalhes de um relatório
     */
    public function show($id)
    {
        $report = $this->billingReport->findById($id);
        
        if (!$report) {
            $_SESSION['error_message'] = 'Relatório não encontrado.';
            redirect(url('billing'));
        }
        
        $billingData = $this->billingReport->getBillingData($id);
        $unlinkedData = $this->billingReport->getUnlinkedData($id);
        
        $data = [
            'title' => 'Detalhes do Relatório',
            'report' => $report,
            'billing_data' => $billingData,
            'unlinked_data' => $unlinkedData
        ];
        
        $this->render('billing/show', $data);
    }
    
    /**
     * Busca estabelecimentos para vinculação
     */
    public function searchEstablishments()
    {
        header('Content-Type: application/json');
        
        try {
            $query = $_GET['q'] ?? '';
            
            if (empty($query)) {
                echo json_encode([]);
                exit;
            }
            
            $establishments = $this->billingReport->searchEstablishments($query);
            
            echo json_encode($establishments);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Vincula dados de faturamento a um estabelecimento
     */
    public function linkEstablishment()
    {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['billing_data_id']) || !isset($input['establishment_id'])) {
                throw new \Exception('Parâmetros obrigatórios não fornecidos.');
            }
            
            $result = $this->billingReport->linkToEstablishment(
                $input['billing_data_id'],
                $input['establishment_id']
            );
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Vinculação realizada com sucesso.']);
            } else {
                throw new \Exception('Erro ao realizar vinculação.');
            }
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Remove vinculação de dados de faturamento
     */
    public function unlinkEstablishment()
    {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['billing_data_id'])) {
                throw new \Exception('ID dos dados de faturamento não fornecido.');
            }
            
            $result = $this->billingReport->linkToEstablishment(
                $input['billing_data_id'],
                null
            );
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Vinculação removida com sucesso.']);
            } else {
                throw new \Exception('Erro ao remover vinculação.');
            }
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Remove um relatório
     */
    public function destroy($id)
    {
        try {
            // Verificar se é admin
            Auth::requireAdmin();
            
            $report = $this->billingReport->findById($id);
            
            if (!$report) {
                throw new \Exception('Relatório não encontrado.');
            }
            
            error_log("Excluindo relatório ID: " . $id);
            
            $this->billingReport->deleteReport($id);
            
            error_log("Relatório ID " . $id . " excluído com sucesso");
            
            $_SESSION['success_message'] = 'Relatório removido com sucesso.';
            
        } catch (\Exception $e) {
            error_log("ERRO ao excluir relatório ID " . $id . ": " . $e->getMessage());
            $_SESSION['error_message'] = 'Erro ao remover relatório: ' . $e->getMessage();
        }
        
        redirect(url('billing'));
    }
    
    /**
     * Exporta dados de faturamento para Excel
     */
    public function export($id)
    {
        try {
            $report = $this->billingReport->findById($id);
            
            if (!$report) {
                throw new \Exception('Relatório não encontrado.');
            }
            
            $billingData = $this->billingReport->getBillingData($id);
            
            // Configurar cabeçalho para download Excel
            $filename = 'relatorio_faturamento_' . $id . '_' . date('Y-m-d') . '.xls';
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Criar arquivo Excel usando formato XML (SpreadsheetML)
            $this->generateExcelFile($report, $billingData);
            exit;
            
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Erro ao exportar relatório: ' . $e->getMessage();
            redirect(url('billing/' . $id));
        }
    }
    
    /**
     * Gera arquivo Excel no formato XML (SpreadsheetML) - compatível com Excel
     */
    private function generateExcelFile($report, $billingData)
    {
        // Usar formato SpreadsheetML que é compatível com Excel
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        
        // Estilos
        $xml .= '<Styles>' . "\n";
        $xml .= '<Style ss:ID="Header">' . "\n";
        $xml .= '<Font ss:Bold="1"/>' . "\n";
        $xml .= '<Interior ss:Color="#D3D3D3" ss:Pattern="Solid"/>' . "\n";
        $xml .= '<Borders>' . "\n";
        $xml .= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        $xml .= '</Borders>' . "\n";
        $xml .= '</Style>' . "\n";
        $xml .= '<Style ss:ID="Title">' . "\n";
        $xml .= '<Font ss:Size="14" ss:Bold="1"/>' . "\n";
        $xml .= '</Style>' . "\n";
        $xml .= '<Style ss:ID="Currency">' . "\n";
        $xml .= '<NumberFormat ss:Format="#,##0.00"/>' . "\n";
        $xml .= '</Style>' . "\n";
        $xml .= '</Styles>' . "\n";
        
        // Planilha
        $xml .= '<Worksheet ss:Name="Relatório">' . "\n";
        $xml .= '<Table>' . "\n";
        
        // Linha 1: Título
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell ss:StyleID="Title"><Data ss:Type="String">Relatório de Faturamento</Data></Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        
        // Linha 2: Título do relatório
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell><Data ss:Type="String">' . $this->escapeXml($report['title'] ?? '') . '</Data></Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        
        // Linha 3: Data de geração
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell><Data ss:Type="String">Gerado em: ' . date('d/m/Y H:i:s') . '</Data></Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        
        // Linha vazia
        $xml .= '<Row></Row>' . "\n";
        
        // Cabeçalho da tabela
        $xml .= '<Row ss:StyleID="Header">' . "\n";
        $xml .= '<Cell><Data ss:Type="String">Nome</Data></Cell>' . "\n";
        $xml .= '<Cell><Data ss:Type="String">CNPJ/CPF</Data></Cell>' . "\n";
        $xml .= '<Cell><Data ss:Type="String">REPRESENTANTE</Data></Cell>' . "\n";
        $xml .= '<Cell><Data ss:Type="String">TPV Total</Data></Cell>' . "\n";
        $xml .= '<Cell><Data ss:Type="String">Markup</Data></Cell>' . "\n";
        $xml .= '<Cell><Data ss:Type="String">Estabelecimento</Data></Cell>' . "\n";
        $xml .= '<Cell><Data ss:Type="String">Status</Data></Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        
        // Dados
        foreach ($billingData as $data) {
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell><Data ss:Type="String">' . $this->escapeXml($data['nome'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell><Data ss:Type="String">' . $this->escapeXml($data['cnpj_cpf'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell><Data ss:Type="String">' . $this->escapeXml($data['representante'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . number_format($data['tpv_total'] ?? 0, 2, '.', '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . number_format($data['markup'] ?? 0, 2, '.', '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell><Data ss:Type="String">' . $this->escapeXml(($data['nome_fantasia'] ?? '') ?: ($data['razao_social'] ?? '')) . '</Data></Cell>' . "\n";
            $xml .= '<Cell><Data ss:Type="String">' . ($data['establishment_id'] ? 'Vinculado' : 'Não vinculado') . '</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
        }
        
        // Linha vazia
        $xml .= '<Row></Row>' . "\n";
        
        // Totais
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell><Data ss:Type="String">TOTAL:</Data></Cell>' . "\n";
        $xml .= '<Cell></Cell>' . "\n";
        $xml .= '<Cell></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . number_format($report['total_tpv'] ?? 0, 2, '.', '') . '</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . number_format($report['total_markup'] ?? 0, 2, '.', '') . '</Data></Cell>' . "\n";
        $xml .= '<Cell></Cell>' . "\n";
        $xml .= '<Cell></Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        
        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        $xml .= '</Workbook>' . "\n";
        
        echo $xml;
    }
    
    /**
     * Escapa caracteres especiais para XML
     */
    private function escapeXml($string)
    {
        return htmlspecialchars($string ?? '', ENT_XML1, 'UTF-8');
    }
    
    /**
     * Renderiza uma view
     */
    private function render($view, $data = [])
    {
        extract($data);
        
        $layoutFile = __DIR__ . '/../Views/layouts/app.php';
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View não encontrada: {$view}");
        }
        
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        include $layoutFile;
    }
}
