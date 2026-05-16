<?php
$currentPage = 'tipos-documentos';
ob_start();

$type = $type ?? null;
$isEdit = $type !== null;
$old = $_SESSION['old_input'] ?? [];

$code = $old['code'] ?? ($type['code'] ?? '');
$label = $old['label'] ?? ($type['label'] ?? '');
$sortOrder = $old['sort_order'] ?? ($type['sort_order'] ?? 0);
$isActive = (string) ($old['is_active'] ?? ($type['is_active'] ?? 1));
?>

<div class="pt-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-file-signature mr-2"></i>
                <?= $isEdit ? 'Editar Tipo de Documento' : 'Novo Tipo de Documento' ?>
            </h1>
            <p class="text-gray-600 mt-1">Defina os tipos que aparecerão no cadastro/edição de estabelecimentos.</p>
        </div>
        <a href="<?= url('tipos-documentos') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Voltar
        </a>
    </div>

    <?php if (isset($_SESSION['validation_errors']) && !empty($_SESSION['validation_errors'])): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <strong class="block mb-2">Erros encontrados:</strong>
            <ul class="list-disc list-inside space-y-1">
                <?php foreach ($_SESSION['validation_errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['validation_errors']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Dados do Tipo de Documento</h3>
        </div>
        <form method="POST" action="<?= $isEdit ? url('tipos-documentos/' . (int) $type['id']) : url('tipos-documentos') ?>" class="p-6">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                    <input type="text" name="code" value="<?= htmlspecialchars((string) $code) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md uppercase" placeholder="EX: COMPROVANTE_ENDERECO" required>
                    <p class="mt-1 text-xs text-gray-500">Somente letras maiúsculas, números e underscore.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="1" <?= $isActive === '1' ? 'selected' : '' ?>>Ativo</option>
                        <option value="0" <?= $isActive === '0' ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome/Descrição *</label>
                    <input type="text" name="label" value="<?= htmlspecialchars((string) $label) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Ex: COMPROVANTE DE ENDEREÇO COMERCIAL (Da loja)" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                    <input type="number" name="sort_order" value="<?= (int) $sortOrder ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="<?= url('tipos-documentos') ?>" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<?php
unset($_SESSION['old_input']);
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

