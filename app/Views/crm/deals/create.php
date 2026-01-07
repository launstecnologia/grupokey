<?php
$currentPage = 'crm';
ob_start();

$pipelines = $pipelines ?? [];
$currentPipelineId = $currentPipelineId ?? null;
$stages = $stages ?? [];
$establishments = $establishments ?? [];
$representatives = $representatives ?? [];
$users = $users ?? [];
?>

<div class="pt-6 px-4 max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="<?= url('crm?pipeline_id=' . $currentPipelineId) ?>" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <i class="fas fa-arrow-left mr-2"></i>Voltar
        </a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Novo Negócio</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= url('crm/deals') ?>" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <?= csrf_field() ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Título *</label>
                <input type="text" name="title" required 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pipeline *</label>
                <select name="pipeline_id" id="pipeline_id" required 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Selecione...</option>
                    <?php foreach ($pipelines as $pipeline): ?>
                        <option value="<?= $pipeline['id'] ?>" <?= $pipeline['id'] == $currentPipelineId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pipeline['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Stage *</label>
                <select name="stage_id" id="stage_id" required 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Selecione...</option>
                    <?php foreach ($stages as $stage): ?>
                        <option value="<?= $stage['id'] ?>"><?= htmlspecialchars($stage['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor</label>
                <input type="text" name="value" placeholder="0,00" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Probabilidade (%)</label>
                <input type="number" name="probability" min="0" max="100" value="0" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prioridade</label>
                <select name="priority" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="LOW">Baixa</option>
                    <option value="MEDIUM" selected>Média</option>
                    <option value="HIGH">Alta</option>
                    <option value="URGENT">Urgente</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Prevista de Fechamento</label>
                <input type="date" name="expected_close_date" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estabelecimento</label>
                <select name="establishment_id" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Nenhum</option>
                    <?php foreach ($establishments as $establishment): ?>
                        <option value="<?= $establishment['id'] ?>"><?= htmlspecialchars($establishment['nome_fantasia'] ?? $establishment['nome_completo'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Representante</label>
                <select name="representative_id" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Nenhum</option>
                    <?php foreach ($representatives as $rep): ?>
                        <option value="<?= $rep['id'] ?>"><?= htmlspecialchars($rep['nome_completo'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Atribuído a</label>
                <select name="assigned_to_user_id" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Nenhum</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descrição</label>
                <textarea name="description" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <a href="<?= url('crm?pipeline_id=' . $currentPipelineId) ?>" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Salvar
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('pipeline_id').addEventListener('change', function() {
    const pipelineId = this.value;
    if (pipelineId) {
        window.location.href = '<?= url('crm/deals/create') ?>?pipeline_id=' + pipelineId;
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
?>

