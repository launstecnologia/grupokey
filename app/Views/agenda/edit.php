<?php
$currentPage = 'agenda';
ob_start();

$contact = $contact ?? [];
?>

<div class="pt-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-user-edit mr-2 text-green-500"></i>
                Editar Contato
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Altere os dados do contato</p>
        </div>
        <div>
            <a href="<?= url('agenda') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                <i class="fas fa-address-card mr-2 text-green-500"></i>
                Dados do Contato
            </h3>
        </div>

        <form method="POST" action="<?= url('agenda/' . $contact['id']) ?>" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="PUT">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome *</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($contact['name'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500"
                           placeholder="Nome completo">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telefone (WhatsApp) *</label>
                    <input type="text" name="phone" required value="<?= htmlspecialchars($contact['phone'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500"
                           placeholder="5516999999999">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">E-mail</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($contact['email'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500"
                           placeholder="email@exemplo.com">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observações</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500"
                              placeholder="Anotações"><?= htmlspecialchars($contact['notes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="<?= url('agenda') ?>" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 px-6 py-2 rounded-lg">
                    Cancelar
                </a>
                <a href="<?= url('whatsapp/attendance') ?>?phone=<?= urlencode($contact['phone'] ?? '') ?>" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg inline-flex items-center">
                    <i class="fab fa-whatsapp mr-2"></i>Chamar no WhatsApp
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
