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
            <div class="flex gap-2">
                <a href="<?= url('whatsapp/instances') ?>" class="bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500 text-white font-medium px-4 py-2 rounded-lg inline-flex items-center transition-colors shadow">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
                <form method="POST" action="<?= url('whatsapp/instances/' . $instance['id'] . '/delete') ?>" class="inline" onsubmit="return confirm('Excluir esta instância? A ação não pode ser desfeita.');">
                    <?= csrf_field() ?>
                    <button type="submit" class="bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500 text-white font-medium px-4 py-2 rounded-lg inline-flex items-center transition-colors shadow">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Excluir
                    </button>
                </form>
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
                            echo $instance['status'] === 'CONNECTED' ? 'bg-green-100 text-green-800 dark:bg-green-600 dark:text-white' : '';
                            echo $instance['status'] === 'CONNECTING' ? 'bg-yellow-100 text-yellow-800 dark:bg-amber-500 dark:text-white' : '';
                            echo $instance['status'] === 'DISCONNECTED' ? 'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-100' : '';
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
                            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-500 text-white font-medium px-4 py-2 rounded-lg transition-colors shadow">
                                <i class="fas fa-link mr-2"></i>
                                Conectar (Gerar QR Code)
                            </button>
                        </form>
                    <?php elseif ($instance['status'] === 'CONNECTING'): ?>
                        <div class="text-center text-yellow-700 dark:text-amber-400 font-medium">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Aguardando conexão...
                        </div>
                    <?php elseif ($instance['status'] === 'CONNECTED'): ?>
                        <form method="POST" action="<?= url('whatsapp/instances/' . $instance['id'] . '/disconnect') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 dark:bg-orange-600 dark:hover:bg-orange-500 text-white font-medium px-4 py-2 rounded-lg transition-colors shadow">
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
            <?php
            $qr = trim($instance['qr_code']);
            $qrSrc = '';
            $qrClean = preg_replace('/\s+/', '', $qr);
            $looksLikeBase64 = (strlen($qrClean) > 100 && preg_match('/^[A-Za-z0-9+\/=]+$/', $qrClean));
            $isPairCode = (strpos($qr, 'http') === 0) || (strlen($qrClean) < 100 && !$looksLikeBase64);
            if (strpos($qr, 'data:image') === 0) {
                $qrSrc = $qr;
            } elseif ($isPairCode) {
                $qrSrc = '';
            } else {
                $qrSrc = 'data:image/png;base64,' . $qrClean;
            }
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">QR Code para Conexão</h2>
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Escaneie este QR Code com o WhatsApp que deseja conectar
                    </p>
                    <?php if ($qrSrc): ?>
                        <img id="qrcode-img" 
                             src="<?= htmlspecialchars($qrSrc, ENT_QUOTES, 'UTF-8') ?>" 
                             alt="QR Code" 
                             class="mx-auto border-4 border-gray-200 dark:border-gray-700 rounded-lg max-w-xs w-64 h-64 object-contain bg-white"
                             onerror="this.style.display='none'; document.getElementById('qrcode-fallback').style.display='block';">
                        <div id="qrcode-fallback" style="display:none;" class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg max-w-xs mx-auto">
                            <p class="text-sm text-amber-800 dark:text-amber-200 mb-2">Imagem do QR não carregou.</p>
                            <p class="text-xs text-amber-700 dark:text-amber-300">Clique em &quot;Conectar&quot; novamente para gerar um novo QR Code.</p>
                        </div>
                    <?php else: ?>
                        <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg max-w-xs mx-auto">
                            <p class="text-sm text-gray-700 dark:text-gray-200 mb-2">Código de pareamento:</p>
                            <p class="font-mono text-xs break-all text-gray-900 dark:text-white"><?= htmlspecialchars($qr) ?></p>
                            <?php if (strpos($qr, 'http') === 0): ?>
                                <a href="<?= htmlspecialchars($qr) ?>" target="_blank" rel="noopener" class="inline-block mt-2 text-sm text-blue-600 dark:text-blue-400 hover:underline">Abrir link para parear</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                        O QR Code expira em alguns minutos. Se expirar, clique em &quot;Conectar&quot; novamente.
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
                            <button type="submit" class="text-sm bg-indigo-500 hover:bg-indigo-600 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-medium px-3 py-1.5 rounded-lg transition-colors shadow">
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

