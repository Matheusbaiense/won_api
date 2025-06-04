<?php
/**
 * Diagnóstico Ultra-Simplificado WON API v2.1.0
 * Versão otimizada com foco nos problemas críticos de instalação
 */

class Won_Api_Diagnostic_Light 
{
    private $results = [];
    private $errors = [];
    private $warnings = [];
    
    public function __construct() 
    {
        // Header com estilo responsivo
        echo '<div style="max-width:900px;margin:20px auto;font-family:Arial,sans-serif;background:#f8f9fa;border-radius:10px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                <div style="text-align:center;margin-bottom:30px;">
                    <h2 style="color:#2c3e50;margin:0;font-size:32px;">🔍 DIAGNÓSTICO WON API v2.1.0</h2>
                    <p style="color:#6c757d;margin:10px 0 0 0;font-size:16px;">Verificação rápida de problemas críticos</p>
                </div>';
    }
    
    public function run_critical_checks() 
    {
        $this->check_directory_structure();
        $this->check_file_permissions();
        $this->check_php_requirements();
        $this->check_perfex_compatibility();
        $this->check_file_integrity();
        $this->show_results();
        $this->show_solutions();
        
        echo '</div>';
        return $this->results;
    }
    
    private function check_directory_structure() 
    {
        echo '<div style="background:white;padding:20px;margin:15px 0;border-radius:8px;border-left:4px solid #17a2b8;">
                <h4 style="color:#17a2b8;margin:0 0 15px 0;">📁 1. Estrutura de Diretórios</h4>';
        
        $current_dir = basename(__DIR__);
        if ($current_dir === 'won_api') {
            echo '<p style="color:#28a745;margin:5px 0;">✅ Diretório correto: /modules/won_api/</p>';
        } else {
            $this->errors[] = "Diretório incorreto: /modules/{$current_dir}/ (deveria ser: /modules/won_api/)";
            echo '<p style="color:#dc3545;margin:5px 0;">❌ Diretório incorreto</p>';
        }
        
        $required_structure = [
            'won_api.php' => 'Arquivo principal',
            'install.php' => 'Script de instalação',
            'controllers/Won.php' => 'Controller API',
            'controllers/Won_api.php' => 'Controller Admin',
            'views/configuracoes.php' => 'Interface de configuração'
        ];
        
        $missing = [];
        foreach ($required_structure as $file => $desc) {
            if (file_exists($file)) {
                echo '<p style="color:#28a745;margin:2px 0;">✅ ' . $desc . '</p>';
            } else {
                $missing[] = $file;
                echo '<p style="color:#dc3545;margin:2px 0;">❌ ' . $desc . ' (ausente)</p>';
            }
        }
        
        if (!empty($missing)) {
            $this->errors[] = 'Arquivos ausentes: ' . implode(', ', $missing);
        }
        
        echo '</div>';
    }
    
    private function check_file_permissions() 
    {
        echo '<div style="background:white;padding:20px;margin:15px 0;border-radius:8px;border-left:4px solid #ffc107;">
                <h4 style="color:#ffc107;margin:0 0 15px 0;">🔐 2. Permissões de Arquivos</h4>';
        
        $critical_paths = [
            __DIR__ => 'Diretório principal',
            __DIR__ . '/controllers' => 'Diretório controllers',
            __DIR__ . '/views' => 'Diretório views',
            __FILE__ => 'Este arquivo'
        ];
        
        $permission_issues = [];
        foreach ($critical_paths as $path => $desc) {
            if (!file_exists($path)) {
                echo '<p style="color:#dc3545;margin:2px 0;">❌ ' . $desc . ' (não encontrado)</p>';
                continue;
            }
            
            $readable = is_readable($path);
            $writable = is_writable($path);
            $perms = substr(sprintf('%o', fileperms($path)), -3);
            
            if ($readable && (is_dir($path) ? $writable : true)) {
                echo '<p style="color:#28a745;margin:2px 0;">✅ ' . $desc . ' (perm: ' . $perms . ')</p>';
            } else {
                echo '<p style="color:#dc3545;margin:2px 0;">❌ ' . $desc . ' (problemas de permissão)</p>';
                $permission_issues[] = $path;
            }
        }
        
        if (!empty($permission_issues)) {
            $this->errors[] = 'Problemas de permissão em: ' . implode(', ', $permission_issues);
        }
        
        echo '</div>';
    }
    
    private function check_php_requirements() 
    {
        echo '<div style="background:white;padding:20px;margin:15px 0;border-radius:8px;border-left:4px solid #28a745;">
                <h4 style="color:#28a745;margin:0 0 15px 0;">🐘 3. Requisitos PHP</h4>';
        
        // Versão PHP
        $php_version = PHP_VERSION;
        echo '<p style="margin:5px 0;">Versão PHP: <strong>' . $php_version . '</strong> ';
        if (version_compare($php_version, '7.4', '>=')) {
            echo '<span style="color:#28a745;">✅ Compatível</span></p>';
        } else {
            echo '<span style="color:#dc3545;">❌ PHP 7.4+ requerido</span></p>';
            $this->errors[] = "PHP {$php_version} incompatível (mínimo: 7.4)";
        }
        
        // Extensões críticas
        $required_ext = ['json', 'curl', 'openssl', 'mbstring'];
        $missing_ext = [];
        
        foreach ($required_ext as $ext) {
            if (extension_loaded($ext)) {
                echo '<p style="color:#28a745;margin:2px 0;">✅ Extensão ' . $ext . '</p>';
            } else {
                echo '<p style="color:#dc3545;margin:2px 0;">❌ Extensão ' . $ext . ' (ausente)</p>';
                $missing_ext[] = $ext;
            }
        }
        
        if (!empty($missing_ext)) {
            $this->errors[] = 'Extensões PHP ausentes: ' . implode(', ', $missing_ext);
        }
        
        echo '</div>';
    }
    
    private function check_perfex_compatibility() 
    {
        echo '<div style="background:white;padding:20px;margin:15px 0;border-radius:8px;border-left:4px solid #6f42c1;">
                <h4 style="color:#6f42c1;margin:0 0 15px 0;">🎯 4. Compatibilidade Perfex CRM</h4>';
        
        // Verificar se está no ambiente Perfex
        $perfex_indicators = [
            'BASEPATH' => 'Constante BASEPATH',
            'FCPATH' => 'Constante FCPATH',
            'APPPATH' => 'Constante APPPATH'
        ];
        
        $perfex_detected = false;
        foreach ($perfex_indicators as $const => $desc) {
            if (defined($const)) {
                echo '<p style="color:#28a745;margin:2px 0;">✅ ' . $desc . '</p>';
                $perfex_detected = true;
            }
        }
        
        if (!$perfex_detected) {
            echo '<p style="color:#dc3545;margin:5px 0;">❌ Ambiente Perfex CRM não detectado</p>';
            $this->errors[] = 'Não está executando no ambiente Perfex CRM';
        } else {
            echo '<p style="color:#28a745;margin:5px 0;">✅ Ambiente Perfex CRM detectado</p>';
        }
        
        // Verificar estrutura de módulos
        $modules_dir = __DIR__ . '/../../';
        if (is_dir($modules_dir . 'application')) {
            echo '<p style="color:#28a745;margin:2px 0;">✅ Estrutura Perfex válida</p>';
        } else {
            echo '<p style="color:#dc3545;margin:2px 0;">❌ Estrutura Perfex inválida</p>';
            $this->warnings[] = 'Estrutura de diretórios do Perfex CRM não padrão';
        }
        
        echo '</div>';
    }
    
    private function check_file_integrity() 
    {
        echo '<div style="background:white;padding:20px;margin:15px 0;border-radius:8px;border-left:4px solid #e83e8c;">
                <h4 style="color:#e83e8c;margin:0 0 15px 0;">🔍 5. Integridade dos Arquivos</h4>';
        
        $core_files = [
            'won_api.php' => ['min_size' => 1000, 'contains' => 'won_api_module_init_menu_items'],
            'install.php' => ['min_size' => 3000, 'contains' => 'tblmodules'],
            'controllers/Won.php' => ['min_size' => 5000, 'contains' => 'class Won'],
            'controllers/Won_api.php' => ['min_size' => 1500, 'contains' => 'class Won_api']
        ];
        
        foreach ($core_files as $file => $checks) {
            if (!file_exists($file)) {
                echo '<p style="color:#dc3545;margin:2px 0;">❌ ' . $file . ' (não encontrado)</p>';
                continue;
            }
            
            $size = filesize($file);
            $content = file_get_contents($file);
            
            $size_ok = $size >= $checks['min_size'];
            $content_ok = strpos($content, $checks['contains']) !== false;
            
            if ($size_ok && $content_ok) {
                echo '<p style="color:#28a745;margin:2px 0;">✅ ' . $file . ' (' . round($size/1024, 1) . 'KB)</p>';
            } else {
                echo '<p style="color:#dc3545;margin:2px 0;">❌ ' . $file . ' (corrompido)</p>';
                $this->errors[] = "Arquivo {$file} pode estar corrompido";
            }
        }
        
        echo '</div>';
    }
    
    private function show_results() 
    {
        echo '<div style="background:white;padding:25px;margin:20px 0;border-radius:8px;text-align:center;">';
        
        if (empty($this->errors)) {
            echo '<h3 style="color:#28a745;margin:0 0 15px 0;">🎉 DIAGNÓSTICO APROVADO</h3>
                  <p style="color:#155724;margin:0;font-size:16px;">Não foram encontrados problemas críticos. O módulo deve funcionar corretamente.</p>';
        } else {
            echo '<h3 style="color:#dc3545;margin:0 0 15px 0;">⚠️ PROBLEMAS ENCONTRADOS</h3>
                  <p style="color:#721c24;margin:0 0 15px 0;font-size:16px;">' . count($this->errors) . ' erro(s) crítico(s) identificado(s):</p>
                  <ul style="text-align:left;color:#721c24;margin:0;">';
            
            foreach ($this->errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            
            echo '</ul>';
        }
        
        if (!empty($this->warnings)) {
            echo '<div style="background:#fff3cd;padding:15px;margin:15px 0;border-radius:5px;border-left:3px solid #ffc107;">
                    <p style="color:#856404;margin:0;font-weight:bold;">⚠️ Avisos (' . count($this->warnings) . '):</p>
                    <ul style="color:#856404;margin:5px 0 0 0;">';
            
            foreach ($this->warnings as $warning) {
                echo '<li>' . htmlspecialchars($warning) . '</li>';
            }
            
            echo '</ul></div>';
        }
        
        echo '</div>';
    }
    
    private function show_solutions() 
    {
        if (!empty($this->errors)) {
            echo '<div style="background:#fff5f5;padding:25px;margin:20px 0;border-radius:8px;border:2px solid #dc3545;">
                    <h3 style="color:#721c24;margin:0 0 20px 0;">🛠️ SOLUÇÕES RECOMENDADAS</h3>';
            
            echo '<div style="background:white;padding:15px;margin:10px 0;border-radius:5px;">
                    <h4 style="color:#495057;margin:0 0 10px 0;">1. Corrigir Permissões:</h4>
                    <pre style="background:#f8f9fa;padding:10px;border-radius:3px;margin:0;font-size:12px;overflow-x:auto;">chmod 755 modules/won_api/
chmod 644 modules/won_api/*.php
chmod 755 modules/won_api/controllers/
chmod 755 modules/won_api/views/</pre>
                  </div>';
            
            echo '<div style="background:white;padding:15px;margin:10px 0;border-radius:5px;">
                    <h4 style="color:#495057;margin:0 0 10px 0;">2. Instalação Manual de Emergência:</h4>
                    <pre style="background:#f8f9fa;padding:10px;border-radius:3px;margin:0;font-size:12px;overflow-x:auto;">php modules/won_api/install_manual.php</pre>
                  </div>';
            
            echo '<div style="background:white;padding:15px;margin:10px 0;border-radius:5px;">
                    <h4 style="color:#495057;margin:0 0 10px 0;">3. Registro Manual no Banco:</h4>
                    <pre style="background:#f8f9fa;padding:10px;border-radius:3px;margin:0;font-size:11px;overflow-x:auto;">INSERT INTO tblmodules (module_name, installed_version, active) 
VALUES (\'won_api\', \'2.1.0\', 1);</pre>
                  </div>';
            
            echo '<div style="background:white;padding:15px;margin:10px 0;border-radius:5px;">
                    <h4 style="color:#495057;margin:0 0 10px 0;">4. Verificar Requisitos do Servidor:</h4>
                    <ul style="margin:5px 0;color:#495057;">
                        <li>PHP 7.4+ com extensões: json, curl, openssl, mbstring</li>
                        <li>Perfex CRM 2.9.0+</li>
                        <li>Permissões de leitura/escrita no diretório modules/</li>
                    </ul>
                  </div>';
            
            echo '</div>';
        }
        
        // Informações de contato sempre
        echo '<div style="background:#e9ecef;padding:20px;margin:20px 0;border-radius:8px;text-align:center;">
                <h4 style="color:#495057;margin:0 0 15px 0;">📞 Suporte Técnico</h4>
                <p style="margin:5px 0;color:#6c757d;">Logs detalhados: <code>application/logs/</code></p>
                <p style="margin:5px 0;color:#6c757d;">Documentação: Admin → WON API → Documentação</p>
                <p style="margin:5px 0;color:#6c757d;">GitHub: https://github.com/Matheusbaiense/won_api</p>
              </div>';
    }
}

// Executar diagnóstico
$diagnostic = new Won_Api_Diagnostic_Light();
$diagnostic->run_critical_checks();
?> 