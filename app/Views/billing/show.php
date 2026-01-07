<?php
$currentPage = 'billing';

// Definir variáveis com valores padrão
$report = $report ?? [];
$billing_data = $billing_data ?? [];
$unlinked_data = $unlinked_data ?? [];

// Calcular contadores
$linkedCount = 0;
$unlinkedCount = count($unlinked_data);

foreach ($billing_data as $data) {
    if ($data['establishment_id']) {
        $linkedCount++;
    }
}

// Status badges
$statusColors = [
    'PROCESSING' => 'bg-yellow-100 text-yellow-800',
    'COMPLETED' => 'bg-green-100 text-green-800',
    'ERROR' => 'bg-red-100 text-red-800'
];
$statusLabels = [
    'PROCESSING' => 'Processando',
    'COMPLETED' => 'Concluído',
    'ERROR' => 'Erro'
];
$status = $report['status'] ?? 'PROCESSING';
$statusClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
$statusLabel = $statusLabels[$status] ?? $status;
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-file-excel mr-2"></i>
                Detalhes do Relatório
            </h1>
            <p class="text-gray-600 mt-1">Informações completas do relatório de faturamento</p>
            <div class="mt-2">
                <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full <?= $statusClass ?>">
                    <?= $statusLabel ?>
                </span>
            </div>
        </div>
        <div class="flex space-x-3">
            <?php if ($report['status'] === 'COMPLETED'): ?>
                <a href="<?= url('billing/' . $report['id'] . '/export') ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Exportar Excel
                </a>
            <?php endif; ?>
            <a href="<?= url('billing') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Informações do Relatório -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informações do Relatório
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Título</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($report['title'] ?? '') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Empresa</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($report['company_code'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total de Registros</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= number_format($report['total_records']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">TPV Total</dt>
                            <dd class="mt-1 text-sm text-gray-900">R$ <?= number_format($report['total_tpv'], 2, ',', '.') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Markup Total</dt>
                            <dd class="mt-1 text-sm text-gray-900">R$ <?= number_format($report['total_markup'], 2, ',', '.') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Upload</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= date('d/m/Y H:i', strtotime($report['uploaded_at'])) ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- Resumo -->
        <div>
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-chart-pie mr-2 text-purple-600"></i>
                        Resumo
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="border-r border-gray-200">
                            <h4 class="text-2xl font-bold text-green-600"><?= $linkedCount ?></h4>
                            <p class="text-sm text-gray-500 mt-1">Vinculados</p>
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-yellow-600"><?= $unlinkedCount ?></h4>
                            <p class="text-sm text-gray-500 mt-1">Não vinculados</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dados de Faturamento -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <i class="fas fa-table mr-2 text-blue-600"></i>
                Dados de Faturamento
            </h3>
        </div>
        <div class="p-6">
            <?php if (empty($billing_data)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-triangle text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum dado encontrado</h3>
                    <p class="text-gray-500">Não há dados de faturamento para exibir.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="billingTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(0, 'text')">
                                    Nome <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(1, 'text')">
                                    CNPJ/CPF <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(2, 'text')">
                                    REPRESENTANTE <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(3, 'number')">
                                    TPV Total <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(4, 'number')">
                                    Markup <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(5, 'text')">
                                    Estabelecimento <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($billing_data as $data): ?>
                                <tr class="hover:bg-blue-200 transition-colors cursor-pointer" data-billing-id="<?= $data['id'] ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($data['nome'] ?? '') ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($data['cnpj_cpf'] ?? '') ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($data['representante'] ?? '') ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">R$ <?= number_format($data['tpv_total'], 2, ',', '.') ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">R$ <?= number_format($data['markup'], 2, ',', '.') ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($data['establishment_id']): ?>
                                            <div class="text-sm text-gray-900">
                                                <?= htmlspecialchars(($data['nome_fantasia'] ?? '') ?: ($data['razao_social'] ?? '')) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?= htmlspecialchars(($data['cidade'] ?? '') . '/' . ($data['uf'] ?? '')) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">Não vinculado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($data['establishment_id']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Vinculado
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Não vinculado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <?php if ($data['establishment_id']): ?>
                                                <button type="button" class="text-yellow-600 hover:text-yellow-900" onclick="unlinkEstablishment(<?= $data['id'] ?>)" title="Desvincular">
                                                    <i class="fas fa-unlink"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="text-blue-600 hover:text-blue-900" onclick="linkEstablishment(<?= $data['id'] ?>)" title="Vincular">
                                                    <i class="fas fa-link"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Vinculação -->
<div id="linkModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Vincular Estabelecimento</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeLinkModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-4">
                <label for="searchEstablishment" class="block text-sm font-medium text-gray-700 mb-2">Buscar Estabelecimento</label>
                <input type="text" id="searchEstablishment" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Digite nome, CNPJ ou CPF...">
            </div>
            <div id="establishmentResults" class="border border-gray-200 rounded-md max-h-96 overflow-y-auto">
                <div class="text-gray-500 text-center p-3">Digite pelo menos 3 caracteres para buscar</div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" onclick="closeLinkModal()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentBillingId = null;

function closeLinkModal() {
    document.getElementById('linkModal').classList.add('hidden');
}

function linkEstablishment(billingId) {
    currentBillingId = billingId;
    document.getElementById('linkModal').classList.remove('hidden');
    document.getElementById('searchEstablishment').value = '';
    document.getElementById('establishmentResults').innerHTML = '<div class="text-gray-500 text-center p-3">Digite pelo menos 3 caracteres para buscar</div>';
}

function unlinkEstablishment(billingId) {
    if (confirm('Tem certeza que deseja desvincular este estabelecimento?')) {
        fetch('<?= url('billing/unlink-establishment') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                billing_data_id: billingId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao desvincular estabelecimento.');
        });
    }
}

// Busca de estabelecimentos
document.getElementById('searchEstablishment').addEventListener('input', function() {
    const query = this.value;
    const resultsDiv = document.getElementById('establishmentResults');
    
    if (query.length < 3) {
        resultsDiv.innerHTML = '<div class="text-gray-500 text-center p-3">Digite pelo menos 3 caracteres para buscar</div>';
        return;
    }
    
    fetch('<?= url('billing/search-establishments') ?>?q=' + encodeURIComponent(query))
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                resultsDiv.innerHTML = '<div class="text-gray-500 text-center p-3">Nenhum estabelecimento encontrado</div>';
                return;
            }
            
            resultsDiv.innerHTML = data.map(establishment => `
                <div class="p-3 border-b border-gray-200 hover:bg-blue-50 cursor-pointer transition-colors" onclick="selectEstablishment(${establishment.id})">
                    <div class="flex justify-between items-start">
                        <div>
                            <h6 class="text-sm font-medium text-gray-900">${establishment.nome_fantasia || establishment.razao_social}</h6>
                            <p class="text-xs text-gray-500 mt-1">
                                ${establishment.cpf ? 'CPF: ' + establishment.cpf : ''}
                                ${establishment.cnpj ? 'CNPJ: ' + establishment.cnpj : ''}
                            </p>
                        </div>
                        <span class="text-xs text-gray-500">${establishment.cidade}/${establishment.uf}</span>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Erro:', error);
            resultsDiv.innerHTML = '<div class="text-red-500 text-center p-3">Erro ao buscar estabelecimentos</div>';
        });
});

function selectEstablishment(establishmentId) {
    if (!currentBillingId) return;
    
    fetch('<?= url('billing/link-establishment') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            billing_data_id: currentBillingId,
            establishment_id: establishmentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeLinkModal();
            location.reload();
        } else {
            alert('Erro: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao vincular estabelecimento.');
    });
}

// Função de ordenação da tabela
let sortDirection = {};

function sortTable(columnIndex, type) {
    const table = document.getElementById('billingTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Alternar direção de ordenação
    if (!sortDirection[columnIndex]) {
        sortDirection[columnIndex] = 'asc';
    } else {
        sortDirection[columnIndex] = sortDirection[columnIndex] === 'asc' ? 'desc' : 'asc';
    }
    
    const direction = sortDirection[columnIndex];
    
    // Ordenar as linhas
    rows.sort((a, b) => {
        const aText = a.cells[columnIndex].textContent.trim();
        const bText = b.cells[columnIndex].textContent.trim();
        
        let aValue, bValue;
        
        if (type === 'number') {
            // Extrair números (remover R$, pontos, vírgulas)
            aValue = parseFloat(aText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
            bValue = parseFloat(bText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
        } else {
            // Ordenação alfabética
            aValue = aText.toLowerCase();
            bValue = bText.toLowerCase();
        }
        
        if (aValue < bValue) {
            return direction === 'asc' ? -1 : 1;
        }
        if (aValue > bValue) {
            return direction === 'asc' ? 1 : -1;
        }
        return 0;
    });
    
    // Remover todas as linhas
    rows.forEach(row => tbody.removeChild(row));
    
    // Adicionar linhas ordenadas
    rows.forEach(row => tbody.appendChild(row));
    
    // Atualizar ícones de ordenação
    const headers = table.querySelectorAll('th');
    headers.forEach((header, index) => {
        const icon = header.querySelector('i');
        if (icon) {
            if (index === columnIndex) {
                icon.className = direction === 'asc' ? 'fas fa-sort-up ml-1' : 'fas fa-sort-down ml-1';
            } else {
                icon.className = 'fas fa-sort ml-1';
            }
        }
    });
}
</script>
