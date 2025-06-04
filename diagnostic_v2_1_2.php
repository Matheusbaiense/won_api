<?php
/**
 * Script de Diagnóstico WON API v2.1.2
 * Verifica se todas as funcionalidades estão funcionando corretamente
 * 
 * USO: Acesse via navegador ou execute via CLI
 */

// Verificar se está sendo executado no contexto do Perfex CRM
if (!defined('BASEPATH')) {
    // Definir BASEPATH temporariamente para testes standalone
    define('BASEPATH', '../../../');
    
    // Função auxiliar para get_option (se não estiver disponível)
    if (!function_exists('get_option')) {
        function get_option($name) {
            return 'teste_' . $name;
        }
    }
    
    // Função auxiliar para db_prefix (se não estiver disponível)
    if (!function_exists('db_prefix')) {
        function db_prefix() {
            return 'tbl';
        }
    }
}

/**
 * Classe de diagnóstico da WON API
 */
class WonApiDiagnostic
{
    private $results = [];
    private $errors = [];
    private $warnings = [];
    
    public function __construct()
    {
        $this->check_version();
        $this->check_files();
        $this->check_libraries();
        $this->check_routes();
        $this->check_database_requirements();
        $this->check_php_extensions();
    }
    
    /**
     * Verificar versão e consistência
     */
    private function check_version()
    {
        $this->log_check("Verificação de Versão", "info");
        
        // Verificar arquivos principais
        $main_file = __DIR__ . '/won_api.php';
        if (file_exists($main_file)) {
            $content = file_get_contents($main_file);
            if (strpos($content, "2.1.2") !== false) {
                $this->log_result("✅ Versão 2.1.2 detectada no arquivo principal", "success");
            } else {
                $this->log_result("❌ Versão incorreta no arquivo principal", "error");
            }
        } else {
            $this->log_result("❌ Arquivo principal won_api.php não encontrado", "error");
        }
        
        // Verificar controlador
        $controller_file = __DIR__ . '/controllers/Won.php';
        if (file_exists($controller_file)) {
            $content = file_get_contents($controller_file);
            if (strpos($content, "v2.1.2") !== false) {
                $this->log_result("✅ Versão 2.1.2 detectada no controlador", "success");
            } else {
                $this->log_result("⚠️ Versão pode estar incorreta no controlador", "warning");
            }
        }
    }
    
    /**
     * Verificar arquivos essenciais
     */
    private function check_files()
    {
        $this->log_check("Verificação de Arquivos", "info");
        
        $required_files = [
            'won_api.php' => 'Arquivo principal do módulo',
            'controllers/Won.php' => 'Controlador principal da API',
            'controllers/Won_api.php' => 'Controlador administrativo',
            'libraries/Won_error_handler.php' => 'Gerenciador de erros',
            'libraries/Won_validator.php' => 'Sistema de validação',
            'libraries/Won_operations.php' => 'Operações especializadas',
            'config/routes.php' => 'Configuração de rotas',
            'views/configuracoes.php' => 'Interface de configurações',
            'views/api_documentation.php' => 'Documentação da API',
            'views/logs.php' => 'Interface de logs',
            'tests/api_test_v2_1_2.php' => 'Script de testes',
            'README.md' => 'Documentação principal'
        ];
        
        foreach ($required_files as $file => $description) {
            $full_path = __DIR__ . '/' . $file;
            if (file_exists($full_path)) {
                $size = filesize($full_path);
                $this->log_result("✅ {$description} ({$size} bytes)", "success");
            } else {
                $this->log_result("❌ {$description} - AUSENTE", "error");
            }
        }
    }
    
    /**
     * Verificar bibliotecas auxiliares
     */
    private function check_libraries()
    {
        $this->log_check("Verificação de Bibliotecas", "info");
        
        // Verificar se as bibliotecas podem ser carregadas
        $libraries = [
            'Won_error_handler' => 'libraries/Won_error_handler.php',
            'Won_validator' => 'libraries/Won_validator.php',
            'Won_operations' => 'libraries/Won_operations.php'
        ];
        
        foreach ($libraries as $class_name => $file_path) {
            $full_path = __DIR__ . '/' . $file_path;
            if (file_exists($full_path)) {
                // Tentar incluir e verificar se a classe está definida
                require_once $full_path;
                if (class_exists($class_name)) {
                    $this->log_result("✅ Biblioteca {$class_name} carregada", "success");
                    
                    // Verificar métodos essenciais
                    $reflection = new ReflectionClass($class_name);
                    $methods = $reflection->getMethods();
                    $this->log_result("   📋 {$class_name} tem " . count($methods) . " métodos", "info");
                } else {
                    $this->log_result("❌ Biblioteca {$class_name} não pôde ser carregada", "error");
                }
            } else {
                $this->log_result("❌ Arquivo da biblioteca {$class_name} não encontrado", "error");
            }
        }
    }
    
    /**
     * Verificar configuração de rotas
     */
    private function check_routes()
    {
        $this->log_check("Verificação de Rotas", "info");
        
        $routes_file = __DIR__ . '/config/routes.php';
        if (file_exists($routes_file)) {
            $content = file_get_contents($routes_file);
            
            // Verificar rotas essenciais
            $essential_routes = [
                'won_api/won/api' => 'Rota principal da API',
                'won_api/won/status' => 'Rota de status',
                'won_api/won/join' => 'Rota de busca por CPF/CNPJ',
                'won_api/won/estimate/convert' => 'Conversão de orçamento',
                'won_api/won/dashboard/stats' => 'Estatísticas do dashboard'
            ];
            
            foreach ($essential_routes as $route => $description) {
                if (strpos($content, $route) !== false) {
                    $this->log_result("✅ {$description}", "success");
                } else {
                    $this->log_result("❌ {$description} - NÃO ENCONTRADA", "error");
                }
            }
        } else {
            $this->log_result("❌ Arquivo de rotas não encontrado", "error");
        }
    }
    
    /**
     * Verificar requisitos do banco de dados
     */
    private function check_database_requirements()
    {
        $this->log_check("Verificação de Requisitos do Banco", "info");
        
        // Verificar se as funções do CodeIgniter estão disponíveis
        if (function_exists('get_option')) {
            $this->log_result("✅ Função get_option() disponível", "success");
        } else {
            $this->log_result("⚠️ Função get_option() não disponível (normal em teste standalone)", "warning");
        }
        
        if (function_exists('db_prefix')) {
            $this->log_result("✅ Função db_prefix() disponível", "success");
        } else {
            $this->log_result("⚠️ Função db_prefix() não disponível (normal em teste standalone)", "warning");
        }
        
        // Verificar tabelas que a API vai acessar
        $required_tables = [
            'clients' => 'Tabela de clientes',
            'projects' => 'Tabela de projetos',
            'tasks' => 'Tabela de tarefas',
            'invoices' => 'Tabela de faturas',
            'estimates' => 'Tabela de orçamentos',
            'leads' => 'Tabela de leads',
            'staff' => 'Tabela de funcionários'
        ];
        
        foreach ($required_tables as $table => $description) {
            $this->log_result("📋 {$description} (tbl{$table})", "info");
        }
    }
    
    /**
     * Verificar extensões PHP necessárias
     */
    private function check_php_extensions()
    {
        $this->log_check("Verificação de Extensões PHP", "info");
        
        $required_extensions = [
            'json' => 'Processamento JSON',
            'curl' => 'Requisições HTTP',
            'mbstring' => 'Manipulação de strings',
            'hash' => 'Funções de hash para segurança',
            'openssl' => 'Criptografia (recomendado)'
        ];
        
        foreach ($required_extensions as $extension => $description) {
            if (extension_loaded($extension)) {
                $this->log_result("✅ {$description} ({$extension})", "success");
            } else {
                $level = $extension === 'openssl' ? 'warning' : 'error';
                $this->log_result("❌ {$description} ({$extension}) - NÃO DISPONÍVEL", $level);
            }
        }
        
        // Verificar versão do PHP
        $php_version = PHP_VERSION;
        if (version_compare($php_version, '7.4.0', '>=')) {
            $this->log_result("✅ PHP {$php_version} (compatível)", "success");
        } else {
            $this->log_result("❌ PHP {$php_version} (requer 7.4+)", "error");
        }
    }
    
    /**
     * Registrar verificação
     */
    private function log_check($title, $level)
    {
        $this->results[] = [
            'type' => 'check',
            'title' => $title,
            'level' => $level,
            'timestamp' => date('H:i:s')
        ];
    }
    
    /**
     * Registrar resultado
     */
    private function log_result($message, $level)
    {
        $this->results[] = [
            'type' => 'result',
            'message' => $message,
            'level' => $level,
            'timestamp' => date('H:i:s')
        ];
        
        if ($level === 'error') {
            $this->errors[] = $message;
        } elseif ($level === 'warning') {
            $this->warnings[] = $message;
        }
    }
    
    /**
     * Gerar relatório
     */
    public function generate_report()
    {
        $total_errors = count($this->errors);
        $total_warnings = count($this->warnings);
        $status = $total_errors === 0 ? 'Aprovado' : 'Falhas Encontradas';
        
        echo "<h1>🔍 Diagnóstico WON API v2.1.2</h1>\n";
        echo "<p><strong>Status:</strong> {$status} | ";
        echo "<strong>Erros:</strong> {$total_errors} | ";
        echo "<strong>Avisos:</strong> {$total_warnings}</p>\n";
        echo "<hr>\n";
        
        foreach ($this->results as $result) {
            if ($result['type'] === 'check') {
                echo "<h3>📋 {$result['title']}</h3>\n";
            } else {
                echo "<p>{$result['message']}</p>\n";
            }
        }
        
        if ($total_errors > 0) {
            echo "<hr><h3>🚨 Erros Críticos:</h3>\n";
            foreach ($this->errors as $error) {
                echo "<p style='color: red;'>{$error}</p>\n";
            }
        }
        
        if ($total_warnings > 0) {
            echo "<hr><h3>⚠️ Avisos:</h3>\n";
            foreach ($this->warnings as $warning) {
                echo "<p style='color: orange;'>{$warning}</p>\n";
            }
        }
        
        if ($total_errors === 0) {
            echo "<hr><h3>🎉 Parabéns!</h3>\n";
            echo "<p>A WON API v2.1.2 passou em todas as verificações críticas!</p>\n";
        }
    }
}

// Executar diagnóstico
$diagnostic = new WonApiDiagnostic();

// Output HTML
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Diagnóstico WON API v2.1.2</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        h3 { color: #666; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        p { margin: 5px 0; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>";

$diagnostic->generate_report();

echo "</body></html>";
?> 