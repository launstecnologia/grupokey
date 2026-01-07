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
            Editar Categoria
        </h1>
        <p class="text-muted mb-0">Atualize as informações da categoria</p>
    </div>
    <div>
        <a href="<?= url('material/categories') ?>" class="btn btn-outline-secondary shadow-sm">
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
                    <i class="fas fa-folder-edit me-2"></i>
                    Informações da Categoria
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

                <form method="POST" action="<?= url('material/categories/' . $category['id']) ?>">
                    <input type="hidden" name="_method" value="PUT">
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="name" class="form-label fw-semibold">Nome da Categoria <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="name" name="name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? $category['name']) ?>" 
                                   placeholder="Ex: Manuais, Formulários, Treinamentos..." required>
                            <div class="form-text">Seja claro e objetivo no nome</div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label fw-semibold">Descrição</label>
                            <textarea class="form-control shadow-sm" id="description" name="description" rows="3" 
                                      placeholder="Descreva o propósito desta categoria..."><?= htmlspecialchars($_POST['description'] ?? $category['description']) ?></textarea>
                            <div class="form-text">Descrição opcional da categoria</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label fw-semibold">Ícone</label>
                            <select class="form-select shadow-sm" id="icon" name="icon">
                                <option value="fas fa-folder" <?= ($_POST['icon'] ?? $category['icon']) == 'fas fa-folder' ? 'selected' : '' ?>>📁 Pasta</option>
                                <option value="fas fa-book" <?= ($_POST['icon'] ?? $category['icon']) == 'fas fa-book' ? 'selected' : '' ?>>📚 Livro</option>
                                <option value="fas fa-file-alt" <?= ($_POST['icon'] ?? $category['icon']) == 'fas fa-file-alt' ? 'selected' : '' ?>>📄 Documento</option>
                                <option value="fas fa-graduation-cap" <?= ($_POST['icon'] ?? $category['icon']) == 'fas fa-graduation-cap' ? 'selected' : '' ?>>🎓 Graduação</option>
                                <option value="fas fa-clipboard-list" <?= ($_POST['icon'] ?? $category['icon']) == 'fas fa-clipboard-list' ? 'selected' : '' ?>>📋 Lista</option>
                                <option value="fas fa-tools" <?= ($_POST['icon'] ?? $category['icon']) == 'fas fa-tools' ? 'selected' : '' ?>>🔧 Ferramentas</option>
                                <option value="fas fa-chart-bar" <?= ($_POST['icon'] ?? $category['icon']) == 'fas fa-chart-bar' ? 'selected' : '' ?>>📊 Gráfico</option>
                                <option value="fas fa-video" <?= ($_POST['icon'] ?? $category['icon']) == 'fas fa-video' ? 'selected' : '' ?>>🎥 Vídeo</option>
                                <option value="fas fa-image" <?= ($_POST['icon'] ?? $category['icon']) == 'fas fa-image' ? 'selected' : '' ?>>🖼️ Imagem</option>
                                <option value="fas fa-download" <?= ($_POST['icon'] ?? $category['icon']) == 'fas fa-download' ? 'selected' : '' ?>>⬇️ Download</option>
                            </select>
                            <div class="form-text">Ícone que representará a categoria</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label fw-semibold">Cor</label>
                            <select class="form-select shadow-sm" id="color" name="color">
                                <option value="#007bff" <?= ($_POST['color'] ?? $category['color']) == '#007bff' ? 'selected' : '' ?>>🔵 Azul</option>
                                <option value="#28a745" <?= ($_POST['color'] ?? $category['color']) == '#28a745' ? 'selected' : '' ?>>🟢 Verde</option>
                                <option value="#dc3545" <?= ($_POST['color'] ?? $category['color']) == '#dc3545' ? 'selected' : '' ?>>🔴 Vermelho</option>
                                <option value="#ffc107" <?= ($_POST['color'] ?? $category['color']) == '#ffc107' ? 'selected' : '' ?>>🟡 Amarelo</option>
                                <option value="#17a2b8" <?= ($_POST['color'] ?? $category['color']) == '#17a2b8' ? 'selected' : '' ?>>🔵 Ciano</option>
                                <option value="#6f42c1" <?= ($_POST['color'] ?? $category['color']) == '#6f42c1' ? 'selected' : '' ?>>🟣 Roxo</option>
                                <option value="#fd7e14" <?= ($_POST['color'] ?? $category['color']) == '#fd7e14' ? 'selected' : '' ?>>🟠 Laranja</option>
                                <option value="#6c757d" <?= ($_POST['color'] ?? $category['color']) == '#6c757d' ? 'selected' : '' ?>>⚫ Cinza</option>
                            </select>
                            <div class="form-text">Cor do ícone da categoria</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label fw-semibold">Ordem de Exibição</label>
                            <input type="number" class="form-control shadow-sm" id="sort_order" name="sort_order" 
                                   value="<?= htmlspecialchars($_POST['sort_order'] ?? $category['sort_order']) ?>" 
                                   min="0" max="999">
                            <div class="form-text">Número para ordenar as categorias (menor = primeiro)</div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('material/categories') ?>" class="btn btn-outline-secondary shadow-sm">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary shadow-sm">
                            <i class="fas fa-save me-2"></i>
                            Atualizar Categoria
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
                    Informações Atuais
                </h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary">Categoria Atual:</h6>
                <ul class="list-unstyled">
                    <li><strong>Nome:</strong> <?= htmlspecialchars($category['name']) ?></li>
                    <li><strong>Descrição:</strong> <?= htmlspecialchars($category['description'] ?: 'Sem descrição') ?></li>
                    <li><strong>Ícone:</strong> <i class="<?= htmlspecialchars($category['icon']) ?>" style="color: <?= htmlspecialchars($category['color']) ?>"></i></li>
                    <li><strong>Cor:</strong> <span class="badge" style="background-color: <?= htmlspecialchars($category['color']) ?>"><?= htmlspecialchars($category['color']) ?></span></li>
                    <li><strong>Ordem:</strong> <?= $category['sort_order'] ?></li>
                    <li><strong>Criada em:</strong> <?= format_datetime($category['created_at']) ?></li>
                    <li><strong>Atualizada em:</strong> <?= format_datetime($category['updated_at']) ?></li>
                </ul>
                
                <hr>
                
                <h6 class="text-primary">Dicas:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i> Mantenha nomes claros</li>
                    <li><i class="fas fa-check text-success me-2"></i> Use ícones representativos</li>
                    <li><i class="fas fa-check text-success me-2"></i> Organize por ordem lógica</li>
                </ul>
            </div>
        </div>
    </div>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview do ícone e cor
    const iconSelect = document.getElementById('icon');
    const colorSelect = document.getElementById('color');
    
    function updatePreview() {
        const icon = iconSelect.value;
        const color = colorSelect.value;
        
        // Aqui você poderia adicionar um preview visual se necessário
        console.log('Ícone:', icon, 'Cor:', color);
    }
    
    iconSelect.addEventListener('change', updatePreview);
    colorSelect.addEventListener('change', updatePreview);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
