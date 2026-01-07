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

                <form method="POST" action="<?= url('material/files/' . $file['id']) ?>">
                    <input type="hidden" name="_method" value="PUT">
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="title" class="form-label fw-semibold">Título do Arquivo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="title" name="title" 
                                   value="<?= htmlspecialchars($_POST['title'] ?? $file['title']) ?>" 
                                   placeholder="Ex: Manual do Usuário v2.0" required>
                            <div class="form-text">Seja claro e objetivo no título</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label fw-semibold">Categoria <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm" id="category_id" name="category_id" required>
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= ($_POST['category_id'] ?? $file['category_id']) == $category['id'] ? 'selected' : '' ?>>
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
                                        <?= ($_POST['subcategory_id'] ?? $file['subcategory_id']) == $subcategory['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subcategory['name']) ?>
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
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('material/files') ?>" class="btn btn-outline-secondary shadow-sm">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary shadow-sm">
                            <i class="fas fa-save me-2"></i>
                            Atualizar Arquivo
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
                    Informações do Arquivo
                </h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary">Arquivo Atual:</h6>
                <ul class="list-unstyled">
                    <li><strong>Título:</strong> <?= htmlspecialchars($file['title']) ?></li>
                    <li><strong>Categoria:</strong> <?= htmlspecialchars($file['category_name']) ?></li>
                    <?php if ($file['subcategory_name']): ?>
                    <li><strong>Subcategoria:</strong> <?= htmlspecialchars($file['subcategory_name']) ?></li>
                    <?php endif; ?>
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
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
