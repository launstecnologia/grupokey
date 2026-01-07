<?php
$currentPage = 'dashboard';
ob_start();

// Definir variáveis com valores padrão para evitar warnings
$client_stats = $client_stats ?? ['total_clientes' => 0, 'aprovados' => 0, 'pendentes' => 0, 'reprovados' => 0];
$current_month_stats = $current_month_stats ?? ['total' => 0, 'cadastros_ultimo_mes' => 0];
?>

<div class="pt-6 px-4">
    <!-- KPIs Cards -->
    <div class="w-full grid grid-cols-1 xl:grid-cols-2 2xl:grid-cols-3 gap-4">
        <!-- Total Clientes Card -->
        <div class="bg-white shadow rounded-lg mb-4 p-4 sm:p-6 h-full">
            <div class="flex items-center justify-between mb-4">
                <div class="flex-shrink-0">
                    <span class="text-2xl sm:text-3xl leading-none font-bold text-gray-900">
                        <?= $client_stats['total_clientes'] ?>
                    </span>
                    <h3 class="text-base font-normal text-gray-500">Total de Clientes</h3>
                </div>
                <div class="flex items-center justify-end flex-1 text-blue-500 text-base font-bold">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-building text-2xl text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Clientes Pendentes Card -->
        <div class="bg-white shadow rounded-lg mb-4 p-4 sm:p-6 h-full">
            <div class="flex items-center justify-between mb-4">
                <div class="flex-shrink-0">
                    <span class="text-2xl sm:text-3xl leading-none font-bold text-gray-900">
                        <?= $client_stats['pendentes'] ?>
                    </span>
                    <h3 class="text-base font-normal text-gray-500">Clientes Pendentes</h3>
                </div>
                <div class="flex items-center justify-end flex-1 text-yellow-500 text-base font-bold">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-clock text-2xl text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Cadastros Último Mês Card -->
        <div class="bg-white shadow rounded-lg mb-4 p-4 sm:p-6 h-full">
            <div class="flex items-center justify-between mb-4">
                <div class="flex-shrink-0">
                    <span class="text-2xl sm:text-3xl leading-none font-bold text-gray-900">
                        <?= $client_stats['cadastros_ultimo_mes'] ?>
                    </span>
                    <h3 class="text-base font-normal text-gray-500">Cadastros Último Mês</h3>
                </div>
                <div class="flex items-center justify-end flex-1 text-green-500 text-base font-bold">
                    +<?= $client_stats['cadastros_ultimo_mes'] ?>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-chart-line text-2xl text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables Row -->
    <div class="grid grid-cols-1 2xl:grid-cols-2 xl:gap-4 my-4">
        <!-- Status Chart -->
        <div class="bg-white shadow rounded-lg mb-4 p-4 sm:p-6 h-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold leading-none text-gray-900">Status dos Clientes</h3>
            </div>
            <div class="flow-root">
                <canvas id="statusChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Recent Clients -->
        <div class="bg-white shadow rounded-lg mb-4 p-4 sm:p-6 h-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold leading-none text-gray-900">Clientes Recentes</h3>
                <a href="<?= url('estabelecimentos') ?>" class="text-sm font-medium text-cyan-600 hover:bg-gray-100 rounded-lg inline-flex items-center p-2">
                    Ver todos
                </a>
            </div>
            <div class="flow-root">
                <ul role="list" class="divide-y divide-gray-200">
                    <?php foreach ($recent_clients as $client): ?>
                    <li class="py-3 sm:py-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-cyan-600 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white">
                                        <?= strtoupper(substr($client['razao_social'] ?: $client['nome_completo'], 0, 1)) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <?= htmlspecialchars($client['razao_social'] ?: $client['nome_completo']) ?>
                                </p>
                                <p class="text-sm text-gray-500 truncate">
                                    <?= htmlspecialchars($client['cidade']) ?>/<?= htmlspecialchars($client['uf']) ?>
                                </p>
                            </div>
                            <div class="inline-flex items-center text-base font-semibold text-gray-900">
                                <?= get_status_badge($client['status']) ?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Pending Clients Table -->
    <div class="bg-white shadow rounded-lg mb-4 p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Clientes Pendentes</h3>
                <span class="text-base font-normal text-gray-500">Aguardando aprovação</span>
            </div>
            <div class="flex-shrink-0">
                <a href="<?= url('estabelecimentos') ?>?status=PENDING" class="text-sm font-medium text-cyan-600 hover:bg-gray-100 rounded-lg p-2">Ver todos</a>
            </div>
        </div>
        <!-- Table -->
        <div class="flex flex-col mt-8">
            <div class="overflow-x-auto rounded-lg">
                <div class="align-middle inline-block min-w-full">
                    <div class="shadow overflow-hidden sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="p-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    <th scope="col" class="p-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Produto
                                    </th>
                                    <th scope="col" class="p-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Data Cadastro
                                    </th>
                                    <th scope="col" class="p-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <?php foreach ($pending_clients as $client): ?>
                                <tr>
                                    <td class="p-4 whitespace-nowrap text-sm font-normal text-gray-900">
                                        <div class="text-base font-semibold text-gray-900"><?= htmlspecialchars($client['razao_social'] ?: $client['nome_completo']) ?></div>
                                        <div class="text-sm font-normal text-gray-500"><?= htmlspecialchars($client['cidade']) ?>/<?= htmlspecialchars($client['uf']) ?></div>
                                    </td>
                                    <td class="p-4 whitespace-nowrap text-sm font-normal text-gray-900">
                                        <?= htmlspecialchars($client['produto']) ?>
                                    </td>
                                    <td class="p-4 whitespace-nowrap text-sm font-normal text-gray-500">
                                        <?= date('d/m/Y', strtotime($client['created_at'])) ?>
                                    </td>
                                    <td class="p-4 whitespace-nowrap text-sm font-medium">
                                        <a href="<?= url('estabelecimentos/' . $client['id']) ?>" class="text-cyan-600 hover:text-cyan-900 mr-3">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <a href="<?= url('estabelecimentos/' . $client['id'] . '/edit') ?>" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Stats -->
    <div class="bg-white shadow rounded-lg mb-4 p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold leading-none text-gray-900">Estatísticas do Mês Atual</h3>
        </div>
        <div class="block w-full overflow-x-auto">
            <table class="items-center w-full bg-transparent border-collapse">
                <thead>
                    <tr>
                        <th class="px-4 bg-gray-50 text-gray-700 align-middle py-3 text-xs font-semibold text-left uppercase border-l-0 border-r-0 whitespace-nowrap">Métrica</th>
                        <th class="px-4 bg-gray-50 text-gray-700 align-middle py-3 text-xs font-semibold text-left uppercase border-l-0 border-r-0 whitespace-nowrap">Valor</th>
                        <th class="px-4 bg-gray-50 text-gray-700 align-middle py-3 text-xs font-semibold text-left uppercase border-l-0 border-r-0 whitespace-nowrap min-w-140-px"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="text-gray-500">
                        <th class="border-t-0 px-4 align-middle text-sm font-normal whitespace-nowrap p-4 text-left">Total Cadastros</th>
                        <td class="border-t-0 px-4 align-middle text-xs font-medium text-gray-900 whitespace-nowrap p-4"><?= $current_month_stats['total'] ?></td>
                        <td class="border-t-0 px-4 align-middle text-xs whitespace-nowrap p-4">
                            <div class="flex items-center">
                                <span class="mr-2 text-xs font-medium">100%</span>
                                <div class="relative w-full">
                                    <div class="w-full bg-gray-200 rounded-sm h-2">
                                        <div class="bg-cyan-600 h-2 rounded-sm" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="text-gray-500">
                        <th class="border-t-0 px-4 align-middle text-sm font-normal whitespace-nowrap p-4 text-left">Aprovados</th>
                        <td class="border-t-0 px-4 align-middle text-xs font-medium text-gray-900 whitespace-nowrap p-4"><?= $current_month_stats['aprovados'] ?></td>
                        <td class="border-t-0 px-4 align-middle text-xs whitespace-nowrap p-4">
                            <div class="flex items-center">
                                <span class="mr-2 text-xs font-medium"><?= $current_month_stats['total'] > 0 ? round(($current_month_stats['aprovados'] / $current_month_stats['total']) * 100) : 0 ?>%</span>
                                <div class="relative w-full">
                                    <div class="w-full bg-gray-200 rounded-sm h-2">
                                        <div class="bg-green-500 h-2 rounded-sm" style="width: <?= $current_month_stats['total'] > 0 ? ($current_month_stats['aprovados'] / $current_month_stats['total']) * 100 : 0 ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="text-gray-500">
                        <th class="border-t-0 px-4 align-middle text-sm font-normal whitespace-nowrap p-4 text-left">Pendentes</th>
                        <td class="border-t-0 px-4 align-middle text-xs font-medium text-gray-900 whitespace-nowrap p-4"><?= $current_month_stats['pendentes'] ?></td>
                        <td class="border-t-0 px-4 align-middle text-xs whitespace-nowrap p-4">
                            <div class="flex items-center">
                                <span class="mr-2 text-xs font-medium"><?= $current_month_stats['total'] > 0 ? round(($current_month_stats['pendentes'] / $current_month_stats['total']) * 100) : 0 ?>%</span>
                                <div class="relative w-full">
                                    <div class="w-full bg-gray-200 rounded-sm h-2">
                                        <div class="bg-yellow-500 h-2 rounded-sm" style="width: <?= $current_month_stats['total'] > 0 ? ($current_month_stats['pendentes'] / $current_month_stats['total']) * 100 : 0 ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Status Chart
const ctx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Aprovados', 'Pendentes', 'Reprovados'],
        datasets: [{
            data: [
                <?= $client_stats['aprovados'] ?>,
                <?= $client_stats['pendentes'] ?>,
                <?= $client_stats['reprovados'] ?>
            ],
            backgroundColor: [
                '#10B981',
                '#F59E0B',
                '#EF4444'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>