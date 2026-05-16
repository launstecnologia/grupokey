<?php
$currentPage = 'tipos-documentos';
ob_start();

$types = $types ?? [];
$filters = $filters ?? [];
?>

<div class="pt-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-file-signature mr-2"></i>
                Tipos de Documento
            </h1>
            <p class="text-gray-600 mt-1">Cadastre e gerencie os tipos usados nos documentos dos estabelecimentos.</p>
        </div>
        <a href="<?= url('tipos-documentos/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
            <i class="fas fa-plus mr-2"></i>Novo Tipo
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
        <form method="GET" action="<?= url('tipos-documentos') ?>" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Buscar por código ou nome..." class="flex-1 min-w-[220px] px-3 py-2 border border-gray-300 rounded-md">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-md">
                <option value="">Todos os status</option>
                <option value="1" <?= (($filters['is_active'] ?? null) === 1) ? 'selected' : '' ?>>Ativo</option>
                <option value="0" <?= (($filters['is_active'] ?? null) === 0) ? 'selected' : '' ?>>Inativo</option>
            </select>
            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="<?= url('tipos-documentos') ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">Limpar</a>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordem</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($types)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-gray-500">Nenhum tipo de documento encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($types as $type): ?>
                        <tr>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900"><?= htmlspecialchars($type['code']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($type['label']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?= (int) ($type['sort_order'] ?? 0) ?></td>
                            <td class="px-6 py-4 text-sm">
                                <?php if ((int) ($type['is_active'] ?? 0) === 1): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Ativo</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <a href="<?= url('tipos-documentos/' . (int) $type['id'] . '/edit') ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="<?= url('tipos-documentos/' . (int) $type['id']) ?>" class="inline" onsubmit="return confirm('Deseja excluir este tipo de documento?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
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

