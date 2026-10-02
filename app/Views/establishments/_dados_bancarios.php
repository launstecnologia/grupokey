<?php
$sharedBankForm = $sharedBankForm ?? ['options' => [], 'values' => [], 'visible' => false];
$sharedBankValues = $sharedBankForm['values'] ?? [];
$sharedBankOptions = $sharedBankForm['options'] ?? [];
$sharedBankVisible = !empty($sharedBankForm['visible']);
$sharedBankValue = function (string $slot) use ($sharedBankValues): string {
    return (string) ($sharedBankValues[$slot] ?? '');
};
?>
<div id="dados-bancarios-section" class="<?= $sharedBankVisible ? '' : 'hidden' ?> mt-4 p-4 bg-gray-800 rounded-lg border border-gray-700">
    <h5 class="font-medium text-white mb-3">Dados Bancários</h5>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">BANCO *</label>
            <select name="shared_bank[banco]"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione</option>
                <?php foreach ($sharedBankOptions['banco'] ?? [] as $option): ?>
                    <option value="<?= htmlspecialchars($option['value']) ?>" <?= $sharedBankValue('banco') === (string) $option['value'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($option['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">AGÊNCIA *</label>
            <input type="text" name="shared_bank[agencia]"
                   value="<?= htmlspecialchars($sharedBankValue('agencia')) ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Digite a agência">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">CONTA - DÍGITO *</label>
            <input type="text" name="shared_bank[conta]"
                   value="<?= htmlspecialchars($sharedBankValue('conta')) ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Digite a conta">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">CHAVE PIX *</label>
            <input type="text" name="shared_bank[pix]"
                   value="<?= htmlspecialchars($sharedBankValue('pix')) ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Digite a chave PIX">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">TIPO DE CONTA *</label>
            <select name="shared_bank[tipo_conta]"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione</option>
                <?php foreach ($sharedBankOptions['tipo_conta'] ?? [] as $option): ?>
                    <option value="<?= htmlspecialchars($option['value']) ?>" <?= $sharedBankValue('tipo_conta') === (string) $option['value'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($option['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>
