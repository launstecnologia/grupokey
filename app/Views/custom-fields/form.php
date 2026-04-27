<?php
$currentPage = 'campos-dinamicos';
ob_start();

$field = $field ?? null;
$isEdit = $field !== null;
$oldInput = $_SESSION['old_input'] ?? [];

$entityValue = $oldInput['entity_type'] ?? ($field['entity_type'] ?? 'establishment');
$keyValue = $oldInput['field_key'] ?? ($field['field_key'] ?? '');
$labelValue = $oldInput['label'] ?? ($field['label'] ?? '');
$typeValue = $oldInput['field_type'] ?? ($field['field_type'] ?? 'text');
$requiredValue = isset($oldInput['is_required'])
    ? ($oldInput['is_required'] === '1')
    : ((int)($field['is_required'] ?? 0) === 1);
$placeholderValue = $oldInput['placeholder'] ?? ($field['placeholder'] ?? '');
$helpTextValue = $oldInput['help_text'] ?? ($field['help_text'] ?? '');
$sortValue = $oldInput['sort_order'] ?? ($field['sort_order'] ?? 1);
$optionsValue = $oldInput['options_text'] ?? implode("\n", (array)($field['options'] ?? []));
?>

<div class="pt-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-sliders-h mr-2"></i>
                <?= $isEdit ? 'Editar Campo Dinâmico' : 'Novo Campo Dinâmico' ?>
            </h1>
            <p class="text-gray-600 mt-1">Defina campos extras para os cadastros de estabelecimento e representante.</p>
        </div>
        <a href="<?= url('campos-dinamicos') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
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

    <form method="POST" action="<?= $isEdit ? url('campos-dinamicos/' . $field['id']) : url('campos-dinamicos') ?>" class="space-y-6">
        <?= csrf_field() ?>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuração do Campo</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Entidade *</label>
                    <select name="entity_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="establishment" <?= $entityValue === 'establishment' ? 'selected' : '' ?>>Estabelecimento</option>
                        <option value="representative" <?= $entityValue === 'representative' ? 'selected' : '' ?>>Representante</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                    <input type="number" min="1" name="sort_order" value="<?= (int)$sortValue ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chave *</label>
                    <input type="text" name="field_key" value="<?= htmlspecialchars($keyValue) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="ex: nome_mae" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rótulo *</label>
                    <input type="text" name="label" value="<?= htmlspecialchars($labelValue) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Nome da mãe" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="field_type" id="field_type" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <option value="text" <?= $typeValue === 'text' ? 'selected' : '' ?>>Texto</option>
                        <option value="number" <?= $typeValue === 'number' ? 'selected' : '' ?>>Número</option>
                        <option value="email" <?= $typeValue === 'email' ? 'selected' : '' ?>>E-mail</option>
                        <option value="date" <?= $typeValue === 'date' ? 'selected' : '' ?>>Data</option>
                        <option value="datetime-local" <?= $typeValue === 'datetime-local' ? 'selected' : '' ?>>Data e hora</option>
                        <option value="textarea" <?= $typeValue === 'textarea' ? 'selected' : '' ?>>Texto longo</option>
                        <option value="select" <?= $typeValue === 'select' ? 'selected' : '' ?>>Seleção</option>
                        <option value="currency" <?= $typeValue === 'currency' ? 'selected' : '' ?>>Moeda</option>
                        <option value="phone" <?= $typeValue === 'phone' ? 'selected' : '' ?>>Telefone</option>
                        <option value="cpf" <?= $typeValue === 'cpf' ? 'selected' : '' ?>>CPF</option>
                        <option value="cnpj" <?= $typeValue === 'cnpj' ? 'selected' : '' ?>>CNPJ</option>
                    </select>
                </div>
                <div class="flex items-center mt-7">
                    <input type="hidden" name="is_required" value="0">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_required" value="1" <?= $requiredValue ? 'checked' : '' ?> class="mr-2">
                        <span class="text-sm text-gray-700">Obrigatório</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Placeholder</label>
                    <input type="text" name="placeholder" value="<?= htmlspecialchars($placeholderValue) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Texto de ajuda</label>
                    <input type="text" name="help_text" value="<?= htmlspecialchars($helpTextValue) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div id="options-wrapper" class="md:col-span-2 <?= $typeValue === 'select' ? '' : 'hidden' ?>">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opções (uma por linha ou separadas por vírgula)</label>
                    <textarea name="options_text" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Opção 1&#10;Opção 2"><?= htmlspecialchars($optionsValue) ?></textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mb-8">
            <a href="<?= url('campos-dinamicos') ?>" class="px-5 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i><?= $isEdit ? 'Salvar Alterações' : 'Salvar Campo' ?>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('field_type');
    const optionsWrapper = document.getElementById('options-wrapper');

    function syncOptionsVisibility() {
        if (!typeSelect || !optionsWrapper) return;
        optionsWrapper.classList.toggle('hidden', typeSelect.value !== 'select');
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', syncOptionsVisibility);
    }

    syncOptionsVisibility();
});
</script>

<?php
unset($_SESSION['old_input']);
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
