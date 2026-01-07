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
            <i class="fas fa-plus me-2 text-primary"></i>
            Novo Arquivo
        </h1>
        <p class="text-muted mb-0">Envie um novo arquivo para o material de apoio</p>
    </div>
    <div>
        <a href="<?= url('material/files') ?>" class="btn btn-outline-secondary shadow-sm">
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
                    <i class="fas fa-upload me-2"></i>
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

                <form method="POST" action="<?= url('material/files') ?>" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="title" class="form-label fw-semibold">Título do Arquivo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="title" name="title" 
                                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" 
                                   placeholder="Ex: Manual do Usuário v2.0" required>
                            <div class="form-text">Seja claro e objetivo no título</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label fw-semibold">Categoria <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm" id="category_id" name="category_id" required>
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= ($_POST['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="subcategory_id" class="form-label fw-semibold">Subcategoria</label>
                            <select class="form-select shadow-sm" id="subcategory_id" name="subcategory_id">
                                <option value="">Selecione uma subcategoria (opcional)</option>
                                <?php foreach ($subcategories as $subcategory): ?>
                                <option value="<?= $subcategory['id'] ?>" 
                                        <?= ($_POST['subcategory_id'] ?? '') == $subcategory['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subcategory['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label fw-semibold">Descrição</label>
                            <textarea class="form-control shadow-sm" id="description" name="description" rows="3" 
                                      placeholder="Descreva o conteúdo do arquivo..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            <div class="form-text">Descrição opcional do arquivo</div>
                        </div>
                        
                        <div class="col-12 mb-4">
                            <label for="file" class="form-label fw-semibold">Arquivo <span class="text-danger">*</span></label>
                            <input type="file" class="form-control shadow-sm" id="file" name="file" required>
                            <div class="form-text">
                                <strong>Tipos permitidos:</strong> PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, PNG, GIF, MP4, AVI, ZIP, RAR<br>
                                <strong>Tamanho máximo:</strong> 50MB
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('material/files') ?>" class="btn btn-outline-secondary shadow-sm">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary shadow-sm">
                            <i class="fas fa-upload me-2"></i>
                            Enviar Arquivo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle me-2"></i>
                    Informações
                </h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary">Tipos de Arquivo Suportados:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-file-pdf text-danger me-2"></i> PDF</li>
                    <li><i class="fas fa-file-word text-primary me-2"></i> Word (DOC, DOCX)</li>
                    <li><i class="fas fa-file-excel text-success me-2"></i> Excel (XLS, XLSX)</li>
                    <li><i class="fas fa-file-powerpoint text-warning me-2"></i> PowerPoint (PPT, PPTX)</li>
                    <li><i class="fas fa-file-alt text-secondary me-2"></i> Texto (TXT)</li>
                    <li><i class="fas fa-file-image text-info me-2"></i> Imagens (JPG, PNG, GIF)</li>
                    <li><i class="fas fa-file-video text-danger me-2"></i> Vídeos (MP4, AVI)</li>
                    <li><i class="fas fa-file-archive text-warning me-2"></i> Compactados (ZIP, RAR)</li>
                </ul>
                
                <hr>
                
                <h6 class="text-primary">Dicas:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i> Use títulos descritivos</li>
                    <li><i class="fas fa-check text-success me-2"></i> Organize por categorias</li>
                    <li><i class="fas fa-check text-success me-2"></i> Adicione descrições úteis</li>
                    <li><i class="fas fa-check text-success me-2"></i> Verifique o tamanho do arquivo</li>
                </ul>
            </div>
        </div>
    </div>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Atualizar subcategorias quando categoria mudar
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    
    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        
        // Limpar subcategorias
        subcategorySelect.innerHTML = '<option value="">Selecione uma subcategoria (opcional)</option>';
        
        if (categoryId) {
            // Aqui você poderia fazer uma requisição AJAX para carregar subcategorias
            // Por enquanto, vamos manter todas as subcategorias visíveis
        }
    });
    
    // Validar arquivo antes do envio
    const fileInput = document.getElementById('file');
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        const file = fileInput.files[0];
        
        if (file) {
            // Validar tamanho (50MB)
            if (file.size > 50 * 1024 * 1024) {
                e.preventDefault();
                alert('Arquivo muito grande! Tamanho máximo: 50MB');
                return;
            }
            
            // Validar tipo
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'image/jpeg',
                'image/png',
                'image/gif',
                'video/mp4',
                'video/avi',
                'application/zip',
                'application/x-rar-compressed'
            ];
            
            if (!allowedTypes.includes(file.type)) {
                e.preventDefault();
                alert('Tipo de arquivo não permitido!');
                return;
            }
        }
    });
});
</script>

<style>
/* Dark mode styles */
.dark .container-fluid {
    background-color: transparent !important;
}

.dark .text-gray-800 {
    color: #f9fafb !important;
}

.dark .text-muted {
    color: #9ca3af !important;
}

.dark .card {
    background-color: #1f2937 !important;
    border-color: #374151 !important;
}

.dark .card-header.bg-light {
    background-color: #111827 !important;
    border-bottom-color: #374151 !important;
}

.dark .card-header.bg-light h6,
.dark .card-header.bg-light .text-primary,
.dark .card-header.bg-light .font-weight-bold {
    color: #f9fafb !important;
}

.dark .card-body {
    background-color: #1f2937 !important;
    color: #f9fafb !important;
}

.dark .card-body .text-gray-800,
.dark .card-body strong {
    color: #f9fafb !important;
}

.dark .card-body .text-muted {
    color: #9ca3af !important;
}

.dark .form-label {
    color: #f9fafb !important;
}

.dark .form-control,
.dark .form-select {
    background-color: #374151 !important;
    border-color: #4b5563 !important;
    color: #f9fafb !important;
}

.dark .form-control:focus,
.dark .form-select:focus {
    background-color: #374151 !important;
    border-color: #3b82f6 !important;
    color: #f9fafb !important;
}

.dark .form-control::placeholder,
.dark .form-select::placeholder {
    color: #9ca3af !important;
}

.dark .form-text {
    color: #9ca3af !important;
}

.dark .form-text strong {
    color: #f9fafb !important;
}

.dark .btn-outline-secondary {
    border-color: #4b5563 !important;
    color: #f9fafb !important;
}

.dark .btn-outline-secondary:hover {
    background-color: #374151 !important;
    border-color: #4b5563 !important;
    color: #f9fafb !important;
}

.dark hr {
    border-color: #374151 !important;
}

/* Textos da seção Informações em branco */
.dark .card-body h6.text-primary {
    color: #f9fafb !important;
}

.dark .card-body ul.list-unstyled li {
    color: #f9fafb !important;
}

.dark .card-body ul.list-unstyled {
    color: #f9fafb !important;
}

/* Ícones em branco no modo dark */
.dark .fas,
.dark i.fas {
    color: #f9fafb !important;
}

.dark .text-primary,
.dark i.text-primary {
    color: #60a5fa !important;
}

.dark [class*="fa-"] {
    color: #f9fafb !important;
}

.dark .text-primary [class*="fa-"],
.dark i.text-primary {
    color: #60a5fa !important;
}

.dark .text-muted [class*="fa-"],
.dark i.text-muted {
    color: #9ca3af !important;
}

/* Manter cores dos ícones de tipo de arquivo */
.dark .text-danger {
    color: #ef4444 !important;
}

.dark .text-success {
    color: #10b981 !important;
}

.dark .text-warning {
    color: #f59e0b !important;
}

.dark .text-info {
    color: #06b6d4 !important;
}

.dark .text-secondary {
    color: #6b7280 !important;
}

.dark h1,
.dark h2,
.dark h3,
.dark h4,
.dark h5,
.dark h6 {
    color: #f9fafb !important;
}

.dark .alert-danger {
    background-color: #7f1d1d !important;
    border-color: #dc2626 !important;
    color: #fecaca !important;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
