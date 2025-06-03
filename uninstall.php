<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Script de Desinstalação do Módulo WON API
 * 
 * Remove todas as configurações, tabelas e arquivos criados pelo módulo
 */

$CI = &get_instance();
$CI->load->database();

try {
    log_message('info', '[Won API] Iniciando processo de desinstalação...');

    // Verificar se a conexão com o banco de dados está funcionando
    if (!$CI->db->conn_id) {
        log_message('error', '[Won API] Falha ao conectar ao banco de dados durante a desinstalação.');
        show_error('Erro ao conectar ao banco de dados. Verifique as configurações do banco.');
        return false;
    }

    // Remover o módulo da tabela de módulos
    if ($CI->db->table_exists(db_prefix() . 'modules')) {
        $CI->db->where('module_name', 'won_api');
        if ($CI->db->delete(db_prefix() . 'modules')) {
            log_message('info', '[Won API] Módulo removido da tabela de módulos com sucesso.');
        } else {
            $error = $CI->db->error();
            log_message('error', '[Won API] Erro ao remover módulo da tabela: ' . $error['message']);
        }
    }

    // Remover tabela de logs
    $logs_table = db_prefix() . 'won_api_logs';
    if ($CI->db->table_exists($logs_table)) {
        if ($CI->db->query('DROP TABLE `' . $logs_table . '`')) {
            log_message('info', '[Won API] Tabela de logs removida com sucesso.');
        } else {
            $error = $CI->db->error();
            log_message('error', '[Won API] Erro ao remover tabela de logs: ' . $error['message']);
        }
    }

    // Remover todas as opções de configuração relacionadas ao módulo
    $won_api_options = [
        'won_api_token',
        'won_api_rate_limit',
        'won_api_cache_duration',
        'won_api_log_level',
        'won_api_cors_enabled',
        'won_api_cors_origins',
        'won_api_cors_methods',
        'won_api_cors_headers',
        'won_api_whitelist_tables',
        'won_api_max_page_size',
        'won_api_default_page_size',
        'won_api_cache_enabled',
        'won_api_debug_mode',
        'won_api_require_https',
        'won_api_allowed_ips',
        'won_api_webhook_url',
        'won_api_webhook_secret'
    ];

    foreach ($won_api_options as $option_name) {
        $CI->db->where('name', $option_name);
        if ($CI->db->delete(db_prefix() . 'options')) {
            log_message('info', '[Won API] Opção ' . $option_name . ' removida com sucesso.');
        }
    }

    // Remover arquivos de cache relacionados ao módulo
    $cache_patterns = [
        APPPATH . 'logs/cache/won_api_*',
        APPPATH . 'logs/cache/api_*'
    ];

    foreach ($cache_patterns as $pattern) {
        $cache_files = glob($pattern);
        foreach ($cache_files as $file) {
            if (is_file($file)) {
                @unlink($file);
                log_message('info', '[Won API] Arquivo de cache removido: ' . $file);
            }
        }
    }

    // Remover arquivos de log específicos do módulo (se existirem)
    $log_files = [
        FCPATH . 'modules/won_api/logs/api_access.log',
        FCPATH . 'modules/won_api/logs/api_errors.log',
        FCPATH . 'modules/won_api/logs/api_debug.log'
    ];

    foreach ($log_files as $log_file) {
        if (file_exists($log_file)) {
            @unlink($log_file);
            log_message('info', '[Won API] Arquivo de log removido: ' . $log_file);
        }
    }

    // Remover arquivo de configuração se existir
    $config_file = FCPATH . 'modules/won_api/config/won_api_config.php';
    if (file_exists($config_file)) {
        @unlink($config_file);
        log_message('info', '[Won API] Arquivo de configuração removido.');
    }

    // Limpar cache geral do sistema
    if (is_dir(APPPATH . 'logs/cache')) {
        $cache_files = glob(APPPATH . 'logs/cache/*');
        foreach ($cache_files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        log_message('info', '[Won API] Cache do sistema limpo.');
    }

    // Remover permissões específicas do módulo (se suportado)
    $module_permissions = [
        'view_api_logs',
        'manage_api_settings', 
        'regenerate_api_token'
    ];

    // Tentar remover permissões se a tabela existir
    if ($CI->db->table_exists(db_prefix() . 'staff_permissions')) {
        foreach ($module_permissions as $permission) {
            $CI->db->where('feature', $permission);
            $CI->db->delete(db_prefix() . 'staff_permissions');
        }
        log_message('info', '[Won API] Permissões específicas do módulo removidas.');
    }

    log_message('info', '[Won API] Desinstalação concluída com sucesso.');
    
    // Se chegou até aqui, a desinstalação foi bem-sucedida
    return true;

} catch (Exception $e) {
    log_message('error', '[Won API] Erro durante a desinstalação: ' . $e->getMessage());
    // Não exibir erro fatal durante desinstalação, apenas registrar
    log_message('error', '[Won API] A desinstalação pode não ter sido completamente bem-sucedida.');
    return false;
}