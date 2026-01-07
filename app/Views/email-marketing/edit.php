<?php
$currentPage = 'email-marketing';
ob_start();

$campaign = $campaign ?? [];
$attachments = $attachments ?? [];
?>

<div class="pt-6 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-edit mr-2"></i>
                Editar Campanha
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Edite os detalhes da campanha</p>
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
        <form method="POST" action="<?= url('email-marketing/' . $campaign['id']) ?>" enctype="multipart/form-data" class="space-y-6" id="campaignForm" onsubmit="return syncEditor()">
            <input type="hidden" name="_method" value="PUT">
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <!-- Nome da Campanha -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nome da Campanha <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($campaign['name'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Assunto -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Assunto do E-mail <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="subject" required value="<?= htmlspecialchars($campaign['subject'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Corpo do E-mail -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Conteúdo do E-mail <span class="text-red-500">*</span>
                    </label>
                    <div id="emailBodyEditor" style="height: 400px; background-color: #ffffff;" class="border border-gray-300 dark:border-gray-600 rounded-lg"></div>
                    <textarea id="emailBody" name="body" style="display: none;"><?= htmlspecialchars($campaign['body'] ?? '') ?></textarea>
                </div>

                <!-- Agendamento -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Agendar Envio
                    </label>
                    <input type="datetime-local" name="scheduled_at"
                           value="<?= !empty($campaign['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($campaign['scheduled_at'])) : '' ?>"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Anexos Existentes -->
                <?php if (!empty($attachments)): ?>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Anexos Atuais
                        </label>
                        <div class="space-y-2">
                            <?php foreach ($attachments as $attachment): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-paperclip mr-2 text-gray-500 dark:text-gray-400"></i>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            <?= htmlspecialchars($attachment['file_name']) ?>
                                            (<?= number_format($attachment['file_size'] / 1024, 2) ?> KB)
                                        </span>
                                    </div>
                                    <a href="<?= url('email-marketing/' . $campaign['id'] . '/attachments/' . $attachment['id'] . '/remove') ?>"
                                       class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                       onclick="return confirm('Deseja remover este anexo?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Novos Anexos -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Adicionar Anexos
                    </label>
                    <input type="file" name="attachments[]" multiple
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-4">
                <a href="<?= url('email-marketing/' . $campaign['id']) ?>" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Salvar Alterações
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
    // Sincronizar conteúdo do editor
    if (quillEditor) {
        const content = quillEditor.root.innerHTML;
        document.getElementById('emailBody').value = content;
    }
    return true; // Permitir submit
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
    
    // Carregar conteúdo existente
    const existingContent = document.getElementById('emailBody').value;
    if (existingContent) {
        quillEditor.root.innerHTML = existingContent;
    }
    
    // Forçar fundo branco no editor
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
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

