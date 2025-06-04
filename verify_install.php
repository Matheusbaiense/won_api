<?php
/**
 * Verificador de Instalação WON API v2.1.0
 * Script simplificado para identificar problemas
 */

echo "=== VERIFICADOR WON API v2.1.0 ===\n\n";

// 1. Verificar estrutura de arquivos
echo "1. Verificando estrutura de arquivos...\n";
$required_files = [
    'won_api.php',
    'install.php', 
    'module_info.php',
    'controllers/Won.php',
    'controllers/Won_api.php',
    'views/configuracoes.php'
];

$missing_files = [];
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        $missing_files[] = $file;
    }
}

if (empty($missing_files)) {
    echo "✅ Todos os arquivos necessários encontrados\n";
} else {
    echo "❌ Arquivos ausentes: " . implode(', ', $missing_files) . "\n";
}

// 2. Verificar permissões
echo "\n2. Verificando permissões...\n";
$current_dir = __DIR__;
if (is_readable($current_dir) && is_writable($current_dir)) {
    echo "✅ Permissões do diretório: OK\n";
} else {
    echo "❌ Problemas de permissão no diretório\n";
}

// 3. Verificar conteúdo dos arquivos críticos
echo "\n3. Verificando conteúdo dos arquivos...\n";

// Verificar module_info.php
if (file_exists('module_info.php')) {
    $content = file_get_contents('module_info.php');
    if (strpos($content, 'won_api') !== false) {
        echo "✅ module_info.php: Nome do módulo correto\n";
    } else {
        echo "❌ module_info.php: Nome do módulo incorreto\n";
    }
} else {
    echo "❌ module_info.php: Arquivo não encontrado\n";
}

// Verificar install.php
if (file_exists('install.php')) {
    $content = file_get_contents('install.php');
    if (strpos($content, 'tblmodules') !== false) {
        echo "✅ install.php: Registro de módulo encontrado\n";
    } else {
        echo "❌ install.php: Registro de módulo ausente\n";
    }
} else {
    echo "❌ install.php: Arquivo não encontrado\n";
}

// 4. Verificar compatibilidade PHP
echo "\n4. Verificando compatibilidade PHP...\n";
echo "Versão atual: " . PHP_VERSION . "\n";

if (version_compare(PHP_VERSION, '7.4', '>=')) {
    echo "✅ Versão PHP compatível\n";
} else {
    echo "❌ PHP 7.4+ requerido\n";
}

// Verificar extensões
$extensions = ['json', 'curl', 'openssl'];
$missing_ext = [];
foreach ($extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_ext[] = $ext;
    }
}

if (empty($missing_ext)) {
    echo "✅ Extensões PHP: OK\n";
} else {
    echo "❌ Extensões ausentes: " . implode(', ', $missing_ext) . "\n";
}

// 5. Instruções de correção
echo "\n=== INSTRUÇÕES DE CORREÇÃO ===\n\n";

if (!empty($missing_files)) {
    echo "📁 ARQUIVOS AUSENTES:\n";
    echo "- Faça download completo do módulo\n";
    echo "- Verifique se todos os arquivos foram enviados\n\n";
}

echo "🔧 CORREÇÕES COMUNS:\n\n";

echo "1. PROBLEMA: Módulo não aparece na lista\n";
echo "   SOLUÇÃO: Verificar se está em /modules/won_api/\n";
echo "   COMANDO: chmod 755 modules/won_api\n\n";

echo "2. PROBLEMA: Erro de instalação\n";
echo "   SOLUÇÃO: Instalar manualmente via SQL:\n";
echo "   SQL: INSERT INTO tblmodules (module_name, installed_version, active) VALUES ('won_api', '2.1.0', 1);\n\n";

echo "3. PROBLEMA: API retorna 404\n";
echo "   SOLUÇÃO: Adicionar rota em application/config/routes.php:\n";
echo "   CÓDIGO: \$route['api/won/(.+)'] = 'won_api/won/\$1';\n\n";

echo "4. PROBLEMA: Token não funciona\n";
echo "   SOLUÇÃO: Regenerar em Admin > WON API > Configurações\n\n";

echo "5. PROBLEMA: Permissões\n";
echo "   COMANDO: chmod 755 diretórios, chmod 644 arquivos\n\n";

echo "=== VERIFICAÇÃO CONCLUÍDA ===\n";
echo "Use estas informações para corrigir problemas de instalação.\n";

if (isset($this->results['registration']) && strpos($this->results['registration'], 'ERRO') !== false) {
    echo "🔧 MÓDULO NÃO APARECE:\n";
    echo "   1. Execute instalação manual: php install_manual.php\n";
    echo "   2. SQL direto: INSERT INTO " . db_prefix() . "modules (module_name, installed_version, active) VALUES ('won_api', '2.1.0', 1);\n\n";
}
?> 