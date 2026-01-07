<?php
use App\Core\Auth;
$currentPage = 'chamados';
ob_start();
?>

<div class="container-fluid">
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit me-2 text-primary"></i>
            Editar Chamado #<?= $chamado['id'] ?>
        </h1>
        <p class="text-muted mb-0">Atualize as informações do chamado</p>
    </div>
    <div>
        <a href="<?= url('chamados/' . $chamado['id']) ?>" class="btn btn-outline-secondary shadow-sm">
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
                <h5 class="mb-0 text-gray-800">
                    <i class="fas fa-headset me-2 text-primary"></i>
                    Dados do Chamado
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['validation_errors'])): ?>
                    <div class="alert alert-danger shadow-sm">
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['validation_errors'] as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['validation_errors']); ?>
                <?php endif; ?>

                <form method="POST" action="<?= url('chamados/' . $chamado['id']) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PUT">
                    
                    <!-- Informações Básicas -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Informações Básicas
                            </h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="titulo" class="form-label fw-semibold">Título do Chamado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="titulo" name="titulo" 
                                   value="<?= htmlspecialchars($_POST['titulo'] ?? $chamado['assunto']) ?>" 
                                   placeholder="Descreva brevemente o problema" required>
                            <div class="form-text">Seja claro e objetivo no título</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="produto" class="form-label fw-semibold">Produto <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm" id="produto" name="produto" required>
                                <option value="">Selecione o produto</option>
                                <option value="CDC" <?= ($_POST['produto'] ?? $chamado['produto']) === 'CDC' ? 'selected' : '' ?>>CDC</option>
                                <option value="CDX_EVO" <?= ($_POST['produto'] ?? $chamado['produto']) === 'CDX_EVO' ? 'selected' : '' ?>>CDX/EVO</option>
                                <option value="GOOGLE" <?= ($_POST['produto'] ?? $chamado['produto']) === 'GOOGLE' ? 'selected' : '' ?>>Google</option>
                                <option value="MEMBRO_KEY" <?= ($_POST['produto'] ?? $chamado['produto']) === 'MEMBRO_KEY' ? 'selected' : '' ?>>Membro Key</option>
                                <option value="OUTROS" <?= ($_POST['produto'] ?? $chamado['produto']) === 'OUTROS' ? 'selected' : '' ?>>Outros</option>
                                <option value="PAGBANK" <?= ($_POST['produto'] ?? $chamado['produto']) === 'PAGBANK' ? 'selected' : '' ?>>PagBank</option>
                            </select>
                        </div>
                        
                        <?php if (Auth::isAdmin()): ?>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select class="form-select shadow-sm" id="status" name="status">
                                <option value="ABERTO" <?= ($_POST['status'] ?? $chamado['status']) === 'ABERTO' ? 'selected' : '' ?>>Aberto</option>
                                <option value="EM_ANDAMENTO" <?= ($_POST['status'] ?? $chamado['status']) === 'EM_ANDAMENTO' ? 'selected' : '' ?>>Em Andamento</option>
                                <option value="FECHADO" <?= ($_POST['status'] ?? $chamado['status']) === 'FECHADO' ? 'selected' : '' ?>>Fechado</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Descrição -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-align-left me-2"></i>
                                Descrição Detalhada
                            </h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="descricao" class="form-label fw-semibold">Descrição <span class="text-danger">*</span></label>
                            <textarea class="form-control shadow-sm" id="descricao" name="descricao" rows="8" 
                                      placeholder="Descreva detalhadamente o problema, incluindo passos para reproduzir, se aplicável..." required><?= htmlspecialchars($_POST['descricao'] ?? $chamado['descricao']) ?></textarea>
                            <div class="form-text">Quanto mais detalhes, melhor será o atendimento</div>
                        </div>
                    </div>
                    
                    <!-- Botões -->
                    <div class="row">
                        <div class="col-12">
                            <hr>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= url('chamados/' . $chamado['id']) ?>" class="btn btn-secondary shadow-sm">
                                    <i class="fas fa-times me-2"></i>
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="fas fa-save me-2"></i>
                                    Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0 text-gray-800">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    Informações
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info shadow-sm">
                    <h6 class="alert-heading">
                        <i class="fas fa-lightbulb me-2"></i>
                        Dicas Importantes
                    </h6>
                    <ul class="mb-0">
                        <li>Seja específico no título</li>
                        <li>Descreva o problema detalhadamente</li>
                        <li>Inclua passos para reproduzir</li>
                        <li>Use a prioridade correta</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning shadow-sm">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Níveis de Prioridade
                    </h6>
                    <ul class="mb-0 small">
                        <li><strong>Baixa:</strong> Melhorias ou dúvidas</li>
                        <li><strong>Média:</strong> Problemas não críticos</li>
                        <li><strong>Alta:</strong> Problemas que afetam o trabalho</li>
                        <li><strong>Urgente:</strong> Sistema fora do ar</li>
                    </ul>
                </div>
                
                <div class="mt-3">
                    <strong class="text-gray-800">Criado em:</strong><br>
                    <span class="text-muted"><?= format_datetime($chamado['created_at']) ?></span>
                </div>
                
                <div class="mt-3">
                    <strong class="text-gray-800">Última atualização:</strong><br>
                    <span class="text-muted"><?= format_datetime($chamado['updated_at']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: 1px solid #e3e6f0;
    border-radius: 0.35rem;
}

.card-header.bg-light {
    background-color: #f8f9fc !important;
    border-bottom: 1px solid #e3e6f0;
}

.shadow-sm {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.fw-semibold {
    font-weight: 600 !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

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

.dark .card-header.bg-light h5,
.dark .card-header.bg-light .text-gray-800 {
    color: #f9fafb !important;
}

.dark .card-body {
    background-color: #1f2937 !important;
    color: #f9fafb !important;
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

.dark .alert-info {
    background-color: #1e3a5f !important;
    border-color: #2563eb !important;
    color: #dbeafe !important;
}

.dark .alert-warning {
    background-color: #78350f !important;
    border-color: #f59e0b !important;
    color: #fef3c7 !important;
}

/* Responsividade */
@media (max-width: 768px) {
    .d-flex.gap-2 {
        flex-direction: column;
    }
    
    .d-flex.gap-2 .btn {
        margin-bottom: 0.5rem;
    }
}
</style>

</div> <!-- Fechar container-fluid -->

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
