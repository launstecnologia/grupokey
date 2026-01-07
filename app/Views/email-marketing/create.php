<?php
$currentPage = 'email-marketing';
ob_start();
?>

<div class="pt-6 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-envelope mr-2"></i>
                Nova Campanha de E-mail
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Crie uma nova campanha de e-mail marketing</p>
        </div>

        <!-- Mensagens -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Formulário -->
        <form method="POST" action="<?= url('email-marketing') ?>" enctype="multipart/form-data" class="space-y-6" id="campaignForm" onsubmit="return syncEditor()">
            <?= csrf_field() ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <!-- Nome da Campanha -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nome da Campanha <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="Ex: Promoção de Verão 2025">
                </div>

                <!-- Assunto -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Assunto do E-mail <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="subject" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="Ex: Confira nossas ofertas especiais!">
                </div>

                <!-- Corpo do E-mail (Editor de Texto Rico) -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Conteúdo do E-mail <span class="text-red-500">*</span>
                    </label>
                    <div id="emailBodyEditor" style="height: 400px; background-color: #ffffff;" class="border border-gray-300 dark:border-gray-600 rounded-lg"></div>
                    <textarea id="emailBody" name="body" style="display: none;"></textarea>
                </div>

                <!-- Agendamento -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Agendar Envio (opcional)
                    </label>
                    <input type="datetime-local" name="scheduled_at"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Deixe em branco para enviar imediatamente após adicionar destinatários
                    </p>
                </div>

                <!-- Anexos -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Anexos (opcional)
                    </label>
                    <input type="file" name="attachments[]" multiple
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Formatos aceitos: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG (máx. 10MB por arquivo)
                    </p>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-4">
                <a href="<?= url('email-marketing') ?>" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancelar
                </a>
                <button type="submit" id="submitBtn" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    <span id="submitText">Salvar e Continuar</span>
                    <span id="submitLoading" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Salvando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quill Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<style>
    #emailBodyEditor .ql-editor {
        background-color: #ffffff !important;
        color: #000000 !important;
    }
    #emailBodyEditor .ql-container {
        background-color: #ffffff !important;
    }
    #emailBodyEditor {
        background-color: #ffffff !important;
    }
</style>
<script>
let quillEditor;

// Função para sincronizar Quill antes do submit
function syncEditor() {
    console.log('syncEditor chamado');
    try {
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitLoading = document.getElementById('submitLoading');
        const bodyTextarea = document.getElementById('emailBody');
        
        console.log('quillEditor:', quillEditor);
        console.log('bodyTextarea:', bodyTextarea);
        
        // Sincronizar conteúdo do editor
        if (quillEditor && bodyTextarea) {
            const content = quillEditor.root.innerHTML;
            console.log('Conteúdo do editor:', content);
            bodyTextarea.value = content;
        } else if (!quillEditor) {
            console.warn('Quill editor não foi inicializado, tentando alternativa');
            // Se o editor não foi inicializado, tentar pegar o conteúdo diretamente
            if (bodyTextarea) {
                const editorDiv = document.getElementById('emailBodyEditor');
                if (editorDiv) {
                    bodyTextarea.value = editorDiv.innerHTML;
                }
            }
        }
        
        // Validar campos obrigatórios
        const name = document.querySelector('input[name="name"]')?.value?.trim();
        const subject = document.querySelector('input[name="subject"]')?.value?.trim();
        const body = bodyTextarea?.value?.trim() || '';
        
        console.log('Validação - name:', name, 'subject:', subject, 'body length:', body.length);
        
        if (!name) {
            alert('Por favor, preencha o nome da campanha.');
            return false;
        }
        
        if (!subject) {
            alert('Por favor, preencha o assunto do e-mail.');
            return false;
        }
        
        // Verificar se o body está vazio (remover tags HTML vazias)
        const bodyText = body.replace(/<[^>]*>/g, '').trim();
        if (!body || bodyText === '' || body === '<p><br></p>' || body === '<p></p>') {
            alert('Por favor, preencha o conteúdo do e-mail.');
            return false;
        }
        
        // Mostrar loading apenas se passou todas as validações
        if (submitText) submitText.classList.add('hidden');
        if (submitLoading) submitLoading.classList.remove('hidden');
        if (submitBtn) submitBtn.disabled = true;
        
        console.log('Validação passou, permitindo submit');
        return true; // Permitir submit
    } catch (error) {
        console.error('Erro em syncEditor:', error);
        alert('Erro ao processar formulário. Por favor, tente novamente.');
        return false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Quill Editor
    quillEditor = new Quill('#emailBodyEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Digite o conteúdo do e-mail aqui...'
    });
    
    // Garantir que o editor comece vazio
    quillEditor.setContents([]);
    
    // Forçar fundo branco no editor (com delay para garantir que o DOM está pronto)
    setTimeout(function() {
        const editorElement = document.querySelector('#emailBodyEditor .ql-editor');
        const containerElement = document.querySelector('#emailBodyEditor .ql-container');
        if (editorElement) {
            editorElement.style.backgroundColor = '#ffffff';
            editorElement.style.color = '#000000';
        }
        if (containerElement) {
            containerElement.style.backgroundColor = '#ffffff';
        }
        
        // Garantir que o container também tenha fundo branco
        const quillContainer = document.querySelector('#emailBodyEditor');
        if (quillContainer) {
            quillContainer.style.backgroundColor = '#ffffff';
        }
    }, 100);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

