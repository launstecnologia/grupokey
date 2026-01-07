<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\EmailCampaign;
use App\Models\EmailQueue;
use App\Models\Establishment;
use App\Models\Representative;
use App\Models\User;
use App\Core\Mailer;
use App\Core\FileUpload;

class EmailMarketingController
{
    private $campaignModel;
    private $queueModel;
    private $establishmentModel;
    private $representativeModel;
    private $userModel;
    private $fileUpload;
    
    public function __construct()
    {
        $this->campaignModel = new EmailCampaign();
        $this->queueModel = new EmailQueue();
        $this->establishmentModel = new Establishment();
        $this->representativeModel = new Representative();
        $this->userModel = new User();
        $this->fileUpload = new FileUpload();
    }
    
    /**
     * Lista todas as campanhas
     */
    public function index()
    {
        Auth::requireAdmin();
        
        $filters = [
            'search' => $_GET['search'] ?? null,
            'status' => $_GET['status'] ?? null
        ];
        
        $campaigns = $this->campaignModel->getAll($filters);
        
        $data = [
            'title' => 'E-mail Marketing',
            'currentPage' => 'email-marketing',
            'campaigns' => $campaigns,
            'filters' => $filters
        ];
        
        view('email-marketing/index', $data);
    }
    
    /**
     * Exibe formulário de criação de campanha
     */
    public function create()
    {
        Auth::requireAdmin();
        
        $data = [
            'title' => 'Nova Campanha de E-mail',
            'currentPage' => 'email-marketing'
        ];
        
        view('email-marketing/create', $data);
    }
    
    /**
     * Salva nova campanha
     */
    public function store()
    {
        Auth::requireAdmin();
        
        $errors = [];
        
        // Validações
        if (empty($_POST['name']) || trim($_POST['name']) === '') {
            $errors[] = 'Nome da campanha é obrigatório';
        }
        
        if (empty($_POST['subject']) || trim($_POST['subject']) === '') {
            $errors[] = 'Assunto é obrigatório';
        }
        
        // Verificar se o body está vazio (pode estar vazio se o TinyMCE não sincronizou)
        $body = $_POST['body'] ?? '';
        // Remover tags HTML vazias e espaços
        $bodyClean = trim(strip_tags($body));
        if (empty($body) || $bodyClean === '') {
            $errors[] = 'Conteúdo do e-mail é obrigatório. Certifique-se de preencher o campo de conteúdo.';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            redirect(url('email-marketing/create'));
        }
        
        // Preparar dados
        $user = Auth::user();
        $representative = Auth::representative();
        
        $campaignData = [
            'name' => sanitize_input($_POST['name']),
            'subject' => sanitize_input($_POST['subject']),
            'body' => $_POST['body'], // HTML - não sanitizar
            'signature' => $_POST['signature'] ?? null,
            'status' => 'DRAFT',
            'scheduled_at' => !empty($_POST['scheduled_at']) ? $_POST['scheduled_at'] : null,
            'created_by_user_id' => $user ? $user['id'] : null,
            'created_by_representative_id' => $representative ? $representative['id'] : null
        ];
        
        try {
            $campaignId = $this->campaignModel->create($campaignData);
            
            // Processar anexos se houver
            if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                $this->handleAttachments($campaignId);
            }
            
            $_SESSION['success'] = 'Campanha criada com sucesso!';
            redirect(url('email-marketing/' . $campaignId . '/recipients'));
            
        } catch (\Exception $e) {
            write_log('Erro ao criar campanha: ' . $e->getMessage(), 'email-marketing.log');
            $_SESSION['error'] = 'Erro ao criar campanha: ' . $e->getMessage();
            redirect(url('email-marketing/create'));
        }
    }
    
    /**
     * Exibe detalhes da campanha
     */
    public function show($id)
    {
        Auth::requireAdmin();
        
        $campaign = $this->campaignModel->findById($id);
        
        if (!$campaign) {
            $_SESSION['error'] = 'Campanha não encontrada';
            redirect(url('email-marketing'));
        }
        
        $attachments = $this->campaignModel->getAttachments($id);
        write_log('Campanha ID: ' . $id . ' - Total de anexos encontrados: ' . count($attachments), 'email-marketing.log');
        if (!empty($attachments)) {
            write_log('Anexos: ' . json_encode($attachments), 'email-marketing.log');
        }
        
        $recipients = $this->campaignModel->getRecipients($id);
        $queueStats = $this->queueModel->getStats($id);
        
        $data = [
            'title' => 'Detalhes da Campanha',
            'currentPage' => 'email-marketing',
            'campaign' => $campaign,
            'attachments' => $attachments,
            'recipients' => $recipients,
            'queueStats' => $queueStats
        ];
        
        view('email-marketing/show', $data);
    }
    
    /**
     * Exibe formulário de edição
     */
    public function edit($id)
    {
        Auth::requireAdmin();
        
        $campaign = $this->campaignModel->findById($id);
        
        if (!$campaign) {
            $_SESSION['error'] = 'Campanha não encontrada';
            redirect(url('email-marketing'));
        }
        
        if ($campaign['status'] !== 'DRAFT' && $campaign['status'] !== 'PAUSED') {
            $_SESSION['error'] = 'Apenas campanhas em rascunho ou pausadas podem ser editadas';
            redirect(url('email-marketing/' . $id));
        }
        
        $attachments = $this->campaignModel->getAttachments($id);
        
        $data = [
            'title' => 'Editar Campanha',
            'currentPage' => 'email-marketing',
            'campaign' => $campaign,
            'attachments' => $attachments
        ];
        
        view('email-marketing/edit', $data);
    }
    
    /**
     * Atualiza campanha
     */
    public function update($id)
    {
        Auth::requireAdmin();
        
        $campaign = $this->campaignModel->findById($id);
        
        if (!$campaign) {
            $_SESSION['error'] = 'Campanha não encontrada';
            redirect(url('email-marketing'));
        }
        
        if ($campaign['status'] !== 'DRAFT' && $campaign['status'] !== 'PAUSED') {
            $_SESSION['error'] = 'Apenas campanhas em rascunho ou pausadas podem ser editadas';
            redirect(url('email-marketing/' . $id));
        }
        
        $errors = [];
        
        if (empty($_POST['name'])) {
            $errors[] = 'Nome da campanha é obrigatório';
        }
        
        if (empty($_POST['subject'])) {
            $errors[] = 'Assunto é obrigatório';
        }
        
        if (empty($_POST['body'])) {
            $errors[] = 'Conteúdo do e-mail é obrigatório';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            redirect(url('email-marketing/' . $id . '/edit'));
        }
        
        $campaignData = [
            'name' => sanitize_input($_POST['name']),
            'subject' => sanitize_input($_POST['subject']),
            'body' => $_POST['body'],
            'signature' => $_POST['signature'] ?? null,
            'scheduled_at' => !empty($_POST['scheduled_at']) ? $_POST['scheduled_at'] : null
        ];
        
        try {
            $this->campaignModel->update($id, $campaignData);
            
            // Processar novos anexos se houver
            if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                $this->handleAttachments($id);
            }
            
            $_SESSION['success'] = 'Campanha atualizada com sucesso!';
            redirect(url('email-marketing/' . $id));
            
        } catch (\Exception $e) {
            write_log('Erro ao atualizar campanha: ' . $e->getMessage(), 'email-marketing.log');
            $_SESSION['error'] = 'Erro ao atualizar campanha: ' . $e->getMessage();
            redirect(url('email-marketing/' . $id . '/edit'));
        }
    }
    
    /**
     * Exibe página de seleção de destinatários
     */
    public function recipients($id)
    {
        Auth::requireAdmin();
        
        $campaign = $this->campaignModel->findById($id);
        
        if (!$campaign) {
            $_SESSION['error'] = 'Campanha não encontrada';
            redirect(url('email-marketing'));
        }
        
        $recipients = $this->campaignModel->getRecipients($id);
        
        // Buscar opções de destinatários
        $establishments = $this->establishmentModel->getAll(['status' => 'APPROVED']);
        $representatives = $this->representativeModel->getAll(['status' => 'ACTIVE']);
        $users = $this->userModel->getAll(['status' => 'ACTIVE']);
        
        // Extrair estados e cidades únicos dos estabelecimentos
        $states = [];
        $cities = [];
        foreach ($establishments as $est) {
            if (!empty($est['uf'])) {
                $states[$est['uf']] = $est['uf'];
            }
            if (!empty($est['cidade'])) {
                $cities[$est['cidade']] = $est['cidade'];
            }
        }
        sort($states);
        sort($cities);
        
        $data = [
            'title' => 'Selecionar Destinatários',
            'currentPage' => 'email-marketing',
            'campaign' => $campaign,
            'recipients' => $recipients,
            'establishments' => $establishments,
            'representatives' => $representatives,
            'users' => $users,
            'states' => $states,
            'cities' => $cities
        ];
        
        view('email-marketing/recipients', $data);
    }
    
    /**
     * Adiciona destinatários à campanha
     */
    public function addRecipients($id)
    {
        Auth::requireAdmin();
        
        $campaign = $this->campaignModel->findById($id);
        
        if (!$campaign) {
            $_SESSION['error'] = 'Campanha não encontrada';
            redirect(url('email-marketing'));
        }
        
        if ($campaign['status'] !== 'DRAFT' && $campaign['status'] !== 'PAUSED') {
            $_SESSION['error'] = 'Não é possível adicionar destinatários a uma campanha em envio';
            redirect(url('email-marketing/' . $id . '/recipients'));
        }
        
        $recipients = [];
        
        // Estabelecimentos
        if (!empty($_POST['establishment_ids'])) {
            $establishmentIds = is_array($_POST['establishment_ids']) ? $_POST['establishment_ids'] : explode(',', $_POST['establishment_ids']);
            foreach ($establishmentIds as $estId) {
                $establishment = $this->establishmentModel->findById($estId);
                if ($establishment && !empty($establishment['email'])) {
                    $recipients[] = [
                        'type' => 'ESTABLISHMENT',
                        'id' => $estId,
                        'email' => $establishment['email'],
                        'name' => $establishment['nome_fantasia'] ?? $establishment['nome_completo']
                    ];
                }
            }
        }
        
        // Representantes
        if (!empty($_POST['representative_ids'])) {
            $representativeIds = is_array($_POST['representative_ids']) ? $_POST['representative_ids'] : explode(',', $_POST['representative_ids']);
            foreach ($representativeIds as $repId) {
                $representative = $this->representativeModel->findById($repId);
                if ($representative && !empty($representative['email'])) {
                    $recipients[] = [
                        'type' => 'REPRESENTATIVE',
                        'id' => $repId,
                        'email' => $representative['email'],
                        'name' => $representative['nome_completo']
                    ];
                }
            }
        }
        
        // Usuários
        if (!empty($_POST['user_ids'])) {
            $userIds = is_array($_POST['user_ids']) ? $_POST['user_ids'] : explode(',', $_POST['user_ids']);
            foreach ($userIds as $userId) {
                $user = $this->userModel->findById($userId);
                if ($user && !empty($user['email'])) {
                    $recipients[] = [
                        'type' => 'USER',
                        'id' => $userId,
                        'email' => $user['email'],
                        'name' => $user['name']
                    ];
                }
            }
        }
        
        // E-mails customizados
        if (!empty($_POST['custom_emails'])) {
            $customEmails = explode("\n", $_POST['custom_emails']);
            foreach ($customEmails as $emailLine) {
                $emailLine = trim($emailLine);
                if (!empty($emailLine)) {
                    // Formato: email ou "Nome <email>"
                    if (preg_match('/^(.+?)\s*<(.+?)>$/', $emailLine, $matches)) {
                        $name = trim($matches[1]);
                        $email = trim($matches[2]);
                    } else {
                        $email = trim($emailLine);
                        $name = null;
                    }
                    
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $recipients[] = [
                            'type' => 'CUSTOM',
                            'id' => null,
                            'email' => $email,
                            'name' => $name
                        ];
                    }
                }
            }
        }
        
        if (empty($recipients)) {
            $_SESSION['error'] = 'Nenhum destinatário válido foi selecionado';
            redirect(url('email-marketing/' . $id . '/recipients'));
        }
        
        try {
            $this->campaignModel->addRecipientsBatch($id, $recipients);
            $_SESSION['success'] = count($recipients) . ' destinatário(s) adicionado(s) com sucesso!';
            redirect(url('email-marketing/' . $id . '/recipients'));
            
        } catch (\Exception $e) {
            write_log('Erro ao adicionar destinatários: ' . $e->getMessage(), 'email-marketing.log');
            $_SESSION['error'] = 'Erro ao adicionar destinatários: ' . $e->getMessage();
            redirect(url('email-marketing/' . $id . '/recipients'));
        }
    }
    
    /**
     * Remove destinatário
     */
    public function removeRecipient($campaignId, $recipientId)
    {
        Auth::requireAdmin();
        
        $sql = "DELETE FROM email_campaign_recipients WHERE id = ? AND campaign_id = ?";
        $db = \App\Core\Database::getInstance();
        $db->query($sql, [$recipientId, $campaignId]);
        
        $_SESSION['success'] = 'Destinatário removido com sucesso!';
        redirect(url('email-marketing/' . $campaignId . '/recipients'));
    }
    
    /**
     * Agenda e inicia o envio da campanha
     */
    public function start($id)
    {
        write_log('=== TENTATIVA DE INICIAR CAMPANHA ===', 'email-marketing.log');
        write_log('Campanha ID: ' . $id, 'email-marketing.log');
        write_log('Usuário: ' . (Auth::user()['name'] ?? 'N/A'), 'email-marketing.log');
        
        Auth::requireAdmin();
        
        $campaign = $this->campaignModel->findById($id);
        
        if (!$campaign) {
            $errorMsg = 'Campanha não encontrada';
            write_log('ERRO: ' . $errorMsg, 'email-marketing.log');
            $_SESSION['error'] = $errorMsg;
            redirect(url('email-marketing'));
        }
        
        write_log('Campanha encontrada: ' . json_encode([
            'id' => $campaign['id'],
            'name' => $campaign['name'] ?? '',
            'status' => $campaign['status'] ?? '',
            'scheduled_at' => $campaign['scheduled_at'] ?? null
        ], JSON_UNESCAPED_UNICODE), 'email-marketing.log');
        
        if ($campaign['status'] !== 'DRAFT' && $campaign['status'] !== 'PAUSED') {
            $errorMsg = 'Apenas campanhas em rascunho ou pausadas podem ser iniciadas';
            write_log('ERRO: ' . $errorMsg, 'email-marketing.log');
            write_log('Status atual: ' . $campaign['status'], 'email-marketing.log');
            $_SESSION['error'] = $errorMsg;
            redirect(url('email-marketing/' . $id));
        }
        
        // Verificar se há destinatários
        $recipients = $this->campaignModel->getRecipients($id);
        write_log('Total de destinatários encontrados: ' . count($recipients), 'email-marketing.log');
        
        if (empty($recipients)) {
            $errorMsg = 'Adicione destinatários antes de iniciar a campanha';
            write_log('ERRO: ' . $errorMsg, 'email-marketing.log');
            $_SESSION['error'] = $errorMsg;
            redirect(url('email-marketing/' . $id . '/recipients'));
        }
        
        try {
            // Atualizar status da campanha
            $scheduledAt = $campaign['scheduled_at'] ?? date('Y-m-d H:i:s');
            write_log('Agendando campanha para: ' . $scheduledAt, 'email-marketing.log');
            
            $this->campaignModel->update($id, [
                'name' => $campaign['name'],
                'subject' => $campaign['subject'],
                'body' => $campaign['body'],
                'signature' => $campaign['signature'],
                'status' => 'SCHEDULED',
                'scheduled_at' => $scheduledAt
            ]);
            
            write_log('Status da campanha atualizado para: SCHEDULED', 'email-marketing.log');
            
            // Adicionar todos os destinatários pendentes à fila
            $pendingRecipients = $this->campaignModel->getRecipientsByStatus($id, 'PENDING');
            $recipientIds = array_column($pendingRecipients, 'id');
            
            write_log('Destinatários pendentes: ' . count($pendingRecipients), 'email-marketing.log');
            write_log('IDs dos destinatários: ' . json_encode($recipientIds), 'email-marketing.log');
            
            if (!empty($recipientIds)) {
                $addedCount = $this->queueModel->addToQueue($id, $recipientIds, $scheduledAt);
                write_log('SUCESSO: ' . $addedCount . ' destinatários adicionados à fila de envio', 'email-marketing.log');
                write_log('IMPORTANTE: Para enviar os emails, é necessário executar o processamento da fila', 'email-marketing.log');
                write_log('URL para processar manualmente: ' . url('email-marketing/process-queue'), 'email-marketing.log');
                write_log('Ou configure um cron job para executar automaticamente', 'email-marketing.log');
            } else {
                write_log('AVISO: Nenhum destinatário pendente para adicionar à fila', 'email-marketing.log');
            }
            
            $_SESSION['success'] = 'Campanha agendada com sucesso! O envio será processado automaticamente.';
            redirect(url('email-marketing/' . $id));
            
        } catch (\Exception $e) {
            $errorMsg = 'Erro ao iniciar campanha: ' . $e->getMessage();
            write_log('ERRO EXCEÇÃO: ' . $errorMsg, 'email-marketing.log');
            write_log('Stack trace: ' . $e->getTraceAsString(), 'email-marketing.log');
            $_SESSION['error'] = $errorMsg;
            redirect(url('email-marketing/' . $id));
        }
    }
    
    /**
     * Processa a fila de e-mails (chamado via cron ou manualmente)
     */
    public function processQueue()
    {
        write_log('=== PROCESSAMENTO DA FILA DE EMAILS INICIADO ===', 'email-queue.log');
        write_log('Data/Hora: ' . date('Y-m-d H:i:s'), 'email-queue.log');
        write_log('Método: ' . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'), 'email-queue.log');
        write_log('User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A'), 'email-queue.log');
        
        // Verificar se é chamada via cron (sem User-Agent de navegador) ou manual
        $isCron = empty($_SERVER['HTTP_USER_AGENT']) || 
                  stripos($_SERVER['HTTP_USER_AGENT'], 'curl') !== false || 
                  stripos($_SERVER['HTTP_USER_AGENT'], 'wget') !== false ||
                  stripos($_SERVER['HTTP_USER_AGENT'], 'cron') !== false;
        
        write_log('Via Cron: ' . ($isCron ? 'Sim' : 'Não'), 'email-queue.log');
        
        // Se não for cron, exigir autenticação
        if (!$isCron) {
            Auth::requireAdmin();
            write_log('Autenticado como: ' . (Auth::user()['name'] ?? 'N/A'), 'email-queue.log');
        }
        
        // Configurações de rate limiting
        $batchSize = (int)($_GET['batch_size'] ?? 10); // E-mails por lote
        $delaySeconds = (int)($_GET['delay'] ?? 2); // Delay entre envios (segundos)
        
        write_log('Configurações: batch_size=' . $batchSize . ', delay=' . $delaySeconds . 's', 'email-queue.log');
        
        // Verificar itens na fila antes de buscar
        $db = \App\Core\Database::getInstance();
        $statsQuery = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'PENDING' AND scheduled_at <= NOW() THEN 1 ELSE 0 END) as ready,
            SUM(CASE WHEN status = 'PENDING' AND scheduled_at > NOW() THEN 1 ELSE 0 END) as scheduled_future,
            SUM(CASE WHEN status = 'SENT' THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as failed
            FROM email_queue";
        $queueStats = $db->fetch($statsQuery);
        
        write_log('Estatísticas da fila: Total=' . ($queueStats['total'] ?? 0) . 
                 ', Prontos para envio=' . ($queueStats['ready'] ?? 0) . 
                 ', Agendados para futuro=' . ($queueStats['scheduled_future'] ?? 0) . 
                 ', Enviados=' . ($queueStats['sent'] ?? 0) . 
                 ', Falhas=' . ($queueStats['failed'] ?? 0), 'email-queue.log');
        
        if (($queueStats['scheduled_future'] ?? 0) > 0) {
            // Buscar itens agendados para o futuro para mostrar no log
            $futureItems = $db->fetchAll(
                "SELECT id, campaign_id, scheduled_at, status 
                 FROM email_queue 
                 WHERE status = 'PENDING' AND scheduled_at > NOW() 
                 ORDER BY scheduled_at ASC 
                 LIMIT 5"
            );
            write_log('Itens agendados para o futuro (próximos 5):', 'email-queue.log');
            foreach ($futureItems as $item) {
                write_log('  - Queue ID: ' . $item['id'] . ', Campanha: ' . $item['campaign_id'] . 
                         ', Agendado para: ' . $item['scheduled_at'], 'email-queue.log');
            }
        }
        
        // Buscar itens da fila prontos para envio
        $items = $this->queueModel->getPendingItems($batchSize);
        
        write_log('Itens prontos para envio (scheduled_at <= NOW()): ' . count($items), 'email-queue.log');
        
        if (empty($items)) {
            if (($queueStats['scheduled_future'] ?? 0) > 0) {
                $message = 'Nenhum item pronto para envio. Existem ' . ($queueStats['scheduled_future'] ?? 0) . ' itens agendados para horários futuros.';
                write_log('AVISO: ' . $message, 'email-queue.log');
            } else {
                write_log('Nenhum item pendente na fila para processar', 'email-queue.log');
            }
            
            if ($isCron) {
                echo json_encode([
                    'message' => 'Nenhum item na fila',
                    'processed' => 0,
                    'stats' => $queueStats
                ]);
            } else {
                if (($queueStats['scheduled_future'] ?? 0) > 0) {
                    $_SESSION['info'] = 'Nenhum item pronto para envio. Existem ' . ($queueStats['scheduled_future'] ?? 0) . ' itens agendados para horários futuros.';
                } else {
                    $_SESSION['info'] = 'Nenhum item pendente na fila para processar';
                }
                redirect(url('email-marketing'));
            }
            return;
        }
        
        write_log('Iniciando processamento de ' . count($items) . ' itens', 'email-queue.log');
        
        $stats = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0
        ];
        
        $mailer = new Mailer();
        
        foreach ($items as $item) {
            write_log('--- Processando item da fila ---', 'email-queue.log');
            write_log('Queue ID: ' . $item['id'], 'email-queue.log');
            write_log('Campanha ID: ' . $item['campaign_id'], 'email-queue.log');
            write_log('Destinatário: ' . ($item['email'] ?? 'N/A'), 'email-queue.log');
            
            try {
                // Buscar anexos da campanha
                write_log('=== BUSCANDO ANEXOS DA CAMPANHA ===', 'email-queue.log');
                write_log('Campaign ID: ' . $item['campaign_id'], 'email-queue.log');
                
                $attachments = $this->campaignModel->getAttachments($item['campaign_id']);
                write_log('Total de anexos no banco: ' . count($attachments), 'email-queue.log');
                
                $attachmentPaths = [];
                
                foreach ($attachments as $index => $attachment) {
                    write_log("Anexo #{$index}: " . json_encode($attachment), 'email-queue.log');
                    
                    $filePath = $attachment['file_path'] ?? null;
                    $fileName = $attachment['file_name'] ?? null;
                    
                    if ($filePath) {
                        write_log("Verificando arquivo: {$filePath}", 'email-queue.log');
                        
                        $absolutePath = null;
                        $basePath = dirname(__DIR__, 2);
                        
                        // Se o caminho começa com 'storage/', é relativo
                        if (strpos($filePath, 'storage' . DIRECTORY_SEPARATOR) === 0 || strpos($filePath, 'storage/') === 0) {
                            $absolutePath = $basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
                            write_log("Caminho relativo detectado. Construindo: {$absolutePath}", 'email-queue.log');
                        }
                        // Se é caminho absoluto (começa com / ou tem :), usar direto
                        elseif (strpos($filePath, DIRECTORY_SEPARATOR) === 0 || strpos($filePath, '/') === 0 || (strlen($filePath) > 2 && $filePath[1] === ':')) {
                            $absolutePath = $filePath;
                            write_log("Caminho absoluto detectado: {$absolutePath}", 'email-queue.log');
                            
                            // Se não existe, tentar extrair parte relativa
                            if (!file_exists($absolutePath)) {
                                if (strpos($filePath, 'storage' . DIRECTORY_SEPARATOR . 'uploads') !== false) {
                                    $relativePart = substr($filePath, strpos($filePath, 'storage' . DIRECTORY_SEPARATOR . 'uploads'));
                                    $absolutePath = $basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePart);
                                    write_log("Tentando caminho relativo extraído: {$absolutePath}", 'email-queue.log');
                                }
                            }
                        }
                        // Fallback: assumir que é relativo a partir de storage/uploads
                        else {
                            $absolutePath = $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
                            write_log("Fallback: assumindo caminho relativo: {$absolutePath}", 'email-queue.log');
                        }
                        
                        if ($absolutePath && file_exists($absolutePath)) {
                            $fileSize = filesize($absolutePath);
                            write_log("✓ Arquivo existe. Tamanho: {$fileSize} bytes", 'email-queue.log');
                            
                            $attachmentPaths[] = [
                                'path' => $absolutePath,
                                'name' => $fileName ?: basename($absolutePath)
                            ];
                        } else {
                            write_log("✗ ERRO: Arquivo não existe em nenhum caminho testado", 'email-queue.log');
                            write_log("Caminho original: {$filePath}", 'email-queue.log');
                            write_log("Caminho absoluto tentado: " . ($absolutePath ?? 'N/A'), 'email-queue.log');
                        }
                    } else {
                        write_log("✗ ERRO: Caminho do arquivo não definido", 'email-queue.log');
                    }
                }
                
                write_log('Anexos válidos preparados: ' . count($attachmentPaths), 'email-queue.log');
                if (count($attachmentPaths) > 0) {
                    write_log('Lista de anexos: ' . json_encode($attachmentPaths), 'email-queue.log');
                }
                
                // Preparar corpo do e-mail com assinatura
                $body = $item['body'];
                if (!empty($item['signature'])) {
                    $body .= '<br><br>' . $item['signature'];
                }
                
                write_log('Tentando enviar email para: ' . $item['email'], 'email-queue.log');
                
                // Enviar e-mail
                $mailer->send(
                    $item['email'],
                    $item['subject'],
                    $body,
                    strip_tags($body),
                    $attachmentPaths
                );
                
                write_log('SUCESSO: Email enviado para ' . $item['email'], 'email-queue.log');
                
                // Marcar como enviado
                $this->queueModel->markAsSent($item['id']);
                
                // Atualizar status do destinatário
                $db = \App\Core\Database::getInstance();
                $db->query(
                    "UPDATE email_campaign_recipients SET status = 'SENT', sent_at = NOW() WHERE id = ?",
                    [$item['recipient_id']]
                );
                
                // Atualizar contadores da campanha
                $this->campaignModel->updateCounts($item['campaign_id'], 1, 0);
                
                $stats['sent']++;
                $stats['processed']++;
                
                // Delay entre envios
                if ($delaySeconds > 0 && $stats['processed'] < count($items)) {
                    write_log('Aguardando ' . $delaySeconds . ' segundos antes do próximo envio...', 'email-queue.log');
                    sleep($delaySeconds);
                }
                
            } catch (\Exception $e) {
                $errorMsg = 'Erro ao enviar e-mail da fila: ' . $e->getMessage();
                write_log('ERRO: ' . $errorMsg, 'email-queue.log');
                write_log('Email: ' . ($item['email'] ?? 'N/A'), 'email-queue.log');
                write_log('Stack trace: ' . $e->getTraceAsString(), 'email-queue.log');
                
                // Marcar como falhou
                $this->queueModel->markAsFailed($item['id'], $e->getMessage());
                
                // Atualizar status do destinatário
                $db = \App\Core\Database::getInstance();
                $db->query(
                    "UPDATE email_campaign_recipients SET status = 'FAILED', error_message = ? WHERE id = ?",
                    [$e->getMessage(), $item['recipient_id']]
                );
                
                // Atualizar contadores da campanha
                $this->campaignModel->updateCounts($item['campaign_id'], 0, 1);
                
                $stats['failed']++;
                $stats['processed']++;
            }
        }
        
        write_log('=== PROCESSAMENTO CONCLUÍDO ===', 'email-queue.log');
        write_log('Estatísticas: Processados=' . $stats['processed'] . ', Enviados=' . $stats['sent'] . ', Falhas=' . $stats['failed'], 'email-queue.log');
        
        // Verificar se a campanha foi concluída
        foreach (array_unique(array_column($items, 'campaign_id')) as $campaignId) {
            $pendingCount = count($this->campaignModel->getRecipientsByStatus($campaignId, 'PENDING'));
            $queueStats = $this->queueModel->getStats($campaignId);
            
            if ($pendingCount == 0 && ($queueStats['pending'] ?? 0) == 0) {
                $this->campaignModel->updateStatus($campaignId, 'COMPLETED');
            } elseif (($queueStats['pending'] ?? 0) > 0 || ($queueStats['processing'] ?? 0) > 0) {
                $this->campaignModel->updateStatus($campaignId, 'SENDING');
            }
        }
        
        if ($isCron) {
            echo json_encode($stats);
        } else {
            $_SESSION['success'] = "Processados: {$stats['processed']} | Enviados: {$stats['sent']} | Falhas: {$stats['failed']}";
            redirect(url('email-marketing'));
        }
    }
    
    /**
     * Pausa uma campanha
     */
    public function pause($id)
    {
        Auth::requireAdmin();
        
        $campaign = $this->campaignModel->findById($id);
        
        if (!$campaign || $campaign['status'] !== 'SENDING') {
            $_SESSION['error'] = 'Apenas campanhas em envio podem ser pausadas';
            redirect(url('email-marketing'));
        }
        
        $this->campaignModel->updateStatus($id, 'PAUSED');
        $_SESSION['success'] = 'Campanha pausada com sucesso!';
        redirect(url('email-marketing/' . $id));
    }
    
    /**
     * Cancela uma campanha
     */
    public function cancel($id)
    {
        Auth::requireAdmin();
        
        $campaign = $this->campaignModel->findById($id);
        
        if (!$campaign) {
            $_SESSION['error'] = 'Campanha não encontrada';
            redirect(url('email-marketing'));
        }
        
        $this->campaignModel->updateStatus($id, 'CANCELLED');
        $_SESSION['success'] = 'Campanha cancelada com sucesso!';
        redirect(url('email-marketing/' . $id));
    }
    
    /**
     * Deleta uma campanha
     */
    public function destroy($id)
    {
        write_log('=== TENTATIVA DE DELEÇÃO DE CAMPANHA ===', 'email-marketing.log');
        write_log('Campanha ID: ' . $id, 'email-marketing.log');
        write_log('Método HTTP: ' . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'), 'email-marketing.log');
        write_log('Usuário: ' . (Auth::user()['name'] ?? 'N/A'), 'email-marketing.log');
        
        Auth::requireAdmin();
        
        $campaign = $this->campaignModel->findById($id);
        
        if (!$campaign) {
            $errorMsg = 'Campanha não encontrada';
            write_log('ERRO: ' . $errorMsg, 'email-marketing.log');
            write_log('Campanha ID ' . $id . ' não existe no banco de dados', 'email-marketing.log');
            $_SESSION['error'] = $errorMsg;
            redirect(url('email-marketing'));
        }
        
        write_log('Campanha encontrada: ' . json_encode([
            'id' => $campaign['id'],
            'name' => $campaign['name'] ?? '',
            'status' => $campaign['status'] ?? '',
            'total_recipients' => $campaign['total_recipients'] ?? 0
        ], JSON_UNESCAPED_UNICODE), 'email-marketing.log');
        
        if ($campaign['status'] === 'SENDING') {
            $errorMsg = 'Não é possível deletar uma campanha em envio. Pause ou cancele primeiro.';
            write_log('ERRO: ' . $errorMsg, 'email-marketing.log');
            write_log('Status da campanha: ' . $campaign['status'], 'email-marketing.log');
            $_SESSION['error'] = $errorMsg;
            redirect(url('email-marketing/' . $id));
        }
        
        try {
            // Deletar anexos
            write_log('Iniciando deleção de anexos...', 'email-marketing.log');
            $attachments = $this->campaignModel->getAttachments($id);
            write_log('Total de anexos encontrados: ' . count($attachments), 'email-marketing.log');
            
            foreach ($attachments as $attachment) {
                try {
                    $this->campaignModel->deleteAttachment($attachment['id']);
                    write_log('Anexo deletado: ID ' . $attachment['id'] . ' - ' . ($attachment['file_name'] ?? 'N/A'), 'email-marketing.log');
                } catch (\Exception $e) {
                    write_log('ERRO ao deletar anexo ID ' . $attachment['id'] . ': ' . $e->getMessage(), 'email-marketing.log');
                }
            }
            
            // Deletar campanha
            write_log('Iniciando deleção da campanha...', 'email-marketing.log');
            $result = $this->campaignModel->delete($id);
            
            if ($result) {
                write_log('SUCESSO: Campanha deletada com sucesso!', 'email-marketing.log');
                write_log('Campanha ID ' . $id . ' removida do banco de dados', 'email-marketing.log');
                $_SESSION['success'] = 'Campanha deletada com sucesso!';
                redirect(url('email-marketing'));
            } else {
                $errorMsg = 'Erro ao deletar campanha. A operação não foi concluída.';
                write_log('ERRO: ' . $errorMsg, 'email-marketing.log');
                write_log('Método delete() retornou false', 'email-marketing.log');
                $_SESSION['error'] = $errorMsg;
                redirect(url('email-marketing/' . $id));
            }
            
        } catch (\Exception $e) {
            $errorMsg = 'Erro ao deletar campanha: ' . $e->getMessage();
            write_log('ERRO EXCEÇÃO: ' . $errorMsg, 'email-marketing.log');
            write_log('Stack trace: ' . $e->getTraceAsString(), 'email-marketing.log');
            $_SESSION['error'] = $errorMsg;
            redirect(url('email-marketing/' . $id));
        }
    }
    
    /**
     * Remove anexo
     */
    public function removeAttachment($campaignId, $attachmentId)
    {
        Auth::requireAdmin();
        
        try {
            $this->campaignModel->deleteAttachment($attachmentId);
            $_SESSION['success'] = 'Anexo removido com sucesso!';
        } catch (\Exception $e) {
            write_log('Erro ao remover anexo: ' . $e->getMessage(), 'email-marketing.log');
            $_SESSION['error'] = 'Erro ao remover anexo: ' . $e->getMessage();
        }
        
        redirect(url('email-marketing/' . $campaignId . '/edit'));
    }
    
    /**
     * Processa upload de anexos
     */
    private function handleAttachments($campaignId)
    {
        if (!isset($_FILES['attachments']) || empty($_FILES['attachments']['name'][0])) {
            return;
        }
        
        $files = $_FILES['attachments'];
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'size' => $files['size'][$i]
                ];
                
                try {
                    write_log('=== INICIANDO UPLOAD DE ANEXO ===', 'email-marketing.log');
                    write_log('Nome do arquivo: ' . $file['name'], 'email-marketing.log');
                    write_log('Tipo MIME do arquivo: ' . ($file['type'] ?? 'N/A'), 'email-marketing.log');
                    write_log('Tamanho: ' . $file['size'] . ' bytes', 'email-marketing.log');
                    
                    $uploadResult = $this->fileUpload->upload($file, 'email-attachments');
                    
                    write_log('Resultado do upload: ' . json_encode($uploadResult), 'email-marketing.log');
                    
                    // FileUpload retorna: file_path, original_name, file_name, size, mime_type, extension
                    if (isset($uploadResult['file_path']) && !empty($uploadResult['file_path'])) {
                        $filePath = $uploadResult['file_path'];
                        
                        // Garantir que o caminho seja absoluto
                        if (!file_exists($filePath)) {
                            // Tentar caminho relativo a partir da raiz do projeto
                            $basePath = dirname(__DIR__, 2);
                            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $filePath);
                            $fullPath = $basePath . DIRECTORY_SEPARATOR . $relativePath;
                            
                            if (file_exists($fullPath)) {
                                $filePath = $fullPath;
                            } else {
                                // Tentar caminho direto
                                $directPath = __DIR__ . '/../../' . ltrim($filePath, '/');
                                if (file_exists($directPath)) {
                                    $filePath = $directPath;
                                }
                            }
                        }
                        
                        // Verificar novamente se o arquivo existe
                        if (!file_exists($filePath)) {
                            write_log('ERRO: Arquivo não encontrado após correção de caminho. Caminho original: ' . ($uploadResult['file_path'] ?? 'N/A'), 'email-marketing.log');
                            continue;
                        }
                        
                        // Garantir que o caminho seja absoluto e normalizado
                        $absolutePath = realpath($filePath) ?: $filePath;
                        
                        // Converter para caminho relativo a partir de storage/uploads
                        // Isso garante que funcione tanto em localhost quanto no servidor
                        $storagePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads';
                        $relativePath = str_replace($storagePath . DIRECTORY_SEPARATOR, '', $absolutePath);
                        
                        // Se não conseguiu extrair caminho relativo, usar o caminho completo
                        if ($relativePath === $absolutePath) {
                            // Tentar extrair apenas a parte após 'storage/uploads'
                            if (strpos($absolutePath, 'storage' . DIRECTORY_SEPARATOR . 'uploads') !== false) {
                                $relativePath = substr($absolutePath, strpos($absolutePath, 'storage' . DIRECTORY_SEPARATOR . 'uploads') + strlen('storage' . DIRECTORY_SEPARATOR . 'uploads') + 1);
                            } else {
                                // Fallback: usar caminho absoluto
                                $relativePath = $absolutePath;
                            }
                        }
                        
                        // Construir caminho relativo no formato: email-attachments/nome-arquivo.ext
                        $pathToSave = 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $relativePath;
                        
                        write_log('Upload de anexo bem-sucedido. Caminho absoluto: ' . $absolutePath, 'email-marketing.log');
                        write_log('Caminho relativo para salvar: ' . $pathToSave, 'email-marketing.log');
                        write_log('Nome original: ' . ($uploadResult['original_name'] ?? $file['name']), 'email-marketing.log');
                        write_log('Tamanho: ' . ($uploadResult['size'] ?? $file['size']) . ' bytes', 'email-marketing.log');
                        
                        $this->campaignModel->addAttachment(
                            $campaignId,
                            $pathToSave,
                            $uploadResult['original_name'] ?? $file['name'],
                            $uploadResult['size'] ?? $file['size'],
                            $uploadResult['mime_type'] ?? $file['type']
                        );
                        
                        write_log('Anexo salvo no banco de dados com sucesso', 'email-marketing.log');
                    } else {
                        write_log('ERRO: Upload não retornou file_path. Resultado: ' . json_encode($uploadResult), 'email-marketing.log');
                    }
                } catch (\Exception $e) {
                    write_log('Erro ao fazer upload de anexo: ' . $e->getMessage(), 'email-marketing.log');
                }
            }
        }
    }
}

