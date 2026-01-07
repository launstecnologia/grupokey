<?php
$title = $title ?? 'Upload de Relatório';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($title) ?></h1>
        <a href="<?= url('billing') ?>" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Voltar
        </a>
    </div>

    <!-- Formulário de Upload -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upload de Arquivo Excel</h6>
                </div>
                <div class="card-body">
                    <form action="<?= url('billing') ?>" method="POST" enctype="multipart/form-data" id="uploadForm" onsubmit="return false;">
                        <div class="mb-3">
                            <label for="report_title" class="form-label">Título do Relatório</label>
                            <input type="text" class="form-control" id="report_title" name="report_title" 
                                   placeholder="Ex: Relatório de Faturamento - Janeiro 2025" required>
                            <div class="form-text">
                                Digite um título descritivo para o relatório
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="company_code" class="form-label">Código da Empresa</label>
                            <input type="text" class="form-control" id="company_code" name="company_code" 
                                   placeholder="Ex: EMP001">
                            <div class="form-text">
                                Código identificador da empresa (opcional)
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">Arquivo Excel</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xls,.xlsx,.csv" required>
                            <div class="form-text">
                                Formatos aceitos: .xls, .xlsx, .csv (máximo 10MB)
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-upload"></i> Processar Arquivo
                            </button>
                            <a href="<?= url('billing') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Exemplo de Estrutura</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>CNPJ/CPF</th>
                                    <th>REPRESENTANTE</th>
                                    <th>TPV Total</th>
                                    <th>Markup</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>João Silva</td>
                                    <td>123.456.789-00</td>
                                    <td>Nome do Representante</td>
                                    <td>R$ 1.000,00</td>
                                    <td>R$ 10,00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Processamento -->
<div id="processingModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" style="display: none; align-items: center; justify-content: center;">
    <div class="relative mx-auto p-8 bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="text-center">
            <div class="mb-4">
                <i class="fas fa-spinner fa-spin text-blue-600 text-5xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Processando Relatório</h3>
            <p class="text-sm text-gray-500 mb-4">
                Por favor, aguarde enquanto o arquivo está sendo processado...
            </p>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full animate-pulse" style="width: 100%"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('excel_file');
    const titleInput = document.getElementById('report_title');
    const companyCodeInput = document.getElementById('company_code');
    const submitBtn = document.getElementById('submitBtn');
    const processingModal = document.getElementById('processingModal');
    
    // Verificar se o formulário existe
    if (!uploadForm) {
        console.error('Formulário não encontrado!');
        return;
    }
    
    // Log quando arquivo é selecionado
    fileInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            const file = this.files[0];
            const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
            console.log('Arquivo selecionado:', file.name, '| Tamanho:', fileSizeMB + 'MB');
        } else {
            console.log('Nenhum arquivo selecionado');
        }
    });
    
    // Interceptar o envio do formulário e usar AJAX
    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('=== INÍCIO VALIDAÇÃO DO FORMULÁRIO ===');
        
        // Validar título
        if (!titleInput.value.trim()) {
            alert('Por favor, digite um título para o relatório.');
            return false;
        }
        
        // Validar arquivo
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Por favor, selecione um arquivo Excel.');
            return false;
        }
        
        const file = fileInput.files[0];
        const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
        console.log('Arquivo:', file.name, '| Tamanho:', fileSizeMB + 'MB');
        
        // Validar extensão
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedExtensions = ['xls', 'xlsx', 'csv'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedExtensions.includes(fileExtension)) {
            alert('Tipo de arquivo não permitido. Apenas arquivos Excel (.xls, .xlsx) ou CSV são aceitos.');
            return false;
        }
        
        // Validar tamanho
        if (file.size > maxSize) {
            alert('Arquivo muito grande. Tamanho máximo permitido: 10MB. Tamanho do arquivo: ' + fileSizeMB + 'MB.');
            return false;
        }
        
        console.log('Validações passaram. Preparando envio via AJAX...');
        console.log('Arquivo antes de criar FormData:', file.name, file.size, file.type);
        
        // Criar FormData
        const formData = new FormData();
        
        // Adicionar arquivo primeiro
        console.log('Adicionando arquivo ao FormData...');
        formData.append('excel_file', file, file.name);
        console.log('Arquivo adicionado. Verificando...');
        console.log('FormData.has(excel_file):', formData.has('excel_file'));
        
        // Adicionar outros campos
        formData.append('report_title', titleInput.value.trim());
        if (companyCodeInput && companyCodeInput.value.trim()) {
            formData.append('company_code', companyCodeInput.value.trim());
        }
        
        // Verificar se o arquivo está no FormData
        if (!formData.has('excel_file')) {
            console.error('ERRO CRÍTICO: Arquivo não encontrado no FormData após adicionar!');
            alert('Erro ao preparar arquivo para envio. Por favor, tente novamente.');
            return false;
        }
        
        // Verificar o arquivo no FormData
        const fileInFormData = formData.get('excel_file');
        console.log('Arquivo no FormData:', fileInFormData instanceof File ? fileInFormData.name + ' (' + fileInFormData.size + ' bytes)' : 'NÃO É FILE!');
        
        if (!(fileInFormData instanceof File)) {
            console.error('ERRO CRÍTICO: O valor em FormData não é um File!');
            alert('Erro: Arquivo não foi adicionado corretamente ao formulário. Por favor, selecione o arquivo novamente.');
            return false;
        }
        
        // Desabilitar botão e mostrar modal de processamento
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
        
        // Mostrar modal de processamento
        processingModal.classList.remove('hidden');
        processingModal.style.display = 'flex';
        
        console.log('Enviando via AJAX...');
        console.log('FormData tem arquivo?', formData.has('excel_file'));
        console.log('FormData tem report_title?', formData.has('report_title'));
        
        // Verificar conteúdo do FormData
        for (let pair of formData.entries()) {
            console.log('FormData:', pair[0], '=', pair[1] instanceof File ? pair[1].name + ' (' + pair[1].size + ' bytes)' : pair[1]);
        }
        
        // Usar XMLHttpRequest (mais confiável para uploads)
        const xhr = new XMLHttpRequest();
        const uploadUrl = '<?= url('billing') ?>';
        
        console.log('Enviando para:', uploadUrl);
        console.log('Tipo do arquivo no FormData:', file.type);
        console.log('Nome do arquivo no FormData:', file.name);
        console.log('Tamanho do arquivo no FormData:', file.size, 'bytes');
        
        xhr.open('POST', uploadUrl, true);
        
        // NÃO definir Content-Type manualmente - deixar o browser definir automaticamente
        // Isso é crítico para multipart/form-data com arquivos
        
        // Acompanhar progresso
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                console.log('Progresso do upload:', percentComplete.toFixed(2) + '%');
            }
        });
        
        // Tratar resposta
        xhr.onload = function() {
            console.log('Resposta recebida. Status:', xhr.status);
            console.log('Response URL:', xhr.responseURL);
            
            if (xhr.status >= 200 && xhr.status < 300) {
                // Sucesso - redirecionar
                if (xhr.responseURL && xhr.responseURL !== window.location.href) {
                    window.location.href = xhr.responseURL;
                } else {
                    // Se não houve redirect, recarregar a página
                    window.location.reload();
                }
            } else {
                // Erro
                console.error('Erro na resposta:', xhr.status, xhr.statusText);
                console.error('Resposta:', xhr.responseText);
                alert('Erro ao processar arquivo. Status: ' + xhr.status);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-upload"></i> Processar Arquivo';
                processingModal.classList.add('hidden');
                processingModal.style.display = 'none';
            }
        };
        
        xhr.onerror = function() {
            console.error('Erro de rede ao enviar arquivo');
            alert('Erro de rede ao processar arquivo. Por favor, verifique sua conexão e tente novamente.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload"></i> Processar Arquivo';
            processingModal.classList.add('hidden');
            processingModal.style.display = 'none';
        };
        
        xhr.onabort = function() {
            console.warn('Upload cancelado');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload"></i> Processar Arquivo';
            processingModal.classList.add('hidden');
            processingModal.style.display = 'none';
        };
        
        // Enviar FormData
        console.log('Iniciando envio do FormData...');
        xhr.send(formData);
        
        return false;
    });
});
</script>
