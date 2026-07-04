<?php
use App\Core\Auth;
$currentPage = 'material';
ob_start();
?>

<div class="container-fluid">
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit me-2 text-primary"></i>
            Editar Arquivo
        </h1>
        <p class="text-muted mb-0">Atualize as informações do arquivo</p>
    </div>
    <div>
        <a href="<?= url('material') ?>" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar
        </a>
    </div>
</div>

<!-- Formulário -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-edit me-2"></i>
                    Informações do Arquivo
                </h6>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['validation_errors'])): ?>
                <div class="alert alert-danger shadow-sm">
                    <h6 class="alert-heading">Erros de Validação:</h6>
                    <ul class="mb-0">
                        <?php foreach ($_SESSION['validation_errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['validation_errors']); ?>
                <?php endif; ?>

                <form method="POST" action="<?= url('material/files/' . $file['id']) ?>" enctype="multipart/form-data" id="material-edit-file-form">
                    <input type="hidden" name="_method" value="PUT">
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="title" class="form-label fw-semibold">Título do Arquivo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="title" name="title" 
                                   value="<?= htmlspecialchars($_POST['title'] ?? $file['title']) ?>" 
                                   placeholder="Ex: Manual do Usuário v2.0" required>
                            <div class="form-text">Seja claro e objetivo no título</div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="product" class="form-label fw-semibold">Produto <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm" id="product" name="product" required>
                                <option value="">Selecione um produto</option>
                                <?php foreach (($productOptions ?? []) as $productValue => $productLabel): ?>
                                <option value="<?= htmlspecialchars($productValue) ?>" 
                                        <?= ($_POST['product'] ?? ($selectedProduct ?? '')) === $productValue ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($productLabel) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label fw-semibold">Descrição</label>
                            <textarea class="form-control shadow-sm" id="description" name="description" rows="3" 
                                      placeholder="Descreva o conteúdo do arquivo..."><?= htmlspecialchars($_POST['description'] ?? $file['description']) ?></textarea>
                            <div class="form-text">Descrição opcional do arquivo</div>
                        </div>

                        <div class="col-12 mb-4">
                            <label class="form-label fw-semibold d-block">Exibir na listagem</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= (($_POST['is_active'] ?? (string) ($file['is_active'] ?? '1')) === '1') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Arquivo visível para os usuários no Material de Apoio</label>
                            </div>
                        </div>

                        <div class="col-12 mb-4">
                            <label for="file" class="form-label fw-semibold">Substituir arquivo de download</label>
                            <label for="file" class="material-dropzone" id="material-dropzone">
                                <span class="material-dropzone-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </span>
                                <span class="material-dropzone-title">Arraste e solte o novo arquivo aqui</span>
                                <span class="material-dropzone-subtitle">ou clique para escolher no computador</span>
                                <span class="material-dropzone-filename" id="material-dropzone-filename"></span>
                            </label>
                            <input type="file" class="visually-hidden" id="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.mp4,.m4v,.mov,.avi,.webm,.mkv,.zip,.rar">
                            <div class="form-text">
                                Deixe em branco para manter o arquivo atual. Tipos permitidos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, PNG, GIF, MP4, M4V, MOV, AVI, WEBM, MKV, ZIP, RAR. Tamanho máximo: 200MB.
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('material') ?>" class="btn btn-outline-secondary shadow-sm">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary shadow-sm" id="material-edit-file-submit-btn">
                            <i class="fas fa-save me-2" id="material-edit-file-submit-icon"></i>
                            <span id="material-edit-file-submit-text">Atualizar Arquivo</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow-sm material-file-info-card">
            <div class="card-header bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle me-2"></i>
                    Informações do Arquivo
                </h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary">Arquivo Atual:</h6>
                <ul class="list-unstyled">
                    <li><strong>Título:</strong> <?= htmlspecialchars($file['title']) ?></li>
                    <li><strong>Produto:</strong> <?= htmlspecialchars($file['category_name']) ?></li>
                    <li><strong>Arquivo:</strong> <?= htmlspecialchars($file['original_filename']) ?></li>
                    <li><strong>Tipo:</strong> <?= strtoupper($file['file_type']) ?></li>
                    <li><strong>Tamanho:</strong> <?= format_file_size($file['file_size']) ?></li>
                    <li><strong>Downloads:</strong> <?= number_format($file['download_count']) ?></li>
                    <li><strong>Enviado por:</strong> <?= htmlspecialchars($file['uploaded_by_name']) ?></li>
                    <li><strong>Criado em:</strong> <?= format_datetime($file['created_at']) ?></li>
                    <li><strong>Atualizado em:</strong> <?= format_datetime($file['updated_at']) ?></li>
                </ul>
                
                <hr>
                
                <div class="d-grid gap-2">
                    <a href="<?= url('material/download/' . $file['id']) ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-download me-2"></i>
                        Download
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('material-edit-file-form');
    const submitBtn = document.getElementById('material-edit-file-submit-btn');
    const submitIcon = document.getElementById('material-edit-file-submit-icon');
    const submitText = document.getElementById('material-edit-file-submit-text');
    const fileInput = document.getElementById('file');
    const dropzone = document.getElementById('material-dropzone');
    const dropzoneFilename = document.getElementById('material-dropzone-filename');

    if (!form) return;

    function updateDropzoneFilename() {
        const file = fileInput && fileInput.files ? fileInput.files[0] : null;
        if (dropzoneFilename) {
            dropzoneFilename.textContent = file ? file.name : '';
        }
    }

    if (dropzone && fileInput) {
        ['dragenter', 'dragover'].forEach(function(eventName) {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                dropzone.classList.add('is-dragging');
            });
        });

        ['dragleave', 'drop'].forEach(function(eventName) {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                dropzone.classList.remove('is-dragging');
            });
        });

        dropzone.addEventListener('drop', function(e) {
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                updateDropzoneFilename();
            }
        });

        fileInput.addEventListener('change', updateDropzoneFilename);
    }

    form.addEventListener('submit', function(e) {
        const file = fileInput && fileInput.files ? fileInput.files[0] : null;
        if (file) {
            if (file.size > 200 * 1024 * 1024) {
                e.preventDefault();
                alert('Arquivo muito grande! Tamanho máximo: 200MB');
                return;
            }

            const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'm4v', 'mov', 'avi', 'webm', 'mkv', 'zip', 'rar'];
            const fileExtension = (file.name.split('.').pop() || '').toLowerCase();
            if (!allowedExtensions.includes(fileExtension)) {
                e.preventDefault();
                alert('Tipo de arquivo não permitido!');
                return;
            }
        }

        if (submitBtn && submitIcon && submitText) {
            submitBtn.disabled = true;
            submitIcon.className = 'fas fa-spinner fa-spin me-2';
            submitText.textContent = 'Salvando...';
        }
    });
});
</script>

<style>
.material-dropzone {
    align-items: center;
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 0.75rem;
    color: #334155;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 150px;
    padding: 1.5rem;
    text-align: center;
    transition: border-color 0.2s ease, background-color 0.2s ease, color 0.2s ease;
}
.material-dropzone:hover,
.material-dropzone.is-dragging {
    background: #eff6ff;
    border-color: #2563eb;
    color: #1d4ed8;
}
.material-dropzone-icon {
    align-items: center;
    background: #dbeafe;
    border-radius: 999px;
    color: #2563eb;
    display: inline-flex;
    font-size: 1.5rem;
    height: 3rem;
    justify-content: center;
    margin-bottom: 0.75rem;
    width: 3rem;
}
.material-dropzone-title {
    font-size: 1rem;
    font-weight: 700;
}
.material-dropzone-subtitle,
.material-dropzone-filename {
    color: #64748b;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
.material-dropzone-filename {
    color: #2563eb;
    font-weight: 700;
    word-break: break-word;
}
.dark .material-dropzone {
    background: #111827 !important;
    border-color: #475569 !important;
    color: #e5e7eb !important;
}
.dark .material-dropzone:hover,
.dark .material-dropzone.is-dragging {
    background: #172554 !important;
    border-color: #60a5fa !important;
    color: #eff6ff !important;
}
.dark .material-dropzone-icon {
    background: #1e3a8a !important;
    color: #bfdbfe !important;
}
.dark .material-dropzone-subtitle {
    color: #cbd5e1 !important;
}
.dark .material-dropzone-filename {
    color: #93c5fd !important;
}
.material-file-info-card .card-body,
.material-file-info-card li,
.material-file-info-card strong {
    color: #374151;
}
.dark .material-file-info-card .card-body,
.dark .material-file-info-card li,
.dark .material-file-info-card strong {
    color: #e5e7eb !important;
}
.dark .material-file-info-card .card-header {
    background-color: #111827 !important;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
