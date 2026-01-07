<?php
$currentPage = 'dashboard';
ob_start();

// Definir variáveis com valores padrão para evitar warnings
$pagseguro_stats = $pagseguro_stats ?? ['aprovados' => 0, 'pendentes' => 0, 'reprovados' => 0];
$fgts_stats = $fgts_stats ?? ['aprovados' => 0, 'pendentes' => 0, 'reprovados' => 0];
$stats = $stats ?? ['total' => 0, 'aprovados' => 0, 'pendentes' => 0, 'reprovados' => 0];
$user_stats = $user_stats ?? ['total' => 0, 'ativos' => 0];
$representative_stats = $representative_stats ?? ['total' => 0, 'ativos' => 0];
$current_month_stats = $current_month_stats ?? ['total' => 0, 'cadastros_ultimo_mes' => 0];
$billing_stats = $billing_stats ?? ['total_reports' => 0, 'total_tpv' => 0, 'total_markup' => 0, 'reports_this_month' => 0];
?>

<div class="px-4" style="padding-top: 124px;">
    <!-- Botões de Ação Rápida -->
    <div class="mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= url('estabelecimentos/create') ?>" class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                <i class="fas fa-plus mr-2"></i>
                Novo Estabelecimento
            </a>
            <a href="<?= url('representantes/create') ?>" class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                <i class="fas fa-user-plus mr-2"></i>
                Novo Representante
            </a>
            <a href="<?= url('usuarios/create') ?>" class="flex items-center justify-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                <i class="fas fa-user-shield mr-2"></i>
                Novo Usuário
            </a>
            <a href="<?= url('billing/create') ?>" class="flex items-center justify-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors text-sm font-medium">
                <i class="fas fa-file-excel mr-2"></i>
                Novo Relatório
            </a>
            <a href="<?= url('estabelecimentos') ?>" class="flex items-center justify-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm font-medium">
                <i class="fas fa-list mr-2"></i>
                Listar Estabelecimentos
            </a>
        </div>
    </div>

    <!-- KPIs Cards -->
    <div class="w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- PagSeguro Card -->
        <div class="bg-white shadow rounded-lg mb-4 p-4 sm:p-6 h-full">
            <div class="flex items-center justify-between mb-4">
                <div class="flex-shrink-0">
                    <span class="text-2xl sm:text-3xl leading-none font-bold text-gray-900">
                        <?= format_currency(($pagseguro_stats['aprovados'] ?? 0) * 1000) ?>
                    </span>
                    <h3 class="text-base font-normal text-gray-500">PagSeguro - Último Mês</h3>
                </div>
                <div class="flex items-center justify-end flex-1 text-green-500 text-base font-bold">
                    <?= $pagseguro_stats['aprovados'] ?? 0 ?>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-credit-card text-2xl text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Total Estabelecimentos Card -->
        <div class="bg-white shadow rounded-lg mb-4 p-4 sm:p-6 h-full">
            <div class="flex items-center justify-between mb-4">
                <div class="flex-shrink-0">
                    <span class="text-2xl sm:text-3xl leading-none font-bold text-gray-900">
                        <?= $stats['total'] ?? 0 ?>
                    </span>
                    <h3 class="text-base font-normal text-gray-500">Total Estabelecimentos</h3>
                </div>
                <div class="flex items-center justify-end flex-1 text-blue-500 text-base font-bold">
                    <?= $stats['aprovados'] ?? 0 ?>
                    <span class="ml-1 text-sm text-gray-500">Aprovados</span>
                </div>
            </div>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-building text-2xl text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Representantes Ativos Card -->
        <div class="bg-white shadow rounded-lg mb-4 p-4 sm:p-6 h-full">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <span class="text-2xl sm:text-3xl leading-none font-bold text-gray-900">
                        <?= $representative_stats['total'] ?? 0 ?>
                    </span>
                    <h3 class="text-base font-normal text-gray-500">Representantes Ativos</h3>
                </div>
                <div class="ml-5 w-0 flex items-center justify-end flex-1 text-green-500 text-base font-bold">
                    <?= $representative_stats['ativos'] ?? 0 ?>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Usuários Ativos Card -->
        <div class="bg-white shadow rounded-lg mb-4 p-4 sm:p-6 h-full">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <span class="text-2xl sm:text-3xl leading-none font-bold text-gray-900">
                        <?= $user_stats['total'] ?? 0 ?>
                    </span>
                    <h3 class="text-base font-normal text-gray-500">Usuários Ativos</h3>
                </div>
                <div class="ml-5 w-0 flex items-center justify-end flex-1 text-green-500 text-base font-bold">
                    <?= $user_stats['ativos'] ?? 0 ?>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Cadastros Último Mês Card -->
        <div class="bg-white shadow rounded-lg mb-4 p-4 sm:p-6 h-full">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <span class="text-2xl sm:text-3xl leading-none font-bold text-gray-900">
                        <?= $current_month_stats['cadastros_ultimo_mes'] ?? 0 ?>
                    </span>
                    <h3 class="text-base font-normal text-gray-500">Cadastros Último Mês</h3>
                </div>
                <div class="ml-5 w-0 flex items-center justify-end flex-1 text-blue-500 text-base font-bold">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z"></path>
                        <path d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5zM15 11h2a1 1 0 110 2h-2v-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="w-full grid grid-cols-1 xl:grid-cols-2 gap-4 mt-8">
        <!-- Evolução de Cadastros Chart -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Evolução de Cadastros</h3>
            <div style="height: 300px; width: 100%; position: relative; overflow: hidden;">
                <canvas id="evolutionChart" style="max-height: 300px !important;"></canvas>
            </div>
        </div>

        <!-- Top Cidades -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Top 5 Cidades</h3>
            <div class="space-y-3">
                <?php if (!empty($top_cities)): ?>
                    <?php foreach ($top_cities as $city): ?>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600"><?= htmlspecialchars($city['cidade'] . ', ' . $city['uf']) ?></span>
                            <span class="font-semibold"><?= $city['total'] ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500">Nenhum dado disponível</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dados para o gráfico de evolução
    const evolutionData = <?= json_encode($monthly_evolution ?? []) ?>;
    
    // Se não há dados, criar dados padrão
    let labels, data;
    if (evolutionData && evolutionData.length > 0) {
        labels = evolutionData.map(item => {
            const date = new Date(item.mes);
            return date.toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' });
        });
        data = evolutionData.map(item => item.total);
    } else {
        // Dados padrão para os últimos 6 meses quando não há dados
        labels = [];
        data = [];
        for (let i = 5; i >= 0; i--) {
            const date = new Date();
            date.setMonth(date.getMonth() - i);
            labels.push(date.toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' }));
            data.push(0); // Valor zero para cada mês
        }
    }

    const ctx = document.getElementById('evolutionChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cadastros',
                data: data,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        padding: 10,
                        color: '#808080'
                    }
                },
                x: {
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        padding: 10,
                        color: '#808080'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            },
            elements: {
                point: {
                    radius: 4,
                    hoverRadius: 6
                }
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>