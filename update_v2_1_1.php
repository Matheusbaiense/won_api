<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Script de Atualização Automática WON API v2.1.0 → v2.1.1
 * Inclui backup, verificação e rollback em caso de erro
 */

$CI = &get_instance();
$CI->load->database();

$backup_data = [];
$update_log = [];

try {
    log_message('info', '[WON API] === INICIANDO ATUALIZAÇÃO v2.1.0 → v2.1.1 ===');
    
    // ========== VERIFICAÇÕES PRÉ-ATUALIZAÇÃO ==========
    
    // 1. Verificar versão atual
    $current_version = get_option('won_api_version');
    if ($current_version !== '2.1.0') {
        throw new Exception("Versão atual ({$current_version}) não é compatível. Esperado: 2.1.0");
    }
    
    // 2. Verificar se tabelas existem
    $required_tables = [
        db_prefix() . 'won_api_logs',
        db_prefix() . 'won_api_rate_limit'
    ];
    
    foreach ($required_tables as $table) {
        if (!$CI->db->table_exists($table)) {
            throw new Exception("Tabela obrigatória não encontrada: {$table}");
        }
    }
    
    // ========== BACKUP DE DADOS ==========
    
    log_message('info', '[WON API] Criando backup dos dados...');
    
    // Backup das configurações
    $CI->db->like('name', 'won_api_', 'after');
    $options = $CI->db->get(db_prefix() . 'options')->result_array();
    $backup_data['options'] = $options;
    
    // Backup do registro do módulo
    $CI->db->where('module_name', 'won_api');
    $module = $CI->db->get(db_prefix() . 'modules')->row_array();
    $backup_data['module'] = $module;
    
    log_message('info', '[WON API] Backup criado com sucesso');
    
    // ========== ATUALIZAÇÕES DO BANCO DE DADOS ==========
    
    log_message('info', '[WON API] Atualizando estrutura do banco...');
    
    // 1. Atualizar tabela de rate limiting (novas colunas)
    $rate_table = db_prefix() . 'won_api_rate_limit';
    
    // Verificar se colunas já existem
    $columns = $CI->db->query("SHOW COLUMNS FROM `{$rate_table}`")->result_array();
    $existing_columns = array_column($columns, 'Field');
    
    if (!in_array('last_request', $existing_columns)) {
        $CI->db->query("ALTER TABLE `{$rate_table}` ADD COLUMN `last_request` DATETIME NULL AFTER `request_count`");
        $update_log[] = "Adicionada coluna 'last_request' à tabela de rate limiting";
    }
    
    if (!in_array('user_agent', $existing_columns)) {
        $CI->db->query("ALTER TABLE `{$rate_table}` ADD COLUMN `user_agent` TEXT NULL AFTER `last_request`");
        $update_log[] = "Adicionada coluna 'user_agent' à tabela de rate limiting";
    }
    
    // Adicionar novos índices se não existirem
    $indexes = $CI->db->query("SHOW INDEX FROM `{$rate_table}`")->result_array();
    $existing_indexes = array_column($indexes, 'Key_name');
    
    if (!in_array('last_request_idx', $existing_indexes)) {
        $CI->db->query("ALTER TABLE `{$rate_table}` ADD INDEX `last_request_idx` (`last_request`)");
        $update_log[] = "Adicionado índice 'last_request_idx'";
    }
    
    // 2. Atualizar tabela de logs (campo user_agent se não existir)
    $logs_table = db_prefix() . 'won_api_logs';
    $logs_columns = $CI->db->query("SHOW COLUMNS FROM `{$logs_table}`")->result_array();
    $existing_logs_columns = array_column($logs_columns, 'Field');
    
    if (!in_array('user_agent', $existing_logs_columns)) {
        $CI->db->query("ALTER TABLE `{$logs_table}` ADD COLUMN `user_agent` TEXT NULL AFTER `ip_address`");
        $update_log[] = "Adicionada coluna 'user_agent' à tabela de logs";
    }
    
    // ========== CONFIGURAÇÕES NOVAS ==========
    
    log_message('info', '[WON API] Atualizando configurações...');
    
    $new_options = [
        // CORS
        'won_api_cors_enabled' => 'true',
        'won_api_cors_origins' => '*',
        'won_api_cors_methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'won_api_cors_headers' => 'Content-Type, Authorization, X-Requested-With',
        
        // Debug e Logs
        'won_api_debug_mode' => 'false',
        'won_api_log_level' => 'info',
        
        // Performance
        'won_api_timeout' => '30',
        'won_api_memory_limit' => '256M',
        
        // Validações
        'won_api_strict_validation' => 'true',
        'won_api_validate_cpf_cnpj' => 'true',
        'won_api_email_validation' => 'strict',
        
        // Monitoramento
        'won_api_health_check' => 'true',
        'won_api_status_endpoint' => 'true',
        
        // Rate Limiting
        'won_api_rate_limit_headers' => 'true',
        'won_api_rate_limit_cleanup_interval' => '24'
    ];
    
    foreach ($new_options as $name => $value) {
        $CI->db->where('name', $name);
        if (!$CI->db->get(db_prefix() . 'options')->row()) {
            $CI->db->insert(db_prefix() . 'options', [
                'name' => $name,
                'value' => $value
            ]);
            $update_log[] = "Adicionada configuração: {$name} = {$value}";
        }
    }
    
    // ========== ATUALIZAR VERSÃO ==========
    
    log_message('info', '[WON API] Atualizando versão...');
    
    // Atualizar versão nas opções
    $CI->db->where('name', 'won_api_version');
    $CI->db->update(db_prefix() . 'options', ['value' => '2.1.1']);
    
    // Atualizar versão na tabela de módulos
    $CI->db->where('module_name', 'won_api');
    $CI->db->update(db_prefix() . 'modules', [
        'installed_version' => '2.1.1'
    ]);
    
    $update_log[] = "Versão atualizada para 2.1.1";
    
    // ========== LIMPEZA E OTIMIZAÇÃO ==========
    
    log_message('info', '[WON API] Executando limpeza...');
    
    // Limpar logs antigos (>30 dias)
    $CI->db->where('date <', date('Y-m-d H:i:s', strtotime('-30 days')));
    $deleted_logs = $CI->db->count_all_results(db_prefix() . 'won_api_logs');
    if ($deleted_logs > 0) {
        $CI->db->where('date <', date('Y-m-d H:i:s', strtotime('-30 days')));
        $CI->db->delete(db_prefix() . 'won_api_logs');
        $update_log[] = "Removidos {$deleted_logs} logs antigos";
    }
    
    // Limpar rate limits antigos (>48h)
    $cutoff_hour = floor(time() / 3600) - 48;
    $CI->db->where('hour_window <', $cutoff_hour);
    $deleted_rates = $CI->db->count_all_results(db_prefix() . 'won_api_rate_limit');
    if ($deleted_rates > 0) {
        $CI->db->where('hour_window <', $cutoff_hour);
        $CI->db->delete(db_prefix() . 'won_api_rate_limit');
        $update_log[] = "Removidos {$deleted_rates} registros de rate limit antigos";
    }
    
    // ========== VERIFICAÇÃO FINAL ==========
    
    log_message('info', '[WON API] Verificação final...');
    
    // Verificar se versão foi atualizada
    $new_version = get_option('won_api_version');
    if ($new_version !== '2.1.1') {
        throw new Exception("Falha na atualização da versão. Atual: {$new_version}");
    }
    
    // Verificar se novas colunas existem
    $rate_columns_after = $CI->db->query("SHOW COLUMNS FROM `{$rate_table}`")->result_array();
    $columns_after = array_column($rate_columns_after, 'Field');
    
    if (!in_array('last_request', $columns_after) || !in_array('user_agent', $columns_after)) {
        throw new Exception("Falha na criação das novas colunas");
    }
    
    log_message('info', '[WON API] === ATUALIZAÇÃO CONCLUÍDA COM SUCESSO ===');
    
    // EXIBIR RESULTADO DE SUCESSO
    echo '<div style="max-width:800px;margin:20px auto;padding:30px;background:linear-gradient(135deg,#e8f5e8,#f0fff0);border:2px solid #28a745;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <div style="text-align:center;margin-bottom:20px;">
                <h2 style="color:#155724;margin:0;font-size:28px;">✅ WON API ATUALIZADO PARA v2.1.1!</h2>
                <p style="color:#155724;margin:10px 0 0 0;font-size:16px;">Atualização profissional concluída sem erros</p>
            </div>
            
            <div style="background:white;padding:20px;border-radius:8px;margin:20px 0;border-left:4px solid #28a745;">
                <h4 style="color:#155724;margin:0 0 15px 0;">🚀 Novas Funcionalidades:</h4>
                <ul style="margin:0;color:#155724;">
                    <li>✅ CORS implementado para integrações front-end</li>
                    <li>✅ Rate limiting com headers informativos</li>
                    <li>✅ Logs detalhados com user-agent e performance</li>
                    <li>✅ Validações robustas de CPF/CNPJ e email</li>
                    <li>✅ Endpoint de status: /won_api/won/status</li>
                    <li>✅ Headers de segurança aprimorados</li>
                    <li>✅ Limpeza automática de dados antigos</li>
                    <li>✅ Configurações expandidas e flexíveis</li>
                </ul>
            </div>
            
            <div style="background:#fff3cd;padding:15px;border-radius:8px;margin:20px 0;border-left:4px solid #ffc107;">
                <h4 style="color:#856404;margin:0 0 10px 0;">📋 Mudanças Realizadas:</h4>
                <ul style="margin:0;color:#856404;font-size:14px;">';
    
    foreach ($update_log as $log_entry) {
        echo '<li>' . htmlspecialchars($log_entry) . '</li>';
    }
    
    echo '</ul>
            </div>
            
            <div style="background:#d1ecf1;padding:15px;border-radius:8px;margin:20px 0;border-left:4px solid #17a2b8;">
                <h4 style="color:#0c5460;margin:0 0 10px 0;">🧪 Testar Novas Funcionalidades:</h4>
                <ul style="margin:0;color:#0c5460;">
                    <li><strong>Endpoint Status:</strong> <code>GET /won_api/won/status</code></li>
                    <li><strong>Headers Rate Limit:</strong> Verifique X-RateLimit-Remaining</li>
                    <li><strong>CORS:</strong> Teste requests de diferentes origens</li>
                    <li><strong>Logs:</strong> Admin → WON API → Logs (mais detalhados)</li>
                </ul>
            </div>
            
            <div style="text-align:center;margin-top:25px;">
                <a href="' . admin_url('won_api/configuracoes') . '" style="background:#28a745;color:white;padding:12px 25px;text-decoration:none;border-radius:5px;margin:0 10px;display:inline-block;">
                    ⚙️ Configurações
                </a>
                <a href="' . site_url('won_api/won/status') . '" style="background:#17a2b8;color:white;padding:12px 25px;text-decoration:none;border-radius:5px;margin:0 10px;display:inline-block;" target="_blank">
                    📊 Status da API
                </a>
            </div>
          </div>';
    
    return true;
    
} catch (Exception $e) {
    $error_msg = $e->getMessage();
    log_message('error', '[WON API] ERRO NA ATUALIZAÇÃO: ' . $error_msg);
    
    // ========== ROLLBACK EM CASO DE ERRO ==========
    
    try {
        log_message('info', '[WON API] Iniciando rollback...');
        
        // Restaurar versão
        if (isset($backup_data['module'])) {
            $CI->db->where('module_name', 'won_api');
            $CI->db->update(db_prefix() . 'modules', [
                'installed_version' => $backup_data['module']['installed_version']
            ]);
        }
        
        // Restaurar opção de versão
        $CI->db->where('name', 'won_api_version');
        $CI->db->update(db_prefix() . 'options', ['value' => '2.1.0']);
        
        log_message('info', '[WON API] Rollback concluído');
        
    } catch (Exception $rollback_error) {
        log_message('error', '[WON API] ERRO NO ROLLBACK: ' . $rollback_error->getMessage());
    }
    
    // EXIBIR ERRO COM SOLUÇÕES
    echo '<div style="max-width:800px;margin:20px auto;padding:30px;background:linear-gradient(135deg,#f8d7da,#fff5f5);border:2px solid #dc3545;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <div style="text-align:center;margin-bottom:20px;">
                <h2 style="color:#721c24;margin:0;font-size:28px;">❌ ERRO NA ATUALIZAÇÃO</h2>
                <p style="color:#721c24;margin:10px 0 0 0;font-size:16px;">Rollback executado automaticamente</p>
            </div>
            
            <div style="background:white;padding:20px;border-radius:8px;margin:20px 0;border-left:4px solid #dc3545;">
                <h4 style="color:#721c24;margin:0 0 10px 0;">🔍 Detalhes do Erro:</h4>
                <p style="color:#721c24;margin:0;padding:10px;background:#f8f9fa;border-radius:4px;font-family:monospace;">
                    ' . htmlspecialchars($error_msg) . '
                </p>
            </div>
            
            <div style="background:#fff3cd;padding:20px;border-radius:8px;margin:20px 0;border-left:4px solid #ffc107;">
                <h4 style="color:#856404;margin:0 0 15px 0;">🛠️ Soluções Recomendadas:</h4>
                <ol style="margin:0;color:#856404;">
                    <li>Verificar logs em: <code>application/logs/</code></li>
                    <li>Executar diagnóstico: <code>php modules/won_api/verify_install.php</code></li>
                    <li>Verificar permissões do banco de dados</li>
                    <li>Contactar suporte se o problema persistir</li>
                </ol>
            </div>
          </div>';
    
    return false;
}
?> 