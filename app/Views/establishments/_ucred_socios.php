<?php
$savedUcredSocios = (isset($ucred_socios) && is_array($ucred_socios)) ? array_values($ucred_socios) : [];
$oldFormInput = (isset($_SESSION['old_input']) && is_array($_SESSION['old_input'])) ? $_SESSION['old_input'] : [];
if ($oldFormInput === [] && isset($oldInput) && is_array($oldInput)) {
    $oldFormInput = $oldInput;
}
$postedUcredSocios = (isset($oldFormInput['socios']) && is_array($oldFormInput['socios'])) ? $oldFormInput['socios'] : [];
$ucredSocioRows = $postedUcredSocios !== [] ? $postedUcredSocios : $savedUcredSocios;

$ucredCustomValues = (isset($customFieldValues) && is_array($customFieldValues)) ? $customFieldValues : [];
if (isset($oldCustomFieldValues) && is_array($oldCustomFieldValues)) {
    foreach ($oldCustomFieldValues as $customKey => $customValue) {
        if (trim((string) $customValue) !== '') {
            $ucredCustomValues[$customKey] = $customValue;
        }
    }
}

$ucredSocioOpen = false;
$titularCpfOpen = false;
$ucredCatalog = $dynamicProductsCatalog ?? ($dynamic_products_catalog ?? []);
$ucredSelectedIds = [];
if (isset($oldFormInput['dynamic_products']) && is_array($oldFormInput['dynamic_products'])) {
    $ucredSelectedIds = array_map('strval', $oldFormInput['dynamic_products']);
} elseif (!empty($dynamicProductData) && is_array($dynamicProductData)) {
    $ucredSelectedIds = array_map('strval', array_keys($dynamicProductData));
}
foreach ($ucredCatalog as $ucredCatalogProduct) {
    $ucredCatalogLabel = strtoupper(trim((string) ($ucredCatalogProduct['name'] ?? '') . ' ' . (string) ($ucredCatalogProduct['slug'] ?? '')));
    $ucredCatalogId = (string) ($ucredCatalogProduct['id'] ?? '');
    if ($ucredCatalogId === '' || !in_array($ucredCatalogId, $ucredSelectedIds, true)) {
        continue;
    }
    $ucredCatalogNormalized = trim((string) preg_replace('/[^A-Z0-9]+/', ' ', $ucredCatalogLabel));
    if (str_contains($ucredCatalogLabel, 'UCRED')) {
        $ucredSocioOpen = true;
        $titularCpfOpen = true;
    }
    if (str_contains($ucredCatalogLabel, 'PARCELEX') || preg_match('/(^| )EVO( |$)/', $ucredCatalogNormalized)) {
        $titularCpfOpen = true;
    }
}

$ucredFirstSocio = [];
if (isset($ucredSocioRows[0]) && is_array($ucredSocioRows[0])) {
    $ucredFirstSocio = $ucredSocioRows[0];
}
$ucredFormatCpf = static function ($value): string {
    $digits = preg_replace('/\D/', '', (string) $value);
    if (strlen($digits) !== 11) {
        return trim((string) $value);
    }
    return substr($digits, 0, 3) . '.' . substr($digits, 3, 3) . '.' . substr($digits, 6, 3) . '-' . substr($digits, 9, 2);
};
$ucredFormatDate = static function ($value): string {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
        $date = \DateTime::createFromFormat('Y-m-d', substr($value, 0, 10));
        return $date ? $date->format('d/m/Y') : $value;
    }
    return $value;
};

$ucredFirstCpf = trim((string) ($ucredFirstSocio['cpf'] ?? ''));
$ucredFirstBirth = trim((string) ($ucredFirstSocio['data_nascimento'] ?? ''));
$ucredFirstRg = trim((string) ($ucredFirstSocio['rg'] ?? ''));
if ($postedUcredSocios === []) {
    if ($ucredFirstCpf === '') {
        $ucredFirstCpf = trim((string) ($ucredCustomValues['cpf'] ?? ''));
    }
    if ($ucredFirstBirth === '') {
        $ucredFirstBirth = trim((string) ($ucredCustomValues['data_de_nacimento'] ?? ''));
    }
    if ($ucredFirstRg === '') {
        $ucredFirstRg = trim((string) ($ucredCustomValues['rg'] ?? ''));
    }
}
$ucredFirstCpf = $ucredFormatCpf($ucredFirstCpf);
$ucredFirstBirth = $ucredFormatDate($ucredFirstBirth);

$ucredExtraSocios = [];
foreach ($ucredSocioRows as $ucredSocioIndex => $ucredSocioRow) {
    if ((int) $ucredSocioIndex === 0 || !is_array($ucredSocioRow)) {
        continue;
    }
    $ucredExtraSocios[] = $ucredSocioRow;
}
$ucredFieldClass = 'mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500';
$ucredHiddenClass = $ucredSocioOpen ? '' : 'hidden';
$titularCpfHiddenClass = $titularCpfOpen ? '' : 'hidden';
?>
<div id="socio-cpf-titular" class="<?= $titularCpfHiddenClass ?>">
    <label class="block text-sm font-medium text-gray-700 mb-1">CPF do Sócio *</label>
    <input type="text" name="socios[0][cpf]" value="<?= htmlspecialchars($ucredFirstCpf) ?>"
           maxlength="14" inputmode="numeric" class="mask-cpf <?= $ucredFieldClass ?>"
           placeholder="000.000.000-00" <?= $titularCpfOpen ? 'required' : '' ?>>
</div>
<div id="ucred-socio-fields" class="md:col-span-2 <?= $ucredHiddenClass ?>">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data de nascimento *</label>
            <input type="text" name="socios[0][data_nascimento]" value="<?= htmlspecialchars($ucredFirstBirth) ?>"
                   maxlength="10" inputmode="numeric" class="mask-date <?= $ucredFieldClass ?>"
                   placeholder="dd/mm/aaaa" <?= $ucredSocioOpen ? 'required' : '' ?>>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">RG *</label>
            <input type="text" name="socios[0][rg]" value="<?= htmlspecialchars($ucredFirstRg) ?>"
                   class="<?= $ucredFieldClass ?>"
                   placeholder="Digite o RG" <?= $ucredSocioOpen ? 'required' : '' ?>>
        </div>
    </div>
</div>
<div id="socios-extras" class="md:col-span-2 <?= $ucredHiddenClass ?>">
    <?php foreach ($ucredExtraSocios as $ucredExtraIndex => $ucredExtraSocio): ?>
        <?php $ucredExtraNumber = $ucredExtraIndex + 1; ?>
        <div class="socio-extra mt-4 p-4 border border-gray-300 rounded-md">
            <div class="flex items-center justify-between mb-3">
                <h5 class="text-sm font-semibold text-gray-800">Sócio adicional</h5>
                <button type="button" class="remove-socio text-sm text-red-600 hover:text-red-800">Remover</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo *</label>
                    <input type="text" name="socios[<?= $ucredExtraNumber ?>][nome]" value="<?= htmlspecialchars((string) ($ucredExtraSocio['nome'] ?? '')) ?>"
                           class="<?= $ucredFieldClass ?>" placeholder="Digite o nome completo" <?= $ucredSocioOpen ? 'required' : '' ?>>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF titular *</label>
                    <input type="text" name="socios[<?= $ucredExtraNumber ?>][cpf]" value="<?= htmlspecialchars($ucredFormatCpf($ucredExtraSocio['cpf'] ?? '')) ?>"
                           maxlength="14" inputmode="numeric" class="mask-cpf <?= $ucredFieldClass ?>"
                           placeholder="000.000.000-00" <?= $ucredSocioOpen ? 'required' : '' ?>>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de nascimento *</label>
                    <input type="text" name="socios[<?= $ucredExtraNumber ?>][data_nascimento]" value="<?= htmlspecialchars($ucredFormatDate($ucredExtraSocio['data_nascimento'] ?? '')) ?>"
                           maxlength="10" inputmode="numeric" class="mask-date <?= $ucredFieldClass ?>"
                           placeholder="dd/mm/aaaa" <?= $ucredSocioOpen ? 'required' : '' ?>>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RG *</label>
                    <input type="text" name="socios[<?= $ucredExtraNumber ?>][rg]" value="<?= htmlspecialchars((string) ($ucredExtraSocio['rg'] ?? '')) ?>"
                           class="<?= $ucredFieldClass ?>" placeholder="Digite o RG" <?= $ucredSocioOpen ? 'required' : '' ?>>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div id="add-socio-wrap" class="md:col-span-2 <?= $ucredHiddenClass ?>">
    <button type="button" id="add-socio" class="mt-4 inline-flex items-center px-3 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">
        <i class="fas fa-plus mr-1"></i> Adicionar sócio
    </button>
</div>
<template id="socio-extra-template">
    <div class="socio-extra mt-4 p-4 border border-gray-300 rounded-md">
        <div class="flex items-center justify-between mb-3">
            <h5 class="text-sm font-semibold text-gray-800">Sócio adicional</h5>
            <button type="button" class="remove-socio text-sm text-red-600 hover:text-red-800">Remover</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo *</label>
                <input type="text" data-socio-field="nome" class="<?= $ucredFieldClass ?>" placeholder="Digite o nome completo">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CPF titular *</label>
                <input type="text" data-socio-field="cpf" maxlength="14" inputmode="numeric" class="mask-cpf <?= $ucredFieldClass ?>" placeholder="000.000.000-00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data de nascimento *</label>
                <input type="text" data-socio-field="data_nascimento" maxlength="10" inputmode="numeric" class="mask-date <?= $ucredFieldClass ?>" placeholder="dd/mm/aaaa">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">RG *</label>
                <input type="text" data-socio-field="rg" class="<?= $ucredFieldClass ?>" placeholder="Digite o RG">
            </div>
        </div>
    </div>
</template>
<script>
document.addEventListener('input', function(event) {
    const field = event.target;
    if (!field || !field.classList) {
        return;
    }
    if (field.classList.contains('mask-cpf')) {
        let digits = String(field.value || '').replace(/\D/g, '').substring(0, 11);
        if (digits.length > 9) {
            digits = digits.substring(0, 3) + '.' + digits.substring(3, 6) + '.' + digits.substring(6, 9) + '-' + digits.substring(9);
        } else if (digits.length > 6) {
            digits = digits.substring(0, 3) + '.' + digits.substring(3, 6) + '.' + digits.substring(6);
        } else if (digits.length > 3) {
            digits = digits.substring(0, 3) + '.' + digits.substring(3);
        }
        field.value = digits;
    }
    if (field.classList.contains('mask-date')) {
        let digits = String(field.value || '').replace(/\D/g, '').substring(0, 8);
        if (digits.length >= 5) {
            digits = digits.substring(0, 2) + '/' + digits.substring(2, 4) + '/' + digits.substring(4);
        } else if (digits.length >= 3) {
            digits = digits.substring(0, 2) + '/' + digits.substring(2);
        }
        field.value = digits;
    }
});
</script>
