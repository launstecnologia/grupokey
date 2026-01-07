<?php
$currentPage = 'perfil';
ob_start();
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                <i class="fas fa-key mr-2"></i>
                <?= $force_change ? 'Definir Nova Senha' : 'Alterar Senha' ?>
            </h1>
            <p class="text-gray-600">
                <?= $force_change ? 'Defina uma nova senha para acessar o sistema' : 'Altere sua senha atual' ?>
            </p>
        </div>
        <?php if (!$force_change): ?>
        <div>
            <a href="<?= url('perfil') ?>" class="text-gray-900 bg-white border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar ao Perfil
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Formulário -->
    <div class="flex justify-center">
        <div class="w-full max-w-2xl">
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-lock mr-2"></i>
                        <?= $force_change ? 'Nova Senha' : 'Alteração de Senha' ?>
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (isset($_SESSION['validation_errors'])): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <div>
                                    <strong>Erro de validação:</strong>
                                    <ul class="mt-1 list-disc list-inside">
                                        <?php foreach ($_SESSION['validation_errors'] as $error): ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php unset($_SESSION['validation_errors']); ?>
                    <?php endif; ?>

                    <?php if ($force_change): ?>
                        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                <div>
                                    <strong>Primeiro acesso:</strong> Para sua segurança, você deve definir uma nova senha antes de acessar o sistema.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= url('change-password') ?>" class="space-y-6">
                        <?= csrf_field() ?>
                        
                        <?php if (!$force_change): ?>
                        <div>
                            <label for="current_password" class="text-sm font-medium text-gray-900 block mb-2">
                                <i class="fas fa-lock mr-1"></i>
                                Senha Atual <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" 
                                   id="current_password" 
                                   name="current_password" 
                                   placeholder="Digite sua senha atual"
                                   required>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <label for="new_password" class="text-sm font-medium text-gray-900 block mb-2">
                                <i class="fas fa-key mr-1"></i>
                                <?= $force_change ? 'Nova Senha' : 'Nova Senha' ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" 
                                   id="new_password" 
                                   name="new_password" 
                                   placeholder="Digite sua nova senha"
                                   required>
                            <p class="mt-1 text-sm text-gray-500">Mínimo de 6 caracteres</p>
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="text-sm font-medium text-gray-900 block mb-2">
                                <i class="fas fa-check mr-1"></i>
                                Confirmar Senha <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="Confirme sua nova senha"
                                   required>
                        </div>
                        
                        <!-- Indicador de força da senha -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">Força da senha</span>
                                <span class="text-sm text-gray-500" id="password-feedback">Digite uma senha</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gray-400 h-2 rounded-full transition-all duration-300" id="password-strength" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <!-- Botões -->
                        <div class="flex justify-end space-x-3">
                            <?php if (!$force_change): ?>
                            <a href="<?= url('perfil') ?>" class="text-gray-900 bg-white border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
                                <i class="fas fa-times mr-2"></i>
                                Cancelar
                            </a>
                            <?php endif; ?>
                            <button type="submit" class="text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
                                <i class="fas fa-save mr-2"></i>
                                <?= $force_change ? 'Definir Senha' : 'Alterar Senha' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Dicas de segurança -->
            <div class="bg-white shadow rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Dicas de Segurança
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                <span class="text-sm text-gray-700">Use pelo menos 8 caracteres</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                <span class="text-sm text-gray-700">Misture letras maiúsculas e minúsculas</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                <span class="text-sm text-gray-700">Inclua números e símbolos especiais</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fas fa-times-circle text-red-500 mr-3"></i>
                                <span class="text-sm text-gray-700">Evite informações pessoais</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-times-circle text-red-500 mr-3"></i>
                                <span class="text-sm text-gray-700">Não reutilize senhas antigas</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-times-circle text-red-500 mr-3"></i>
                                <span class="text-sm text-gray-700">Evite sequências óbvias</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const strengthBar = document.getElementById('password-strength');
    const feedback = document.getElementById('password-feedback');
    
    // Validação de força da senha
    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        
        // Atualizar barra de progresso
        strengthBar.style.width = strength.score + '%';
        strengthBar.className = `h-2 rounded-full transition-all duration-300 ${strength.class}`;
        
        // Atualizar feedback
        feedback.textContent = strength.text;
        feedback.className = `text-sm ${strength.textClass}`;
    });
    
    // Validação de confirmação de senha
    confirmPasswordInput.addEventListener('input', function() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = this.value;
        
        if (confirmPassword && newPassword !== confirmPassword) {
            this.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
            this.classList.remove('border-gray-300', 'focus:ring-cyan-600', 'focus:border-cyan-600');
        } else {
            this.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
            this.classList.add('border-gray-300', 'focus:ring-cyan-600', 'focus:border-cyan-600');
        }
    });
    
    function calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length === 0) {
            return {
                score: 0,
                class: 'bg-gray-400',
                text: 'Digite uma senha',
                textClass: 'text-gray-500'
            };
        }
        
        if (password.length < 6) {
            return {
                score: 20,
                class: 'bg-red-500',
                text: 'Muito fraca - mínimo 6 caracteres',
                textClass: 'text-red-500'
            };
        }
        
        // Critérios de força
        if (password.length >= 6) score += 20;
        if (password.length >= 8) score += 10;
        if (/[a-z]/.test(password)) score += 10;
        if (/[A-Z]/.test(password)) score += 10;
        if (/[0-9]/.test(password)) score += 10;
        if (/[^A-Za-z0-9]/.test(password)) score += 10;
        
        // Determinar classificação
        if (score < 40) {
            return {
                score: score,
                class: 'bg-red-500',
                text: 'Fraca',
                textClass: 'text-red-500'
            };
        } else if (score < 70) {
            return {
                score: score,
                class: 'bg-yellow-500',
                text: 'Média',
                textClass: 'text-yellow-600'
            };
        } else {
            return {
                score: score,
                class: 'bg-green-500',
                text: 'Forte',
                textClass: 'text-green-600'
            };
        }
    }
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('As senhas não coincidem. Por favor, verifique e tente novamente.');
            return;
        }
        
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('A senha deve ter pelo menos 6 caracteres.');
            return;
        }
        
        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processando...';
        submitBtn.disabled = true;
        
        // Re-enable button after 3 seconds (in case of error)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 3000);
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>