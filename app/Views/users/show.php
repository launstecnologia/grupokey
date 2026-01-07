<?php
$currentPage = 'usuarios';
ob_start();

// Definir variáveis com valores padrão
$user = $user ?? [];
$activities = $activities ?? [];
$stats = $stats ?? ['total_activities' => 0, 'logins' => 0, 'logouts' => 0, 'activities_last_month' => 0];

// Status badges
$statusColors = [
    'ACTIVE' => 'bg-green-100 text-green-800',
    'INACTIVE' => 'bg-yellow-100 text-yellow-800',
    'BLOCKED' => 'bg-red-100 text-red-800'
];
$statusLabels = [
    'ACTIVE' => 'Ativo',
    'INACTIVE' => 'Inativo',
    'BLOCKED' => 'Bloqueado'
];
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-user mr-2"></i>
                <?= htmlspecialchars($user['name']) ?>
            </h1>
            <p class="text-gray-600 mt-1">Detalhes do usuário administrativo</p>
            <div class="mt-2">
                <?php 
                $status = $user['status'] ?? 'ACTIVE';
                $statusClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                $statusLabel = $statusLabels[$status] ?? $status;
                ?>
                <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full <?= $statusClass ?>">
                    <?= $statusLabel ?>
                </span>
                <?php if ($user['id'] == App\Core\Auth::user()['id']): ?>
                    <span class="ml-2 px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        Você
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex space-x-3">
            <a href="<?= url('usuarios/' . $user['id'] . '/edit') ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Editar
            </a>
            <a href="<?= url('usuarios') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conteúdo Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Dados Pessoais -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-id-card mr-2 text-blue-600"></i>
                        Dados Pessoais
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nome Completo</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['name']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 flex items-center">
                                <i class="fas fa-envelope mr-2 text-gray-400"></i>
                                <?= htmlspecialchars($user['email']) ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID do Usuário</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono"><?= htmlspecialchars($user['id']) ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
            
            <!-- Atividades Recentes -->
            <?php if (!empty($activities)): ?>
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-history mr-2 text-blue-600"></i>
                        Atividades Recentes
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($activities as $activity): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $actionColors = [
                                        'LOGIN' => 'bg-green-100 text-green-800',
                                        'LOGOUT' => 'bg-yellow-100 text-yellow-800',
                                        'CREATE' => 'bg-blue-100 text-blue-800',
                                        'UPDATE' => 'bg-indigo-100 text-indigo-800',
                                        'DELETE' => 'bg-red-100 text-red-800'
                                    ];
                                    $actionClass = $actionColors[$activity['action']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $actionClass ?>">
                                        <?= htmlspecialchars($activity['action']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($activity['module']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($activity['ip_address'] ?? '-') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Ações Administrativas -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-cogs mr-2 text-blue-600"></i>
                        Ações Administrativas
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <button type="button" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg inline-flex items-center justify-center transition-colors btn-reset-password" 
                                data-id="<?= $user['id'] ?>" 
                                data-name="<?= htmlspecialchars($user['name']) ?>">
                            <i class="fas fa-key mr-2"></i>
                            Resetar Senha
                        </button>
                        
                        <?php if ($user['id'] != App\Core\Auth::user()['id']): ?>
                        <button type="button" class="w-full bg-<?= $user['status'] === 'ACTIVE' ? 'yellow' : 'green' ?>-600 hover:bg-<?= $user['status'] === 'ACTIVE' ? 'yellow' : 'green' ?>-700 text-white px-4 py-2 rounded-lg inline-flex items-center justify-center transition-colors btn-toggle-status" 
                                data-id="<?= $user['id'] ?>" 
                                data-name="<?= htmlspecialchars($user['name']) ?>"
                                data-status="<?= $user['status'] ?>">
                            <i class="fas fa-<?= $user['status'] === 'ACTIVE' ? 'pause' : 'play' ?> mr-2"></i>
                            <?= $user['status'] === 'ACTIVE' ? 'Desativar' : 'Ativar' ?>
                        </button>
                        
                        <button type="button" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center justify-center transition-colors btn-delete" 
                                data-id="<?= $user['id'] ?>" 
                                data-name="<?= htmlspecialchars($user['name']) ?>">
                            <i class="fas fa-trash mr-2"></i>
                            Excluir
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Estatísticas -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                        Estatísticas
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600"><?= $stats['total_activities'] ?></div>
                            <div class="text-sm text-gray-500">Total</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600"><?= $stats['logins'] ?></div>
                            <div class="text-sm text-gray-500">Logins</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600"><?= $stats['logouts'] ?></div>
                            <div class="text-sm text-gray-500">Logouts</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600"><?= $stats['activities_last_month'] ?></div>
                            <div class="text-sm text-gray-500">Último Mês</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informações do Sistema -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informações do Sistema
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Cadastrado em</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Última atualização</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?>
                            </dd>
                        </div>
                        <?php if ($user['last_login']): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Último login</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                            </dd>
                        </div>
                        <?php else: ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Último login</dt>
                            <dd class="mt-1 text-sm text-gray-500">Nunca</dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    // Resetar senha
    if (e.target.closest('.btn-reset-password')) {
        const button = e.target.closest('.btn-reset-password');
        const id = button.dataset.id;
        const name = button.dataset.name;
        
        if (confirm(`Tem certeza que deseja resetar a senha do usuário "${name}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('usuarios') ?>/' + id + '/reset-password';
            
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
        
        if (confirm(`Tem certeza que deseja ${action} o usuário "${name}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('usuarios') ?>/' + id + '/toggle-status';
            
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Excluir
    if (e.target.closest('.btn-delete')) {
        const button = e.target.closest('.btn-delete');
        const id = button.dataset.id;
        const name = button.dataset.name;
        
        if (confirm(`Tem certeza que deseja excluir o usuário "${name}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('usuarios') ?>/' + id;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            form.appendChild(methodInput);
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
