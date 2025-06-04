<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Script de Instalação Simplificado - WON API v2.1.0
 */

$CI = &get_instance();
$CI->load->database();

try {
    log_message('info', '[WON API] Iniciando instalação v2.1.0');
    
    // 1. Registrar módulo
    $modules_table = db_prefix() . 'modules';
    
    if (!$CI->db->table_exists($modules_table)) {
        $CI->db->query("CREATE TABLE `{$modules_table}` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `module_name` VARCHAR(55) NOT NULL UNIQUE,
            `installed_version` VARCHAR(11) NOT NULL,
            `active` TINYINT(1) DEFAULT 1,
            `date_installed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    
    // Registrar/atualizar módulo
    $CI->db->where('module_name', 'won_api');
    $existing = $CI->db->get($modules_table)->row();

    if ($existing) {
        $CI->db->where('module_name', 'won_api');
        $CI->db->update($modules_table, [
            'installed_version' => '2.1.0',
            'active' => 1
        ]);
    } else {
        $CI->db->insert($modules_table, [
            'module_name' => 'won_api',
            'installed_version' => '2.1.0',
            'active' => 1
        ]);
    }
    
    // 2. Criar tabela de logs
    $logs_table = db_prefix() . 'won_api_logs';
    
    if (!$CI->db->table_exists($logs_table)) {
        $CI->db->query("CREATE TABLE `{$logs_table}` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `endpoint` VARCHAR(255) NOT NULL,
            `method` VARCHAR(10) NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `status` INT NOT NULL,
            `response_time` FLOAT NOT NULL,
            `error_message` TEXT NULL,
            `date` DATETIME NOT NULL,
            INDEX `endpoint_idx` (`endpoint`),
            INDEX `date_idx` (`date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    
    // 3. Criar tabela de rate limiting
    $rate_limit_table = db_prefix() . 'won_api_rate_limit';

    if (!$CI->db->table_exists($rate_limit_table)) {
        $CI->db->query("CREATE TABLE `{$rate_limit_table}` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ip_address` VARCHAR(45) NOT NULL,
            `hour_window` BIGINT NOT NULL,
            `request_count` INT DEFAULT 1,
            `created_at` DATETIME NOT NULL,
            UNIQUE KEY `ip_hour` (`ip_address`, `hour_window`),
            INDEX `hour_idx` (`hour_window`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    
    // 4. Configurar opções essenciais
    $options = [
        'won_api_token' => bin2hex(random_bytes(32)),
        'won_api_rate_limit' => '100',
        'won_api_version' => '2.1.0'
    ];
    
    foreach ($options as $name => $value) {
        $CI->db->where('name', $name);
        if (!$CI->db->get(db_prefix() . 'options')->row()) {
            $CI->db->insert(db_prefix() . 'options', [
                'name' => $name, 
                'value' => $value
            ]);
        }
    }
    
    log_message('info', '[WON API] Instalação concluída com sucesso');
    
    echo '<div style="max-width:600px;margin:50px auto;padding:30px;background:#d4edda;border:2px solid #28a745;border-radius:8px;text-align:center;">
            <h2 style="color:#155724;">✅ WON API v2.1.0 Instalado!</h2>
            <p>Acesse Admin → WON API → Configurações</p>
          </div>';
    
    return true;
    
} catch (Exception $e) {
    log_message('error', '[WON API] Erro na instalação: ' . $e->getMessage());
    
    echo '<div style="max-width:600px;margin:50px auto;padding:30px;background:#f8d7da;border:2px solid #dc3545;border-radius:8px;text-align:center;">
            <h2 style="color:#721c24;">❌ Erro na Instalação</h2>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
          </div>';
    
    return false;
}
?>