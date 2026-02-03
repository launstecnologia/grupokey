<?php

namespace App\Core;

class Router
{
    private $routes = [];
    private $middleware = [];
    private $prefix = '';
    
    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function put($path, $handler)
    {
        $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete($path, $handler)
    {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    private function addRoute($method, $path, $handler)
    {
        // Adicionar prefixo ao path se existir
        $fullPath = $this->prefix . $path;
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler
        ];
    }
    
    public function middleware($middleware)
    {
        $this->middleware[] = $middleware;
        return $this;
    }
    
    public function setPrefix($prefix)
    {
        $this->prefix = rtrim($prefix, '/');
        return $this;
    }
    
    public function getPrefix()
    {
        return $this->prefix;
    }
    
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Suporte para método PUT/DELETE através de _method (method spoofing)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Debug: mostrar informações (apenas em desenvolvimento e CLI)
        if (defined('APP_DEBUG') && APP_DEBUG && php_sapi_name() === 'cli') {
            echo "🔍 Debug Router:\n";
            echo "  - Method original: " . ($_SERVER['REQUEST_METHOD'] ?? 'GET') . "\n";
            echo "  - Method final: " . $method . "\n";
            echo "  - Path original: " . ($_SERVER['REQUEST_URI'] ?? '/') . "\n";
            echo "  - Path após parse: " . $path . "\n";
            echo "  - Prefixo: " . $this->prefix . "\n";
            echo "  - FOLDER: " . (defined('FOLDER') ? FOLDER : 'não definido') . "\n";
        }
        
        // Normalizar o path (remover barras duplicadas)
        $path = '/' . trim($path, '/');
        
        // Usar a constante FOLDER para remover o prefixo da pasta
        // Em produção, FOLDER geralmente é '/' então não precisa remover nada
        if (defined('FOLDER') && FOLDER !== '/' && FOLDER !== '') {
            $folderPath = rtrim(FOLDER, '/');
            // Garantir que o folderPath comece com /
            if ($folderPath && $folderPath[0] !== '/') {
                $folderPath = '/' . $folderPath;
            }
            // Remover o prefixo da pasta se o path começar com ele
            if ($folderPath !== '/' && strpos($path, $folderPath) === 0) {
                $path = substr($path, strlen($folderPath));
                // Garantir que o path comece com /
                if ($path === '' || $path[0] !== '/') {
                    $path = '/' . $path;
                }
            }
        }
        
        // Servir arquivos estáticos diretamente (para servidor PHP embutido)
        if (strpos($path, '/public/') === 0 || strpos($path, '/images/') === 0 || 
            strpos($path, '/css/') === 0 || strpos($path, '/js/') === 0 ||
            strpos($path, '/uploads/') === 0) {
            $filePath = __DIR__ . '/../../' . ltrim($path, '/');
            if (file_exists($filePath) && is_file($filePath)) {
                // Determinar tipo MIME
                $mimeType = 'application/octet-stream';
                if (function_exists('mime_content_type')) {
                    $mimeType = \mime_content_type($filePath) ?: $mimeType;
                } elseif (function_exists('finfo_open')) {
                    $finfo = \finfo_open(FILEINFO_MIME_TYPE);
                    if ($finfo) {
                        $mimeType = \finfo_file($finfo, $filePath) ?: $mimeType;
                        \finfo_close($finfo);
                    }
                }
                if (!$mimeType) {
                    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                    $mimeTypes = [
                        'png' => 'image/png',
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        'svg' => 'image/svg+xml',
                        'css' => 'text/css',
                        'js' => 'application/javascript',
                    ];
                    $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
                }
                header('Content-Type: ' . $mimeType);
                readfile($filePath);
                exit;
            }
        }
        
        // Remover /public/ se estiver na URL (após tentar servir arquivo estático)
        if (strpos($path, '/public/') === 0) {
            $path = substr($path, 7);
        }
        
        $path = $path ?: '/';
        
        // Debug em produção também (via parâmetro GET)
        $debug = isset($_GET['_debug_router']) && defined('APP_DEBUG') && APP_DEBUG;
        
        if ($debug) {
            echo "<pre style='background:#f0f0f0;padding:10px;border:1px solid #ccc;'>";
            echo "🔍 DEBUG ROUTER\n";
            echo "Method: {$method}\n";
            echo "Path original: " . ($_SERVER['REQUEST_URI'] ?? '/') . "\n";
            echo "Path processado: {$path}\n";
            echo "Prefix: {$this->prefix}\n";
            echo "FOLDER: " . (defined('FOLDER') ? FOLDER : 'não definido') . "\n";
            echo "\nRotas disponíveis:\n";
            foreach ($this->routes as $route) {
                $routePath = $route['path'];
                if ($this->prefix && strpos($routePath, $this->prefix) === 0) {
                    $routePath = substr($routePath, strlen($this->prefix));
                }
                echo "  - {$route['method']} {$routePath}\n";
            }
            echo "</pre>";
        }
        
        if (defined('APP_DEBUG') && APP_DEBUG && php_sapi_name() === 'cli') {
            echo "  - Path final: " . $path . "\n";
            echo "  - Comparando: {$method} {$path}\n";
        }
        
        // Separar rotas exatas (sem parâmetros) e rotas com parâmetros
        $exactRoutes = [];
        $paramRoutes = [];
        
        foreach ($this->routes as $route) {
            $routePath = $route['path'];
            if ($this->prefix && strpos($routePath, $this->prefix) === 0) {
                $routePath = substr($routePath, strlen($this->prefix));
            }
            
            // Normalizar routePath
            $routePath = '/' . trim($routePath, '/');
            if ($routePath === '') {
                $routePath = '/';
            }
            
            // Verificar se a rota tem parâmetros dinâmicos
            if (strpos($routePath, '{') !== false) {
                $paramRoutes[] = ['route' => $route, 'path' => $routePath];
            } else {
                $exactRoutes[] = ['route' => $route, 'path' => $routePath];
            }
        }
        
        // Primeiro verificar rotas exatas (sem parâmetros)
        foreach ($exactRoutes as $routeData) {
            $route = $routeData['route'];
            $routePath = $routeData['path'];
            
            if ($debug) {
                echo "<pre style='background:#e8f4f8;padding:5px;margin:5px 0;'>";
                echo "Comparando EXATA: {$route['method']} '{$routePath}' com {$method} '{$path}'\n";
                echo "Match: " . ($route['method'] === $method && $routePath === $path ? 'SIM' : 'NÃO') . "\n";
                echo "</pre>";
            }
            
            if ($route['method'] === $method && $routePath === $path) {
                if ($debug) {
                    echo "<p style='color:green;'>✅ Rota exata encontrada! Executando handler...</p>";
                }
                $this->executeHandler($route['handler'], []);
                return;
            }
        }
        
        // Depois verificar rotas com parâmetros
        foreach ($paramRoutes as $routeData) {
            $route = $routeData['route'];
            $routePath = $routeData['path'];
            
            if ($debug) {
                echo "<pre style='background:#e8f4f8;padding:5px;margin:5px 0;'>";
                echo "Comparando COM PARÂMETROS: {$route['method']} '{$routePath}' com {$method} '{$path}'\n";
                echo "Match: " . ($route['method'] === $method && $this->matchPath($routePath, $path) ? 'SIM' : 'NÃO') . "\n";
                echo "</pre>";
            }
            
            if ($route['method'] === $method && $this->matchPath($routePath, $path)) {
                if ($debug) {
                    echo "<p style='color:green;'>✅ Rota com parâmetros encontrada! Executando handler...</p>";
                }
                $this->executeHandler($route['handler'], $this->extractParams($routePath, $path));
                return;
            }
        }
        
        if ($debug) {
            echo "<p style='color:red;'>❌ Nenhuma rota encontrada. Chamando handleNotFound()...</p>";
        }
        
        $this->handleNotFound();
    }
    
    private function matchPath($routePath, $requestPath)
    {
        $routePattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';
        
        return preg_match($routePattern, $requestPath);
    }
    
    private function extractParams($routePath, $requestPath)
    {
        $routePattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';
        
        preg_match($routePattern, $requestPath, $matches);
        array_shift($matches);
        
        $params = [];
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        
        foreach ($paramNames[1] as $index => $paramName) {
            $params[$paramName] = $matches[$index] ?? null;
        }
        
        return $params;
    }
    
    private function executeHandler($handler, $params = [])
    {
        if (is_string($handler)) {
            [$controller, $method] = explode('@', $handler);
            $controllerClass = "App\\Controllers\\{$controller}";
            
            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();
                if (method_exists($controllerInstance, $method)) {
                    // Passar parâmetros por posição (valores do array) para o método do controller
                    $args = array_values($params);
                    call_user_func_array([$controllerInstance, $method], $args);
                    return;
                }
            }
        }
        
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }
        
        $this->handleNotFound();
    }
    
    private function handleNotFound()
    {
        http_response_code(404);
        
        // Em modo debug, mostrar informações úteis
        if (defined('APP_DEBUG') && APP_DEBUG) {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            
            echo "<!DOCTYPE html>";
            echo "<html><head><title>404 - Página não encontrada</title></head><body>";
            echo "<h1>404 - Página não encontrada</h1>";
            echo "<p><strong>Método:</strong> {$method}</p>";
            echo "<p><strong>Path:</strong> {$path}</p>";
            echo "<p><strong>FOLDER:</strong> " . (defined('FOLDER') ? FOLDER : 'não definido') . "</p>";
            echo "<p><strong>Prefix:</strong> {$this->prefix}</p>";
            echo "<h2>Rotas disponíveis:</h2>";
            echo "<ul>";
            foreach ($this->routes as $route) {
                echo "<li>{$route['method']} {$route['path']}</li>";
            }
            echo "</ul>";
            echo "</body></html>";
        } else {
            echo "<!DOCTYPE html>";
            echo "<html><head><title>404 - Página não encontrada</title></head><body>";
            echo "<h1>404 - Página não encontrada</h1>";
            echo "<p>A página que você está procurando não existe.</p>";
            echo "<p><a href=\"/\">Voltar para a página inicial</a></p>";
            echo "</body></html>";
        }
    }
}
