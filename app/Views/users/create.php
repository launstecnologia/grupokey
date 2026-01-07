<?php
$currentPage = 'usuarios';
ob_start();
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-user-plus mr-2"></i>
                Novo Usuário
            </h1>
            <p class="text-gray-600 mt-1">Cadastre um novo usuário administrativo</p>
        </div>
        <div>
            <a href="<?= url('usuarios') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
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

        <form method="POST" action="<?= url('usuarios') ?>" class="p-6">
            <?= csrf_field() ?>
            
            <!-- Mensagens de erro -->
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
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o nome completo">
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
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
                    <!-- Senha -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
                        <input type="password" name="password" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite a senha">
                        <p class="mt-1 text-sm text-gray-500">Mínimo de 6 caracteres</p>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="ACTIVE" <?= ($_POST['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'selected' : '' ?>>Ativo</option>
                            <option value="INACTIVE" <?= ($_POST['status'] ?? '') === 'INACTIVE' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Informações Adicionais -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                    Informações Importantes
                </h4>
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lightbulb text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Dicas Importantes</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>O email será usado para login no sistema</li>
                                    <li>A senha será enviada por email</li>
                                    <li>Usuários ativos podem fazer login</li>
                                    <li>Use senhas seguras com pelo menos 6 caracteres</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botões -->
            <div class="flex justify-end space-x-4">
                <a href="<?= url('usuarios') ?>" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Cadastrar Usuário
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
            feedback.textContent = 'Mínimo de 6 caracteres';
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
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
