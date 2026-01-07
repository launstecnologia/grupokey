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
            Nova Categoria
        </h1>
        <p class="text-muted mb-0">Crie uma nova categoria para organizar o material de apoio</p>
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
                    <i class="fas fa-folder-plus me-2"></i>
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

                <form method="POST" action="<?= url('material/categories') ?>">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="name" class="form-label fw-semibold">Nome da Categoria <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="name" name="name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                                   placeholder="Ex: Manuais, Formulários, Treinamentos..." required>
                            <div class="form-text">Seja claro e objetivo no nome</div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label fw-semibold">Descrição</label>
                            <textarea class="form-control shadow-sm" id="description" name="description" rows="3" 
                                      placeholder="Descreva o propósito desta categoria..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            <div class="form-text">Descrição opcional da categoria</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label fw-semibold">Ícone</label>
                            <select class="form-select shadow-sm" id="icon" name="icon">
                                <option value="fas fa-folder" <?= ($_POST['icon'] ?? 'fas fa-folder') == 'fas fa-folder' ? 'selected' : '' ?>>📁 Pasta</option>
                                <option value="fas fa-book" <?= ($_POST['icon'] ?? '') == 'fas fa-book' ? 'selected' : '' ?>>📚 Livro</option>
                                <option value="fas fa-file-alt" <?= ($_POST['icon'] ?? '') == 'fas fa-file-alt' ? 'selected' : '' ?>>📄 Documento</option>
                                <option value="fas fa-graduation-cap" <?= ($_POST['icon'] ?? '') == 'fas fa-graduation-cap' ? 'selected' : '' ?>>🎓 Graduação</option>
                                <option value="fas fa-clipboard-list" <?= ($_POST['icon'] ?? '') == 'fas fa-clipboard-list' ? 'selected' : '' ?>>📋 Lista</option>
                                <option value="fas fa-tools" <?= ($_POST['icon'] ?? '') == 'fas fa-tools' ? 'selected' : '' ?>>🔧 Ferramentas</option>
                                <option value="fas fa-chart-bar" <?= ($_POST['icon'] ?? '') == 'fas fa-chart-bar' ? 'selected' : '' ?>>📊 Gráfico</option>
                                <option value="fas fa-video" <?= ($_POST['icon'] ?? '') == 'fas fa-video' ? 'selected' : '' ?>>🎥 Vídeo</option>
                                <option value="fas fa-image" <?= ($_POST['icon'] ?? '') == 'fas fa-image' ? 'selected' : '' ?>>🖼️ Imagem</option>
                                <option value="fas fa-download" <?= ($_POST['icon'] ?? '') == 'fas fa-download' ? 'selected' : '' ?>>⬇️ Download</option>
                            </select>
                            <div class="form-text">Ícone que representará a categoria</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label fw-semibold">Cor</label>
                            <select class="form-select shadow-sm" id="color" name="color">
                                <option value="#007bff" <?= ($_POST['color'] ?? '#007bff') == '#007bff' ? 'selected' : '' ?>>🔵 Azul</option>
                                <option value="#28a745" <?= ($_POST['color'] ?? '') == '#28a745' ? 'selected' : '' ?>>🟢 Verde</option>
                                <option value="#dc3545" <?= ($_POST['color'] ?? '') == '#dc3545' ? 'selected' : '' ?>>🔴 Vermelho</option>
                                <option value="#ffc107" <?= ($_POST['color'] ?? '') == '#ffc107' ? 'selected' : '' ?>>🟡 Amarelo</option>
                                <option value="#17a2b8" <?= ($_POST['color'] ?? '') == '#17a2b8' ? 'selected' : '' ?>>🔵 Ciano</option>
                                <option value="#6f42c1" <?= ($_POST['color'] ?? '') == '#6f42c1' ? 'selected' : '' ?>>🟣 Roxo</option>
                                <option value="#fd7e14" <?= ($_POST['color'] ?? '') == '#fd7e14' ? 'selected' : '' ?>>🟠 Laranja</option>
                                <option value="#6c757d" <?= ($_POST['color'] ?? '') == '#6c757d' ? 'selected' : '' ?>>⚫ Cinza</option>
                            </select>
                            <div class="form-text">Cor do ícone da categoria</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label fw-semibold">Ordem de Exibição</label>
                            <input type="number" class="form-control shadow-sm" id="sort_order" name="sort_order" 
                                   value="<?= htmlspecialchars($_POST['sort_order'] ?? '0') ?>" 
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
                            Criar Categoria
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
                <h6 class="text-primary">Dicas para Categorias:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i> Use nomes claros e objetivos</li>
                    <li><i class="fas fa-check text-success me-2"></i> Organize por tipo de conteúdo</li>
                    <li><i class="fas fa-check text-success me-2"></i> Escolha ícones representativos</li>
                    <li><i class="fas fa-check text-success me-2"></i> Use cores para diferenciação</li>
                    <li><i class="fas fa-check text-success me-2"></i> Defina ordem lógica</li>
                </ul>
                
                <hr>
                
                <h6 class="text-primary">Exemplos de Categorias:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-book text-primary me-2"></i> Manuais</li>
                    <li><i class="fas fa-file-alt text-info me-2"></i> Formulários</li>
                    <li><i class="fas fa-graduation-cap text-warning me-2"></i> Treinamentos</li>
                    <li><i class="fas fa-clipboard-list text-success me-2"></i> Políticas</li>
                    <li><i class="fas fa-tools text-secondary me-2"></i> Ferramentas</li>
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
