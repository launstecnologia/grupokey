<?php
use App\Core\Auth;
$currentPage = 'chamados';
ob_start();

// Definir variáveis com valores padrão para evitar warnings
$stats = $stats ?? ['total' => 0, 'abertos' => 0, 'em_andamento' => 0, 'fechados' => 0];
$chamados = $chamados ?? [];
$filters = $filters ?? [];
?>

<div class="pt-6 px-4">
    <!-- Header com ações -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-headset mr-2"></i>
                Chamados
            </h1>
            <p class="text-gray-600 mt-1">
                <?php if (Auth::isAdmin()): ?>
                    Gerencie todos os chamados do sistema
                <?php else: ?>
                    Meus chamados de suporte
                <?php endif; ?>
            </p>
        </div>
        <div>
            <a href="<?= url('chamados/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Novo Chamado
            </a>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <!-- Total -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-headset text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['total'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Abertos -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Abertos</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['abertos'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Em Andamento -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-cyan-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-cog text-cyan-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Em Andamento</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['em_andamento'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Fechados -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Fechados</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['fechados'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
        <form method="GET" action="<?= url('chamados') ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos os status</option>
                    <option value="OPEN" <?= ($filters['status'] ?? '') === 'OPEN' ? 'selected' : '' ?>>Aberto</option>
                    <option value="IN_PROGRESS" <?= ($filters['status'] ?? '') === 'IN_PROGRESS' ? 'selected' : '' ?>>Em Andamento</option>
                    <option value="CLOSED" <?= ($filters['status'] ?? '') === 'CLOSED' ? 'selected' : '' ?>>Fechado</option>
                </select>
            </div>

            <!-- Produto -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Produto</label>
                <select name="produto" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos os produtos</option>
                    <?php foreach (($productOptions ?? []) as $productValue => $productLabel): ?>
                        <option value="<?= htmlspecialchars($productValue) ?>" <?= ($filters['produto'] ?? '') === $productValue ? 'selected' : '' ?>>
                            <?= htmlspecialchars($productLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buscar -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Título ou descrição" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Data Inicial -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
                <input type="date" name="date_from" value="<?= $filters['date_from'] ?? '' ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Data Final -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
                <input type="date" name="date_to" value="<?= $filters['date_to'] ?? '' ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Botões -->
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md mr-2">
                    <i class="fas fa-search mr-1"></i>
                    Filtrar
                </button>
                <a href="<?= url('chamados') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-times mr-1"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Chamados -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-list mr-2"></i>
                Lista de Chamados
            </h3>
        </div>

        <?php if (empty($chamados)): ?>
            <div class="p-12 text-center">
                <i class="fas fa-headset text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum chamado encontrado</h3>
                <p class="text-gray-500 mb-6">
                    <?php if (!empty($filters) && array_filter($filters)): ?>
                        Nenhum chamado corresponde aos filtros aplicados.<br>
                        Tente ajustar os filtros ou limpar a busca.
                    <?php else: ?>
                        Ainda não há chamados cadastrados.<br>
                        Crie o primeiro chamado para começar.
                    <?php endif; ?>
                </p>
                <div class="flex justify-center gap-3">
                    <?php if (!empty($filters) && array_filter($filters)): ?>
                        <a href="<?= url('chamados') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                            <i class="fas fa-times mr-2"></i>
                            Limpar Filtros
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('chamados/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg inline-flex items-center transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Criar Chamado
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($chamados as $chamado): ?>
                <div class="px-6 py-4 hover:bg-blue-200 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($chamado['assunto']) ?>
                                </h3>
                                <?php
                                $statusColors = [
                                    'OPEN' => 'ticket-badge ticket-badge-status-open',
                                    'IN_PROGRESS' => 'ticket-badge ticket-badge-status-progress',
                                    'RESOLVED' => 'ticket-badge ticket-badge-status-resolved',
                                    'CLOSED' => 'ticket-badge ticket-badge-status-closed'
                                ];
                                $statusLabels = [
                                    'OPEN' => 'Aberto',
                                    'IN_PROGRESS' => 'Em Andamento',
                                    'RESOLVED' => 'Resolvido',
                                    'CLOSED' => 'Fechado'
                                ];
                                $statusClass = $statusColors[$chamado['status']] ?? 'ticket-badge ticket-badge-status-default';
                                $statusLabel = $statusLabels[$chamado['status']] ?? $chamado['status'];
                                ?>
                                <span class="<?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                                <?php
                                $productMap = $productOptions ?? [];
                                $productMap['PAGBANK'] = 'PagSeguro';
                                $productMap['PAGSEGURO'] = 'PagSeguro';
                                $productMap['PAGSEGURO_MP'] = 'CDX/EVO';
                                $productMap['BRASILCARD'] = 'CDC';
                                $productMap['DIVERSOS'] = 'Outros';
                                $produtoDisplay = $productMap[$chamado['produto']] ?? ucwords(str_replace('_', ' ', strtolower((string) $chamado['produto'])));
                                ?>
                                <span class="ticket-badge ticket-badge-product">
                                    <?= htmlspecialchars($produtoDisplay) ?>
                                </span>
                                <span class="ticket-badge ticket-badge-comments">
                                    <i class="fas fa-comments mr-1"></i>
                                    <?= $chamado['total_respostas'] ?? 0 ?>
                                </span>
                                <?php if (Auth::isAdmin()): ?>
                                <span class="text-xs text-gray-500">
                                    <?= htmlspecialchars($chamado['representative_nome'] ?? 'N/A') ?>
                                </span>
                                <?php endif; ?>
                                <span class="text-xs text-gray-500">
                                    <?= format_date($chamado['created_at'], 'd/m/Y H:i') ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 flex-shrink-0">
                            <a href="<?= url('chamados/' . $chamado['id']) ?>" class="text-blue-600 hover:text-blue-900" title="Visualizar">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php 
                            $currentUserId = Auth::isRepresentative() ? Auth::representative()['id'] : (Auth::isAdmin() ? Auth::user()['id'] : null);
                            if ($chamado['status'] === 'OPEN' && 
                                (Auth::isAdmin() || $chamado['created_by_representative_id'] == $currentUserId)): ?>
                            <a href="<?= url('chamados/' . $chamado['id'] . '/edit') ?>" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (Auth::isAdmin() && $chamado['status'] !== 'CLOSED'): ?>
                            <button type="button" class="text-green-600 hover:text-green-900 btn-fechar-chamado" 
                                    data-id="<?= $chamado['id'] ?>" 
                                    data-titulo="<?= htmlspecialchars($chamado['assunto']) ?>"
                                    title="Fechar Chamado">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php endif; ?>
                            <?php 
                            if ($chamado['status'] === 'OPEN' && 
                                (Auth::isAdmin() || $chamado['created_by_representative_id'] == $currentUserId)): ?>
                            <button type="button" class="text-red-600 hover:text-red-900 btn-delete" 
                                    data-id="<?= $chamado['id'] ?>" 
                                    data-titulo="<?= htmlspecialchars($chamado['assunto']) ?>"
                                    title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.ticket-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.7rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    line-height: 1.1rem;
    font-weight: 700;
    border: 1px solid transparent;
}

.ticket-badge-status-open {
    background: #dbeafe;
    color: #1e40af;
    border-color: #93c5fd;
}

.ticket-badge-status-progress {
    background: #cffafe;
    color: #155e75;
    border-color: #67e8f9;
}

.ticket-badge-status-resolved {
    background: #dcfce7;
    color: #166534;
    border-color: #86efac;
}

.ticket-badge-status-closed {
    background: #e2e8f0;
    color: #334155;
    border-color: #cbd5e1;
}

.ticket-badge-status-default {
    background: #e5e7eb;
    color: #1f2937;
    border-color: #d1d5db;
}

.ticket-badge-product {
    background: #dbeafe;
    color: #1e3a8a;
    border-color: #93c5fd;
}

.ticket-badge-comments {
    background: #ccfbf1;
    color: #0f766e;
    border-color: #99f6e4;
}

.dark .ticket-badge-status-open {
    background: #1e3a8a;
    color: #dbeafe;
    border-color: #3b82f6;
}

.dark .ticket-badge-status-progress {
    background: #164e63;
    color: #cffafe;
    border-color: #06b6d4;
}

.dark .ticket-badge-status-resolved {
    background: #14532d;
    color: #dcfce7;
    border-color: #22c55e;
}

.dark .ticket-badge-status-closed {
    background: #374151;
    color: #f3f4f6;
    border-color: #6b7280;
}

.dark .ticket-badge-status-default {
    background: #374151;
    color: #f3f4f6;
    border-color: #6b7280;
}

.dark .ticket-badge-product {
    background: #1e3a8a;
    color: #dbeafe;
    border-color: #2563eb;
}

.dark .ticket-badge-comments {
    background: #134e4a;
    color: #ccfbf1;
    border-color: #14b8a6;
}
</style>

<script>
// Confirmar exclusão
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-delete')) {
        const button = e.target.closest('.btn-delete');
        const id = button.dataset.id;
        const titulo = button.dataset.titulo;
        
        if (confirm(`Tem certeza que deseja excluir o chamado "${titulo}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('chamados') ?>/' + id;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Fechar chamado
    if (e.target.closest('.btn-fechar-chamado')) {
        const button = e.target.closest('.btn-fechar-chamado');
        const id = button.dataset.id;
        const titulo = button.dataset.titulo;
        
        if (confirm(`Tem certeza que deseja fechar o chamado "${titulo}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('chamados') ?>/' + id + '/fechar';
            
            document.body.appendChild(form);
            form.submit();
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
