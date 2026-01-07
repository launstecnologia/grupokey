<?php
$currentPage = 'usuarios';
ob_start();

// Definir variáveis com valores padrão para evitar warnings
$stats = $stats ?? ['total' => 0, 'active' => 0, 'inactive' => 0, 'blocked' => 0, 'cadastros_ultimo_mes' => 0];
$users = $users ?? [];
?>

<div class="pt-6 px-4">
    <!-- Header com ações -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-user-shield mr-2"></i>
                Usuários
            </h1>
            <p class="text-gray-600 mt-1">Gerencie todos os usuários administrativos cadastrados</p>
        </div>
        <div>
            <?php if (App\Core\Auth::isAdmin()): ?>
            <a href="<?= url('usuarios/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Novo Usuário
            </a>
            <?php endif; ?>
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
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['active'] ?></dd>
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
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['inactive'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Bloqueados -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-user-lock text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Bloqueados</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['blocked'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
        <form method="GET" action="<?= url('usuarios') ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos os status</option>
                    <option value="ACTIVE" <?= ($filters['status'] ?? '') === 'ACTIVE' ? 'selected' : '' ?>>Ativo</option>
                    <option value="INACTIVE" <?= ($filters['status'] ?? '') === 'INACTIVE' ? 'selected' : '' ?>>Inativo</option>
                    <option value="BLOCKED" <?= ($filters['status'] ?? '') === 'BLOCKED' ? 'selected' : '' ?>>Bloqueado</option>
                </select>
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
                <a href="<?= url('usuarios') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-times mr-1"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Usuários -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-list mr-2"></i>
                Lista de Usuários
            </h3>
        </div>

        <?php if (empty($users)): ?>
            <div class="p-12 text-center">
                <i class="fas fa-user-shield text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum usuário encontrado</h3>
                <p class="text-gray-500 mb-6">Tente ajustar os filtros ou cadastre um novo usuário.</p>
                <a href="<?= url('usuarios/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg inline-flex items-center transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Cadastrar Usuário
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Login</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($user['name']) ?>
                                        <?php if ($user['id'] == App\Core\Auth::user()['id']): ?>
                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Você
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <i class="fas fa-envelope mr-1"></i>
                                    <?= htmlspecialchars($user['email']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
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
                                $statusClass = $statusColors[$user['status']] ?? 'bg-gray-100 text-gray-800';
                                $statusLabel = $statusLabels[$user['status']] ?? $user['status'];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if ($user['last_login']): ?>
                                    <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                                <?php else: ?>
                                    <span class="text-gray-400">Nunca</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="<?= url('usuarios/' . $user['id']) ?>" class="text-blue-600 hover:text-blue-900" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= url('usuarios/' . $user['id'] . '/edit') ?>" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="text-yellow-600 hover:text-yellow-900 btn-reset-password" 
                                            data-id="<?= $user['id'] ?>" 
                                            data-name="<?= htmlspecialchars($user['name']) ?>"
                                            title="Resetar Senha">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <?php if ($user['id'] != App\Core\Auth::user()['id']): ?>
                                    <button type="button" class="text-<?= $user['status'] === 'ACTIVE' ? 'yellow' : 'green' ?>-600 hover:text-<?= $user['status'] === 'ACTIVE' ? 'yellow' : 'green' ?>-900 btn-toggle-status" 
                                            data-id="<?= $user['id'] ?>" 
                                            data-name="<?= htmlspecialchars($user['name']) ?>"
                                            data-status="<?= $user['status'] ?>"
                                            title="<?= $user['status'] === 'ACTIVE' ? 'Desativar' : 'Ativar' ?>">
                                        <i class="fas fa-<?= $user['status'] === 'ACTIVE' ? 'pause' : 'play' ?>"></i>
                                    </button>
                                    <button type="button" class="text-red-600 hover:text-red-900 btn-delete" 
                                            data-id="<?= $user['id'] ?>" 
                                            data-name="<?= htmlspecialchars($user['name']) ?>"
                                            title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
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
        
        if (confirm(`Tem certeza que deseja excluir o usuário "${name}"?`)) {
            // Criar formulário para exclusão
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
    
    // Resetar senha
    if (e.target.closest('.btn-reset-password')) {
        const button = e.target.closest('.btn-reset-password');
        const id = button.dataset.id;
        const name = button.dataset.name;
        
        showPasswordModal(id, name);
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
});

// Função para mostrar modal de senha
function showPasswordModal(userId, userName) {
    document.getElementById('userName').textContent = userName;
    document.getElementById('passwordForm').action = '<?= url('usuarios') ?>/' + userId + '/reset-password';
    document.getElementById('passwordModal').classList.remove('hidden');
    
    // Limpar campos
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    document.getElementById('passwordError').classList.add('hidden');
}

// Função para fechar modal
function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
}

// Validação de senhas
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const errorDiv = document.getElementById('passwordError');
    const errorText = errorDiv.querySelector('p');
    
    // Limpar erro anterior
    errorDiv.classList.add('hidden');
    
    // Validações
    if (password.length < 6) {
        errorText.textContent = 'A senha deve ter pelo menos 6 caracteres.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (password !== confirmPassword) {
        errorText.textContent = 'As senhas não coincidem.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Se chegou até aqui, enviar formulário
    this.submit();
});
</script>

<!-- Modal para Reset de Senha -->
<div id="passwordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-key mr-2 text-blue-600"></i>
                    Redefinir Senha
                </h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closePasswordModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600">
                    Definir nova senha para: <span id="userName" class="font-medium text-gray-900"></span>
                </p>
            </div>
            
            <form id="passwordForm" method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha *</label>
                    <input type="password" id="newPassword" name="password" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Digite a nova senha" minlength="6">
                    <p class="mt-1 text-xs text-gray-500">Mínimo de 6 caracteres</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha *</label>
                    <input type="password" id="confirmPassword" name="password_confirm" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Confirme a nova senha" minlength="6">
                    <p class="mt-1 text-xs text-gray-500">Digite a senha novamente</p>
                </div>
                
                <!-- Mensagem de erro -->
                <div id="passwordError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md hidden">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-red-400 mt-0.5"></i>
                        <p class="ml-2 text-sm text-red-700"></p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closePasswordModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" id="submitPasswordBtn"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Alterar Senha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
