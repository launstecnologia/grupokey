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
                    <option value="CDC" <?= ($filters['produto'] ?? '') === 'CDC' ? 'selected' : '' ?>>CDC</option>
                    <option value="CDX_EVO" <?= ($filters['produto'] ?? '') === 'CDX_EVO' ? 'selected' : '' ?>>CDX/EVO</option>
                    <option value="GOOGLE" <?= ($filters['produto'] ?? '') === 'GOOGLE' ? 'selected' : '' ?>>Google</option>
                    <option value="MEMBRO_KEY" <?= ($filters['produto'] ?? '') === 'MEMBRO_KEY' ? 'selected' : '' ?>>Membro Key</option>
                    <option value="OUTROS" <?= ($filters['produto'] ?? '') === 'OUTROS' ? 'selected' : '' ?>>Outros</option>
                    <option value="PAGBANK" <?= ($filters['produto'] ?? '') === 'PAGBANK' ? 'selected' : '' ?>>PagBank</option>
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
                                    'OPEN' => 'bg-blue-100 text-blue-800',
                                    'IN_PROGRESS' => 'bg-cyan-100 text-cyan-800',
                                    'RESOLVED' => 'bg-green-100 text-green-800',
                                    'CLOSED' => 'bg-gray-100 text-gray-800'
                                ];
                                $statusLabels = [
                                    'OPEN' => 'Aberto',
                                    'IN_PROGRESS' => 'Em Andamento',
                                    'RESOLVED' => 'Resolvido',
                                    'CLOSED' => 'Fechado'
                                ];
                                $statusClass = $statusColors[$chamado['status']] ?? 'bg-gray-100 text-gray-800';
                                $statusLabel = $statusLabels[$chamado['status']] ?? $chamado['status'];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                                <?php
                                // Mapeamento de produtos para exibição amigável
                                $productMap = [
                                    'CDC' => 'CDC',
                                    'CDX_EVO' => 'CDX/EVO',
                                    'GOOGLE' => 'Google',
                                    'MEMBRO_KEY' => 'Membro Key',
                                    'OUTROS' => 'Outros',
                                    'PAGBANK' => 'PagBank',
                                    // Valores antigos para compatibilidade
                                    'PAGSEGURO' => 'CDX/EVO',
                                    'PAGSEGURO_MP' => 'CDX/EVO',
                                    'BRASILCARD' => 'CDC',
                                    'DIVERSOS' => 'Outros'
                                ];
                                $produtoDisplay = $productMap[$chamado['produto']] ?? $chamado['produto'];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($produtoDisplay) ?>
                                </span>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-cyan-100 text-cyan-800">
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
