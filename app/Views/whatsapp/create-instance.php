<?php
$currentPage = 'whatsapp';
ob_start();
?>

<div class="pt-6 px-4">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fab fa-whatsapp mr-2 text-green-500"></i>
                Nova Instância WhatsApp
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Configure uma nova instância do WhatsApp via Evolution API</p>
        </div>

        <!-- Formulário -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <form method="POST" action="<?= url('whatsapp/instances') ?>">
                <?= csrf_field() ?>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nome da Instância *
                        </label>
                        <input type="text" 
                               name="name" 
                               required
                               placeholder="Ex: Atendimento Principal"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Chave da Instância (Instance Key) *
                        </label>
                        <input type="text" 
                               name="instance_key" 
                               required
                               placeholder="Ex: atendimento-principal"
                               pattern="[a-z0-9-]+"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Apenas letras minúsculas, números e hífens
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            URL da Evolution API *
                        </label>
                        <input type="url" 
                               name="evolution_api_url" 
                               required
                               value="https://evolutionapi.launs.com.br"
                               placeholder="https://evolutionapi.launs.com.br"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            API Key da Evolution API *
                        </label>
                        <input type="text" 
                               name="evolution_api_key" 
                               required
                               value="E4C35BD2041F-42FB-AD3D-E39810A5E374"
                               placeholder="E4C35BD2041F-42FB-AD3D-E39810A5E374"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            URL do Webhook (opcional)
                        </label>
                        <input type="url" 
                               name="webhook_url" 
                               placeholder="https://seudominio.com.br (deixe em branco para auto-detectar)"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Se deixar em branco, o sistema detectará automaticamente a URL base e configurará o webhook como: <strong>{auto}/whatsapp/webhook</strong>
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                            <i class="fas fa-check-circle mr-1"></i>
                            O webhook será configurado automaticamente na Evolution API ao criar a instância.
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Máximo de Conexões
                        </label>
                        <input type="number" 
                               name="max_connections" 
                               value="10"
                               min="1"
                               max="50"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               id="is_active"
                               checked
                               class="w-4 h-4 text-green-600 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Instância ativa
                        </label>
                    </div>
                </div>
                
                <div class="flex gap-2 mt-6">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Criar Instância
                    </button>
                    <a href="<?= url('whatsapp/instances') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

