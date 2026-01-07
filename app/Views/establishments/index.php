<?php
$currentPage = 'estabelecimentos';
ob_start();

// Definir variáveis com valores padrão para evitar warnings
$stats = $stats ?? ['total' => 0, 'aprovados' => 0, 'pendentes' => 0, 'reprovados' => 0, 'desabilitados' => 0, 'cadastros_ultimo_mes' => 0];
$establishments = $establishments ?? [];
$representatives = $representatives ?? [];
?>

<div class="pt-6 px-4">
    <!-- Header com ações -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-building mr-2"></i>
                Estabelecimentos
            </h1>
            <p class="text-gray-600 mt-1">Gerencie todos os estabelecimentos cadastrados</p>
        </div>
        <div class="flex gap-3">
            <?php if (App\Core\Auth::isAdmin()): ?>
            <a href="<?= url('estabelecimentos/import') ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-file-upload mr-2"></i>
                Importar CSV
            </a>
            <?php endif; ?>
            <?php if (App\Core\Auth::isAdmin() || App\Core\Auth::isRepresentative()): ?>
            <a href="<?= url('estabelecimentos/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Novo Estabelecimento
            </a>
            <?php endif; ?>
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

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <!-- Total -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-building text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['total'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Aprovados -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Aprovados</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['aprovados'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Pendentes -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Pendentes</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['pendentes'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Último Mês -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-calendar text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Último Mês</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $stats['cadastros_ultimo_mes'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filtros</h3>
        <form method="GET" action="<?= url('estabelecimentos') ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select name="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos os status</option>
                    <option value="PENDING" <?= ($filters['status'] ?? '') === 'PENDING' ? 'selected' : '' ?>>Pendente</option>
                    <option value="APPROVED" <?= ($filters['status'] ?? '') === 'APPROVED' ? 'selected' : '' ?>>Aprovado</option>
                    <option value="REPROVED" <?= ($filters['status'] ?? '') === 'REPROVED' ? 'selected' : '' ?>>Reprovado</option>
                    <option value="DISABLED" <?= ($filters['status'] ?? '') === 'DISABLED' ? 'selected' : '' ?>>Desabilitado</option>
                </select>
            </div>

            <!-- Produto -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Produto</label>
                <select name="produto" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos os produtos</option>
                    <option value="CDX_EVO" <?= ($filters['produto'] ?? '') === 'CDX_EVO' ? 'selected' : '' ?>>CDX/EVO</option>
                    <option value="CDC" <?= ($filters['produto'] ?? '') === 'CDC' ? 'selected' : '' ?>>CDC</option>
                    <option value="GOOGLE" <?= ($filters['produto'] ?? '') === 'GOOGLE' ? 'selected' : '' ?>>Google</option>
                    <option value="MEMBRO_KEY" <?= ($filters['produto'] ?? '') === 'MEMBRO_KEY' ? 'selected' : '' ?>>Membro Key</option>
                    <option value="PAGBANK" <?= ($filters['produto'] ?? '') === 'PAGBANK' ? 'selected' : '' ?>>PagBank</option>
                    <option value="OUTROS" <?= ($filters['produto'] ?? '') === 'OUTROS' ? 'selected' : '' ?>>Outros</option>
                </select>
            </div>

            <!-- Cidade -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cidade</label>
                <input type="text" name="cidade" value="<?= htmlspecialchars($filters['cidade'] ?? '') ?>" placeholder="Digite a cidade" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Representante -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Representante</label>
                <select name="representative_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos os representantes</option>
                    <?php foreach ($representatives as $representative): ?>
                    <option value="<?= $representative['id'] ?>" <?= ($filters['representative_id'] ?? '') == $representative['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($representative['nome_completo']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- CPF -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CPF</label>
                <input type="text" name="cpf" value="<?= htmlspecialchars($filters['cpf'] ?? '') ?>" placeholder="Digite o CPF" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- CNPJ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CNPJ</label>
                <input type="text" name="cnpj" value="<?= htmlspecialchars($filters['cnpj'] ?? '') ?>" placeholder="Digite o CNPJ" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Razão Social -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Razão Social</label>
                <input type="text" name="razao_social" value="<?= htmlspecialchars($filters['razao_social'] ?? '') ?>" placeholder="Digite a razão social" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Nome -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome / Nome Fantasia</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($filters['nome'] ?? '') ?>" placeholder="Digite o nome" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Botões -->
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md mr-2">
                    <i class="fas fa-search mr-1"></i>
                    Filtrar
                </button>
                <a href="<?= url('estabelecimentos') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-times mr-1"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Estabelecimentos -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-list mr-2"></i>
                Lista de Estabelecimentos
            </h3>
        </div>

        <?php if (empty($establishments)): ?>
            <div class="p-12 text-center">
                <i class="fas fa-building text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum estabelecimento encontrado</h3>
                <p class="text-gray-500 mb-6">Tente ajustar os filtros ou cadastre um novo estabelecimento.</p>
                <a href="<?= url('estabelecimentos/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg inline-flex items-center transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Cadastrar Estabelecimento
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estabelecimento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Representante</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($establishments as $establishment): ?>
                        <tr class="bg-white hover:bg-blue-200 transition-colors cursor-pointer">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($establishment['nome_fantasia']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($establishment['nome_completo']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?= htmlspecialchars($establishment['cidade']) ?>/<?= htmlspecialchars($establishment['uf']) ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php 
                                // Coletar todos os produtos - usar apenas produtos_adicionais que vêm das tabelas reais
                                $produtos = [];
                                
                                // Usar apenas produtos_adicionais que vêm das tabelas de produtos (mais confiável)
                                if (!empty($establishment['produtos_adicionais'])) {
                                    $produtosAdicionais = explode(',', $establishment['produtos_adicionais']);
                                    foreach ($produtosAdicionais as $prod) {
                                        $prod = trim($prod);
                                        // Só adicionar produtos válidos que realmente existem nas tabelas
                                        $produtosValidos = ['CDX/EVO', 'CDC', 'Google', 'Membro Key', 'PagBank', 'Outros'];
                                        if (!empty($prod) && in_array($prod, $produtosValidos) && !in_array($prod, $produtos)) {
                                            $produtos[] = $prod;
                                        }
                                    }
                                }
                                
                                // Se não tiver produtos_adicionais, verificar o campo produto (apenas se for válido)
                                if (empty($produtos) && !empty($establishment['produto'])) {
                                    $produtoEnum = $establishment['produto'];
                                    // Mapear ENUM para nome legível (apenas valores válidos)
                                    $produtoMap = [
                                        'CDX_EVO' => 'CDX/EVO',
                                        'PAGSEGURO_MP' => 'CDX/EVO', // Valor antigo
                                        'CDC' => 'CDC',
                                        'BRASILCARD' => 'CDC', // Valor antigo
                                        'GOOGLE' => 'Google',
                                        'MEMBRO_KEY' => 'Membro Key',
                                        'PAGBANK' => 'PagBank',
                                        'OUTROS' => 'Outros',
                                    ];
                                    $produtoNome = $produtoMap[$produtoEnum] ?? null;
                                    // Só adicionar se for um produto válido e não for DIVERSOS (valor antigo inválido)
                                    if ($produtoNome && $produtoEnum !== 'DIVERSOS' && !in_array($produtoNome, $produtos)) {
                                        $produtos[] = $produtoNome;
                                    }
                                }
                                
                                if (!empty($produtos)): ?>
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($produtos as $produto): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <?= htmlspecialchars($produto) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Sem produto
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($establishment['created_by_user_id'])): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        Administrador
                                    </span>
                                <?php elseif (!empty($establishment['created_by_representative_name'])): ?>
                                    <span class="text-sm text-gray-900">
                                        <?= htmlspecialchars($establishment['created_by_representative_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">
                                        N/A
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'PENDING' => 'bg-yellow-100 text-yellow-800',
                                    'APPROVED' => 'bg-green-100 text-green-800',
                                    'REPROVED' => 'bg-red-100 text-red-800',
                                    'DISABLED' => 'bg-gray-100 text-gray-800'
                                ];
                                $statusLabels = [
                                    'PENDING' => 'Pendente',
                                    'APPROVED' => 'Aprovado',
                                    'REPROVED' => 'Reprovado',
                                    'DISABLED' => 'Desabilitado'
                                ];
                                $statusClass = $statusColors[$establishment['status']] ?? 'bg-gray-100 text-gray-800';
                                $statusLabel = $statusLabels[$establishment['status']] ?? $establishment['status'];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('d/m/Y', strtotime($establishment['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="<?= url('estabelecimentos/' . $establishment['id']) ?>" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (App\Core\Auth::isAdmin() || 
                                        (App\Core\Auth::isRepresentative() && $establishment['created_by_representative_id'] == App\Core\Auth::representative()['id'] && $establishment['status'] !== 'APPROVED')): ?>
                                    <a href="<?= url('estabelecimentos/' . $establishment['id'] . '/edit') ?>" class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (App\Core\Auth::isAdmin()): ?>
                                    <a href="<?= url('estabelecimentos/' . $establishment['id']) ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza que deseja excluir este estabelecimento?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Mostrando <span class="font-medium"><?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?></span>
                    até <span class="font-medium"><?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']) ?></span>
                    de <span class="font-medium"><?= $pagination['total_records'] ?></span> resultados
                </div>
                <div class="flex space-x-2">
                    <?php
                    $queryParams = $_GET;
                    $baseUrl = url('estabelecimentos');
                    
                    // Página anterior
                    if ($pagination['current_page'] > 1):
                        $queryParams['page'] = $pagination['current_page'] - 1;
                        $prevUrl = $baseUrl . '?' . http_build_query($queryParams);
                    ?>
                    <a href="<?= $prevUrl ?>" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <i class="fas fa-chevron-left mr-1"></i>
                        Anterior
                    </a>
                    <?php else: ?>
                    <span class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-800 cursor-not-allowed">
                        <i class="fas fa-chevron-left mr-1"></i>
                        Anterior
                    </span>
                    <?php endif; ?>
                    
                    <!-- Números das páginas -->
                    <?php
                    $startPage = max(1, $pagination['current_page'] - 2);
                    $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                        $queryParams['page'] = $i;
                        $pageUrl = $baseUrl . '?' . http_build_query($queryParams);
                    ?>
                    <a href="<?= $pageUrl ?>" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium <?= $i == $pagination['current_page'] ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                    
                    <!-- Próxima página -->
                    <?php if ($pagination['current_page'] < $pagination['total_pages']):
                        $queryParams['page'] = $pagination['current_page'] + 1;
                        $nextUrl = $baseUrl . '?' . http_build_query($queryParams);
                    ?>
                    <a href="<?= $nextUrl ?>" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Próxima
                        <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                    <?php else: ?>
                    <span class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-800 cursor-not-allowed">
                        Próxima
                        <i class="fas fa-chevron-right ml-1"></i>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
