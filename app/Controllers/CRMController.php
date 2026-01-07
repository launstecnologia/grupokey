<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Pipeline;
use App\Models\Stage;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\DealTask;
use App\Models\Notification;
use App\Models\Establishment;
use App\Models\Representative;
use App\Models\User;
use App\Core\Mailer;

class CRMController
{
    private $pipelineModel;
    private $stageModel;
    private $dealModel;
    private $activityModel;
    private $taskModel;
    private $notificationModel;
    private $establishmentModel;
    private $representativeModel;
    private $userModel;
    
    public function __construct()
    {
        $this->pipelineModel = new Pipeline();
        $this->stageModel = new Stage();
        $this->dealModel = new Deal();
        $this->activityModel = new DealActivity();
        $this->taskModel = new DealTask();
        $this->notificationModel = new Notification();
        $this->establishmentModel = new Establishment();
        $this->representativeModel = new Representative();
        $this->userModel = new User();
    }
    
    /**
     * Exibe o Kanban do CRM
     */
    public function index()
    {
        Auth::requireAdmin();
        
        $pipelineId = $_GET['pipeline_id'] ?? null;
        
        // Se não especificou pipeline, buscar o padrão
        if (!$pipelineId) {
            $defaultPipeline = $this->pipelineModel->getDefault();
            $pipelineId = $defaultPipeline ? $defaultPipeline['id'] : null;
        }
        
        $pipelines = $this->pipelineModel->getAll(['is_active' => true]);
        $currentPipeline = $pipelineId ? $this->pipelineModel->findById($pipelineId) : null;
        
        if (!$currentPipeline && !empty($pipelines)) {
            $currentPipeline = $pipelines[0];
            $pipelineId = $currentPipeline['id'];
        }
        
        $stages = [];
        $deals = [];
        
        if ($currentPipeline) {
            $stages = $this->stageModel->getByPipelineId($currentPipeline['id'], ['is_active' => true]);
            $deals = $this->dealModel->getByPipelineId($currentPipeline['id'], ['status' => 'ACTIVE']);
            
            // Organizar deals por stage
            $dealsByStage = [];
            foreach ($deals as $deal) {
                $dealsByStage[$deal['stage_id']][] = $deal;
            }
        }
        
        $data = [
            'title' => 'CRM - Kanban',
            'currentPage' => 'crm',
            'pipelines' => $pipelines,
            'currentPipeline' => $currentPipeline,
            'stages' => $stages,
            'deals' => $deals,
            'dealsByStage' => $dealsByStage ?? []
        ];
        
        view('crm/index', $data);
    }
    
    /**
     * API: Mover deal entre stages
     */
    public function moveDeal()
    {
        Auth::requireAdmin();
        
        header('Content-Type: application/json');
        
        $dealId = $_POST['deal_id'] ?? null;
        $stageId = $_POST['stage_id'] ?? null;
        $sortOrder = $_POST['sort_order'] ?? 0;
        
        if (!$dealId || !$stageId) {
            echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
            return;
        }
        
        try {
            $deal = $this->dealModel->findById($dealId);
            if (!$deal) {
                echo json_encode(['success' => false, 'message' => 'Deal não encontrado']);
                return;
            }
            
            $oldStageId = $deal['stage_id'];
            $this->dealModel->moveToStage($dealId, $stageId, $sortOrder);
            
            // Registrar atividade
            $this->activityModel->create([
                'deal_id' => $dealId,
                'activity_type' => 'STAGE_CHANGE',
                'title' => 'Deal movido',
                'old_value' => $oldStageId,
                'new_value' => $stageId,
                'created_by_user_id' => Auth::user()['id']
            ]);
            
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * API: Atualizar ordem dos deals
     */
    public function updateDealOrder()
    {
        Auth::requireAdmin();
        
        header('Content-Type: application/json');
        
        $deals = $_POST['deals'] ?? [];
        
        if (empty($deals)) {
            echo json_encode(['success' => false, 'message' => 'Nenhum deal fornecido']);
            return;
        }
        
        try {
            foreach ($deals as $order => $dealId) {
                $this->dealModel->updateSortOrder($dealId, $order);
            }
            
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Exibe formulário de criação de deal
     */
    public function createDeal()
    {
        Auth::requireAdmin();
        
        $pipelineId = $_GET['pipeline_id'] ?? null;
        if (!$pipelineId) {
            $defaultPipeline = $this->pipelineModel->getDefault();
            $pipelineId = $defaultPipeline ? $defaultPipeline['id'] : null;
        }
        
        $pipelines = $this->pipelineModel->getAll(['is_active' => true]);
        $stages = $pipelineId ? $this->stageModel->getByPipelineId($pipelineId, ['is_active' => true]) : [];
        $establishments = $this->establishmentModel->getAll(['status' => 'APPROVED']);
        $representatives = $this->representativeModel->getAll(['status' => 'ACTIVE']);
        $users = $this->userModel->getAll(['status' => 'ACTIVE']);
        
        $data = [
            'title' => 'Novo Negócio',
            'currentPage' => 'crm',
            'pipelines' => $pipelines,
            'currentPipelineId' => $pipelineId,
            'stages' => $stages,
            'establishments' => $establishments,
            'representatives' => $representatives,
            'users' => $users
        ];
        
        view('crm/deals/create', $data);
    }
    
    /**
     * Salva novo deal
     */
    public function storeDeal()
    {
        Auth::requireAdmin();
        
        $errors = [];
        
        if (empty($_POST['title']) || trim($_POST['title']) === '') {
            $errors[] = 'Título é obrigatório';
        }
        
        if (empty($_POST['pipeline_id'])) {
            $errors[] = 'Pipeline é obrigatório';
        }
        
        if (empty($_POST['stage_id'])) {
            $errors[] = 'Stage é obrigatório';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            redirect(url('crm/deals/create?pipeline_id=' . ($_POST['pipeline_id'] ?? '')));
        }
        
        try {
            $data = [
                'pipeline_id' => $_POST['pipeline_id'],
                'stage_id' => $_POST['stage_id'],
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? null,
                'value' => !empty($_POST['value']) ? str_replace(['.', ','], ['', '.'], $_POST['value']) : null,
                'currency' => $_POST['currency'] ?? 'BRL',
                'expected_close_date' => !empty($_POST['expected_close_date']) ? $_POST['expected_close_date'] : null,
                'probability' => !empty($_POST['probability']) ? (int)$_POST['probability'] : 0,
                'priority' => $_POST['priority'] ?? 'MEDIUM',
                'establishment_id' => !empty($_POST['establishment_id']) ? $_POST['establishment_id'] : null,
                'representative_id' => !empty($_POST['representative_id']) ? $_POST['representative_id'] : null,
                'assigned_to_user_id' => !empty($_POST['assigned_to_user_id']) ? $_POST['assigned_to_user_id'] : null,
                'created_by_user_id' => Auth::user()['id']
            ];
            
            $dealId = $this->dealModel->create($data);
            
            // Registrar atividade
            $this->activityModel->create([
                'deal_id' => $dealId,
                'activity_type' => 'NOTE',
                'title' => 'Deal criado',
                'description' => 'Deal criado por ' . Auth::user()['name'],
                'created_by_user_id' => Auth::user()['id']
            ]);
            
            $_SESSION['success'] = 'Negócio criado com sucesso!';
            redirect(url('crm?pipeline_id=' . $data['pipeline_id']));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar negócio: ' . $e->getMessage();
            redirect(url('crm/deals/create?pipeline_id=' . ($_POST['pipeline_id'] ?? '')));
        }
    }
    
    /**
     * Exibe detalhes do deal
     */
    public function showDeal($id)
    {
        Auth::requireAdmin();
        
        $deal = $this->dealModel->findById($id);
        
        if (!$deal) {
            $_SESSION['error'] = 'Negócio não encontrado';
            redirect(url('crm'));
        }
        
        $activities = $this->activityModel->getByDealId($id);
        
        // Buscar tarefas (pode não existir se a tabela não foi criada ainda)
        $tasks = [];
        try {
            $tasks = $this->taskModel->getByDealId($id);
        } catch (\Exception $e) {
            // Se a tabela não existir, continuar sem tarefas
            write_log('Erro ao buscar tarefas: ' . $e->getMessage(), 'app.log');
        }
        
        $data = [
            'title' => 'Detalhes do Negócio',
            'currentPage' => 'crm',
            'deal' => $deal,
            'activities' => $activities,
            'tasks' => $tasks
        ];
        
        view('crm/deals/show', $data);
    }
    
    /**
     * Exibe formulário de edição de deal
     */
    public function editDeal($id)
    {
        Auth::requireAdmin();
        
        $deal = $this->dealModel->findById($id);
        
        if (!$deal) {
            $_SESSION['error'] = 'Negócio não encontrado';
            redirect(url('crm'));
        }
        
        $pipelines = $this->pipelineModel->getAll(['is_active' => true]);
        $stages = $this->stageModel->getByPipelineId($deal['pipeline_id'], ['is_active' => true]);
        $establishments = $this->establishmentModel->getAll(['status' => 'APPROVED']);
        $representatives = $this->representativeModel->getAll(['status' => 'ACTIVE']);
        $users = $this->userModel->getAll(['status' => 'ACTIVE']);
        
        $data = [
            'title' => 'Editar Negócio',
            'currentPage' => 'crm',
            'deal' => $deal,
            'pipelines' => $pipelines,
            'stages' => $stages,
            'establishments' => $establishments,
            'representatives' => $representatives,
            'users' => $users
        ];
        
        view('crm/deals/edit', $data);
    }
    
    /**
     * Atualiza deal
     */
    public function updateDeal($id)
    {
        Auth::requireAdmin();
        
        $deal = $this->dealModel->findById($id);
        
        if (!$deal) {
            $_SESSION['error'] = 'Negócio não encontrado';
            redirect(url('crm'));
        }
        
        $errors = [];
        
        if (empty($_POST['title']) || trim($_POST['title']) === '') {
            $errors[] = 'Título é obrigatório';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            redirect(url('crm/deals/' . $id . '/edit'));
        }
        
        try {
            $oldValue = $deal['value'];
            $oldStageId = $deal['stage_id'];
            
            $data = [
                'pipeline_id' => $_POST['pipeline_id'] ?? $deal['pipeline_id'],
                'stage_id' => $_POST['stage_id'] ?? $deal['stage_id'],
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? null,
                'value' => !empty($_POST['value']) ? str_replace(['.', ','], ['', '.'], $_POST['value']) : null,
                'currency' => $_POST['currency'] ?? 'BRL',
                'expected_close_date' => !empty($_POST['expected_close_date']) ? $_POST['expected_close_date'] : null,
                'probability' => !empty($_POST['probability']) ? (int)$_POST['probability'] : 0,
                'priority' => $_POST['priority'] ?? 'MEDIUM',
                'establishment_id' => !empty($_POST['establishment_id']) ? $_POST['establishment_id'] : null,
                'representative_id' => !empty($_POST['representative_id']) ? $_POST['representative_id'] : null,
                'assigned_to_user_id' => !empty($_POST['assigned_to_user_id']) ? $_POST['assigned_to_user_id'] : null
            ];
            
            // Se mudou o status para WON ou LOST, definir data de fechamento
            if (isset($_POST['status']) && in_array($_POST['status'], ['WON', 'LOST']) && $deal['status'] === 'ACTIVE') {
                $data['status'] = $_POST['status'];
                $data['actual_close_date'] = date('Y-m-d');
            } elseif (isset($_POST['status'])) {
                $data['status'] = $_POST['status'];
            }
            
            $this->dealModel->update($id, $data);
            
            // Registrar atividades de mudanças
            if ($oldValue != $data['value']) {
                $this->activityModel->create([
                    'deal_id' => $id,
                    'activity_type' => 'VALUE_CHANGE',
                    'title' => 'Valor alterado',
                    'old_value' => $oldValue,
                    'new_value' => $data['value'],
                    'created_by_user_id' => Auth::user()['id']
                ]);
            }
            
            if ($oldStageId != $data['stage_id']) {
                $this->activityModel->create([
                    'deal_id' => $id,
                    'activity_type' => 'STAGE_CHANGE',
                    'title' => 'Stage alterado',
                    'old_value' => $oldStageId,
                    'new_value' => $data['stage_id'],
                    'created_by_user_id' => Auth::user()['id']
                ]);
            }
            
            $_SESSION['success'] = 'Negócio atualizado com sucesso!';
            redirect(url('crm/deals/' . $id));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar negócio: ' . $e->getMessage();
            redirect(url('crm/deals/' . $id . '/edit'));
        }
    }
    
    /**
     * Deleta deal
     */
    public function deleteDeal($id)
    {
        Auth::requireAdmin();
        
        try {
            $this->dealModel->delete($id);
            $_SESSION['success'] = 'Negócio excluído com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir negócio: ' . $e->getMessage();
        }
        
        redirect(url('crm'));
    }
    
    /**
     * Adiciona atividade ao deal
     */
    public function addActivity($dealId)
    {
        Auth::requireAdmin();
        
        $deal = $this->dealModel->findById($dealId);
        
        if (!$deal) {
            $_SESSION['error'] = 'Negócio não encontrado';
            redirect(url('crm'));
        }
        
        if (empty($_POST['activity_type']) || empty($_POST['description'])) {
            $_SESSION['error'] = 'Tipo e descrição da atividade são obrigatórios';
            redirect(url('crm/deals/' . $dealId));
        }
        
        try {
            $this->activityModel->create([
                'deal_id' => $dealId,
                'activity_type' => $_POST['activity_type'],
                'title' => $_POST['title'] ?? null,
                'description' => $_POST['description'],
                'activity_date' => !empty($_POST['activity_date']) ? $_POST['activity_date'] : date('Y-m-d H:i:s'),
                'duration_minutes' => !empty($_POST['duration_minutes']) ? (int)$_POST['duration_minutes'] : null,
                'created_by_user_id' => Auth::user()['id']
            ]);
            
            $_SESSION['success'] = 'Atividade adicionada com sucesso!';
            redirect(url('crm/deals/' . $dealId));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao adicionar atividade: ' . $e->getMessage();
            redirect(url('crm/deals/' . $dealId));
        }
    }
    
    // ============================================
    // GERENCIAMENTO DE PIPELINES (ADMIN)
    // ============================================
    
    public function pipelines()
    {
        Auth::requireAdmin();
        
        $pipelines = $this->pipelineModel->getAll();
        
        $data = [
            'title' => 'Configurar Pipelines',
            'currentPage' => 'crm',
            'pipelines' => $pipelines
        ];
        
        view('crm/pipelines/index', $data);
    }
    
    public function createPipeline()
    {
        Auth::requireAdmin();
        
        $data = [
            'title' => 'Novo Pipeline',
            'currentPage' => 'crm'
        ];
        
        view('crm/pipelines/create', $data);
    }
    
    public function storePipeline()
    {
        Auth::requireAdmin();
        
        if (empty($_POST['name']) || trim($_POST['name']) === '') {
            $_SESSION['error'] = 'Nome do pipeline é obrigatório';
            redirect(url('crm/pipelines/create'));
        }
        
        try {
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? null,
                'color' => $_POST['color'] ?? '#3B82F6',
                'is_default' => !empty($_POST['is_default']),
                'is_active' => !empty($_POST['is_active']),
                'created_by_user_id' => Auth::user()['id']
            ];
            
            $pipelineId = $this->pipelineModel->create($data);
            
            $_SESSION['success'] = 'Pipeline criado com sucesso!';
            redirect(url('crm/pipelines'));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar pipeline: ' . $e->getMessage();
            redirect(url('crm/pipelines/create'));
        }
    }
    
    public function editPipeline($id)
    {
        Auth::requireAdmin();
        
        $pipeline = $this->pipelineModel->findById($id);
        
        if (!$pipeline) {
            $_SESSION['error'] = 'Pipeline não encontrado';
            redirect(url('crm/pipelines'));
        }
        
        $data = [
            'title' => 'Editar Pipeline',
            'currentPage' => 'crm',
            'pipeline' => $pipeline
        ];
        
        view('crm/pipelines/edit', $data);
    }
    
    public function updatePipeline($id)
    {
        Auth::requireAdmin();
        
        if (empty($_POST['name']) || trim($_POST['name']) === '') {
            $_SESSION['error'] = 'Nome do pipeline é obrigatório';
            redirect(url('crm/pipelines/' . $id . '/edit'));
        }
        
        try {
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? null,
                'color' => $_POST['color'] ?? '#3B82F6',
                'is_default' => !empty($_POST['is_default']),
                'is_active' => !empty($_POST['is_active'])
            ];
            
            $this->pipelineModel->update($id, $data);
            
            $_SESSION['success'] = 'Pipeline atualizado com sucesso!';
            redirect(url('crm/pipelines'));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar pipeline: ' . $e->getMessage();
            redirect(url('crm/pipelines/' . $id . '/edit'));
        }
    }
    
    public function deletePipeline($id)
    {
        Auth::requireAdmin();
        
        try {
            $this->pipelineModel->delete($id);
            $_SESSION['success'] = 'Pipeline excluído com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir pipeline: ' . $e->getMessage();
        }
        
        redirect(url('crm/pipelines'));
    }
    
    // ============================================
    // GERENCIAMENTO DE STAGES (ADMIN)
    // ============================================
    
    public function stages($pipelineId)
    {
        Auth::requireAdmin();
        
        $pipeline = $this->pipelineModel->findById($pipelineId);
        
        if (!$pipeline) {
            $_SESSION['error'] = 'Pipeline não encontrado';
            redirect(url('crm/pipelines'));
        }
        
        $stages = $this->stageModel->getByPipelineId($pipelineId);
        
        $data = [
            'title' => 'Configurar Stages - ' . $pipeline['name'],
            'currentPage' => 'crm',
            'pipeline' => $pipeline,
            'stages' => $stages
        ];
        
        view('crm/stages/index', $data);
    }
    
    public function createStage($pipelineId)
    {
        Auth::requireAdmin();
        
        $pipeline = $this->pipelineModel->findById($pipelineId);
        
        if (!$pipeline) {
            $_SESSION['error'] = 'Pipeline não encontrado';
            redirect(url('crm/pipelines'));
        }
        
        $data = [
            'title' => 'Novo Stage',
            'currentPage' => 'crm',
            'pipeline' => $pipeline
        ];
        
        view('crm/stages/create', $data);
    }
    
    public function storeStage($pipelineId)
    {
        Auth::requireAdmin();
        
        if (empty($_POST['name']) || trim($_POST['name']) === '') {
            $_SESSION['error'] = 'Nome do stage é obrigatório';
            redirect(url('crm/pipelines/' . $pipelineId . '/stages/create'));
        }
        
        try {
            // Buscar último sort_order
            $stages = $this->stageModel->getByPipelineId($pipelineId);
            $maxOrder = 0;
            foreach ($stages as $stage) {
                if ($stage['sort_order'] > $maxOrder) {
                    $maxOrder = $stage['sort_order'];
                }
            }
            
            $data = [
                'pipeline_id' => $pipelineId,
                'name' => $_POST['name'],
                'color' => $_POST['color'] ?? '#6B7280',
                'sort_order' => $maxOrder + 1,
                'is_final' => !empty($_POST['is_final']),
                'is_active' => !empty($_POST['is_active'])
            ];
            
            $this->stageModel->create($data);
            
            $_SESSION['success'] = 'Stage criado com sucesso!';
            redirect(url('crm/pipelines/' . $pipelineId . '/stages'));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar stage: ' . $e->getMessage();
            redirect(url('crm/pipelines/' . $pipelineId . '/stages/create'));
        }
    }
    
    public function editStage($pipelineId, $id)
    {
        Auth::requireAdmin();
        
        $pipeline = $this->pipelineModel->findById($pipelineId);
        $stage = $this->stageModel->findById($id);
        
        if (!$pipeline || !$stage || $stage['pipeline_id'] != $pipelineId) {
            $_SESSION['error'] = 'Stage não encontrado';
            redirect(url('crm/pipelines/' . $pipelineId . '/stages'));
        }
        
        $data = [
            'title' => 'Editar Stage',
            'currentPage' => 'crm',
            'pipeline' => $pipeline,
            'stage' => $stage
        ];
        
        view('crm/stages/edit', $data);
    }
    
    public function updateStage($pipelineId, $id)
    {
        Auth::requireAdmin();
        
        if (empty($_POST['name']) || trim($_POST['name']) === '') {
            $_SESSION['error'] = 'Nome do stage é obrigatório';
            redirect(url('crm/pipelines/' . $pipelineId . '/stages/' . $id . '/edit'));
        }
        
        try {
            $data = [
                'name' => $_POST['name'],
                'color' => $_POST['color'] ?? '#6B7280',
                'sort_order' => !empty($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0,
                'is_final' => !empty($_POST['is_final']),
                'is_active' => !empty($_POST['is_active'])
            ];
            
            $this->stageModel->update($id, $data);
            
            $_SESSION['success'] = 'Stage atualizado com sucesso!';
            redirect(url('crm/pipelines/' . $pipelineId . '/stages'));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar stage: ' . $e->getMessage();
            redirect(url('crm/pipelines/' . $pipelineId . '/stages/' . $id . '/edit'));
        }
    }
    
    public function deleteStage($pipelineId, $id)
    {
        Auth::requireAdmin();
        
        try {
            $this->stageModel->delete($id);
            $_SESSION['success'] = 'Stage excluído com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir stage: ' . $e->getMessage();
        }
        
        redirect(url('crm/pipelines/' . $pipelineId . '/stages'));
    }
    
    /**
     * API: Atualizar ordem dos stages
     */
    public function updateStageOrder($pipelineId)
    {
        Auth::requireAdmin();
        
        header('Content-Type: application/json');
        
        $stages = $_POST['stages'] ?? [];
        
        if (empty($stages)) {
            echo json_encode(['success' => false, 'message' => 'Nenhum stage fornecido']);
            return;
        }
        
        try {
            $this->stageModel->updateSortOrders($stages);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    // ============================================
    // GERENCIAMENTO DE TAREFAS
    // ============================================
    
    public function createTask($dealId)
    {
        Auth::requireAdmin();
        
        $deal = $this->dealModel->findById($dealId);
        
        if (!$deal) {
            $_SESSION['error'] = 'Negócio não encontrado';
            redirect(url('crm'));
        }
        
        if (empty($_POST['title']) || empty($_POST['scheduled_at'])) {
            $_SESSION['error'] = 'Título e data/hora são obrigatórios';
            redirect(url('crm/deals/' . $dealId));
        }
        
        try {
            $taskId = $this->taskModel->create([
                'deal_id' => $dealId,
                'task_type' => $_POST['task_type'] ?? 'OTHER',
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? null,
                'scheduled_at' => $_POST['scheduled_at'],
                'reminder_minutes' => !empty($_POST['reminder_minutes']) ? (int)$_POST['reminder_minutes'] : 15,
                'created_by_user_id' => Auth::user()['id']
            ]);
            
            // Registrar atividade
            $this->activityModel->create([
                'deal_id' => $dealId,
                'activity_type' => 'TASK',
                'title' => 'Tarefa criada',
                'description' => 'Tarefa: ' . $_POST['title'],
                'activity_date' => $_POST['scheduled_at'],
                'created_by_user_id' => Auth::user()['id']
            ]);
            
            $_SESSION['success'] = 'Tarefa criada com sucesso!';
            redirect(url('crm/deals/' . $dealId));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar tarefa: ' . $e->getMessage();
            redirect(url('crm/deals/' . $dealId));
        }
    }
    
    public function completeTask($taskId)
    {
        Auth::requireAdmin();
        
        header('Content-Type: application/json');
        
        try {
            $task = $this->taskModel->findById($taskId);
            
            if (!$task) {
                echo json_encode(['success' => false, 'message' => 'Tarefa não encontrada']);
                return;
            }
            
            $this->taskModel->markAsCompleted($taskId);
            
            // Registrar atividade
            $this->activityModel->create([
                'deal_id' => $task['deal_id'],
                'activity_type' => 'TASK',
                'title' => 'Tarefa concluída',
                'description' => 'Tarefa: ' . $task['title'],
                'created_by_user_id' => Auth::user()['id']
            ]);
            
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function deleteTask($taskId)
    {
        Auth::requireAdmin();
        
        try {
            $task = $this->taskModel->findById($taskId);
            
            if (!$task) {
                $_SESSION['error'] = 'Tarefa não encontrada';
                redirect(url('crm'));
            }
            
            $this->taskModel->delete($taskId);
            $_SESSION['success'] = 'Tarefa excluída com sucesso!';
            redirect(url('crm/deals/' . $task['deal_id']));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir tarefa: ' . $e->getMessage();
            redirect(url('crm'));
        }
    }
    
    // ============================================
    // NOTIFICAÇÕES
    // ============================================
    
    public function getNotifications()
    {
        Auth::requireAuth();
        
        header('Content-Type: application/json');
        
        $notifications = $this->notificationModel->getByUserId(Auth::user()['id'], ['limit' => 20]);
        $unreadCount = $this->notificationModel->getUnreadCount(Auth::user()['id']);
        
        echo json_encode([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }
    
    public function markNotificationRead($id)
    {
        Auth::requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            $this->notificationModel->markAsRead($id, Auth::user()['id']);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function markAllNotificationsRead()
    {
        Auth::requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            $this->notificationModel->markAllAsRead(Auth::user()['id']);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    // ============================================
    // PROCESSAMENTO DE LEMBRETES (CRON)
    // ============================================
    
    public function processTaskReminders()
    {
        // Verificar se é chamada de cron (sem User-Agent ou curl/wget)
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $isCron = empty($userAgent) || 
                  stripos($userAgent, 'curl') !== false || 
                  stripos($userAgent, 'wget') !== false;
        
        if (!$isCron) {
            Auth::requireAdmin();
        }
        
        header('Content-Type: application/json');
        
        try {
            // Buscar tarefas que precisam de lembrete (próximos 30 minutos)
            $tasks = $this->taskModel->getTasksForReminder(30);
            
            $processed = 0;
            $mailer = new Mailer();
            
            foreach ($tasks as $task) {
                if (!$task['user_email'] || !$task['assigned_to_user_id']) {
                    continue;
                }
                
                // Criar notificação in-app
                $this->notificationModel->create([
                    'user_id' => $task['assigned_to_user_id'],
                    'type' => 'TASK_REMINDER',
                    'title' => 'Lembrete de Tarefa',
                    'message' => sprintf(
                        'Você tem uma tarefa agendada: "%s" no negócio "%s" às %s',
                        $task['title'],
                        $task['deal_title'],
                        date('d/m/Y H:i', strtotime($task['scheduled_at']))
                    ),
                    'related_type' => 'task',
                    'related_id' => $task['id']
                ]);
                
                // Enviar email
                $emailSubject = 'Lembrete: ' . $task['title'];
                $emailBody = sprintf(
                    '<h2>Lembrete de Tarefa</h2>
                    <p>Olá %s,</p>
                    <p>Você tem uma tarefa agendada:</p>
                    <ul>
                        <li><strong>Tarefa:</strong> %s</li>
                        <li><strong>Negócio:</strong> %s</li>
                        <li><strong>Data/Hora:</strong> %s</li>
                        <li><strong>Tipo:</strong> %s</li>
                    </ul>
                    <p><a href="%s">Ver detalhes do negócio</a></p>',
                    htmlspecialchars($task['user_name']),
                    htmlspecialchars($task['title']),
                    htmlspecialchars($task['deal_title']),
                    date('d/m/Y H:i', strtotime($task['scheduled_at'])),
                    $this->getTaskTypeLabel($task['task_type']),
                    url('crm/deals/' . $task['deal_id'])
                );
                
                $mailer->send(
                    $task['user_email'],
                    $emailSubject,
                    $emailBody
                );
                
                // Marcar lembrete como enviado
                $this->taskModel->markReminderSent($task['id']);
                
                $processed++;
            }
            
            echo json_encode([
                'success' => true,
                'processed' => $processed,
                'message' => "$processed lembretes processados"
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    private function getTaskTypeLabel($type)
    {
        $labels = [
            'CALL' => 'Ligação',
            'MEETING' => 'Reunião',
            'EMAIL' => 'E-mail',
            'FOLLOW_UP' => 'Acompanhamento',
            'OTHER' => 'Outro'
        ];
        
        return $labels[$type] ?? 'Outro';
    }
}

