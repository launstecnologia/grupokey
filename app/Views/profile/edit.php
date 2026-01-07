<?php
$currentPage = 'perfil';
ob_start();

// Definir variáveis com valores padrão
$user = $user ?? [];
$type = $type ?? 'admin';
?>

<div class="pt-6 px-4">
    <!-- Exibir mensagens de erro -->
    <?php if (isset($_SESSION['validation_errors']) && !empty($_SESSION['validation_errors'])): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <h4 class="font-bold">Erros de Validação:</h4>
            <ul class="list-disc list-inside">
                <?php foreach ($_SESSION['validation_errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['validation_errors']); ?>
    <?php endif; ?>
    
    <!-- Exibir mensagens de erro -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Exibir mensagens de sucesso -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-edit mr-2"></i>
                Editar Perfil
            </h1>
            <p class="text-gray-600 mt-1">Atualize suas informações de perfil</p>
        </div>
        <div>
            <a href="<?= url('perfil') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
        </div>
    </div>

    <!-- Formulário -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-user-edit mr-2"></i>
                Dados do Perfil
            </h3>
        </div>

        <form method="POST" action="<?= url('perfil/update') ?>" enctype="multipart/form-data" class="p-6">
            <?= csrf_field() ?>
            
            <!-- Upload de Foto -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto do Perfil</label>
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <?php 
                        $photoPath = null;
                        if (!empty($user['photo'])) {
                            $photoPath = url('public/uploads/profiles/' . $user['photo']);
                        }
                        ?>
                        <div class="h-20 w-20 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                            <?php 
                            $fullPhotoPath = null;
                            if ($photoPath && !empty($user['photo'])) {
                                $fullPhotoPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR . $user['photo'];
                            }
                            ?>
                            <?php if ($photoPath && $fullPhotoPath && file_exists($fullPhotoPath)): ?>
                                <img src="<?= $photoPath ?>" alt="Foto do perfil" class="h-full w-full object-cover" id="photo-preview">
                            <?php else: ?>
                                <span class="text-2xl font-bold text-gray-500" id="photo-initial">
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
                    </div>
                    <div class="flex-1">
                        <input type="file" name="photo" id="photo" accept="image/*" 
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-xs text-gray-500">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</p>
                    </div>
                </div>
            </div>
            
            <?php if ($type === 'admin'): ?>
                <!-- Campos para Admin -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" name="name" required 
                               value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o nome">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" required 
                               value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o email">
                    </div>
                </div>
            <?php else: ?>
                <!-- Campos para Representante -->
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                            <input type="text" name="nome_completo" required 
                                   value="<?= htmlspecialchars($user['nome_completo'] ?? '') ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Digite o nome completo">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="email" required 
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Digite o email">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                            <input type="tel" name="telefone" 
                                   value="<?= htmlspecialchars($user['telefone'] ?? '') ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="(00) 00000-0000">
                        </div>
                    </div>

                    <!-- Endereço -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                            Endereço
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                                <input type="text" name="cep" 
                                       value="<?= htmlspecialchars($user['cep'] ?? '') ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="00000-000">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                                <input type="text" name="logradouro" 
                                       value="<?= htmlspecialchars($user['logradouro'] ?? '') ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Digite o logradouro">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                                <input type="text" name="numero" 
                                       value="<?= htmlspecialchars($user['numero'] ?? '') ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Digite o número">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                                <input type="text" name="complemento" 
                                       value="<?= htmlspecialchars($user['complemento'] ?? '') ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Digite o complemento">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                                <input type="text" name="bairro" 
                                       value="<?= htmlspecialchars($user['bairro'] ?? '') ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Digite o bairro">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                                <input type="text" name="cidade" 
                                       value="<?= htmlspecialchars($user['cidade'] ?? '') ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Digite a cidade">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                                <select name="uf" 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecione a UF</option>
                                    <?php
                                    $ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
                                    foreach ($ufs as $uf): ?>
                                        <option value="<?= $uf ?>" <?= ($user['uf'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Botões -->
            <div class="flex justify-end space-x-4 mt-8">
                <a href="<?= url('perfil') ?>" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo-preview');
    const photoInitial = document.getElementById('photo-initial');
    
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (photoPreview) {
                        photoPreview.src = e.target.result;
                        photoPreview.style.display = 'block';
                        if (photoInitial) {
                            photoInitial.style.display = 'none';
                        }
                    } else {
                        // Criar elemento img se não existir
                        const previewContainer = photoInput.closest('.mb-6').querySelector('.h-20');
                        if (previewContainer) {
                            previewContainer.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="h-full w-full object-cover" id="photo-preview">';
                        }
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>



