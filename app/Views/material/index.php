<?php
use App\Core\Auth;
$currentPage = 'material';
ob_start();

// Definir variáveis com valores padrão para evitar warnings
$stats = $stats ?? ['total_files' => 0, 'total_categories' => 0, 'total_downloads' => 0, 'total_subcategories' => 0];
$files = $files ?? [];
$categories = $categories ?? [];
$filters = $filters ?? [];
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-download mr-2"></i>
                Material de Apoio
            </h1>
            <p class="text-gray-600 mt-1">Acesse manuais, formulários e materiais de treinamento</p>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <!-- Total de Arquivos -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total de Arquivos</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($stats['total_files']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Categorias -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-folder text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Categorias</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($stats['total_categories']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Downloads -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-cyan-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-download text-cyan-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Downloads</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($stats['total_downloads']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Subcategorias -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-tags text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Subcategorias</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($stats['total_subcategories']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
        <form method="GET" action="<?= url('material') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Pesquisar -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pesquisar</label>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Título ou descrição..." class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Categoria -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                <select name="category_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" 
                            <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Botões -->
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md mr-2">
                    <i class="fas fa-search mr-1"></i>
                    Filtrar
                </button>
                <a href="<?= url('material') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-times mr-1"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Arquivos -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-list mr-2"></i>
                Arquivos Disponíveis
                <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"><?= count($files) ?></span>
            </h3>
        </div>

        <?php if (empty($files)): ?>
            <div class="p-12 text-center">
                <i class="fas fa-file-alt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum arquivo encontrado</h3>
                <p class="text-gray-500 mb-6">
                    <?php if (!empty($filters['search']) || !empty($filters['category_id'])): ?>
                        Tente ajustar os filtros para encontrar o que procura.
                    <?php else: ?>
                        Ainda não há arquivos disponíveis no material de apoio.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($files as $file): ?>
                <div class="px-6 py-4 bg-transparent hover:bg-blue-200 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($file['title']) ?>
                                </h3>
                                <?php if ($file['description']): ?>
                                <span class="text-xs text-gray-500">
                                    <?= htmlspecialchars(substr($file['description'], 0, 80)) ?>
                                    <?= strlen($file['description']) > 80 ? '...' : '' ?>
                                </span>
                                <?php endif; ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-600 text-white">
                                    <?= htmlspecialchars($file['category_name']) ?>
                                </span>
                                <?php if ($file['subcategory_name']): ?>
                                <span class="text-xs text-gray-500">
                                    <?= htmlspecialchars($file['subcategory_name']) ?>
                                </span>
                                <?php endif; ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-600 text-white">
                                    <?= strtoupper($file['file_type']) ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    <?= format_file_size($file['file_size']) ?>
                                </span>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-600 text-white">
                                    <i class="fas fa-download mr-1"></i>
                                    <?= number_format($file['download_count']) ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    <?= format_datetime($file['created_at'], 'd/m/Y') ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 flex-shrink-0">
                            <a href="<?= url('material/download/' . $file['id']) ?>" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md inline-flex items-center" 
                               title="Download">
                                <i class="fas fa-download mr-1"></i>
                                Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Links de Administração (apenas para admins) -->
    <?php if (Auth::isAdmin()): ?>
    <div class="mt-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-cog mr-2"></i>
                Administração
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="<?= url('material/categories') ?>" class="bg-white border border-blue-600 text-blue-600 hover:bg-blue-50 px-4 py-3 rounded-md text-center transition-colors">
                    <i class="fas fa-folder mb-2 block text-xl"></i>
                    <span class="text-sm font-medium">Categorias</span>
                </a>
                <a href="<?= url('material/subcategories') ?>" class="bg-white border border-cyan-600 text-cyan-600 hover:bg-cyan-50 px-4 py-3 rounded-md text-center transition-colors">
                    <i class="fas fa-tags mb-2 block text-xl"></i>
                    <span class="text-sm font-medium">Subcategorias</span>
                </a>
                <a href="<?= url('material/files') ?>" class="bg-white border border-green-600 text-green-600 hover:bg-green-50 px-4 py-3 rounded-md text-center transition-colors">
                    <i class="fas fa-file-alt mb-2 block text-xl"></i>
                    <span class="text-sm font-medium">Arquivos</span>
                </a>
                <a href="<?= url('material/files/create') ?>" class="bg-white border border-yellow-600 text-yellow-600 hover:bg-yellow-50 px-4 py-3 rounded-md text-center transition-colors">
                    <i class="fas fa-plus mb-2 block text-xl"></i>
                    <span class="text-sm font-medium">Novo Arquivo</span>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
