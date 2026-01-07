<?php
use App\Core\Auth;
$currentPage = 'material';
ob_start();

// Definir variáveis com valores padrão para evitar warnings
$files = $files ?? [];
$categories = $categories ?? [];
$subcategories = $subcategories ?? [];
$filters = $filters ?? ['search' => '', 'category_id' => '', 'subcategory_id' => ''];
?>

<div class="container-fluid">
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-alt me-2 text-primary"></i>
            Arquivos - Material de Apoio
        </h1>
        <p class="text-muted mb-0">Gerencie os arquivos do material de apoio</p>
    </div>
    <div>
        <a href="<?= url('material/files/create') ?>" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-2"></i>
            Novo Arquivo
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-filter me-2"></i>
            Filtros
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= url('material/files') ?>" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label fw-semibold">Pesquisar</label>
                <input type="text" class="form-control shadow-sm" id="search" name="search" 
                       value="<?= htmlspecialchars($filters['search']) ?>" 
                       placeholder="Título ou descrição...">
            </div>
            
            <div class="col-md-3">
                <label for="category_id" class="form-label fw-semibold">Categoria</label>
                <select class="form-select shadow-sm" id="category_id" name="category_id">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" 
                            <?= $filters['category_id'] == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="subcategory_id" class="form-label fw-semibold">Subcategoria</label>
                <select class="form-select shadow-sm" id="subcategory_id" name="subcategory_id">
                    <option value="">Todas as subcategorias</option>
                    <?php foreach ($subcategories as $subcategory): ?>
                    <option value="<?= $subcategory['id'] ?>" 
                            <?= $filters['subcategory_id'] == $subcategory['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($subcategory['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <div class="d-flex align-items-end h-100">
                    <button type="submit" class="btn btn-primary shadow-sm me-2">
                        <i class="fas fa-search me-2"></i>
                        Filtrar
                    </button>
                    <a href="<?= url('material/files') ?>" class="btn btn-outline-secondary shadow-sm">
                        <i class="fas fa-times me-2"></i>
                        Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Arquivos -->
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>
            Arquivos
            <span class="badge bg-primary ms-2"><?= count($files) ?></span>
        </h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($files)): ?>
        <div class="text-center py-5">
            <i class="fas fa-file-alt fa-3x text-gray-300 mb-3"></i>
            <h5 class="text-gray-600">Nenhum arquivo encontrado</h5>
            <p class="text-muted">
                <?php if (!empty($filters['search']) || !empty($filters['category_id']) || !empty($filters['subcategory_id'])): ?>
                    Tente ajustar os filtros para encontrar o que procura.
                <?php else: ?>
                    <a href="<?= url('material/files/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Adicionar primeiro arquivo
                    </a>
                <?php endif; ?>
            </p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Título</th>
                        <th>Categoria</th>
                        <th>Arquivo</th>
                        <th>Tamanho</th>
                        <th>Downloads</th>
                        <th>Enviado por</th>
                        <th>Data</th>
                        <th width="150">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                    <tr>
                        <td>
                            <div>
                                <strong class="text-gray-800"><?= htmlspecialchars($file['title'] ?? 'Sem título') ?></strong>
                                <?php if (!empty($file['description'])): ?>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars(substr($file['description'], 0, 80)) ?><?= strlen($file['description']) > 80 ? '...' : '' ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info"><?= htmlspecialchars($file['category_name'] ?? 'Sem categoria') ?></span>
                            <?php if (!empty($file['subcategory_name'])): ?>
                            <br>
                            <small class="text-muted"><?= htmlspecialchars($file['subcategory_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="<?= get_file_icon($file['file_type'] ?? '') ?> me-2"></i>
                                <div>
                                    <small class="text-muted"><?= htmlspecialchars($file['original_filename'] ?? 'N/A') ?></small>
                                    <br>
                                    <span class="badge bg-secondary"><?= strtoupper($file['file_type'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?= format_file_size($file['file_size'] ?? 0) ?>
                        </td>
                        <td>
                            <span class="badge bg-success"><?= number_format($file['download_count'] ?? 0) ?></span>
                        </td>
                        <td>
                            <small class="text-muted"><?= htmlspecialchars($file['uploaded_by_name'] ?? 'N/A') ?></small>
                        </td>
                        <td>
                            <small class="text-muted"><?= format_datetime($file['created_at'] ?? date('Y-m-d H:i:s')) ?></small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="<?= url('material/download/' . $file['id']) ?>" 
                                   class="btn btn-primary btn-sm shadow-sm" 
                                   title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="<?= url('material/files/' . $file['id'] . '/edit') ?>" 
                                   class="btn btn-warning btn-sm shadow-sm" 
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm shadow-sm btn-delete" 
                                        data-id="<?= $file['id'] ?>" 
                                        data-title="<?= htmlspecialchars($file['title']) ?>"
                                        title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Links de Navegação -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-cog me-2"></i>
                    Administração
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="<?= url('material') ?>" class="btn btn-outline-primary w-100 shadow-sm">
                            <i class="fas fa-eye me-2"></i>
                            Visualizar Material
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= url('material/categories') ?>" class="btn btn-outline-info w-100 shadow-sm">
                            <i class="fas fa-folder me-2"></i>
                            Categorias
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= url('material/subcategories') ?>" class="btn btn-outline-success w-100 shadow-sm">
                            <i class="fas fa-tags me-2"></i>
                            Subcategorias
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= url('material/files/create') ?>" class="btn btn-outline-warning w-100 shadow-sm">
                            <i class="fas fa-plus me-2"></i>
                            Novo Arquivo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o arquivo <strong id="fileTitle"></strong>?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita e o arquivo será removido permanentemente.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar modal de exclusão
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const deleteModalElement = document.getElementById('deleteModal');
    const deleteForm = document.getElementById('deleteForm');
    const fileTitle = document.getElementById('fileTitle');
    
    if (deleteModalElement && deleteForm && fileTitle && typeof bootstrap !== 'undefined') {
        const deleteModal = new bootstrap.Modal(deleteModalElement);
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const title = this.dataset.title;
                
                if (fileTitle) {
                    fileTitle.textContent = title || 'este arquivo';
                }
                if (deleteForm) {
                    deleteForm.action = '<?= url('material/files') ?>/' + id;
                }
                
                if (deleteModal) {
                    deleteModal.show();
                }
            });
        });
    } else {
        // Fallback: usar confirm() se Bootstrap não estiver disponível
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                const title = this.dataset.title || 'este arquivo';
                
                if (confirm('Tem certeza que deseja excluir o arquivo "' + title + '"?\n\nEsta ação não pode ser desfeita.')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?= url('material/files') ?>/' + id;
                    
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    }
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

.dark .card-body.p-0 {
    background-color: #1f2937 !important;
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

.dark .table-responsive {
    background-color: #1f2937 !important;
}

.dark .table {
    background-color: #1f2937 !important;
    color: #f9fafb !important;
}

.dark .table thead.table-light {
    background-color: #111827 !important;
    color: #f9fafb !important;
}

.dark .table thead th {
    background-color: #111827 !important;
    color: #f9fafb !important;
    border-color: #374151 !important;
}

.dark .table tbody {
    background-color: #1f2937 !important;
}

.dark .table tbody tr {
    background-color: #1f2937 !important;
    border-color: #374151 !important;
}

.dark .table tbody tr:hover {
    background-color: #374151 !important;
}

.dark .table tbody td {
    background-color: transparent !important;
    color: #f9fafb !important;
    border-color: #374151 !important;
}

.dark .table tbody .text-gray-800,
.dark .table tbody strong {
    color: #f9fafb !important;
}

.dark .table tbody .text-muted {
    color: #9ca3af !important;
}

.dark .badge {
    color: #f9fafb !important;
}

.dark .badge.bg-info {
    background-color: #0ea5e9 !important;
    color: #ffffff !important;
}

.dark .badge.bg-secondary {
    background-color: #6b7280 !important;
    color: #ffffff !important;
}

.dark .badge.bg-success {
    background-color: #10b981 !important;
    color: #ffffff !important;
}

.dark .badge.bg-primary {
    background-color: #3b82f6 !important;
    color: #ffffff !important;
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

.dark h1,
.dark h2,
.dark h3,
.dark h4,
.dark h5,
.dark h6 {
    color: #f9fafb !important;
}

.dark .btn-outline-primary,
.dark .btn-outline-info,
.dark .btn-outline-success,
.dark .btn-outline-warning {
    border-color: #4b5563 !important;
    color: #f9fafb !important;
}

.dark .btn-outline-primary:hover,
.dark .btn-outline-info:hover,
.dark .btn-outline-success:hover,
.dark .btn-outline-warning:hover {
    background-color: #374151 !important;
    border-color: #4b5563 !important;
    color: #f9fafb !important;
}

.dark .modal-content {
    background-color: #1f2937 !important;
    color: #f9fafb !important;
}

.dark .modal-header {
    border-bottom-color: #374151 !important;
}

.dark .modal-title {
    color: #f9fafb !important;
}

.dark .modal-body {
    color: #f9fafb !important;
}

.dark .modal-footer {
    border-top-color: #374151 !important;
}

.dark .btn-close {
    filter: invert(1);
}

/* Garantir que todas as linhas da tabela tenham fundo escuro */
.dark .table tbody tr:nth-child(even) {
    background-color: #1f2937 !important;
}

.dark .table tbody tr:nth-child(odd) {
    background-color: #1f2937 !important;
}

/* Área vazia da tabela */
.dark .text-center.py-5 {
    background-color: #1f2937 !important;
    color: #f9fafb !important;
}

.dark .text-gray-600,
.dark .text-gray-300 {
    color: #9ca3af !important;
}

/* Forçar fundo escuro em todos os elementos da tabela */
.dark table,
.dark table * {
    background-color: #1f2937 !important;
}

.dark table thead,
.dark table thead * {
    background-color: #111827 !important;
}

.dark table tbody,
.dark table tbody * {
    background-color: #1f2937 !important;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
