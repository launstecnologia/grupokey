<?php
$currentPage = 'banners';
ob_start();

$banner = $banner ?? null;
$old = $_SESSION['old_input'] ?? [];
$materialFiles = $materialFiles ?? [];
$isEdit = $banner !== null;

$value = function (string $key, $default = '') use ($old, $banner) {
    if (array_key_exists($key, $old)) {
        return $old[$key];
    }
    return $banner[$key] ?? $default;
};
?>

<div class="pt-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-images mr-2"></i><?= $isEdit ? 'Editar Banner' : 'Novo Banner' ?>
            </h1>
            <p class="text-gray-600 mt-1">Configure imagem, destino e tempo do slide.</p>
        </div>
        <a href="<?= url('banners') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">Voltar</a>
    </div>

    <?php if (isset($_SESSION['validation_errors']) && !empty($_SESSION['validation_errors'])): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <?php foreach ($_SESSION['validation_errors'] as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['validation_errors']); ?>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <form id="banner-form" method="POST" action="<?= $isEdit ? url('banners/' . (int) $banner['id']) : url('banners') ?>" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                <input type="text" name="title" value="<?= htmlspecialchars((string) $value('title')) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Origem da Imagem</label>
                <?php $source = (string) ($value('image_source_type', !empty($value('image_url')) ? 'url' : 'upload')); ?>
                <select name="image_source_type" id="image_source_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="upload" <?= $source === 'upload' ? 'selected' : '' ?>>Upload</option>
                    <option value="url" <?= $source === 'url' ? 'selected' : '' ?>>URL externa</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tempo do Slide (segundos)</label>
                <input type="number" min="1" max="60" name="slide_duration_seconds" value="<?= (int) $value('slide_duration_seconds', 5) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div id="image_upload_group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Imagem do Banner</label>
                <input type="file" id="banner-image-input" name="image" accept=".jpg,.jpeg,.png,.gif,.webp" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <div id="banner-image-preview-wrap" class="mt-3 hidden">
                    <p class="text-xs text-gray-500 mb-2">Preview da imagem</p>
                    <img id="banner-image-preview" src="" alt="Preview do banner" class="w-full max-h-64 object-contain bg-black rounded-md border border-gray-200">
                </div>
            </div>
            <div id="image_url_group">
                <label class="block text-sm font-medium text-gray-700 mb-1">URL da Imagem</label>
                <input type="url" id="banner-image-url-input" name="image_url" value="<?= htmlspecialchars((string) $value('image_url')) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="https://...">
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
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                <input type="number" name="sort_order" value="<?= (int) $value('sort_order', 0) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>

            <div id="external_link_group" class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">URL Externa</label>
                <input type="url" name="external_link" value="<?= htmlspecialchars((string) $value('external_link')) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="https://...">
            </div>

            <div id="internal_target_type_group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Destino Interno</label>
                <?php $internalType = (string) $value('internal_target_type'); ?>
                <select name="internal_target_type" id="internal_target_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Selecione</option>
                    <option value="dashboard" <?= $internalType === 'dashboard' ? 'selected' : '' ?>>Dashboard</option>
                    <option value="material" <?= $internalType === 'material' ? 'selected' : '' ?>>Material de Apoio</option>
                    <option value="material_file" <?= $internalType === 'material_file' ? 'selected' : '' ?>>Arquivo do Material</option>
                    <option value="chamados" <?= $internalType === 'chamados' ? 'selected' : '' ?>>Chamados</option>
                    <option value="estabelecimentos" <?= $internalType === 'estabelecimentos' ? 'selected' : '' ?>>Estabelecimentos</option>
                </select>
            </div>

            <div id="internal_target_id_group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Arquivo do Material</label>
                <?php $selectedFile = (string) $value('internal_target_id'); ?>
                <select name="internal_target_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Selecione o arquivo</option>
                    <?php foreach ($materialFiles as $file): ?>
                        <option value="<?= htmlspecialchars((string) $file['id']) ?>" <?= $selectedFile === (string) $file['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($file['title'] ?? $file['original_filename'] ?? 'Arquivo')) ?>
                        </option>
                    <?php endforeach; ?>
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

            <div class="md:col-span-2 flex justify-end gap-3">
                <a href="<?= url('banners') ?>" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
                <button type="submit" id="banner-submit-btn" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed">
                    <span id="banner-submit-text">Salvar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageSourceType = document.getElementById('image_source_type');
    const imageUploadGroup = document.getElementById('image_upload_group');
    const imageUrlGroup = document.getElementById('image_url_group');
    const linkType = document.getElementById('link_type');
    const externalLinkGroup = document.getElementById('external_link_group');
    const internalTypeGroup = document.getElementById('internal_target_type_group');
    const internalIdGroup = document.getElementById('internal_target_id_group');
    const internalType = document.getElementById('internal_target_type');
    const imageInput = document.getElementById('banner-image-input');
    const imageUrlInput = document.getElementById('banner-image-url-input');
    const previewWrap = document.getElementById('banner-image-preview-wrap');
    const previewImg = document.getElementById('banner-image-preview');

    function toggleImageSource() {
        const mode = imageSourceType.value;
        imageUploadGroup.classList.toggle('hidden', mode !== 'upload');
        imageUrlGroup.classList.toggle('hidden', mode !== 'url');
    }

    function toggleLinkType() {
        const mode = linkType.value;
        externalLinkGroup.classList.toggle('hidden', mode !== 'external');
        internalTypeGroup.classList.toggle('hidden', mode !== 'internal');
        internalIdGroup.classList.toggle('hidden', !(mode === 'internal' && internalType.value === 'material_file'));
    }

    imageSourceType.addEventListener('change', toggleImageSource);
    linkType.addEventListener('change', toggleLinkType);
    internalType.addEventListener('change', toggleLinkType);
    toggleImageSource();
    toggleLinkType();

    function showPreview(src) {
        if (!previewWrap || !previewImg || !src) return;
        previewImg.src = src;
        previewWrap.classList.remove('hidden');
    }

    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file) return;
            showPreview(URL.createObjectURL(file));
        });
    }

    if (imageUrlInput) {
        imageUrlInput.addEventListener('input', function() {
            const val = String(this.value || '').trim();
            if (val !== '') {
                showPreview(val);
            }
        });
    }

    <?php if ($isEdit): ?>
    <?php $existingImageSrc = !empty($banner['image_path']) ? url('banners/' . (int) $banner['id'] . '/image') : (!empty($banner['image_url']) ? (string) $banner['image_url'] : ''); ?>
    <?php if ($existingImageSrc !== ''): ?>
    showPreview('<?= htmlspecialchars($existingImageSrc, ENT_QUOTES) ?>');
    <?php endif; ?>
    <?php endif; ?>

    const bannerForm = document.getElementById('banner-form');
    const submitBtn = document.getElementById('banner-submit-btn');
    const submitText = document.getElementById('banner-submit-text');
    if (bannerForm && submitBtn && submitText) {
        bannerForm.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
        });
    }
});
</script>

<?php
unset($_SESSION['old_input']);
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
