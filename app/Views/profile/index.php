<?php
$currentPage = 'perfil';
ob_start();

// Definir variáveis com valores padrão
$user = $user ?? [];
$type = $type ?? 'admin';
?>

<div class="pt-6 px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-user mr-2"></i>
                Meu Perfil
            </h1>
            <p class="text-gray-600 mt-1">Visualize suas informações de perfil</p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= url('perfil/edit') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Editar Perfil
            </a>
            <a href="<?= url('change-password') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-key mr-2"></i>
                Alterar Senha
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conteúdo Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informações Pessoais -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-blue-600"></i>
                        Informações Pessoais
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php if ($type === 'admin'): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nome</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['name'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['email'] ?? '-') ?></dd>
                        </div>
                        <?php else: ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nome Completo</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['nome_completo'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['email'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Telefone</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['telefone'] ?? '-') ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <?php if ($type === 'representative'): ?>
            <!-- Endereço -->
            <?php if (!empty($user['cep']) || !empty($user['logradouro'])): ?>
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                        Endereço
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php if (!empty($user['cep'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">CEP</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['cep']) ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($user['logradouro'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Logradouro</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['logradouro']) ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($user['numero'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Número</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['numero']) ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($user['complemento'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Complemento</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['complemento']) ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($user['bairro'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Bairro</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['bairro']) ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($user['cidade']) || !empty($user['uf'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Cidade / UF</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= htmlspecialchars(($user['cidade'] ?? '-') . '/' . ($user['uf'] ?? '-')) ?>
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Informações do Sistema -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informações do Sistema
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['id'] ?? '-') ?></dd>
                        </div>
                        <?php if (isset($user['created_at'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Criado em</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                            </dd>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($user['updated_at'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Atualizado em</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?>
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Foto do Perfil -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-image mr-2 text-blue-600"></i>
                        Foto do Perfil
                    </h3>
                </div>
                <div class="p-6 text-center">
                    <?php 
                    $photoPath = null;
                    $fullPath = null;
                    $hasPhoto = false;
                    
                    // Verificar se photo existe no array e construir caminhos
                    $photoFileName = $user['photo'] ?? null;
                    if (!empty($photoFileName)) {
                        // Caminho absoluto do arquivo - usando caminho relativo ao arquivo atual
                        // Estamos em app/Views/profile/index.php, então precisamos voltar 3 níveis para chegar na raiz
                        $basePath = dirname(__DIR__, 3); // Volta 3 níveis: profile -> Views -> app -> raiz
                        $fullPath = $basePath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR . $photoFileName;
                        
                        // Normalizar caminho para Windows/Linux
                        $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fullPath);
                        
                        // Caminho relativo para URL
                        $photoPath = url('public/uploads/profiles/' . $photoFileName);
                        
                        // Verificar se o arquivo existe
                        if (file_exists($fullPath) && is_file($fullPath)) {
                            $hasPhoto = true;
                        }
                    }
                    ?>
                    <div class="mx-auto h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                        <?php if ($hasPhoto && $photoPath): ?>
                            <img src="<?= $photoPath ?>" alt="Foto do perfil" class="h-full w-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span class="text-4xl font-bold text-gray-500 hidden">
                                <?php
                                if ($type === 'admin') {
                                    $displayName = $user['name'] ?? 'U';
                                } else {
                                    $displayName = $user['nome_completo'] ?? 'U';
                                }
                                echo strtoupper(substr($displayName, 0, 1));
                                ?>
                            </span>
                        <?php else: ?>
                            <span class="text-4xl font-bold text-gray-500">
                                <?php
                                if ($type === 'admin') {
                                    $displayName = $user['name'] ?? 'U';
                                } else {
                                    $displayName = $user['nome_completo'] ?? 'U';
                                }
                                echo strtoupper(substr($displayName, 0, 1));
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">
                        <?= $hasPhoto ? 'Foto do perfil' : 'Avatar padrão do sistema' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

