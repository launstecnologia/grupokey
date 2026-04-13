<?php
$currentPage = 'produtos-dinamicos';
ob_start();

$product = $product ?? null;
$isEdit = $product !== null;
$oldInput = $_SESSION['old_input'] ?? [];

if (!empty($oldInput)) {
    $formName = $oldInput['name'] ?? '';
    $formSlug = $oldInput['slug'] ?? '';
    $formDescription = $oldInput['description'] ?? '';
    $formHasApi = isset($oldInput['has_api']) && $oldInput['has_api'] === '1';
    $formApiProvider = $oldInput['api_provider'] ?? '';
    $formApiConfig = $oldInput['api_config_json'] ?? '';

    $fieldKeys = $oldInput['field_key'] ?? [];
    $fieldLabels = $oldInput['field_label'] ?? [];
    $fieldTypes = $oldInput['field_type'] ?? [];
    $fieldRequired = $oldInput['field_required'] ?? [];
    $fieldPlaceholders = $oldInput['field_placeholder'] ?? [];
    $fieldHelpTexts = $oldInput['field_help_text'] ?? [];
    $fieldOptions = $oldInput['field_options'] ?? [];

    $initialFields = [];
    $totalRows = max(count($fieldKeys), count($fieldLabels), 1);
    for ($i = 0; $i < $totalRows; $i++) {
        $initialFields[] = [
            'field_key' => $fieldKeys[$i] ?? '',
            'label' => $fieldLabels[$i] ?? '',
            'field_type' => $fieldTypes[$i] ?? 'text',
            'is_required' => isset($fieldRequired[$i]) && $fieldRequired[$i] === '1',
            'placeholder' => $fieldPlaceholders[$i] ?? '',
            'help_text' => $fieldHelpTexts[$i] ?? '',
            'options_text' => $fieldOptions[$i] ?? ''
        ];
    }
} else {
    $formName = $product['name'] ?? '';
    $formSlug = $product['slug'] ?? '';
    $formDescription = $product['description'] ?? '';
    $formHasApi = (int) ($product['has_api'] ?? 0) === 1;
    $formApiProvider = $product['api_provider'] ?? '';
    $formApiConfig = $product['api_config_json'] ?? '';

    $initialFields = [];
    if (!empty($product['fields'])) {
        foreach ($product['fields'] as $field) {
            $optionsText = '';
            if (!empty($field['options'])) {
                $labels = array_map(function ($option) {
                    return $option['option_label'];
                }, $field['options']);
                $optionsText = implode("\n", $labels);
            }

            $initialFields[] = [
                'field_key' => $field['field_key'] ?? '',
                'label' => $field['label'] ?? '',
                'field_type' => $field['field_type'] ?? 'text',
                'is_required' => (int) ($field['is_required'] ?? 0) === 1,
                'placeholder' => $field['placeholder'] ?? '',
                'help_text' => $field['help_text'] ?? '',
                'options_text' => $optionsText
            ];
        }
    }
}

if (empty($initialFields)) {
    $initialFields[] = [
        'field_key' => '',
        'label' => '',
        'field_type' => 'text',
        'is_required' => false,
        'placeholder' => '',
        'help_text' => '',
        'options_text' => ''
    ];
}
?>

<div class="pt-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-drafting-compass mr-2"></i>
                <?= $isEdit ? 'Editar Produto Dinâmico' : 'Novo Produto Dinâmico' ?>
            </h1>
            <p class="text-gray-600 mt-1">Defina campos e opções que o admin vai usar no cadastro de estabelecimentos.</p>
        </div>
        <a href="<?= url('produtos-dinamicos') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
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

    <form method="POST" action="<?= $isEdit ? url('produtos-dinamicos/' . $product['id']) : url('produtos-dinamicos') ?>" class="space-y-6">
        <?= csrf_field() ?>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dados do Produto</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($formName) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug (opcional)</label>
                    <input type="text" name="slug" value="<?= htmlspecialchars($formSlug) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="ex: muse_api">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars($formDescription) ?></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Integração API</h3>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="has_api" value="1" <?= $formHasApi ? 'checked' : '' ?> class="mr-2">
                    <span class="text-sm text-gray-700">Produto possui API</span>
                </label>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provedor/API</label>
                    <input type="text" name="api_provider" value="<?= htmlspecialchars($formApiProvider) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="ex: MUSE">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Configuração JSON</label>
                    <textarea name="api_config_json" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder='{"endpoint":"https://api.exemplo.com","auth":"bearer"}'><?= htmlspecialchars($formApiConfig) ?></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Campos Dinâmicos</h3>
                <button type="button" id="add-field-row" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md">
                    <i class="fas fa-plus mr-1"></i>Adicionar Campo
                </button>
            </div>

            <div id="fields-container" class="space-y-4">
                <?php foreach ($initialFields as $field): ?>
                    <div class="field-row border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                            <div>
                                <label class="text-xs font-medium text-gray-600">Chave *</label>
                                <input type="text" name="field_key[]" value="<?= htmlspecialchars($field['field_key']) ?>" class="w-full px-2 py-2 border border-gray-300 rounded-md" placeholder="previsao_faturamento">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-600">Rótulo *</label>
                                <input type="text" name="field_label[]" value="<?= htmlspecialchars($field['label']) ?>" class="w-full px-2 py-2 border border-gray-300 rounded-md" placeholder="Previsão de Faturamento">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-600">Tipo *</label>
                                <select name="field_type[]" class="w-full px-2 py-2 border border-gray-300 rounded-md field-type-select">
                                    <?php
                                    $types = ['text', 'number', 'email', 'date', 'datetime-local', 'textarea', 'select', 'currency', 'phone', 'cpf', 'cnpj'];
                                    $typeLabels = [
                                        'text' => 'Texto',
                                        'number' => 'Número',
                                        'email' => 'E-mail',
                                        'date' => 'Data',
                                        'datetime-local' => 'Data e Hora',
                                        'textarea' => 'Texto Longo',
                                        'select' => 'Seleção',
                                        'currency' => 'Moeda',
                                        'phone' => 'Telefone',
                                        'cpf' => 'CPF',
                                        'cnpj' => 'CNPJ'
                                    ];
                                    foreach ($types as $type):
                                    ?>
                                        <option value="<?= $type ?>" <?= $field['field_type'] === $type ? 'selected' : '' ?>><?= $typeLabels[$type] ?? $type ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-600">Texto de apoio</label>
                                <input type="text" name="field_placeholder[]" value="<?= htmlspecialchars($field['placeholder']) ?>" class="w-full px-2 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-600">Texto de ajuda</label>
                                <input type="text" name="field_help_text[]" value="<?= htmlspecialchars($field['help_text']) ?>" class="w-full px-2 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div class="flex items-end gap-2">
                                <label class="inline-flex items-center mb-2">
                                    <input type="hidden" name="field_required[]" value="<?= $field['is_required'] ? '1' : '0' ?>">
                                    <input type="checkbox" <?= $field['is_required'] ? 'checked' : '' ?> class="mr-2 required-toggle">
                                    <span class="text-sm text-gray-700">Obrigatório</span>
                                </label>
                                <button type="button" class="remove-field-row text-red-600 hover:text-red-800 px-2 py-2" title="Remover campo">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-3 select-options-block <?= $field['field_type'] === 'select' ? '' : 'hidden' ?>">
                            <label class="text-xs font-medium text-gray-600 block mb-1">Opções (uma por linha ou separadas por vírgula)</label>
                            <textarea name="field_options[]" rows="3" class="w-full px-2 py-2 border border-gray-300 rounded-md" placeholder="POS&#10;LINK&#10;POS + LINK"><?= htmlspecialchars($field['options_text']) ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex justify-end gap-3 mb-8">
            <a href="<?= url('produtos-dinamicos') ?>" class="px-5 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Salvar Produto Dinâmico
            </button>
        </div>
    </form>
</div>

<template id="field-row-template">
    <div class="field-row border border-gray-200 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-600">Chave *</label>
                <input type="text" name="field_key[]" value="" class="w-full px-2 py-2 border border-gray-300 rounded-md" placeholder="novo_campo">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Rótulo *</label>
                <input type="text" name="field_label[]" value="" class="w-full px-2 py-2 border border-gray-300 rounded-md" placeholder="Novo Campo">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Tipo *</label>
                <select name="field_type[]" class="w-full px-2 py-2 border border-gray-300 rounded-md field-type-select">
                    <option value="text">Texto</option>
                    <option value="number">Número</option>
                    <option value="email">E-mail</option>
                    <option value="date">Data</option>
                    <option value="datetime-local">Data e Hora</option>
                    <option value="textarea">Texto Longo</option>
                    <option value="select">Seleção</option>
                    <option value="currency">Moeda</option>
                    <option value="phone">Telefone</option>
                    <option value="cpf">CPF</option>
                    <option value="cnpj">CNPJ</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Texto de apoio</label>
                <input type="text" name="field_placeholder[]" value="" class="w-full px-2 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Texto de ajuda</label>
                <input type="text" name="field_help_text[]" value="" class="w-full px-2 py-2 border border-gray-300 rounded-md">
            </div>
            <div class="flex items-end gap-2">
                <label class="inline-flex items-center mb-2">
                    <input type="hidden" name="field_required[]" value="0">
                    <input type="checkbox" class="mr-2 required-toggle">
                    <span class="text-sm text-gray-700">Obrigatório</span>
                </label>
                <button type="button" class="remove-field-row text-red-600 hover:text-red-800 px-2 py-2" title="Remover campo">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="mt-3 select-options-block hidden">
            <label class="text-xs font-medium text-gray-600 block mb-1">Opções (uma por linha ou separadas por vírgula)</label>
            <textarea name="field_options[]" rows="3" class="w-full px-2 py-2 border border-gray-300 rounded-md" placeholder="Opcao 1&#10;Opcao 2"></textarea>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('fields-container');
    const addButton = document.getElementById('add-field-row');
    const template = document.getElementById('field-row-template');

    function bindRowEvents(row) {
        const removeBtn = row.querySelector('.remove-field-row');
        const typeSelect = row.querySelector('.field-type-select');
        const optionsBlock = row.querySelector('.select-options-block');
        const requiredToggle = row.querySelector('.required-toggle');
        const requiredHidden = row.querySelector('input[type="hidden"][name="field_required[]"]');

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                const rows = container.querySelectorAll('.field-row');
                if (rows.length > 1) {
                    row.remove();
                }
            });
        }

        if (typeSelect && optionsBlock) {
            typeSelect.addEventListener('change', function () {
                if (typeSelect.value === 'select') {
                    optionsBlock.classList.remove('hidden');
                } else {
                    optionsBlock.classList.add('hidden');
                    const optionsInput = optionsBlock.querySelector('textarea[name="field_options[]"]');
                    if (optionsInput) optionsInput.value = '';
                }
            });
        }

        if (requiredToggle && requiredHidden) {
            requiredToggle.addEventListener('change', function () {
                requiredHidden.value = requiredToggle.checked ? '1' : '0';
            });
        }
    }

    container.querySelectorAll('.field-row').forEach(bindRowEvents);

    addButton.addEventListener('click', function () {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.field-row');
        container.appendChild(clone);
        bindRowEvents(container.lastElementChild);
    });
});
</script>

<?php
unset($_SESSION['old_input']);
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
