<?php
$currentPage = 'crm';
ob_start();

$deal = $deal ?? [];
$activities = $activities ?? [];
?>

<div class="pt-6 px-4 max-w-6xl mx-auto">
    <div class="mb-6">
        <a href="<?= url('crm?pipeline_id=' . ($deal['pipeline_id'] ?? '')) ?>" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <i class="fas fa-arrow-left mr-2"></i>Voltar
        </a>
    </div>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($deal['title'] ?? '') ?></h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <span class="inline-block w-3 h-3 rounded-full mr-1" style="background-color: <?= htmlspecialchars($deal['stage_color'] ?? '#6B7280') ?>"></span>
                <?= htmlspecialchars($deal['stage_name'] ?? '') ?>
            </p>
        </div>
        <div>
            <a href="<?= url('crm/deals/' . ($deal['id'] ?? '') . '/edit') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Editar
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informações Principais -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informações</h2>
                
                <?php if (!empty($deal['description'])): ?>
                    <div class="mb-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Descrição</h3>
                        <p class="text-gray-900 dark:text-white"><?= nl2br(htmlspecialchars($deal['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <dl class="grid grid-cols-2 gap-4">
                    <?php if (!empty($deal['value'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Valor</dt>
                            <dd class="mt-1 text-lg font-bold text-blue-600 dark:text-blue-400">
                                R$ <?= number_format($deal['value'], 2, ',', '.') ?>
                            </dd>
                        </div>
                    <?php endif; ?>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Probabilidade</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            <?= $deal['probability'] ?? 0 ?>%
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-1">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $deal['probability'] ?? 0 ?>%"></div>
                            </div>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Prioridade</dt>
                        <dd class="mt-1">
                            <span class="text-xs px-2 py-1 rounded <?php
                                echo $deal['priority'] === 'URGENT' ? 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300' : '';
                                echo $deal['priority'] === 'HIGH' ? 'bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300' : '';
                                echo $deal['priority'] === 'MEDIUM' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300' : '';
                                echo $deal['priority'] === 'LOW' ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' : '';
                            ?>">
                                <?= $deal['priority'] === 'URGENT' ? 'Urgente' : ($deal['priority'] === 'HIGH' ? 'Alta' : ($deal['priority'] === 'MEDIUM' ? 'Média' : 'Baixa')) ?>
                            </span>
                        </dd>
                    </div>

                    <?php if (!empty($deal['expected_close_date'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Data Prevista</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <?= date('d/m/Y', strtotime($deal['expected_close_date'])) ?>
                            </dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($deal['establishment_name'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estabelecimento</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <?= htmlspecialchars($deal['establishment_name']) ?>
                            </dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($deal['representative_name'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Representante</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <?= htmlspecialchars($deal['representative_name']) ?>
                            </dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($deal['assigned_user_name'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Atribuído a</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <?= htmlspecialchars($deal['assigned_user_name']) ?>
                            </dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>

            <!-- Tarefas -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tarefas</h2>
                
                <form method="POST" action="<?= url('crm/deals/' . ($deal['id'] ?? '') . '/tasks') ?>" class="mb-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <?= csrf_field() ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Tarefa</label>
                            <select name="task_type" required 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white">
                                <option value="CALL">Ligação</option>
                                <option value="MEETING">Reunião</option>
                                <option value="EMAIL">E-mail</option>
                                <option value="FOLLOW_UP">Acompanhamento</option>
                                <option value="OTHER">Outro</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data e Hora *</label>
                            <input type="datetime-local" name="scheduled_at" required 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título *</label>
                        <input type="text" name="title" required placeholder="Ex: Ligar para cliente" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição</label>
                        <textarea name="description" rows="2" placeholder="Detalhes da tarefa..." 
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lembrete (minutos antes)</label>
                        <input type="number" name="reminder_minutes" value="15" min="0" max="1440" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Você receberá um email e notificação X minutos antes da tarefa</p>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Criar Tarefa
                    </button>
                </form>

                <div class="space-y-3">
                    <?php 
                    $tasks = $tasks ?? [];
                    $pendingTasks = array_filter($tasks, fn($t) => !$t['is_completed']);
                    $completedTasks = array_filter($tasks, fn($t) => $t['is_completed']);
                    ?>
                    
                    <?php if (empty($tasks)): ?>
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">Nenhuma tarefa criada</p>
                    <?php else: ?>
                        <!-- Tarefas Pendentes -->
                        <?php if (!empty($pendingTasks)): ?>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pendentes</h3>
                            <?php foreach ($pendingTasks as $task): ?>
                                <div class="border-l-4 pl-4 py-3 bg-gray-50 dark:bg-gray-700 rounded-r-lg" style="border-color: <?php
                                    echo $task['task_type'] === 'CALL' ? '#3B82F6' : '';
                                    echo $task['task_type'] === 'MEETING' ? '#F59E0B' : '';
                                    echo $task['task_type'] === 'EMAIL' ? '#10B981' : '';
                                    echo $task['task_type'] === 'FOLLOW_UP' ? '#8B5CF6' : '#6B7280';
                                ?>">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h4 class="font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($task['title']) ?></h4>
                                                <span class="text-xs px-2 py-1 rounded bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300">
                                                    <?= $task['task_type'] === 'CALL' ? 'Ligação' : ($task['task_type'] === 'MEETING' ? 'Reunião' : ($task['task_type'] === 'EMAIL' ? 'E-mail' : ($task['task_type'] === 'FOLLOW_UP' ? 'Acompanhamento' : 'Outro'))) ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($task['description'])): ?>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1"><?= nl2br(htmlspecialchars($task['description'])) ?></p>
                                            <?php endif; ?>
                                            <p class="text-xs text-gray-500 dark:text-gray-500">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($task['scheduled_at'])) ?>
                                                <?php if ($task['reminder_minutes'] > 0): ?>
                                                    <span class="ml-2">
                                                        <i class="fas fa-bell mr-1"></i>
                                                        Lembrete: <?= $task['reminder_minutes'] ?> min antes
                                                    </span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="flex gap-2 ml-4">
                                            <button onclick="completeTask(<?= $task['id'] ?>)" 
                                                    class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300" 
                                                    title="Marcar como concluída">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <form method="POST" action="<?= url('crm/tasks/' . $task['id']) ?>" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta tarefa?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Tarefas Concluídas -->
                        <?php if (!empty($completedTasks)): ?>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 mt-4">Concluídas</h3>
                            <?php foreach ($completedTasks as $task): ?>
                                <div class="border-l-4 pl-4 py-3 bg-gray-50 dark:bg-gray-700 rounded-r-lg opacity-60" style="border-color: #10B981;">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h4 class="font-semibold text-gray-900 dark:text-white line-through"><?= htmlspecialchars($task['title']) ?></h4>
                                                <span class="text-xs px-2 py-1 rounded bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300">
                                                    Concluída
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-500">
                                                Concluída em <?= !empty($task['completed_at']) ? date('d/m/Y H:i', strtotime($task['completed_at'])) : date('d/m/Y H:i', strtotime($task['updated_at'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Atividades -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Atividades</h2>
                
                <form method="POST" action="<?= url('crm/deals/' . ($deal['id'] ?? '') . '/activities') ?>" class="mb-4">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <select name="activity_type" required 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="NOTE">Nota</option>
                            <option value="CALL">Ligação</option>
                            <option value="EMAIL">E-mail</option>
                            <option value="MEETING">Reunião</option>
                            <option value="TASK">Tarefa</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="title" placeholder="Título (opcional)" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="mb-2">
                        <textarea name="description" rows="3" placeholder="Descrição..." required 
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Adicionar Atividade
                    </button>
                </form>

                <div class="space-y-4">
                    <?php if (empty($activities)): ?>
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">Nenhuma atividade registrada</p>
                    <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                            <div class="border-l-4 pl-4 py-2" style="border-color: <?php
                                echo $activity['activity_type'] === 'CALL' ? '#3B82F6' : '';
                                echo $activity['activity_type'] === 'EMAIL' ? '#10B981' : '';
                                echo $activity['activity_type'] === 'MEETING' ? '#F59E0B' : '';
                                echo $activity['activity_type'] === 'TASK' ? '#8B5CF6' : '#6B7280';
                            ?>">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <?php if (!empty($activity['title'])): ?>
                                            <h4 class="font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($activity['title']) ?></h4>
                                        <?php endif; ?>
                                        <p class="text-sm text-gray-600 dark:text-gray-400"><?= nl2br(htmlspecialchars($activity['description'] ?? '')) ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                            <?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?>
                                            <?php if (!empty($activity['created_by_user_name'])): ?>
                                                por <?= htmlspecialchars($activity['created_by_user_name']) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        <?= $activity['activity_type'] === 'CALL' ? 'Ligação' : ($activity['activity_type'] === 'EMAIL' ? 'E-mail' : ($activity['activity_type'] === 'MEETING' ? 'Reunião' : ($activity['activity_type'] === 'TASK' ? 'Tarefa' : 'Nota'))) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ações</h3>
                <div class="space-y-2">
                    <a href="<?= url('crm/deals/' . ($deal['id'] ?? '') . '/edit') ?>" class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-center">
                        <i class="fas fa-edit mr-2"></i>Editar
                    </a>
                    <form method="POST" action="<?= url('crm/deals/' . ($deal['id'] ?? '')) ?>" onsubmit="return confirm('Tem certeza que deseja excluir este negócio?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="block w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-trash mr-2"></i>Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function completeTask(taskId) {
    if (!confirm('Marcar esta tarefa como concluída?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('_token', '<?= csrf_token() ?>');
    
    fetch('<?= url('crm/tasks') ?>/' + taskId + '/complete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao concluir tarefa: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao concluir tarefa');
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
?>

