<?php
/**
 * Instalação Manual WON API v2.1.0
 * Script de emergência para instalação quando falha o método automático
 */

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

echo "=== INSTALAÇÃO MANUAL WON API v2.1.0 ===\n\n";

try {
    // 1. Verificar diretório correto
    $current_dir = basename(dirname(__FILE__));
    if ($current_dir !== 'won_api') {
        throw new Exception("❌ Módulo deve estar em /modules/won_api/ (atual: {$current_dir})");
    }
    echo "✅ Diretório correto: /modules/won_api/\n";
    
    // 2. Verificar se tabela modules existe
    if (!$CI->db->table_exists(db_prefix() . 'modules')) {
        echo "📝 Criando tabela de módulos...\n";
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'modules` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `module_name` VARCHAR(55) NOT NULL,
            `installed_version` VARCHAR(11) NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `module_name` (`module_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        echo "✅ Tabela de módulos criada\n";
    } else {
        echo "✅ Tabela de módulos já existe\n";
    }
    
    // 3. Registrar módulo
    $CI->db->where('module_name', 'won_api');
    $existing = $CI->db->get(db_prefix() . 'modules')->row();
    
    if (!$existing) {
        $CI->db->insert(db_prefix() . 'modules', [
            'module_name' => 'won_api',
            'installed_version' => '2.1.0',
            'active' => 1
        ]);
        echo "✅ Módulo registrado na tabela tblmodules\n";
    } else {
        // Atualizar se já existe
        $CI->db->where('module_name', 'won_api');
        $CI->db->update(db_prefix() . 'modules', [
            'installed_version' => '2.1.0',
            'active' => 1
        ]);
        echo "✅ Módulo atualizado (já existia)\n";
    }
    
    // 4. Criar tabela de logs
    $logs_table = db_prefix() . 'won_api_logs';
    if (!$CI->db->table_exists($logs_table)) {
        echo "📝 Criando tabela de logs...\n";
        $CI->db->query("CREATE TABLE `{$logs_table}` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "✅ Tabela de logs criada\n";
    } else {
        echo "✅ Tabela de logs já existe\n";
    }
    
    // 5. Configurar opções básicas
    $options = [
        'won_api_token' => function_exists('random_bytes') ? bin2hex(random_bytes(32)) : md5(uniqid(mt_rand(), true)),
        'won_api_rate_limit' => '100',
        'won_api_log_level' => 'basic',
        'won_api_whitelist_tables' => 'clients,projects,tasks,staff,leads,invoices'
    ];
    
    echo "📝 Configurando opções...\n";
    foreach ($options as $name => $value) {
        $CI->db->where('name', $name);
        $existing_option = $CI->db->get(db_prefix() . 'options')->row();
        
        if (!$existing_option) {
            $CI->db->insert(db_prefix() . 'options', [
                'name' => $name,
                'value' => $value
            ]);
            echo "   ✅ {$name} configurado\n";
        } else {
            echo "   ✅ {$name} já existe\n";
        }
    }
    
    // 6. Criar permissões
    echo "📝 Configurando permissões...\n";
    if (!$CI->db->get_where(db_prefix() . 'permissions', ['name' => 'won_api'])->row()) {
        $CI->db->insert(db_prefix() . 'permissions', [
            'name' => 'won_api',
            'shortname' => 'won_api'
        ]);
        echo "   ✅ Permissões criadas\n";
    } else {
        echo "   ✅ Permissões já existem\n";
    }
    
    // 7. Verificar arquivos críticos
    $critical_files = [
        'won_api.php',
        'install.php', 
        'module_info.php',
        'controllers/Won.php',
        'controllers/Won_api.php'
    ];
    
    echo "📁 Verificando arquivos críticos...\n";
    $missing_files = [];
    foreach ($critical_files as $file) {
        if (file_exists($file)) {
            echo "   ✅ {$file}\n";
        } else {
            echo "   ❌ {$file} AUSENTE\n";
            $missing_files[] = $file;
        }
    }
    
    if (!empty($missing_files)) {
        throw new Exception("Arquivos críticos ausentes: " . implode(', ', $missing_files));
    }
    
    // 8. Limpar cache
    if (is_dir(APPPATH . 'logs/cache')) {
        $cache_files = glob(APPPATH . 'logs/cache/*won_api*');
        foreach ($cache_files as $file) {
            @unlink($file);
        }
        echo "✅ Cache limpo\n";
    }
    
    echo "\n🎉 INSTALAÇÃO MANUAL CONCLUÍDA COM SUCESSO!\n\n";
    echo "📋 PRÓXIMOS PASSOS:\n";
    echo "1. Acesse Admin > Módulos para verificar se WON API aparece\n";
    echo "2. Acesse Admin > WON API > Configurações\n";
    echo "3. Copie o token gerado: " . substr($options['won_api_token'], 0, 16) . "...\n";
    echo "4. Teste a API em: " . site_url('won_api/won/api/clients') . "\n\n";
    
    echo "🔗 ROTAS NECESSÁRIAS:\n";
    echo "Adicione em application/config/routes.php:\n";
    echo "\$route['api/won/(.+)'] = 'won_api/won/\$1';\n\n";
    
    return true;
    
} catch (Exception $e) {
    echo "\n❌ ERRO NA INSTALAÇÃO MANUAL:\n";
    echo $e->getMessage() . "\n\n";
    
    echo "🔧 SOLUÇÕES:\n";
    echo "1. Verificar permissões do banco de dados\n";
    echo "2. Verificar se Perfex CRM está funcionando\n";
    echo "3. Verificar estrutura de arquivos do módulo\n";
    echo "4. Executar: php verify_install.php para diagnóstico\n\n";
    
    return false;
}
?> 