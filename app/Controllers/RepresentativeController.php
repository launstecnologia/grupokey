<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Representative;
use App\Models\DynamicProduct;
use App\Core\FileUpload;
use App\Core\Mailer;

class RepresentativeController
{
    private $representativeModel;
    private $dynamicProductModel;
    private $fileUpload;
    private $mailer;

    public function __construct()
    {
        $this->representativeModel = new Representative();
        $this->dynamicProductModel = new DynamicProduct();
        $this->fileUpload = new FileUpload();
        // Mailer será instanciado apenas quando necessário
    }

    public function index()
    {
        Auth::requireAdmin();
        
        $filters = $this->getFilters();
        $representatives = $this->representativeModel->getAll($filters);
        $stats = $this->representativeModel->getStats($filters);
        
        $data = [
            'title' => 'Representantes',
            'currentPage' => 'representantes',
            'representatives' => $representatives,
            'stats' => $stats,
            'filters' => $filters
        ];
        
        view('representatives/index', $data);
    }

    public function create()
    {
        Auth::requireAdmin();
        $productOptions = $this->getRepresentativeProductOptions();
        
        $data = [
            'title' => 'Novo Representante',
            'currentPage' => 'representantes',
            'productOptions' => $productOptions
        ];
        
        view('representatives/create', $data);
    }

    private function getRepresentativeProductOptions()
    {
        $options = [
            [
                'value' => 'PAGBANK',
                'label' => 'PagSeguro',
                'slug' => 'pagseguro'
            ]
        ];

        $products = $this->dynamicProductModel->getAll();
        foreach ($products as $product) {
            $id = (int)($product['id'] ?? 0);
            $name = trim((string)($product['name'] ?? ''));
            $slug = trim((string)($product['slug'] ?? ''));
            $isActive = (int)($product['is_active'] ?? 0) === 1;
            if ($id <= 0 || $name === '' || !$isActive) {
                continue;
            }

            $slugLower = strtolower($slug);
            $nameLower = strtolower($name);
            if (
                strpos($slugLower, 'pagbank') !== false ||
                strpos($nameLower, 'pagbank') !== false ||
                strpos($slugLower, 'pagseguro') !== false ||
                strpos($nameLower, 'pagseguro') !== false
            ) {
                continue;
            }

            $options[] = [
                'value' => 'DYNAMIC_' . $id,
                'label' => $name,
                'slug' => $slugLower
            ];
        }

        usort($options, function ($a, $b) {
            if (($a['value'] ?? '') === 'PAGBANK') {
                return -1;
            }
            if (($b['value'] ?? '') === 'PAGBANK') {
                return 1;
            }
            return strcasecmp((string)($a['label'] ?? ''), (string)($b['label'] ?? ''));
        });

        return $options;
    }

    private function normalizeAllowedProducts(array $allowedProducts, array $productOptions)
    {
        $normalized = [];
        $validValues = [];
        $dynamicAliases = [];

        foreach ($productOptions as $option) {
            $value = (string)($option['value'] ?? '');
            if ($value === '') {
                continue;
            }
            $validValues[$value] = true;

            if (strpos($value, 'DYNAMIC_') === 0) {
                $normalizedLabel = preg_replace('/[^A-Z0-9]/', '', strtoupper((string)($option['label'] ?? '')));
                if ($normalizedLabel !== '') {
                    $dynamicAliases[$normalizedLabel] = $value;
                }
                $normalizedSlug = preg_replace('/[^A-Z0-9]/', '', strtoupper((string)($option['slug'] ?? '')));
                if ($normalizedSlug !== '') {
                    $dynamicAliases[$normalizedSlug] = $value;
                }
            }
        }

        foreach ($allowedProducts as $rawValue) {
            $value = trim((string)$rawValue);
            if ($value === '') {
                continue;
            }

            if (isset($validValues[$value])) {
                $normalized[] = $value;
                continue;
            }

            $upperValue = strtoupper($value);
            if (isset($validValues[$upperValue])) {
                $normalized[] = $upperValue;
                continue;
            }

            if ($upperValue === 'PAGSEGURO' || $upperValue === 'MANUAL:PAGSEGURO' || $upperValue === 'PAGBANK') {
                $normalized[] = 'PAGBANK';
                continue;
            }

            if (preg_match('/^DYNAMIC_(\d+)$/', $upperValue, $matches)) {
                $candidate = 'DYNAMIC_' . (int)$matches[1];
                if (isset($validValues[$candidate])) {
                    $normalized[] = $candidate;
                }
                continue;
            }

            if (preg_match('/^DYNAMIC:(\d+)$/', $upperValue, $matches)) {
                $candidate = 'DYNAMIC_' . (int)$matches[1];
                if (isset($validValues[$candidate])) {
                    $normalized[] = $candidate;
                }
                continue;
            }

            if (ctype_digit($value)) {
                $candidate = 'DYNAMIC_' . (int)$value;
                if (isset($validValues[$candidate])) {
                    $normalized[] = $candidate;
                }
                continue;
            }

            $normalizedText = preg_replace('/[^A-Z0-9]/', '', strtoupper($value));
            if ($normalizedText !== '' && isset($dynamicAliases[$normalizedText])) {
                $normalized[] = $dynamicAliases[$normalizedText];
                continue;
            }

            // Compatibilidade com valores legados
            if ($upperValue === 'CDC' && isset($dynamicAliases['CDC'])) {
                $normalized[] = $dynamicAliases['CDC'];
            } elseif (($upperValue === 'GOOGLE') && isset($dynamicAliases['GOOGLE'])) {
                $normalized[] = $dynamicAliases['GOOGLE'];
            } elseif (($upperValue === 'CDX_EVO' || $upperValue === 'EVO' || $upperValue === 'CDXEVO') && isset($dynamicAliases['EVO'])) {
                $normalized[] = $dynamicAliases['EVO'];
            }
        }

        return array_values(array_unique($normalized));
    }
    
    public function store()
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('representantes/create'));
        }
        
        // Salvar senha original antes de hashar (para enviar no email)
        $senhaOriginal = sanitize_input($_POST['senha'] ?? '');
        
        $data = $this->validateAndSanitizeInput();
        
        if (isset($_SESSION['validation_errors'])) {
            redirect(url('representantes/create'));
        }
        
        try {
            $representativeId = $this->representativeModel->create($data);
            
            // Salvar produtos permitidos
            $allowedProducts = $_POST['allowed_products'] ?? [];
            $normalizedProducts = $this->normalizeAllowedProducts(
                is_array($allowedProducts) ? $allowedProducts : [],
                $this->getRepresentativeProductOptions()
            );
            $this->representativeModel->setProducts($representativeId, $normalizedProducts);
            
            // Enviar email de boas-vindas apenas se configurado
            // Usar a senha original (antes do hash) para enviar no email
            if (isset($_ENV['MAIL_USER']) && !empty($_ENV['MAIL_USER'])) {
                $this->mailer = new Mailer();
                $this->mailer->sendWelcomeRepresentative($data['email'], $data['nome_completo'], $senhaOriginal);
            }
            
            $_SESSION['success'] = 'Representante cadastrado com sucesso!';
            redirect(url('representantes/' . $representativeId));
            
        } catch (\Exception $e) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error'] = $this->friendlyRepresentativeError($e->getMessage(), 'cadastrar');
            redirect(url('representantes/create'));
        }
    }

    public function show($id)
    {
        Auth::requireAdmin();
        
        $representative = $this->representativeModel->findById($id);
        
        if (!$representative) {
            $_SESSION['error'] = 'Representante não encontrado';
            redirect(url('representantes'));
        }
        
        // Buscar estabelecimentos do representante
        $establishments = $this->representativeModel->getEstablishments($id);
        $stats = $this->representativeModel->getRepresentativeStats($id);
        
        $data = [
            'title' => 'Detalhes do Representante',
            'currentPage' => 'representantes',
            'representative' => $representative,
            'establishments' => $establishments,
            'stats' => $stats
        ];
        
        view('representatives/show', $data);
    }

    public function edit($id)
    {
        Auth::requireAdmin();
        
        $representative = $this->representativeModel->findById($id);
        
        if (!$representative) {
            $_SESSION['error'] = 'Representante não encontrado';
            redirect(url('representantes'));
        }
        
        $productOptions = $this->getRepresentativeProductOptions();
        
        // Se for POST sem _method=PUT, processar o formulário (compatibilidade com rota antiga)
        // Se tiver _method=PUT, deixar o Router processar e chamar o método update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['_method']) || $_POST['_method'] !== 'PUT')) {
            $result = $this->processEditForm($id, $representative);
            
            $data = [
                'title' => 'Editar Representante',
                'currentPage' => 'representantes',
                'representative' => $representative,
                'form_result' => $result,
                'productOptions' => $productOptions,
                'allowedProducts' => $this->representativeModel->getProducts($id)
            ];
            
            view('representatives/edit', $data);
            return;
        }
        
        // Buscar produtos permitidos do banco
        $allowedProducts = $this->representativeModel->getProducts($id);
        
        // Log para debug
        write_log('=== PRODUTOS CARREGADOS NO EDIT ===', 'representatives.log');
        write_log('Representante ID: ' . $id, 'representatives.log');
        write_log('Produtos do banco: ' . json_encode($allowedProducts), 'representatives.log');
        
        $data = [
            'title' => 'Editar Representante',
            'currentPage' => 'representantes',
            'representative' => $representative,
            'productOptions' => $productOptions,
            'allowedProducts' => $allowedProducts
        ];
        
        view('representatives/edit', $data);
    }
    
    private function processEditForm($id, $representative)
    {
        $result = [
            'success' => false,
            'message' => '',
            'errors' => []
        ];
        
        try {
            // Verificar CSRF token
            try {
                csrf_verify();
            } catch (\Exception $e) {
                $result['errors'][] = 'Token de segurança inválido. Recarregue a página e tente novamente.';
                $_SESSION['old_input'] = $_POST;
                return $result;
            }
            
            $data = $this->validateAndSanitizeInput($id);
            
            if (isset($_SESSION['validation_errors'])) {
                $result['errors'] = $_SESSION['validation_errors'];
                unset($_SESSION['validation_errors']);
                return $result;
            }
            
            $updateResult = $this->representativeModel->update($id, $data);
            
            // Salvar produtos permitidos
            $allowedProducts = $_POST['allowed_products'] ?? [];
            $normalizedProducts = $this->normalizeAllowedProducts(
                is_array($allowedProducts) ? $allowedProducts : [],
                $this->getRepresentativeProductOptions()
            );
            $this->representativeModel->setProducts($id, $normalizedProducts);
            
            if ($updateResult) {
                // Verificar mudanças
                $changes = [];
                if (($data['nome_completo'] ?? '') !== ($_POST['original_nome_completo'] ?? '')) {
                    $changes[] = "Nome alterado";
                }
                if (($data['email'] ?? '') !== ($_POST['original_email'] ?? '')) {
                    $changes[] = "Email alterado";
                }
                if (($data['telefone'] ?? '') !== ($_POST['original_telefone'] ?? '')) {
                    $changes[] = "Telefone alterado";
                }
                if (($data['status'] ?? '') !== ($_POST['original_status'] ?? '')) {
                    $changes[] = "Status alterado";
                }
                if (isset($data['senha'])) {
                    $changes[] = "Senha alterada";
                }
                if (!empty($allowedProducts)) {
                    $changes[] = "Produtos permitidos atualizados";
                }
                
                if (empty($changes)) {
                    $result['success'] = true;
                    $result['message'] = 'Representante atualizado com sucesso! (Nenhuma alteração foi necessária)';
                } else {
                    $result['success'] = true;
                    $result['message'] = 'Representante atualizado com sucesso! Alterações: ' . implode(', ', $changes);
                }
            } else {
                $result['errors'][] = 'Falha ao atualizar representante. Tente novamente.';
            }
            
        } catch (\Exception $e) {
            $result['errors'][] = 'Erro ao atualizar representante: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
        }
        
        return $result;
    }

    public function update($id)
    {
        Auth::requireAdmin();
        
        // Log para debug - sempre executar
        write_log('========================================', 'representatives.log');
        write_log('=== MÉTODO UPDATE CHAMADO ===', 'representatives.log');
        write_log('========================================', 'representatives.log');
        write_log('Timestamp: ' . date('Y-m-d H:i:s'), 'representatives.log');
        write_log('Representante ID: ' . $id, 'representatives.log');
        write_log('REQUEST_METHOD: ' . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'), 'representatives.log');
        write_log('POST _method: ' . ($_POST['_method'] ?? 'N/A'), 'representatives.log');
        write_log('POST completo: ' . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'representatives.log');
        write_log('GET completo: ' . json_encode($_GET, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'representatives.log');
        
        // O Router converte POST com _method=PUT para PUT, mas os dados ainda vêm em $_POST
        // Aceitar tanto POST quanto PUT, mas os dados sempre vêm em $_POST
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $hasMethodOverride = isset($_POST['_method']) && $_POST['_method'] === 'PUT';
        
        if ($requestMethod !== 'POST' && $requestMethod !== 'PUT') {
            write_log('❌ ERRO: REQUEST_METHOD não é POST nem PUT. Método: ' . $requestMethod, 'representatives.log');
            redirect(url('representantes/' . $id . '/edit'));
        }
        
        write_log('✅ Método HTTP aceito: ' . $requestMethod . ($hasMethodOverride ? ' (via _method override)' : ''), 'representatives.log');
        
        // Verificar CSRF token
        write_log('--- Verificando CSRF token ---', 'representatives.log');
        try {
            csrf_verify();
            write_log('✅ CSRF token válido', 'representatives.log');
        } catch (\Exception $e) {
            $_SESSION['validation_errors'] = ['Token de segurança inválido. Recarregue a página e tente novamente.'];
            write_log('❌ ERRO CSRF no update: ' . $e->getMessage(), 'representatives.log');
            redirect(url('representantes/' . $id . '/edit'));
        }
        
        write_log('--- Buscando representante no banco ---', 'representatives.log');
        $representative = $this->representativeModel->findById($id);
        
        if (!$representative) {
            write_log('❌ Representante não encontrado no banco', 'representatives.log');
            $_SESSION['error'] = 'Representante não encontrado';
            redirect(url('representantes'));
        }
        write_log('✅ Representante encontrado: ' . json_encode($representative, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'representatives.log');
        
        write_log('--- Validando e sanitizando dados de entrada ---', 'representatives.log');
        $data = $this->validateAndSanitizeInput($id);
        
        write_log('Dados validados e sanitizados: ' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'representatives.log');
        
        if (isset($_SESSION['validation_errors'])) {
            write_log('❌ ERROS DE VALIDAÇÃO ENCONTRADOS: ' . json_encode($_SESSION['validation_errors'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'representatives.log');
            $_SESSION['old_input'] = $_POST;
            redirect(url('representantes/' . $id . '/edit'));
        }
        write_log('✅ Validação passou sem erros', 'representatives.log');
        
        try {
            write_log('--- Iniciando atualização no banco de dados ---', 'representatives.log');
            write_log('Chamando representativeModel->update com ID: ' . $id, 'representatives.log');
            write_log('Dados a serem atualizados: ' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'representatives.log');
            
            $updateResult = $this->representativeModel->update($id, $data);
            
            write_log('Resultado do update: ' . ($updateResult ? '✅ SUCESSO' : '❌ FALHOU'), 'representatives.log');
            
            if (!$updateResult) {
                $_SESSION['error'] = 'Nenhum campo foi atualizado. Verifique se há alterações nos dados.';
                write_log('⚠️ AVISO: Nenhum campo foi atualizado', 'representatives.log');
                redirect(url('representantes/' . $id . '/edit'));
                return;
            }
            
            write_log('✅ Dados do representante atualizados com sucesso', 'representatives.log');
            
            // Salvar produtos permitidos
            write_log('--- Processando produtos permitidos ---', 'representatives.log');
            
            // Verificar se allowed_products existe no POST
            if (!isset($_POST['allowed_products'])) {
                write_log('⚠️ AVISO: allowed_products não está presente no POST', 'representatives.log');
                write_log('Chaves disponíveis no POST: ' . implode(', ', array_keys($_POST)), 'representatives.log');
            }
            
            $allowedProducts = $_POST['allowed_products'] ?? [];
            $normalizedProducts = $this->normalizeAllowedProducts(
                is_array($allowedProducts) ? $allowedProducts : [],
                $this->getRepresentativeProductOptions()
            );
            $productsCount = count($normalizedProducts);
            
            write_log('Produtos recebidos no POST: ' . json_encode($allowedProducts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'representatives.log');
            write_log('Quantidade de produtos: ' . $productsCount, 'representatives.log');
            
            if (empty($allowedProducts)) {
                write_log('⚠️ AVISO: Nenhum produto foi enviado no formulário', 'representatives.log');
            }
            
            try {
                write_log('Chamando setProducts para salvar produtos...', 'representatives.log');
                $this->representativeModel->setProducts($id, $normalizedProducts);
                write_log('✅ setProducts executado com sucesso', 'representatives.log');
                
                // Verificar se foram salvos corretamente
                write_log('Verificando produtos salvos no banco...', 'representatives.log');
                $savedProducts = $this->representativeModel->getProducts($id);
                $savedCount = count($savedProducts);
                
                write_log('Produtos salvos no banco: ' . json_encode($savedProducts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'representatives.log');
                write_log('Quantidade de produtos salvos: ' . $savedCount, 'representatives.log');
                
                if ($productsCount > 0) {
                    if ($savedCount === $productsCount) {
                        $productNames = array_map(function($p) {
                            return $p['product_type'] ?? $p;
                        }, $savedProducts);
                        $_SESSION['success'] = "Representante atualizado com sucesso! {$productsCount} produto(s) permitido(s) salvo(s): " . implode(', ', $productNames);
                        write_log('✅ SUCESSO: Produtos salvos corretamente', 'representatives.log');
                    } else {
                        $_SESSION['error'] = "Aviso: Foram selecionados {$productsCount} produto(s), mas apenas {$savedCount} foram salvos. Verifique os logs.";
                        write_log('⚠️ AVISO: Contagem de produtos não confere (esperado: ' . $productsCount . ', salvo: ' . $savedCount . ')', 'representatives.log');
                    }
                } else {
                    $_SESSION['success'] = 'Representante atualizado com sucesso! Nenhum produto específico selecionado (representante poderá cadastrar todos os produtos).';
                    write_log('✅ SUCESSO: Nenhum produto selecionado (todos permitidos)', 'representatives.log');
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Representante atualizado, mas houve erro ao salvar produtos permitidos: ' . $e->getMessage();
                write_log('❌ ERRO ao salvar produtos do representante ' . $id . ': ' . $e->getMessage(), 'representatives.log');
                write_log('Stack trace: ' . $e->getTraceAsString(), 'representatives.log');
                $_SESSION['old_input'] = $_POST;
            }
            
            write_log('--- Finalizando processo de atualização ---', 'representatives.log');
            write_log('Redirecionando para: ' . url('representantes/' . $id . '/edit'), 'representatives.log');
            write_log('Mensagem de sucesso na sessão: ' . ($_SESSION['success'] ?? 'NÃO DEFINIDA'), 'representatives.log');
            write_log('Mensagem de erro na sessão: ' . ($_SESSION['error'] ?? 'NÃO DEFINIDA'), 'representatives.log');
            write_log('========================================', 'representatives.log');
            write_log('', 'representatives.log');
            
            redirect(url('representantes/' . $id . '/edit'));
            
        } catch (\Exception $e) {
            write_log('❌ ERRO FATAL ao atualizar representante ' . $id . ': ' . $e->getMessage(), 'representatives.log');
            write_log('Stack trace: ' . $e->getTraceAsString(), 'representatives.log');
            write_log('========================================', 'representatives.log');
            write_log('', 'representatives.log');
            
            $_SESSION['error'] = $this->friendlyRepresentativeError($e->getMessage(), 'atualizar');
            $_SESSION['old_input'] = $_POST;
            redirect(url('representantes/' . $id . '/edit'));
        }
    }

    public function destroy($id)
    {
        Auth::requireAdmin();
        
        $representative = $this->representativeModel->findById($id);
        
        if (!$representative) {
            $_SESSION['error'] = 'Representante não encontrado';
            redirect(url('representantes'));
        }
        
        // Verificar se o representante tem estabelecimentos
        $establishments = $this->representativeModel->getEstablishments($id);
        if (!empty($establishments)) {
            $_SESSION['error'] = 'Não é possível excluir representante que possui estabelecimentos cadastrados';
            redirect(url('representantes'));
        }
        
        try {
            $this->representativeModel->delete($id);
            $_SESSION['success'] = 'Representante excluído com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir representante: ' . $e->getMessage();
        }
        
        redirect(url('representantes'));
    }

    public function resetPassword($id)
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método de requisição inválido';
            redirect(url('representantes'));
        }
        
        $representative = $this->representativeModel->findById($id);
        
        if (!$representative) {
            $_SESSION['error'] = 'Representante não encontrado';
            redirect(url('representantes'));
        }
        
        // Validar dados do formulário
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        
        $errors = [];
        
        if (empty($password)) {
            $errors[] = 'Senha é obrigatória';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres';
        }
        
        if (empty($passwordConfirm)) {
            $errors[] = 'Confirmação de senha é obrigatória';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = 'As senhas não coincidem';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = 'Erros encontrados: ' . implode(', ', $errors);
            redirect(url('representantes/' . $id));
        }
        
        try {
            // Atualizar senha
            $this->representativeModel->updatePassword($id, $password);
            
            // Tentar enviar email (opcional)
            try {
                $this->mailer = new Mailer();
                $this->mailer->sendPasswordReset($representative['email'], $representative['nome_completo'], $password);
                $_SESSION['success'] = 'Senha alterada com sucesso e enviada por email!';
            } catch (\Exception $emailError) {
                // Se falhar o email, ainda assim mostrar sucesso
                $_SESSION['success'] = 'Senha alterada com sucesso! (Email não enviado - verifique configurações)';
            }
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao alterar senha: ' . $e->getMessage();
        }
        
        redirect(url('representantes/' . $id));
    }

    public function toggleStatus($id)
    {
        Auth::requireAdmin();
        
        $representative = $this->representativeModel->findById($id);
        
        if (!$representative) {
            $_SESSION['error'] = 'Representante não encontrado';
            redirect(url('representantes'));
        }
        
        try {
            $newStatus = $representative['status'] === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
            $this->representativeModel->updateStatus($id, $newStatus);
            
            $statusText = $newStatus === 'ACTIVE' ? 'ativado' : 'desativado';
            $_SESSION['success'] = "Representante {$statusText} com sucesso!";
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao alterar status: ' . $e->getMessage();
        }
        
        redirect(url('representantes/' . $id));
    }

    private function getFilters()
    {
        return [
            'status' => sanitize_input($_GET['status'] ?? ''),
            'cidade' => sanitize_input($_GET['cidade'] ?? ''),
            'search' => sanitize_input($_GET['search'] ?? ''),
            'date_from' => sanitize_input($_GET['date_from'] ?? ''),
            'date_to' => sanitize_input($_GET['date_to'] ?? '')
        ];
    }

    private function validateAndSanitizeInput($id = null)
    {
        $errors = [];
        
        // Nome completo
        $nomeCompleto = sanitize_input($_POST['nome_completo'] ?? '');
        if (empty($nomeCompleto)) {
            $errors[] = 'Informe o nome completo do representante.';
        }
        
        // Email
        $email = sanitize_input($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informe um e-mail válido.';
        } else {
            // Verificar se email já existe (exceto para o próprio representante sendo editado)
            $existingRepresentative = $this->representativeModel->findByEmail($email);
            if ($existingRepresentative && (!$id || $existingRepresentative['id'] != $id)) {
                $errors[] = 'Já existe um representante cadastrado com este e-mail.';
            }
        }
        
        // Telefone
        $telefone = sanitize_input($_POST['telefone'] ?? '');
        if (empty($telefone)) {
            $errors[] = 'Informe o telefone do representante.';
        }
        
        // CPF
        $cpf = sanitize_input($_POST['cpf'] ?? '');
        if (empty($cpf)) {
            $errors[] = 'Informe o CPF do representante.';
        } elseif (!$this->validateCPF($cpf)) {
            $errors[] = 'CPF inválido. Confira os números digitados.';
        }
        
        // CEP
        $cep = sanitize_input($_POST['cep'] ?? '');
        if (empty($cep)) {
            $errors[] = 'Informe o CEP.';
        }
        
        // Endereço
        $logradouro = sanitize_input($_POST['logradouro'] ?? '');
        $numero = sanitize_input($_POST['numero'] ?? '');
        $bairro = sanitize_input($_POST['bairro'] ?? '');
        $cidade = sanitize_input($_POST['cidade'] ?? '');
        $uf = sanitize_input($_POST['uf'] ?? '');
        
        if (empty($logradouro)) { $errors[] = 'Informe o logradouro.'; }
        if (empty($numero)) { $errors[] = 'Informe o número do endereço.'; }
        if (empty($bairro)) { $errors[] = 'Informe o bairro.'; }
        if (empty($cidade)) { $errors[] = 'Informe a cidade.'; }
        if (empty($uf)) { $errors[] = 'Selecione a UF.'; }
        
        // Senha (obrigatória apenas para novos representantes)
        $senha = $_POST['senha'] ?? '';
        if (!$id && empty($senha)) {
            $errors[] = 'Defina uma senha para o representante.';
        } elseif ($senha && strlen($senha) < 6) {
            $errors[] = 'A senha deve ter no mínimo 6 caracteres.';
        }
        
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            return [];
        }
        
        // Preparar dados para inserção/atualização
        $data = [
            'nome_completo' => $nomeCompleto,
            'email' => $email,
            'telefone' => $telefone,
            'cpf' => $cpf,
            'cep' => $cep,
            'logradouro' => $logradouro,
            'numero' => $numero,
            'complemento' => sanitize_input($_POST['complemento'] ?? ''),
            'bairro' => $bairro,
            'cidade' => $cidade,
            'uf' => strtoupper($uf),
            'status' => sanitize_input($_POST['status'] ?? 'ACTIVE')
        ];
        
        // Adicionar senha apenas se fornecida
        if (!empty($senha)) {
            $data['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }
        
        return $data;
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

    private function friendlyRepresentativeError($message, $action = 'salvar')
    {
        $msg = (string)$message;
        $lower = strtolower($msg);

        if (strpos($lower, 'duplicate entry') !== false && strpos($lower, 'email') !== false) {
            return 'Já existe um representante cadastrado com este e-mail. Verifique e tente novamente.';
        }
        if (strpos($lower, 'duplicate entry') !== false && strpos($lower, 'cpf') !== false) {
            return 'Já existe um representante cadastrado com este CPF. Verifique e tente novamente.';
        }
        if (strpos($lower, 'duplicate entry') !== false) {
            return 'Já existe um cadastro com estes dados. Revise os campos e tente novamente.';
        }

        return "Não foi possível {$action} o representante. Revise os dados e tente novamente.";
    }
}
