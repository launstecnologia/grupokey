<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistema CRM' ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= url('public/images/favicon.png') ?>">
    <link rel="shortcut icon" type="image/png" href="<?= url('public/images/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= url('public/images/favicon.png') ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Windster CSS -->
    <link rel="stylesheet" href="<?= url('public/css/windster.css') ?>">
    
    <style>
        /* Custom styles for CRM */
        .toggle-bg:after {
            content: '';
            @apply absolute left-0.5 top-0.5 bg-white border border-gray-300 h-5 w-5 rounded-full ring-0 transition;
        }
        
        input:checked + .toggle-bg:after {
            transform: translateX(100%);
            @apply border-white;
        }
        
        input:checked + .toggle-bg {
            @apply bg-cyan-600 border-cyan-600;
        }
        
        [type=checkbox], [type=radio]{
            @apply text-cyan-600;
        }
        
        /* Custom badge colors */
        .badge-pending {
            @apply bg-yellow-100 text-yellow-800;
        }
        .badge-approved {
            @apply bg-green-100 text-green-800;
        }
        .badge-reproved {
            @apply bg-red-100 text-red-800;
        }
        .badge-disabled {
            @apply bg-gray-600 text-gray-800;
        }
        
        /* Dark mode styles */
        .dark {
            color-scheme: dark;
        }
        
        /* Background colors */
        .dark .bg-white {
            background-color: #111827 !important;
        }
        
        .dark .bg-gray-50 {
            background-color: #1f2937 !important;
        }
        
        /* Product cards in dark mode - ensure dark background */
        .dark .product-card-dark {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
        }
        
        /* Force product cards to be dark in dark mode */
        .dark .product-card-dark.bg-gray-50 {
            background-color: #1f2937 !important;
        }
        
        .dark .bg-gray-600 {
            background-color: #374151 !important;
        }
        
        .dark .bg-gray-200 {
            background-color: #4b5563 !important;
        }
        
        /* Text colors */
        .dark .text-white {
            color: #f9fafb !important;
        }
        
        .dark .text-gray-800 {
            color: #f3f4f6 !important;
        }
        
        .dark .text-gray-700 {
            color: #e5e7eb !important;
        }
        
        .dark .text-gray-600 {
            color: #d1d5db !important;
        }
        
        .dark .text-white {
            color: #9ca3af !important;
        }
        
        .dark .text-gray-400 {
            color: #9ca3af !important;
        }
        
        /* Headings and Titles - White in dark mode */
        .dark h1, .dark h2, .dark h3, .dark h4, .dark h5, .dark h6 {
            color: #ffffff !important;
        }
        
        .dark .text-gray-900 {
            color: #ffffff !important;
        }
        
        .dark .text-gray-800 {
            color: #ffffff !important;
        }
        
        .dark .text-gray-700 {
            color: #e5e7eb !important;
        }
        
        /* Labels and form elements */
        .dark label, .dark .form-label {
            color: #ffffff !important;
        }
        
        /* Links and buttons text */
        .dark a:not(.btn):not(.badge) {
            color: #93c5fd !important;
        }
        
        .dark a:hover:not(.btn):not(.badge) {
            color: #bfdbfe !important;
        }
        
        /* Border colors */
        .dark .border-gray-200 {
            border-color: #374151 !important;
        }
        
        .dark .border-gray-300 {
            border-color: #4b5563 !important;
        }
        
        /* WhatsApp Atendimento: lista de conversas - hover escuro (preto) */
        .dark .conversation-item {
            background-color: transparent !important;
        }
        .dark .conversation-item:hover {
            background-color: #111827 !important;
        }
        /* Tag de fila (ex: "Geral") no tema escuro */
        .dark .queue-tag-whatsapp {
            background-color: #374151 !important;
            color: #9ca3af !important;
        }
        
        /* WhatsApp chat: texto das mensagens em branco (bolhas verdes e conteúdo) */
        .chat-msg-bubble.chat-msg-sent,
        .chat-msg-bubble.chat-msg-sent * {
            color: #ffffff !important;
        }
        
        /* Hover states */
        .dark .hover\:bg-gray-600:hover {
            background-color: #374151 !important;
        }
        
        .dark .hover\:text-white:hover {
            color: #f9fafb !important;
        }
        
        /* Cards */
        .dark .card {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
        }
        
        .dark .card-header {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
        }
        
        /* Tables */
        .dark .table {
            color: #f3f4f6 !important;
        }
        
        .dark .table-light {
            background-color: #374151 !important;
        }
        
        .dark .table-hover tbody tr:hover {
            background-color: #374151 !important;
        }
        
        /* Forms */
        .dark .form-control {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
            color: #ffffff !important;
        }
        
        .dark .form-control:focus {
            border-color: #06b6d4 !important;
            box-shadow: 0 0 0 0.2rem rgba(6, 182, 212, 0.25) !important;
            color: #ffffff !important;
            background-color: #374151 !important;
        }
        
        .dark .form-select {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
            color: #ffffff !important;
        }
        
        .dark .form-select:focus {
            border-color: #06b6d4 !important;
            box-shadow: 0 0 0 0.2rem rgba(6, 182, 212, 0.25) !important;
            color: #ffffff !important;
            background-color: #374151 !important;
        }
        
        .dark .form-label {
            color: #ffffff !important;
        }
        
        .dark .form-text {
            color: #d1d5db !important;
        }
        
        /* Input fields (Tailwind classes) */
        .dark input[type="text"],
        .dark input[type="email"],
        .dark input[type="tel"],
        .dark input[type="number"],
        .dark input[type="password"],
        .dark input[type="date"],
        .dark input[type="time"],
        .dark input[type="datetime-local"],
        .dark textarea,
        .dark select {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
            color: #ffffff !important;
        }
        
        .dark input[type="text"]:focus,
        .dark input[type="email"]:focus,
        .dark input[type="tel"]:focus,
        .dark input[type="number"]:focus,
        .dark input[type="password"]:focus,
        .dark input[type="date"]:focus,
        .dark input[type="time"]:focus,
        .dark input[type="datetime-local"]:focus,
        .dark textarea:focus,
        .dark select:focus {
            background-color: #374151 !important;
            border-color: #06b6d4 !important;
            color: #ffffff !important;
            outline: none;
        }
        
        .dark input[type="text"]::placeholder,
        .dark input[type="email"]::placeholder,
        .dark input[type="tel"]::placeholder,
        .dark input[type="number"]::placeholder,
        .dark input[type="password"]::placeholder,
        .dark textarea::placeholder {
            color: #9ca3af !important;
        }
        
        /* Labels */
        .dark label,
        .dark .form-label,
        .dark dt {
            color: #ffffff !important;
        }
        
        /* Input values */
        .dark input[type="text"],
        .dark input[type="email"],
        .dark input[type="tel"],
        .dark input[type="number"],
        .dark input[type="password"],
        .dark textarea,
        .dark select option {
            color: #ffffff !important;
        }
        
        .dark select {
            background-color: #374151 !important;
            color: #ffffff !important;
        }
        
        .dark select option {
            background-color: #374151 !important;
            color: #ffffff !important;
        }
        
        /* Description lists */
        .dark dd {
            color: #ffffff !important;
        }
        
        .dark dt {
            color: #d1d5db !important;
        }
        
        /* Alerts */
        .dark .alert-success {
            background-color: #064e3b !important;
            border-color: #065f46 !important;
            color: #a7f3d0 !important;
        }
        
        .dark .alert-danger {
            background-color: #7f1d1d !important;
            border-color: #991b1b !important;
            color: #fecaca !important;
        }
        
        .dark .alert-warning {
            background-color: #78350f !important;
            border-color: #92400e !important;
            color: #fde68a !important;
        }
        
        .dark .alert-info {
            background-color: #1e3a8a !important;
            border-color: #1e40af !important;
            color: #bfdbfe !important;
        }
        
        /* Animação para mensagens de sucesso */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-slide-down {
            animation: slideDown 0.3s ease-out;
        }
        
        /* Badges */
        .dark .badge {
            color: #f9fafb !important;
        }
        
        .dark .badge-primary {
            background-color: #2563eb !important;
        }
        
        .dark .badge-success {
            background-color: #16a34a !important;
        }
        
        .dark .badge-danger {
            background-color: #dc2626 !important;
        }
        
        .dark .badge-warning {
            background-color: #d97706 !important;
        }
        
        .dark .badge-info {
            background-color: #0891b2 !important;
        }
        
        .dark .badge-secondary {
            background-color: #4b5563 !important;
        }
        
        /* Buttons */
        .dark .btn-primary {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
        }
        
        .dark .btn-primary:hover {
            background-color: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
        }
        
        .dark .btn-secondary {
            background-color: #4b5563 !important;
            border-color: #4b5563 !important;
        }
        
        .dark .btn-secondary:hover {
            background-color: #374151 !important;
            border-color: #374151 !important;
        }
        
        .dark .btn-success {
            background-color: #16a34a !important;
            border-color: #16a34a !important;
        }
        
        .dark .btn-success:hover {
            background-color: #15803d !important;
            border-color: #15803d !important;
        }
        
        .dark .btn-danger {
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
        }
        
        .dark .btn-danger:hover {
            background-color: #b91c1c !important;
            border-color: #b91c1c !important;
        }
        
        .dark .btn-warning {
            background-color: #d97706 !important;
            border-color: #d97706 !important;
        }
        
        .dark .btn-warning:hover {
            background-color: #b45309 !important;
            border-color: #b45309 !important;
        }
        
        .dark .btn-info {
            background-color: #0891b2 !important;
            border-color: #0891b2 !important;
        }
        
        .dark .btn-info:hover {
            background-color: #0e7490 !important;
            border-color: #0e7490 !important;
        }
        
        /* Outline buttons */
        .dark .btn-outline-primary {
            border-color: #2563eb !important;
            color: #60a5fa !important;
        }
        
        .dark .btn-outline-primary:hover {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }
        
        .dark .btn-outline-secondary {
            border-color: #4b5563 !important;
            color: #9ca3af !important;
        }
        
        .dark .btn-outline-secondary:hover {
            background-color: #4b5563 !important;
            color: #ffffff !important;
        }
        
        .dark .btn-outline-success {
            border-color: #16a34a !important;
            color: #86efac !important;
        }
        
        .dark .btn-outline-success:hover {
            background-color: #16a34a !important;
            color: #ffffff !important;
        }
        
        .dark .btn-outline-danger {
            border-color: #dc2626 !important;
            color: #fca5a5 !important;
        }
        
        .dark .btn-outline-danger:hover {
            background-color: #dc2626 !important;
            color: #ffffff !important;
        }
        
        .dark .btn-outline-warning {
            border-color: #d97706 !important;
            color: #fbbf24 !important;
        }
        
        .dark .btn-outline-warning:hover {
            background-color: #d97706 !important;
            color: #ffffff !important;
        }
        
        .dark .btn-outline-info {
            border-color: #0891b2 !important;
            color: #67e8f9 !important;
        }
        
        .dark .btn-outline-info:hover {
            background-color: #0891b2 !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="border-b border-gray-200 fixed z-30 w-full" style="background-color: #01195b;">
        <!-- Primeira linha: Logo, Busca e Informações do Usuário -->
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start">
                    <button id="toggleSidebarMobile" aria-expanded="true" aria-controls="sidebar" class="lg:hidden mr-2 text-white hover:text-gray-200 cursor-pointer p-2 hover:bg-gray-600 focus:bg-gray-600 focus:ring-2 focus:ring-gray-600 rounded">
                        <svg id="toggleSidebarMobileHamburger" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <svg id="toggleSidebarMobileClose" class="w-6 h-6 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Notificações -->
                    <div class="relative" id="notifications-container">
                        <button type="button" id="notifications-button" class="relative p-2 text-white hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-600 rounded">
                            <i class="fas fa-bell text-xl"></i>
                            <span id="notification-badge" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full hidden">0</span>
                        </button>
                        <!-- Dropdown de Notificações -->
                        <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-50 border border-gray-200 dark:border-gray-700" style="max-height: 400px; overflow-y: auto;">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notificações</h3>
                                <button onclick="markAllNotificationsRead()" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Marcar todas como lidas</button>
                            </div>
                            <div id="notifications-list" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-spinner fa-spin"></i> Carregando...
                                </div>
                            </div>
                            <div class="p-2 border-t border-gray-200 dark:border-gray-700 text-center">
                                <a href="<?= url('crm') ?>" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Ver todas</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dark mode toggle -->
                    <button id="darkModeToggle" type="button" class="text-white hover:text-white hover:bg-gray-600 dark:text-gray-400 dark:hover:text-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-200 flex items-center justify-center">
                        <span class="sr-only">Alternar modo escuro</span>
                        <!-- Sun icon (visible in dark mode) -->
                        <svg id="sunIcon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                        <!-- Moon icon (visible in light mode) -->
                        <svg id="moonIcon" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>
                    
                    <!-- Search mobile -->
                    <button id="toggleSidebarMobileSearch" type="button" class="lg:hidden text-white hover:text-white hover:bg-gray-600 dark:text-gray-400 dark:hover:text-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                        <span class="sr-only">Search</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    
                    <!-- User dropdown -->
                    <div class="flex items-center ml-3">
                        <div class="flex items-center">
                            <div class="flex items-center ml-3">
                                <div class="flex items-center">
                                    <span class="text-sm font-medium text-white mr-2">
                                        <?php
                                        $user = App\Core\Auth::user();
                                        $representative = App\Core\Auth::representative();
                                        echo $user['name'] ?? $representative['name'] ?? 'Usuário';
                                        ?>
                                    </span>
                                    <?php if (App\Core\Auth::isAdmin()): ?>
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Admin</span>
                                    <?php else: ?>
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Representante</span>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-3 relative">
                                    <div>
                                        <button type="button" class="flex text-sm rounded-full focus:ring-4 focus:ring-gray-300" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                            <span class="sr-only">Open user menu</span>
                                            <?php 
                                            $currentUser = $user ?? $representative ?? [];
                                            
                                            // Se não tiver foto na sessão, buscar do banco
                                            if (empty($currentUser['photo'])) {
                                                if (App\Core\Auth::isAdmin() && !empty($user['id'])) {
                                                    $userModel = new \App\Models\User();
                                                    $fullUser = $userModel->findById($user['id']);
                                                    if ($fullUser && !empty($fullUser['photo'])) {
                                                        $currentUser['photo'] = $fullUser['photo'];
                                                        $_SESSION['user_photo'] = $fullUser['photo'];
                                                    }
                                                } elseif (App\Core\Auth::isRepresentative() && !empty($representative['id'])) {
                                                    $repModel = new \App\Models\Representative();
                                                    $fullRep = $repModel->findById($representative['id']);
                                                    if ($fullRep && !empty($fullRep['photo'])) {
                                                        $currentUser['photo'] = $fullRep['photo'];
                                                        $_SESSION['representative_photo'] = $fullRep['photo'];
                                                    }
                                                }
                                            }
                                            
                                            $headerPhotoPath = null;
                                            $headerFullPath = null;
                                            if (!empty($currentUser['photo'])) {
                                                $headerPhotoPath = url('public/uploads/profiles/' . $currentUser['photo']);
                                                $headerFullPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR . $currentUser['photo'];
                                            }
                                            ?>
                                            <div class="h-8 w-8 rounded-full bg-cyan-600 flex items-center justify-center overflow-hidden">
                                                <?php if ($headerPhotoPath && isset($headerFullPath) && file_exists($headerFullPath)): ?>
                                                    <img src="<?= $headerPhotoPath ?>" alt="Foto do perfil" class="h-full w-full object-cover">
                                                <?php else: ?>
                                                    <span class="text-sm font-medium text-white">
                                                        <?php
                                                        $displayName = $user['name'] ?? $representative['nome_completo'] ?? 'U';
                                                        echo strtoupper(substr($displayName, 0, 1));
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </button>
                                    </div>
                                    <div class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button">
                                        <a href="<?= url('perfil') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-600" role="menuitem">
                                            <i class="fas fa-user mr-2"></i>Meu Perfil
                                        </a>
                                        <a href="<?= url('logout') ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-600" role="menuitem">
                                            <i class="fas fa-sign-out-alt mr-2"></i>Sair
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>


    <!-- Sidebar -->
    <aside id="sidebar" class="fixed hidden z-20 left-0 flex lg:flex flex-shrink-0 flex-col w-64 transition-width duration-75" aria-label="Sidebar" style="top: 64px; height: calc(100% - 64px);">
        <div class="relative flex-1 flex flex-col min-h-0 border-r border-gray-200 pt-0" style="background-color: #01195b;">
            <div class="flex-1 flex flex-col pt-2 pb-4 overflow-y-auto">
                <div class="flex-1 px-3" style="background-color: #01195b;">
                    <!-- Logo no topo do sidebar -->
                    <div class="mb-2 px-2 pt-2">
                        <a href="<?= url('dashboard') ?>" class="flex items-center justify-center">
                            <?php $logoUrl = url('public/images/logo-white.png'); ?>
                            <!-- Logo para modo claro -->
                            <img id="logoLight" src="<?= $logoUrl ?>" class="h-32 w-auto" alt="GRUPO Key Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <!-- Logo para modo escuro -->
                            <img id="logoDark" src="<?= $logoUrl ?>" class="h-32 w-auto hidden" alt="GRUPO Key Logo" onerror="this.style.display='none';"> 
                        </a>
                    </div>
                    <ul class="space-y-2 pb-2">
                        <?php if (App\Core\Auth::isAdmin()): ?>
                        <li>
                            <a href="<?= url('crm') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'crm' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">CRM</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li>
                            <a href="<?= url('dashboard') ?>" class="text-base text-white font-normal rounded-lg flex items-center p-2 hover:bg-gray-600 group <?= $currentPage === 'dashboard' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                                    <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                                </svg>
                                <span class="ml-3">Dashboard</span>
                            </a>
                        </li>
                        
                        <?php if (App\Core\Auth::isAdmin()): ?>
                        <li>
                            <a href="<?= url('estabelecimentos') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'estabelecimentos' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-6a1 1 0 00-1-1H9a1 1 0 00-1 1v6a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">Estabelecimentos</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('representantes') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'representantes' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">Representantes</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('usuarios') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'usuarios' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">Usuários</span>
                            </a>
                        </li>
                        <?php else: ?>
                        <li>
                            <a href="<?= url('estabelecimentos') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'clientes' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-6a1 1 0 00-1-1H9a1 1 0 00-1 1v6a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">Meus Clientes</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('estabelecimentos/create') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'novo-cliente' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">Novo Cliente</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li>
                            <a href="<?= url('chamados') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'chamados' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-2 0c0 .993-.241 1.929-.668 2.754l-1.524-1.525a3.997 3.997 0 00.078-2.183l1.562-1.562C15.802 8.249 16 9.1 16 10zm-5.165 3.913l1.58 1.58A5.98 5.98 0 0110 16a5.976 5.976 0 01-2.516-.552l1.562-1.562a4.006 4.006 0 001.789.027zm-4.677-2.796a4.002 4.002 0 01-.041-2.08l-.08.08-1.53-1.533A5.98 5.98 0 004 10c0 .954.223 1.856.619 2.657l1.54-1.54zm1.088-6.45A5.974 5.974 0 0110 4c.954 0 1.856.223 2.657.619l-1.54 1.54a4.002 4.002 0 00-2.346.033L7.246 4.668zM12 10a2 2 0 11-4 0 2 2 0 014 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">Chamados</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="<?= url('material') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'material' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">Material de Apoio</span>
                            </a>
                        </li>
                        
                        <?php if (App\Core\Auth::isAdmin()): ?>
                        <li>
                            <a href="<?= url('segmentos') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'segmentos' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">Segmentos</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('billing') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'billing' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a1 1 0 100-2 1 1 0 000 2zm0 2a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">Faturamento</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('whatsapp/attendance') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= in_array($currentPage ?? '', ['whatsapp', 'whatsapp-attendance']) ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.386 1.262.617 1.694.789.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">WhatsApp</span>
                            </a>
                        </li>
                        <?php if (App\Core\Auth::isAdmin()): ?>
                        <li>
                            <a href="<?= url('whatsapp/instances') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'whatsapp-instances' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">Instâncias WhatsApp</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li>
                            <a href="<?= url('email-marketing') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'email-marketing' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">E-mail Marketing</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('email-settings') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'configuracoes' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">Configurações Email</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('sistpay-settings') ?>" class="text-base text-white font-normal rounded-lg hover:bg-gray-600 flex items-center p-2 group <?= $currentPage === 'configuracoes' ? 'bg-gray-600' : '' ?>">
                                <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:text-white transition duration-75" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-3 flex-1 whitespace-nowrap">API SistPay</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                    </ul>
                </div>
            </div>
        </div>
    </aside>

    <div class="bg-gray-900 opacity-50 hidden fixed inset-0 z-10" id="sidebarBackdrop"></div>

    <!-- Main content -->
    <div class="flex overflow-hidden bg-white pt-16">
        <div id="main-content" class="h-full w-full bg-gray-50 relative overflow-y-auto lg:ml-64">
            <main>
                <!-- Flash messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-lg shadow-lg relative m-4 animate-slide-down" role="alert">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-lg"><?= $_SESSION['success'] ?></p>
                            </div>
                            <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.parentElement.style.display='none'">
                                <svg class="fill-current h-6 w-6 text-green-500 hover:text-green-700 transition-colors" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <title>Fechar</title>
                                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative m-4" role="alert">
                        <span class="block sm:inline">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?= $_SESSION['error'] ?>
                        </span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.style.display='none'">
                            <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Close</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </span>
                    </div>
                    
                    <?php if (isset($_SESSION['debug_error'])): ?>
                        <div class="bg-gray-600 border border-gray-400 text-gray-700 px-4 py-3 rounded relative m-4" role="alert">
                            <details class="cursor-pointer">
                                <summary class="font-bold">
                                    <i class="fas fa-bug mr-2"></i>
                                    Detalhes técnicos do erro (clique para expandir)
                                </summary>
                                <div class="mt-2 text-sm">
                                    <p><strong>Código do erro:</strong> <?= $_SESSION['debug_error']['code'] ?></p>
                                    <p><strong>Mensagem:</strong> <?= htmlspecialchars($_SESSION['debug_error']['message']) ?></p>
                                    <p><strong>Arquivo:</strong> <?= $_SESSION['debug_error']['file'] ?></p>
                                    <p><strong>Linha:</strong> <?= $_SESSION['debug_error']['line'] ?></p>
                                </div>
                            </details>
                        </div>
                        <?php unset($_SESSION['debug_error']); ?>
                    <?php endif; ?>
                    
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['warning'])): ?>
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative m-4" role="alert">
                        <span class="block sm:inline">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?= $_SESSION['warning'] ?>
                        </span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.style.display='none'">
                            <svg class="fill-current h-6 w-6 text-yellow-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Close</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </span>
                    </div>
                    <?php unset($_SESSION['warning']); ?>
                <?php endif; ?>
                
                <!-- Page content -->
                <div class="<?= in_array($currentPage ?? '', ['dashboard', 'estabelecimentos', 'billing']) ? '' : 'pt-4' ?>">
                    <?= $content ?? '' ?>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Windster JS -->
    <script src="<?= url('public/js/windster.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= url('public/js/sidebar.js') ?>"></script>
    
    <!-- Dark Mode Script -->
    <script>
        // Dark mode functionality
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const sunIcon = document.getElementById('sunIcon');
            const moonIcon = document.getElementById('moonIcon');
            const htmlElement = document.documentElement;
            
            // Check for saved theme preference or default to light mode
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = savedTheme === 'dark' || (!savedTheme && prefersDark);
            
            // Apply theme on page load
            function applyTheme(isDarkMode) {
                if (isDarkMode) {
                    htmlElement.classList.add('dark');
                    sunIcon.classList.remove('hidden');
                    moonIcon.classList.add('hidden');
                    // Mostrar logo escuro e esconder logo claro (no sidebar)
                    const sidebarLogoDark = document.getElementById('logoDark');
                    const sidebarLogoLight = document.getElementById('logoLight');
                    if (sidebarLogoDark) {
                        sidebarLogoDark.classList.remove('hidden');
                        sidebarLogoDark.style.display = 'block';
                    }
                    if (sidebarLogoLight) {
                        sidebarLogoLight.classList.add('hidden');
                        sidebarLogoLight.style.display = 'none';
                    }
                    // Logo no header
                    const headerLogoDark = document.getElementById('headerLogoDark');
                    const headerLogoLight = document.getElementById('headerLogoLight');
                    if (headerLogoDark) {
                        headerLogoDark.classList.remove('hidden');
                        headerLogoDark.style.display = 'block';
                    }
                    if (headerLogoLight) {
                        headerLogoLight.classList.add('hidden');
                        headerLogoLight.style.display = 'none';
                    }
                } else {
                    htmlElement.classList.remove('dark');
                    sunIcon.classList.add('hidden');
                    moonIcon.classList.remove('hidden');
                    // Mostrar logo claro e esconder logo escuro (no sidebar)
                    const sidebarLogoDark = document.getElementById('logoDark');
                    const sidebarLogoLight = document.getElementById('logoLight');
                    if (sidebarLogoLight) {
                        sidebarLogoLight.classList.remove('hidden');
                        sidebarLogoLight.style.display = 'block';
                    }
                    if (sidebarLogoDark) {
                        sidebarLogoDark.classList.add('hidden');
                        sidebarLogoDark.style.display = 'none';
                    }
                    // Logo no header
                    const headerLogoDark = document.getElementById('headerLogoDark');
                    const headerLogoLight = document.getElementById('headerLogoLight');
                    if (headerLogoLight) {
                        headerLogoLight.classList.remove('hidden');
                        headerLogoLight.style.display = 'block';
                    }
                    if (headerLogoDark) {
                        headerLogoDark.classList.add('hidden');
                        headerLogoDark.style.display = 'none';
                    }
                }
            }
            
            // Initialize theme
            applyTheme(isDark);
            
            // Toggle theme on button click
            if (darkModeToggle && sunIcon && moonIcon) {
                darkModeToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const isCurrentlyDark = htmlElement.classList.contains('dark');
                    const newTheme = isCurrentlyDark ? 'light' : 'dark';
                    
                    // Save preference
                    localStorage.setItem('theme', newTheme);
                    
                    // Apply new theme
                    applyTheme(newTheme === 'dark');
                    
                    return false;
                }, false);
            } else {
                console.error('Dark mode elements not found!', {
                    toggle: !!darkModeToggle,
                    sun: !!sunIcon,
                    moon: !!moonIcon
                });
            }
            
            // Listen for system theme changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem('theme')) {
                    applyTheme(e.matches);
                }
            });
        });
    </script>
    
    <!-- Custom JS -->
    <script>
        // Auto-hide alerts - mensagens de sucesso ficam por 8 segundos
        setTimeout(function() {
            const successAlerts = document.querySelectorAll('[role="alert"].bg-green-100, [role="alert"].bg-green-50');
            successAlerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 8000);
        
        // Erros ficam por 10 segundos
        setTimeout(function() {
            const errorAlerts = document.querySelectorAll('[role="alert"].bg-red-100, [role="alert"].bg-red-50');
            errorAlerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 10000);
        
        // Confirm delete actions
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
                if (!confirm('Tem certeza que deseja excluir este item?')) {
                    e.preventDefault();
                }
            }
        });
        
        // Format currency inputs
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('currency-input')) {
                let value = e.target.value.replace(/\D/g, '');
                value = (value / 100).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
                e.target.value = value;
            }
        });
        
        // User dropdown toggle
        document.getElementById('user-menu-button').addEventListener('click', function() {
            const menu = document.querySelector('[role="menu"]');
            menu.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const button = document.getElementById('user-menu-button');
            const menu = document.querySelector('[role="menu"]');
            if (!button.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">    </script>
    
    <!-- Script de Notificações -->
    <script>
    let notificationsCheckInterval;
    
    document.addEventListener('DOMContentLoaded', function() {
        const notificationsButton = document.getElementById('notifications-button');
        const notificationsDropdown = document.getElementById('notifications-dropdown');
        
        // Toggle dropdown
        if (notificationsButton) {
            notificationsButton.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationsDropdown.classList.toggle('hidden');
                if (!notificationsDropdown.classList.contains('hidden')) {
                    loadNotifications();
                }
            });
        }
        
        // Fechar ao clicar fora
        document.addEventListener('click', function(e) {
            if (!notificationsButton.contains(e.target) && !notificationsDropdown.contains(e.target)) {
                notificationsDropdown.classList.add('hidden');
            }
        });
        
        // Carregar notificações a cada 30 segundos
        loadNotifications();
        notificationsCheckInterval = setInterval(loadNotifications, 30000);
    });
    
    function loadNotifications() {
        fetch('<?= url('crm/notifications') ?>')
            .then(response => response.json())
            .then(data => {
                updateNotificationBadge(data.unread_count);
                renderNotifications(data.notifications);
            })
            .catch(error => {
                console.error('Erro ao carregar notificações:', error);
            });
    }
    
    function updateNotificationBadge(count) {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }
    
    function renderNotifications(notifications) {
        const list = document.getElementById('notifications-list');
        if (!list) return;
        
        if (notifications.length === 0) {
            list.innerHTML = '<div class="p-4 text-center text-gray-500 dark:text-gray-400">Nenhuma notificação</div>';
            return;
        }
        
        list.innerHTML = notifications.map(notif => `
            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer ${notif.is_read ? '' : 'bg-blue-50 dark:bg-blue-900/20'}" 
                 onclick="markNotificationRead(${notif.id})">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-${notif.type === 'TASK_REMINDER' ? 'bell' : 'info-circle'} text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${escapeHtml(notif.title)}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">${escapeHtml(notif.message)}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">${formatDate(notif.created_at)}</p>
                    </div>
                    ${!notif.is_read ? '<div class="ml-2 w-2 h-2 bg-blue-600 rounded-full"></div>' : ''}
                </div>
            </div>
        `).join('');
    }
    
    function markNotificationRead(id) {
        fetch('<?= url('crm/notifications') ?>/' + id + '/read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                _token: '<?= csrf_token() ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        })
        .catch(error => {
            console.error('Erro ao marcar notificação como lida:', error);
        });
    }
    
    function markAllNotificationsRead() {
        fetch('<?= url('crm/notifications/read-all') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                _token: '<?= csrf_token() ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        })
        .catch(error => {
            console.error('Erro ao marcar todas como lidas:', error);
        });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);
        
        if (diff < 60) return 'Agora';
        if (diff < 3600) return `${Math.floor(diff / 60)} min atrás`;
        if (diff < 86400) return `${Math.floor(diff / 3600)} h atrás`;
        if (diff < 604800) return `${Math.floor(diff / 86400)} dias atrás`;
        
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }
    </script>
</body>
</html>