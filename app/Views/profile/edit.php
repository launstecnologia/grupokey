<?php
$currentPage = 'perfil';
ob_start();

// Definir variáveis com valores padrão
$user = $user ?? [];
$type = $type ?? 'admin';
?>

<style>
    .gk-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding: 0.9rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid transparent;
    }
    .gk-alert-icon {
        margin-top: 0.15rem;
        font-size: 1.05rem;
        line-height: 1;
    }
    .gk-alert-title {
        margin: 0 0 0.25rem;
        font-weight: 600;
        font-size: 0.95rem;
        line-height: 1.3;
    }
    .gk-alert-text {
        margin: 0;
        font-size: 0.875rem;
        line-height: 1.45;
    }
    .gk-alert-list {
        margin: 0;
        padding-left: 1.1rem;
        font-size: 0.875rem;
        line-height: 1.45;
    }
    .gk-alert-error {
        background-color: #fef2f2;
        border-color: #fecaca;
        color: #991b1b;
    }
    .gk-alert-success {
        background-color: #ecfdf5;
        border-color: #a7f3d0;
        color: #065f46;
    }
    .dark .gk-alert-error {
        background-color: rgba(127, 29, 29, 0.45) !important;
        border-color: #991b1b !important;
        color: #fecaca !important;
    }
    .dark .gk-alert-success {
        background-color: rgba(6, 78, 59, 0.55) !important;
        border-color: #065f46 !important;
        color: #a7f3d0 !important;
    }
    .dark .gk-alert-error,
    .dark .gk-alert-error * {
        color: #fecaca !important;
    }
    .dark .gk-alert-success,
    .dark .gk-alert-success * {
        color: #a7f3d0 !important;
    }
</style>

<div class="pt-6 px-4">
    <?php if (isset($_SESSION['validation_errors']) && !empty($_SESSION['validation_errors'])): ?>
        <div class="gk-alert gk-alert-error" role="alert">
            <i class="fas fa-exclamation-circle gk-alert-icon" aria-hidden="true"></i>
            <div>
                <p class="gk-alert-title">Erros de validação</p>
                <ul class="gk-alert-list">
                    <?php foreach ($_SESSION['validation_errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php unset($_SESSION['validation_errors']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="gk-alert gk-alert-error" role="alert">
            <i class="fas fa-exclamation-circle gk-alert-icon" aria-hidden="true"></i>
            <div>
                <p class="gk-alert-title">Não foi possível salvar</p>
                <p class="gk-alert-text"><?= htmlspecialchars($_SESSION['error']) ?></p>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="gk-alert gk-alert-success" role="alert">
            <i class="fas fa-check-circle gk-alert-icon" aria-hidden="true"></i>
            <div>
                <p class="gk-alert-title">Perfil atualizado</p>
                <p class="gk-alert-text"><?= htmlspecialchars($_SESSION['success']) ?></p>
            </div>
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

        <form id="profile-edit-form" method="POST" action="<?= url('perfil/update') ?>" enctype="multipart/form-data" class="p-6" autocomplete="off">
            <?= csrf_field() ?>
            <input type="text" name="fake_username" autocomplete="username" class="hidden" tabindex="-1" aria-hidden="true">
            <input type="password" name="fake_password" autocomplete="new-password" class="hidden" tabindex="-1" aria-hidden="true">
            
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
                            <?php if ($photoPath): ?>
                                <img src="<?= $photoPath ?>" alt="Foto do perfil" class="h-full w-full object-cover" id="photo-preview" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <span class="text-2xl font-bold text-gray-500 hidden" id="photo-initial">
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
                               autocomplete="new-email" autocapitalize="off" spellcheck="false"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite o email">
                    </div>
                </div>
            <?php else: ?>
                <!-- Campos para Parceiro -->
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
                                   autocomplete="new-email" autocapitalize="off" spellcheck="false"
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
    const form = document.getElementById('profile-edit-form');
    const maxPhotoBytes = 1024 * 1024;
    let compressing = false;

    function showProfileAlert(message) {
        const container = document.querySelector('.pt-6.px-4');
        if (!container) {
            return;
        }

        let alertBox = document.getElementById('profile-client-alert');
        if (!alertBox) {
            alertBox = document.createElement('div');
            alertBox.id = 'profile-client-alert';
            alertBox.className = 'gk-alert gk-alert-error';
            alertBox.setAttribute('role', 'alert');
            container.insertBefore(alertBox, container.firstChild);
        }

        alertBox.innerHTML = '<i class="fas fa-exclamation-circle gk-alert-icon" aria-hidden="true"></i><div><p class="gk-alert-title">Não foi possível salvar</p><p class="gk-alert-text"></p></div>';
        alertBox.querySelector('.gk-alert-text').textContent = message;
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function compressImage(file) {
        return new Promise(function(resolve, reject) {
            const image = new Image();
            const objectUrl = URL.createObjectURL(file);

            image.onload = function() {
                URL.revokeObjectURL(objectUrl);
                const maxEdge = 1600;
                let width = image.width;
                let height = image.height;

                if (width > maxEdge || height > maxEdge) {
                    const ratio = Math.min(maxEdge / width, maxEdge / height);
                    width = Math.round(width * ratio);
                    height = Math.round(height * ratio);
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const context = canvas.getContext('2d');
                if (!context) {
                    reject(new Error('canvas'));
                    return;
                }
                context.drawImage(image, 0, 0, width, height);

                const qualities = [0.82, 0.7, 0.55, 0.4];
                (function tryQuality(index) {
                    canvas.toBlob(function(blob) {
                        if (!blob) {
                            reject(new Error('blob'));
                            return;
                        }
                        if (blob.size <= maxPhotoBytes || index === qualities.length - 1) {
                            resolve(new File([blob], 'perfil.jpg', { type: 'image/jpeg' }));
                            return;
                        }
                        tryQuality(index + 1);
                    }, 'image/jpeg', qualities[index]);
                })(0);
            };

            image.onerror = function() {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('load'));
            };

            image.src = objectUrl;
        });
    }
    
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
                        const previewContainer = photoInput.closest('.mb-6').querySelector('.h-20');
                        if (previewContainer) {
                            const preview = document.createElement('img');
                            preview.src = e.target.result;
                            preview.alt = 'Preview';
                            preview.className = 'h-full w-full object-cover';
                            preview.id = 'photo-preview';
                            previewContainer.replaceChildren(preview);
                        }
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (form && photoInput) {
        form.addEventListener('submit', function(e) {
            if (compressing) {
                return;
            }

            const file = photoInput.files && photoInput.files[0];
            if (!file || file.size <= maxPhotoBytes) {
                return;
            }

            if (!file.type || file.type.indexOf('image/') !== 0) {
                e.preventDefault();
                showProfileAlert('Use uma imagem JPG, PNG ou GIF de até 2MB.');
                return;
            }

            e.preventDefault();
            compressing = true;
            const button = form.querySelector('button[type="submit"]');
            const originalLabel = button ? button.innerHTML : '';
            if (button) {
                button.disabled = true;
                button.textContent = 'Otimizando foto...';
            }

            compressImage(file).then(function(compressed) {
                if (typeof DataTransfer === 'undefined') {
                    throw new Error('DataTransfer');
                }
                if (compressed.size > 2 * 1024 * 1024) {
                    throw new Error('size');
                }
                const transfer = new DataTransfer();
                transfer.items.add(compressed);
                photoInput.files = transfer.files;
                compressing = false;
                form.submit();
            }).catch(function() {
                compressing = false;
                if (button) {
                    button.disabled = false;
                    button.innerHTML = originalLabel;
                }
                showProfileAlert('Não foi possível reduzir a foto. Use uma imagem JPG, PNG ou GIF de até 2MB.');
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>



