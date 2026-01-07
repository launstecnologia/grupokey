<?php
$currentPage = 'configuracoes';
ob_start();
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-envelope mr-2"></i>
                Configurações de Email SMTP
            </h1>
            <p class="text-gray-600 mt-1">Configure as credenciais do servidor de email</p>
        </div>
    </div>

    <!-- Flash messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-400 mr-2"></i>
                <span class="text-sm font-medium text-green-800"><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-2"></i>
                <span class="text-sm font-medium text-red-800"><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulário de Configuração -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-cog mr-2"></i>
                        Configurações SMTP
                    </h3>
                </div>
                <form method="POST" action="<?= url('email-settings/update') ?>" class="p-6 space-y-6">
                    <?= csrf_field() ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Servidor SMTP -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Servidor SMTP *
                            </label>
                            <input type="text" 
                                   name="mail_host" 
                                   value="<?= htmlspecialchars($settings['mail_host'] ?? 'smtp.gmail.com') ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="smtp.gmail.com"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">Ex: smtp.gmail.com, smtp.outlook.com</p>
                        </div>
                        
                        <!-- Porta -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Porta *
                            </label>
                            <input type="number" 
                                   name="mail_port" 
                                   value="<?= htmlspecialchars($settings['mail_port'] ?? 587) ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="587"
                                   min="1"
                                   max="65535"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">Geralmente 587 (TLS) ou 465 (SSL)</p>
                        </div>
                        
                        <!-- Usuário/Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Usuário/Email SMTP *
                            </label>
                            <input type="email" 
                                   name="mail_user" 
                                   value="<?= htmlspecialchars($settings['mail_user'] ?? '') ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="seu-email@gmail.com"
                                   required>
                        </div>
                        
                        <!-- Senha -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Senha SMTP *
                            </label>
                            <input type="password" 
                                   name="mail_pass" 
                                   value="<?= htmlspecialchars($settings['mail_pass'] ?? '') ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Sua senha ou senha de app"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">Use senha de app para Gmail</p>
                        </div>
                        
                        <!-- Email Remetente -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email Remetente *
                            </label>
                            <input type="email" 
                                   name="mail_from" 
                                   value="<?= htmlspecialchars($settings['mail_from'] ?? '') ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="noreply@sistema.com"
                                   required>
                        </div>
                        
                        <!-- Nome Remetente -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nome Remetente
                            </label>
                            <input type="text" 
                                   name="mail_name" 
                                   value="<?= htmlspecialchars($settings['mail_name'] ?? 'Sistema CRM') ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Sistema CRM">
                        </div>
                        
                        <!-- Criptografia -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Criptografia *
                            </label>
                            <select name="mail_encryption" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                <option value="tls" <?= ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recomendado)</option>
                                <option value="ssl" <?= ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                <option value="none" <?= ($settings['mail_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>Nenhuma</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Teste de Email -->
        <div>
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Testar Configuração
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        Envie um email de teste para verificar se as configurações estão corretas.
                    </p>
                    <form method="POST" action="<?= url('email-settings/test') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email para teste
                            </label>
                            <input type="email" 
                                   name="test_email" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="teste@email.com"
                                   required>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Enviar Email de Teste
                        </button>
                    </form>
                    
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <h4 class="text-sm font-medium text-blue-800 mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Dicas
                        </h4>
                        <ul class="text-xs text-blue-700 space-y-1">
                            <li>• Gmail: Use senha de app, não a senha normal</li>
                            <li>• Outlook: Pode usar senha normal</li>
                            <li>• Porta 587 geralmente usa TLS</li>
                            <li>• Porta 465 geralmente usa SSL</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

