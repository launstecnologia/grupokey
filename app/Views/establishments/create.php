<?php
$currentPage = 'estabelecimentos';
ob_start();

// Definir variáveis com valores padrão
$products = $products ?? [];
$representatives = $representatives ?? [];
$segments = $segments ?? [];
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-plus mr-2"></i>
                Novo Estabelecimento
            </h1>
            <p class="text-gray-600 mt-1">Cadastre um novo estabelecimento no sistema</p>
        </div>
        <div>
            <a href="<?= url('estabelecimentos') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
        </div>
    </div>

    <!-- Mensagens de Sucesso/Erro -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['validation_errors']) && !empty($_SESSION['validation_errors'])): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle mr-2 mt-1"></i>
                <div class="flex-1">
                    <strong class="block mb-2">Erros de validação encontrados:</strong>
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($_SESSION['validation_errors'] as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['validation_errors']); ?>
    <?php endif; ?>

    <!-- Formulário -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-building mr-2"></i>
                Dados do Estabelecimento
            </h3>
        </div>

        <form method="POST" action="<?= url('estabelecimentos') ?>" enctype="multipart/form-data" class="p-6">
            <?= csrf_field() ?>
            
            <!-- Tipo de Registro -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-id-card mr-2 text-blue-600"></i>
                    Tipo de Registro
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="relative flex cursor-pointer">
                        <input type="radio" name="registration_type" value="PF" class="sr-only peer" required <?= old('registration_type') === 'PF' ? 'checked' : '' ?>>
                        <div class="w-full p-4 border-2 border-gray-300 rounded-lg transition-all duration-200 peer-checked:border-blue-600 peer-checked:bg-blue-600 hover:border-blue-400">
                            <div class="flex items-center justify-center">
                                <div class="text-sm font-medium text-gray-900 peer-checked:text-white transition-colors">Pessoa Física</div>
                            </div>
                        </div>
                    </label>
                    <label class="relative flex cursor-pointer">
                        <input type="radio" name="registration_type" value="PJ" class="sr-only peer" required <?= old('registration_type') === 'PJ' ? 'checked' : '' ?>>
                        <div class="w-full p-4 border-2 border-gray-300 rounded-lg transition-all duration-200 peer-checked:border-blue-600 peer-checked:bg-blue-600 hover:border-blue-400">
                            <div class="flex items-center justify-center">
                                <div class="text-sm font-medium text-gray-900 peer-checked:text-white transition-colors">Pessoa Jurídica</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Dados Básicos -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                    Dados Básicos
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome Completo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                        <input type="text" name="nome_completo" value="<?= htmlspecialchars(old('nome_completo')) ?>" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o nome completo">
                    </div>

                    <!-- Nome Fantasia -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia *</label>
                        <input type="text" name="nome_fantasia" value="<?= htmlspecialchars(old('nome_fantasia')) ?>" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o nome fantasia">
                    </div>

                    <!-- Segmento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Segmento *</label>
                        <select name="segmento" id="segmento" required 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione o segmento</option>
                            <?php foreach ($segments as $segment): ?>
                                <option value="<?= htmlspecialchars($segment['nome']) ?>" <?= old('segmento') === $segment['nome'] ? 'selected' : '' ?>><?= htmlspecialchars($segment['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Telefone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                        <input type="tel" name="telefone" value="<?= htmlspecialchars(old('telefone')) ?>" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="(00) 00000-0000">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars(old('email')) ?>" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o email">
                    </div>
                </div>
            </div>

            <!-- Campos específicos por tipo -->
            <div class="mb-8">
                <!-- Campos PF -->
                <div id="pf-fields" class="hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CPF *</label>
                        <input type="text" name="cpf" value="<?= htmlspecialchars(old('cpf')) ?>" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="000.000.000-00">
                    </div>
                </div>

                <!-- Campos PJ -->
                <div id="pj-fields" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ *</label>
                            <div class="flex gap-2">
                                <input type="text" name="cnpj" id="cnpj" value="<?= htmlspecialchars(old('cnpj')) ?>" 
                                       class="mt-1 block flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="00.000.000/0000-00">
                                <button type="button" id="btn-buscar-cnpj" class="mt-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                            <small class="text-gray-500">Digite o CNPJ e clique em Buscar para preencher automaticamente</small>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Razão Social *</label>
                            <input type="text" name="razao_social" value="<?= htmlspecialchars(old('razao_social')) ?>" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Digite a razão social">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                    Endereço
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- CEP -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CEP *</label>
                        <div class="flex gap-2">
                            <input type="text" name="cep" id="cep" value="<?= htmlspecialchars(old('cep')) ?>" required 
                                   class="mt-1 block flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="00000-000">
                            <button type="button" id="btn-buscar-cep" class="mt-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 hidden">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                        <small id="cep-help" class="text-gray-500 hidden">Digite o CEP e clique em Buscar para preencher automaticamente</small>
                    </div>

                    <!-- Logradouro -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro *</label>
                        <input type="text" name="logradouro" value="<?= htmlspecialchars(old('logradouro')) ?>" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o logradouro">
                    </div>

                    <!-- Número -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número *</label>
                        <input type="text" name="numero" value="<?= htmlspecialchars(old('numero')) ?>" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o número">
                    </div>

                    <!-- Complemento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                        <input type="text" name="complemento" value="<?= htmlspecialchars(old('complemento')) ?>" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o complemento">
                    </div>

                    <!-- Bairro -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bairro *</label>
                        <input type="text" name="bairro" value="<?= htmlspecialchars(old('bairro')) ?>" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o bairro">
                    </div>

                    <!-- Cidade -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cidade *</label>
                        <input type="text" name="cidade" value="<?= htmlspecialchars(old('cidade')) ?>" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite a cidade">
                    </div>

                    <!-- UF -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">UF *</label>
                        <select name="uf" required 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione a UF</option>
                            <option value="AC" <?= old('uf') === 'AC' ? 'selected' : '' ?>>AC</option>
                            <option value="AL" <?= old('uf') === 'AL' ? 'selected' : '' ?>>AL</option>
                            <option value="AP" <?= old('uf') === 'AP' ? 'selected' : '' ?>>AP</option>
                            <option value="AM" <?= old('uf') === 'AM' ? 'selected' : '' ?>>AM</option>
                            <option value="BA" <?= old('uf') === 'BA' ? 'selected' : '' ?>>BA</option>
                            <option value="CE" <?= old('uf') === 'CE' ? 'selected' : '' ?>>CE</option>
                            <option value="DF" <?= old('uf') === 'DF' ? 'selected' : '' ?>>DF</option>
                            <option value="ES" <?= old('uf') === 'ES' ? 'selected' : '' ?>>ES</option>
                            <option value="GO" <?= old('uf') === 'GO' ? 'selected' : '' ?>>GO</option>
                            <option value="MA" <?= old('uf') === 'MA' ? 'selected' : '' ?>>MA</option>
                            <option value="MT" <?= old('uf') === 'MT' ? 'selected' : '' ?>>MT</option>
                            <option value="MS" <?= old('uf') === 'MS' ? 'selected' : '' ?>>MS</option>
                            <option value="MG" <?= old('uf') === 'MG' ? 'selected' : '' ?>>MG</option>
                            <option value="PA" <?= old('uf') === 'PA' ? 'selected' : '' ?>>PA</option>
                            <option value="PB" <?= old('uf') === 'PB' ? 'selected' : '' ?>>PB</option>
                            <option value="PR" <?= old('uf') === 'PR' ? 'selected' : '' ?>>PR</option>
                            <option value="PE" <?= old('uf') === 'PE' ? 'selected' : '' ?>>PE</option>
                            <option value="PI" <?= old('uf') === 'PI' ? 'selected' : '' ?>>PI</option>
                            <option value="RJ" <?= old('uf') === 'RJ' ? 'selected' : '' ?>>RJ</option>
                            <option value="RN" <?= old('uf') === 'RN' ? 'selected' : '' ?>>RN</option>
                            <option value="RS" <?= old('uf') === 'RS' ? 'selected' : '' ?>>RS</option>
                            <option value="RO" <?= old('uf') === 'RO' ? 'selected' : '' ?>>RO</option>
                            <option value="RR" <?= old('uf') === 'RR' ? 'selected' : '' ?>>RR</option>
                            <option value="SC" <?= old('uf') === 'SC' ? 'selected' : '' ?>>SC</option>
                            <option value="SP" <?= old('uf') === 'SP' ? 'selected' : '' ?>>SP</option>
                            <option value="SE" <?= old('uf') === 'SE' ? 'selected' : '' ?>>SE</option>
                            <option value="TO" <?= old('uf') === 'TO' ? 'selected' : '' ?>>TO</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Produtos -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-star mr-2 text-blue-600"></i>
                    Produtos
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php 
                    // Mapeamento de nomes de produtos
                    $productNameMap = [
                        'BrasilCard' => 'CDC',
                        'BRASILCARD' => 'CDC',
                        'brasilcard' => 'CDC',
                        'Brasil Card' => 'CDC',
                        'MERCADO_PAGO' => 'CDX/EVO',
                        'Mercado Pago' => 'CDX/EVO',
                        'MercadoPago' => 'CDX/EVO',
                        'PAGSEGURO_MP' => 'CDX/EVO',
                        'PagSeguro/MP' => 'CDX/EVO',
                        'prod-subaquirente' => 'CDX/EVO',
                        'SUBAQUIRENTE' => 'CDX/EVO',
                        'Conteúdo Digital' => null, // Remover
                        'CONTEUDO_DIGITAL' => null, // Remover
                        'Conteudo Digital' => null, // Remover
                        'Flamex' => null, // Remover
                        'FLAMEX' => null, // Remover
                        'Griva' => null, // Remover
                        'GRIVA' => null, // Remover
                    ];
                    
                    foreach ($products as $product): 
                        $productName = trim($product['name'] ?? '');
                        $productId = strtoupper(trim($product['id'] ?? ''));
                        $productNameUpper = strtoupper($productName);
                        
                        // Verificar se deve remover o produto (por nome ou ID)
                        $shouldRemove = false;
                        if (isset($productNameMap[$productName]) && $productNameMap[$productName] === null) {
                            $shouldRemove = true;
                        } elseif (isset($productNameMap[$productNameUpper]) && $productNameMap[$productNameUpper] === null) {
                            $shouldRemove = true;
                        } elseif (isset($productNameMap[$productId]) && $productNameMap[$productId] === null) {
                            $shouldRemove = true;
                        } elseif (stripos($productName, 'Conteúdo Digital') !== false || stripos($productName, 'Conteudo Digital') !== false) {
                            $shouldRemove = true;
                        } elseif (stripos($productName, 'Flamex') !== false || stripos($productName, 'Flamx') !== false) {
                            $shouldRemove = true;
                        } elseif (stripos($productName, 'Griva') !== false) {
                            $shouldRemove = true;
                        }
                        
                        if ($shouldRemove) {
                            continue; // Pular produtos que devem ser removidos
                        }
                        
                        // Verificar se deve substituir o nome
                        $displayName = $productName;
                        if (isset($productNameMap[$productName])) {
                            $displayName = $productNameMap[$productName];
                        } elseif (isset($productNameMap[$productNameUpper])) {
                            $displayName = $productNameMap[$productNameUpper];
                        } elseif (isset($productNameMap[$productId])) {
                            $displayName = $productNameMap[$productId];
                        } elseif (stripos($productId, 'BRASILCARD') !== false || stripos($productId, 'PROD-BRASIL-CARD') !== false || stripos($productName, 'Brasil Card') !== false || stripos($productName, 'BrasilCard') !== false) {
                            $displayName = 'CDC';
                        } elseif (stripos($productId, 'PAGSEGURO_MP') !== false || stripos($productId, 'SUBAQUIRENTE') !== false || stripos($productId, 'PROD-SUBAQUIRENTE') !== false || stripos($productName, 'PagSeguro/MP') !== false || stripos($productName, 'MercadoPago') !== false || stripos($productName, 'Mercado Pago') !== false) {
                            $displayName = 'CDX/EVO';
                        }
                    ?>
                    <label class="flex items-center p-3 border border-gray-300 rounded-md cursor-pointer hover:bg-blue-600 hover:text-white transition-colors">
                        <input type="checkbox" name="products[]" value="<?= $product['id'] ?>" 
                               class="mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                               <?= isset($_SESSION['old_input']['products']) && in_array($product['id'], $_SESSION['old_input']['products']) ? 'checked' : '' ?>>
                        <span class="text-gray-700 hover:text-white"><?= htmlspecialchars($displayName) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Configurações dos Produtos -->
            <!-- Forçar atualização do cache - v2 -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-cog mr-2 text-blue-600"></i>
                    Configurações dos Produtos
                </h4>
                
                <!-- CDX/EVO - Painel de Configuração -->
                <div id="prod-pagseguro-config" class="hidden mb-6 p-4 bg-gray-800 rounded-lg border border-gray-700">
                    <h5 class="font-medium text-white mb-3">PagSeguro</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Previsão de Faturamento</label>
                            <input type="text" name="previsao_faturamento_prod-pagseguro" id="previsao_faturamento_prod-pagseguro"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="R$ 0,00" data-mask="currency">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Tabela</label>
                            <select name="tabela_prod-pagseguro" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione a tabela</option>
                                <option value="77299">77299</option>
                                <option value="D30">D30</option>
                                <option value="D0 PLUS">D0 PLUS</option>
                                <option value="D0 FIT">D0 FIT</option>
                                <option value="Elite">Elite</option>
                                <option value="Master">Master</option>
                                <option value="Padrão">Padrão</option>
                                <option value="Pro">Pro</option>
                                <option value="Super">Super</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Modelo de Maquininha</label>
                            <select name="modelo_maquininha_prod-pagseguro" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione o modelo</option>
                                <option value="Chip3">Chip3</option>
                                <option value="plus2">Plus 2</option>
                                <option value="pro2">Pro 2</option>
                                <option value="smart">Smart</option>
                                <option value="CDX">CDX</option>
                                <option value="EVO">EVO</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Meio de Pagamento da Adesão</label>
                            <select name="meio_pagamento_prod-pagseguro" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione o meio</option>
                                <option value="a_vista">À Vista</option>
                                <option value="cartao">Cartão</option>
                                <option value="criacao">Criação</option>
                                <option value="isento">Isento</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
                            <input type="text" name="valor_prod-pagseguro" id="valor_prod-pagseguro"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="R$ 0,00" data-mask="currency">
                        </div>
                    </div>
                </div>

                <!-- CDC -->
                <div id="prod-brasil-card-config" class="hidden mb-6 p-4 bg-gray-800 rounded-lg border border-gray-700">
                    <h5 class="font-medium text-white mb-3">CDC</h5>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Financeira</label>
                            <select name="taxa_prod-brasil-card" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione a financeira</option>
                                <option value="UCRED">UCRED</option>
                                <option value="PARCELA FACIL">PARCELA FACIL</option>
                                <option value="PARCELEX">PARCELEX</option>
                                <option value="SOU FÁCIL">SOU FÁCIL</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Meio de Pagamento da Adesão</label>
                            <select name="meio_pagamento_prod-brasil-card" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione o meio</option>
                                <option value="a_vista">À Vista</option>
                                <option value="cartao">Cartão</option>
                                <option value="criacao">Criação</option>
                                <option value="isento">Isento</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Valor</label>
                            <input type="text" name="valor_prod-brasil-card" id="valor_prod-brasil-card"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="R$ 0,00" data-mask="currency">
                        </div>
                    </div>
                </div>

                <!-- Outros Produtos -->
                <?php foreach ($products as $product): ?>
                    <?php if ($product['id'] !== 'prod-pagseguro' && $product['id'] !== 'prod-subaquirente' && $product['id'] !== 'prod-brasil-card'): 
                        // Aplicar o mesmo mapeamento de nomes usado nos checkboxes
                        $productName = trim($product['name'] ?? '');
                        $productId = strtoupper(trim($product['id'] ?? ''));
                        $productNameUpper = strtoupper($productName);
                        
                        $displayName = $productName;
                        if (isset($productNameMap[$productName])) {
                            $displayName = $productNameMap[$productName];
                        } elseif (isset($productNameMap[$productNameUpper])) {
                            $displayName = $productNameMap[$productNameUpper];
                        } elseif (isset($productNameMap[$productId])) {
                            $displayName = $productNameMap[$productId];
                        } elseif (stripos($productId, 'BRASILCARD') !== false || stripos($productId, 'PROD-BRASIL-CARD') !== false || stripos($productName, 'Brasil Card') !== false || stripos($productName, 'BrasilCard') !== false) {
                            $displayName = 'CDC';
                        } elseif (stripos($productId, 'PAGSEGURO_MP') !== false || stripos($productId, 'SUBAQUIRENTE') !== false || stripos($productId, 'PROD-SUBAQUIRENTE') !== false || stripos($productName, 'PagSeguro/MP') !== false || stripos($productName, 'MercadoPago') !== false || stripos($productName, 'Mercado Pago') !== false) {
                            $displayName = 'CDX/EVO';
                        }
                        
                        $isPagBank = ($product['id'] === 'prod-pagbank');
                    ?>
                    <div id="<?= $product['id'] ?>-config" class="hidden mb-6 p-4 bg-gray-800 rounded-lg border border-gray-700">
                        <h5 class="font-medium text-white mb-3"><?= htmlspecialchars($displayName) ?></h5>
                        <?php if ($isPagBank): ?>
                        <!-- PagBank - Campos adicionais como CDX/EVO -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Previsão de Faturamento</label>
                                <input type="text" name="previsao_faturamento_<?= $product['id'] ?>" id="previsao_faturamento_<?= $product['id'] ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="R$ 0,00" data-mask="currency">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Tabela</label>
                                <select name="tabela_<?= $product['id'] ?>" 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecione a tabela</option>
                                    <?php
                                    $tabelas = ['77299', 'D30', 'D0 PLUS', 'D0 FIT', 'Elite', 'Master', 'Padrão', 'Pro', 'Super', 'Outros'];
                                    foreach ($tabelas as $tabela): ?>
                                        <option value="<?= $tabela ?>"><?= $tabela ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Modelo de Maquininha</label>
                                <select name="modelo_maquininha_<?= $product['id'] ?>" 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecione o modelo</option>
                                    <?php
                                    $modelos = ['Chip3', 'plus2', 'pro2', 'smart', 'CDX', 'EVO', 'outros'];
                                    foreach ($modelos as $modelo): ?>
                                        <option value="<?= $modelo ?>"><?= $modelo ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Meio de Pagamento da Adesão</label>
                                <select name="meio_pagamento_<?= $product['id'] ?>" 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecione o meio</option>
                                    <option value="a_vista">À Vista</option>
                                    <option value="cartao">Cartão</option>
                                    <option value="criacao">Criação</option>
                                    <option value="isento">Isento</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Valor</label>
                                <input type="text" name="valor_<?= $product['id'] ?>" id="valor_<?= $product['id'] ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="R$ 0,00" data-mask="currency">
                            </div>
                            <?php if ($isPagBank): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Plano SistPay</label>
                                <select name="plan_<?= $product['id'] ?>" 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecione o plano</option>
                                    <?php if (!empty($sistpay_plans)): ?>
                                        <?php foreach ($sistpay_plans as $plan): ?>
                                            <option value="<?= $plan['id'] ?>">
                                                <?= htmlspecialchars($plan['name']) ?> (ID: <?= $plan['id'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Configure a API SistPay para ver os planos</option>
                                    <?php endif; ?>
                                </select>
                                <p class="mt-1 text-xs text-gray-400">Plano que será usado ao migrar para SistPay</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <!-- Outros produtos (Google, Membro Key, Outros) - apenas meio_pagamento e valor -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Meio de Pagamento da Adesão</label>
                                <select name="meio_pagamento_<?= $product['id'] ?>" 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecione o meio</option>
                                    <option value="a_vista">À Vista</option>
                                    <option value="cartao">Cartão</option>
                                    <option value="criacao">Criação</option>
                                    <option value="isento">Isento</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Valor</label>
                                <input type="text" name="valor_<?= $product['id'] ?>" id="valor_<?= $product['id'] ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="R$ 0,00" data-mask="currency">
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Dados Bancários -->
            <div id="dados-bancarios-section" class="mb-8 hidden">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-university mr-2 text-blue-600"></i>
                    Dados Bancários
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Banco -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Banco</label>
                        <input type="text" name="banco" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o nome do banco">
                    </div>

                    <!-- Agência -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Agência</label>
                        <input type="text" name="agencia" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite a agência">
                    </div>

                    <!-- Conta -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conta</label>
                        <input type="text" name="conta" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o número da conta">
                    </div>

                    <!-- Tipo de Conta -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Conta</label>
                        <select name="tipo_conta" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione o tipo</option>
                            <option value="conta_corrente">Conta Corrente</option>
                            <option value="conta_poupanca">Conta Poupança</option>
                        </select>
                    </div>

                    <!-- Chave PIX -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chave PIX</label>
                        <input type="text" name="chave_pix" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite a chave PIX">
                    </div>
                </div>
            </div>

            <!-- Upload de Documentos -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-file-upload mr-2 text-blue-600"></i>
                    Documentos
                </h3>
                
                <div id="documentos-container" class="space-y-4">
                    <!-- Primeiro campo de documento -->
                    <div class="documento-item p-4 border border-gray-300 rounded-lg bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Tipo de Documento <span class="text-red-500">*</span>
                                </label>
                                <select name="document_type[]" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 documento-type-select">
                                    <option value="">Selecione o tipo</option>
                                    <option value="contrato_social_requerimento">Contrato Social/ Requerimento de Empresario/CCMEI</option>
                                    <option value="documento_foto_frente">Documento com Foto Frente</option>
                                    <option value="documento_foto_verso">Documento com Foto Verso</option>
                                    <option value="comprovante_residencia">Comprovante de Residencia</option>
                                    <option value="fotos">Fotos</option>
                                    <option value="outros_documentos">Outros Documentos</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Anexar Documento
                                </label>
                                <input type="file" name="documents[]" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            </div>
                        </div>
                        <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm remove-documento hidden">
                            <i class="fas fa-trash mr-1"></i> Remover
                        </button>
                    </div>
                </div>
                
                <button type="button" id="adicionar-documento" class="mt-4 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    <i class="fas fa-plus mr-2"></i> Adicionar Mais Documentos
                </button>
            </div>

            <!-- Observações -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-sticky-note mr-2 text-blue-600"></i>
                    Observações
                </h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                    <textarea name="observacoes" rows="4" 
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Digite observações sobre o estabelecimento..."></textarea>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-4">
                <a href="<?= url('estabelecimentos') ?>" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" id="btn-salvar-estabelecimento" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-save mr-2"></i>
                    <span id="btn-salvar-texto">Salvar Estabelecimento</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar/ocultar campos específicos por tipo de registro
    const registrationTypeInputs = document.querySelectorAll('input[name="registration_type"]');
    const pfFields = document.getElementById('pf-fields');
    const pjFields = document.getElementById('pj-fields');
    
    const btnBuscarCep = document.getElementById('btn-buscar-cep');
    const cepHelp = document.getElementById('cep-help');
    
    registrationTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'PF') {
                pfFields.classList.remove('hidden');
                pjFields.classList.add('hidden');
                // Mostrar botão de busca de CEP para Pessoa Física
                if (btnBuscarCep) {
                    btnBuscarCep.classList.remove('hidden');
                }
                if (cepHelp) {
                    cepHelp.classList.remove('hidden');
                }
            } else if (this.value === 'PJ') {
                pfFields.classList.add('hidden');
                pjFields.classList.remove('hidden');
                // Ocultar botão de busca de CEP para Pessoa Jurídica
                if (btnBuscarCep) {
                    btnBuscarCep.classList.add('hidden');
                }
                if (cepHelp) {
                    cepHelp.classList.add('hidden');
                }
            }
        });
    });
    
    // Verificar tipo inicial ao carregar a página
    const tipoInicial = document.querySelector('input[name="registration_type"]:checked');
    if (tipoInicial && tipoInicial.value === 'PF') {
        if (btnBuscarCep) {
            btnBuscarCep.classList.remove('hidden');
        }
        if (cepHelp) {
            cepHelp.classList.remove('hidden');
        }
    }
    
    // Função para verificar se deve mostrar campos bancários (apenas CDC ou EVO)
    function verificarCamposBancarios() {
        const dadosBancariosSection = document.getElementById('dados-bancarios-section');
        if (!dadosBancariosSection) return;
        
        let deveMostrar = false;
        const productCheckboxes = document.querySelectorAll('input[name="products[]"]:checked');
        
        productCheckboxes.forEach(checkbox => {
            const productName = checkbox.nextElementSibling?.textContent?.trim() || '';
            const productValue = checkbox.value || '';
            
            // Verificar se é CDC (prod-brasil-card ou nome contém CDC)
            if (productValue === 'prod-brasil-card' || productName.toUpperCase().includes('CDC')) {
                deveMostrar = true;
            }
            
            // Verificar se é EVO (prod-pagseguro ou prod-subaquirente com modelo EVO ou nome contém EVO)
            if (productValue === 'prod-pagseguro' || productValue === 'prod-subaquirente') {
                // Verificar se o modelo selecionado é EVO
                const modeloSelect = document.querySelector('select[name="modelo_maquininha_prod-pagseguro"]');
                if (modeloSelect && modeloSelect.value === 'EVO') {
                    deveMostrar = true;
                }
                // Também verificar se o nome contém EVO
                if (productName.toUpperCase().includes('EVO')) {
                    deveMostrar = true;
                }
            }
            
            // PagBank NÃO precisa de dados bancários, então não adicionar aqui
        });
        
        if (deveMostrar) {
            dadosBancariosSection.classList.remove('hidden');
        } else {
            dadosBancariosSection.classList.add('hidden');
        }
    }
    
    // Mapeamento de IDs de produtos para IDs de configuração
    const productConfigMap = {
        'prod-brasil-card': 'prod-brasil-card-config',
        'prod-cdc': 'prod-brasil-card-config',
        'prod-brasilcard': 'prod-brasil-card-config',
        'prod-pagseguro': 'prod-pagseguro-config',
        'prod-subaquirente': 'prod-pagseguro-config',
        'prod-pagbank': 'prod-pagbank-config'
    };
    
    // Função para obter o ID de configuração baseado no ID do produto
    function getConfigId(productId) {
        // Verificar mapeamento direto
        if (productConfigMap[productId]) {
            return productConfigMap[productId];
        }
        
        // Verificar se contém 'brasil-card' ou 'cdc' (case insensitive)
        if (productId.toLowerCase().includes('brasil-card') || 
            productId.toLowerCase().includes('brasilcard') || 
            productId.toLowerCase().includes('cdc')) {
            return 'prod-brasil-card-config';
        }
        
        // Verificar se contém 'pagseguro' ou 'subaquirente'
        if (productId.toLowerCase().includes('pagseguro') || 
            productId.toLowerCase().includes('subaquirente')) {
            return 'prod-pagseguro-config';
        }
        
        // Verificar se contém 'pagbank'
        if (productId.toLowerCase().includes('pagbank')) {
            return 'prod-pagbank-config';
        }
        
        // Fallback: tentar com o ID original + '-config'
        return productId + '-config';
    }
    
    // Mostrar/ocultar configurações dos produtos
    const productCheckboxes = document.querySelectorAll('input[name="products[]"]');
    
    productCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const configId = getConfigId(this.value);
            const configDiv = document.getElementById(configId);
            
            if (configDiv) {
                if (this.checked) {
                    configDiv.classList.remove('hidden');
                } else {
                    configDiv.classList.add('hidden');
                }
            }
            
            // Verificar campos bancários quando produto mudar
            verificarCamposBancarios();
        });
        
        // Verificar estado inicial (se já estiver marcado)
        if (checkbox.checked) {
            const configId = getConfigId(checkbox.value);
            const configDiv = document.getElementById(configId);
            if (configDiv) {
                configDiv.classList.remove('hidden');
            }
        }
    });
    
    // Verificar campos bancários quando o modelo de maquininha mudar (para EVO)
    const modeloSelect = document.querySelector('select[name="modelo_maquininha_prod-pagseguro"]');
    if (modeloSelect) {
        modeloSelect.addEventListener('change', function() {
            verificarCamposBancarios();
        });
    }
    
    // Verificar campos bancários ao carregar a página
    verificarCamposBancarios();
    
    // Máscara para CEP
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 5) {
                value = value.substring(0, 5) + '-' + value.substring(5, 8);
            }
            this.value = value;
        });
    }
    
    // Busca de CEP via ViaCEP (apenas para Pessoa Física)
    if (btnBuscarCep && cepInput) {
        btnBuscarCep.addEventListener('click', async function() {
            const cep = cepInput.value.replace(/\D/g, '');
            
            if (cep.length !== 8) {
                alert('Por favor, digite um CEP válido (8 dígitos)');
                return;
            }
            
            btnBuscarCep.disabled = true;
            btnBuscarCep.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
            
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();
                
                if (data.erro) {
                    alert('CEP não encontrado. Por favor, verifique o CEP digitado.');
                    btnBuscarCep.disabled = false;
                    btnBuscarCep.innerHTML = '<i class="fas fa-search"></i> Buscar';
                    return;
                }
                
                // Preencher campos automaticamente
                if (data.logradouro) {
                    const campo = document.querySelector('input[name="logradouro"]');
                    if (campo) campo.value = data.logradouro;
                }
                
                if (data.complemento) {
                    const campo = document.querySelector('input[name="complemento"]');
                    if (campo) campo.value = data.complemento;
                }
                
                if (data.bairro) {
                    const campo = document.querySelector('input[name="bairro"]');
                    if (campo) campo.value = data.bairro;
                }
                
                if (data.localidade) {
                    const campo = document.querySelector('input[name="cidade"]');
                    if (campo) campo.value = data.localidade;
                }
                
                if (data.uf) {
                    const selectUf = document.querySelector('select[name="uf"]');
                    if (selectUf) {
                        // Tentar encontrar a opção correspondente
                        const option = Array.from(selectUf.options).find(opt => 
                            opt.value === data.uf || opt.textContent.trim() === data.uf
                        );
                        if (option) {
                            selectUf.value = option.value;
                        } else {
                            selectUf.value = data.uf;
                        }
                    }
                }
                
                // Formatar CEP corretamente
                if (data.cep) {
                    const cepFormatado = data.cep.replace(/\D/g, '');
                    if (cepFormatado.length === 8) {
                        cepInput.value = cepFormatado.substring(0, 5) + '-' + cepFormatado.substring(5, 8);
                    }
                }
                
                btnBuscarCep.disabled = false;
                btnBuscarCep.innerHTML = '<i class="fas fa-search"></i> Buscar';
                
                // Focar no campo número após preencher
                const numeroInput = document.querySelector('input[name="numero"]');
                if (numeroInput) {
                    numeroInput.focus();
                }
                
            } catch (error) {
                console.error('Erro ao buscar CEP:', error);
                alert('Erro ao buscar CEP. Por favor, tente novamente.');
                btnBuscarCep.disabled = false;
                btnBuscarCep.innerHTML = '<i class="fas fa-search"></i> Buscar';
            }
        });
        
        // Permitir busca ao pressionar Enter no campo CEP
        if (cepInput) {
            cepInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !btnBuscarCep.classList.contains('hidden')) {
                    e.preventDefault();
                    btnBuscarCep.click();
                }
            });
        }
    }
    
    // Máscara para CPF
    const cpfInput = document.querySelector('input[name="cpf"]');
    if (cpfInput) {
        cpfInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 11) {
                value = value.substring(0, 3) + '.' + value.substring(3, 6) + '.' + value.substring(6, 9) + '-' + value.substring(9, 11);
            }
            this.value = value;
        });
    }
    
    // Máscara para CNPJ
    const cnpjInput = document.getElementById('cnpj');
    if (cnpjInput) {
        cnpjInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 14) {
                value = value.substring(0, 2) + '.' + value.substring(2, 5) + '.' + value.substring(5, 8) + '/' + value.substring(8, 12) + '-' + value.substring(12, 14);
            }
            this.value = value;
        });
    }
    
    // Busca automática de CNPJ via API ReceitaWS
    const btnBuscarCnpj = document.getElementById('btn-buscar-cnpj');
    if (btnBuscarCnpj && cnpjInput) {
        btnBuscarCnpj.addEventListener('click', async function() {
            const cnpj = cnpjInput.value.replace(/\D/g, '');
            
            if (cnpj.length !== 14) {
                alert('Por favor, digite um CNPJ válido (14 dígitos)');
                return;
            }
            
            btnBuscarCnpj.disabled = true;
            btnBuscarCnpj.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
            
            try {
                // Usar nossa API local para evitar problemas de CORS
                const response = await fetch(`<?= url('api/buscar-cnpj') ?>?cnpj=${cnpj}`);
                const result = await response.json();
                
                if (result.success && result.data && result.data.status === 'OK') {
                    const data = result.data;
                    let camposPreenchidos = 0;
                    
                    // Preencher campos automaticamente (com verificação de existência)
                    if (data.fantasia) {
                        const campo = document.querySelector('input[name="nome_fantasia"]');
                        if (campo) {
                            campo.value = data.fantasia;
                            camposPreenchidos++;
                        }
                    }
                    if (data.nome) {
                        const campo = document.querySelector('input[name="razao_social"]');
                        if (campo) {
                            campo.value = data.nome;
                            camposPreenchidos++;
                        }
                    }
                    if (data.logradouro) {
                        const campo = document.querySelector('input[name="logradouro"]');
                        if (campo) {
                            campo.value = data.logradouro;
                            camposPreenchidos++;
                        }
                    }
                    if (data.numero) {
                        const campo = document.querySelector('input[name="numero"]');
                        if (campo) {
                            campo.value = data.numero;
                            camposPreenchidos++;
                        }
                    }
                    if (data.complemento) {
                        const campo = document.querySelector('input[name="complemento"]');
                        if (campo) {
                            campo.value = data.complemento;
                            camposPreenchidos++;
                        }
                    }
                    if (data.bairro) {
                        const campo = document.querySelector('input[name="bairro"]');
                        if (campo) {
                            campo.value = data.bairro;
                            camposPreenchidos++;
                        }
                    }
                    if (data.municipio) {
                        const campo = document.querySelector('input[name="cidade"]');
                        if (campo) {
                            campo.value = data.municipio;
                            camposPreenchidos++;
                        }
                    }
                    if (data.uf) {
                        // UF pode ser um select ou input
                        const selectUf = document.querySelector('select[name="uf"]');
                        const inputUf = document.querySelector('input[name="uf"]');
                        if (selectUf) {
                            // Tentar encontrar a opção correspondente
                            const option = Array.from(selectUf.options).find(opt => opt.value === data.uf || opt.textContent.trim() === data.uf);
                            if (option) {
                                selectUf.value = option.value;
                                camposPreenchidos++;
                            } else {
                                // Se não encontrar, criar uma opção temporária ou usar value direto
                                selectUf.value = data.uf;
                                camposPreenchidos++;
                            }
                        } else if (inputUf) {
                            inputUf.value = data.uf;
                            camposPreenchidos++;
                        }
                    }
                    if (data.cep) {
                        const campo = document.querySelector('input[name="cep"]');
                        if (campo) {
                            const cepLimpo = data.cep.replace(/\D/g, '');
                            if (cepLimpo.length >= 8) {
                                campo.value = cepLimpo.substring(0, 5) + '-' + cepLimpo.substring(5, 8);
                            } else if (cepLimpo.length === 5) {
                                campo.value = cepLimpo + '-';
                            } else {
                                campo.value = cepLimpo;
                            }
                            camposPreenchidos++;
                        }
                    }
                    if (data.telefone) {
                        const campo = document.querySelector('input[name="telefone"]');
                        if (campo) {
                            const telefone = data.telefone.replace(/\D/g, '');
                            let telefoneFormatado = telefone;
                            if (telefone.length >= 11) {
                                telefoneFormatado = '(' + telefone.substring(0, 2) + ') ' + telefone.substring(2, 7) + '-' + telefone.substring(7, 11);
                            } else if (telefone.length >= 10) {
                                telefoneFormatado = '(' + telefone.substring(0, 2) + ') ' + telefone.substring(2, 6) + '-' + telefone.substring(6, 10);
                            }
                            campo.value = telefoneFormatado;
                            camposPreenchidos++;
                        }
                    }
                    if (data.email) {
                        const campo = document.querySelector('input[name="email"]');
                        if (campo) {
                            campo.value = data.email;
                            camposPreenchidos++;
                        }
                    }
                    
                    if (camposPreenchidos > 0) {
                        alert(`Dados da empresa preenchidos com sucesso! ${camposPreenchidos} campo(s) preenchido(s).`);
                    } else {
                        alert('Dados recebidos, mas nenhum campo correspondente foi encontrado no formulário.');
                    }
                } else {
                    const errorMsg = result.message || 'CNPJ não encontrado ou erro ao buscar dados. Verifique se o CNPJ está correto.';
                    alert(errorMsg);
                }
            } catch (error) {
                console.error('Erro ao buscar CNPJ:', error);
                alert('Erro ao buscar dados do CNPJ. Tente novamente mais tarde.');
            } finally {
                btnBuscarCnpj.disabled = false;
                btnBuscarCnpj.innerHTML = '<i class="fas fa-search"></i> Buscar';
            }
        });
    }
    
    // Máscara para telefone
    const telefoneInput = document.querySelector('input[name="telefone"]');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 11) {
                value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 7) + '-' + value.substring(7, 11);
            } else if (value.length >= 10) {
                value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 6) + '-' + value.substring(6, 10);
            }
            this.value = value;
        });
    }
    
    // Máscara de moeda (R$ 0,00) para campos de valor
    function aplicarMascaraMoeda(input) {
        if (!input) return;
        
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value === '') {
                e.target.value = '';
                return;
            }
            
            // Converter para número e formatar
            value = (parseInt(value, 10) / 100).toFixed(2) + '';
            value = value.replace('.', ',');
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            
            e.target.value = 'R$ ' + value;
        });
        
        input.addEventListener('blur', function(e) {
            if (e.target.value === '' || e.target.value === 'R$ ') {
                e.target.value = 'R$ 0,00';
            }
        });
        
        input.addEventListener('focus', function(e) {
            if (e.target.value === 'R$ 0,00') {
                e.target.value = '';
            }
        });
    }
    
    // Aplicar máscara em todos os campos de valor
    document.querySelectorAll('input[data-mask="currency"]').forEach(function(input) {
        aplicarMascaraMoeda(input);
    });
    
    // Aplicar máscara quando novos campos de produto forem mostrados
    const productCheckboxesForMask = document.querySelectorAll('input[name="products[]"]');
    productCheckboxesForMask.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            setTimeout(function() {
                document.querySelectorAll('input[data-mask="currency"]').forEach(function(input) {
                    if (!input.hasAttribute('data-masked')) {
                        aplicarMascaraMoeda(input);
                        input.setAttribute('data-masked', 'true');
                    }
                });
            }, 100);
        });
    });
    
    // Gerenciar campos de documentos
    const adicionarDocumentoBtn = document.getElementById('adicionar-documento');
    const documentosContainer = document.getElementById('documentos-container');
    
    if (adicionarDocumentoBtn && documentosContainer) {
        adicionarDocumentoBtn.addEventListener('click', function() {
            const primeiroItem = document.querySelector('.documento-item');
            if (primeiroItem) {
                const novoDocumento = primeiroItem.cloneNode(true);
                novoDocumento.querySelector('select[name="document_type[]"]').value = '';
                novoDocumento.querySelector('input[type="file"]').value = '';
                novoDocumento.querySelector('.remove-documento').classList.remove('hidden');
                documentosContainer.appendChild(novoDocumento);
                
                // Atualizar event listeners para remover
                atualizarEventListenersDocumentos();
            }
        });
    }
    
    function atualizarEventListenersDocumentos() {
        document.querySelectorAll('.remove-documento').forEach(function(btn) {
            btn.removeEventListener('click', removeDocumentoHandler);
            btn.addEventListener('click', removeDocumentoHandler);
        });
    }
    
    function removeDocumentoHandler(e) {
        const items = document.querySelectorAll('.documento-item');
        if (items.length > 1) {
            e.target.closest('.documento-item').remove();
            atualizarEventListenersDocumentos();
        }
    }
    
    atualizarEventListenersDocumentos();
    
    // Validar tipo de documento apenas se arquivo foi selecionado
    document.querySelectorAll('input[name="documents[]"]').forEach(function(fileInput) {
        fileInput.addEventListener('change', function() {
            const documentoItem = this.closest('.documento-item');
            const select = documentoItem.querySelector('select[name="document_type[]"]');
            if (this.files.length > 0) {
                select.setAttribute('required', 'required');
            } else {
                select.removeAttribute('required');
            }
        });
    });
    
    // Feedback visual ao salvar estabelecimento
    const formEstabelecimento = document.querySelector('form');
    const btnSalvar = document.getElementById('btn-salvar-estabelecimento');
    const btnSalvarTexto = document.getElementById('btn-salvar-texto');
    
    if (formEstabelecimento && btnSalvar) {
        formEstabelecimento.addEventListener('submit', function(e) {
            // Validar formulário antes de mostrar mensagem
            if (formEstabelecimento.checkValidity()) {
                // Desabilitar botão e mostrar feedback
                btnSalvar.disabled = true;
                btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Salvando...';
                
                // A mensagem de sucesso será exibida após o redirect no layout
            }
        });
    }
});
</script>

<?php
// Limpar old_input da sessão após renderizar o formulário
if (isset($_SESSION['old_input'])) {
    // Manter apenas por uma requisição, será limpo após o próximo carregamento
    // Isso permite que os dados sejam preservados mesmo após múltiplos erros
}

// Script para restaurar tipo de registro selecionado
$registrationType = old('registration_type');
if ($registrationType): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const registrationType = '<?= htmlspecialchars($registrationType) ?>';
    if (registrationType) {
        const radio = document.querySelector(`input[name="registration_type"][value="${registrationType}"]`);
        if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        }
    }
});
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>