<?php
$currentPage = 'billing';

// Definir variáveis com valores padrão para evitar warnings
$stats = $stats ?? ['total' => 0, 'processando' => 0, 'concluido' => 0, 'erro' => 0];
$reports = $reports ?? [];
$filters = $filters ?? [];
?>

<div class="pt-6 px-4">
    <!-- Mensagens de Sucesso/Erro -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success_message']) ?></span>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error_message']) ?></span>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
    
    <!-- Header com ações -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-file-excel mr-2"></i>
                Relatórios de Faturamento
            </h1>
            <p class="text-gray-600 mt-1">Gerencie todos os relatórios de faturamento</p>
        </div>
        <div>
            <a href="<?= url('billing/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-upload mr-2"></i>
                Upload de Relatório
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
                        <i class="fas fa-file-excel text-blue-600"></i>
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

        <!-- Processando -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Processando</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['processando'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Concluído -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Concluído</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['concluido'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Erro -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Erro</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['erro'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
        <form method="GET" action="<?= url('billing') ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos os status</option>
                    <option value="PROCESSING" <?= ($filters['status'] ?? '') === 'PROCESSING' ? 'selected' : '' ?>>Processando</option>
                    <option value="COMPLETED" <?= ($filters['status'] ?? '') === 'COMPLETED' ? 'selected' : '' ?>>Concluído</option>
                    <option value="ERROR" <?= ($filters['status'] ?? '') === 'ERROR' ? 'selected' : '' ?>>Erro</option>
                </select>
            </div>

            <!-- Data Inicial -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Data Final -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Botões -->
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md mr-2">
                    <i class="fas fa-search mr-1"></i>
                    Filtrar
                </button>
                <a href="<?= url('billing') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-times mr-1"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Relatórios -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-list mr-2"></i>
                Lista de Relatórios
            </h3>
        </div>

        <?php if (empty($reports)): ?>
            <div class="p-12 text-center">
                <i class="fas fa-file-excel text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum relatório encontrado</h3>
                <p class="text-gray-500 mb-6">Faça upload de um arquivo Excel para começar.</p>
                <a href="<?= url('billing/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg inline-flex items-center transition-colors">
                    <i class="fas fa-upload mr-2"></i>
                    Upload de Relatório
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registros</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TPV Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Markup Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($reports as $report): ?>
                        <tr class="bg-white hover:bg-blue-200 transition-colors cursor-pointer">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($report['title']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?= htmlspecialchars($report['company_code'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'PROCESSING' => 'bg-yellow-100 text-yellow-800',
                                    'COMPLETED' => 'bg-green-100 text-green-800',
                                    'ERROR' => 'bg-red-100 text-red-800'
                                ];
                                $statusLabels = [
                                    'PROCESSING' => 'Processando',
                                    'COMPLETED' => 'Concluído',
                                    'ERROR' => 'Erro'
                                ];
                                $statusClass = $statusColors[$report['status']] ?? 'bg-gray-100 text-gray-800';
                                $statusLabel = $statusLabels[$report['status']] ?? ($report['status'] ?? 'Desconhecido');
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?= number_format($report['total_records']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    R$ <?= number_format($report['total_tpv'], 2, ',', '.') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    R$ <?= number_format($report['total_markup'], 2, ',', '.') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($report['uploaded_at'])) ?><br>
                                    <span class="text-xs">por <?= htmlspecialchars($report['uploaded_by_name'] ?? 'Sistema') ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="<?= url('billing/' . $report['id']) ?>" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($report['status'] === 'COMPLETED'): ?>
                                    <a href="<?= url('billing/' . $report['id'] . '/export') ?>" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="#" class="text-red-600 hover:text-red-900 btn-delete-report" 
                                       data-id="<?= $report['id'] ?>"
                                       onclick="event.preventDefault(); return false;">
                                        <i class="fas fa-trash"></i>
                                    </a>
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

<!-- Modal de Confirmação -->
<div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none; align-items: center; justify-content: center;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Confirmar Exclusão</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-500">Tem certeza que deseja excluir este relatório? Esta ação não pode ser desfeita.</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    Cancelar
                </button>
                <button type="button" id="confirmDelete" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md">
                    Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function closeModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-delete-report')) {
        e.preventDefault();
        e.stopPropagation();
        
        const button = e.target.closest('.btn-delete-report');
        const reportId = button.dataset.id;
        
        console.log('Excluindo relatório ID:', reportId);
        
        // Limpar evento anterior se existir
        const oldHandler = document.getElementById('confirmDelete').onclick;
        if (oldHandler) {
            document.getElementById('confirmDelete').onclick = null;
        }
        
        document.getElementById('confirmDelete').onclick = function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('billing') ?>' + '/' + reportId;

            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);

            console.log('Enviando formulário para:', form.action);
            console.log('Método:', methodField.value);

            document.body.appendChild(form);
            form.submit();
        };
        
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').style.display = 'flex';
    }
});
</script>
