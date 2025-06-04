<?php
/**
 * Instalação Manual Ultra-Simplificada - WON API v2.1.0
 * Script de emergência para casos onde a instalação automática falha
 */

// Verificar se está sendo executado em ambiente válido
if (!defined('BASEPATH')) {
    // Tentar definir constantes básicas para execução standalone
    define('BASEPATH', true);
    
    // Detectar ambiente Perfex
    $perfex_root = dirname(dirname(__DIR__));
    if (file_exists($perfex_root . '/application/config/database.php')) {
        echo '<div style="max-width:700px;margin:20px auto;padding:25px;background:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);font-family:Arial,sans-serif;">
                <h2 style="color:#dc3545;margin:0 0 20px 0;">⚠️ INSTALAÇÃO MANUAL - WON API v2.1.0</h2>
                <p style="color:#6c757d;margin-bottom:20px;">Executando instalação de emergência...</p>';
        
        try {
            // Conectar ao banco manualmente
            include $perfex_root . '/application/config/database.php';
            $db_config = $db['default'];
            
            $pdo = new PDO(
                "mysql:host={$db_config['hostname']};dbname={$db_config['database']};charset=utf8mb4",
                $db_config['username'],
                $db_config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            echo '<div style="background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #28a745;">
                    <p style="color:#155724;margin:0;">✅ Conexão com banco estabelecida</p>
                  </div>';
            
            // 1. REGISTRAR MÓDULO
            $modules_table = $db_config['dbprefix'] . 'modules';
            
            // Verificar se tabela de módulos existe
            $table_exists = $pdo->query("SHOW TABLES LIKE '{$modules_table}'")->rowCount() > 0;
            
            if (!$table_exists) {
                $pdo->exec("CREATE TABLE `{$modules_table}` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `module_name` VARCHAR(55) NOT NULL,
                    `installed_version` VARCHAR(11) NOT NULL,
                    `active` TINYINT(1) NOT NULL DEFAULT 1,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `module_name` (`module_name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                
                echo '<div style="background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #28a745;">
                        <p style="color:#155724;margin:0;">✅ Tabela de módulos criada</p>
                      </div>';
            }
            
            // Registrar/atualizar módulo
            $stmt = $pdo->prepare("SELECT id FROM `{$modules_table}` WHERE module_name = ?");
            $stmt->execute(['won_api']);
            
            if ($stmt->rowCount() > 0) {
                $pdo->prepare("UPDATE `{$modules_table}` SET installed_version = ?, active = ? WHERE module_name = ?")
                    ->execute(['2.1.0', 1, 'won_api']);
                echo '<div style="background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #28a745;">
                        <p style="color:#155724;margin:0;">✅ Módulo atualizado para v2.1.0</p>
                      </div>';
            } else {
                $pdo->prepare("INSERT INTO `{$modules_table}` (module_name, installed_version, active) VALUES (?, ?, ?)")
                    ->execute(['won_api', '2.1.0', 1]);
                echo '<div style="background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #28a745;">
                        <p style="color:#155724;margin:0;">✅ Módulo registrado com sucesso</p>
                      </div>';
            }
            
            // 2. CRIAR TABELA DE LOGS
            $logs_table = $db_config['dbprefix'] . 'won_api_logs';
            $logs_exists = $pdo->query("SHOW TABLES LIKE '{$logs_table}'")->rowCount() > 0;
            
            if (!$logs_exists) {
                $pdo->exec("CREATE TABLE `{$logs_table}` (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                
                echo '<div style="background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #28a745;">
                        <p style="color:#155724;margin:0;">✅ Tabela de logs criada</p>
                      </div>';
            } else {
                echo '<div style="background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #28a745;">
                        <p style="color:#155724;margin:0;">✅ Tabela de logs já existe</p>
                      </div>';
            }
            
            // 3. CONFIGURAR OPÇÕES ESSENCIAIS
            $options_table = $db_config['dbprefix'] . 'options';
            $options = [
                'won_api_token' => bin2hex(random_bytes(32)),
                'won_api_rate_limit' => '100',
                'won_api_version' => '2.1.0'
            ];
            
            foreach ($options as $name => $value) {
                $stmt = $pdo->prepare("SELECT id FROM `{$options_table}` WHERE name = ?");
                $stmt->execute([$name]);
                
                if ($stmt->rowCount() > 0) {
                    if ($name === 'won_api_version') {
                        $pdo->prepare("UPDATE `{$options_table}` SET value = ? WHERE name = ?")
                            ->execute([$value, $name]);
                        echo '<div style="background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #28a745;">
                                <p style="color:#155724;margin:0;">✅ Opção ' . $name . ' atualizada</p>
                              </div>';
                    }
                } else {
                    $pdo->prepare("INSERT INTO `{$options_table}` (name, value) VALUES (?, ?)")
                        ->execute([$name, $value]);
                    echo '<div style="background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #28a745;">
                            <p style="color:#155724;margin:0;">✅ Opção ' . $name . ' criada</p>
                          </div>';
                }
            }
            
            // SUCESSO FINAL
            echo '<div style="background:#28a745;color:white;padding:20px;border-radius:8px;margin:20px 0;text-align:center;">
                    <h3 style="margin:0 0 10px 0;">🎉 INSTALAÇÃO MANUAL CONCLUÍDA!</h3>
                    <p style="margin:0;">WON API v2.1.0 foi instalado com sucesso via modo de emergência.</p>
                  </div>';
            
            echo '<div style="background:#f8f9fa;padding:20px;border-radius:8px;margin:20px 0;">
                    <h4 style="color:#495057;margin:0 0 15px 0;">📋 Próximos Passos:</h4>
                    <ol style="color:#495057;margin:0;">
                        <li>Acesse o painel administrativo do Perfex CRM</li>
                        <li>Vá em <strong>Admin → Módulos</strong> e verifique se "WON API" aparece</li>
                        <li>Acesse <strong>Admin → WON API → Configurações</strong></li>
                        <li>Teste a API em <strong>Admin → WON API → Documentação</strong></li>
                    </ol>
                  </div>';
            
        } catch (Exception $e) {
            echo '<div style="background:#f8d7da;padding:20px;border-radius:8px;margin:20px 0;border:2px solid #dc3545;">
                    <h3 style="color:#721c24;margin:0 0 15px 0;">❌ ERRO NA INSTALAÇÃO MANUAL</h3>
                    <p style="color:#721c24;margin:0;background:white;padding:10px;border-radius:4px;font-family:monospace;">' . 
                    htmlspecialchars($e->getMessage()) . '</p>
                  </div>';
            
            echo '<div style="background:#fff3cd;padding:20px;border-radius:8px;margin:20px 0;">
                    <h4 style="color:#856404;margin:0 0 15px 0;">🛠️ Soluções Alternativas:</h4>
                    <div style="background:white;padding:15px;border-radius:5px;margin:10px 0;">
                        <h5 style="color:#495057;margin:0 0 10px 0;">1. SQL Manual via phpMyAdmin:</h5>
                        <pre style="background:#f8f9fa;padding:10px;border-radius:3px;font-size:11px;overflow-x:auto;">INSERT INTO tblmodules (module_name, installed_version, active) VALUES (\'won_api\', \'2.1.0\', 1);
INSERT INTO tbloptions (name, value) VALUES (\'won_api_token\', \'' . bin2hex(random_bytes(16)) . '\');
INSERT INTO tbloptions (name, value) VALUES (\'won_api_rate_limit\', \'100\');</pre>
                    </div>
                    <div style="background:white;padding:15px;border-radius:5px;margin:10px 0;">
                        <h5 style="color:#495057;margin:0 0 5px 0;">2. Verificar Permissões:</h5>
                        <p style="margin:0;color:#495057;">chmod 755 modules/won_api/ && chmod 644 modules/won_api/*.php</p>
                    </div>
                  </div>';
        }
        
        echo '</div>';
        
    } else {
        echo '<div style="max-width:600px;margin:50px auto;padding:30px;background:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);font-family:Arial,sans-serif;text-align:center;">
                <h2 style="color:#dc3545;margin:0 0 20px 0;">❌ ERRO: Ambiente Perfex Não Encontrado</h2>
                <p style="color:#6c757d;">Este script deve ser executado dentro de uma instalação válida do Perfex CRM.</p>
                <p style="color:#6c757d;">Certifique-se de que o módulo está em <code>/modules/won_api/</code></p>
              </div>';
    }
    exit;
}

// Executar via CodeIgniter se constantes estão definidas
$CI = &get_instance();
$CI->load->database();

try {
    echo "=== INSTALAÇÃO MANUAL WON API v2.1.0 ===\n\n";
    
    // 1. Registrar módulo
    $modules_table = db_prefix() . 'modules';
    if (!$CI->db->table_exists($modules_table)) {
        $CI->db->query("CREATE TABLE `{$modules_table}` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `module_name` VARCHAR(55) NOT NULL,
            `installed_version` VARCHAR(11) NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `module_name` (`module_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "✅ Tabela de módulos criada\n";
    }
    
    $CI->db->where('module_name', 'won_api');
    $existing = $CI->db->get($modules_table)->row();
    
    if ($existing) {
        $CI->db->where('module_name', 'won_api');
        $CI->db->update($modules_table, ['installed_version' => '2.1.0', 'active' => 1]);
        echo "✅ Módulo atualizado\n";
    } else {
        $CI->db->insert($modules_table, [
            'module_name' => 'won_api',
            'installed_version' => '2.1.0',
            'active' => 1
        ]);
        echo "✅ Módulo registrado\n";
    }
    
    // 2. Criar tabela de logs
    $logs_table = db_prefix() . 'won_api_logs';
    if (!$CI->db->table_exists($logs_table)) {
        $CI->db->query("CREATE TABLE `{$logs_table}` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `endpoint` VARCHAR(255) NOT NULL,
            `method` VARCHAR(10) NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `status` INT NOT NULL,
            `response_time` FLOAT NOT NULL,
            `date` DATETIME NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "✅ Tabela de logs criada\n";
    } else {
        echo "✅ Tabela de logs já existe\n";
    }
    
    // 3. Configurar opções mínimas
    $options = [
        'won_api_token' => bin2hex(random_bytes(32)),
        'won_api_rate_limit' => '100',
        'won_api_version' => '2.1.0'
    ];
    
    foreach ($options as $name => $value) {
        $CI->db->where('name', $name);
        if (!$CI->db->get(db_prefix() . 'options')->row()) {
            $CI->db->insert(db_prefix() . 'options', ['name' => $name, 'value' => $value]);
            echo "✅ Opção {$name} criada\n";
        }
    }
    
    echo "\n=== INSTALAÇÃO MANUAL CONCLUÍDA ===\n";
    echo "1. Acesse Admin > Módulos para verificar\n";
    echo "2. Configure em Admin > WON API > Configurações\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "\nSOLUÇÃO ALTERNATIVA (SQL manual):\n";
    echo "INSERT INTO tblmodules (module_name, installed_version, active) VALUES ('won_api', '2.1.0', 1);\n";
    echo "INSERT INTO tbloptions (name, value) VALUES ('won_api_token', '" . bin2hex(random_bytes(16)) . "');\n";
}
?> 