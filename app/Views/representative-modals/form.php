<?php
$currentPage = 'modais-representante';
ob_start();
$modal = $modal ?? null;
$old = $_SESSION['old_input'] ?? [];
$representatives = $representatives ?? [];
$materialFiles = $materialFiles ?? [];
$isEdit = $modal !== null;

$value = function (string $key, $default = '') use ($old, $modal) {
    if (array_key_exists($key, $old)) {
        return $old[$key];
    }
    return $modal[$key] ?? $default;
};

$selectedReps = [];
if (isset($old['selected_representative_ids']) && is_array($old['selected_representative_ids'])) {
    $selectedReps = array_map('intval', $old['selected_representative_ids']);
} elseif ($modal && !empty($modal['selected_representative_ids_json'])) {
    $decoded = json_decode((string) $modal['selected_representative_ids_json'], true);
    if (is_array($decoded)) {
        $selectedReps = array_map('intval', $decoded);
    }
}
?>

<div class="pt-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-window-maximize mr-2"></i><?= $isEdit ? 'Editar Modal' : 'Novo Modal' ?></h1>
            <p class="text-gray-600 mt-1">Programe quando o representante verá esta mensagem.</p>
        </div>
        <a href="<?= url('modais-representante') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Voltar</a>
    </div>

    <?php if (isset($_SESSION['validation_errors']) && !empty($_SESSION['validation_errors'])): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <?php foreach ($_SESSION['validation_errors'] as $error): ?><div><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
        </div>
        <?php unset($_SESSION['validation_errors']); ?>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <form id="rep-modal-form" method="POST" action="<?= $isEdit ? url('modais-representante/' . (int) $modal['id']) : url('modais-representante') ?>" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                <input type="text" name="title" value="<?= htmlspecialchars((string) $value('title')) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Texto do Modal *</label>
                <textarea name="message" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars((string) $value('message')) ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Origem da Imagem</label>
                <?php $imageSourceType = (string) ($value('image_source_type', !empty($value('image_url')) ? 'url' : 'upload')); ?>
                <select id="image_source_type" name="image_source_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="upload" <?= $imageSourceType === 'upload' ? 'selected' : '' ?>>Upload</option>
                    <option value="url" <?= $imageSourceType === 'url' ? 'selected' : '' ?>>URL</option>
                </select>
            </div>
            <div id="image_upload_group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Imagem (upload)</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div id="image_url_group" class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">URL da Imagem</label>
                <input type="url" name="image_url" value="<?= htmlspecialchars((string) $value('image_url')) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Gatilho</label>
                <?php $triggerType = (string) $value('trigger_type', 'custom_date'); ?>
                <select id="trigger_type" name="trigger_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="custom_date" <?= $triggerType === 'custom_date' ? 'selected' : '' ?>>Data específica</option>
                    <option value="commemorative_date" <?= $triggerType === 'commemorative_date' ? 'selected' : '' ?>>Data comemorativa (anual)</option>
                    <option value="birthday" <?= $triggerType === 'birthday' ? 'selected' : '' ?>>Aniversário representante</option>
                    <option value="platform_anniversary" <?= $triggerType === 'platform_anniversary' ? 'selected' : '' ?>>Aniversário de plataforma</option>
                    <option value="establishment_milestone" <?= $triggerType === 'establishment_milestone' ? 'selected' : '' ?>>Meta de cadastros</option>
                </select>
            </div>
            <div id="trigger_date_group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Data específica</label>
                <input type="date" name="trigger_date" value="<?= htmlspecialchars((string) $value('trigger_date')) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div id="trigger_month_day_group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Data comemorativa (MM-DD)</label>
                <input type="text" name="trigger_month_day" value="<?= htmlspecialchars((string) $value('trigger_month_day')) ?>" placeholder="12-25" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div id="anniversary_years_group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Anos de plataforma</label>
                <input type="number" min="1" name="anniversary_years" value="<?= (int) $value('anniversary_years', 1) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div id="milestone_group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Meta de cadastros</label>
                <input type="number" min="1" name="milestone_establishments" value="<?= (int) $value('milestone_establishments', 1) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Destino do Clique</label>
                <?php $linkType = (string) $value('link_type', 'none'); ?>
                <select name="link_type" id="link_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="none" <?= $linkType === 'none' ? 'selected' : '' ?>>Sem link</option>
                    <option value="external" <?= $linkType === 'external' ? 'selected' : '' ?>>Link externo</option>
                    <option value="internal" <?= $linkType === 'internal' ? 'selected' : '' ?>>Link interno</option>
                </select>
            </div>
            <div id="external_link_group">
                <label class="block text-sm font-medium text-gray-700 mb-1">URL Externa</label>
                <input type="url" name="external_link" value="<?= htmlspecialchars((string) $value('external_link')) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div id="internal_target_type_group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Destino Interno</label>
                <?php $internalTargetType = (string) $value('internal_target_type'); ?>
                <select id="internal_target_type" name="internal_target_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Selecione</option>
                    <option value="dashboard" <?= $internalTargetType === 'dashboard' ? 'selected' : '' ?>>Dashboard</option>
                    <option value="material" <?= $internalTargetType === 'material' ? 'selected' : '' ?>>Material</option>
                    <option value="material_file" <?= $internalTargetType === 'material_file' ? 'selected' : '' ?>>Arquivo do Material</option>
                    <option value="chamados" <?= $internalTargetType === 'chamados' ? 'selected' : '' ?>>Chamados</option>
                    <option value="estabelecimentos" <?= $internalTargetType === 'estabelecimentos' ? 'selected' : '' ?>>Estabelecimentos</option>
                </select>
            </div>
            <div id="internal_target_id_group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Arquivo do Material</label>
                <?php $selectedFile = (string) $value('internal_target_id'); ?>
                <select name="internal_target_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Selecione</option>
                    <?php foreach ($materialFiles as $f): ?>
                        <option value="<?= htmlspecialchars((string) $f['id']) ?>" <?= $selectedFile === (string) $f['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($f['title'] ?? $f['original_filename'] ?? 'Arquivo')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Público</label>
                <?php $audienceType = (string) $value('audience_type', 'all'); ?>
                <select name="audience_type" id="audience_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="all" <?= $audienceType === 'all' ? 'selected' : '' ?>>Todos representantes</option>
                    <option value="selected" <?= $audienceType === 'selected' ? 'selected' : '' ?>>Representantes específicos</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <?php $isActive = (string) $value('is_active', 1); ?>
                <select name="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="1" <?= $isActive === '1' ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= $isActive === '0' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <div id="selected_representatives_group" class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Representantes</label>
                <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-md p-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                    <?php foreach ($representatives as $rep): ?>
                        <?php $repId = (int) ($rep['id'] ?? 0); ?>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                            <input type="checkbox" name="selected_representative_ids[]" value="<?= $repId ?>" class="h-4 w-4 text-blue-600 border-gray-300 rounded" <?= in_array($repId, $selectedReps, true) ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars((string) ($rep['nome_completo'] ?? '')) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="md:col-span-2 flex justify-end gap-3">
                <button type="button" id="rep-modal-preview-btn" class="px-6 py-2 border border-blue-300 text-blue-700 rounded-md hover:bg-blue-50">Preview</button>
                <a href="<?= url('modais-representante') ?>" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
                <button type="submit" id="rep-modal-submit-btn" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-60">
                    <span id="rep-modal-submit-text">Salvar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div id="rep-modal-preview-overlay" class="fixed inset-0 z-50 bg-black/60 items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden">
        <img id="rep-modal-preview-image" src="" alt="Preview" class="w-full h-56 object-cover hidden">
        <div class="p-6">
            <h2 id="rep-modal-preview-title" class="text-2xl font-bold text-gray-900 mb-2 hidden"></h2>
            <p id="rep-modal-preview-message" class="text-gray-700 whitespace-pre-line"></p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="rep-modal-preview-close" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">Fechar Preview</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageSourceType = document.getElementById('image_source_type');
    const imageUploadGroup = document.getElementById('image_upload_group');
    const imageUrlGroup = document.getElementById('image_url_group');
    const triggerType = document.getElementById('trigger_type');
    const triggerDateGroup = document.getElementById('trigger_date_group');
    const triggerMonthDayGroup = document.getElementById('trigger_month_day_group');
    const anniversaryYearsGroup = document.getElementById('anniversary_years_group');
    const milestoneGroup = document.getElementById('milestone_group');
    const linkType = document.getElementById('link_type');
    const externalLinkGroup = document.getElementById('external_link_group');
    const internalTypeGroup = document.getElementById('internal_target_type_group');
    const internalIdGroup = document.getElementById('internal_target_id_group');
    const internalTargetType = document.getElementById('internal_target_type');
    const audienceType = document.getElementById('audience_type');
    const selectedGroup = document.getElementById('selected_representatives_group');
    const previewBtn = document.getElementById('rep-modal-preview-btn');
    const previewOverlay = document.getElementById('rep-modal-preview-overlay');
    const previewClose = document.getElementById('rep-modal-preview-close');
    const previewImage = document.getElementById('rep-modal-preview-image');
    const previewTitle = document.getElementById('rep-modal-preview-title');
    const previewMessage = document.getElementById('rep-modal-preview-message');

    function toggleImage() {
        const mode = imageSourceType.value;
        imageUploadGroup.classList.toggle('hidden', mode !== 'upload');
        imageUrlGroup.classList.toggle('hidden', mode !== 'url');
    }

    function toggleTrigger() {
        const t = triggerType.value;
        triggerDateGroup.classList.toggle('hidden', t !== 'custom_date');
        triggerMonthDayGroup.classList.toggle('hidden', t !== 'commemorative_date');
        anniversaryYearsGroup.classList.toggle('hidden', t !== 'platform_anniversary');
        milestoneGroup.classList.toggle('hidden', t !== 'establishment_milestone');
    }

    function toggleLink() {
        const mode = linkType.value;
        externalLinkGroup.classList.toggle('hidden', mode !== 'external');
        internalTypeGroup.classList.toggle('hidden', mode !== 'internal');
        internalIdGroup.classList.toggle('hidden', !(mode === 'internal' && internalTargetType.value === 'material_file'));
    }

    function toggleAudience() {
        selectedGroup.classList.toggle('hidden', audienceType.value !== 'selected');
    }

    imageSourceType.addEventListener('change', toggleImage);
    triggerType.addEventListener('change', toggleTrigger);
    linkType.addEventListener('change', toggleLink);
    internalTargetType.addEventListener('change', toggleLink);
    audienceType.addEventListener('change', toggleAudience);
    toggleImage();
    toggleTrigger();
    toggleLink();
    toggleAudience();

    const form = document.getElementById('rep-modal-form');
    const btn = document.getElementById('rep-modal-submit-btn');
    const txt = document.getElementById('rep-modal-submit-text');
    if (form && btn && txt) {
        form.addEventListener('submit', function() {
            btn.disabled = true;
            txt.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
        });
    }

    function openPreview() {
        const title = document.querySelector('input[name="title"]').value.trim();
        const message = document.querySelector('textarea[name="message"]').value.trim();
        const imageMode = imageSourceType.value;
        const imageUrl = document.querySelector('input[name="image_url"]').value.trim();
        const fileInput = document.querySelector('input[name="image"]');

        previewTitle.textContent = title || 'Sem título';
        previewTitle.classList.toggle('hidden', !title);
        previewMessage.textContent = message || 'Sem texto informado.';

        let src = '';
        if (imageMode === 'url' && imageUrl) {
            src = imageUrl;
        } else if (fileInput && fileInput.files && fileInput.files[0]) {
            src = URL.createObjectURL(fileInput.files[0]);
        }

        if (src) {
            previewImage.src = src;
            previewImage.classList.remove('hidden');
        } else {
            previewImage.src = '';
            previewImage.classList.add('hidden');
        }

        previewOverlay.classList.remove('hidden');
        previewOverlay.classList.add('flex');
    }

    function closePreview() {
        previewOverlay.classList.add('hidden');
        previewOverlay.classList.remove('flex');
    }

    if (previewBtn) {
        previewBtn.addEventListener('click', openPreview);
    }
    if (previewClose) {
        previewClose.addEventListener('click', closePreview);
    }
    if (previewOverlay) {
        previewOverlay.addEventListener('click', function(e) {
            if (e.target === previewOverlay) closePreview();
        });
    }
});
</script>

<?php
unset($_SESSION['old_input']);
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
