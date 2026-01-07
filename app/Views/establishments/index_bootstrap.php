<?php
$currentPage = 'estabelecimentos';
ob_start();

// Definir variáveis com valores padrão para evitar warnings
$stats = $stats ?? ['total' => 0, 'aprovados' => 0, 'pendentes' => 0, 'reprovados' => 0, 'desabilitados' => 0, 'cadastros_ultimo_mes' => 0];
$establishments = $establishments ?? [];
$representatives = $representatives ?? [];
?>

<!-- Header com ações -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-building me-2"></i>
            Estabelecimentos
        </h1>
        <p class="text-muted mb-0">Gerencie todos os estabelecimentos cadastrados</p>
    </div>
    <div>
        <?php if (App\Core\Auth::isAdmin() || App\Core\Auth::isRepresentative()): ?>
        <a href="<?= url('estabelecimentos/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Novo Estabelecimento
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Estatísticas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['total'] ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Aprovados
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['aprovados'] ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pendentes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['pendentes'] ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Último Mês
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['cadastros_ultimo_mes'] ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtros
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= url('estabelecimentos') ?>" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos os status</option>
                    <option value="PENDING" <?= ($filters['status'] ?? '') === 'PENDING' ? 'selected' : '' ?>>Pendente</option>
                    <option value="APPROVED" <?= ($filters['status'] ?? '') === 'APPROVED' ? 'selected' : '' ?>>Aprovado</option>
                    <option value="REPROVED" <?= ($filters['status'] ?? '') === 'REPROVED' ? 'selected' : '' ?>>Reprovado</option>
                    <option value="DISABLED" <?= ($filters['status'] ?? '') === 'DISABLED' ? 'selected' : '' ?>>Desabilitado</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="produto" class="form-label">Produto</label>
                <select class="form-select" id="produto" name="produto">
                    <option value="">Todos os produtos</option>
                    <option value="CDX_EVO" <?= ($filters['produto'] ?? '') === 'CDX_EVO' || ($filters['produto'] ?? '') === 'PAGSEGURO_MP' ? 'selected' : '' ?>>CDX /EVO</option>
                    <option value="FGTS" <?= ($filters['produto'] ?? '') === 'FGTS' ? 'selected' : '' ?>>FGTS</option>
                    <option value="MEMBRO_KEY" <?= ($filters['produto'] ?? '') === 'MEMBRO_KEY' ? 'selected' : '' ?>>Membro KEY</option>
                    <option value="DIVERSOS" <?= ($filters['produto'] ?? '') === 'DIVERSOS' ? 'selected' : '' ?>>Diversos</option>
                    <option value="UCREDIT" <?= ($filters['produto'] ?? '') === 'UCREDIT' ? 'selected' : '' ?>>UCredit</option>
                    <option value="CDC" <?= ($filters['produto'] ?? '') === 'CDC' ? 'selected' : '' ?>>CDC</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="cidade" class="form-label">Cidade</label>
                <input type="text" class="form-control" id="cidade" name="cidade" 
                       value="<?= htmlspecialchars($filters['cidade'] ?? '') ?>" 
                       placeholder="Digite a cidade">
            </div>
            
            <?php if (App\Core\Auth::isAdmin()): ?>
            <div class="col-md-3">
                <label for="representative_id" class="form-label">Representante</label>
                <select class="form-select" id="representative_id" name="representative_id">
                    <option value="">Todos os representantes</option>
                    <?php foreach ($representatives as $rep): ?>
                    <option value="<?= $rep['id'] ?>" <?= ($filters['representative_id'] ?? '') == $rep['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($rep['nome_completo']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="col-md-6">
                <label for="date_from" class="form-label">Data Inicial</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?= $filters['date_from'] ?? '' ?>">
            </div>
            
            <div class="col-md-6">
                <label for="date_to" class="form-label">Data Final</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="<?= $filters['date_to'] ?? '' ?>">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>
                    Aplicar Filtros
                </button>
                <a href="<?= url('estabelecimentos') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Estabelecimentos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Lista de Estabelecimentos
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($establishments)): ?>
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhum estabelecimento encontrado</h5>
                <p class="text-muted">Tente ajustar os filtros ou cadastre um novo estabelecimento.</p>
                <a href="<?= url('estabelecimentos/create') ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Cadastrar Estabelecimento
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Estabelecimento</th>
                            <th>Produto</th>
                            <th>Status</th>
                            <th>Criado por</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($establishments as $establishment): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($establishment['nome_fantasia']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($establishment['nome_completo']) ?></small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= htmlspecialchars($establishment['cidade']) ?>/<?= htmlspecialchars($establishment['uf']) ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?= get_product_name($establishment['produto']) ?>
                                </span>
                            </td>
                            <td>
                                <?= get_status_badge($establishment['status']) ?>
                            </td>
                            <td>
                                <small>
                                    <?php if ($establishment['created_by_user_name']): ?>
                                        <i class="fas fa-user-shield me-1"></i>
                                        <?= htmlspecialchars($establishment['created_by_user_name']) ?>
                                    <?php elseif ($establishment['created_by_representative_name']): ?>
                                        <i class="fas fa-user-tie me-1"></i>
                                        <?= htmlspecialchars($establishment['created_by_representative_name']) ?>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <small><?= format_date($establishment['created_at']) ?></small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?= url('estabelecimentos/' . $establishment['id']) ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if (App\Core\Auth::isAdmin() || 
                                             (App\Core\Auth::isRepresentative() && 
                                              $establishment['created_by_representative_id'] == App\Core\Auth::representative()['id'] && 
                                              $establishment['status'] !== 'APPROVED')): ?>
                                    <a href="<?= url('estabelecimentos/' . $establishment['id'] . '/edit') ?>" 
                                       class="btn btn-sm btn-outline-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (App\Core\Auth::isAdmin()): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" 
                                            data-id="<?= $establishment['id'] ?>" 
                                            data-name="<?= htmlspecialchars($establishment['nome_fantasia']) ?>"
                                            title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
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

<style>
.border-left-primary {
    border-left: 0.25rem solid #3498db !important;
}
.border-left-success {
    border-left: 0.25rem solid #27ae60 !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f39c12 !important;
}
.border-left-info {
    border-left: 0.25rem solid #17a2b8 !important;
}
.text-xs {
    font-size: 0.7rem;
}
.font-weight-bold {
    font-weight: 700 !important;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
.text-gray-300 {
    color: #dddfeb !important;
}
</style>

<script>
// Confirmar exclusão
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-delete')) {
        const button = e.target.closest('.btn-delete');
        const id = button.dataset.id;
        const name = button.dataset.name;
        
        if (confirm(`Tem certeza que deseja excluir o estabelecimento "${name}"?`)) {
            // Criar formulário para exclusão
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('estabelecimentos') ?>/' + id;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
