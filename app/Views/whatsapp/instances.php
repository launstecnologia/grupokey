<?php
$currentPage = 'whatsapp';
ob_start();

$instances = $instances ?? [];
?>

<div class="pt-6 px-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <i class="fab fa-whatsapp mr-2 text-green-500"></i>
                    Instâncias WhatsApp
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Gerencie suas instâncias do WhatsApp</p>
            </div>
            <div>
                <a href="<?= url('whatsapp/instances/create') ?>" class="bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-500 text-white font-medium px-4 py-2 rounded-lg inline-flex items-center transition-colors shadow-md">
                    <i class="fas fa-plus mr-2"></i>
                    Nova Instância
                </a>
            </div>
        </div>

        <!-- Lista de Instâncias -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($instances)): ?>
                <div class="col-span-full bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
                    <i class="fab fa-whatsapp text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Nenhuma instância configurada</p>
                    <a href="<?= url('whatsapp/instances/create') ?>" class="bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-500 text-white font-medium px-4 py-2 rounded-lg inline-flex items-center shadow-md">
                        <i class="fas fa-plus mr-2"></i>
                        Criar Primeira Instância
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($instances as $instance): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white text-lg">
                                    <?= htmlspecialchars($instance['name']) ?>
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($instance['instance_key']) ?>
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?php
                                echo $instance['status'] === 'CONNECTED' ? 'bg-green-100 text-green-800 dark:bg-green-600 dark:text-white' : '';
                                echo $instance['status'] === 'CONNECTING' ? 'bg-yellow-100 text-yellow-800 dark:bg-amber-500 dark:text-white' : '';
                                echo $instance['status'] === 'DISCONNECTED' ? 'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-100' : '';
                            ?>">
                                <?= htmlspecialchars($instance['status']) ?>
                            </span>
                        </div>
                        
                        <?php if ($instance['phone_number']): ?>
                            <div class="mb-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Número: </span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($instance['phone_number']) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex flex-wrap gap-2 mt-4">
                            <a href="<?= url('whatsapp/instances/' . $instance['id']) ?>" 
                               class="flex-1 min-w-[80px] bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500 text-white font-medium px-4 py-2 rounded-lg text-center text-sm transition-colors shadow">
                                <i class="fas fa-eye mr-1"></i>
                                Ver
                            </a>
                            
                            <?php if ($instance['status'] === 'DISCONNECTED'): ?>
                                <form method="POST" action="<?= url('whatsapp/instances/' . $instance['id'] . '/connect') ?>" class="flex-1 min-w-[80px]">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-500 text-white font-medium px-4 py-2 rounded-lg text-sm transition-colors shadow">
                                        <i class="fas fa-link mr-1"></i>
                                        Conectar
                                    </button>
                                </form>
                            <?php elseif ($instance['status'] === 'CONNECTED'): ?>
                                <form method="POST" action="<?= url('whatsapp/instances/' . $instance['id'] . '/disconnect') ?>" class="flex-1 min-w-[80px]">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 dark:bg-orange-600 dark:hover:bg-orange-500 text-white font-medium px-4 py-2 rounded-lg text-sm transition-colors shadow">
                                        <i class="fas fa-unlink mr-1"></i>
                                        Desconectar
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" action="<?= url('whatsapp/instances/' . $instance['id'] . '/delete') ?>" class="flex-1 min-w-[80px]" onsubmit="return confirm('Excluir esta instância? A ação não pode ser desfeita.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500 text-white font-medium px-4 py-2 rounded-lg text-sm transition-colors shadow">
                                    <i class="fas fa-trash-alt mr-1"></i>
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

