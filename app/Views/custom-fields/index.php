<?php
$currentPage = 'campos-dinamicos';
ob_start();

$fieldsByEntity = $fieldsByEntity ?? ['establishment' => [], 'representative' => []];

$entityLabels = [
    'establishment' => 'Estabelecimentos',
    'representative' => 'Representantes'
];

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
?>

<div class="pt-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-list-alt mr-2"></i>
                Campos Dinâmicos
            </h1>
            <p class="text-gray-600 mt-1">Crie campos extras para estabelecimento e representante sem alterar código.</p>
        </div>
        <a href="<?= url('campos-dinamicos/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Novo Campo
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

    <?php foreach ($entityLabels as $entityKey => $entityLabel): ?>
        <?php $rows = $fieldsByEntity[$entityKey] ?? []; ?>
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($entityLabel) ?></h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rótulo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chave</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Obrigatório</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-center text-gray-500">Nenhum campo dinâmico cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $field): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($field['label']) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($field['help_text'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($field['field_key']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($typeLabels[$field['field_type']] ?? $field['field_type']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= (int) ($field['is_required'] ?? 0) === 1 ? 'Sim' : 'Não' ?></td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= url('campos-dinamicos/' . $field['id'] . '/edit') ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="<?= url('campos-dinamicos/' . $field['id'] . '/delete') ?>" class="inline" onsubmit="return confirm('Deseja remover este campo dinâmico?');">
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
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
