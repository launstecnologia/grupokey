<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir senha - Sistema CRM</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: #01195b;
            min-height: 100vh;
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="mx-auto md:h-screen flex flex-col justify-center items-center px-6 pt-8">
        <!-- Logo -->
        <div class="text-2xl font-semibold flex justify-center items-center mb-8 lg:mb-10">
            <?php $logoUrl = url('public/images/logo-white.png'); ?>
            <img src="<?= $logoUrl ?>" class="h-24 w-auto" alt="GRUPO Key Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="hidden items-center">
                <span class="text-white text-3xl font-bold">GRUPO</span>
                <span class="text-cyan-400 text-3xl font-bold ml-2">KEY</span>
            </div>
        </div>
        
        <!-- Form Card -->
        <div class="form-container bg-white shadow-2xl rounded-lg w-full sm:max-w-md">
            <div class="p-6 sm:p-8 lg:p-12 space-y-6">
                <div class="text-center">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">
                        Redefinir senha
                    </h2>
                    <p class="text-gray-600">
                        Digite sua nova senha abaixo
                    </p>
                </div>
                
                <!-- Flash messages -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                        </div>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span><?= htmlspecialchars($_SESSION['success']) ?></span>
                        </div>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <form method="POST" action="<?= url('reset-password') ?>" class="space-y-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                    
                    <div>
                        <label for="password" class="text-sm font-medium text-gray-900 block mb-2">
                            <i class="fas fa-lock mr-1"></i>
                            Nova senha
                        </label>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" 
                               placeholder="Mínimo 6 caracteres"
                               required>
                        <p class="mt-1 text-xs text-gray-500">A senha deve ter pelo menos 6 caracteres</p>
                    </div>
                    
                    <div>
                        <label for="password_confirm" class="text-sm font-medium text-gray-900 block mb-2">
                            <i class="fas fa-lock mr-1"></i>
                            Confirmar nova senha
                        </label>
                        <input type="password" 
                               name="password_confirm" 
                               id="password_confirm" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" 
                               placeholder="Digite a senha novamente"
                               required>
                    </div>
                    
                    <button type="submit" class="text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-base px-5 py-3 w-full">
                        <i class="fas fa-key mr-2"></i>
                        Redefinir senha
                    </button>
                    
                    <div class="text-center">
                        <a href="<?= url('login') ?>" class="text-sm text-cyan-600 hover:underline">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Voltar para o login
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8 text-white opacity-75">
            <small>
                <i class="fas fa-info-circle mr-1"></i>
                Acesso restrito a usuários autorizados
            </small>
        </div>
    </div>
    
    <script>
        // Validação de senha
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            
            if (password.length < 6) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 6 caracteres.');
                return;
            }
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('As senhas não coincidem.');
                return;
            }
        });
    </script>
</body>
</html>

