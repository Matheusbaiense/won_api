<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Script de Instalação Otimizado - WON API v2.1.0
 * Instalação simplificada e robusta com logs detalhados
 */

$CI = &get_instance();
$CI->load->database();

try {
    log_message('info', '[Won API] === INICIANDO INSTALAÇÃO v2.1.0 ===');
    
    // 1. VERIFICAR COMPATIBILIDADE CRÍTICA
    log_message('info', '[Won API] Verificando compatibilidade do sistema...');
    
    // Verificar versão do Perfex CRM
    $perfex_version = null;
    if (defined('APP_VERSION')) {
        $perfex_version = APP_VERSION;
    } elseif (method_exists($CI->app, 'get_current_db_version')) {
        $perfex_version = $CI->app->get_current_db_version();
    } elseif (file_exists(FCPATH . 'application/config/migration.php')) {
        include FCPATH . 'application/config/migration.php';
        $perfex_version = isset($config['migration_version']) ? $config['migration_version'] : 'unknown';
    }
    
    if ($perfex_version && version_compare($perfex_version, '2.9.2', '<')) {
        $error_msg = "Perfex CRM versão incompatível. Atual: {$perfex_version}, Requerido: 2.9.2+";
        log_message('error', '[Won API] ' . $error_msg);
        throw new Exception($error_msg);
    }
    
    log_message('info', '[Won API] Perfex CRM compatível: ' . ($perfex_version ?: 'versão detectada'));
    
    // Verificar conexão com banco
    if (!$CI->db->conn_id) {
        throw new Exception('Falha na conexão com banco de dados');
    }
    log_message('info', '[Won API] Conexão com banco: OK');
    
    // 2. VERIFICAR/CRIAR ESTRUTURA DO BANCO
    log_message('info', '[Won API] Configurando estrutura do banco...');
    
    // Verificar se a tabela de módulos existe no Perfex
    $modules_table = db_prefix() . 'modules';
    if (!$CI->db->table_exists($modules_table)) {
        log_message('info', '[Won API] Criando tabela de módulos...');
        $sql = "CREATE TABLE `{$modules_table}` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `module_name` VARCHAR(55) NOT NULL,
            `installed_version` VARCHAR(11) NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `module_name` (`module_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$CI->db->query($sql)) {
            throw new Exception('Erro ao criar tabela de módulos: ' . $CI->db->error()['message']);
        }
    }
    
    // 3. REGISTRAR/ATUALIZAR MÓDULO
    log_message('info', '[Won API] Registrando módulo...');
    
    $CI->db->where('module_name', 'won_api');
    $existing_module = $CI->db->get($modules_table)->row();
    
    if (!$existing_module) {
        // Inserir novo registro
        $module_data = [
            'module_name' => 'won_api',
            'installed_version' => '2.1.0',
            'active' => 1
        ];
        
        if (!$CI->db->insert($modules_table, $module_data)) {
            throw new Exception('Erro ao registrar módulo: ' . $CI->db->error()['message']);
        }
        log_message('info', '[Won API] Módulo registrado com sucesso');
    } else {
        // Atualizar registro existente
        $update_data = [
            'installed_version' => '2.1.0',
            'active' => 1
        ];
        
        $CI->db->where('module_name', 'won_api');
        if (!$CI->db->update($modules_table, $update_data)) {
            throw new Exception('Erro ao atualizar módulo: ' . $CI->db->error()['message']);
        }
        log_message('info', '[Won API] Módulo atualizado para v2.1.0');
    }
    
    // 4. CRIAR TABELA DE LOGS
    $logs_table = db_prefix() . 'won_api_logs';
    if (!$CI->db->table_exists($logs_table)) {
        log_message('info', '[Won API] Criando tabela de logs...');
        $sql = "CREATE TABLE `{$logs_table}` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `endpoint` VARCHAR(255) NOT NULL,
            `method` VARCHAR(10) NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `status` INT NOT NULL,
            `response_time` FLOAT NOT NULL,
            `error_message` TEXT NULL,
            `date` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `endpoint_idx` (`endpoint`),
            INDEX `date_idx` (`date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$CI->db->query($sql)) {
            throw new Exception('Erro ao criar tabela de logs: ' . $CI->db->error()['message']);
        }
        log_message('info', '[Won API] Tabela de logs criada');
    }
    
    // 5. CONFIGURAR OPÇÕES ESSENCIAIS
    log_message('info', '[Won API] Configurando opções...');
    
    $options = [
        'won_api_token' => function_exists('random_bytes') ? bin2hex(random_bytes(32)) : md5(uniqid(mt_rand(), true)),
        'won_api_rate_limit' => '100',
        'won_api_log_level' => 'basic',
        'won_api_whitelist_tables' => 'clients,projects,tasks,staff,leads,estimates,invoices,payments'
    ];
    
    foreach ($options as $name => $value) {
        $CI->db->where('name', $name);
        $existing = $CI->db->get(db_prefix() . 'options')->row();
        
        if (!$existing) {
            $CI->db->insert(db_prefix() . 'options', ['name' => $name, 'value' => $value]);
            log_message('info', '[Won API] Opção criada: ' . $name);
        }
    }
    
    // 6. VERIFICAR ESTRUTURA DE ARQUIVOS
    log_message('info', '[Won API] Verificando estrutura de arquivos...');
    
    $required_files = [
        'won_api.php',
        'module_info.php',
        'controllers/Won.php',
        'controllers/Won_api.php'
    ];
    
    $base_path = FCPATH . 'modules/won_api/';
    foreach ($required_files as $file) {
        if (!file_exists($base_path . $file)) {
            log_message('warning', '[Won API] Arquivo ausente: ' . $file);
        }
    }
    
    // 7. LIMPAR CACHE
    if (is_dir(APPPATH . 'logs/cache')) {
        $cache_files = glob(APPPATH . 'logs/cache/*won_api*');
        foreach ($cache_files as $file) {
            @unlink($file);
        }
    }
    
    log_message('info', '[Won API] === INSTALAÇÃO CONCLUÍDA COM SUCESSO ===');
    echo '<div style="padding:20px;color:green;border:1px solid green;background:#f0fff0;margin:10px;">
            <h3>✅ WON API v2.1.0 instalado com sucesso!</h3>
            <p>O módulo foi registrado e configurado corretamente.</p>
            <p><strong>Próximos passos:</strong></p>
            <ul>
                <li>Acesse Admin > WON API > Configurações para definir o token</li>
                <li>Consulte a documentação em Admin > WON API > Documentação</li>
            </ul>
          </div>';
    
    return true;
    
} catch (Exception $e) {
    $error_msg = 'Erro na instalação: ' . $e->getMessage();
    log_message('error', '[Won API] ' . $error_msg);
    
    echo '<div style="padding:20px;color:red;border:1px solid red;background:#fff0f0;margin:10px;">
            <h3>❌ Erro na Instalação</h3>
            <p>' . htmlspecialchars($error_msg) . '</p>
            <p><strong>Verifique:</strong></p>
            <ul>
                <li>Versão do Perfex CRM (requer 2.9.2+)</li>
                <li>Permissões de banco de dados (CREATE TABLE)</li>
                <li>Estrutura de arquivos do módulo</li>
                <li>Logs em application/logs/ para detalhes</li>
            </ul>
          </div>';
    
    return false;
}