<?php
$currentPage = 'produtos-dinamicos';
ob_start();

$products = $products ?? [];
$filters = $filters ?? [];
?>

<div class="pt-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-boxes mr-2"></i>
                Produtos Dinâmicos
            </h1>
            <p class="text-gray-600 mt-1">Cadastre produtos e monte campos sem depender de código.</p>
        </div>
        <a href="<?= url('produtos-dinamicos/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Novo Produto Dinâmico
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" action="<?= url('produtos-dinamicos') ?>" class="flex gap-3">
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Buscar por nome, slug..." class="flex-1 px-3 py-2 border border-gray-300 rounded-md">
            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <?php if (!empty($filters['search'])): ?>
                <a href="<?= url('produtos-dinamicos') ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campos</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Nenhum produto dinâmico cadastrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($product['description'] ?? '') ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($product['slug']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?= (int) ($product['total_fields'] ?? 0) ?></td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="<?= url('produtos-dinamicos/' . $product['id'] . '/duplicate') ?>" class="inline mr-3" onsubmit="return confirm('Deseja duplicar este produto dinâmico?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900" title="Duplicar">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </form>
                                <a href="<?= url('produtos-dinamicos/' . $product['id'] . '/edit') ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="<?= url('produtos-dinamicos/' . $product['id'] . '/delete') ?>" class="inline" onsubmit="return confirm('Deseja remover este produto dinâmico?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
