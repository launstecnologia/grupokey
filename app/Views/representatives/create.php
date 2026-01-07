<?php
$currentPage = 'representantes';
ob_start();
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-user-plus mr-2"></i>
                Novo Representante
            </h1>
            <p class="text-gray-600 mt-1">Cadastre um novo representante no sistema</p>
        </div>
        <div>
            <a href="<?= url('representantes') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
        </div>
    </div>

    <!-- Formulário -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-user mr-2"></i>
                Dados do Representante
            </h3>
        </div>
        <form method="POST" action="<?= url('representantes') ?>" class="p-6">
            <?= csrf_field() ?>
            
            <!-- Mensagens de erro -->
            <?php if (isset($_SESSION['validation_errors'])): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Erros encontrados:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <?php foreach ($_SESSION['validation_errors'] as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['validation_errors']); ?>
            <?php endif; ?>

            <!-- Dados Pessoais -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-id-card mr-2 text-blue-600"></i>
                    Dados Pessoais
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome Completo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                        <input type="text" name="nome_completo" required 
                               value="<?= htmlspecialchars($_POST['nome_completo'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o nome completo">
                    </div>
                    <!-- CPF -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CPF *</label>
                        <input type="text" name="cpf" required 
                               value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="000.000.000-00">
                    </div>
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o email">
                    </div>
                    <!-- Telefone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                        <input type="text" name="telefone" required 
                               value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="(11) 99999-9999">
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
                            <input type="text" name="cep" id="cep" required 
                                   value="<?= htmlspecialchars($_POST['cep'] ?? '') ?>"
                                   class="mt-1 block flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="00000-000">
                            <button type="button" id="btn-buscar-cep" class="mt-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                        <small id="cep-help" class="text-gray-500">Digite o CEP e clique em Buscar para preencher automaticamente</small>
                    </div>
                    <!-- Logradouro -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro *</label>
                        <input type="text" name="logradouro" required 
                               value="<?= htmlspecialchars($_POST['logradouro'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nome da rua/avenida">
                    </div>
                    <!-- Número -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número *</label>
                        <input type="text" name="numero" required 
                               value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="123">
                    </div>
                    <!-- Complemento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                        <input type="text" name="complemento" 
                               value="<?= htmlspecialchars($_POST['complemento'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Apto, sala, etc.">
                    </div>
                    <!-- Bairro -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bairro *</label>
                        <input type="text" name="bairro" required 
                               value="<?= htmlspecialchars($_POST['bairro'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nome do bairro">
                    </div>
                    <!-- Cidade -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cidade *</label>
                        <input type="text" name="cidade" required 
                               value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nome da cidade">
                    </div>
                    <!-- UF -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">UF *</label>
                        <select name="uf" required 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione</option>
                            <option value="AC" <?= ($_POST['uf'] ?? '') === 'AC' ? 'selected' : '' ?>>AC</option>
                            <option value="AL" <?= ($_POST['uf'] ?? '') === 'AL' ? 'selected' : '' ?>>AL</option>
                            <option value="AP" <?= ($_POST['uf'] ?? '') === 'AP' ? 'selected' : '' ?>>AP</option>
                            <option value="AM" <?= ($_POST['uf'] ?? '') === 'AM' ? 'selected' : '' ?>>AM</option>
                            <option value="BA" <?= ($_POST['uf'] ?? '') === 'BA' ? 'selected' : '' ?>>BA</option>
                            <option value="CE" <?= ($_POST['uf'] ?? '') === 'CE' ? 'selected' : '' ?>>CE</option>
                            <option value="DF" <?= ($_POST['uf'] ?? '') === 'DF' ? 'selected' : '' ?>>DF</option>
                            <option value="ES" <?= ($_POST['uf'] ?? '') === 'ES' ? 'selected' : '' ?>>ES</option>
                            <option value="GO" <?= ($_POST['uf'] ?? '') === 'GO' ? 'selected' : '' ?>>GO</option>
                            <option value="MA" <?= ($_POST['uf'] ?? '') === 'MA' ? 'selected' : '' ?>>MA</option>
                            <option value="MT" <?= ($_POST['uf'] ?? '') === 'MT' ? 'selected' : '' ?>>MT</option>
                            <option value="MS" <?= ($_POST['uf'] ?? '') === 'MS' ? 'selected' : '' ?>>MS</option>
                            <option value="MG" <?= ($_POST['uf'] ?? '') === 'MG' ? 'selected' : '' ?>>MG</option>
                            <option value="PA" <?= ($_POST['uf'] ?? '') === 'PA' ? 'selected' : '' ?>>PA</option>
                            <option value="PB" <?= ($_POST['uf'] ?? '') === 'PB' ? 'selected' : '' ?>>PB</option>
                            <option value="PR" <?= ($_POST['uf'] ?? '') === 'PR' ? 'selected' : '' ?>>PR</option>
                            <option value="PE" <?= ($_POST['uf'] ?? '') === 'PE' ? 'selected' : '' ?>>PE</option>
                            <option value="PI" <?= ($_POST['uf'] ?? '') === 'PI' ? 'selected' : '' ?>>PI</option>
                            <option value="RJ" <?= ($_POST['uf'] ?? '') === 'RJ' ? 'selected' : '' ?>>RJ</option>
                            <option value="RN" <?= ($_POST['uf'] ?? '') === 'RN' ? 'selected' : '' ?>>RN</option>
                            <option value="RS" <?= ($_POST['uf'] ?? '') === 'RS' ? 'selected' : '' ?>>RS</option>
                            <option value="RO" <?= ($_POST['uf'] ?? '') === 'RO' ? 'selected' : '' ?>>RO</option>
                            <option value="RR" <?= ($_POST['uf'] ?? '') === 'RR' ? 'selected' : '' ?>>RR</option>
                            <option value="SC" <?= ($_POST['uf'] ?? '') === 'SC' ? 'selected' : '' ?>>SC</option>
                            <option value="SP" <?= ($_POST['uf'] ?? '') === 'SP' ? 'selected' : '' ?>>SP</option>
                            <option value="SE" <?= ($_POST['uf'] ?? '') === 'SE' ? 'selected' : '' ?>>SE</option>
                            <option value="TO" <?= ($_POST['uf'] ?? '') === 'TO' ? 'selected' : '' ?>>TO</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Produtos Permitidos -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-box mr-2 text-blue-600"></i>
                    Produtos Permitidos
                </h4>
                <p class="text-sm text-gray-600 mb-4">Selecione quais produtos este representante poderá cadastrar nos estabelecimentos:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php 
                    // Mapeamento de nomes de produtos (mesmo do estabelecimento)
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
                        'PagBank' => 'PagBank',
                        'PAGBANK' => 'PagBank',
                        'pagbank' => 'PagBank',
                        'Conteúdo Digital' => null, // Remover
                        'CONTEUDO_DIGITAL' => null, // Remover
                        'Conteudo Digital' => null, // Remover
                        'Flamex' => null, // Remover
                        'FLAMEX' => null, // Remover
                        'Griva' => null, // Remover
                        'GRIVA' => null, // Remover
                    ];
                    
                    $selectedProducts = $_POST['allowed_products'] ?? [];
                    
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
                        } elseif (stripos($productId, 'PAGSEGURO_MP') !== false || stripos($productName, 'PagSeguro/MP') !== false || stripos($productName, 'MercadoPago') !== false || stripos($productName, 'Mercado Pago') !== false) {
                            $displayName = 'CDX/EVO';
                        } elseif (stripos($productNameUpper, 'PAGBANK') !== false || stripos($productId, 'PAGBANK') !== false) {
                            $displayName = 'PagBank';
                        }
                        
                        // Mapear para valor do ENUM do banco
                        $enumValue = 'OUTROS';
                        if (stripos($productId, 'BRASILCARD') !== false || stripos($productId, 'PROD-BRASIL-CARD') !== false || $displayName === 'CDC') {
                            $enumValue = 'CDC';
                        } elseif (stripos($productId, 'PAGSEGURO_MP') !== false || stripos($productId, 'CDX') !== false || stripos($productId, 'EVO') !== false || $displayName === 'CDX/EVO') {
                            $enumValue = 'CDX_EVO';
                        } elseif (stripos($productId, 'PAGSEGURO') !== false || $displayName === 'PagSeguro') {
                            $enumValue = 'PAGSEGURO';
                        } elseif (stripos($productNameUpper, 'GOOGLE') !== false) {
                            $enumValue = 'GOOGLE';
                        } elseif (stripos($productNameUpper, 'MEMBRO') !== false) {
                            $enumValue = 'MEMBRO_KEY';
                        } elseif (stripos($productNameUpper, 'PAGBANK') !== false || stripos($productId, 'PAGBANK') !== false || $displayName === 'PagBank') {
                            $enumValue = 'PAGBANK';
                        } elseif (stripos($productNameUpper, 'DIVERSOS') !== false) {
                            $enumValue = 'DIVERSOS';
                        } elseif (stripos($productNameUpper, 'UCREDIT') !== false || stripos($productNameUpper, 'UCRED') !== false) {
                            $enumValue = 'UCREDIT';
                        } elseif (stripos($productNameUpper, 'FGTS') !== false) {
                            $enumValue = 'FGTS';
                        }
                    ?>
                    <label class="flex items-center p-3 border border-gray-300 rounded-md cursor-pointer hover:bg-blue-600 hover:text-white transition-colors">
                        <input type="checkbox" 
                               name="allowed_products[]" 
                               value="<?= $enumValue ?>" 
                               id="product_<?= $enumValue ?>"
                               <?= in_array($enumValue, $selectedProducts) ? 'checked' : '' ?>
                               class="mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="text-gray-700 hover:text-white"><?= htmlspecialchars($displayName) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <p class="mt-2 text-xs text-gray-500">Se nenhum produto for selecionado, o representante poderá cadastrar todos os produtos.</p>
            </div>

            <!-- Acesso ao Sistema -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-lock mr-2 text-blue-600"></i>
                    Acesso ao Sistema
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Senha -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
                        <input type="password" name="senha" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite a senha">
                        <p class="mt-1 text-sm text-gray-500">Mínimo de 6 caracteres</p>
                    </div>
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="ACTIVE" <?= ($_POST['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'selected' : '' ?>>Ativo</option>
                            <option value="INACTIVE" <?= ($_POST['status'] ?? '') === 'INACTIVE' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                    Informações Importantes
                </h4>
                <div class="bg-gray-900 border border-gray-700 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lightbulb text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-white">Dicas Importantes</h3>
                            <div class="mt-2 text-sm text-gray-300">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>O email será usado para login no sistema</li>
                                    <li>A senha será enviada por email</li>
                                    <li>Representantes ativos podem fazer login</li>
                                    <li>Use senhas seguras com pelo menos 6 caracteres</li>
                                    <li>Verifique todos os dados antes de salvar</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-4">
                <a href="<?= url('representantes') ?>" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Cadastrar Representante
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnBuscarCep = document.getElementById('btn-buscar-cep');
    const cepInput = document.getElementById('cep');
    const cepHelp = document.getElementById('cep-help');
    
    // Máscara para CEP
    if (cepInput) {
        cepInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 5) {
                value = value.substring(0, 5) + '-' + value.substring(5, 8);
            }
            this.value = value;
        });
    }
    
    // Busca de CEP via ViaCEP
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
                if (e.key === 'Enter') {
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
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>