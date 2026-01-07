<?php
$currentPage = 'email-marketing';
ob_start();

$campaign = $campaign ?? [];
$recipients = $recipients ?? [];
$establishments = $establishments ?? [];
$representatives = $representatives ?? [];
$users = $users ?? [];
$states = $states ?? [];
$cities = $cities ?? [];
?>

<div class="pt-6 px-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-users mr-2"></i>
                Selecionar Destinatários
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Adicione destinatários para a campanha: <?= htmlspecialchars($campaign['name'] ?? '') ?></p>
        </div>

        <!-- Mensagens -->
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

        <form method="POST" action="<?= url('email-marketing/' . $campaign['id'] . '/recipients') ?>" id="recipientsForm">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Formulário de Adição -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Estabelecimentos -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Estabelecimentos</h2>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" id="selectAllEstablishments" class="mr-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Selecionar Todos</span>
                                </label>
                                <span class="text-sm text-gray-500 dark:text-gray-400" id="selectedCount">0 selecionados</span>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Filtrar por Estado
                                </label>
                                <select id="filterState" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="">Todos os Estados</option>
                                    <?php foreach ($states as $state): ?>
                                        <option value="<?= htmlspecialchars($state) ?>"><?= htmlspecialchars($state) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Filtrar por Cidade
                                </label>
                                <select id="filterCity" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="">Todas as Cidades</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Lista de Estabelecimentos com Checkboxes -->
                        <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 max-h-96 overflow-y-auto bg-gray-50 dark:bg-gray-900">
                            <div id="establishmentsList" class="space-y-2">
                                <?php foreach ($establishments as $est): ?>
                                    <?php if (!empty($est['email'])): ?>
                                        <div class="establishment-item flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded" 
                                             data-state="<?= htmlspecialchars($est['uf'] ?? '') ?>" 
                                             data-city="<?= htmlspecialchars($est['cidade'] ?? '') ?>">
                                            <input type="checkbox" 
                                                   name="establishment_ids[]" 
                                                   value="<?= $est['id'] ?>" 
                                                   class="establishment-checkbox mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    <?= htmlspecialchars($est['nome_fantasia'] ?? $est['nome_completo']) ?>
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    <?= htmlspecialchars($est['email']) ?>
                                                    <?php if (!empty($est['cidade']) || !empty($est['uf'])): ?>
                                                        - <?= htmlspecialchars($est['cidade'] ?? '') ?><?= !empty($est['cidade']) && !empty($est['uf']) ? '/' : '' ?><?= htmlspecialchars($est['uf'] ?? '') ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <div id="noResults" class="hidden text-center py-8 text-gray-500 dark:text-gray-400">
                                <i class="fas fa-search text-3xl mb-2"></i>
                                <p>Nenhum estabelecimento encontrado com os filtros selecionados</p>
                            </div>
                        </div>
                    </div>

                    <!-- Representantes -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Representantes</h2>
                        <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 max-h-64 overflow-y-auto bg-gray-50 dark:bg-gray-900">
                            <div class="space-y-2">
                                <?php foreach ($representatives as $rep): ?>
                                    <?php if (!empty($rep['email'])): ?>
                                        <div class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded">
                                            <input type="checkbox" 
                                                   name="representative_ids[]" 
                                                   value="<?= $rep['id'] ?>" 
                                                   class="mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    <?= htmlspecialchars($rep['nome_completo']) ?>
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    <?= htmlspecialchars($rep['email']) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Usuários -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Usuários Administradores</h2>
                        <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 max-h-64 overflow-y-auto bg-gray-50 dark:bg-gray-900">
                            <div class="space-y-2">
                                <?php foreach ($users as $user): ?>
                                    <?php if (!empty($user['email'])): ?>
                                        <div class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded">
                                            <input type="checkbox" 
                                                   name="user_ids[]" 
                                                   value="<?= $user['id'] ?>" 
                                                   class="mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    <?= htmlspecialchars($user['name']) ?>
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    <?= htmlspecialchars($user['email']) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- E-mails Customizados -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">E-mails Customizados</h2>
                        <textarea name="custom_emails" rows="5"
                                  placeholder="Exemplo:&#10;contato@exemplo.com&#10;João Silva &lt;joao@exemplo.com&gt;"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Formato: email@exemplo.com ou "Nome &lt;email@exemplo.com&gt;" (um por linha)
                        </p>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Adicionar Destinatários Selecionados
                    </button>
                </div>

                <!-- Lista de Destinatários -->
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 sticky top-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Destinatários (<?= count($recipients) ?>)
                        </h2>
                        
                        <?php if (empty($recipients)): ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                Nenhum destinatário adicionado ainda
                            </p>
                        <?php else: ?>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                <?php foreach ($recipients as $recipient): ?>
                                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                <?= htmlspecialchars($recipient['name'] ?? $recipient['email']) ?>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                <?= htmlspecialchars($recipient['email']) ?>
                                            </p>
                                            <span class="text-xs px-2 py-1 rounded <?= 
                                                $recipient['status'] === 'SENT' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
                                                ($recipient['status'] === 'FAILED' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' :
                                                'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300')
                                            ?>">
                                                <?= 
                                                    $recipient['status'] === 'SENT' ? 'Enviado' :
                                                    ($recipient['status'] === 'FAILED' ? 'Falhou' : 'Pendente')
                                                ?>
                                            </span>
                                        </div>
                                        <?php if ($recipient['status'] === 'PENDING'): ?>
                                            <a href="<?= url('email-marketing/' . $campaign['id'] . '/recipients/' . $recipient['id'] . '/remove') ?>"
                                               class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 ml-2"
                                               onclick="return confirm('Deseja remover este destinatário?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="<?= url('email-marketing/' . $campaign['id']) ?>" class="block w-full text-center bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Voltar para Campanha
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterState = document.getElementById('filterState');
    const filterCity = document.getElementById('filterCity');
    const selectAllCheckbox = document.getElementById('selectAllEstablishments');
    const establishmentCheckboxes = document.querySelectorAll('.establishment-checkbox');
    const establishmentItems = document.querySelectorAll('.establishment-item');
    const selectedCountSpan = document.getElementById('selectedCount');
    const noResults = document.getElementById('noResults');

    // Função para atualizar contador
    function updateSelectedCount() {
        const visibleCheckboxes = Array.from(establishmentCheckboxes).filter(cb => {
            const item = cb.closest('.establishment-item');
            return item && !item.classList.contains('hidden');
        });
        const selected = visibleCheckboxes.filter(cb => cb.checked).length;
        selectedCountSpan.textContent = selected + ' selecionados';
    }

    // Função para filtrar estabelecimentos
    function filterEstablishments() {
        const selectedState = filterState.value;
        const selectedCity = filterCity.value;
        let visibleCount = 0;

        establishmentItems.forEach(item => {
            const state = item.getAttribute('data-state');
            const city = item.getAttribute('data-city');
            
            const matchState = !selectedState || state === selectedState;
            const matchCity = !selectedCity || city === selectedCity;
            
            if (matchState && matchCity) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });

        // Mostrar/ocultar mensagem de "sem resultados"
        if (visibleCount === 0) {
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
        }

        // Atualizar estado do "selecionar todos"
        updateSelectAllState();
        updateSelectedCount();
    }

    // Função para atualizar estado do checkbox "Selecionar Todos"
    function updateSelectAllState() {
        const visibleCheckboxes = Array.from(establishmentCheckboxes).filter(cb => {
            const item = cb.closest('.establishment-item');
            return item && !item.classList.contains('hidden');
        });
        
        if (visibleCheckboxes.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
            return;
        }

        const allChecked = visibleCheckboxes.every(cb => cb.checked);
        const someChecked = visibleCheckboxes.some(cb => cb.checked);

        if (allChecked) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (someChecked) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    // Selecionar todos os estabelecimentos visíveis
    selectAllCheckbox.addEventListener('change', function() {
        const visibleCheckboxes = Array.from(establishmentCheckboxes).filter(cb => {
            const item = cb.closest('.establishment-item');
            return item && !item.classList.contains('hidden');
        });
        
        visibleCheckboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
        });
        updateSelectedCount();
    });

    // Atualizar contador quando checkbox individual mudar
    establishmentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            updateSelectAllState();
        });
    });

    // Filtrar quando estado ou cidade mudar
    filterState.addEventListener('change', filterEstablishments);
    filterCity.addEventListener('change', filterEstablishments);

    // Atualizar cidades quando estado mudar
    filterState.addEventListener('change', function() {
        const selectedState = filterState.value;
        const citySelect = filterCity;
        const currentCity = citySelect.value;
        
        // Limpar opções de cidade
        citySelect.innerHTML = '<option value="">Todas as Cidades</option>';
        
        if (selectedState) {
            // Adicionar apenas cidades do estado selecionado
            const citiesInState = new Set();
            establishmentItems.forEach(item => {
                const state = item.getAttribute('data-state');
                const city = item.getAttribute('data-city');
                if (state === selectedState && city) {
                    citiesInState.add(city);
                }
            });
            
            Array.from(citiesInState).sort().forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                if (city === currentCity) {
                    option.selected = true;
                }
                citySelect.appendChild(option);
            });
        } else {
            // Mostrar todas as cidades
            <?php foreach ($cities as $city): ?>
                const option = document.createElement('option');
                option.value = '<?= htmlspecialchars($city) ?>';
                option.textContent = '<?= htmlspecialchars($city) ?>';
                if (option.value === currentCity) {
                    option.selected = true;
                }
                citySelect.appendChild(option);
            <?php endforeach; ?>
        }
        
        filterEstablishments();
    });

    // Inicializar
    updateSelectedCount();
    updateSelectAllState();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
