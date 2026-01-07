<?php
$currentPage = 'crm';
ob_start();

$pipelines = $pipelines ?? [];
$currentPipeline = $currentPipeline ?? null;
$stages = $stages ?? [];
$dealsByStage = $dealsByStage ?? [];
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-tasks mr-2"></i>
                CRM - Kanban
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Gerencie seus negócios e oportunidades</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= url('crm/pipelines') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-cog mr-2"></i>
                Configurar
            </a>
            <a href="<?= url('crm/deals/create?pipeline_id=' . ($currentPipeline['id'] ?? '')) ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Novo Negócio
            </a>
        </div>
    </div>

    <!-- Mensagens -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Seletor de Pipeline -->
    <?php if (!empty($pipelines)): ?>
    <div class="bg-gray-700 dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex gap-2 flex-wrap">
            <?php foreach ($pipelines as $pipeline): ?>
                <a href="<?= url('crm?pipeline_id=' . $pipeline['id']) ?>" 
                   class="px-4 py-2 rounded-lg transition-colors <?= ($currentPipeline && $currentPipeline['id'] == $pipeline['id']) ? 'bg-blue-600 text-white' : 'bg-gray-700 dark:bg-gray-700 text-gray-300 dark:text-gray-300 hover:bg-gray-600 dark:hover:bg-gray-600' ?>">
                    <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: <?= htmlspecialchars($pipeline['color']) ?>"></span>
                    <?= htmlspecialchars($pipeline['name']) ?>
                    <?php if ($pipeline['is_default']): ?>
                        <span class="text-xs ml-1">(Padrão)</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Kanban Board -->
    <?php if ($currentPipeline && !empty($stages)): ?>
    <div class="overflow-x-auto pb-4">
        <div class="flex gap-4 min-w-max" id="kanban-board">
            <?php foreach ($stages as $stage): ?>
                <div class="flex-shrink-0 w-80" data-stage-id="<?= $stage['id'] ?>">
                    <!-- Header do Stage -->
                    <div class="bg-gray-700 dark:bg-gray-800 rounded-t-lg shadow-sm p-4 border-b-2" style="border-color: <?= htmlspecialchars($stage['color']) ?>">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-3 h-3 rounded-full flex-shrink-0 self-center" style="background-color: <?= htmlspecialchars($stage['color']) ?>"></span>
                                <h3 class="font-semibold text-white dark:text-white m-0 leading-tight"><?= htmlspecialchars($stage['name']) ?></h3>
                                <span class="stage-count text-sm text-gray-300 dark:text-gray-400 whitespace-nowrap self-center" data-stage-id="<?= $stage['id'] ?>">(<?= count($dealsByStage[$stage['id']] ?? []) ?>)</span>
                            </div>
                            <?php if ($stage['is_final']): ?>
                                <span class="text-xs px-2 py-1 rounded <?= $stage['name'] === 'Ganho' ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300' ?>">
                                    <?= $stage['name'] === 'Ganho' ? 'Final' : 'Perdido' ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Cards do Stage -->
                    <div class="bg-gray-700 dark:bg-gray-900 rounded-b-lg p-3 min-h-[500px] space-y-3" 
                         data-stage-id="<?= $stage['id'] ?>" 
                         id="stage-<?= $stage['id'] ?>">
                        <?php if (isset($dealsByStage[$stage['id']])): ?>
                            <?php foreach ($dealsByStage[$stage['id']] as $deal): ?>
                                <div class="bg-gray-700 dark:bg-gray-800 rounded-lg shadow-sm p-4 cursor-move hover:shadow-md transition-shadow deal-card" 
                                     data-deal-id="<?= $deal['id'] ?>"
                                     data-stage-id="<?= $stage['id'] ?>"
                                     draggable="true">
                                    <div class="flex justify-between items-start mb-2">
                                        <a href="<?= url('crm/deals/' . $deal['id']) ?>" class="font-semibold text-white dark:text-white hover:text-blue-400 dark:hover:text-blue-400 flex-1">
                                            <?= htmlspecialchars($deal['title']) ?>
                                        </a>
                                        <span class="text-xs px-2 py-1 rounded <?php
                                            echo $deal['priority'] === 'URGENT' ? 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300' : '';
                                            echo $deal['priority'] === 'HIGH' ? 'bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300' : '';
                                            echo $deal['priority'] === 'MEDIUM' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300' : '';
                                            echo $deal['priority'] === 'LOW' ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' : '';
                                        ?>">
                                            <?= $deal['priority'] === 'URGENT' ? 'Urgente' : ($deal['priority'] === 'HIGH' ? 'Alta' : ($deal['priority'] === 'MEDIUM' ? 'Média' : 'Baixa')) ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (!empty($deal['description'])): ?>
                                        <p class="text-sm text-gray-300 dark:text-gray-400 mb-2 line-clamp-2">
                                            <?= htmlspecialchars(substr($deal['description'], 0, 100)) ?><?= strlen($deal['description']) > 100 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($deal['value'])): ?>
                                        <div class="text-lg font-bold text-blue-400 dark:text-blue-400 mb-2">
                                            R$ <?= number_format($deal['value'], 2, ',', '.') ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex items-center justify-between text-xs text-gray-300 dark:text-gray-400">
                                        <?php if (!empty($deal['establishment_name'])): ?>
                                            <span class="flex items-center">
                                                <i class="fas fa-building mr-1"></i>
                                                <?= htmlspecialchars($deal['establishment_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($deal['assigned_user_name'])): ?>
                                            <span class="flex items-center">
                                                <i class="fas fa-user mr-1"></i>
                                                <?= htmlspecialchars($deal['assigned_user_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($deal['expected_close_date'])): ?>
                                        <div class="mt-2 text-xs text-gray-300 dark:text-gray-400">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Fechamento: <?= date('d/m/Y', strtotime($deal['expected_close_date'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($deal['probability'] > 0): ?>
                                        <div class="mt-2">
                                            <div class="flex justify-between text-xs mb-1">
                                                <span class="text-gray-300 dark:text-gray-400">Probabilidade</span>
                                                <span class="font-semibold text-white"><?= $deal['probability'] ?>%</span>
                                            </div>
                                            <div class="w-full bg-gray-700 dark:bg-gray-700 rounded-full h-2">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $deal['probability'] ?>%"></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
        <div class="bg-gray-700 dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center">
            <i class="fas fa-inbox text-4xl text-gray-400 dark:text-gray-600 mb-4"></i>
            <p class="text-gray-600 dark:text-gray-400">
                <?php if (empty($pipelines)): ?>
                    Nenhum pipeline configurado. <a href="<?= url('crm/pipelines') ?>" class="text-blue-600 dark:text-blue-400 hover:underline">Configure um pipeline</a> para começar.
                <?php else: ?>
                    Nenhum stage configurado para este pipeline. <a href="<?= url('crm/pipelines/' . ($currentPipeline['id'] ?? '') . '/stages') ?>" class="text-blue-600 dark:text-blue-400 hover:underline">Configure os stages</a>.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
// Drag and Drop para Kanban
document.addEventListener('DOMContentLoaded', function() {
    const dealCards = document.querySelectorAll('.deal-card');
    // Selecionar apenas os containers de cards (não o container completo da coluna)
    const stageContainers = document.querySelectorAll('[id^="stage-"]');
    
    let draggedElement = null;
    let draggedStageId = null;
    
    // Eventos de drag
    dealCards.forEach(card => {
        card.addEventListener('dragstart', function(e) {
            draggedElement = this;
            draggedStageId = this.dataset.stageId;
            this.style.opacity = '0.5';
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        
        card.addEventListener('dragend', function(e) {
            this.style.opacity = '1';
            this.classList.remove('dragging');
        });
    });
    
    // Eventos de drop - apenas nos containers de cards
    stageContainers.forEach(container => {
        container.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.dataTransfer.dropEffect = 'move';
            
            // Verificar se o mouse está dentro dos limites do container
            const rect = this.getBoundingClientRect();
            const y = e.clientY;
            
            // Só permitir se estiver dentro da área do container
            if (y < rect.top || y > rect.bottom) {
                return;
            }
            
            const afterElement = getDragAfterElement(this, y);
            
            if (afterElement == null) {
                this.appendChild(draggedElement);
            } else {
                this.insertBefore(draggedElement, afterElement);
            }
        });
        
        container.addEventListener('dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // Adicionar indicador visual
            this.classList.add('drag-over');
        });
        
        container.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // Verificar se realmente saiu do container
            const rect = this.getBoundingClientRect();
            const x = e.clientX;
            const y = e.clientY;
            
            if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
                this.classList.remove('drag-over');
            }
        });
        
        container.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('drag-over');
            
            if (!draggedElement) return;
            
            // Verificar se o drop está dentro dos limites do container
            const rect = this.getBoundingClientRect();
            const y = e.clientY;
            
            if (y < rect.top || y > rect.bottom) {
                // Se soltou fora, restaurar posição original
                location.reload();
                return;
            }
            
            const newStageId = this.dataset.stageId || this.id.replace('stage-', '');
            const oldStageId = draggedStageId;
            
            if (newStageId && newStageId !== oldStageId) {
                // Calcular nova ordem
                const cards = Array.from(this.querySelectorAll('.deal-card'));
                const newIndex = cards.indexOf(draggedElement);
                
                // Enviar requisição para mover
                const formData = new FormData();
                formData.append('deal_id', draggedElement.dataset.dealId);
                formData.append('stage_id', newStageId);
                formData.append('sort_order', newIndex);
                formData.append('_token', '<?= csrf_token() ?>');
                
                fetch('<?= url('crm/move-deal') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        draggedElement.dataset.stageId = newStageId;
                        // Atualizar contador do stage
                        updateStageCount(oldStageId);
                        updateStageCount(newStageId);
                    } else {
                        alert('Erro ao mover negócio: ' + (data.message || 'Erro desconhecido'));
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao mover negócio');
                    location.reload();
                });
            } else if (newStageId === oldStageId) {
                // Mesmo stage, apenas reordenar
                const cards = Array.from(this.querySelectorAll('.deal-card'));
                const newIndex = cards.indexOf(draggedElement);
                
                const formData = new FormData();
                formData.append('deal_id', draggedElement.dataset.dealId);
                formData.append('stage_id', newStageId);
                formData.append('sort_order', newIndex);
                formData.append('_token', '<?= csrf_token() ?>');
                
                fetch('<?= url('crm/move-deal') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    location.reload();
                });
            }
        });
    });
    
    // Prevenir drop em qualquer lugar fora dos containers
    document.addEventListener('dragover', function(e) {
        e.preventDefault();
    });
    
    document.addEventListener('drop', function(e) {
        e.preventDefault();
        // Se soltou fora de qualquer container, recarregar para restaurar
        if (draggedElement) {
            location.reload();
        }
    });
    
    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.deal-card:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    
    function updateStageCount(stageId) {
        const stageContainer = document.querySelector(`#stage-${stageId}`);
        if (stageContainer) {
            const count = stageContainer.querySelectorAll('.deal-card').length;
            const countElement = document.querySelector(`.stage-count[data-stage-id="${stageId}"]`);
            if (countElement) {
                countElement.textContent = `(${count})`;
            }
        }
    }
});
</script>

<style>
.drag-over {
    background-color: rgba(59, 130, 246, 0.1) !important;
    border: 2px dashed #3b82f6 !important;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

