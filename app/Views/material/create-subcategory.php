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
            Nova Subcategoria
        </h1>
        <p class="text-muted mb-0">Crie uma nova subcategoria para organizar melhor o material</p>
    </div>
    <div>
        <a href="<?= url('material/subcategories') ?>" class="btn btn-outline-secondary shadow-sm">
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
                    <i class="fas fa-tag-plus me-2"></i>
                    Informações da Subcategoria
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

                <form method="POST" action="<?= url('material/subcategories') ?>">
                    <div class="row">
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
                            <div class="form-text">Categoria pai desta subcategoria</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold">Nome da Subcategoria <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="name" name="name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                                   placeholder="Ex: Manual do Usuário, Formulários de Cadastro..." required>
                            <div class="form-text">Seja claro e objetivo no nome</div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label fw-semibold">Descrição</label>
                            <textarea class="form-control shadow-sm" id="description" name="description" rows="3" 
                                      placeholder="Descreva o propósito desta subcategoria..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            <div class="form-text">Descrição opcional da subcategoria</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label fw-semibold">Ordem de Exibição</label>
                            <input type="number" class="form-control shadow-sm" id="sort_order" name="sort_order" 
                                   value="<?= htmlspecialchars($_POST['sort_order'] ?? '0') ?>" 
                                   min="0" max="999">
                            <div class="form-text">Número para ordenar as subcategorias (menor = primeiro)</div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('material/subcategories') ?>" class="btn btn-outline-secondary shadow-sm">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary shadow-sm">
                            <i class="fas fa-save me-2"></i>
                            Criar Subcategoria
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
                <h6 class="text-primary">Dicas para Subcategorias:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i> Organize por tipo específico</li>
                    <li><i class="fas fa-check text-success me-2"></i> Use nomes descritivos</li>
                    <li><i class="fas fa-check text-success me-2"></i> Mantenha consistência</li>
                    <li><i class="fas fa-check text-success me-2"></i> Defina ordem lógica</li>
                </ul>
                
                <hr>
                
                <h6 class="text-primary">Exemplos por Categoria:</h6>
                <ul class="list-unstyled">
                    <li><strong>Manuais:</strong></li>
                    <li class="ms-3">• Manual do Usuário</li>
                    <li class="ms-3">• Manual Técnico</li>
                    <li class="ms-3">• FAQ</li>
                    <li><strong>Formulários:</strong></li>
                    <li class="ms-3">• Cadastros</li>
                    <li class="ms-3">• Relatórios</li>
                    <li class="ms-3">• Contratos</li>
                </ul>
            </div>
        </div>
    </div>
</div>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
