<?php
$currentPage = 'usuarios';
ob_start();
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-user-edit mr-2"></i>
                Editar Usuário
            </h1>
            <p class="text-gray-600 mt-1">Atualize os dados do usuário administrativo</p>
        </div>
        <div>
            <a href="<?= url('usuarios/' . $user['id']) ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
        </div>
    </div>

    <!-- Formulário -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-user mr-2"></i>
                Dados do Usuário
            </h3>
        </div>

        <form method="POST" action="<?= url('usuarios/' . $user['id'] . '/edit') ?>" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="original_name" value="<?= htmlspecialchars($user['name']) ?>">
            <input type="hidden" name="original_email" value="<?= htmlspecialchars($user['email']) ?>">
            <input type="hidden" name="original_status" value="<?= htmlspecialchars($user['status']) ?>">
            
            <!-- Mensagens de sucesso -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800"><?= htmlspecialchars($_SESSION['success']) ?></p>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Mensagens de erro -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800"><?= htmlspecialchars($_SESSION['error']) ?></p>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Mensagens de validação -->
            <?php if (isset($_SESSION['validation_errors'])): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Erros encontrados:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <?php foreach ($_SESSION['validation_errors'] as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['validation_errors']); ?>
            <?php endif; ?>
            
            <!-- Dados Pessoais -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-id-card mr-2 text-blue-600"></i>
                    Dados Pessoais
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome Completo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                        <input type="text" name="name" required 
                               value="<?= htmlspecialchars($_POST['name'] ?? $user['name']) ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o nome completo">
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o email">
                    </div>
                </div>
            </div>
            
            <!-- Acesso ao Sistema -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-lock mr-2 text-blue-600"></i>
                    Acesso ao Sistema
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nova Senha -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
                        <input type="password" name="password" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite a nova senha">
                        <p class="mt-1 text-sm text-gray-500">Deixe em branco para manter a senha atual</p>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="ACTIVE" <?= ($_POST['status'] ?? $user['status']) === 'ACTIVE' ? 'selected' : '' ?>>Ativo</option>
                            <option value="INACTIVE" <?= ($_POST['status'] ?? $user['status']) === 'INACTIVE' ? 'selected' : '' ?>>Inativo</option>
                            <option value="BLOCKED" <?= ($_POST['status'] ?? $user['status']) === 'BLOCKED' ? 'selected' : '' ?>>Bloqueado</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Informações do Usuário -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                    Informações do Usuário
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Cadastrado em</h5>
                        <p class="text-sm text-gray-900"><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></p>
                    </div>
                    
                    <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Última atualização</h5>
                        <p class="text-sm text-gray-900"><?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?></p>
                    </div>
                    
                    <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Último login</h5>
                        <p class="text-sm text-gray-900">
                            <?php if ($user['last_login']): ?>
                                <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                            <?php else: ?>
                                <span class="text-gray-500">Nunca</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Dicas Importantes -->
            <div class="mb-8">
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lightbulb text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Dicas Importantes</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Deixe a senha em branco para manter a atual</li>
                                    <li>Usuários bloqueados não podem fazer login</li>
                                    <li>Alterações são salvas automaticamente</li>
                                    <li>Verifique o email antes de salvar</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botões -->
            <div class="flex justify-end space-x-4">
                <a href="<?= url('usuarios/' . $user['id']) ?>" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors" id="saveButton">
                    <i class="fas fa-save mr-2"></i>
                    <span class="button-text">Salvar Alterações</span>
                    <span class="button-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Salvando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validação de senha em tempo real
    const passwordInput = document.querySelector('input[name="password"]');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const feedback = this.nextElementSibling;
        
        if (password.length > 0 && password.length < 6) {
            feedback.textContent = 'Senha deve ter pelo menos 6 caracteres';
            feedback.className = 'mt-1 text-sm text-red-500';
        } else if (password.length >= 6) {
            feedback.textContent = 'Senha válida';
            feedback.className = 'mt-1 text-sm text-green-500';
        } else {
            feedback.textContent = 'Deixe em branco para manter a senha atual';
            feedback.className = 'mt-1 text-sm text-gray-500';
        }
    });
    
    // Validação de email em tempo real
    const emailInput = document.querySelector('input[name="email"]');
    emailInput.addEventListener('blur', function() {
        const email = this.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            this.classList.add('border-red-500');
            this.classList.remove('border-gray-300');
        } else {
            this.classList.remove('border-red-500');
            this.classList.add('border-gray-300');
        }
    });
    
    // Loading no botão de salvar
    const form = document.querySelector('form');
    const saveButton = document.getElementById('saveButton');
    const buttonText = saveButton.querySelector('.button-text');
    const buttonLoading = saveButton.querySelector('.button-loading');
    
    form.addEventListener('submit', function(e) {
        // Mostrar loading
        buttonText.classList.add('hidden');
        buttonLoading.classList.remove('hidden');
        saveButton.disabled = true;
    });
    
    // Auto-hide alerts após 5 segundos
    setTimeout(function() {
        const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        });
    }, 5000);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
