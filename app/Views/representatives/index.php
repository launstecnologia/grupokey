<?php
$currentPage = 'representantes';
ob_start();

// Definir variáveis com valores padrão para evitar warnings
$stats = $stats ?? ['total' => 0, 'ativos' => 0, 'inativos' => 0, 'cadastros_ultimo_mes' => 0];
$representatives = $representatives ?? [];
$filters = $filters ?? [];
?>

<div class="pt-6 px-4">
    <!-- Header com ações -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-users mr-2"></i>
                Representantes
            </h1>
            <p class="text-gray-600 mt-1">Gerencie todos os representantes cadastrados</p>
        </div>
        <div>
            <a href="<?= url('representantes/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Novo Representante
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
                        <i class="fas fa-users text-blue-600"></i>
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

        <!-- Ativos -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-user-check text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Ativos</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['ativos'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Inativos -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-user-times text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Inativos</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['inativos'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Último Mês -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-calendar text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Último Mês</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['cadastros_ultimo_mes'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
        <form method="GET" action="<?= url('representantes') ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos os status</option>
                    <option value="ACTIVE" <?= ($filters['status'] ?? '') === 'ACTIVE' ? 'selected' : '' ?>>Ativo</option>
                    <option value="INACTIVE" <?= ($filters['status'] ?? '') === 'INACTIVE' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <!-- Cidade -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                <input type="text" name="cidade" value="<?= htmlspecialchars($filters['cidade'] ?? '') ?>" placeholder="Digite a cidade" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Buscar -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Nome ou email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
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
                <a href="<?= url('representantes') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-times mr-1"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Representantes -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-list mr-2"></i>
                Lista de Representantes
            </h3>
        </div>

        <?php if (empty($representatives)): ?>
            <div class="p-12 text-center">
                <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum representante encontrado</h3>
                <p class="text-gray-500 mb-6">
                    <?php if (!empty($filters) && array_filter($filters)): ?>
                        Nenhum representante corresponde aos filtros aplicados.<br>
                        Tente ajustar os filtros ou limpar a busca.
                    <?php else: ?>
                        Ainda não há representantes cadastrados no sistema.<br>
                        Cadastre o primeiro representante para começar.
                    <?php endif; ?>
                </p>
                <div class="flex justify-center gap-3">
                    <?php if (!empty($filters) && array_filter($filters)): ?>
                        <a href="<?= url('representantes') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                            <i class="fas fa-times mr-2"></i>
                            Limpar Filtros
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('representantes/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg inline-flex items-center transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Cadastrar Representante
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Representante</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($representatives as $representative): ?>
                        <tr class="bg-white hover:bg-blue-200 transition-colors cursor-pointer">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($representative['nome_completo']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        CPF: <?= format_cpf($representative['cpf']) ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="mb-1">
                                        <i class="fas fa-envelope mr-1"></i>
                                        <?= htmlspecialchars($representative['email']) ?>
                                    </div>
                                    <div>
                                        <i class="fas fa-phone mr-1"></i>
                                        <?= format_phone($representative['telefone']) ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?= htmlspecialchars($representative['cidade']) ?>/<?= htmlspecialchars($representative['uf']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'ACTIVE' => 'bg-green-100 text-green-800',
                                    'INACTIVE' => 'bg-gray-100 text-gray-800'
                                ];
                                $statusLabels = [
                                    'ACTIVE' => 'Ativo',
                                    'INACTIVE' => 'Inativo'
                                ];
                                $statusClass = $statusColors[$representative['status']] ?? 'bg-gray-100 text-gray-800';
                                $statusLabel = $statusLabels[$representative['status']] ?? $representative['status'];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= format_date($representative['created_at'], 'd/m/Y') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex space-x-2 justify-end">
                                    <a href="<?= url('representantes/' . $representative['id']) ?>" class="text-blue-600 hover:text-blue-900" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= url('representantes/' . $representative['id'] . '/edit') ?>" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="text-cyan-600 hover:text-cyan-900 btn-reset-password" 
                                            data-id="<?= $representative['id'] ?>" 
                                            data-name="<?= htmlspecialchars($representative['nome_completo']) ?>"
                                            title="Resetar Senha">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button type="button" class="text-orange-600 hover:text-orange-900 btn-toggle-status" 
                                            data-id="<?= $representative['id'] ?>" 
                                            data-name="<?= htmlspecialchars($representative['nome_completo']) ?>"
                                            data-status="<?= $representative['status'] ?>"
                                            title="<?= $representative['status'] === 'ACTIVE' ? 'Desativar' : 'Ativar' ?>">
                                        <i class="fas fa-<?= $representative['status'] === 'ACTIVE' ? 'pause' : 'play' ?>"></i>
                                    </button>
                                    <button type="button" class="text-red-600 hover:text-red-900 btn-delete" 
                                            data-id="<?= $representative['id'] ?>" 
                                            data-name="<?= htmlspecialchars($representative['nome_completo']) ?>"
                                            title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
        const name = button.dataset.name;
        
        if (confirm(`Tem certeza que deseja excluir o representante "${name}"?`)) {
            // Criar formulário para exclusão
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('representantes') ?>/' + id;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Resetar senha
    if (e.target.closest('.btn-reset-password')) {
        const button = e.target.closest('.btn-reset-password');
        const id = button.dataset.id;
        const name = button.dataset.name;
        
        if (confirm(`Tem certeza que deseja resetar a senha do representante "${name}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('representantes') ?>/' + id + '/reset-password';
            
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Toggle status
    if (e.target.closest('.btn-toggle-status')) {
        const button = e.target.closest('.btn-toggle-status');
        const id = button.dataset.id;
        const name = button.dataset.name;
        const status = button.dataset.status;
        const action = status === 'ACTIVE' ? 'desativar' : 'ativar';
        
        if (confirm(`Tem certeza que deseja ${action} o representante "${name}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('representantes') ?>/' + id + '/toggle-status';
            
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
