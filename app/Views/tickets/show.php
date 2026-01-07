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
            <i class="fas fa-headset me-2 text-primary"></i>
            Chamado #<?= $chamado['id'] ?>
        </h1>
        <p class="text-muted mb-0"><?= htmlspecialchars($chamado['assunto']) ?></p>
    </div>
    <div>
        <a href="<?= url('chamados') ?>" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar
        </a>
        <?php 
        $currentRepresentativeId = Auth::isRepresentative() ? (Auth::representative()['id'] ?? null) : null;
        if ($chamado['status'] === 'OPEN' && 
                 (Auth::isAdmin() || ($currentRepresentativeId && $chamado['created_by_representative_id'] == $currentRepresentativeId))): ?>
        <a href="<?= url('chamados/' . $chamado['id'] . '/edit') ?>" class="btn btn-warning shadow-sm">
            <i class="fas fa-edit me-2"></i>
            Editar
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Informações Principais -->
    <div class="col-lg-8">
        <!-- Dados do Chamado -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0 text-gray-800">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    Informações do Chamado
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong class="text-gray-800">Título:</strong><br>
                        <span class="text-muted"><?= htmlspecialchars($chamado['assunto']) ?></span>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <strong class="text-gray-800">Produto:</strong><br>
                        <?php
                        // Mapeamento de produtos para exibição amigável
                        $productMap = [
                            'CDC' => 'CDC',
                            'CDX_EVO' => 'CDX/EVO',
                            'GOOGLE' => 'Google',
                            'MEMBRO_KEY' => 'Membro Key',
                            'OUTROS' => 'Outros',
                            'PAGBANK' => 'PagBank',
                            // Valores antigos para compatibilidade
                            'PAGSEGURO' => 'CDX/EVO',
                            'PAGSEGURO_MP' => 'CDX/EVO',
                            'BRASILCARD' => 'CDC',
                            'DIVERSOS' => 'Outros'
                        ];
                        $produtoDisplay = $productMap[$chamado['produto']] ?? $chamado['produto'];
                        ?>
                        <span class="badge bg-info"><?= htmlspecialchars($produtoDisplay) ?></span>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <strong class="text-gray-800">Status:</strong><br>
                        <?= get_status_badge($chamado['status']) ?>
                    </div>
                    
                    <?php if (Auth::isAdmin()): ?>
                    <div class="col-md-6 mb-3">
                        <strong class="text-gray-800">Representante:</strong><br>
                        <span class="text-muted"><?= htmlspecialchars($chamado['representative_nome']) ?></span>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong class="text-gray-800">Email:</strong><br>
                        <span class="text-muted"><?= htmlspecialchars($chamado['representative_email']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-12 mb-3">
                        <strong class="text-gray-800">Descrição:</strong><br>
                        <div class="mt-2 p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($chamado['descricao'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Respostas -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0 text-gray-800">
                    <i class="fas fa-comments me-2 text-primary"></i>
                    Conversa (<?= count($respostas) ?> resposta<?= count($respostas) !== 1 ? 's' : '' ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($respostas)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Ainda não há respostas neste chamado.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($respostas as $index => $resposta): ?>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar <?= $resposta['user_type'] === 'admin' ? 'bg-primary' : 'bg-secondary' ?> text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-<?= $resposta['user_type'] === 'admin' ? 'user-shield' : 'user' ?>"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <?= htmlspecialchars($resposta['autor_nome']) ?>
                                                <?php if ($resposta['user_type'] === 'admin'): ?>
                                                    <span class="badge bg-primary ms-2">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary ms-2">Representante</span>
                                                <?php endif; ?>
                                            </h6>
                                            <small class="text-muted"><?= format_datetime($resposta['created_at']) ?></small>
                                        </div>
                                    </div>
                                    <div class="mt-2 p-3 bg-light rounded">
                                        <?= nl2br(htmlspecialchars($resposta['comment'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Formulário de Resposta -->
        <?php if ($chamado['status'] !== 'CLOSED'): ?>
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0 text-gray-800">
                    <i class="fas fa-reply me-2 text-primary"></i>
                    Adicionar Resposta
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('chamados/' . $chamado['id'] . '/responder') ?>">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label for="mensagem" class="form-label fw-semibold">Sua mensagem</label>
                        <textarea class="form-control shadow-sm" id="mensagem" name="mensagem" rows="4" 
                                  placeholder="Digite sua resposta aqui..." required></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i>
                            Enviar Resposta
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Status e Ações -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0 text-gray-800">
                    <i class="fas fa-cogs me-2 text-primary"></i>
                    Ações
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if (Auth::isAdmin() && $chamado['status'] !== 'CLOSED'): ?>
                    <button type="button" class="btn btn-success shadow-sm btn-fechar-chamado" 
                            data-id="<?= $chamado['id'] ?>" 
                            data-titulo="<?= htmlspecialchars($chamado['assunto']) ?>">
                        <i class="fas fa-check me-2"></i>
                        Fechar Chamado
                    </button>
                    <?php endif; ?>
                    
                    <?php 
                    $currentRepresentativeId = Auth::isRepresentative() ? (Auth::representative()['id'] ?? null) : null;
                    if ($chamado['status'] === 'OPEN' && 
                             (Auth::isAdmin() || ($currentRepresentativeId && $chamado['created_by_representative_id'] == $currentRepresentativeId))): ?>
                    <a href="<?= url('chamados/' . $chamado['id'] . '/edit') ?>" class="btn btn-warning shadow-sm">
                        <i class="fas fa-edit me-2"></i>
                        Editar Chamado
                    </a>
                    
                    <button type="button" class="btn btn-danger shadow-sm btn-delete" 
                            data-id="<?= $chamado['id'] ?>" 
                            data-titulo="<?= htmlspecialchars($chamado['assunto']) ?>">
                        <i class="fas fa-trash me-2"></i>
                        Excluir Chamado
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Informações do Sistema -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0 text-gray-800">
                    <i class="fas fa-clock me-2 text-primary"></i>
                    Informações do Sistema
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong class="text-gray-800">Criado em:</strong><br>
                    <span class="text-muted"><?= format_datetime($chamado['created_at']) ?></span>
                </div>
                
                <div class="mb-3">
                    <strong class="text-gray-800">Última atualização:</strong><br>
                    <span class="text-muted"><?= format_datetime($chamado['updated_at']) ?></span>
                </div>
                
                <div class="mb-3">
                    <strong class="text-gray-800">Total de respostas:</strong><br>
                    <span class="text-muted"><?= count($respostas) ?></span>
                </div>
                
                <div>
                    <strong class="text-gray-800">ID:</strong><br>
                    <code class="text-muted"><?= $chamado['id'] ?></code>
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

.timeline-item {
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    bottom: -16px;
    width: 2px;
    background-color: #e3e6f0;
}

.avatar {
    font-size: 14px;
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

.dark .card-body .text-gray-800,
.dark .card-body strong {
    color: #f9fafb !important;
}

.dark .card-body .text-muted {
    color: #9ca3af !important;
}

.dark .card-body code {
    color: #9ca3af !important;
    background-color: #374151 !important;
}

.dark .bg-light {
    background-color: #374151 !important;
}

.dark .form-label {
    color: #f9fafb !important;
}

.dark .form-control,
.dark textarea {
    background-color: #374151 !important;
    border-color: #4b5563 !important;
    color: #f9fafb !important;
}

.dark .form-control:focus,
.dark textarea:focus {
    background-color: #374151 !important;
    border-color: #3b82f6 !important;
    color: #f9fafb !important;
}

.dark .form-control::placeholder,
.dark textarea::placeholder {
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

/* Ícones em branco/azul claro no modo dark */
.dark .fas,
.dark i.fas {
    color: #f9fafb !important;
}

.dark .text-primary,
.dark i.text-primary {
    color: #60a5fa !important;
}

/* Garantir que todos os ícones Font Awesome fiquem visíveis */
.dark [class*="fa-"] {
    color: #f9fafb !important;
}

.dark .text-primary [class*="fa-"],
.dark i.text-primary {
    color: #60a5fa !important;
}

/* Ícones com text-muted também devem ser visíveis */
.dark .text-muted [class*="fa-"],
.dark i.text-muted {
    color: #9ca3af !important;
}

.dark .timeline-item:not(:last-child)::after {
    background-color: #374151 !important;
}

.dark h1,
.dark h2,
.dark h3,
.dark h4,
.dark h5,
.dark h6 {
    color: #f9fafb !important;
}

.dark .badge {
    color: #f9fafb !important;
}

/* Responsividade */
@media (max-width: 768px) {
    .d-grid.gap-2 {
        gap: 0.5rem !important;
    }
}
</style>

<script>
document.addEventListener('click', function(e) {
    // Fechar chamado
    if (e.target.closest('.btn-fechar-chamado')) {
        const button = e.target.closest('.btn-fechar-chamado');
        const id = button.dataset.id;
        const titulo = button.dataset.titulo;
        
        if (confirm(`Tem certeza que deseja fechar o chamado "${titulo}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('chamados') ?>/' + id + '/fechar';
            
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Excluir chamado
    if (e.target.closest('.btn-delete')) {
        const button = e.target.closest('.btn-delete');
        const id = button.dataset.id;
        const titulo = button.dataset.titulo;
        
        if (confirm(`Tem certeza que deseja excluir o chamado "${titulo}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('chamados') ?>/' + id;
            
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

</div> <!-- Fechar container-fluid -->

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
