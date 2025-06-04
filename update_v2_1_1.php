<?php
/**
 * Script de Atualização WON API v2.1.0 → v2.1.1
 */

if (!defined('BASEPATH')) {
    define('BASEPATH', true);
    $perfex_root = dirname(dirname(__DIR__));
    
    if (file_exists($perfex_root . '/application/config/database.php')) {
        echo '<div style="max-width:800px;margin:20px auto;padding:25px;background:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);font-family:Arial,sans-serif;">
                <h2 style="color:#28a745;margin:0 0 20px 0;">🚀 ATUALIZAÇÃO WON API v2.1.1</h2>';
        
        try {
            include $perfex_root . '/application/config/database.php';
            $db_config = $db['default'];
            
            $pdo = new PDO(
                "mysql:host={$db_config['hostname']};dbname={$db_config['database']};charset=utf8mb4",
                $db_config['username'],
                $db_config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Atualizar versão
            $modules_table = $db_config['dbprefix'] . 'modules';
            $stmt = $pdo->prepare("UPDATE `{$modules_table}` SET installed_version = ? WHERE module_name = ?");
            $stmt->execute(['2.1.1', 'won_api']);
            
            echo '<div style="background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;">
                    <p style="color:#155724;margin:0;">✅ Módulo atualizado para v2.1.1</p>
                  </div>';
            
            // Melhorar tabela de rate limiting
            $rate_limit_table = $db_config['dbprefix'] . 'won_api_rate_limit';
            $columns = $pdo->query("SHOW COLUMNS FROM `{$rate_limit_table}` LIKE 'last_request'")->rowCount();
            
            if ($columns == 0) {
                $pdo->exec("ALTER TABLE `{$rate_limit_table}` 
                    ADD COLUMN `last_request` DATETIME NULL,
                    ADD COLUMN `user_agent` VARCHAR(500) NULL,
                    ADD INDEX `ip_idx` (`ip_address`)");
                
                echo '<div style="background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;">
                        <p style="color:#155724;margin:0;">✅ Tabela de rate limiting melhorada</p>
                      </div>';
            }
            
            // Adicionar configurações
            $options_table = $db_config['dbprefix'] . 'options';
            $new_options = [
                'won_api_version' => '2.1.1',
                'won_api_cors_enabled' => '1',
                'won_api_cors_origins' => '*',
                'won_api_timeout' => '30'
            ];
            
            foreach ($new_options as $name => $value) {
                $stmt = $pdo->prepare("SELECT id FROM `{$options_table}` WHERE name = ?");
                $stmt->execute([$name]);
                
                if ($stmt->rowCount() > 0) {
                    $pdo->prepare("UPDATE `{$options_table}` SET value = ? WHERE name = ?")
                        ->execute([$value, $name]);
                } else {
                    $pdo->prepare("INSERT INTO `{$options_table}` (name, value) VALUES (?, ?)")
                        ->execute([$name, $value]);
                }
            }
            
            echo '<div style="background:#28a745;color:white;padding:20px;border-radius:8px;margin:20px 0;text-align:center;">
                    <h3 style="margin:0;">🎉 ATUALIZAÇÃO CONCLUÍDA!</h3>
                    <p style="margin:10px 0 0 0;">WON API v2.1.1 pronto para uso</p>
                  </div>';
            
        } catch (Exception $e) {
            echo '<div style="background:#f8d7da;padding:20px;border-radius:8px;margin:20px 0;">
                    <h3 style="color:#721c24;">❌ ERRO: ' . htmlspecialchars($e->getMessage()) . '</h3>
                  </div>';
        }
        
        echo '</div>';
    }
    exit;
}
?> 