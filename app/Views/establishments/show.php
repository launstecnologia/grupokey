<?php
use App\Core\Auth;
$currentPage = 'estabelecimentos';
ob_start();

// Definir variáveis com valores padrão
$establishment = $establishment ?? [];
$documents = $documents ?? [];
$approvalHistory = $approvalHistory ?? [];

// Decodificar dados dos produtos se existirem
$productData = [];
if (!empty($establishment['products'])) {
    $productData = $establishment['products'];
}

// Status badges
$statusColors = [
    'PENDING' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
    'APPROVED' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
    'REPROVED' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
    'DISABLED' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'
];
$statusLabels = [
    'PENDING' => 'Pendente',
    'APPROVED' => 'Aprovado',
    'REPROVED' => 'Reprovado',
    'DISABLED' => 'Desabilitado'
];
?>

<div class="pt-6 px-4">
    <!-- Mensagens de Sucesso/Erro -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-building mr-2"></i>
                <?= htmlspecialchars($establishment['nome_fantasia'] ?? 'Estabelecimento') ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Detalhes do estabelecimento</p>
            <div class="mt-2">
                <?php 
                $status = $establishment['status'] ?? 'PENDING';
                $statusClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                $statusLabel = $statusLabels[$status] ?? $status;
                
                // Buscar motivo da reprovação se estiver reprovado
                $reprovalReason = null;
                $reprovalObservation = null;
                if ($status === 'REPROVED' && !empty($approvalHistory)) {
                    foreach ($approvalHistory as $history) {
                        if (($history['status'] ?? '') === 'REPROVED') {
                            $reprovalReason = $history['reason'] ?? null;
                            $reprovalObservation = $history['observation'] ?? null;
                            break; // Pegar a primeira (mais recente)
                        }
                    }
                }
                ?>
                <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full <?= $statusClass ?>">
                    <?= $statusLabel ?>
                </span>
            </div>
            <?php if ($status === 'REPROVED' && !empty($reprovalReason)): ?>
            <div class="mt-3 p-3 bg-red-50 dark:!bg-black dark:border-gray-800 border border-red-200 dark:border-gray-800 rounded-lg shadow-sm reproval-card">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 text-sm mr-2 mt-0.5"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-semibold text-red-800 dark:text-red-200 mb-1.5">
                            Motivo da Reprovação
                        </h4>
                        <div class="bg-white dark:!bg-black rounded-md p-2 border border-red-100 dark:border-gray-800">
                            <p class="text-xs text-red-700 dark:text-red-300 leading-relaxed whitespace-pre-wrap">
                                <?= htmlspecialchars($reprovalReason) ?>
                            </p>
                        </div>
                        <?php if (!empty($reprovalObservation)): ?>
                        <div class="mt-2 pt-2 border-t border-red-200 dark:border-gray-800">
                            <h5 class="text-xs font-medium text-red-700 dark:text-red-300 mb-1">
                                Observação:
                            </h5>
                            <div class="bg-white dark:!bg-black rounded-md p-2 border border-red-100 dark:border-gray-800">
                                <p class="text-xs text-red-600 dark:text-red-400 leading-relaxed whitespace-pre-wrap">
                                    <?= htmlspecialchars($reprovalObservation) ?>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="flex space-x-3">
            <?php if (Auth::isAdmin() || (Auth::isRepresentative() && $establishment['created_by_representative_id'] == Auth::representative()['id'])): ?>
            <a href="<?= url('estabelecimentos/' . ($establishment['id'] ?? '') . '/edit') ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Editar
            </a>
            <?php endif; ?>
            
            <?php if (Auth::isAdmin()): ?>
            <a href="<?= url('estabelecimentos') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conteúdo Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Dados Básicos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600 dark:text-blue-400"></i>
                        Dados Básicos
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nome Completo</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['nome_completo'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nome Fantasia</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['nome_fantasia'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Segmento</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['segmento'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo de Registro</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <?= ($establishment['registration_type'] ?? '') === 'PF' ? 'Pessoa Física' : 'Pessoa Jurídica' ?>
                            </dd>
                        </div>
                        <?php if (($establishment['registration_type'] ?? '') === 'PF'): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">CPF</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['cpf'] ?? '-') ?></dd>
                        </div>
                        <?php else: ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">CNPJ</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['cnpj'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Razão Social</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['razao_social'] ?? '-') ?></dd>
                        </div>
                        <?php endif; ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Telefone</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['telefone'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['email'] ?? '-') ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Endereço -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-blue-600 dark:text-blue-400"></i>
                        Endereço
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">CEP</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['cep'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Logradouro</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['logradouro'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['numero'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Complemento</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['complemento'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Bairro</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['bairro'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cidade / UF</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <?= htmlspecialchars(($establishment['cidade'] ?? '-') . '/' . ($establishment['uf'] ?? '-')) ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Documentos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-file-alt mr-2 text-blue-600 dark:text-blue-400"></i>
                        Documentos
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (!empty($documents)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome do Arquivo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data de Upload</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tamanho</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php 
                                    $documentTypeLabels = [
                                        'CONTRATO_SOCIAL' => 'Contrato Social/ Requerimento de Empresario/CCMEI',
                                        'DOCUMENTO_FOTO_FRENTE' => 'Documento com Foto Frente',
                                        'DOCUMENTO_FOTO_VERSO' => 'Documento com Foto Verso',
                                        'COMPROVANTE_RESIDENCIA' => 'Comprovante de Residencia',
                                        'FOTO_FACHADA' => 'Fotos',
                                        'OUTROS_DOCUMENTOS' => 'Outros Documentos',
                                        'RG_CPF_CNH' => 'RG/CPF/CNH',
                                        'COMPROVANTE_BANCARIO' => 'Comprovante Bancário'
                                    ];
                                    
                                    foreach ($documents as $document): 
                                        $tipoLabel = $documentTypeLabels[$document['document_type'] ?? ''] ?? $document['document_type'] ?? 'Desconhecido';
                                        $fileSize = isset($document['size']) ? number_format($document['size'] / 1024, 2) : '0';
                                    ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($tipoLabel) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <i class="fas fa-file mr-2 text-blue-500 dark:text-blue-400"></i>
                                            <?= htmlspecialchars($document['original_name'] ?? basename($document['file_path'] ?? '')) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?= date('d/m/Y H:i', strtotime($document['uploaded_at'] ?? '')) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= $fileSize ?> KB</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="<?= url('estabelecimentos/' . $establishment['id'] . '/documentos/' . $document['id'] . '/download') ?>" 
                                               class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                                <i class="fas fa-download mr-1"></i> Baixar
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhum documento anexado</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dados Bancários -->
            <?php
            // Verificar se tem PagBank selecionado
            $hasPagBank = false;
            if (isset($productData['other']) && is_array($productData['other'])) {
                foreach ($productData['other'] as $product) {
                    if (($product['product_type'] ?? $product['product_name'] ?? '') === 'prod-pagbank') {
                        $hasPagBank = true;
                        break;
                    }
                }
            }
            // Só mostrar dados bancários se NÃO tiver PagBank
            if (!$hasPagBank):
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-university mr-2 text-blue-600 dark:text-blue-400"></i>
                        Dados Bancários
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Banco</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['banco'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Agência</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['agencia'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Conta</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark-white"><?= htmlspecialchars($establishment['conta'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo de Conta</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <?php
                                $tipoConta = $establishment['tipo_conta'] ?? '';
                                if ($tipoConta === 'conta_corrente') {
                                    echo 'Conta Corrente';
                                } elseif ($tipoConta === 'conta_poupanca') {
                                    echo 'Conta Poupança';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Chave PIX</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['chave_pix'] ?? '-') ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
            <?php endif; ?>

            <!-- Observações -->
            <?php if (!empty($establishment['observacoes'])): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-sticky-note mr-2 text-blue-600 dark:text-blue-400"></i>
                        Observações
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap"><?= htmlspecialchars($establishment['observacoes']) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Produtos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-star mr-2 text-blue-600 dark:text-blue-400"></i>
                        Produtos
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (!empty($productData)): ?>
                        <!-- CDX/EVO -->
                        <?php if (isset($productData['pagseguro'])): ?>
                        <div class="mb-6 p-4 bg-gray-50 dark:!bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                                <i class="fas fa-credit-card mr-2"></i>
                                CDX/EVO
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                <?php if (!empty($productData['pagseguro']['previsao_faturamento'])): ?>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Previsão de Faturamento:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">R$ <?= number_format($productData['pagseguro']['previsao_faturamento'], 2, ',', '.') ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($productData['pagseguro']['tabela'])): ?>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Tabela:</span>
                                    <span class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($productData['pagseguro']['tabela']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($productData['pagseguro']['modelo_maquininha'])): ?>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Modelo de Maquininha:</span>
                                    <span class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($productData['pagseguro']['modelo_maquininha']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($productData['pagseguro']['meio_pagamento'])): ?>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Meio de Pagamento da Adesão:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        <?php
                                        $meioPagamento = $productData['pagseguro']['meio_pagamento'];
                                        $meioLabels = [
                                            'a_vista' => 'À Vista',
                                            'cartao' => 'Cartão',
                                            'criacao' => 'Criação',
                                            'isento' => 'Isento'
                                        ];
                                        echo $meioLabels[$meioPagamento] ?? $meioPagamento;
                                        ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($productData['pagseguro']['valor'])): ?>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Valor:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">R$ <?= number_format($productData['pagseguro']['valor'], 2, ',', '.') ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- CDC -->
                        <?php if (isset($productData['brasilcard'])): ?>
                        <div class="mb-6 p-4 bg-gray-50 dark:!bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                                <i class="fas fa-id-card mr-2"></i>
                                CDC
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <?php if (!empty($productData['brasilcard']['taxa'])): ?>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Taxa:</span>
                                    <span class="font-medium text-gray-900 dark:text-white"><?= number_format($productData['brasilcard']['taxa'], 2, ',', '.') ?>%</span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($productData['brasilcard']['meio_pagamento'])): ?>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Meio de Pagamento da Adesão:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        <?php
                                        $meioPagamento = $productData['brasilcard']['meio_pagamento'];
                                        $meioLabels = [
                                            'a_vista' => 'À Vista',
                                            'cartao' => 'Cartão',
                                            'criacao' => 'Criação',
                                            'isento' => 'Isento'
                                        ];
                                        echo $meioLabels[$meioPagamento] ?? $meioPagamento;
                                        ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($productData['brasilcard']['valor'])): ?>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Valor:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">R$ <?= number_format($productData['brasilcard']['valor'], 2, ',', '.') ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Outros Produtos -->
                        <?php if (isset($productData['other']) && !empty($productData['other'])): ?>
                        <div class="space-y-4">
                            <?php 
                            // Mapeamento de nomes de produtos para exibição
                            $productNameMap = [
                                'prod-outros' => 'Outros Produtos',
                                'prod-membro-key' => 'Membro Key',
                                'prod-google' => 'Google',
                                'prod-pagbank' => 'PagBank',
                                'prod-cdc' => 'CDC',
                                'prod-subaquirente' => 'CDX/EVO',
                            ];
                            
                            // Filtrar produtos que já têm cards próprios (CDC e CDX/EVO)
                            $excludedProductTypes = ['prod-brasil-card', 'prod-cdc', 'prod-pagseguro', 'prod-subaquirente'];
                            
                            foreach ($productData['other'] as $otherProduct): 
                                $productName = $otherProduct['product_name'] ?? $otherProduct['product_type'] ?? '';
                                
                                // Pular produtos que já têm cards próprios (CDC e CDX/EVO)
                                if (in_array($productName, $excludedProductTypes)) {
                                    continue;
                                }
                                
                                $displayName = $productNameMap[$productName] ?? $productName;
                                $isPagBank = ($productName === 'prod-pagbank');
                            ?>
                            <div class="p-4 bg-gray-50 dark:!bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                                    <i class="fas fa-box mr-2"></i>
                                    <?= htmlspecialchars($displayName) ?>
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                    <?php if ($isPagBank && !empty($otherProduct['previsao_faturamento'])): ?>
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Previsão de Faturamento:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">R$ <?= number_format($otherProduct['previsao_faturamento'], 2, ',', '.') ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($isPagBank && !empty($otherProduct['tabela'])): ?>
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Tabela:</span>
                                        <span class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($otherProduct['tabela']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($isPagBank && !empty($otherProduct['modelo_maquininha'])): ?>
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Modelo de Maquininha:</span>
                                        <span class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($otherProduct['modelo_maquininha']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($otherProduct['meio_pagamento'])): ?>
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Meio de Pagamento da Adesão:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            <?php
                                            $meioPagamento = $otherProduct['meio_pagamento'];
                                            $meioLabels = [
                                                'a_vista' => 'À Vista',
                                                'cartao' => 'Cartão',
                                                'criacao' => 'Criação',
                                                'isento' => 'Isento'
                                            ];
                                            echo $meioLabels[$meioPagamento] ?? $meioPagamento;
                                            ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($otherProduct['valor'])): ?>
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Valor:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">R$ <?= number_format($otherProduct['valor'], 2, ',', '.') ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhum produto configurado</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Informações do Sistema -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600 dark:text-blue-400"></i>
                        Informações do Sistema
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['id'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="mt-1">
                                <?php 
                                $status = $establishment['status'] ?? 'PENDING';
                                $statusClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                                $statusLabel = $statusLabels[$status] ?? $status;
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Criado em</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <?= date('d/m/Y H:i', strtotime($establishment['created_at'] ?? '')) ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Atualizado em</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <?= date('d/m/Y H:i', strtotime($establishment['updated_at'] ?? '')) ?>
                            </dd>
                        </div>
                        <?php if (!empty($establishment['created_by_user_name'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Criado por (Admin)</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['created_by_user_name']) ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($establishment['created_by_representative_name'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Criado por (Representante)</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($establishment['created_by_representative_name']) ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- Ações Administrativas -->
            <?php if (Auth::isAdmin()): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-cogs mr-2 text-blue-600 dark:text-blue-400"></i>
                        Ações Administrativas
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex flex-col gap-4">
                        <?php 
                        // Verificar se tem prod-pagbank
                        $hasPagBank = false;
                        if (isset($productData['other']) && is_array($productData['other'])) {
                            foreach ($productData['other'] as $product) {
                                if (($product['product_type'] ?? $product['product_name'] ?? '') === 'prod-pagbank') {
                                    $hasPagBank = true;
                                    break;
                                }
                            }
                        }
                        
                        // Verificar se API SistPay está configurada
                        $sistPayApiActive = $sistPayApiActive ?? false;
                        $sistPaySettings = $sistPaySettings ?? [];
                        $isSandbox = !empty($sistPaySettings['is_sandbox'] ?? false);
                        ?>
                        
                        <?php if ($hasPagBank && $sistPayApiActive): ?>
                        <form method="POST" action="<?= url('estabelecimentos/' . ($establishment['id'] ?? '') . '/migrate-sistpay') ?>" class="w-full" onsubmit="return confirm('<?= $isSandbox ? 'Tem certeza que deseja testar a migração para SistPay em modo SANDBOX? Os dados serão validados mas não serão salvos no banco da SistPay.' : 'Tem certeza que deseja migrar este estabelecimento para SistPay?' ?>')">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-full <?= $isSandbox ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-blue-600 hover:bg-blue-700' ?> text-white px-4 py-2 rounded-lg inline-flex items-center justify-center transition-colors">
                                <i class="fas fa-cloud-upload-alt mr-2"></i>
                                <?= $isSandbox ? 'Testar Migração (Sandbox)' : 'Migrar para SistPay' ?>
                            </button>
                            <?php if ($isSandbox): ?>
                            <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-2 text-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Modo Sandbox: dados serão validados mas não salvos
                            </p>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                        
                        <?php if (!empty($establishment['sistpay_id'])): ?>
                        <a href="https://sistpay.com.br/envio-documento/<?= htmlspecialchars($establishment['sistpay_id']) ?>" 
                           target="_blank"
                           class="w-full bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-lg inline-flex items-center justify-center transition-colors">
                            <i class="fas fa-paper-plane mr-2 text-blue-600 dark:text-blue-400"></i>
                            Enviar Documento para Muse
                        </a>
                        <?php endif; ?>
                        
                        <?php if (($establishment['status'] ?? '') === 'PENDING'): ?>
                        <form method="POST" action="<?= url('estabelecimentos/' . ($establishment['id'] ?? '') . '/approve') ?>" class="w-full">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-full bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-lg inline-flex items-center justify-center transition-colors">
                                <i class="fas fa-check mr-2 text-green-600 dark:text-green-400"></i>
                                Aprovar
                            </button>
                        </form>
                        <button type="button" onclick="openReproveModal()" class="w-full bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-lg inline-flex items-center justify-center transition-colors">
                            <i class="fas fa-times mr-2 text-red-600 dark:text-red-400"></i>
                            Reprovar
                        </button>
                        <?php endif; ?>
                        
                        <form method="POST" action="<?= url('estabelecimentos/' . ($establishment['id'] ?? '')) ?>" class="w-full" onsubmit="return confirm('Tem certeza que deseja excluir este estabelecimento?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="w-full bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-lg inline-flex items-center justify-center transition-colors">
                                <i class="fas fa-trash mr-2 text-red-600 dark:text-red-400"></i>
                                Excluir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Reprovação -->
<div id="reproveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reprovar Estabelecimento</h3>
        <form method="POST" action="<?= url('estabelecimentos/' . ($establishment['id'] ?? '') . '/reprove') ?>">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label for="reprove_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Motivo da Reprovação <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="reprove_reason" 
                    name="reason" 
                    rows="4" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Digite o motivo da reprovação..."></textarea>
            </div>
            <div class="mb-4">
                <label for="reprove_observation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Observação (opcional)
                </label>
                <textarea 
                    id="reprove_observation" 
                    name="observation" 
                    rows="3" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Observações adicionais..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button 
                    type="button" 
                    onclick="closeReproveModal()" 
                    class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                    Cancelar
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Confirmar Reprovação
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openReproveModal() {
    document.getElementById('reproveModal').classList.remove('hidden');
    document.getElementById('reprove_reason').focus();
}

function closeReproveModal() {
    document.getElementById('reproveModal').classList.add('hidden');
    document.getElementById('reprove_reason').value = '';
    document.getElementById('reprove_observation').value = '';
}

// Fechar modal ao clicar fora
document.getElementById('reproveModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReproveModal();
    }
});

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReproveModal();
    }
});
</script>

<style>
/* Dark mode - Botões das Ações Administrativas com fundo branco/claro */
.dark button.bg-white,
.dark .bg-white button {
    background-color: #374151 !important;
    color: #f9fafb !important;
    border-color: #4b5563 !important;
}

.dark button.bg-white:hover,
.dark .bg-white button:hover {
    background-color: #4b5563 !important;
    color: #f9fafb !important;
}

.dark button.bg-gray-700,
.dark .bg-gray-700 button {
    background-color: #374151 !important;
    color: #f9fafb !important;
    border-color: #4b5563 !important;
}

.dark button.bg-gray-700:hover,
.dark .bg-gray-700 button:hover {
    background-color: #4b5563 !important;
    color: #f9fafb !important;
}

/* Card do motivo da reprovação - fundo preto no modo dark */
.dark .reproval-card {
    background-color: #000000 !important;
    border-color: #1f2937 !important;
}

.dark .reproval-card > div > div > div {
    background-color: #000000 !important;
}

.dark .reproval-card .bg-white {
    background-color: #000000 !important;
}

.dark .reproval-card div[class*="bg-white"] {
    background-color: #000000 !important;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>