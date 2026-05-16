<?php
$currentPage = 'dashboard';
ob_start();

// Definir variáveis com valores padrão para evitar warnings
$client_stats = $client_stats ?? ['total_clientes' => 0, 'aprovados' => 0, 'pendentes' => 0, 'reprovados' => 0];
$current_month_stats = $current_month_stats ?? ['total' => 0, 'cadastros_ultimo_mes' => 0];
$open_tickets = $open_tickets ?? 0;
$monthly_evolution = $monthly_evolution ?? [];
$banners = $banners ?? [];
$pending_modal = $pending_modal ?? null;
?>

<div class="pt-6 px-4">
    <?php if (!empty($pending_modal)): ?>
        <?php
        $modalImage = !empty($pending_modal['image_path'])
            ? url('modais-representante/' . (int) ($pending_modal['id'] ?? 0) . '/image')
            : (string) ($pending_modal['image_url'] ?? '');
        $modalLink = $pending_modal['resolved_link'] ?? null;
        $modalTarget = !empty($pending_modal['target_blank']) ? '_blank' : '_self';
        ?>
        <div id="rep-modal-overlay" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden">
                <?php if ($modalImage !== ''): ?>
                    <img src="<?= htmlspecialchars($modalImage) ?>" alt="Modal" class="w-full h-56 object-cover">
                <?php endif; ?>
                <div class="p-6">
                    <?php if (!empty($pending_modal['title'])): ?>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2"><?= htmlspecialchars((string) $pending_modal['title']) ?></h2>
                    <?php endif; ?>
                    <div class="text-gray-700 prose prose-sm max-w-none"><?= (string) ($pending_modal['message'] ?? '') ?></div>
                    <div class="mt-6 flex justify-end gap-3">
                        <?php if (!empty($modalLink)): ?>
                            <a href="<?= htmlspecialchars((string) $modalLink) ?>" target="<?= $modalTarget ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Ir para destino</a>
                        <?php endif; ?>
                        <button id="rep-modal-read-btn" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">Li e fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="rep-modal-delivery-id" value="<?= (int) ($pending_modal['delivery_id'] ?? 0) ?>">
    <?php endif; ?>

    <?php if (!empty($banners)): ?>
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div id="rep-banner-slider" class="relative overflow-hidden rounded-lg bg-black h-[800px]">
            <?php foreach ($banners as $index => $banner): ?>
                <?php
                    $imageSrc = !empty($banner['image_path'])
                        ? url('banners/' . (int) ($banner['id'] ?? 0) . '/image')
                        : (string) ($banner['image_url'] ?? '');
                    $slideSeconds = max(1, min(60, (int) ($banner['slide_duration_seconds'] ?? 5)));
                    $slideLink = $banner['resolved_link'] ?? null;
                    $slideTarget = !empty($banner['target_blank']) ? '_blank' : '_self';
                ?>
                <div class="rep-banner-slide transition-opacity duration-500 <?= $index === 0 ? 'block' : 'hidden' ?> h-full" data-duration-ms="<?= $slideSeconds * 1000 ?>">
                    <?php if (!empty($slideLink)): ?>
                        <a href="<?= htmlspecialchars((string) $slideLink) ?>" target="<?= $slideTarget ?>" class="block h-full">
                    <?php endif; ?>
                    <div class="relative w-full h-full overflow-hidden bg-black">
                        <img src="<?= htmlspecialchars($imageSrc) ?>" alt="" aria-hidden="true" class="absolute inset-0 w-full h-full object-cover blur-2xl opacity-40 scale-110">
                        <div class="absolute inset-0 bg-black/35"></div>
                        <div class="relative z-10 w-full h-full flex items-center justify-center">
                            <img src="<?= htmlspecialchars($imageSrc) ?>" alt="<?= htmlspecialchars((string) ($banner['title'] ?? 'Banner')) ?>" class="w-full h-full object-contain">
                        </div>
                    </div>
                    <?php if (!empty($banner['title']) || !empty($banner['subtitle'])): ?>
                    <div class="absolute left-0 right-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                        <?php if (!empty($banner['title'])): ?><h4 class="text-white text-lg font-bold"><?= htmlspecialchars((string) $banner['title']) ?></h4><?php endif; ?>
                        <?php if (!empty($banner['subtitle'])): ?><p class="text-gray-200 text-sm"><?= htmlspecialchars((string) $banner['subtitle']) ?></p><?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($slideLink)): ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (count($banners) > 1): ?>
            <button type="button" id="rep-banner-prev" class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center" aria-label="Banner anterior">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button type="button" id="rep-banner-next" class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center" aria-label="Próximo banner">
                <i class="fas fa-chevron-right"></i>
            </button>
            <?php endif; ?>
        </div>
        <?php if (count($banners) > 1): ?>
        <div id="rep-banner-dots" class="flex justify-center gap-2 mt-3">
            <?php foreach ($banners as $index => $banner): ?>
                <button type="button" class="rep-banner-dot w-2.5 h-2.5 rounded-full <?= $index === 0 ? 'bg-blue-600' : 'bg-gray-300' ?>" data-index="<?= $index ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="w-full grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <a href="<?= url('estabelecimentos') ?>" title="Abrir lista de estabelecimentos" class="bg-white shadow rounded-lg p-5 block hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total estabelecimento</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?= (int) ($client_stats['total_clientes'] ?? 0) ?></p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-building text-lg"></i>
                </div>
            </div>
        </a>

        <a href="<?= url('estabelecimentos') ?>?status=PENDING" title="Abrir estabelecimentos pendentes" class="bg-white shadow rounded-lg p-5 block hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Estabelecimento pendente</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?= (int) ($client_stats['pendentes'] ?? 0) ?></p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-yellow-100 text-yellow-700 flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-lg"></i>
                </div>
            </div>
        </a>

        <a href="<?= url('estabelecimentos') ?>?status=REPROVED" title="Abrir estabelecimentos reprovados" class="bg-white shadow rounded-lg p-5 block hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Estabelecimento reprovado</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?= (int) ($client_stats['reprovados'] ?? 0) ?></p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-red-100 text-red-700 flex items-center justify-center">
                    <i class="fas fa-times-circle text-lg"></i>
                </div>
            </div>
        </a>

        <a href="<?= url('chamados') ?>?status=OPEN" title="Abrir chamados em aberto" class="bg-white shadow rounded-lg p-5 block hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Chamado (aberto)</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?= (int) $open_tickets ?></p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <i class="fas fa-headset text-lg"></i>
                </div>
            </div>
        </a>

        <a href="<?= url('estabelecimentos') ?>?date_from=<?= date('Y-m-01') ?>" title="Abrir cadastros do mês" class="bg-white shadow rounded-lg p-5 block hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Cadastro do mês</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?= (int) ($client_stats['cadastros_ultimo_mes'] ?? 0) ?></p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-lg"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="mt-6 bg-white shadow rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Evolução de Cadastros</h3>
        <div class="h-64">
            <canvas id="rep-evolution-chart"></canvas>
        </div>
    </div>
</div>

<script>
(function() {
    const chartEl = document.getElementById('rep-evolution-chart');
    if (!chartEl || typeof Chart === 'undefined') return;

    const labels = <?= json_encode(array_map(static function($row) {
        $mes = (string) ($row['mes'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}$/', $mes)) return $mes;
        $dt = \DateTime::createFromFormat('Y-m', $mes);
        return $dt ? $dt->format('M/Y') : $mes;
    }, $monthly_evolution), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const values = <?= json_encode(array_map(static function($row) {
        return (int) ($row['total'] ?? 0);
    }, $monthly_evolution), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    new Chart(chartEl.getContext('2d'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Cadastros',
                data: values,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.15)',
                pointBackgroundColor: '#3b82f6',
                pointRadius: 3,
                pointHoverRadius: 5,
                borderWidth: 2,
                tension: 0.35,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(148,163,184,0.15)' },
                    ticks: { color: '#64748b' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148,163,184,0.15)' },
                    ticks: { color: '#64748b', precision: 0 }
                }
            }
        }
    });
})();

(function() {
    const slider = document.getElementById('rep-banner-slider');
    if (!slider) return;
    const slides = Array.from(slider.querySelectorAll('.rep-banner-slide'));
    if (slides.length <= 1) return;
    const dots = Array.from(document.querySelectorAll('.rep-banner-dot'));
    const prevBtn = document.getElementById('rep-banner-prev');
    const nextBtn = document.getElementById('rep-banner-next');
    let active = 0;
    let timer = null;

    function setActive(nextIndex) {
        slides.forEach((slide, index) => {
            const show = index === nextIndex;
            slide.classList.toggle('block', show);
            slide.classList.toggle('hidden', !show);
        });
        dots.forEach((dot, index) => {
            dot.classList.toggle('bg-blue-600', index === nextIndex);
            dot.classList.toggle('bg-gray-300', index !== nextIndex);
        });
        active = nextIndex;
    }

    function queueNext() {
        clearTimeout(timer);
        const duration = Number(slides[active].dataset.durationMs || 5000);
        timer = setTimeout(() => {
            const next = (active + 1) % slides.length;
            setActive(next);
            queueNext();
        }, duration);
    }

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            const index = Number(dot.dataset.index || 0);
            if (Number.isNaN(index)) return;
            setActive(index);
            queueNext();
        });
    });

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            const next = (active - 1 + slides.length) % slides.length;
            setActive(next);
            queueNext();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            const next = (active + 1) % slides.length;
            setActive(next);
            queueNext();
        });
    }

    queueNext();
})();

(function() {
    const overlay = document.getElementById('rep-modal-overlay');
    const btn = document.getElementById('rep-modal-read-btn');
    const deliveryIdInput = document.getElementById('rep-modal-delivery-id');
    if (!overlay || !btn || !deliveryIdInput) return;

    btn.addEventListener('click', async function() {
        const deliveryId = deliveryIdInput.value;
        if (!deliveryId) {
            overlay.remove();
            return;
        }
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
        try {
            await fetch('<?= url('modais-representante/delivery') ?>/' + deliveryId + '/ack', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'csrf_token=<?= csrf_token() ?>'
            });
        } catch (e) {}
        overlay.remove();
    });
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
