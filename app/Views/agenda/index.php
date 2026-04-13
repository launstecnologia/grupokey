<?php
$currentPage = 'agenda';
ob_start();

$contacts = $contacts ?? [];
$filters = $filters ?? [];
?>

<div class="pt-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-address-book mr-2 text-green-500"></i>
                Agenda de Contatos
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Cadastre e gerencie seus contatos</p>
        </div>
        <div>
            <a href="<?= url('agenda/create') ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Novo Contato
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" action="<?= url('agenda') ?>" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                       placeholder="Buscar por nome, telefone ou e-mail..."
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-search mr-2"></i>Buscar
                </button>
            </div>
            <?php if (!empty($filters)): ?>
            <div>
                <a href="<?= url('agenda') ?>" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-lg">
                    Limpar
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Telefone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">E-mail</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (empty($contacts)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-address-book text-4xl mb-2 opacity-50"></i>
                        <p>Nenhum contato cadastrado. <a href="<?= url('agenda/create') ?>" class="text-green-600 dark:text-green-400 hover:underline">Cadastrar primeiro contato</a></p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($contacts as $c): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($c['name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            <a href="<?= url('whatsapp/attendance') ?>?phone=<?= urlencode($c['phone']) ?>" class="text-green-600 dark:text-green-400 hover:underline inline-flex items-center">
                                <i class="fab fa-whatsapp mr-1"></i> <?= htmlspecialchars($c['phone']) ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($c['email'] ?? '-') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?= url('whatsapp/attendance') ?>?phone=<?= urlencode($c['phone']) ?>" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 mr-3" title="Chamar no WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="<?= url('agenda/' . $c['id'] . '/edit') ?>" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mr-3" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= url('agenda/' . $c['id']) ?>" class="inline" onsubmit="return confirm('Remover este contato?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300" title="Excluir">
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
