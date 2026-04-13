<?php
// Habilitar exibição de erros para debug (apenas se não estiver em produção)
if (defined('APP_DEBUG') && APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Garantir que não há output antes
while (ob_get_level() > 0) {
    ob_end_clean();
}

$currentPage = 'email-marketing';
ob_start();

// Tratamento de erros
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (error_reporting() === 0) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}, E_ALL & ~E_NOTICE & ~E_WARNING);

$campaign = $campaign ?? [];
$attachments = $attachments ?? [];
$recipients = $recipients ?? [];
$queueStats = $queueStats ?? [];

$statusColors = [
    'DRAFT' => 'bg-gray-100 text-black dark:bg-gray-700 dark:text-black',
    'SCHEDULED' => 'bg-blue-100 text-black dark:bg-blue-900 dark:text-black',
    'SENDING' => 'bg-yellow-100 text-black dark:bg-yellow-900 dark:text-black',
    'COMPLETED' => 'bg-green-100 text-black dark:bg-green-900 dark:text-black',
    'PAUSED' => 'bg-orange-100 text-black dark:bg-orange-900 dark:text-black',
    'CANCELLED' => 'bg-red-100 text-black dark:bg-red-900 dark:text-black'
];

$statusLabels = [
    'DRAFT' => 'Rascunho',
    'SCHEDULED' => 'Agendada',
    'SENDING' => 'Enviando',
    'COMPLETED' => 'Concluída',
    'PAUSED' => 'Pausada',
    'CANCELLED' => 'Cancelada'
];

$total = $campaign['total_recipients'] ?? 0;
$sent = $campaign['sent_count'] ?? 0;
$failed = $campaign['failed_count'] ?? 0;
$progress = $total > 0 ? round(($sent / $total) * 100) : 0;
?>

<div class="pt-6 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-envelope mr-2"></i>
                    <?= htmlspecialchars($campaign['name'] ?? 'Campanha') ?>
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Detalhes da campanha</p>
            </div>
            <div class="flex gap-2">
                <?php if ($campaign['status'] === 'DRAFT' || $campaign['status'] === 'PAUSED'): ?>
                    <a href="<?= url('email-marketing/' . $campaign['id'] . '/recipients') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                        <i class="fas fa-users mr-2"></i>
                        Selecionar Destinatários
                    </a>
                    <a href="<?= url('email-marketing/' . $campaign['id'] . '/edit') ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                        <i class="fas fa-edit mr-2"></i>
                        Editar
                    </a>
                    <?php if ($total > 0): ?>
                        <form method="POST" action="<?= url('email-marketing/' . $campaign['id'] . '/start') ?>" class="inline" onsubmit="return confirm('Deseja iniciar o envio desta campanha?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Iniciar Envio
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($campaign['status'] === 'SENDING'): ?>
                    <form method="POST" action="<?= url('email-marketing/' . $campaign['id'] . '/pause') ?>" class="inline" onsubmit="return confirm('Deseja pausar esta campanha?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                            <i class="fas fa-pause mr-2"></i>
                            Pausar
                        </button>
                    </form>
                <?php endif; ?>
                <a href="<?= url('email-marketing') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
        </div>

        <!-- Mensagens -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Informações Principais -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Status e Estatísticas -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status e Estatísticas</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <div class="mt-1">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full <?= $statusColors[$campaign['status']] ?? $statusColors['DRAFT'] ?>">
                                    <?= $statusLabels[$campaign['status']] ?? $campaign['status'] ?>
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Progresso</label>
                            <div class="mt-2">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-600 dark:text-gray-400"><?= $sent ?> de <?= $total ?> enviados</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white"><?= $progress ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: <?= $progress ?>%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Enviados</label>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?= $sent ?></p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Falhas</label>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400"><?= $failed ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo do E-mail -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Conteúdo do E-mail</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Assunto</label>
                            <p class="mt-1 text-gray-900 dark:text-white"><?= htmlspecialchars($campaign['subject'] ?? '') ?></p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Corpo do E-mail</label>
                            <div class="mt-1 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="prose dark:prose-invert max-w-none">
                                    <?= $campaign['body'] ?? '' ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($campaign['signature'])): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Assinatura</label>
                                <div class="mt-1 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="prose dark:prose-invert max-w-none">
                                        <?= $campaign['signature'] ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Anexos -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Anexos</h2>
                    <?php if (!empty($attachments)): ?>
                        <div class="space-y-2">
                            <?php foreach ($attachments as $attachment): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-paperclip mr-2 text-gray-500 dark:text-gray-400"></i>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            <?= htmlspecialchars($attachment['file_name']) ?>
                                            (<?= number_format($attachment['file_size'] / 1024, 2) ?> KB)
                                        </span>
                                    </div>
                                    <?php
                                    try {
                                        // Construir caminho e URL do arquivo
                                        $filePath = $attachment['file_path'] ?? '';
                                        $absolutePath = null;
                                        $fileUrl = null;
                                        $fileExists = false;
                                        
                                        if (!empty($filePath)) {
                                            // Raiz do projeto: view está em app/Views/email-marketing -> 3 níveis
                                            $basePath = dirname(__DIR__, 3);
                                            // Se o caminho já começa com 'storage/', usar como relativo
                                            if (strpos($filePath, 'storage' . DIRECTORY_SEPARATOR) === 0 || strpos($filePath, 'storage/') === 0) {
                                                // Caminho relativo - construir caminho absoluto
                                                $absolutePath = $basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
                                                if (function_exists('url')) {
                                                    $fileUrl = url(str_replace(DIRECTORY_SEPARATOR, '/', $filePath));
                                                }
                                            }
                                            // Se é caminho absoluto do servidor, tentar extrair parte relativa
                                            elseif (strpos($filePath, DIRECTORY_SEPARATOR) === 0 || strpos($filePath, '/') === 0 || (strlen($filePath) > 2 && $filePath[1] === ':')) {
                                                // Caminho absoluto - tentar encontrar parte relativa
                                                $absolutePath = $filePath;
                                                
                                                // Tentar extrair parte após 'storage/uploads'
                                                if (strpos($filePath, 'storage' . DIRECTORY_SEPARATOR . 'uploads') !== false) {
                                                    $relativePart = substr($filePath, strpos($filePath, 'storage' . DIRECTORY_SEPARATOR . 'uploads'));
                                                    if (function_exists('url')) {
                                                        $fileUrl = url(str_replace(DIRECTORY_SEPARATOR, '/', $relativePart));
                                                    }
                                                } elseif (strpos($filePath, 'storage/uploads') !== false) {
                                                    $relativePart = substr($filePath, strpos($filePath, 'storage/uploads'));
                                                    if (function_exists('url')) {
                                                        $fileUrl = url($relativePart);
                                                    }
                                                } else {
                                                    // Fallback: usar apenas o nome do arquivo
                                                    if (function_exists('url')) {
                                                        $fileUrl = url('storage/uploads/email-attachments/' . basename($filePath));
                                                    }
                                                }
                                            }
                                            // Fallback: assumir que é relativo (email-attachments/arquivo.ext)
                                            else {
                                                $absolutePath = $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
                                                if (function_exists('url')) {
                                                    $fileUrl = url('storage/uploads/' . str_replace(['\\', DIRECTORY_SEPARATOR], '/', $filePath));
                                                }
                                            }
                                            
                                            // Verificar se o arquivo existe
                                            if ($absolutePath) {
                                                $fileExists = @file_exists($absolutePath);
                                            }
                                            
                                            // Se não encontrou, tentar caminho direto do banco
                                            if (!$fileExists && $filePath) {
                                                $fileExists = @file_exists($filePath);
                                                if ($fileExists) {
                                                    $absolutePath = $filePath;
                                                }
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        // Em caso de erro, definir valores padrão
                                        $fileExists = false;
                                        $fileUrl = null;
                                        $absolutePath = null;
                                    }
                                    ?>
                                    <?php if ($fileExists && $fileUrl): ?>
                                        <a href="<?= $fileUrl ?>" 
                                           target="_blank"
                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                           title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-red-500 text-xs" title="Arquivo não encontrado no servidor">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum anexo adicionado a esta campanha.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Informações Gerais -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informações</h2>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Criado em</label>
                            <p class="text-sm text-gray-900 dark:text-white">
                                <?= date('d/m/Y H:i', strtotime($campaign['created_at'] ?? 'now')) ?>
                            </p>
                        </div>

                        <?php if (!empty($campaign['scheduled_at'])): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Agendado para</label>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <?= date('d/m/Y H:i', strtotime($campaign['scheduled_at'])) ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($campaign['started_at'])): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Iniciado em</label>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <?= date('d/m/Y H:i', strtotime($campaign['started_at'])) ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($campaign['completed_at'])): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Concluído em</label>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <?= date('d/m/Y H:i', strtotime($campaign['completed_at'])) ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($campaign['created_by_user_name'])): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Criado por</label>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($campaign['created_by_user_name']) ?>
                                </p>
                            </div>
                        <?php elseif (!empty($campaign['created_by_representative_name'])): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Criado por</label>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($campaign['created_by_representative_name']) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ações</h2>
                    
                    <div class="space-y-2">
                        <a href="<?= url('email-marketing/' . $campaign['id'] . '/recipients') ?>" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors font-semibold">
                            <i class="fas fa-users mr-2"></i>
                            Selecionar Destinatários
                        </a>
                        
                        <?php if ($total === 0): ?>
                            <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <p class="text-xs text-yellow-800 dark:text-yellow-300 text-center">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Adicione destinatários antes de iniciar o envio
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($campaign['status'] === 'DRAFT' || $campaign['status'] === 'PAUSED'): ?>
                            <a href="<?= url('email-marketing/' . $campaign['id'] . '/edit') ?>" class="block w-full text-center bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-edit mr-2"></i>
                                Editar Campanha
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Restaurar handler de erros
restore_error_handler();

try {
    // Capturar conteúdo do buffer
    if (ob_get_level() > 0) {
        $content = ob_get_clean();
    } else {
        $content = '';
    }
    
    // Verificar se o conteúdo foi capturado
    if (empty($content) && ob_get_level() > 0) {
        // Tentar capturar novamente
        $content = ob_get_clean();
    }
    
    // Garantir que o conteúdo está definido
    if (!isset($content)) {
        $content = '';
    }
    
    // Verificar se o layout existe
    $layoutPath = __DIR__ . '/../layouts/app.php';
    if (!file_exists($layoutPath)) {
        // Tentar caminho alternativo
        $altPath = dirname(__DIR__, 2) . '/Views/layouts/app.php';
        if (file_exists($altPath)) {
            $layoutPath = $altPath;
        }
    }
    
    // Incluir o layout
    if (file_exists($layoutPath)) {
        // Garantir que as variáveis necessárias estão definidas
        if (!isset($title)) {
            $title = $campaign['name'] ?? 'Detalhes da Campanha';
        }
        if (!isset($currentPage)) {
            $currentPage = 'email-marketing';
        }
        
        include $layoutPath;
    } else {
        // Fallback: renderizar sem layout se não encontrar (apenas para debug)
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Erro</title></head><body>';
        echo '<h1>Erro: Layout não encontrado</h1>';
        echo '<p>Layout esperado em: ' . htmlspecialchars($layoutPath) . '</p>';
        echo '<p>Diretório atual: ' . htmlspecialchars(__DIR__) . '</p>';
        echo '<p>Arquivo existe: ' . (file_exists($layoutPath) ? 'SIM' : 'NÃO') . '</p>';
        echo '<hr>';
        echo '<h2>Conteúdo da view (' . strlen($content) . ' caracteres):</h2>';
        echo '<div style="border: 1px solid #ccc; padding: 10px;">' . htmlspecialchars($content) . '</div>';
        echo '</body></html>';
        exit;
    }
} catch (\Throwable $e) {
    // Capturar qualquer erro e exibir
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Erro</title></head><body>';
    echo '<h1>Erro ao renderizar view</h1>';
    echo '<p><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
    echo '<p><strong>Linha:</strong> ' . $e->getLine() . '</p>';
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
    echo '</body></html>';
    exit;
}
?>

