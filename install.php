<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Script de Instalação ULTRA-ROBUSTO - WON API v2.1.0
 * Instalação com verificações críticas e correções automáticas
 */

$CI = &get_instance();
$CI->load->database();

try {
    log_message('info', '[Won API] === INSTALAÇÃO ULTRA-ROBUSTA v2.1.0 ===');
    
    // ========== VERIFICAÇÕES CRÍTICAS PRÉ-INSTALAÇÃO ==========
    
    // 1. VERIFICAR DIRETÓRIO CORRETO
    log_message('info', '[Won API] 1. Verificando estrutura de diretórios...');
    $module_dir = basename(dirname(__FILE__));
    if ($module_dir !== 'won_api') {
        throw new Exception("Módulo deve estar em /modules/won_api/. Atual: /modules/{$module_dir}/");
    }
    
    $expected_path = FCPATH . 'modules/won_api/';
    $current_path = dirname(__FILE__) . '/';
    if (realpath($current_path) !== realpath($expected_path)) {
        throw new Exception("Caminho incorreto. Esperado: {$expected_path}, Atual: {$current_path}");
    }
    
    // 2. VERIFICAR PERMISSÕES CRÍTICAS
    log_message('info', '[Won API] 2. Verificando permissões críticas...');
    $critical_paths = [
        dirname(__FILE__) => ['read' => true, 'write' => true, 'required_perm' => 0755],
        __FILE__ => ['read' => true, 'write' => false, 'required_perm' => 0644],
        dirname(__FILE__) . '/controllers' => ['read' => true, 'write' => true, 'required_perm' => 0755],
        dirname(__FILE__) . '/views' => ['read' => true, 'write' => true, 'required_perm' => 0755],
        dirname(__FILE__) . '/won_api.php' => ['read' => true, 'write' => false, 'required_perm' => 0644]
    ];
    
    $permission_errors = [];
    foreach ($critical_paths as $path => $requirements) {
        if (!file_exists($path)) {
            $permission_errors[] = "Arquivo/diretório não encontrado: {$path}";
            continue;
        }
        
        if ($requirements['read'] && !is_readable($path)) {
            $permission_errors[] = "Sem permissão de leitura: {$path}";
        }
        
        if ($requirements['write'] && !is_writable($path)) {
            $permission_errors[] = "Sem permissão de escrita: {$path}";
        }
        
        $current_perm = substr(sprintf('%o', fileperms($path)), -3);
        $required_perm = sprintf('%03o', $requirements['required_perm']);
        if ($current_perm != $required_perm) {
            log_message('warning', "[Won API] Permissão subótima em {$path}: {$current_perm} (recomendado: {$required_perm})");
        }
    }
    
    if (!empty($permission_errors)) {
        throw new Exception('Problemas de permissão: ' . implode('; ', $permission_errors));
    }
    
    // 3. VERIFICAR COMPATIBILIDADE PERFEX CRM
    log_message('info', '[Won API] 3. Verificando compatibilidade Perfex CRM...');
    
    // Múltiplas formas de detectar versão
    $perfex_version = 'unknown';
    $version_sources = [
        'APP_VERSION constant' => defined('APP_VERSION') ? APP_VERSION : null,
        'app->get_current_db_version()' => method_exists($CI->app, 'get_current_db_version') ? $CI->app->get_current_db_version() : null,
        'migration config' => null
    ];
    
    // Tentar migration config
    $migration_file = FCPATH . 'application/config/migration.php';
    if (file_exists($migration_file)) {
        include $migration_file;
        $version_sources['migration config'] = isset($config['migration_version']) ? $config['migration_version'] : null;
    }
    
    foreach ($version_sources as $source => $version) {
        if ($version && $version != 'unknown') {
            $perfex_version = $version;
            log_message('info', "[Won API] Versão detectada via {$source}: {$version}");
            break;
        }
    }
    
    if ($perfex_version != 'unknown' && version_compare($perfex_version, '2.9.0', '<')) {
        throw new Exception("Perfex CRM versão incompatível. Atual: {$perfex_version}, Mínimo: 2.9.0");
    }
    
    // 4. VERIFICAR EXTENSÕES PHP CRÍTICAS
    log_message('info', '[Won API] 4. Verificando extensões PHP...');
    $required_extensions = ['json', 'curl', 'openssl', 'mbstring'];
    $missing_extensions = [];
    
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $missing_extensions[] = $ext;
        }
    }
    
    if (!empty($missing_extensions)) {
        throw new Exception('Extensões PHP ausentes: ' . implode(', ', $missing_extensions));
    }
    
    // 5. VERIFICAR CONEXÃO BANCO DE DADOS
    log_message('info', '[Won API] 5. Verificando banco de dados...');
    if (!$CI->db->conn_id) {
        throw new Exception('Falha crítica na conexão com banco de dados');
    }
    
    // Testar operação básica
    $test_query = $CI->db->query('SELECT 1 as test');
    if (!$test_query || !$test_query->row()) {
        throw new Exception('Banco de dados não responsivo');
    }
    
    // ========== PROCESSO DE INSTALAÇÃO ==========
    
    // 6. VERIFICAR/CRIAR TABELA DE MÓDULOS
    log_message('info', '[Won API] 6. Configurando tabela de módulos...');
    $modules_table = db_prefix() . 'modules';
    
    if (!$CI->db->table_exists($modules_table)) {
        log_message('info', '[Won API] Criando tabela de módulos...');
        $sql = "CREATE TABLE `{$modules_table}` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `module_name` VARCHAR(55) NOT NULL,
            `installed_version` VARCHAR(11) NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `date_installed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `module_name_unique` (`module_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$CI->db->query($sql)) {
            $db_error = $CI->db->error();
            throw new Exception("Erro ao criar tabela de módulos: {$db_error['message']} (Código: {$db_error['code']})");
        }
    }
    
    // 7. REGISTRAR/ATUALIZAR MÓDULO COM VERIFICAÇÃO
    log_message('info', '[Won API] 7. Registrando módulo no sistema...');
    
    // Primeiro, limpar registros duplicados se existirem
    $CI->db->where('module_name', 'won_api');
    $existing_count = $CI->db->count_all_results($modules_table);
    
    if ($existing_count > 1) {
        log_message('warning', '[Won API] Removendo registros duplicados...');
        $CI->db->query("DELETE FROM `{$modules_table}` WHERE module_name = 'won_api'");
    }
    
    // Registrar módulo
    $CI->db->where('module_name', 'won_api');
    $existing_module = $CI->db->get($modules_table)->row();
    
    if (!$existing_module) {
        $module_data = [
            'module_name' => 'won_api',
            'installed_version' => '2.1.0',
            'active' => 1
        ];
        
        if (!$CI->db->insert($modules_table, $module_data)) {
            $db_error = $CI->db->error();
            throw new Exception("Erro ao registrar módulo: {$db_error['message']}");
        }
        log_message('info', '[Won API] Módulo registrado com sucesso');
    } else {
        $update_data = [
            'installed_version' => '2.1.0',
            'active' => 1
        ];
        
        $CI->db->where('module_name', 'won_api');
        if (!$CI->db->update($modules_table, $update_data)) {
            $db_error = $CI->db->error();
            throw new Exception("Erro ao atualizar módulo: {$db_error['message']}");
        }
        log_message('info', '[Won API] Módulo atualizado para v2.1.0');
    }
    
    // 8. CRIAR TABELA DE LOGS OTIMIZADA
    log_message('info', '[Won API] 8. Configurando sistema de logs...');
    $logs_table = db_prefix() . 'won_api_logs';
    
    if (!$CI->db->table_exists($logs_table)) {
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
            INDEX `date_idx` (`date`),
            INDEX `status_idx` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$CI->db->query($sql)) {
            $db_error = $CI->db->error();
            throw new Exception("Erro ao criar tabela de logs: {$db_error['message']}");
        }
        log_message('info', '[Won API] Tabela de logs criada com índices otimizados');
    }
    
    // 9. CONFIGURAR OPÇÕES COM VERIFICAÇÃO DE INTEGRIDADE
    log_message('info', '[Won API] 9. Configurando opções do sistema...');
    
    $options = [
        'won_api_token' => function_exists('random_bytes') ? bin2hex(random_bytes(32)) : md5(uniqid(mt_rand(), true) . microtime()),
        'won_api_rate_limit' => '100',
        'won_api_log_level' => 'basic',
        'won_api_whitelist_tables' => 'clients,projects,tasks,staff,leads,estimates,invoices,payments,tickets',
        'won_api_version' => '2.1.0',
        'won_api_installed_date' => date('Y-m-d H:i:s')
    ];
    
    foreach ($options as $name => $value) {
        $CI->db->where('name', $name);
        $existing = $CI->db->get(db_prefix() . 'options')->row();
        
        if (!$existing) {
            $result = $CI->db->insert(db_prefix() . 'options', ['name' => $name, 'value' => $value]);
            if (!$result) {
                log_message('warning', "[Won API] Falha ao criar opção: {$name}");
            } else {
                log_message('info', "[Won API] Opção criada: {$name}");
            }
        } else if ($name === 'won_api_version') {
            // Sempre atualizar versão
            $CI->db->where('name', $name);
            $CI->db->update(db_prefix() . 'options', ['value' => $value]);
            log_message('info', "[Won API] Opção atualizada: {$name} = {$value}");
        }
    }
    
    // 10. VERIFICAÇÃO FINAL DE INTEGRIDADE
    log_message('info', '[Won API] 10. Verificação final de integridade...');
    
    // Verificar se módulo foi registrado corretamente
    $CI->db->where('module_name', 'won_api');
    $final_check = $CI->db->get($modules_table)->row();
    
    if (!$final_check || $final_check->active != 1) {
        throw new Exception('Falha na verificação final: módulo não registrado corretamente');
    }
    
    // Verificar token criado
    $token_check = get_option('won_api_token');
    if (empty($token_check)) {
        throw new Exception('Falha na criação do token de API');
    }
    
    // 11. LIMPAR CACHE E FINALIZAR
    log_message('info', '[Won API] 11. Limpando cache...');
    
    // Limpar cache do Perfex se disponível
    if (method_exists($CI, 'app_object_cache') && method_exists($CI->app_object_cache, 'delete')) {
        $CI->app_object_cache->delete('modules_*');
    }
    
    // Limpar arquivos de cache
    $cache_patterns = [
        APPPATH . 'logs/cache/*won_api*',
        APPPATH . 'cache/*won_api*'
    ];
    
    foreach ($cache_patterns as $pattern) {
        $files = glob($pattern);
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
    
    log_message('info', '[Won API] === INSTALAÇÃO ULTRA-ROBUSTA CONCLUÍDA ===');
    
    // EXIBIR RESULTADO DE SUCESSO
    echo '<div style="max-width:800px;margin:20px auto;padding:30px;background:linear-gradient(135deg,#e8f5e8,#f0fff0);border:2px solid #28a745;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <div style="text-align:center;margin-bottom:20px;">
                <h2 style="color:#155724;margin:0;font-size:28px;">✅ WON API v2.1.0 INSTALADO COM SUCESSO!</h2>
                <p style="color:#155724;margin:10px 0 0 0;font-size:16px;">Instalação ultra-robusta concluída sem erros</p>
            </div>
            
            <div style="background:white;padding:20px;border-radius:8px;margin:20px 0;border-left:4px solid #28a745;">
                <h4 style="color:#155724;margin:0 0 15px 0;">📋 Verificações Realizadas:</h4>
                <ul style="margin:0;color:#155724;">
                    <li>✅ Estrutura de diretórios verificada</li>
                    <li>✅ Permissões críticas validadas</li>
                    <li>✅ Compatibilidade Perfex CRM confirmada</li>
                    <li>✅ Extensões PHP verificadas</li>
                    <li>✅ Banco de dados testado</li>
                    <li>✅ Módulo registrado no sistema</li>
                    <li>✅ Tabelas criadas com índices otimizados</li>
                    <li>✅ Token de segurança gerado</li>
                    <li>✅ Cache limpo</li>
                    <li>✅ Integridade final confirmada</li>
                </ul>
            </div>
            
            <div style="background:#fff3cd;padding:15px;border-radius:8px;margin:20px 0;border-left:4px solid #ffc107;">
                <h4 style="color:#856404;margin:0 0 10px 0;">🚀 Próximos Passos:</h4>
                <ol style="margin:0;color:#856404;">
                    <li><strong>Acessar:</strong> Admin → Módulos → Verificar "WON API" ativo</li>
                    <li><strong>Configurar:</strong> Admin → WON API → Configurações</li>
                    <li><strong>Testar:</strong> Admin → WON API → Documentação</li>
                    <li><strong>Monitorar:</strong> Admin → WON API → Logs</li>
                </ol>
            </div>
            
            <div style="background:#d4edda;padding:15px;border-radius:8px;margin:20px 0;text-align:center;">
                <p style="margin:0;color:#155724;font-weight:bold;">
                    🔐 Token de API: ' . substr($token_check, 0, 16) . '... (visualize completo nas configurações)
                </p>
            </div>
            
            <div style="text-align:center;margin-top:25px;">
                <a href="' . admin_url('modules') . '" style="background:#28a745;color:white;padding:12px 25px;text-decoration:none;border-radius:5px;margin:0 10px;display:inline-block;">
                    📦 Ver Módulos
                </a>
                <a href="' . admin_url('won_api/configuracoes') . '" style="background:#007bff;color:white;padding:12px 25px;text-decoration:none;border-radius:5px;margin:0 10px;display:inline-block;">
                    ⚙️ Configurações
                </a>
            </div>
          </div>';
    
    return true;
    
} catch (Exception $e) {
    $error_msg = $e->getMessage();
    $error_details = [
        'arquivo' => basename(__FILE__),
        'linha' => $e->getLine(),
        'erro' => $error_msg,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    log_message('error', '[Won API] ERRO CRÍTICO NA INSTALAÇÃO: ' . json_encode($error_details));
    
    // EXIBIR ERRO DETALHADO COM SOLUÇÕES
    echo '<div style="max-width:800px;margin:20px auto;padding:30px;background:linear-gradient(135deg,#f8d7da,#fff5f5);border:2px solid #dc3545;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <div style="text-align:center;margin-bottom:20px;">
                <h2 style="color:#721c24;margin:0;font-size:28px;">❌ ERRO NA INSTALAÇÃO</h2>
                <p style="color:#721c24;margin:10px 0 0 0;font-size:16px;">Falha detectada durante o processo</p>
            </div>
            
            <div style="background:white;padding:20px;border-radius:8px;margin:20px 0;border-left:4px solid #dc3545;">
                <h4 style="color:#721c24;margin:0 0 10px 0;">🔍 Detalhes do Erro:</h4>
                <p style="color:#721c24;margin:0;padding:10px;background:#f8f9fa;border-radius:4px;font-family:monospace;">
                    ' . htmlspecialchars($error_msg) . '
                </p>
            </div>
            
            <div style="background:#fff3cd;padding:20px;border-radius:8px;margin:20px 0;border-left:4px solid #ffc107;">
                <h4 style="color:#856404;margin:0 0 15px 0;">🛠️ Soluções Recomendadas:</h4>
                
                <div style="margin-bottom:15px;">
                    <h5 style="color:#856404;margin:0 0 5px 0;">1. Verificar Estrutura:</h5>
                    <p style="margin:0;color:#856404;">Certifique-se que o módulo está em: <code>/modules/won_api/</code></p>
                </div>
                
                <div style="margin-bottom:15px;">
                    <h5 style="color:#856404;margin:0 0 5px 0;">2. Ajustar Permissões:</h5>
                    <pre style="background:#f8f9fa;padding:10px;border-radius:4px;margin:5px 0;color:#856404;">chmod 755 modules/won_api/
chmod 644 modules/won_api/*.php
chmod 755 modules/won_api/controllers/
chmod 755 modules/won_api/views/</pre>
                </div>
                
                <div style="margin-bottom:15px;">
                    <h5 style="color:#856404;margin:0 0 5px 0;">3. Instalação Manual:</h5>
                    <p style="margin:0;color:#856404;">Execute: <code>php modules/won_api/install_manual.php</code></p>
                </div>
                
                <div style="margin-bottom:15px;">
                    <h5 style="color:#856404;margin:0 0 5px 0;">4. Verificar Perfex CRM:</h5>
                    <p style="margin:0;color:#856404;">Versão mínima requerida: 2.9.0</p>
                </div>
            </div>
            
            <div style="background:#d1ecf1;padding:15px;border-radius:8px;margin:20px 0;border-left:4px solid #17a2b8;">
                <h4 style="color:#0c5460;margin:0 0 10px 0;">📞 Suporte de Emergência:</h4>
                <ul style="margin:0;color:#0c5460;">
                    <li>Verifique logs em: <code>application/logs/</code></li>
                    <li>Execute diagnóstico: <code>php modules/won_api/verify_install.php</code></li>
                    <li>Consulte documentação no GitHub</li>
                </ul>
            </div>
          </div>';
    
    return false;
}
?>