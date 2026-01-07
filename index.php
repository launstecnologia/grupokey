<?php
// ===========================================
// Method Spoofing para PUT/DELETE
// ===========================================
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar HTTP_X_METHOD_OVERRIDE (header)
    if (isset($_SERVER['HTTP_X_METHOD_OVERRIDE'])) {
        $method = strtoupper($_SERVER['HTTP_X_METHOD_OVERRIDE']);
        if (in_array($method, ['PUT', 'DELETE', 'PATCH'])) {
            $_SERVER['REQUEST_METHOD'] = $method;
        }
    }
    // Verificar _method (campo do formulário)
    elseif (isset($_POST['_method'])) {
        $method = strtoupper($_POST['_method']);
        if (in_array($method, ['PUT', 'DELETE', 'PATCH'])) {
            $_SERVER['REQUEST_METHOD'] = $method;
        }
    }
}

try {
    // Carregar autoloader do Composer
    require_once __DIR__ . '/vendor/autoload.php';

    // Carregar sistema de configuração automático
    require_once __DIR__ . '/app/Core/AutoConfig.php';
    App\Core\AutoConfig::init();

    // Iniciar sessão
    session_start();

    // Carregar helpers
    require_once __DIR__ . '/app/helpers.php';

    // Carregar rotas
    require_once __DIR__ . '/config/routes.php';

} catch (Exception $e) {
    // Em caso de erro, mostrar página de erro amigável
    http_response_code(500);
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Erro do Sistema</title></head><body>";
    echo "<h1>Erro Interno do Sistema</h1>";
    echo "<p>Ocorreu um erro ao carregar o sistema. Detalhes técnicos:</p>";
    echo "<pre>";
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . " linha " . $e->getLine() . "\n";
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo "Stack trace:\n" . $e->getTraceAsString();
    }
    echo "</pre>";
    echo "</body></html>";
    exit;
} catch (Error $e) {
    // Em caso de erro fatal
    http_response_code(500);
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Erro Fatal</title></head><body>";
    echo "<h1>Erro Fatal do Sistema</h1>";
    echo "<p>Ocorreu um erro fatal. Detalhes técnicos:</p>";
    echo "<pre>";
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . " linha " . $e->getLine() . "\n";
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo "Stack trace:\n" . $e->getTraceAsString();
    }
    echo "</pre>";
    echo "</body></html>";
    exit;
}