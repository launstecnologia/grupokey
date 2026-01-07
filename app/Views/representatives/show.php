<?php
$currentPage = 'representantes';
ob_start();

// Definir cores de status
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
                <?= htmlspecialchars($representative['nome_completo']) ?>
            </h1>
            <p class="text-gray-600 mt-1">Detalhes do representante</p>
            <div class="mt-2">
                <?php 
                $status = $representative['status'] ?? 'ACTIVE';
                $statusClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                $statusLabel = $statusLabels[$status] ?? $status;
                ?>
                <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full <?= $statusClass ?>">
                    <?= $statusLabel ?>
                </span>
            </div>
        </div>
        <div class="flex space-x-3">
            <a href="<?= url('representantes/' . $representative['id'] . '/edit') ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Editar
            </a>
            <a href="<?= url('representantes') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
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
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($representative['nome_completo']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">CPF</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= format_cpf($representative['cpf']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 flex items-center">
                                <i class="fas fa-envelope mr-2 text-gray-400"></i>
                                <?= htmlspecialchars($representative['email']) ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Telefone</dt>
                            <dd class="mt-1 text-sm text-gray-900 flex items-center">
                                <i class="fas fa-phone mr-2 text-gray-400"></i>
                                <?= format_phone($representative['telefone']) ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Endereço -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                        Endereço
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">CEP</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= format_cep($representative['cep']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Logradouro</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= htmlspecialchars($representative['logradouro']) ?>, <?= htmlspecialchars($representative['numero']) ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Complemento</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($representative['complemento'] ?: '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Bairro</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($representative['bairro']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Cidade</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($representative['cidade']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">UF</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($representative['uf']) ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Estabelecimentos -->
            <?php if (!empty($establishments)): ?>
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-building mr-2 text-blue-600"></i>
                        Estabelecimentos Cadastrados
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estabelecimento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($establishments as $establishment): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($establishment['nome']) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($establishment['cnpj']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($establishment['produto_nome']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $estStatusColors = [
                                        'APPROVED' => 'bg-green-100 text-green-800',
                                        'PENDING' => 'bg-yellow-100 text-yellow-800',
                                        'REPROVED' => 'bg-red-100 text-red-800'
                                    ];
                                    $estStatusLabels = [
                                        'APPROVED' => 'Aprovado',
                                        'PENDING' => 'Pendente',
                                        'REPROVED' => 'Reprovado'
                                    ];
                                    $estStatusClass = $estStatusColors[$establishment['status']] ?? 'bg-gray-100 text-gray-800';
                                    $estStatusLabel = $estStatusLabels[$establishment['status']] ?? $establishment['status'];
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $estStatusClass ?>">
                                        <?= $estStatusLabel ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('d/m/Y', strtotime($establishment['created_at'])) ?>
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
                                data-id="<?= $representative['id'] ?>" 
                                data-name="<?= htmlspecialchars($representative['nome_completo']) ?>">
                            <i class="fas fa-key mr-2"></i>
                            Resetar Senha
                        </button>
                        
                        <button type="button" class="w-full bg-<?= $representative['status'] === 'ACTIVE' ? 'yellow' : 'green' ?>-600 hover:bg-<?= $representative['status'] === 'ACTIVE' ? 'yellow' : 'green' ?>-700 text-white px-4 py-2 rounded-lg inline-flex items-center justify-center transition-colors btn-toggle-status" 
                                data-id="<?= $representative['id'] ?>" 
                                data-name="<?= htmlspecialchars($representative['nome_completo']) ?>"
                                data-status="<?= $representative['status'] ?>">
                            <i class="fas fa-<?= $representative['status'] === 'ACTIVE' ? 'pause' : 'play' ?> mr-2"></i>
                            <?= $representative['status'] === 'ACTIVE' ? 'Desativar' : 'Ativar' ?>
                        </button>
                        
                        <button type="button" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center justify-center transition-colors btn-delete" 
                                data-id="<?= $representative['id'] ?>" 
                                data-name="<?= htmlspecialchars($representative['nome_completo']) ?>">
                            <i class="fas fa-trash mr-2"></i>
                            Excluir
                        </button>
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
                            <div class="text-2xl font-bold text-blue-600"><?= $stats['total_establishments'] ?? 0 ?></div>
                            <div class="text-sm text-gray-500">Total</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600"><?= $stats['approved_establishments'] ?? 0 ?></div>
                            <div class="text-sm text-gray-500">Aprovados</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600"><?= $stats['pending_establishments'] ?? 0 ?></div>
                            <div class="text-sm text-gray-500">Pendentes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600"><?= $stats['reproved_establishments'] ?? 0 ?></div>
                            <div class="text-sm text-gray-500">Reprovados</div>
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
                                <?= date('d/m/Y H:i', strtotime($representative['created_at'])) ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Última atualização</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= date('d/m/Y H:i', strtotime($representative['updated_at'])) ?>
                            </dd>
                        </div>
                        <?php if ($representative['last_login']): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Último login</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= date('d/m/Y H:i', strtotime($representative['last_login'])) ?>
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

<script>
// Confirmar exclusão
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-delete')) {
        const button = e.target.closest('.btn-delete');
        const id = button.dataset.id;
        const name = button.dataset.name;
        
        if (confirm(`Tem certeza que deseja excluir o representante "${name}"?`)) {
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
        
        showPasswordModal(id, name);
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

// Funções para o modal de senha
function showPasswordModal(id, name) {
    document.getElementById('userName').textContent = name;
    document.getElementById('passwordForm').action = '<?= url('representantes') ?>/' + id + '/reset-password';
    document.getElementById('passwordModal').classList.remove('hidden');
    document.getElementById('newPassword').focus();
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
    document.getElementById('passwordForm').reset();
    document.getElementById('passwordError').classList.add('hidden');
}

// Validação do formulário de senha
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const password = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const errorDiv = document.getElementById('passwordError');
    const errorText = errorDiv.querySelector('p');
    
    // Limpar erro anterior
    errorDiv.classList.add('hidden');
    
    // Validações
    if (password.length < 6) {
        e.preventDefault();
        errorText.textContent = 'A senha deve ter pelo menos 6 caracteres';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (password !== confirmPassword) {
        e.preventDefault();
        errorText.textContent = 'As senhas não coincidem';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Se chegou até aqui, pode submeter
    document.getElementById('submitPasswordBtn').disabled = true;
    document.getElementById('submitPasswordBtn').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Alterando...';
});

// Fechar modal ao clicar fora
document.getElementById('passwordModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePasswordModal();
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>