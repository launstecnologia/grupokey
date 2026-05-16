<?php
use App\Core\Auth;
$currentPage = 'material';
ob_start();

// Definir variáveis com valores padrão para evitar warnings
$stats = $stats ?? ['total_files' => 0, 'total_categories' => 0, 'total_downloads' => 0, 'total_subcategories' => 0];
$files = $files ?? [];
$productOptions = $productOptions ?? [];
$filters = $filters ?? [];
$readMap = $readMap ?? [];
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
        <?php if (Auth::isAdmin()): ?>
        <div>
            <a href="<?= url('material/files/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md inline-flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Novo Arquivo
            </a>
        </div>
        <?php endif; ?>
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

            <!-- Produto -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Produto</label>
                <select name="product" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos os produtos</option>
                    <?php foreach ($productOptions as $productValue => $productLabel): ?>
                    <option value="<?= htmlspecialchars($productValue) ?>" 
                            <?= ($filters['product'] ?? '') === $productValue ? 'selected' : '' ?>>
                        <?= htmlspecialchars($productLabel) ?>
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
                    <?php if (!empty($filters['search']) || !empty($filters['product'])): ?>
                        Tente ajustar os filtros para encontrar o que procura.
                    <?php else: ?>
                        Ainda não há arquivos disponíveis no material de apoio.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <?php foreach ($files as $file): ?>
                <?php $isImage = stripos((string) ($file['mime_type'] ?? ''), 'image/') === 0; ?>
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
                    <?php if ($isImage): ?>
                    <a href="<?= url('material/preview/' . $file['id']) ?>" target="_blank" title="Ver imagem">
                        <img src="<?= url('material/preview/' . $file['id']) ?>" alt="Miniatura" class="w-full h-48 object-cover">
                    </a>
                    <?php else: ?>
                    <div class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-500">
                        <i class="fas fa-file-alt text-4xl"></i>
                    </div>
                    <?php endif; ?>

                    <div class="p-4">
                        <h3 class="text-base font-semibold text-gray-900 mb-2"><?= htmlspecialchars($file['title']) ?></h3>
                        <?php if ($file['description']): ?>
                        <p class="text-sm text-gray-600 mb-3">
                            <?= htmlspecialchars(substr($file['description'], 0, 90)) ?><?= strlen($file['description']) > 90 ? '...' : '' ?>
                        </p>
                        <?php endif; ?>

                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-600 text-white"><?= htmlspecialchars($file['category_name']) ?></span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-600 text-white"><?= strtoupper($file['file_type']) ?></span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-600 text-white"><i class="fas fa-download mr-1"></i><?= number_format($file['download_count']) ?></span>
                            <span class="text-xs text-gray-500 self-center"><?= format_file_size($file['file_size']) ?></span>
                        </div>

                        <?php if (Auth::isRepresentative()): ?>
                        <a href="<?= url('material/download/' . $file['id']) ?>" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md inline-flex items-center justify-center" title="Download">
                            <i class="fas fa-download mr-2"></i>Download
                        </a>
                        <?php else: ?>
                        <div class="flex items-center gap-2">
                            <a href="<?= url('material/download/' . $file['id']) ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md inline-flex items-center justify-center" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="<?= url('material/files/' . $file['id'] . '/edit') ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-md inline-flex items-center justify-center" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= url('material/files/' . $file['id']) ?>" onsubmit="return confirm('Deseja excluir este arquivo?');" class="inline">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md inline-flex items-center justify-center" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
