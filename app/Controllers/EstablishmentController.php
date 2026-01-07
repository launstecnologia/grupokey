<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Establishment;
use App\Models\Representative;
use App\Models\Product;
use App\Models\EstablishmentProduct;
use App\Models\Segment;
use App\Models\SistPaySettings;
use App\Core\FileUpload;
use App\Services\SistPayApi;

class EstablishmentController
{
    private $establishmentModel;
    private $representativeModel;
    private $productModel;
    private $establishmentProductModel;
    private $segmentModel;
    private $fileUpload;
    
    public function __construct()
    {
        $this->establishmentModel = new Establishment();
        $this->representativeModel = new Representative();
        $this->productModel = new Product();
        $this->establishmentProductModel = new EstablishmentProduct();
        $this->segmentModel = new Segment();
        $this->fileUpload = new FileUpload();
    }
    
    public function index()
    {
        Auth::requireAuth();
        
        $filters = $this->getFilters();
        
        // Obter total de registros para paginação
        $totalRecords = $this->establishmentModel->getCount($filters);
        $totalPages = ceil($totalRecords / $filters['per_page']);
        
        $establishments = $this->establishmentModel->getAll($filters);
        $representatives = $this->representativeModel->getAll(['status' => 'ACTIVE']);
        
        $stats = $this->establishmentModel->getStats($filters);
        
        $data = [
            'title' => 'Estabelecimentos',
            'currentPage' => 'estabelecimentos',
            'establishments' => $establishments,
            'representatives' => $representatives,
            'filters' => $filters,
            'stats' => $stats ?? [
                'total' => 0,
                'aprovados' => 0,
                'pendentes' => 0,
                'reprovados' => 0,
                'desabilitados' => 0,
                'cadastros_ultimo_mes' => 0
            ],
            'pagination' => [
                'current_page' => $filters['page'],
                'total_pages' => $totalPages,
                'total_records' => $totalRecords,
                'per_page' => $filters['per_page']
            ]
        ];
        
        view('establishments/index', $data);
    }
    
    public function create()
    {
        Auth::requireAuth();
        
        $representatives = [];
        if (Auth::isAdmin()) {
            $representatives = $this->representativeModel->getAll(['status' => 'ACTIVE']);
        }
        
        // Buscar planos disponíveis da API SistPay
        $plans = [];
        try {
            $api = new SistPayApi();
            // Tentar buscar planos mesmo em sandbox (só precisa ter token)
            $plans = $api->getPlans();
            write_log('Planos carregados com sucesso no create: ' . count($plans), 'app.log');
        } catch (\Exception $e) {
            // Se não conseguir buscar, continuar sem planos
            write_log('Erro ao buscar planos no create: ' . $e->getMessage(), 'app.log');
            write_log('Stack trace: ' . $e->getTraceAsString(), 'app.log');
        }
        
        $data = [
            'title' => 'Novo Estabelecimento',
            'currentPage' => 'estabelecimentos',
            'representatives' => $representatives,
            'products' => $this->productModel->getAll(),
            'segments' => $this->segmentModel->getActive(),
            'sistpay_plans' => $plans
        ];
        
        view('establishments/create', $data);
    }
    
    public function store()
    {
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('estabelecimentos/create'));
        }
        
        $data = $this->validateAndSanitizeInput();
        
        if (isset($_SESSION['validation_errors'])) {
            redirect(url('estabelecimentos/create'));
        }
        
        try {
            // Debug: Log dos dados que estão sendo enviados
            error_log('Dados para criação: ' . json_encode($data));
            
            if (empty($data)) {
                throw new \Exception('Dados inválidos para criação do estabelecimento');
            }
            
            $establishmentId = $this->establishmentModel->create($data);
            
            if (!$establishmentId) {
                throw new \Exception('Falha ao criar estabelecimento. Verifique os dados e tente novamente.');
            }
            
            // Upload de documentos se houver
            $this->handleDocumentUpload($establishmentId);
            
            // Limpar dados antigos da sessão após sucesso
            if (isset($_SESSION['old_input'])) {
                unset($_SESSION['old_input']);
            }
            
            $_SESSION['success'] = 'Estabelecimento salvo com sucesso!';
            
            if (Auth::isAdmin()) {
                redirect(url('estabelecimentos'));
            } else {
                redirect(url('estabelecimentos/' . $establishmentId));
            }
            
        } catch (\PDOException $e) {
            // Erro específico do banco de dados
            $errorMessage = 'Erro no banco de dados: ';
            
            switch ($e->getCode()) {
                case 23000: // Integrity constraint violation
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $errorMessage .= 'Dados duplicados encontrados. Verifique se o email ou CPF/CNPJ já não estão cadastrados.';
                    } elseif (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                        $errorMessage .= 'Erro de referência - dados relacionados não encontrados.';
                    } else {
                        $errorMessage .= 'Violação de integridade dos dados.';
                    }
                    break;
                case '42S22': // Column not found
                    $errorMessage .= 'Campo não encontrado na tabela.';
                    break;
                case '42S02': // Table doesn't exist
                    $errorMessage .= 'Tabela não encontrada no banco de dados.';
                    break;
                case '22001': // Data too long
                    $errorMessage .= 'Dados muito longos para alguns campos.';
                    break;
                default:
                    $errorMessage .= $e->getMessage();
            }
            
            $_SESSION['error'] = $errorMessage;
            $_SESSION['debug_error'] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            redirect(url('estabelecimentos/create'));
            
        } catch (\Exception $e) {
            // Outros erros
            error_log('ERRO ao cadastrar estabelecimento: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            $_SESSION['error'] = 'Erro ao cadastrar estabelecimento: ' . $e->getMessage();
            $_SESSION['debug_error'] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            redirect(url('estabelecimentos/create'));
        }
    }
    
    public function show($id)
    {
        Auth::requireAuth();
        
        $establishment = $this->establishmentModel->findById($id);
        
        if (!$establishment) {
            $_SESSION['error'] = 'Estabelecimento não encontrado';
            redirect(url('estabelecimentos'));
        }
        
        // Verificar permissões
        if (Auth::isRepresentative() && $establishment['created_by_representative_id'] != Auth::representative()['id']) {
            $_SESSION['error'] = 'Você não tem permissão para visualizar este estabelecimento';
            redirect(url('dashboard'));
        }
        
        $documents = $this->establishmentModel->getDocumentsByType($id);
        $approvalHistory = $this->establishmentModel->getApprovalHistory($id);
        
        // Verificar se API SistPay está configurada (ativa ou em sandbox)
        $sistPaySettingsModel = new SistPaySettings();
        $sistPaySettings = $sistPaySettingsModel->getActiveSettings();
        // Botão aparece se tiver token configurado (permite testar em sandbox mesmo sem estar ativa)
        $sistPayApiActive = !empty($sistPaySettings) && !empty($sistPaySettings['token']);
        
        $data = [
            'title' => 'Detalhes do Estabelecimento',
            'currentPage' => 'estabelecimentos',
            'establishment' => $establishment,
            'documents' => $documents,
            'approvalHistory' => $approvalHistory,
            'sistPayApiActive' => $sistPayApiActive,
            'sistPaySettings' => $sistPaySettings ?? []
        ];
        
        view('establishments/show', $data);
    }
    
    public function edit($id)
    {
        Auth::requireAuth();
        
        $establishment = $this->establishmentModel->findById($id);
        
        if (!$establishment) {
            $_SESSION['error'] = 'Estabelecimento não encontrado';
            redirect(url('estabelecimentos'));
        }
        
        // Verificar permissões
        if (Auth::isRepresentative() && $establishment['created_by_representative_id'] != Auth::representative()['id']) {
            $_SESSION['error'] = 'Você não tem permissão para editar este estabelecimento';
            redirect(url('dashboard'));
        }
        
        // Não permitir edição de estabelecimentos aprovados (exceto admin)
        if ($establishment['status'] === 'APPROVED' && !Auth::isAdmin()) {
            $_SESSION['error'] = 'Não é possível editar estabelecimentos aprovados';
            redirect(url('estabelecimentos/' . $id));
        }
        
        $representatives = [];
        if (Auth::isAdmin()) {
            $representatives = $this->representativeModel->getAll(['status' => 'ACTIVE']);
        }
        
        // Debug: Log dos dados do estabelecimento
        write_log('=== DADOS DO ESTABELECIMENTO ===', 'app.log');
        write_log('Estabelecimento: ' . json_encode($establishment), 'app.log');
        write_log('Produtos do estabelecimento: ' . json_encode($establishment['products'] ?? []), 'app.log');
        
        // Buscar planos disponíveis da API SistPay
        $plans = [];
        try {
            $api = new SistPayApi();
            // Tentar buscar planos mesmo em sandbox (só precisa ter token)
            $plans = $api->getPlans();
            write_log('Planos carregados com sucesso no edit: ' . count($plans), 'app.log');
        } catch (\Exception $e) {
            // Se não conseguir buscar, continuar sem planos
            write_log('Erro ao buscar planos no edit: ' . $e->getMessage(), 'app.log');
            write_log('Stack trace: ' . $e->getTraceAsString(), 'app.log');
        }
        
        $data = [
            'title' => 'Editar Estabelecimento',
            'currentPage' => 'estabelecimentos',
            'establishment' => $establishment,
            'representatives' => $representatives,
            'products' => $this->productModel->getAll(),
            'segments' => $this->segmentModel->getActive(),
            'sistpay_plans' => $plans
        ];
        
        // Debug: Log dos produtos disponíveis
        write_log('Produtos disponíveis: ' . json_encode($data['products']), 'app.log');
        
        view('establishments/edit', $data);
    }
    
    public function update($id)
    {
        Auth::requireAuth();
        
        // Debug: Log do início do método
        error_log('=== INÍCIO UPDATE ESTABELECIMENTO ===');
        error_log('ID: ' . $id);
        error_log('POST data: ' . json_encode($_POST));
        
        $establishment = $this->establishmentModel->findById($id);
        
        if (!$establishment) {
            error_log('ERRO: Estabelecimento não encontrado');
            $_SESSION['error'] = 'Estabelecimento não encontrado';
            redirect(url('estabelecimentos'));
        }
        
        // Verificar permissões
        if (Auth::isRepresentative() && $establishment['created_by_representative_id'] != Auth::representative()['id']) {
            error_log('ERRO: Sem permissão para editar');
            $_SESSION['error'] = 'Você não tem permissão para editar este estabelecimento';
            redirect(url('dashboard'));
        }
        
        $data = $this->validateAndSanitizeInput($id);
        
        // Debug: Log dos dados validados
        error_log('Dados validados: ' . json_encode($data));
        
        if (isset($_SESSION['validation_errors'])) {
            error_log('ERRO: Erros de validação encontrados: ' . json_encode($_SESSION['validation_errors']));
            redirect(url('estabelecimentos/' . $id . '/edit'));
        }
        
        try {
            // Debug: Log dos dados que estão sendo enviados
            error_log('Dados para atualização: ' . json_encode($data));
            
            // Teste simples primeiro - apenas atualizar dados básicos
            $result = $this->establishmentModel->update($id, $data);
            
            if (!$result) {
                error_log('ERRO: Model retornou false');
                throw new \Exception('Falha ao atualizar estabelecimento - operação não executada');
            }
            
            error_log('SUCESSO: Model retornou true');
            
            // Upload de novos documentos se houver
            $this->handleDocumentUpload($id);
            
            error_log('SUCESSO: Estabelecimento atualizado com sucesso');
            $_SESSION['success'] = 'Estabelecimento editado com sucesso!';
            redirect(url('estabelecimentos/' . $id));
            
        } catch (\PDOException $e) {
            // Erro específico do banco de dados
            error_log('ERRO PDO: ' . $e->getMessage());
            $errorMessage = 'Erro no banco de dados: ';
            
            switch ($e->getCode()) {
                case 23000: // Integrity constraint violation
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $errorMessage .= 'Dados duplicados encontrados. Verifique se o email ou CPF/CNPJ já não estão cadastrados.';
                    } elseif (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                        $errorMessage .= 'Erro de referência - dados relacionados não encontrados.';
                    } else {
                        $errorMessage .= 'Violação de integridade dos dados.';
                    }
                    break;
                case '42S22': // Column not found
                    $errorMessage .= 'Campo não encontrado na tabela.';
                    break;
                case '42S02': // Table doesn't exist
                    $errorMessage .= 'Tabela não encontrada no banco de dados.';
                    break;
                case '22001': // Data too long
                    $errorMessage .= 'Dados muito longos para alguns campos.';
                    break;
                default:
                    $errorMessage .= $e->getMessage();
            }
            
            $_SESSION['error'] = $errorMessage;
            $_SESSION['debug_error'] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            redirect(url('estabelecimentos/' . $id . '/edit'));
            
        } catch (\Exception $e) {
            // Outros erros
            error_log('ERRO GERAL: ' . $e->getMessage());
            $_SESSION['error'] = 'Erro ao atualizar estabelecimento: ' . $e->getMessage();
            $_SESSION['debug_error'] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            redirect(url('estabelecimentos/' . $id . '/edit'));
        }
    }
    
    public function destroy($id)
    {
        Auth::requireAdmin();
        
        $establishment = $this->establishmentModel->findById($id);
        
        if (!$establishment) {
            $_SESSION['error'] = 'Estabelecimento não encontrado';
            redirect(url('estabelecimentos'));
        }
        
        try {
            $this->establishmentModel->delete($id);
            $_SESSION['success'] = 'Estabelecimento excluído com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir estabelecimento: ' . $e->getMessage();
        }
        
        redirect(url('estabelecimentos'));
    }
    
    public function approve($id)
    {
        Auth::requireAdmin();
        
        $establishment = $this->establishmentModel->findById($id);
        
        if (!$establishment) {
            $_SESSION['error'] = 'Estabelecimento não encontrado';
            redirect(url('estabelecimentos'));
        }
        
        if ($establishment['status'] === 'APPROVED') {
            $_SESSION['warning'] = 'Estabelecimento já está aprovado';
            redirect(url('estabelecimentos/' . $id));
        }
        
        $reason = sanitize_input($_POST['reason'] ?? 'Aprovado pelo administrador');
        
        try {
            $this->establishmentModel->approve($id, $reason);
            $_SESSION['success'] = 'Estabelecimento aprovado com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao aprovar estabelecimento: ' . $e->getMessage();
        }
        
        redirect(url('estabelecimentos/' . $id));
    }
    
    public function reprove($id)
    {
        Auth::requireAdmin();
        
        $establishment = $this->establishmentModel->findById($id);
        
        if (!$establishment) {
            $_SESSION['error'] = 'Estabelecimento não encontrado';
            redirect(url('estabelecimentos'));
        }
        
        $reason = sanitize_input($_POST['reason'] ?? '');
        $observation = sanitize_input($_POST['observation'] ?? null);
        
        if (empty(trim($reason))) {
            $_SESSION['error'] = 'Motivo da reprovação é obrigatório';
            redirect(url('estabelecimentos/' . $id));
        }
        
        try {
            $this->establishmentModel->reprove($id, $reason, $observation);
            $_SESSION['success'] = 'Estabelecimento reprovado com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao reprovar estabelecimento: ' . $e->getMessage();
        }
        
        redirect(url('estabelecimentos/' . $id));
    }
    
    public function downloadDocument($id, $documentId)
    {
        Auth::requireAuth();
        
        $establishment = $this->establishmentModel->findById($id);
        
        if (!$establishment) {
            $_SESSION['error'] = 'Estabelecimento não encontrado';
            redirect(url('estabelecimentos'));
        }
        
        // Verificar permissões
        if (Auth::isRepresentative() && $establishment['created_by_representative_id'] != Auth::representative()['id']) {
            $_SESSION['error'] = 'Você não tem permissão para acessar este documento';
            redirect(url('estabelecimentos'));
        }
        
        // Buscar documento específico
        $document = $this->establishmentModel->getDocumentById($documentId, $id);
        
        if (!$document || !file_exists($document['file_path'])) {
            $_SESSION['error'] = 'Documento não encontrado';
            redirect(url('estabelecimentos/' . $id));
        }
        
        // Configurar headers para download
        header('Content-Type: ' . ($document['mime_type'] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . basename($document['original_name']) . '"');
        header('Content-Length: ' . filesize($document['file_path']));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Limpar output buffer e enviar arquivo
        while (ob_get_level()) {
            ob_end_clean();
        }
        readfile($document['file_path']);
        exit;
    }
    
    private function getFilters()
    {
        $filters = [];
        
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $filters['status'] = sanitize_input($_GET['status']);
        }
        
        if (isset($_GET['produto']) && !empty($_GET['produto'])) {
            $filters['produto'] = sanitize_input($_GET['produto']);
        }
        
        if (isset($_GET['cidade']) && !empty($_GET['cidade'])) {
            $filters['cidade'] = sanitize_input($_GET['cidade']);
        }
        
        if (isset($_GET['representative_id']) && !empty($_GET['representative_id'])) {
            $filters['representative_id'] = (int)$_GET['representative_id'];
        }
        
        if (isset($_GET['cpf']) && !empty($_GET['cpf'])) {
            $filters['cpf'] = preg_replace('/[^0-9]/', '', sanitize_input($_GET['cpf']));
        }
        
        if (isset($_GET['cnpj']) && !empty($_GET['cnpj'])) {
            $filters['cnpj'] = preg_replace('/[^0-9]/', '', sanitize_input($_GET['cnpj']));
        }
        
        if (isset($_GET['razao_social']) && !empty($_GET['razao_social'])) {
            $filters['razao_social'] = sanitize_input($_GET['razao_social']);
        }
        
        if (isset($_GET['nome']) && !empty($_GET['nome'])) {
            $filters['nome'] = sanitize_input($_GET['nome']);
        }
        
        if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
            $filters['date_from'] = sanitize_input($_GET['date_from']);
        }
        
        if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
            $filters['date_to'] = sanitize_input($_GET['date_to']);
        }
        
        // Paginação
        $filters['page'] = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $filters['per_page'] = 15;
        
        // Se for representante, filtrar apenas seus estabelecimentos
        if (Auth::isRepresentative()) {
            $filters['representative_id'] = Auth::representative()['id'];
        }
        
        return $filters;
    }
    
    private function validateAndSanitizeInput($id = null)
    {
        $errors = [];
        
        // Debug: Log dos dados recebidos
        error_log('=== VALIDAÇÃO DE DADOS ===');
        error_log('ID: ' . $id);
        error_log('POST recebido: ' . json_encode($_POST));
        
        // Dados obrigatórios
        $registrationType = sanitize_input($_POST['registration_type'] ?? '');
        $nomeCompleto = sanitize_input($_POST['nome_completo'] ?? '');
        $nomeFantasia = sanitize_input($_POST['nome_fantasia'] ?? '');
        $segmento = sanitize_input($_POST['segmento'] ?? '');
        $telefone = sanitize_input($_POST['telefone'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $produto = sanitize_input($_POST['produto'] ?? '');
        $cep = sanitize_input($_POST['cep'] ?? '');
        $logradouro = sanitize_input($_POST['logradouro'] ?? '');
        $numero = sanitize_input($_POST['numero'] ?? '');
        $bairro = sanitize_input($_POST['bairro'] ?? '');
        $cidade = sanitize_input($_POST['cidade'] ?? '');
        $uf = sanitize_input($_POST['uf'] ?? '');
        
        // Validações
        if (empty($registrationType) || !in_array($registrationType, ['PF', 'PJ'])) {
            $errors[] = 'Tipo de registro é obrigatório';
        }
        
        if (empty($nomeCompleto)) {
            $errors[] = 'Nome completo é obrigatório';
        }
        
        if (empty($nomeFantasia)) {
            $errors[] = 'Nome fantasia é obrigatório';
        }
        
        if (empty($segmento)) {
            $errors[] = 'Segmento é obrigatório';
        }
        
        if (empty($telefone)) {
            $errors[] = 'Telefone é obrigatório';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email válido é obrigatório';
        }
        
        // Validar produtos (múltiplos ou único)
        $products = $_POST['products'] ?? [];
        error_log('Produtos recebidos: ' . json_encode($products));
        
        if (empty($products) || !is_array($products)) {
            $errors[] = 'Pelo menos um produto deve ser selecionado';
            error_log('ERRO: Nenhum produto selecionado');
        } else {
            // Verificar se pelo menos um produto foi selecionado
            $hasSelectedProduct = false;
            foreach ($products as $product) {
                if (!empty($product)) {
                    $hasSelectedProduct = true;
                    break;
                }
            }
            if (!$hasSelectedProduct) {
                $errors[] = 'Pelo menos um produto deve ser selecionado';
                error_log('ERRO: Produtos vazios');
            } else {
                error_log('SUCESSO: Produtos validados');
            }
        }
        
        if (empty($cep)) {
            $errors[] = 'CEP é obrigatório';
        }
        
        if (empty($logradouro)) {
            $errors[] = 'Logradouro é obrigatório';
        }
        
        if (empty($numero)) {
            $errors[] = 'Número é obrigatório';
        }
        
        if (empty($bairro)) {
            $errors[] = 'Bairro é obrigatório';
        }
        
        if (empty($cidade)) {
            $errors[] = 'Cidade é obrigatória';
        }
        
        if (empty($uf) || strlen($uf) !== 2) {
            $errors[] = 'UF é obrigatória';
        }
        
        // Validações específicas por tipo de registro
        if ($registrationType === 'PF') {
            $cpf = sanitize_input($_POST['cpf'] ?? '');
            if (empty($cpf)) {
                $errors[] = 'CPF é obrigatório para pessoa física';
            } elseif (!$this->validateCPF($cpf)) {
                $errors[] = 'CPF inválido';
            }
        } else {
            $cnpj = sanitize_input($_POST['cnpj'] ?? '');
            $razaoSocial = sanitize_input($_POST['razao_social'] ?? '');
            if (empty($cnpj)) {
                $errors[] = 'CNPJ é obrigatório para pessoa jurídica';
            } elseif (!$this->validateCNPJ($cnpj)) {
                $errors[] = 'CNPJ inválido';
            }
            if (empty($razaoSocial)) {
                $errors[] = 'Razão social é obrigatória para pessoa jurídica';
            }
        }
        
        // Verificar se email já existe (exceto para o próprio registro)
        if ($id) {
            $existing = $this->establishmentModel->findByEmail($email);
            if ($existing && $existing['id'] != $id) {
                $errors[] = 'Email já está sendo usado por outro estabelecimento';
            }
        } else {
            if ($this->establishmentModel->findByEmail($email)) {
                $errors[] = 'Email já está sendo usado por outro estabelecimento';
            }
        }
        
        if (!empty($errors)) {
            error_log('ERROS DE VALIDAÇÃO: ' . json_encode($errors));
            $_SESSION['validation_errors'] = $errors;
            // Salvar dados do formulário na sessão para preservar após redirecionamento
            $_SESSION['old_input'] = $_POST;
            return [];
        }
        
        // Mapear IDs dos produtos para nomes do ENUM
        $produtoNome = null;
        if (!empty($products[0])) {
            $produtoNome = $this->mapProductIdToEnumValue($products[0]);
            error_log('Produto mapeado: ' . $products[0] . ' -> ' . $produtoNome);
            
            // Validar se o valor do ENUM é válido no banco
            // O ENUM do banco pode ter valores antigos, então vamos usar NULL se não for válido
            // Os produtos reais estão nas tabelas individuais agora
            $validEnumsNoBanco = ['PAGSEGURO_MP', 'FLAMEX', 'DIVERSOS', 'FGTS', 'UCREDIT', 'BRASILCARD', 'MEMBRO_KEY'];
            $validEnumsNovos = ['CDX_EVO', 'CDC', 'GOOGLE', 'MEMBRO_KEY', 'PAGBANK', 'OUTROS'];
            
            // Se não for válido no ENUM do banco, usar NULL (os produtos estão nas tabelas individuais)
            if (!in_array($produtoNome, $validEnumsNoBanco) && !in_array($produtoNome, $validEnumsNovos)) {
                error_log('AVISO: Valor do ENUM produto inválido: ' . $produtoNome . '. Usando NULL.');
                $produtoNome = null;
            } elseif (!in_array($produtoNome, $validEnumsNoBanco)) {
                // Se for um valor novo que não existe no ENUM do banco, usar NULL
                error_log('AVISO: Valor novo do ENUM não existe no banco: ' . $produtoNome . '. Usando NULL.');
                $produtoNome = null;
            }
        }
        
        // Preparar dados para inserção/atualização
        $data = [
            'registration_type' => $registrationType,
            'nome_completo' => $nomeCompleto,
            'nome_fantasia' => $nomeFantasia,
            'segmento' => $segmento,
            'telefone' => $telefone,
            'email' => $email,
            'produto' => $produtoNome, // Produto principal (nome do ENUM)
            'products' => $products, // Array de produtos (IDs)
            'cep' => $cep,
            'logradouro' => $logradouro,
            'numero' => $numero,
            'complemento' => sanitize_input($_POST['complemento'] ?? ''),
            'bairro' => $bairro,
            'cidade' => $cidade,
            'uf' => strtoupper($uf),
            'banco' => sanitize_input($_POST['banco'] ?? ''),
            'agencia' => sanitize_input($_POST['agencia'] ?? ''),
            'conta' => sanitize_input($_POST['conta'] ?? ''),
            'tipo_conta' => $this->sanitizeTipoConta($_POST['tipo_conta'] ?? ''),
            'chave_pix' => sanitize_input($_POST['chave_pix'] ?? ''),
            'observacoes' => sanitize_input($_POST['observacoes'] ?? '')
        ];
        
        if ($registrationType === 'PF') {
            $data['cpf'] = sanitize_input($_POST['cpf'] ?? '');
        } else {
            $data['cnpj'] = sanitize_input($_POST['cnpj'] ?? '');
            $data['razao_social'] = $razaoSocial;
        }
        
        // Dados específicos dos produtos
        $data['product_data'] = $this->getProductSpecificData($products);
        error_log('Dados específicos dos produtos: ' . json_encode($data['product_data']));
        
        // Definir quem criou/atualizou
        if (Auth::isAdmin()) {
            $data['created_by_user_id'] = Auth::user()['id'];
            if (isset($_POST['representative_id']) && !empty($_POST['representative_id'])) {
                $data['created_by_representative_id'] = (int)$_POST['representative_id'];
            }
        } else {
            $representative = Auth::representative();
            $data['created_by_representative_id'] = $representative ? $representative['id'] : null;
        }
        
        error_log('VALIDAÇÃO PASSOU - Dados preparados: ' . json_encode($data));
        return $data;
    }
    
    private function getProductSpecificData($products)
    {
        $data = [];
        error_log('=== COLETANDO DADOS ESPECÍFICOS DOS PRODUTOS ===');
        error_log('Produtos recebidos: ' . json_encode($products));
        error_log('POST completo: ' . json_encode($_POST));
        
        foreach ($products as $productType) {
            error_log('Processando produto: ' . $productType);
            switch ($productType) {
                case 'prod-pagseguro':
                case 'prod-subaquirente':
                case 'prod-pagbank':
                    // CDX/EVO e PagBank têm os mesmos campos
                    // Usar prod-pagseguro como chave para campos do formulário (compatibilidade)
                    $formKey = ($productType === 'prod-subaquirente') ? 'prod-pagseguro' : $productType;
                    $data[$productType] = [
                        'previsao_faturamento' => parse_currency($_POST['previsao_faturamento_' . $formKey] ?? '0'),
                        'tabela' => sanitize_input($_POST['tabela_' . $formKey] ?? ''),
                        'modelo_maquininha' => sanitize_input($_POST['modelo_maquininha_' . $formKey] ?? ''),
                        'meio_pagamento' => sanitize_input($_POST['meio_pagamento_' . $formKey] ?? ''),
                        'valor' => parse_currency($_POST['valor_' . $formKey] ?? '0'),
                        'plan' => !empty($_POST['plan_' . $formKey]) ? (int)$_POST['plan_' . $formKey] : null
                    ];
                    break;
                    
                case 'prod-brasil-card':
                    $data[$productType] = [
                        'taxa' => sanitize_input($_POST['taxa_' . $productType] ?? ''),
                        'meio_pagamento' => sanitize_input($_POST['meio_pagamento_' . $productType] ?? ''),
                        'valor' => parse_currency($_POST['valor_' . $productType] ?? '0')
                    ];
                    break;
                    
                default:
                    // Google, Membro Key, Outros - apenas meio_pagamento e valor
                    $data[$productType] = [
                        'meio_pagamento' => sanitize_input($_POST['meio_pagamento_' . $productType] ?? ''),
                        'valor' => parse_currency($_POST['valor_' . $productType] ?? '0')
                    ];
                    break;
            }
        }
        
        error_log('Dados coletados: ' . json_encode($data));
        return $data;
    }
    
    /**
     * Migra estabelecimento para SistPay (apenas se tiver produto PagBank)
     */
    public function migrateToSistPay($id)
    {
        Auth::requireAdmin();
        
        $establishment = $this->establishmentModel->findById($id);
        
        if (!$establishment) {
            $_SESSION['error'] = 'Estabelecimento não encontrado';
            redirect(url('estabelecimentos'));
        }
        
        // Verificar se tem prod-pagbank
        $hasPagBank = false;
        $products = $this->establishmentProductModel->getAllProducts($id);
        
        write_log('=== VERIFICAÇÃO DE PRODUTOS PAGBANK ===', 'app.log');
        write_log('Produtos encontrados: ' . json_encode($products), 'app.log');
        
        // Verificar em 'other' products
        if (isset($products['other']) && is_array($products['other'])) {
            foreach ($products['other'] as $product) {
                $productType = $product['product_type'] ?? $product['product_name'] ?? '';
                write_log('Verificando produto: ' . $productType, 'app.log');
                if ($productType === 'prod-pagbank') {
                    $hasPagBank = true;
                    write_log('Produto PagBank encontrado!', 'app.log');
                    break;
                }
            }
        }
        
        if (!$hasPagBank) {
            write_log('ERRO: Estabelecimento não possui produto PagBank', 'app.log');
            $_SESSION['error'] = 'Este estabelecimento não possui o produto PagBank. A migração só é permitida para estabelecimentos com PagBank.';
            redirect(url('estabelecimentos/' . $id));
        }
        
        try {
            write_log('=== INICIANDO MIGRAÇÃO PARA SISTPAY ===', 'app.log');
            write_log('ID do estabelecimento: ' . $id, 'app.log');
            
            // Verificar configurações da API
            $settingsModel = new \App\Models\SistPaySettings();
            $settings = $settingsModel->getActiveSettings();
            write_log('Configurações SistPay: ' . json_encode($settings), 'app.log');
            
            // Preparar dados do estabelecimento
            $data = [
                'nome_fantasia' => $establishment['nome_fantasia'] ?? '',
                'nome_completo' => $establishment['nome_completo'] ?? '',
                'cpf' => $establishment['cpf'] ?? '',
                'cnpj' => $establishment['cnpj'] ?? '',
                'email' => $establishment['email'] ?? '',
                'telefone' => $establishment['telefone'] ?? '',
                'registration_type' => $establishment['registration_type'] ?? 'PF',
                'razao_social' => $establishment['razao_social'] ?? null,
                'logradouro' => $establishment['logradouro'] ?? '',
                'numero' => $establishment['numero'] ?? '',
                'complemento' => $establishment['complemento'] ?? null,
                'bairro' => $establishment['bairro'] ?? '',
                'cidade' => $establishment['cidade'] ?? '',
                'uf' => $establishment['uf'] ?? '',
                'cep' => $establishment['cep'] ?? '',
                'segmento' => $establishment['segmento'] ?? ''
            ];
            
            write_log('Dados preparados para migração: ' . json_encode($data, JSON_UNESCAPED_UNICODE), 'app.log');
            write_log('Chamando integrateWithSistPay...', 'app.log');
            
            $result = $this->integrateWithSistPay($id, $data);
            
            write_log('Resultado de integrateWithSistPay: ' . json_encode($result, JSON_UNESCAPED_UNICODE), 'app.log');
            
            if ($result && isset($result['success']) && $result['success']) {
                // Salvar o sistpay_id retornado pela API
                if (isset($result['data']['id'])) {
                    $this->establishmentModel->updateSistPayId($id, $result['data']['id']);
                }
                
                $_SESSION['success'] = 'Estabelecimento migrado para SistPay com sucesso!';
                if (isset($result['data']['id'])) {
                    $_SESSION['success'] .= ' ID SistPay: ' . $result['data']['id'];
                }
                if (isset($result['data']['document_url'])) {
                    $_SESSION['success'] .= ' URL para documentos: ' . $result['data']['document_url'];
                }
            } else {
                $_SESSION['error'] = 'Erro ao migrar para SistPay. Verifique os logs para mais detalhes.';
            }
        } catch (\Exception $e) {
            $errorMessage = 'Erro ao migrar para SistPay: ' . $e->getMessage();
            $_SESSION['error'] = $errorMessage;
            write_log('Erro na migração para SistPay: ' . $e->getMessage(), 'app.log');
            write_log('Stack trace: ' . $e->getTraceAsString(), 'app.log');
            write_log('Dados do estabelecimento: ' . json_encode($establishment), 'app.log');
        }
        
        redirect(url('estabelecimentos/' . $id));
    }
    
    /**
     * Integra com a API SistPay
     * Nota: A verificação de produto PagBank já foi feita antes de chamar este método
     */
    private function integrateWithSistPay($establishmentId, $data)
    {
        write_log('=== INTEGRATE WITH SISTPAY INICIADO ===', 'app.log');
        write_log('Establishment ID: ' . $establishmentId, 'app.log');
        write_log('Dados recebidos: ' . json_encode($data, JSON_UNESCAPED_UNICODE), 'app.log');
        
        try {
            $api = new SistPayApi();
            
            write_log('Verificando se API está configurada...', 'app.log');
            if (!$api->isConfigured()) {
                $errorMsg = 'API SistPay não está configurada ou não está ativa';
                write_log('ERRO: ' . $errorMsg, 'app.log');
                throw new \Exception($errorMsg);
            }
            write_log('API está configurada, continuando...', 'app.log');
            
            // Selecionar segmento baseado no tipo de pessoa
            // PF = segmento 7, PJ = segmento 6
            $registrationType = $data['registration_type'] ?? 'PF';
            $segmentId = ($registrationType === 'PF') ? 7 : 6;
            
            // Buscar plano do estabelecimento (do produto PagBank)
            $pagBankProduct = $this->establishmentProductModel->getOtherProducts($establishmentId);
            $planId = null;
            foreach ($pagBankProduct as $product) {
                if (($product['product_type'] ?? '') === 'prod-pagbank') {
                    $planId = !empty($product['plan']) ? (int)$product['plan'] : null;
                    break;
                }
            }
            
            // Se não tiver plano configurado, usar fallback
            if (empty($planId)) {
                $planId = 11; // D+1 - PADRÃO como fallback
                write_log('AVISO: Plano não configurado para o estabelecimento, usando fallback: ' . $planId, 'app.log');
            }
            
            // Preparar dados para a API
            $apiData = [
                'nome_fantasia' => $data['nome_fantasia'] ?? '',
                'nome_completo' => $data['nome_completo'] ?? '',
                'cpf' => $data['cpf'] ?? '',
                'cnpj' => $data['cnpj'] ?? '',
                'email' => $data['email'] ?? '',
                'telefone' => $data['telefone'] ?? '',
                'registration_type' => $registrationType,
                'razao_social' => $data['razao_social'] ?? null,
                'logradouro' => $data['logradouro'] ?? '',
                'numero' => $data['numero'] ?? '',
                'complemento' => $data['complemento'] ?? null,
                'bairro' => $data['bairro'] ?? '',
                'cidade' => $data['cidade'] ?? '',
                'uf' => $data['uf'] ?? '',
                'cep' => $data['cep'] ?? '',
                'plan' => $planId,
                'segment' => $segmentId,
                'code' => 'EST-' . $establishmentId // Código único baseado no ID do estabelecimento
            ];
            
            // Chamar API
            write_log('Chamando API SistPay com dados: ' . json_encode($apiData, JSON_UNESCAPED_UNICODE), 'app.log');
            
            $result = $api->createEstablishment($apiData);
            
            write_log('Integração com SistPay bem-sucedida: ' . json_encode($result, JSON_UNESCAPED_UNICODE), 'app.log');
            
            // Retornar resultado para uso no método público
            return $result;
            
        } catch (\Exception $e) {
            write_log('=== ERRO NA INTEGRAÇÃO COM SISTPAY ===', 'app.log');
            write_log('Mensagem: ' . $e->getMessage(), 'app.log');
            write_log('Arquivo: ' . $e->getFile() . ':' . $e->getLine(), 'app.log');
            write_log('Stack trace: ' . $e->getTraceAsString(), 'app.log');
            // Re-lançar exceção para tratamento no método público
            throw $e;
        }
    }
    
    private function handleDocumentUpload($establishmentId)
    {
        if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
            // Coletar tipos de documentos do POST
            $documentTypes = $_POST['document_type'] ?? [];
            
            foreach ($_FILES['documents']['name'] as $key => $filename) {
                if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK && !empty($filename)) {
                    $file = [
                        'name' => $_FILES['documents']['name'][$key],
                        'type' => $_FILES['documents']['type'][$key],
                        'tmp_name' => $_FILES['documents']['tmp_name'][$key],
                        'size' => $_FILES['documents']['size'][$key]
                    ];
                    
                    try {
                        $uploadResult = $this->fileUpload->upload($file, 'documents');
                        
                        // Verificar se upload retornou array ou string
                        if (is_array($uploadResult)) {
                            $filePath = $uploadResult['file_path'];
                            $originalName = $uploadResult['original_name'] ?? $filename;
                        } else {
                            $filePath = $uploadResult;
                            $originalName = $filename;
                        }
                        
                        // Obter tipo do documento do array, com fallback seguro
                        $rawDocumentType = $documentTypes[$key] ?? '';
                        
                        // Limpar e normalizar o tipo de documento
                        $documentType = trim($rawDocumentType);
                        $documentType = strtolower($documentType);
                        
                        // Se não tiver tipo ou estiver vazio, usar padrão
                        if (empty($documentType)) {
                            $documentType = 'outros_documentos';
                        }
                        
                        // Log para debug (remover em produção se necessário)
                        write_log("Tipo de documento recebido: '{$rawDocumentType}' -> normalizado: '{$documentType}'", 'app.log');
                        
                        $this->establishmentModel->addDocument(
                            $establishmentId, 
                            $filePath, 
                            $originalName, 
                            $documentType
                        );
                    } catch (\PDOException $e) {
                        // Capturar especificamente erros de banco de dados
                        $errorMessage = $e->getMessage();
                        write_log('Erro PDO ao fazer upload de documento: ' . $errorMessage, 'app.log');
                        
                        // Se for erro de ENUM, usar valor padrão e tentar novamente
                        if (strpos($errorMessage, 'document_type') !== false || strpos($errorMessage, 'Data truncated') !== false) {
                            try {
                                write_log('Tentando salvar documento com tipo padrão devido a erro de ENUM', 'app.log');
                                $this->establishmentModel->addDocument(
                                    $establishmentId, 
                                    $filePath, 
                                    $originalName, 
                                    'outros_documentos'
                                );
                            } catch (\Exception $retryException) {
                                write_log('Erro ao tentar salvar com tipo padrão: ' . $retryException->getMessage(), 'app.log');
                                $_SESSION['warning'] = 'Erro ao fazer upload de alguns documentos. Verifique os logs para mais detalhes.';
                            }
                        } else {
                            $_SESSION['warning'] = 'Erro ao fazer upload de alguns documentos: ' . $errorMessage;
                        }
                    } catch (\Exception $e) {
                        write_log('Erro ao fazer upload de documento: ' . $e->getMessage(), 'app.log');
                        $_SESSION['warning'] = 'Erro ao fazer upload de alguns documentos: ' . $e->getMessage();
                    }
                }
            }
        }
    }
    
    private function getAvailableProducts()
    {
        $products = [
            'CDX_EVO' => 'CDX/EVO',
            'CDC' => 'CDC',
            'GOOGLE' => 'Google',
            'MEMBRO_KEY' => 'Membro Key',
            'PAGBANK' => 'PagBank',
            'OUTROS' => 'Outros'
        ];
        
        // Se for representante, filtrar apenas produtos permitidos
        if (Auth::isRepresentative()) {
            $representative = Auth::representative();
            $allowedProducts = $this->representativeModel->getProducts($representative['id']);
            $allowedProductTypes = array_column($allowedProducts, 'product_type');
            
            // Se o representante tem produtos permitidos definidos, filtrar
            // Se não tiver nenhum produto definido, pode cadastrar todos
            if (!empty($allowedProductTypes)) {
                $products = array_filter($products, function($key) use ($allowedProductTypes) {
                    return in_array($key, $allowedProductTypes);
                }, ARRAY_FILTER_USE_KEY);
            }
        }
        
        return $products;
    }
    
    /**
     * Mapeia ID do produto (do formulário) para valor do ENUM (do banco)
     */
    private function mapProductIdToEnumValue($productId)
    {
        // Mapeamento exato conforme especificado
        $mapping = [
            'prod-google' => 'GOOGLE',
            'prod-membro-key' => 'MEMBRO_KEY',
            'prod-outros' => 'OUTROS',
            'prod-pagbank' => 'PAGBANK',
            'prod-cdc' => 'CDC',
            'prod-subaquirente' => 'CDX_EVO',
        ];
        
        // Retornar o mapeamento se existir
        if (isset($mapping[$productId])) {
            return $mapping[$productId];
        }
        
        // Se não encontrou no mapeamento, retornar OUTROS como fallback
        return 'OUTROS';
    }
    
    /**
     * Sanitiza e valida o tipo de conta para garantir valor válido do ENUM
     */
    private function sanitizeTipoConta($tipoConta)
    {
        $tipoConta = trim(sanitize_input($tipoConta));
        
        // Se estiver vazio, retornar NULL (a coluna permite NULL)
        if (empty($tipoConta)) {
            return null;
        }
        
        // Valores válidos do ENUM
        $validValues = ['conta_corrente', 'conta_poupanca'];
        
        // Se o valor for válido, retornar; caso contrário, retornar NULL
        if (in_array($tipoConta, $validValues)) {
            return $tipoConta;
        }
        
        return null;
    }
    
    private function validateCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        // Verificar se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Para desenvolvimento/teste, aceitar alguns CPFs comuns
        $testCPFs = [
            '12345678901', // CPF comum para teste
            '11111111111', // CPF comum para teste
            '00000000000', // CPF comum para teste
            '12345678909', // CPF válido
            '11144477735', // CPF válido
            '98765432100'  // CPF válido
        ];
        
        if (in_array($cpf, $testCPFs)) {
            return true;
        }
        
        // Validar dígitos verificadores para CPFs reais
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }
    
    private function validateCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Preencher com zeros à esquerda se tiver menos de 14 dígitos
        if (strlen($cnpj) < 14) {
            $cnpj = str_pad($cnpj, 14, '0', STR_PAD_LEFT);
        }
        
        if (strlen($cnpj) !== 14) {
            return false;
        }
        
        // Verificar se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        
        // Validar primeiro dígito verificador
        $sum = 0;
        $weight = 5;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weight;
            $weight = ($weight == 2) ? 9 : $weight - 1;
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;
        
        if ($cnpj[12] != $digit1) {
            return false;
        }
        
        // Validar segundo dígito verificador
        $sum = 0;
        $weight = 6;
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weight;
            $weight = ($weight == 2) ? 9 : $weight - 1;
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;
        
        return $cnpj[13] == $digit2;
    }
    
    /**
     * Exibe a página de importação de CSV
     */
    public function import()
    {
        // Aumentar timeout para esta operação
        set_time_limit(60);
        
        try {
            Auth::requireAdmin();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Acesso negado. Apenas administradores podem acessar esta página.';
            redirect(url('estabelecimentos'));
        }
        
        try {
            // Garantir que não há transações abertas
            try {
                $db = \App\Core\Database::getInstance();
                $connection = $db->getConnection();
                if ($connection->inTransaction()) {
                    $db->rollback();
                }
            } catch (\Exception $dbError) {
                // Ignorar erros de banco nesta etapa, apenas logar
                error_log('Aviso ao verificar transações: ' . $dbError->getMessage());
            }
            
            $data = [
                'title' => 'Importar Estabelecimentos',
                'currentPage' => 'estabelecimentos'
            ];
            
            view('establishments/import', $data);
        } catch (\Exception $e) {
            error_log('Erro ao exibir página de importação: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $_SESSION['error'] = 'Erro ao carregar página de importação: ' . $e->getMessage();
            redirect(url('estabelecimentos'));
        } finally {
            // Restaurar timeout padrão
            set_time_limit(30);
        }
    }
    
    /**
     * Processa a importação do arquivo CSV
     */
    public function processImport()
    {
        try {
            Auth::requireAdmin();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Acesso negado. Apenas administradores podem importar CSV.';
            redirect(url('estabelecimentos/import'));
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método inválido. Use o formulário de importação.';
            redirect(url('estabelecimentos/import'));
        }
        
        // Validar CSRF
        try {
            csrf_verify();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Token de segurança inválido. Recarregue a página e tente novamente. Erro: ' . $e->getMessage();
            redirect(url('estabelecimentos/import'));
        }
        
        // Verificar se arquivo foi enviado
        if (!isset($_FILES['csv_file'])) {
            $_SESSION['error'] = 'Nenhum arquivo foi enviado. Por favor, selecione um arquivo CSV.';
            redirect(url('estabelecimentos/import'));
        }
        
        if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'O arquivo excede o tamanho máximo permitido pelo servidor.',
                UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o tamanho máximo permitido pelo formulário.',
                UPLOAD_ERR_PARTIAL => 'O arquivo foi enviado parcialmente.',
                UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta a pasta temporária.',
                UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo no disco.',
                UPLOAD_ERR_EXTENSION => 'Uma extensão PHP parou o upload do arquivo.'
            ];
            
            $errorMsg = $errorMessages[$_FILES['csv_file']['error']] ?? 'Erro desconhecido no upload: ' . $_FILES['csv_file']['error'];
            $_SESSION['error'] = 'Erro ao fazer upload do arquivo: ' . $errorMsg;
            redirect(url('estabelecimentos/import'));
        }
        
        $file = $_FILES['csv_file'];
        $skipDuplicates = !empty($_POST['skip_duplicates']);
        $autoApprove = !empty($_POST['auto_approve']);
        
        // Validar se o arquivo tem nome
        if (empty($file['name'])) {
            $_SESSION['error'] = 'Nome do arquivo inválido.';
            redirect(url('estabelecimentos/import'));
        }
        
        // Validar extensão
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['csv', 'txt'])) {
            $_SESSION['error'] = 'Arquivo inválido. Apenas arquivos CSV ou TXT são permitidos. Extensão encontrada: ' . $extension;
            redirect(url('estabelecimentos/import'));
        }
        
        // Validar tamanho do arquivo (máximo 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            $_SESSION['error'] = 'Arquivo muito grande. Tamanho máximo permitido: 10MB. Tamanho do arquivo: ' . round($file['size'] / 1024 / 1024, 2) . 'MB';
            redirect(url('estabelecimentos/import'));
        }
        
        // Verificar se o arquivo temporário existe
        if (!file_exists($file['tmp_name'])) {
            $_SESSION['error'] = 'Arquivo temporário não encontrado. Verifique as configurações de upload do PHP.';
            redirect(url('estabelecimentos/import'));
        }
        
        try {
            // Log inicial
            if (function_exists('write_log')) {
                write_log('Iniciando importação CSV. Arquivo: ' . $file['name'] . ', Tamanho: ' . $file['size'] . ' bytes', 'csv-import.log');
            }
            
            // Ler arquivo CSV com encoding correto
            $handle = @fopen($file['tmp_name'], 'r');
            if (!$handle) {
                $error = error_get_last();
                $errorMsg = 'Não foi possível abrir o arquivo CSV. Erro: ' . ($error['message'] ?? 'Desconhecido');
                if (function_exists('write_log')) {
                    write_log($errorMsg, 'csv-import.log');
                }
                throw new \Exception($errorMsg);
            }
            
            // Detectar encoding e converter se necessário
            $content = @file_get_contents($file['tmp_name']);
            if ($content === false) {
                throw new \Exception('Não foi possível ler o conteúdo do arquivo CSV');
            }
            
            $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                $tempFile = tempnam(sys_get_temp_dir(), 'csv_import_');
                if ($tempFile === false) {
                    throw new \Exception('Não foi possível criar arquivo temporário');
                }
                file_put_contents($tempFile, $content);
                fclose($handle);
                $handle = fopen($tempFile, 'r');
                if (!$handle) {
                    throw new \Exception('Não foi possível reabrir o arquivo após conversão de encoding');
                }
            }
            
            // Ler cabeçalho (primeira linha)
            $header = fgetcsv($handle, 0, ';');
            if (!$header || empty($header)) {
                throw new \Exception('Arquivo CSV vazio ou inválido. Verifique se o arquivo contém dados.');
            }
            
            // Normalizar cabeçalhos (remover acentos, espaços, etc)
            $header = array_map(function($h) {
                $h = trim($h);
                // Remover BOM se presente
                $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
                return mb_strtoupper($h);
            }, $header);
            
            // Log dos cabeçalhos encontrados para debug
            if (function_exists('write_log')) {
                write_log('Cabeçalhos CSV encontrados: ' . json_encode($header), 'csv-import.log');
            }
            
            // Mapear índices das colunas
            $columnMap = [];
            $expectedColumns = [
                // Formato GrupoKey
                'CNPJ' => 'cnpj',
                'CPF' => 'cpf',
                'TIPO' => 'cadastro', // TIPO = PF ou PJ
                'CADASTRO' => 'cadastro', // Formato antigo
                'SEGMENTO' => 'segmento',
                'RAZÃO SOCIAL' => 'razao_social',
                'RAZAO SOCIAL' => 'razao_social',
                'RAZO SOCIAL' => 'razao_social', // Encoding ISO-8859-1
                'NOME FANTASIA' => 'nome_fantasia',
                'NOME COMPLETO' => 'nome_completo',
                'DATA DE NASCIMENTO' => 'data_nascimento',
                'TELEFONE CELULAR' => 'telefone',
                'TELEFONE' => 'telefone', // Formato alternativo
                'ENDEREÇO' => 'endereco',
                'ENDERECO' => 'endereco',
                'ENDEREO' => 'endereco', // Encoding ISO-8859-1
                'BAIRRO' => 'bairro',
                'CIDADE' => 'cidade',
                'UF' => 'uf',
                'CEP' => 'cep',
                'E-MAIL' => 'email',
                'EMAIL' => 'email',
                'E-MAIL' => 'email'
            ];
            
            foreach ($expectedColumns as $headerName => $fieldName) {
                $index = array_search($headerName, $header);
                if ($index !== false) {
                    $columnMap[$fieldName] = $index;
                }
            }
            
            // Se não encontrou alguns campos, tentar busca case-insensitive e com normalização
            if (count($columnMap) < 3) {
                foreach ($header as $index => $headerValue) {
                    $normalized = mb_strtoupper(trim($headerValue));
                    // Tentar mapear por similaridade
                    if (stripos($normalized, 'CNPJ') !== false && !isset($columnMap['cnpj'])) {
                        $columnMap['cnpj'] = $index;
                    }
                    if (stripos($normalized, 'CPF') !== false && !isset($columnMap['cpf'])) {
                        $columnMap['cpf'] = $index;
                    }
                    if ((stripos($normalized, 'CADASTRO') !== false || stripos($normalized, 'TIPO') !== false) && !isset($columnMap['cadastro'])) {
                        $columnMap['cadastro'] = $index;
                    }
                    if ((stripos($normalized, 'RAZ') !== false || stripos($normalized, 'SOCIAL') !== false) && !isset($columnMap['razao_social'])) {
                        $columnMap['razao_social'] = $index;
                    }
                    if (stripos($normalized, 'FANTASIA') !== false && !isset($columnMap['nome_fantasia'])) {
                        $columnMap['nome_fantasia'] = $index;
                    }
                    if (stripos($normalized, 'NOME COMPLETO') !== false && !isset($columnMap['nome_completo'])) {
                        $columnMap['nome_completo'] = $index;
                    }
                    if (stripos($normalized, 'SEGMENTO') !== false && !isset($columnMap['segmento'])) {
                        $columnMap['segmento'] = $index;
                    }
                    if (stripos($normalized, 'TELEFONE') !== false && !isset($columnMap['telefone'])) {
                        $columnMap['telefone'] = $index;
                    }
                    if ((stripos($normalized, 'ENDERE') !== false || stripos($normalized, 'RUA') !== false) && !isset($columnMap['endereco'])) {
                        $columnMap['endereco'] = $index;
                    }
                    if (stripos($normalized, 'BAIRRO') !== false && !isset($columnMap['bairro'])) {
                        $columnMap['bairro'] = $index;
                    }
                    if (stripos($normalized, 'CIDADE') !== false && !isset($columnMap['cidade'])) {
                        $columnMap['cidade'] = $index;
                    }
                    if (stripos($normalized, 'UF') !== false && !isset($columnMap['uf'])) {
                        $columnMap['uf'] = $index;
                    }
                    if (stripos($normalized, 'CEP') !== false && !isset($columnMap['cep'])) {
                        $columnMap['cep'] = $index;
                    }
                    if ((stripos($normalized, 'EMAIL') !== false || stripos($normalized, 'E-MAIL') !== false) && !isset($columnMap['email'])) {
                        $columnMap['email'] = $index;
                    }
                }
            }
            
            // Verificar se temos as colunas essenciais
            $requiredColumns = ['cadastro', 'nome_completo', 'email'];
            $missingColumns = [];
            foreach ($requiredColumns as $col) {
                if (!isset($columnMap[$col])) {
                    $missingColumns[] = $col;
                }
            }
            
            if (!empty($missingColumns)) {
                $foundHeaders = implode(', ', $header);
                throw new \Exception('Colunas obrigatórias não encontradas: ' . implode(', ', $missingColumns) . '. Cabeçalhos encontrados: ' . $foundHeaders);
            }
            
            // Log do mapeamento para debug
            if (function_exists('write_log')) {
                write_log('Mapeamento de colunas: ' . json_encode($columnMap), 'csv-import.log');
            }
            
            $stats = [
                'total' => 0,
                'success' => 0,
                'errors' => 0,
                'skipped' => 0,
                'errors_list' => []
            ];
            
            $lineNumber = 1; // Começar em 1 porque já lemos o cabeçalho
            
            // Processar cada linha
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $lineNumber++;
                $stats['total']++;
                
                // Pular apenas linhas completamente vazias (todos os campos vazios)
                $hasData = false;
                foreach ($row as $cell) {
                    if (!empty(trim($cell))) {
                        $hasData = true;
                        break;
                    }
                }
                
                if (!$hasData) {
                    continue; // Linha completamente vazia
                }
                
                try {
                    // Extrair dados da linha
                    $data = [];
                    foreach ($columnMap as $field => $index) {
                        $value = isset($row[$index]) ? $row[$index] : '';
                        // Limpar e normalizar valor
                        $value = trim($value);
                        // Remover caracteres de controle
                        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
                        $data[$field] = $value;
                    }
                    
                    // Validar dados básicos - tornar mais flexível
                    if (empty($data['nome_completo'])) {
                        // Tentar usar nome fantasia ou razao social como fallback
                        if (!empty($data['nome_fantasia'])) {
                            $data['nome_completo'] = $data['nome_fantasia'];
                        } elseif (!empty($data['razao_social'])) {
                            $data['nome_completo'] = $data['razao_social'];
                        } else {
                            throw new \Exception('Nome completo, nome fantasia ou razão social obrigatório');
                        }
                    }
                    
                    // Se não tiver email, gerar um temporário baseado no CPF/CNPJ
                    if (empty($data['email'])) {
                        $cpfCnpj = !empty($data['cpf']) ? $this->cleanDocument($data['cpf']) : $this->cleanDocument($data['cnpj'] ?? '');
                        if (!empty($cpfCnpj)) {
                            $data['email'] = 'temp_' . substr($cpfCnpj, 0, 8) . '@importado.temp';
                        } else {
                            // Se não tiver CPF/CNPJ nem email, usar nome como base
                            $nomeBase = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($data['nome_completo']));
                            $data['email'] = 'temp_' . substr($nomeBase, 0, 15) . '_' . $lineNumber . '@importado.temp';
                        }
                    }
                    
                    // Determinar tipo de cadastro
                    $cadastro = strtoupper(trim($data['cadastro'] ?? ''));
                    
                    // Normalizar valores comuns (formato GrupoKey usa "TIPO" com valores PF/PJ)
                    $cadastroMap = [
                        'F' => 'PF',
                        'J' => 'PJ',
                        'FISICA' => 'PF',
                        'JURIDICA' => 'PJ',
                        'PESSOA FISICA' => 'PF',
                        'PESSOA JURIDICA' => 'PJ'
                    ];
                    
                    if (isset($cadastroMap[$cadastro])) {
                        $cadastro = $cadastroMap[$cadastro];
                    }
                    
                    if (!in_array($cadastro, ['PF', 'PJ'])) {
                        // Tentar inferir pelo CPF/CNPJ se o tipo não foi informado
                        if (!empty($data['cpf']) && empty($data['cnpj'])) {
                            $cadastro = 'PF';
                        } elseif (!empty($data['cnpj']) && empty($data['cpf'])) {
                            $cadastro = 'PJ';
                        } else {
                            throw new \Exception('Tipo de cadastro inválido: ' . ($cadastro ?: 'vazio') . '. Deve ser PF ou PJ.');
                        }
                    }
                    
                    $registrationType = $cadastro;
                    
                    // Validar e limpar CPF/CNPJ - preencher zeros à esquerda se necessário
                    $cpfRaw = $this->cleanDocument($data['cpf'] ?? '');
                    $cnpjRaw = $this->cleanDocument($data['cnpj'] ?? '');
                    
                    // Preencher zeros à esquerda se tiver menos dígitos
                    if (!empty($cpfRaw) && strlen($cpfRaw) < 11) {
                        $cpfRaw = str_pad($cpfRaw, 11, '0', STR_PAD_LEFT);
                    }
                    if (!empty($cnpjRaw) && strlen($cnpjRaw) < 14) {
                        $cnpjRaw = str_pad($cnpjRaw, 14, '0', STR_PAD_LEFT);
                    }
                    
                    $cpf = $cpfRaw;
                    $cnpj = $cnpjRaw;
                    
                    // Validar CPF/CNPJ - tornar mais flexível
                    $documentValid = false;
                    $finalCpf = null;
                    $finalCnpj = null;
                    
                    if ($registrationType === 'PF') {
                        if (!empty($cpf)) {
                            if ($this->validateCPF($cpf)) {
                                $finalCpf = $cpf;
                                $documentValid = true;
                            } else {
                                // Se CPF inválido mesmo após preencher zeros, tentar usar CNPJ
                                if (!empty($cnpj) && $this->validateCNPJ($cnpj)) {
                                    $registrationType = 'PJ';
                                    $finalCnpj = $cnpj;
                                    $documentValid = true;
                                }
                            }
                        } else {
                            // Se não tiver CPF, tentar usar CNPJ
                            if (!empty($cnpj) && $this->validateCNPJ($cnpj)) {
                                $registrationType = 'PJ';
                                $finalCnpj = $cnpj;
                                $documentValid = true;
                            }
                        }
                    } else {
                        if (!empty($cnpj)) {
                            if ($this->validateCNPJ($cnpj)) {
                                $finalCnpj = $cnpj;
                                $documentValid = true;
                            } else {
                                // Se CNPJ inválido mesmo após preencher zeros, tentar usar CPF
                                if (!empty($cpf) && $this->validateCPF($cpf)) {
                                    $registrationType = 'PF';
                                    $finalCpf = $cpf;
                                    $documentValid = true;
                                }
                            }
                        } else {
                            // Se não tiver CNPJ, tentar usar CPF
                            if (!empty($cpf) && $this->validateCPF($cpf)) {
                                $registrationType = 'PF';
                                $finalCpf = $cpf;
                                $documentValid = true;
                            }
                        }
                    }
                    
                    // Se ainda não tiver documento válido, gerar temporário
                    if (!$documentValid) {
                        if ($registrationType === 'PF') {
                            $finalCpf = '00000000000';
                            write_log("AVISO: Linha {$lineNumber} sem CPF/CNPJ válido. Usando CPF temporário. CPF original: " . ($data['cpf'] ?? 'vazio'), 'csv-import.log');
                        } else {
                            $finalCnpj = '00000000000000';
                            write_log("AVISO: Linha {$lineNumber} sem CNPJ/CPF válido. Usando CNPJ temporário. CNPJ original: " . ($data['cnpj'] ?? 'vazio'), 'csv-import.log');
                        }
                    }
                    
                    $cpf = $finalCpf;
                    $cnpj = $finalCnpj;
                    
                    // Verificar duplicatas se necessário
                    if ($skipDuplicates) {
                        $existing = null;
                        if ($registrationType === 'PF' && !empty($cpf)) {
                            $existing = $this->establishmentModel->findByCpf($cpf);
                        } elseif ($registrationType === 'PJ' && !empty($cnpj)) {
                            $existing = $this->establishmentModel->findByCnpj($cnpj);
                        }
                        
                        if ($existing) {
                            $stats['skipped']++;
                            continue;
                        }
                    }
                    
                    // Preparar dados para criação
                    $establishmentData = [
                        'registration_type' => $registrationType,
                        'nome_completo' => $data['nome_completo'],
                        'nome_fantasia' => !empty($data['nome_fantasia']) ? $data['nome_fantasia'] : $data['nome_completo'],
                        'segmento' => !empty($data['segmento']) ? $data['segmento'] : 'OUTROS',
                        'telefone' => $this->cleanPhone($data['telefone'] ?? ''),
                        'email' => strtolower(trim($data['email'])),
                        'cep' => $this->cleanCep($data['cep'] ?? ''),
                        'logradouro' => $data['endereco'] ?? '',
                        'numero' => $this->extractNumberFromAddress($data['endereco'] ?? ''),
                        'complemento' => null,
                        'bairro' => $data['bairro'] ?? '',
                        'cidade' => $data['cidade'] ?? '',
                        'uf' => strtoupper(trim($data['uf'] ?? '')),
                        'status' => $autoApprove ? 'APPROVED' : 'PENDING',
                        'products' => ['prod-pagbank'], // Sempre adicionar PagBank
                        'product_data' => []
                    ];
                    
                    if ($registrationType === 'PF') {
                        $establishmentData['cpf'] = $cpf;
                        // Tentar extrair data de nascimento
                        if (!empty($data['data_nascimento'])) {
                            $birthDate = $this->parseDate($data['data_nascimento']);
                            if ($birthDate) {
                                // Podemos salvar em um campo adicional se necessário
                            }
                        }
                    } else {
                        $establishmentData['cnpj'] = $cnpj;
                        $establishmentData['razao_social'] = !empty($data['razao_social']) ? $data['razao_social'] : $data['nome_completo'];
                    }
                    
                    // Garantir que campos obrigatórios não estejam vazios
                    if (empty($establishmentData['telefone'])) {
                        $establishmentData['telefone'] = '00000000000'; // Telefone temporário
                    }
                    if (empty($establishmentData['cep'])) {
                        $establishmentData['cep'] = '00000000'; // CEP temporário
                    }
                    if (empty($establishmentData['logradouro'])) {
                        $establishmentData['logradouro'] = 'Endereço não informado';
                    }
                    if (empty($establishmentData['bairro'])) {
                        $establishmentData['bairro'] = 'Não informado';
                    }
                    if (empty($establishmentData['cidade'])) {
                        $establishmentData['cidade'] = 'Não informado';
                    }
                    if (empty($establishmentData['uf']) || strlen($establishmentData['uf']) !== 2) {
                        $establishmentData['uf'] = 'MG'; // Default para MG
                    }
                    
                    // Definir quem criou
                    if (Auth::isAdmin()) {
                        $establishmentData['created_by_user_id'] = Auth::user()['id'];
                    }
                    
                    // Criar estabelecimento
                    $establishmentId = $this->establishmentModel->create($establishmentData);
                    
                    if ($establishmentId) {
                        $stats['success']++;
                    } else {
                        throw new \Exception('Falha ao criar estabelecimento no banco de dados');
                    }
                    
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $errorInfo = [
                        'line' => $lineNumber,
                        'error' => $e->getMessage(),
                        'data' => $data['nome_completo'] ?? 'N/A'
                    ];
                    $stats['errors_list'][] = $errorInfo;
                    
                    if (function_exists('write_log')) {
                        write_log("ERRO na linha {$lineNumber}: " . $e->getMessage(), 'csv-import.log');
                        write_log("Dados da linha {$lineNumber}: " . json_encode($row), 'csv-import.log');
                        write_log("Dados extraídos linha {$lineNumber}: " . json_encode($data), 'csv-import.log');
                    }
                }
            }
            
            fclose($handle);
            
            // Preparar mensagem de resultado
            $message = "Importação concluída! ";
            $message .= "Total: {$stats['total']}, ";
            $message .= "Sucesso: {$stats['success']}, ";
            $message .= "Erros: {$stats['errors']}, ";
            $message .= "Ignorados: {$stats['skipped']}";
            
            if ($stats['errors'] > 0 && count($stats['errors_list']) > 0) {
                $message .= "\n\nErros encontrados:\n";
                foreach (array_slice($stats['errors_list'], 0, 10) as $error) {
                    $message .= "Linha {$error['line']}: {$error['error']} ({$error['data']})\n";
                }
                if (count($stats['errors_list']) > 10) {
                    $message .= "... e mais " . (count($stats['errors_list']) - 10) . " erros.";
                }
            }
            
            $_SESSION['success'] = $message;
            
            // Log detalhado
            if (function_exists('write_log')) {
                write_log("Importação CSV concluída: " . json_encode($stats), 'csv-import.log');
            } else {
                error_log("Importação CSV concluída: " . json_encode($stats));
            }
            
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorTrace = $e->getTraceAsString();
            
            if (function_exists('write_log')) {
                write_log("Erro na importação CSV: " . $errorMessage . "\nStack trace: " . $errorTrace, 'csv-import.log');
            } else {
                error_log("Erro na importação CSV: " . $errorMessage);
                error_log("Stack trace: " . $errorTrace);
            }
            
            $_SESSION['error'] = 'Erro ao processar importação: ' . $errorMessage;
            
            // Fechar handle se ainda estiver aberto
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
        }
        
        redirect(url('estabelecimentos/import'));
    }
    
    /**
     * Limpa documento (CPF/CNPJ) removendo formatação e preenche zeros à esquerda se necessário
     */
    private function cleanDocument($doc, $expectedLength = null)
    {
        $cleaned = preg_replace('/[^0-9]/', '', $doc);
        
        // Se especificou o tamanho esperado e o documento tem menos dígitos, preencher com zeros à esquerda
        if ($expectedLength !== null && strlen($cleaned) < $expectedLength) {
            $cleaned = str_pad($cleaned, $expectedLength, '0', STR_PAD_LEFT);
        }
        
        return $cleaned;
    }
    
    /**
     * Limpa telefone removendo formatação
     */
    private function cleanPhone($phone)
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        // Se começar com 0, remover
        if (strlen($cleaned) > 10 && $cleaned[0] === '0') {
            $cleaned = substr($cleaned, 1);
        }
        return $cleaned;
    }
    
    /**
     * Limpa CEP removendo formatação
     */
    private function cleanCep($cep)
    {
        return preg_replace('/[^0-9]/', '', $cep);
    }
    
    /**
     * Extrai número do endereço
     */
    private function extractNumberFromAddress($address)
    {
        // Tentar encontrar número no endereço
        if (preg_match('/\b(\d+)\b/', $address, $matches)) {
            return $matches[1];
        }
        return 'S/N';
    }
    
    /**
     * Parse data em vários formatos
     */
    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }
        
        // Tentar vários formatos
        $formats = ['Y-m-d H:i:s', 'Y-m-d', 'd/m/Y', 'd-m-Y'];
        
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }
        
        return null;
    }
}
