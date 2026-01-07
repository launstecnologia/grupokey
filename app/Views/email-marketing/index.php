<?php
$currentPage = 'email-marketing';
ob_start();

$campaigns = $campaigns ?? [];
$filters = $filters ?? [];
?>

<div class="pt-6 px-4">
    <!-- Header com ações -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-envelope mr-2"></i>
                E-mail Marketing
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Gerencie suas campanhas de e-mail</p>
        </div>
        <div>
            <a href="<?= url('email-marketing/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Nova Campanha
            </a>
            <a href="<?= url('email-marketing/process-queue') ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors ml-2">
                <i class="fas fa-paper-plane mr-2"></i>
                Processar Fila
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

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" action="<?= url('email-marketing') ?>" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                       placeholder="Nome ou assunto da campanha..."
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Todos</option>
                    <option value="DRAFT" <?= ($filters['status'] ?? '') === 'DRAFT' ? 'selected' : '' ?>>Rascunho</option>
                    <option value="SCHEDULED" <?= ($filters['status'] ?? '') === 'SCHEDULED' ? 'selected' : '' ?>>Agendada</option>
                    <option value="SENDING" <?= ($filters['status'] ?? '') === 'SENDING' ? 'selected' : '' ?>>Enviando</option>
                    <option value="COMPLETED" <?= ($filters['status'] ?? '') === 'COMPLETED' ? 'selected' : '' ?>>Concluída</option>
                    <option value="PAUSED" <?= ($filters['status'] ?? '') === 'PAUSED' ? 'selected' : '' ?>>Pausada</option>
                    <option value="CANCELLED" <?= ($filters['status'] ?? '') === 'CANCELLED' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="<?= url('email-marketing') ?>" class="bg-black hover:bg-gray-900 dark:bg-black dark:hover:bg-gray-800 text-white px-4 py-2 rounded-lg transition-colors">
                Limpar
            </a>
        </form>
    </div>

    <!-- Lista de Campanhas -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assunto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Destinatários</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Progresso</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Criado em</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (empty($campaigns)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            Nenhuma campanha encontrada
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($campaigns as $campaign): ?>
                        <?php
                        $total = $campaign['total_recipients'] ?? 0;
                        $sent = $campaign['sent_count'] ?? 0;
                        $failed = $campaign['failed_count'] ?? 0;
                        $progress = $total > 0 ? round(($sent / $total) * 100) : 0;
                        
                        $statusColors = [
                            'DRAFT' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                            'SCHEDULED' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                            'SENDING' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                            'COMPLETED' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                            'PAUSED' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                            'CANCELLED' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                        ];
                        
                        $statusLabels = [
                            'DRAFT' => 'Rascunho',
                            'SCHEDULED' => 'Agendada',
                            'SENDING' => 'Enviando',
                            'COMPLETED' => 'Concluída',
                            'PAUSED' => 'Pausada',
                            'CANCELLED' => 'Cancelada'
                        ];
                        ?>
                        <tr class="hover:bg-gray-900 dark:hover:bg-black cursor-pointer transition-colors" onclick="window.location.href='<?= url('email-marketing/' . $campaign['id']) ?>'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($campaign['name']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($campaign['subject']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusColors[$campaign['status']] ?? $statusColors['DRAFT'] ?>">
                                    <?= $statusLabels[$campaign['status']] ?? $campaign['status'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?= $total ?> destinatário(s)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $progress ?>%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">
                                        <?= $sent ?>/<?= $total ?> (<?= $progress ?>%)
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?= date('d/m/Y H:i', strtotime($campaign['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation()">
                                <a href="<?= url('email-marketing/' . $campaign['id']) ?>" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($campaign['status'] === 'DRAFT' || $campaign['status'] === 'PAUSED'): ?>
                                    <a href="<?= url('email-marketing/' . $campaign['id'] . '/recipients') ?>" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3" onclick="event.stopPropagation()" title="Selecionar destinatários">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <a href="<?= url('email-marketing/' . $campaign['id'] . '/edit') ?>" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300 mr-3" onclick="event.stopPropagation()" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($campaign['status'] === 'SENDING'): ?>
                                    <form method="POST" action="<?= url('email-marketing/' . $campaign['id'] . '/pause') ?>" class="inline" onsubmit="event.stopPropagation(); return confirm('Deseja pausar esta campanha?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300 mr-3" title="Pausar">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" action="<?= url('email-marketing/' . $campaign['id']) ?>" class="inline" onsubmit="event.stopPropagation(); return confirm('Tem certeza que deseja deletar esta campanha?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Deletar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

