<?php
$currentPage = 'whatsapp';
ob_start();

$instance = $instance ?? [];
?>

<div class="pt-6 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <i class="fab fa-whatsapp mr-2 text-green-500"></i>
                    <?= htmlspecialchars($instance['name'] ?? 'Instância') ?>
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Detalhes da instância WhatsApp</p>
            </div>
            <div>
                <a href="<?= url('whatsapp/instances') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
        </div>

        <!-- Status e Informações -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Status -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status</h2>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Status da Conexão:</span>
                        <span class="ml-2 px-3 py-1 rounded-full text-xs font-semibold <?php
                            echo $instance['status'] === 'CONNECTED' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : '';
                            echo $instance['status'] === 'CONNECTING' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' : '';
                            echo $instance['status'] === 'DISCONNECTED' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : '';
                        ?>">
                            <?= htmlspecialchars($instance['status'] ?? 'DISCONNECTED') ?>
                        </span>
                    </div>
                    
                    <?php if ($instance['phone_number']): ?>
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Número:</span>
                            <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                <?= htmlspecialchars($instance['phone_number']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($instance['profile_name']): ?>
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Nome do Perfil:</span>
                            <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                <?= htmlspecialchars($instance['profile_name']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ações -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ações</h2>
                <div class="space-y-2">
                    <?php if ($instance['status'] === 'DISCONNECTED'): ?>
                        <form method="POST" action="<?= url('whatsapp/instances/' . $instance['id'] . '/connect') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-link mr-2"></i>
                                Conectar (Gerar QR Code)
                            </button>
                        </form>
                    <?php elseif ($instance['status'] === 'CONNECTING'): ?>
                        <div class="text-center text-yellow-600 dark:text-yellow-400">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Aguardando conexão...
                        </div>
                    <?php elseif ($instance['status'] === 'CONNECTED'): ?>
                        <form method="POST" action="<?= url('whatsapp/instances/' . $instance['id'] . '/disconnect') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-unlink mr-2"></i>
                                Desconectar
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- QR Code (exibir quando houver QR e ainda não estiver CONNECTED) -->
        <?php if (!empty($instance['qr_code']) && ($instance['status'] ?? '') !== 'CONNECTED'): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">QR Code para Conexão</h2>
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Escaneie este QR Code com o WhatsApp que deseja conectar
                    </p>
                    <img src="data:image/png;base64,<?= htmlspecialchars($instance['qr_code']) ?>" 
                         alt="QR Code" 
                         class="mx-auto border-4 border-gray-200 dark:border-gray-700 rounded-lg max-w-xs">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                        O QR Code expira em alguns minutos. Se expirar, clique em "Conectar" novamente.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Informações Técnicas -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informações Técnicas</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Instance Key:</span>
                    <p class="font-mono text-sm text-gray-900 dark:text-white">
                        <?= htmlspecialchars($instance['instance_key'] ?? '') ?>
                    </p>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Evolution API URL:</span>
                    <p class="font-mono text-sm text-gray-900 dark:text-white">
                        <?= htmlspecialchars($instance['evolution_api_url'] ?? '') ?>
                    </p>
                </div>
                <div class="md:col-span-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Webhook URL:</span>
                    <p class="font-mono text-sm text-gray-900 dark:text-white break-all">
                        <?= !empty($instance['webhook_url']) ? htmlspecialchars($instance['webhook_url']) : 'Não configurado' ?>
                    </p>
                    <?php if (empty($instance['webhook_url'])): ?>
                        <form method="POST" action="<?= url('whatsapp/instances/' . $instance['id'] . '/set-webhook') ?>" class="mt-2">
                            <?= csrf_field() ?>
                            <button type="submit" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg transition-colors">
                                Configurar webhook agora
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Máximo de Conexões:</span>
                    <p class="text-sm text-gray-900 dark:text-white">
                        <?= $instance['max_connections'] ?? 10 ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh se estiver conectando
<?php if ($instance['status'] === 'CONNECTING'): ?>
setInterval(function() {
    location.reload();
}, 10000); // Recarregar a cada 10 segundos
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

