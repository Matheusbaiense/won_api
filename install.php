<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Verificar se o módulo já está registrado para evitar duplicações
$CI = &get_instance();
$CI->load->database();

try {
    // Verificar compatibilidade de versão do Perfex CRM
    if (method_exists($CI->app, 'get_current_db_version')) {
        $perfex_version = $CI->app->get_current_db_version();
        if (version_compare($perfex_version, '2.9.2', '<')) {
            log_message('error', '[Won API] Versão incompatível do Perfex CRM: ' . $perfex_version);
            show_error('Este módulo requer Perfex CRM versão 2.9.2 ou superior. Sua versão atual é ' . $perfex_version . '.');
            return false;
        }
    }

    // Verificar se a conexão com o banco de dados está funcionando
    if (!$CI->db->conn_id) {
        log_message('error', '[Won API] Falha ao conectar ao banco de dados durante a instalação.');
        show_error('Erro ao conectar ao banco de dados. Verifique as configurações do banco.');
        return false;
    }

    // Verificar se a tabela de módulos existe
    if (!$CI->db->table_exists(db_prefix() . 'modules')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'modules` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `module_name` VARCHAR(55) NOT NULL,
            `installed_version` VARCHAR(11) NOT NULL,
            `active` TINYINT(1) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `module_name` (`module_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        log_message('info', '[Won API] Tabela de módulos criada com sucesso.');
    }

    // Verificar se o módulo já está registrado
    $CI->db->where('module_name', 'won_api');
    $module = $CI->db->get(db_prefix() . 'modules')->row();

    if (!$module) {
        // Registrar o módulo na tabela de módulos
        $module_data = [
            'module_name' => 'won_api',
            'installed_version' => '2.1.0',
            'active' => 1,
        ];
        
        if ($CI->db->insert(db_prefix() . 'modules', $module_data)) {
            log_message('info', '[Won API] Módulo registrado com sucesso na tabela de módulos.');
        } else {
            $error = $CI->db->error();
            log_message('error', '[Won API] Erro ao registrar módulo: ' . $error['message']);
            throw new Exception('Erro ao registrar módulo: ' . $error['message']);
        }
    } else {
        // Atualizar versão se necessário
        $CI->db->where('module_name', 'won_api');
        $CI->db->update(db_prefix() . 'modules', [
            'installed_version' => '2.1.0',
            'active' => 1
        ]);
        log_message('info', '[Won API] Módulo atualizado na tabela de módulos.');
    }

    // Criar tabela de logs da API
    $logs_table = db_prefix() . 'won_api_logs';
    if (!$CI->db->table_exists($logs_table)) {
        $CI->db->query('CREATE TABLE `' . $logs_table . '` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `endpoint` VARCHAR(255) NOT NULL,
            `method` VARCHAR(10) NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `status` INT NOT NULL,
            `response_time` FLOAT NOT NULL,
            `request_data` TEXT,
            `response_data` TEXT,
            `error_message` TEXT,
            `date` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `endpoint_idx` (`endpoint`),
            INDEX `date_idx` (`date`),
            INDEX `status_idx` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        log_message('info', '[Won API] Tabela de logs criada com sucesso.');
    }

    // Adicionar/atualizar opções de configuração
    $options = [
        'won_api_token' => bin2hex(random_bytes(32)),
        'won_api_rate_limit' => '100',
        'won_api_cache_duration' => '300',
        'won_api_log_level' => 'basic',
        'won_api_cors_enabled' => '1',
        'won_api_cors_origins' => '*',
        'won_api_cors_methods' => 'GET,POST,PUT,DELETE,OPTIONS',
        'won_api_cors_headers' => 'Authorization,Content-Type,X-Requested-With',
        'won_api_whitelist_tables' => 'clients,projects,tasks,staff,leads,estimates,invoices,payments,expenses,contracts,proposals,tickets,knowledge_base',
        'won_api_max_page_size' => '100',
        'won_api_default_page_size' => '20',
        'won_api_cache_enabled' => '1',
        'won_api_debug_mode' => '0',
        'won_api_require_https' => '1',
        'won_api_allowed_ips' => '',
        'won_api_webhook_url' => '',
        'won_api_webhook_secret' => bin2hex(random_bytes(16))
    ];

    foreach ($options as $name => $default_value) {
        // Verificar se a opção já existe
        $CI->db->where('name', $name);
        $existing = $CI->db->get(db_prefix() . 'options')->row();
        
        if (!$existing) {
            $option_data = [
                'name' => $name,
                'value' => $default_value
            ];
            
            if ($CI->db->insert(db_prefix() . 'options', $option_data)) {
                log_message('info', '[Won API] Opção ' . $name . ' criada com sucesso.');
            } else {
                $error = $CI->db->error();
                log_message('error', '[Won API] Erro ao criar opção ' . $name . ': ' . $error['message']);
            }
        }
    }

    // Verificar e criar diretórios necessários com permissões adequadas
    $directories = [
        FCPATH . 'modules/won_api/controllers',
        FCPATH . 'modules/won_api/models',
        FCPATH . 'modules/won_api/views',
        FCPATH . 'modules/won_api/config',
        FCPATH . 'modules/won_api/tests',
        FCPATH . 'modules/won_api/logs'
    ];

    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            if (@mkdir($dir, 0755, true)) {
                log_message('info', '[Won API] Diretório criado: ' . $dir);
            } else {
                log_message('warning', '[Won API] Não foi possível criar diretório: ' . $dir);
            }
        }
    }

    // Verificar permissões de arquivos críticos
    $critical_files = [
        FCPATH . 'modules/won_api/won_api.php',
        FCPATH . 'modules/won_api/install.php',
        FCPATH . 'modules/won_api/module_info.php',
        FCPATH . 'modules/won_api/controllers/Won.php',
        FCPATH . 'modules/won_api/controllers/Won_api.php'
    ];

    foreach ($critical_files as $file) {
        if (file_exists($file)) {
            if (!is_readable($file)) {
                @chmod($file, 0644);
                log_message('info', '[Won API] Permissões ajustadas para: ' . $file);
            }
        }
    }

    // Limpar cache do sistema
    if (is_dir(APPPATH . 'logs/cache')) {
        $cache_files = glob(APPPATH . 'logs/cache/*');
        foreach ($cache_files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        log_message('info', '[Won API] Cache do sistema limpo.');
    }

    // Verificar se extensões PHP necessárias estão disponíveis
    $required_extensions = ['json', 'curl', 'openssl', 'mbstring'];
    $missing_extensions = [];
    
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $missing_extensions[] = $ext;
        }
    }
    
    if (!empty($missing_extensions)) {
        log_message('warning', '[Won API] Extensões PHP ausentes: ' . implode(', ', $missing_extensions));
        // Não bloquear a instalação, apenas alertar
    }

    // Criar arquivo de configuração inicial se não existir
    $config_file = FCPATH . 'modules/won_api/config/won_api_config.php';
    if (!file_exists($config_file)) {
        $config_content = "<?php\ndefined('BASEPATH') or exit('No direct script access allowed');\n\n// Configurações do módulo WON API\n\$config['won_api_version'] = '2.1.0';\n\$config['won_api_debug'] = false;\n";
        @file_put_contents($config_file, $config_content);
    }

    log_message('info', '[Won API] Instalação concluída com sucesso - Versão 2.1.0');
    
    // Se chegou até aqui, a instalação foi bem-sucedida
    return true;

} catch (Exception $e) {
    log_message('error', '[Won API] Erro durante a instalação: ' . $e->getMessage());
    show_error('Erro durante a instalação do módulo WON API: ' . $e->getMessage());
    return false;
}