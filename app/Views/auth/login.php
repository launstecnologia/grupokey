<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema CRM</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= url('public/images/favicon.png') ?>">
    <link rel="shortcut icon" type="image/png" href="<?= url('public/images/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= url('public/images/favicon.png') ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Windster CSS -->
    <link rel="stylesheet" href="<?= url('css/windster.css') ?>">
    
    <style>
        body {
            background: #01195b;
            min-height: 100vh;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .input-field {
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.15);
        }
        
        .btn-login {
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.3);
        }
        
        .alert {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-container {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-container {
            animation: fadeInUp 0.8s ease-out;
        }
    </style>
</head>
<body>
    <div class="mx-auto min-h-screen flex flex-col justify-center items-center px-6 py-8 md:py-12">
        <!-- Logo -->
        <div class="logo-container text-2xl font-semibold flex justify-center items-center mb-8 lg:mb-12 mt-8 md:mt-12">
            <?php $logoUrl = url('public/images/logo-white.png'); ?>
            <img src="<?= $logoUrl ?>" class="h-40 md:h-48 lg:h-56 w-auto" alt="GRUPO Key Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="hidden items-center">
                <span class="text-white text-5xl md:text-6xl lg:text-7xl font-bold">GRUPO</span>
                <span class="text-cyan-400 text-5xl md:text-6xl lg:text-7xl font-bold ml-2">KEY</span>
            </div>
        </div>
        
        <!-- Login Card -->
        <div class="login-container bg-white shadow-2xl rounded-lg md:mt-0 w-full sm:max-w-screen-sm xl:p-0">
            <div class="p-6 sm:p-8 lg:p-16 space-y-8">
                <div class="text-center">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">
                        Faça login na plataforma
                    </h2>
                    <p class="text-gray-600">
                        Sistema unificado para Administradores e Representantes
                    </p>
                </div>
                
                <!-- Flash messages -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span><?= $_SESSION['error'] ?></span>
                        </div>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span><?= $_SESSION['success'] ?></span>
                        </div>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <form class="form-container mt-8 space-y-6" method="POST" action="<?= url('login') ?>">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label for="email" class="text-sm font-medium text-gray-900 block mb-2">
                            <i class="fas fa-envelope mr-1"></i>
                            Seu e-mail
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               class="input-field bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" 
                               placeholder="seu@email.com" 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required>
                    </div>
                    
                    <div>
                        <label for="password" class="text-sm font-medium text-gray-900 block mb-2">
                            <i class="fas fa-lock mr-1"></i>
                            Sua senha
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   placeholder="••••••••" 
                                   class="input-field bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5 pr-10" 
                                   required>
                            <button type="button" 
                                    id="togglePassword" 
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none"
                                    aria-label="Mostrar/Ocultar senha">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="remember" 
                                   aria-describedby="remember" 
                                   name="remember" 
                                   type="checkbox" 
                                   class="bg-gray-50 border-gray-300 focus:ring-3 focus:ring-cyan-200 h-4 w-4 rounded">
                        </div>
                        <div class="text-sm ml-3">
                            <label for="remember" class="font-medium text-gray-900">Lembrar de mim</label>
                        </div>
                        <a href="<?= url('forgot-password') ?>" class="text-sm text-cyan-600 hover:underline ml-auto">
                            <i class="fas fa-key mr-1"></i>
                            Esqueci minha senha
                        </a>
                    </div>
                    
                    <button type="submit" class="btn-login text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-base px-5 py-3 w-full sm:w-auto text-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Entrar na sua conta
                    </button>
                    
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-500">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Sistema seguro e confiável
                        </div>
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
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
        
        // Focus on email field
        document.getElementById('email').focus();
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                showAlert('Por favor, preencha todos os campos.', 'error');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('A senha deve ter pelo menos 6 caracteres.', 'error');
                return;
            }
            
            // Show loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Entrando...';
            submitBtn.disabled = true;
            
            // Re-enable button after 3 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });
        
        // Show alert function
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${type === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700'} border px-4 py-3 rounded-lg`;
            alertDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;
            
            const form = document.querySelector('form');
            form.insertBefore(alertDiv, form.firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Add enter key support
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const form = document.querySelector('form');
                form.dispatchEvent(new Event('submit'));
            }
        });
        
        // Add visual feedback for form fields
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-cyan-200');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-cyan-200');
            });
        });
        
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            if (type === 'password') {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        });
    </script>
</body>
</html>