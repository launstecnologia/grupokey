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
            <i class="fas fa-folder me-2 text-primary"></i>
            Categorias - Material de Apoio
        </h1>
        <p class="text-muted mb-0">Gerencie as categorias do material de apoio</p>
    </div>
    <div>
        <a href="<?= url('material/categories/create') ?>" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-2"></i>
            Nova Categoria
        </a>
    </div>
</div>

<!-- Lista de Categorias -->
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>
            Categorias
            <span class="badge bg-primary ms-2"><?= count($categories) ?></span>
        </h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($categories)): ?>
        <div class="text-center py-5">
            <i class="fas fa-folder fa-3x text-gray-300 mb-3"></i>
            <h5 class="text-gray-600">Nenhuma categoria encontrada</h5>
            <p class="text-muted">
                <a href="<?= url('material/categories/create') ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Criar primeira categoria
                </a>
            </p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Ícone</th>
                        <th>Cor</th>
                        <th>Ordem</th>
                        <th width="150">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $index => $category): ?>
                    <tr>
                        <td>
                            <span class="badge bg-secondary"><?= $index + 1 ?></span>
                        </td>
                        <td>
                            <strong class="text-gray-800"><?= htmlspecialchars($category['name']) ?></strong>
                        </td>
                        <td>
                            <span class="text-muted"><?= htmlspecialchars($category['description'] ?: 'Sem descrição') ?></span>
                        </td>
                        <td>
                            <i class="<?= htmlspecialchars($category['icon']) ?>" style="color: <?= htmlspecialchars($category['color']) ?>"></i>
                        </td>
                        <td>
                            <span class="badge" style="background-color: <?= htmlspecialchars($category['color']) ?>">
                                <?= htmlspecialchars($category['color']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info"><?= $category['sort_order'] ?></span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="<?= url('material/categories/' . $category['id'] . '/edit') ?>" 
                                   class="btn btn-warning btn-sm shadow-sm" 
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm shadow-sm btn-delete" 
                                        data-id="<?= $category['id'] ?>" 
                                        data-name="<?= htmlspecialchars($category['name']) ?>"
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
                        <a href="<?= url('material/subcategories') ?>" class="btn btn-outline-info w-100 shadow-sm">
                            <i class="fas fa-tags me-2"></i>
                            Subcategorias
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= url('material/files') ?>" class="btn btn-outline-success w-100 shadow-sm">
                            <i class="fas fa-file-alt me-2"></i>
                            Arquivos
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= url('material/categories/create') ?>" class="btn btn-outline-warning w-100 shadow-sm">
                            <i class="fas fa-plus me-2"></i>
                            Nova Categoria
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
                <p>Tem certeza que deseja excluir a categoria <strong id="categoryName"></strong>?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita e excluirá também todas as subcategorias e arquivos desta categoria.</small></p>
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
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');
    const categoryName = document.getElementById('categoryName');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            
            categoryName.textContent = name;
            deleteForm.action = '<?= url('material/categories') ?>/' + id;
            
            deleteModal.show();
        });
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

.dark .card-body.p-0 {
    background-color: #1f2937 !important;
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
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
