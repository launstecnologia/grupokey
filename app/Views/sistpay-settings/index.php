<?php
$currentPage = 'configuracoes';
ob_start();
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-plug mr-2"></i>
                Configurações API SistPay
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Configure a integração com a API SistPay para cadastro automático de estabelecimentos</p>
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        <i class="fas fa-cog mr-2"></i>
                        Configurações da API
                    </h3>
                </div>
                <form method="POST" action="<?= url('sistpay-settings/update') ?>" class="p-6 space-y-6">
                    <?= csrf_field() ?>
                    
                    <div class="space-y-6">
                        <!-- Token -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Token de Autenticação *
                            </label>
                            <input type="text" 
                                   name="token" 
                                   value="<?= htmlspecialchars($settings['token'] ?? '') ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="SEU_TOKEN_AQUI"
                                   required>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Token fornecido pela SistPay para autenticação na API</p>
                        </div>
                        
                        <!-- Método de Autenticação -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Método de Autenticação *
                            </label>
                            <select name="auth_method" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    required>
                                <option value="Authorization" <?= ($settings['auth_method'] ?? 'Authorization') === 'Authorization' ? 'selected' : '' ?>>
                                    Authorization: Bearer (Recomendado)
                                </option>
                                <option value="X-Authorization" <?= ($settings['auth_method'] ?? '') === 'X-Authorization' ? 'selected' : '' ?>>
                                    X-Authorization: Bearer (Para proxies/CDN)
                                </option>
                                <option value="X-Api-Token" <?= ($settings['auth_method'] ?? '') === 'X-Api-Token' ? 'selected' : '' ?>>
                                    X-Api-Token (Alternativo)
                                </option>
                                <option value="X-Api-Key" <?= ($settings['auth_method'] ?? '') === 'X-Api-Key' ? 'selected' : '' ?>>
                                    X-Api-Key (Alternativo)
                                </option>
                                <option value="Query-Param" <?= ($settings['auth_method'] ?? '') === 'Query-Param' ? 'selected' : '' ?>>
                                    Query Parameter (Apenas para testes)
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Escolha o método de autenticação. Use headers alternativos se proxies/CDN removerem o header Authorization.
                            </p>
                        </div>
                        
                        <!-- URL Base -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                URL Base da API *
                            </label>
                            <input type="url" 
                                   name="base_url" 
                                   value="<?= htmlspecialchars($settings['base_url'] ?? 'https://sistpay.com.br/api') ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="https://sistpay.com.br/api"
                                   required>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">URL base da API SistPay</p>
                        </div>
                        
                        <!-- Sandbox -->
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="is_sandbox" 
                                   id="is_sandbox"
                                   value="1"
                                   <?= !empty($settings['is_sandbox']) ? 'checked' : '' ?>
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_sandbox" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                Modo Sandbox
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 -mt-4">
                            Quando ativado, os dados serão validados mas não serão salvos no banco de dados da SistPay
                        </p>
                        
                        <!-- Ativo -->
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active"
                                   value="1"
                                   <?= !empty($settings['is_active']) ? 'checked' : '' ?>
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                API Ativa
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 -mt-4">
                            Quando ativado, a integração será usada automaticamente para estabelecimentos com produto PagBank
                        </p>
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
        
        <!-- Informações e Teste -->
        <div>
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-info-circle mr-2"></i>
                        Informações
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-800 mb-2">
                                <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                Quando a API é usada?
                            </h4>
                            <p class="text-xs text-gray-600">
                                A API SistPay será usada automaticamente apenas quando o estabelecimento tiver o produto <strong>PagBank</strong> selecionado. Outros produtos não utilizam esta integração.
                            </p>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-800 mb-2">
                                <i class="fas fa-shield-alt text-blue-500 mr-1"></i>
                                Modo Sandbox
                            </h4>
                            <p class="text-xs text-gray-600">
                                Use o modo sandbox para testar a integração sem salvar dados reais no banco da SistPay. Os dados serão apenas validados.
                            </p>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-800 mb-2">
                                <i class="fas fa-key text-yellow-500 mr-1"></i>
                                Token
                            </h4>
                            <p class="text-xs text-gray-600">
                                Entre em contato com o administrador da SistPay para obter ou gerar seu token de acesso.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Teste de Conexão -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-network-wired mr-2"></i>
                        Testar Conexão
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        Teste a conexão com a API SistPay para verificar se as configurações estão corretas.
                    </p>
                    <form method="POST" action="<?= url('sistpay-settings/test') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                            <i class="fas fa-plug mr-2"></i>
                            Testar Conexão
                        </button>
                    </form>
                    
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <h4 class="text-sm font-medium text-blue-800 mb-2">
                            <i class="fas fa-lightbulb mr-1"></i>
                            Dicas
                        </h4>
                        <ul class="text-xs text-blue-700 space-y-1">
                            <li>• Certifique-se de que o token está correto</li>
                            <li>• Verifique se a URL base está correta</li>
                            <li>• Use o modo sandbox para testes</li>
                            <li>• A API só funciona para estabelecimentos com PagBank</li>
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

