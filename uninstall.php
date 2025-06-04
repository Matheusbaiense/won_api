<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Script de Desinstalação WON API v2.1.0 - Otimizado
 */

$CI = &get_instance();
$CI->load->database();

try {
    log_message('info', '[Won API] Iniciando desinstalação...');

    // 1. Remover módulo da tabela de módulos
    if ($CI->db->table_exists(db_prefix() . 'modules')) {
        $CI->db->where('module_name', 'won_api');
        $CI->db->delete(db_prefix() . 'modules');
        log_message('info', '[Won API] Módulo removido da tabela');
    }

    // 2. Remover tabela de logs
    $logs_table = db_prefix() . 'won_api_logs';
    if ($CI->db->table_exists($logs_table)) {
        $CI->db->query('DROP TABLE `' . $logs_table . '`');
        log_message('info', '[Won API] Tabela de logs removida');
    }

    // 3. Remover opções essenciais
    $options = ['won_api_token', 'won_api_rate_limit', 'won_api_log_level', 'won_api_whitelist_tables'];
    foreach ($options as $option) {
        $CI->db->where('name', $option);
        $CI->db->delete(db_prefix() . 'options');
    }

    // 4. Limpar cache
    if (is_dir(APPPATH . 'logs/cache')) {
        $cache_files = glob(APPPATH . 'logs/cache/*won_api*');
        foreach ($cache_files as $file) {
            @unlink($file);
        }
    }

    log_message('info', '[Won API] Desinstalação concluída com sucesso');
    return true;

} catch (Exception $e) {
    log_message('error', '[Won API] Erro na desinstalação: ' . $e->getMessage());
    return false;
}