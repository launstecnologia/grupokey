<?php
$currentPage = 'modais-representante';
ob_start();
$modals = $modals ?? [];
$filters = $filters ?? [];
?>

<div class="pt-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-window-maximize mr-2"></i>Modais Representante</h1>
            <p class="text-gray-600 mt-1">Mensagens programadas que abrem para representantes no acesso ao sistema.</p>
        </div>
        <a href="<?= url('modais-representante/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">Novo Modal</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?><div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg"><?= htmlspecialchars($_SESSION['success']) ?></div><?php unset($_SESSION['success']); endif; ?>
    <?php if (isset($_SESSION['error'])): ?><div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg"><?= htmlspecialchars($_SESSION['error']) ?></div><?php unset($_SESSION['error']); endif; ?>

    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" action="<?= url('modais-representante') ?>" class="flex gap-3">
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Buscar..." class="flex-1 px-3 py-2 border border-gray-300 rounded-md">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-md">
                <option value="">Todos</option>
                <option value="1" <?= (($filters['is_active'] ?? '') === 1) ? 'selected' : '' ?>>Ativo</option>
                <option value="0" <?= (($filters['is_active'] ?? '') === 0) ? 'selected' : '' ?>>Inativo</option>
            </select>
            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md">Filtrar</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disparo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Público</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($modals)): ?>
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Nenhum modal cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($modals as $m): ?>
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars((string) ($m['title'] ?? 'Sem título')) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars((string) ($m['trigger_type'] ?? '')) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?= (string) ($m['audience_type'] ?? 'all') === 'selected' ? 'Selecionados' : 'Todos' ?></td>
                            <td class="px-6 py-4"><?php if ((int) ($m['is_active'] ?? 0) === 1): ?><span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Ativo</span><?php else: ?><span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inativo</span><?php endif; ?></td>
                            <td class="px-6 py-4 text-right text-sm">
                                <a href="<?= url('modais-representante/' . (int) $m['id'] . '/edit') ?>" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="<?= url('modais-representante/' . (int) $m['id']) ?>" class="inline" onsubmit="return confirm('Deseja excluir este modal?');">
                                    <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
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

