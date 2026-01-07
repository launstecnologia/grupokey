<?php
$currentPage = 'estabelecimentos';
ob_start();
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            <i class="fas fa-file-upload mr-2"></i>
            Importar Estabelecimentos (CSV)
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Importe estabelecimentos do PagSeguro via arquivo CSV com produto PagBank</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg dark:bg-green-800 dark:border-green-600 dark:text-green-200">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg dark:bg-red-800 dark:border-red-600 dark:text-red-200">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Formulário de Upload -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-file-csv mr-2"></i>
            Selecionar Arquivo CSV
        </h2>
        
        <form method="POST" action="<?= url('estabelecimentos/import') ?>" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Arquivo CSV *
                </label>
                <input type="file" 
                       name="csv_file" 
                       accept=".csv,.txt"
                       required
                       class="block w-full text-sm text-gray-500 dark:text-gray-400
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-lg file:border-0
                              file:text-sm file:font-semibold
                              file:bg-blue-50 file:text-blue-700
                              hover:file:bg-blue-100
                              dark:file:bg-gray-700 dark:file:text-gray-300
                              dark:hover:file:bg-gray-600">
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Formato esperado: CNPJ;CPF;CADASTRO;SEGMENTO;RAZÃO SOCIAL;NOME FANTASIA;NOME COMPLETO;DATA DE NASCIMENTO;TELEFONE CELULAR;ENDEREÇO;BAIRRO;CIDADE;UF;CEP;E-MAIL
                </p>
            </div>

            <div class="flex items-center">
                <input type="checkbox" 
                       name="skip_duplicates" 
                       id="skip_duplicates"
                       value="1"
                       checked
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                <label for="skip_duplicates" class="ml-2 block text-sm text-gray-900 dark:text-white">
                    Pular registros duplicados (mesmo CPF/CNPJ)
                </label>
            </div>

            <div class="flex items-center">
                <input type="checkbox" 
                       name="auto_approve" 
                       id="auto_approve"
                       value="1"
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                <label for="auto_approve" class="ml-2 block text-sm text-gray-900 dark:text-white">
                    Aprovar automaticamente os estabelecimentos importados
                </label>
            </div>

            <div class="pt-4">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-flex items-center transition-colors">
                    <i class="fas fa-upload mr-2"></i>
                    Importar CSV
                </button>
                <a href="<?= url('estabelecimentos') ?>" 
                   class="ml-3 bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg inline-flex items-center transition-colors dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-white">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <!-- Instruções -->
    <div class="bg-blue-50 dark:bg-gray-700 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
            <i class="fas fa-info-circle mr-2"></i>
            Instruções
        </h3>
        <ul class="list-disc list-inside space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <li>O arquivo CSV deve usar ponto e vírgula (;) como separador</li>
            <li>A primeira linha deve conter os cabeçalhos das colunas</li>
            <li>Todos os estabelecimentos importados receberão automaticamente o produto <strong>PagBank</strong></li>
            <li>O campo CADASTRO deve conter "PF" para Pessoa Física ou "PJ" para Pessoa Jurídica</li>
            <li>CPF e CNPJ devem estar formatados (com pontos e traços) ou apenas números</li>
            <li>Registros com CPF/CNPJ duplicados serão ignorados se a opção estiver marcada</li>
            <li>O sistema validará automaticamente CPF e CNPJ antes de importar</li>
        </ul>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

