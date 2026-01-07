<?php
$currentPage = 'crm';
ob_start();

$pipeline = $pipeline ?? [];
?>

<div class="pt-6 px-4 max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="<?= url('crm/pipelines') ?>" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <i class="fas fa-arrow-left mr-2"></i>Voltar
        </a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Editar Pipeline</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= url('crm/pipelines/' . $pipeline['id']) ?>" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT">
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($pipeline['name'] ?? '') ?>" required 
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descrição</label>
            <textarea name="description" rows="3" 
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"><?= htmlspecialchars($pipeline['description'] ?? '') ?></textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cor</label>
            <input type="color" name="color" value="<?= htmlspecialchars($pipeline['color'] ?? '#3B82F6') ?>" 
                   class="w-full h-10 border border-gray-300 dark:border-gray-600 rounded-lg">
        </div>

        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="is_default" value="1" <?= !empty($pipeline['is_default']) ? 'checked' : '' ?> 
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Pipeline padrão</span>
            </label>
        </div>

        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" <?= !empty($pipeline['is_active']) ? 'checked' : '' ?> 
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Ativo</span>
            </label>
        </div>

        <div class="flex justify-end gap-2">
            <a href="<?= url('crm/pipelines') ?>" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Salvar
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
?>

