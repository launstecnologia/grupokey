<?php

/**
 * Sistema de Configuração Totalmente Automático
 * Detecta ambiente pelo domínio e configura automaticamente
 */

namespace App\Core;

if (!class_exists('App\Core\AutoConfig')) {
    class AutoConfig
    {
        private static $config = [];
        private static $initialized = false;
        
        public static function init()
        {
            if (self::$initialized) {
                return;
            }
            
            // Detectar ambiente automaticamente pelo domínio
            $environment = self::detectEnvironment();
            
            // Carregar configurações baseadas no ambiente
            self::loadConfig($environment);
            
            // Definir constantes globais automaticamente
            self::defineGlobalConstants($environment);
            
            // Carregar variáveis de ambiente
            self::loadEnvironmentVariables();
            
            self::$initialized = true;
        }
        
        private static function detectEnvironment()
        {
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            
            // Se executando via CLI, usar localhost por padrão
            if (php_sapi_name() === 'cli') {
                return 'localhost';
            }
            
            // Verificar se é localhost ou IP local
            if (strpos($host, 'localhost') !== false || 
                strpos($host, '127.0.0.1') !== false ||
                strpos($host, '192.168.') !== false ||
                strpos($host, '10.0.') !== false) {
                return 'localhost';
            }
            
            // Qualquer outro domínio é produção
            return 'production';
        }
        
        private static function getBaseUrl()
        {
            // Se executando via CLI, retornar string vazia
            if (php_sapi_name() === 'cli') {
                return '';
            }
            
            // Detectar protocolo
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            
            // Detectar host
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            
            // Detectar porta se não for padrão
            $port = '';
            if (isset($_SERVER['SERVER_PORT']) && 
                (($protocol === 'http' && $_SERVER['SERVER_PORT'] != 80) || 
                 ($protocol === 'https' && $_SERVER['SERVER_PORT'] != 443))) {
                $port = ':' . $_SERVER['SERVER_PORT'];
            }
            
            // Construir URL base
            return $protocol . '://' . $host . $port;
        }
        
        private static function getFolder()
        {
            // Se executando via CLI, retornar /
            if (php_sapi_name() === 'cli') {
                return '/';
            }
            
            // Detectar ambiente primeiro
            $environment = self::detectEnvironment();
            
            // Em produção (grupokey.com.br), sempre retornar / (raiz)
            if ($environment === 'production') {
                return '/';
            }
            
            // Para servidor PHP embutido, sempre retornar / (raiz)
            // O servidor PHP embutido serve da raiz do projeto
            if (isset($_SERVER['SERVER_SOFTWARE'])) {
                $serverSoftware = $_SERVER['SERVER_SOFTWARE'];
                if (strpos($serverSoftware, 'PHP') !== false && 
                    (strpos($serverSoftware, 'Development Server') !== false ||
                     strpos($serverSoftware, 'cli-server') !== false)) {
                    return '/';
                }
            }
            
            // Se SCRIPT_NAME é /index.php ou não está definido, está na raiz
            if (!isset($_SERVER['SCRIPT_NAME']) || $_SERVER['SCRIPT_NAME'] === '/index.php') {
                return '/';
            }
            
            // Detectar pasta base do script
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $scriptDir = dirname($scriptName);
            
            // Se o script está na raiz, retornar /
            if ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') {
                return '/';
            }
            
            // Retornar o diretório do script
            return rtrim($scriptDir, '/') . '/';
        }
        
        private static function loadConfig($environment)
        {
            // Detectar URL e pasta automaticamente
            $baseUrl = self::getBaseUrl();
            $folder = self::getFolder();
            
            // Configurações baseadas no ambiente detectado
            if ($environment === 'production') {
                self::$config = [
                    'FOLDER' => $folder,
                    'URL' => $baseUrl,
                    'APP_DEBUG' => 'false',
                    'DB_HOST' => '72.60.158.222',
                    'DB_PORT' => '3306',
                    'DB_NAME' => 'grup_platform',
                    'DB_USER' => 'grup_platform',
                    'DB_PASS' => '117910Campi!25',
                    'APP_NAME' => 'Sistema CRM',
                    'APP_ENV' => 'production',
                    'MAIL_HOST' => 'smtp.gmail.com',
                    'MAIL_PORT' => '587',
                    'MAIL_USER' => 'seu-email@gmail.com',
                    'MAIL_PASS' => 'sua-senha-app',
                    'MAIL_FROM' => 'seu-email@gmail.com',
                    'MAIL_NAME' => 'Sistema CRM',
                    'MAX_FILE_SIZE' => '10485760',
                    'ALLOWED_EXTENSIONS' => 'jpg,jpeg,png,pdf,doc,docx',
                    'SESSION_TIMEOUT' => '7200',
                    'MAX_LOGIN_ATTEMPTS' => '5',
                    'BACKUP_ENABLED' => 'true',
                    'BACKUP_PATH' => 'storage/backups/',
                    'BACKUP_RETENTION_DAYS' => '30'
                ];
            } else {
                // localhost
                self::$config = [
                    'FOLDER' => $folder,
                    'URL' => $baseUrl,
                    'APP_DEBUG' => 'true',
                    'DB_HOST' => '72.60.158.222',
                    'DB_PORT' => '3306',
                    'DB_NAME' => 'grup_platform',
                    'DB_USER' => 'grup_platform',
                    'DB_PASS' => '117910Campi!25',
                    'APP_NAME' => 'Sistema CRM',
                    'APP_ENV' => 'development',
                    'MAIL_HOST' => 'smtp.gmail.com',
                    'MAIL_PORT' => '587',
                    'MAIL_USER' => 'seu-email@gmail.com',
                    'MAIL_PASS' => 'sua-senha-app',
                    'MAIL_FROM' => 'seu-email@gmail.com',
                    'MAIL_NAME' => 'Sistema CRM',
                    'MAX_FILE_SIZE' => '10485760',
                    'ALLOWED_EXTENSIONS' => 'jpg,jpeg,png,pdf,doc,docx',
                    'SESSION_TIMEOUT' => '7200',
                    'MAX_LOGIN_ATTEMPTS' => '5',
                    'BACKUP_ENABLED' => 'false',
                    'BACKUP_PATH' => 'storage/backups/',
                    'BACKUP_RETENTION_DAYS' => '30'
                ];
            }
        }
        
        private static function defineGlobalConstants($environment)
        {
            // Definir constantes baseadas no ambiente
            define('FOLDER', self::$config['FOLDER']);
            define('URL', self::$config['URL']);
            define('APP_DEBUG', self::$config['APP_DEBUG'] === 'true');
            define('APP_NAME', self::$config['APP_NAME']);
        }
        
        private static function loadEnvironmentVariables()
        {
            // Carregar todas as configurações como variáveis de ambiente
            foreach (self::$config as $key => $value) {
                $_ENV[$key] = $value;
            }
        }
        
        public static function get($key, $default = null)
        {
            return self::$config[$key] ?? $default;
        }
        
        public static function isProduction()
        {
            return self::detectEnvironment() === 'production';
        }
        
        public static function isLocalhost()
        {
            return self::detectEnvironment() === 'localhost';
        }
        
        public static function getEnvironment()
        {
            return self::detectEnvironment();
        }
        
        public static function getAll()
        {
            return self::$config;
        }
    }
}
