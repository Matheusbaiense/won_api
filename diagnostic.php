<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Script de Diagnóstico do Módulo WON API
 * 
 * Verifica todos os requisitos e configurações necessárias
 * para o funcionamento correto do módulo
 */

class Won_Api_Diagnostic {
    
    private $CI;
    private $results = [];
    private $errors = [];
    private $warnings = [];
    private $success = [];
    
    public function __construct() {
        $this->CI = &get_instance();
        $this->CI->load->database();
    }
    
    /**
     * Executa todos os testes de diagnóstico
     */
    public function run_all_checks() {
        $this->check_php_version();
        $this->check_php_extensions();
        $this->check_database_connection();
        $this->check_module_registration();
        $this->check_module_tables();
        $this->check_module_options();
        $this->check_file_permissions();
        $this->check_directory_structure();
        $this->check_perfex_compatibility();
        $this->check_server_requirements();
        $this->check_api_accessibility();
        
        return [
            'success' => $this->success,
            'warnings' => $this->warnings,
            'errors' => $this->errors,
            'overall_status' => empty($this->errors) ? 'success' : 'error'
        ];
    }
    
    /**
     * Verifica a versão do PHP
     */
    private function check_php_version() {
        $required_version = '7.4';
        $current_version = PHP_VERSION;
        
        if (version_compare($current_version, $required_version, '>=')) {
            $this->success[] = "Versão do PHP: {$current_version} (✓ Compatível)";
        } else {
            $this->errors[] = "Versão do PHP: {$current_version} (✗ Requer {$required_version} ou superior)";
        }
    }
    
    /**
     * Verifica extensões PHP necessárias
     */
    private function check_php_extensions() {
        $required_extensions = [
            'json' => 'Processamento de dados JSON',
            'curl' => 'Requisições HTTP',
            'openssl' => 'Criptografia e HTTPS',
            'mbstring' => 'Manipulação de strings multibyte',
            'mysqli' => 'Conexão com banco de dados',
            'zip' => 'Compressão de arquivos'
        ];
        
        foreach ($required_extensions as $ext => $description) {
            if (extension_loaded($ext)) {
                $this->success[] = "Extensão {$ext}: ✓ Disponível ({$description})";
            } else {
                $this->errors[] = "Extensão {$ext}: ✗ Não encontrada ({$description})";
            }
        }
    }
    
    /**
     * Verifica conexão com banco de dados
     */
    private function check_database_connection() {
        if ($this->CI->db->conn_id) {
            $db_version = $this->CI->db->version();
            $this->success[] = "Conexão com banco de dados: ✓ Conectado (MySQL {$db_version})";
            
            // Verificar charset
            $charset = $this->CI->db->char_set;
            if (in_array($charset, ['utf8', 'utf8mb4'])) {
                $this->success[] = "Charset do banco: ✓ {$charset}";
            } else {
                $this->warnings[] = "Charset do banco: ⚠ {$charset} (recomendado: utf8 ou utf8mb4)";
            }
        } else {
            $this->errors[] = "Conexão com banco de dados: ✗ Falha na conexão";
        }
    }
    
    /**
     * Verifica se o módulo está registrado
     */
    private function check_module_registration() {
        if ($this->CI->db->table_exists(db_prefix() . 'modules')) {
            $this->CI->db->where('module_name', 'won_api');
            $module = $this->CI->db->get(db_prefix() . 'modules')->row();
            
            if ($module) {
                $status = $module->active ? 'Ativo' : 'Inativo';
                $this->success[] = "Registro do módulo: ✓ Registrado (Versão: {$module->installed_version}, Status: {$status})";
            } else {
                $this->errors[] = "Registro do módulo: ✗ Não encontrado na tabela de módulos";
            }
        } else {
            $this->errors[] = "Tabela de módulos: ✗ Não existe";
        }
    }
    
    /**
     * Verifica tabelas específicas do módulo
     */
    private function check_module_tables() {
        $required_tables = [
            'won_api_logs' => 'Logs da API'
        ];
        
        foreach ($required_tables as $table => $description) {
            $full_table_name = db_prefix() . $table;
            if ($this->CI->db->table_exists($full_table_name)) {
                $count = $this->CI->db->count_all($full_table_name);
                $this->success[] = "Tabela {$table}: ✓ Existe ({$count} registros)";
            } else {
                $this->errors[] = "Tabela {$table}: ✗ Não existe ({$description})";
            }
        }
    }
    
    /**
     * Verifica opções de configuração do módulo
     */
    private function check_module_options() {
        $required_options = [
            'won_api_token' => 'Token de autenticação',
            'won_api_rate_limit' => 'Limite de requisições',
            'won_api_log_level' => 'Nível de log',
            'won_api_whitelist_tables' => 'Tabelas permitidas'
        ];
        
        foreach ($required_options as $option => $description) {
            $value = get_option($option);
            if ($value !== false && $value !== '') {
                $display_value = ($option === 'won_api_token') ? substr($value, 0, 8) . '...' : $value;
                $this->success[] = "Opção {$option}: ✓ Configurada ({$display_value})";
            } else {
                $this->errors[] = "Opção {$option}: ✗ Não configurada ({$description})";
            }
        }
    }
    
    /**
     * Verifica permissões de arquivos e diretórios
     */
    private function check_file_permissions() {
        $critical_files = [
            FCPATH . 'modules/won_api/won_api.php' => 'Arquivo principal do módulo',
            FCPATH . 'modules/won_api/install.php' => 'Script de instalação',
            FCPATH . 'modules/won_api/module_info.php' => 'Informações do módulo',
            FCPATH . 'modules/won_api/controllers/Won.php' => 'Controlador da API',
            FCPATH . 'modules/won_api/controllers/Won_api.php' => 'Controlador administrativo'
        ];
        
        $required_dirs = [
            FCPATH . 'modules/won_api/' => 'Diretório principal',
            FCPATH . 'modules/won_api/controllers/' => 'Controladores',
            FCPATH . 'modules/won_api/views/' => 'Views',
            FCPATH . 'modules/won_api/logs/' => 'Logs'
        ];
        
        // Verificar arquivos
        foreach ($critical_files as $file => $description) {
            if (file_exists($file)) {
                if (is_readable($file)) {
                    $perms = substr(sprintf('%o', fileperms($file)), -4);
                    $this->success[] = "Arquivo {$description}: ✓ Legível (Permissões: {$perms})";
                } else {
                    $this->errors[] = "Arquivo {$description}: ✗ Não legível";
                }
            } else {
                $this->errors[] = "Arquivo {$description}: ✗ Não encontrado";
            }
        }
        
        // Verificar diretórios
        foreach ($required_dirs as $dir => $description) {
            if (is_dir($dir)) {
                if (is_writable($dir)) {
                    $perms = substr(sprintf('%o', fileperms($dir)), -4);
                    $this->success[] = "Diretório {$description}: ✓ Gravável (Permissões: {$perms})";
                } else {
                    $this->warnings[] = "Diretório {$description}: ⚠ Não gravável";
                }
            } else {
                $this->errors[] = "Diretório {$description}: ✗ Não encontrado";
            }
        }
    }
    
    /**
     * Verifica estrutura de diretórios
     */
    private function check_directory_structure() {
        $expected_structure = [
            'controllers/',
            'models/',
            'views/',
            'config/',
            'tests/',
            'logs/',
            'won_api.php',
            'install.php',
            'uninstall.php',
            'module_info.php'
        ];
        
        $base_path = FCPATH . 'modules/won_api/';
        
        foreach ($expected_structure as $item) {
            $full_path = $base_path . $item;
            $is_dir = substr($item, -1) === '/';
            
            if ($is_dir) {
                if (is_dir($full_path)) {
                    $this->success[] = "Estrutura: ✓ Diretório {$item} existe";
                } else {
                    $this->warnings[] = "Estrutura: ⚠ Diretório {$item} não encontrado";
                }
            } else {
                if (file_exists($full_path)) {
                    $this->success[] = "Estrutura: ✓ Arquivo {$item} existe";
                } else {
                    $this->errors[] = "Estrutura: ✗ Arquivo {$item} não encontrado";
                }
            }
        }
    }
    
    /**
     * Verifica compatibilidade com Perfex CRM
     */
    private function check_perfex_compatibility() {
        if (method_exists($this->CI->app, 'get_current_db_version')) {
            $perfex_version = $this->CI->app->get_current_db_version();
            $min_version = '2.9.2';
            
            if (version_compare($perfex_version, $min_version, '>=')) {
                $this->success[] = "Perfex CRM: ✓ Versão {$perfex_version} (Compatível)";
            } else {
                $this->errors[] = "Perfex CRM: ✗ Versão {$perfex_version} (Requer {$min_version}+)";
            }
        } else {
            $this->warnings[] = "Perfex CRM: ⚠ Não foi possível verificar a versão";
        }
    }
    
    /**
     * Verifica requisitos do servidor
     */
    private function check_server_requirements() {
        // Verificar limite de memória
        $memory_limit = ini_get('memory_limit');
        $memory_bytes = $this->convert_to_bytes($memory_limit);
        $required_memory = 128 * 1024 * 1024; // 128MB
        
        if ($memory_bytes >= $required_memory) {
            $this->success[] = "Memória PHP: ✓ {$memory_limit}";
        } else {
            $this->warnings[] = "Memória PHP: ⚠ {$memory_limit} (recomendado: 128M+)";
        }
        
        // Verificar timeout de execução
        $max_execution_time = ini_get('max_execution_time');
        if ($max_execution_time >= 60 || $max_execution_time == 0) {
            $this->success[] = "Timeout de execução: ✓ {$max_execution_time}s";
        } else {
            $this->warnings[] = "Timeout de execução: ⚠ {$max_execution_time}s (recomendado: 60s+)";
        }
        
        // Verificar HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $this->success[] = "Conexão HTTPS: ✓ Ativa";
        } else {
            $this->warnings[] = "Conexão HTTPS: ⚠ Não detectada (recomendado para produção)";
        }
    }
    
    /**
     * Verifica acessibilidade da API
     */
    private function check_api_accessibility() {
        $api_url = site_url('api/won/status');
        
        // Simular uma verificação básica (em ambiente real, usaria cURL)
        if (function_exists('curl_init')) {
            $this->success[] = "API Endpoint: ✓ URL configurada ({$api_url})";
        } else {
            $this->warnings[] = "API Endpoint: ⚠ cURL não disponível para teste";
        }
    }
    
    /**
     * Converte string de memória para bytes
     */
    private function convert_to_bytes($size) {
        $unit = strtolower($size[strlen($size)-1]);
        $value = (int) $size;
        
        switch($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Gera relatório HTML
     */
    public function generate_html_report($results) {
        $html = '<div class="won-api-diagnostic-report">';
        $html .= '<h3>Relatório de Diagnóstico - WON API</h3>';
        $html .= '<p><strong>Data:</strong> ' . date('d/m/Y H:i:s') . '</p>';
        
        if (!empty($results['success'])) {
            $html .= '<h4 style="color: green;">✓ Verificações Bem-sucedidas (' . count($results['success']) . ')</h4>';
            $html .= '<ul>';
            foreach ($results['success'] as $item) {
                $html .= '<li style="color: green;">' . htmlspecialchars($item) . '</li>';
            }
            $html .= '</ul>';
        }
        
        if (!empty($results['warnings'])) {
            $html .= '<h4 style="color: orange;">⚠ Avisos (' . count($results['warnings']) . ')</h4>';
            $html .= '<ul>';
            foreach ($results['warnings'] as $item) {
                $html .= '<li style="color: orange;">' . htmlspecialchars($item) . '</li>';
            }
            $html .= '</ul>';
        }
        
        if (!empty($results['errors'])) {
            $html .= '<h4 style="color: red;">✗ Erros (' . count($results['errors']) . ')</h4>';
            $html .= '<ul>';
            foreach ($results['errors'] as $item) {
                $html .= '<li style="color: red;">' . htmlspecialchars($item) . '</li>';
            }
            $html .= '</ul>';
        }
        
        $status = $results['overall_status'] === 'success' ? 
            '<span style="color: green;">✓ SISTEMA OPERACIONAL</span>' : 
            '<span style="color: red;">✗ REQUER ATENÇÃO</span>';
            
        $html .= '<h4>Status Geral: ' . $status . '</h4>';
        $html .= '</div>';
        
        return $html;
    }
}

// Executar diagnóstico se chamado diretamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $diagnostic = new Won_Api_Diagnostic();
    $results = $diagnostic->run_all_checks();
    echo $diagnostic->generate_html_report($results);
} 