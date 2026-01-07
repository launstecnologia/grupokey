<?php

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $statusCode = 302)
    {
        // Limpar qualquer output buffer e output já enviado
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Se já houve output, limpar
        if (headers_sent()) {
            // Se os headers já foram enviados, usar JavaScript para redirect
            echo "<script>window.location.href = '$url';</script>";
            echo "<noscript><meta http-equiv='refresh' content='0;url=$url'></noscript>";
            exit;
        }
        
        header("Location: $url", true, $statusCode);
        exit;
    }
}

if (!function_exists('view')) {
    function view($template, $data = [])
    {
        extract($data);
        $templatePath = __DIR__ . "/Views/{$template}.php";
        
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            throw new Exception("Template não encontrado: {$template}");
        }
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify()
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Token CSRF inválido');
        }
    }
}

if (!function_exists('sanitize_input')) {
    function sanitize_input($data)
    {
        if (is_array($data)) {
            return array_map('sanitize_input', $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url($path = '')
    {
        // Sempre retornar path relativo (sem URL completa)
        // Isso funciona tanto para servidor PHP embutido quanto para Apache/Nginx
        $folder = defined('FOLDER') ? FOLDER : '';
        
        // Garantir que não há barras duplas
        $path = ltrim($path, '/');
        $folder = rtrim($folder, '/');
        
        // Se há pasta e não é raiz, incluir na URL
        if ($folder && $folder !== '/') {
            if ($path) {
                return $folder . '/' . $path;
            }
            return $folder . '/';
        }
        
        // Retornar path relativo simples
        if ($path) {
            return '/' . $path;
        }
        
        return '/';
    }
}

if (!function_exists('asset')) {
    function asset($path)
    {
        return url($path);
    }
}

if (!function_exists('format_currency')) {
    function format_currency($value)
    {
        return 'R$ ' . number_format((float)$value, 2, ',', '.');
    }
}

if (!function_exists('format_cep')) {
    function format_cep($cep)
    {
        if (empty($cep)) {
            return '-';
        }
        
        // Remover caracteres não numéricos
        $cep = preg_replace('/\D/', '', $cep);
        
        // Se tiver 8 dígitos, formatar como 00000-000
        if (strlen($cep) === 8) {
            return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
        }
        
        // Se não tiver 8 dígitos, retornar como está
        return $cep;
    }
}

if (!function_exists('parse_currency')) {
    function parse_currency($value)
    {
        // Remove R$, espaços e converte vírgula para ponto
        $value = preg_replace('/[^\d,]/', '', $value);
        $value = str_replace(',', '.', $value);
        return (float)$value;
    }
}

if (!function_exists('format_cpf')) {
    function format_cpf($cpf)
    {
        if (empty($cpf)) {
            return '';
        }
        
        // Remove tudo que não é número
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verifica se tem 11 dígitos
        if (strlen($cpf) !== 11) {
            return $cpf; // Retorna sem formatação se não tiver 11 dígitos
        }
        
        // Formata: 000.000.000-00
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
}

if (!function_exists('format_cnpj')) {
    function format_cnpj($cnpj)
    {
        if (empty($cnpj)) {
            return '';
        }
        
        // Remove tudo que não é número
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Verifica se tem 14 dígitos
        if (strlen($cnpj) !== 14) {
            return $cnpj; // Retorna sem formatação se não tiver 14 dígitos
        }
        
        // Formata: 00.000.000/0000-00
        return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
    }
}

if (!function_exists('format_phone')) {
    function format_phone($phone)
    {
        if (empty($phone)) {
            return '';
        }
        
        // Remove tudo que não é número
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove zeros à esquerda se houver
        $phone = ltrim($phone, '0');
        
        // Formata baseado no número de dígitos
        $length = strlen($phone);
        
        if ($length === 10) {
            // Telefone fixo: (00) 0000-0000
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6, 4);
        } elseif ($length === 11) {
            // Celular: (00) 00000-0000
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7, 4);
        } else {
            // Retorna sem formatação se não tiver 10 ou 11 dígitos
            return $phone;
        }
    }
}

if (!function_exists('get_status_badge')) {
    function get_status_badge($status)
    {
        if (empty($status)) {
            return '<span class="badge bg-secondary">Desconhecido</span>';
        }
        
        $status = strtoupper($status);
        $badgeClass = 'bg-secondary';
        $statusText = $status;
        
        // Mapeamento de status para classes e textos
        $statusMap = [
            // Status de Representantes/Usuários
            'ACTIVE' => ['class' => 'bg-success', 'text' => 'Ativo'],
            'INACTIVE' => ['class' => 'bg-danger', 'text' => 'Inativo'],
            'BLOCKED' => ['class' => 'bg-warning', 'text' => 'Bloqueado'],
            
            // Status de Estabelecimentos
            'PENDING' => ['class' => 'bg-warning', 'text' => 'Pendente'],
            'APPROVED' => ['class' => 'bg-success', 'text' => 'Aprovado'],
            'REPROVED' => ['class' => 'bg-danger', 'text' => 'Reprovado'],
            'DISABLED' => ['class' => 'bg-secondary', 'text' => 'Desabilitado'],
            
            // Status de Tickets/Chamados
            'OPEN' => ['class' => 'bg-primary', 'text' => 'Aberto'],
            'IN_PROGRESS' => ['class' => 'bg-info', 'text' => 'Em Andamento'],
            'RESOLVED' => ['class' => 'bg-success', 'text' => 'Resolvido'],
            'CLOSED' => ['class' => 'bg-secondary', 'text' => 'Fechado'],
            
            // Status em português
            'ABERTO' => ['class' => 'bg-primary', 'text' => 'Aberto'],
            'EM ANDAMENTO' => ['class' => 'bg-info', 'text' => 'Em Andamento'],
            'RESOLVIDO' => ['class' => 'bg-success', 'text' => 'Resolvido'],
            'FECHADO' => ['class' => 'bg-secondary', 'text' => 'Fechado'],
        ];
        
        if (isset($statusMap[$status])) {
            $badgeClass = $statusMap[$status]['class'];
            $statusText = $statusMap[$status]['text'];
        }
        
        return '<span class="badge ' . htmlspecialchars($badgeClass) . '">' . htmlspecialchars($statusText) . '</span>';
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'd/m/Y H:i')
    {
        if (empty($date) || $date === '0000-00-00 00:00:00' || $date === '0000-00-00') {
            return '-';
        }
        
        try {
            $dateTime = new DateTime($date);
            return $dateTime->format($format);
        } catch (Exception $e) {
            return $date; // Retorna a data original se houver erro
        }
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime($date, $format = 'd/m/Y H:i')
    {
        if (empty($date) || $date === '0000-00-00 00:00:00' || $date === '0000-00-00') {
            return '-';
        }
        
        try {
            $dateTime = new DateTime($date);
            return $dateTime->format($format);
        } catch (Exception $e) {
            return $date; // Retorna a data original se houver erro
        }
    }
}

if (!function_exists('format_file_size')) {
    function format_file_size($bytes, $precision = 2)
    {
        if (empty($bytes) || $bytes == 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('get_file_icon')) {
    function get_file_icon($fileType)
    {
        $icons = [
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc' => 'fas fa-file-word text-primary',
            'docx' => 'fas fa-file-word text-primary',
            'xls' => 'fas fa-file-excel text-success',
            'xlsx' => 'fas fa-file-excel text-success',
            'ppt' => 'fas fa-file-powerpoint text-warning',
            'pptx' => 'fas fa-file-powerpoint text-warning',
            'jpg' => 'fas fa-file-image text-info',
            'jpeg' => 'fas fa-file-image text-info',
            'png' => 'fas fa-file-image text-info',
            'gif' => 'fas fa-file-image text-info',
            'zip' => 'fas fa-file-archive text-secondary',
            'rar' => 'fas fa-file-archive text-secondary',
            'txt' => 'fas fa-file-alt text-muted',
            'csv' => 'fas fa-file-csv text-success',
        ];
        
        $fileTypeLower = strtolower($fileType ?? '');
        return $icons[$fileTypeLower] ?? 'fas fa-file text-secondary';
    }
}

/**
 * Função para salvar logs em arquivo customizado
 */
if (!function_exists('write_log')) {
    function write_log($message, $logFile = 'app.log')
    {
        $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
        
        // Criar diretório se não existir
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logPath = $logDir . $logFile;
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        // Escrever no arquivo de log customizado
        file_put_contents($logPath, $logMessage, FILE_APPEND | LOCK_EX);
        
        // Também escrever no error_log padrão do PHP
        error_log($message);
    }
}

if (!function_exists('log_audit')) {
    function log_audit($action, $module, $recordId, $recordType = null)
    {
        try {
            $db = \App\Core\Database::getInstance();
            
            // Verificar se a tabela existe
            $checkTable = "SHOW TABLES LIKE 'audit_logs'";
            $tableExists = $db->fetch($checkTable);
            
            if (!$tableExists) {
                // Tabela não existe, retornar silenciosamente
                if (defined('APP_DEBUG') && APP_DEBUG) {
                    error_log("Tabela audit_logs não existe. Execute a migração: database/migrations/002_create_audit_logs_table.sql");
                }
                return false;
            }
            
            // Determinar se é user_id ou representative_id
            $userId = null;
            $representativeId = null;
            
            if ($recordType === 'User' || $recordType === 'admin') {
                $userId = $recordId;
            } elseif ($recordType === 'Representative' || $recordType === 'representative') {
                $representativeId = $recordId;
            } else {
                // Tentar determinar automaticamente baseado no tipo
                // Se não especificado, assumir que é user_id
                $userId = $recordId;
            }
            
            // Capturar IP e User Agent
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Inserir log de auditoria
            $sql = "INSERT INTO audit_logs 
                    (action, module, record_id, record_type, user_id, representative_id, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $db->query($sql, [
                $action,
                $module,
                $recordId,
                $recordType,
                $userId,
                $representativeId,
                $ipAddress,
                $userAgent
            ]);
            
            return true;
        } catch (Exception $e) {
            // Em caso de erro, não quebrar o fluxo da aplicação
            // Apenas logar o erro se debug estiver ativo
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("Erro ao registrar log de auditoria: " . $e->getMessage());
            }
            return false;
        } catch (\PDOException $e) {
            // Erro de banco de dados (tabela não existe, etc)
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("Erro PDO ao registrar log de auditoria: " . $e->getMessage());
            }
            return false;
        }
    }
}

if (!function_exists('write_log')) {
    /**
     * Escreve uma mensagem de log em arquivo
     * 
     * @param string $message Mensagem a ser logada
     * @param string $logFile Nome do arquivo de log (padrão: 'app.log')
     */
    function write_log($message, $logFile = 'app.log')
    {
        $logDir = __DIR__ . '/../storage/logs';
        
        // Criar diretório se não existir
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logPath = $logDir . '/' . $logFile;
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        // Escrever no arquivo (append mode)
        file_put_contents($logPath, $logMessage, FILE_APPEND | LOCK_EX);
        
        // Também escrever no error_log padrão do PHP para compatibilidade
        error_log($message);
    }
}

if (!function_exists('old')) {
    /**
     * Recupera um valor antigo do formulário da sessão
     * 
     * @param string $key Chave do campo
     * @param mixed $default Valor padrão se não existir
     * @return mixed
     */
    function old($key, $default = '')
    {
        if (isset($_SESSION['old_input'][$key])) {
            $value = $_SESSION['old_input'][$key];
            // Limpar o valor da sessão após usar (opcional, pode manter para múltiplos usos)
            return $value;
        }
        return $default;
    }
}
