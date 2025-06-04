<?php
/**
 * Diagnóstico Simples WON API v2.1.1 Easy Install
 * Verificação básica para instalação via Easy Install
 */

echo "<h1>🔍 Diagnóstico WON API v2.1.1 Easy Install</h1>";

// Verificar se está no contexto do Perfex CRM
if (!defined('BASEPATH')) {
    echo "<p style='color: red;'>❌ Execute este script através do Perfex CRM</p>";
    exit;
}

$errors = [];
$warnings = [];
$success = [];

// 1. Verificar arquivo principal
if (file_exists(__DIR__ . '/won_api.php')) {
    $success[] = "✅ Arquivo principal encontrado";
} else {
    $errors[] = "❌ Arquivo principal won_api.php não encontrado";
}

// 2. Verificar controlador
if (file_exists(__DIR__ . '/controllers/Won.php')) {
    $success[] = "✅ Controlador principal encontrado";
} else {
    $errors[] = "❌ Controlador Won.php não encontrado";
}

// 3. Verificar token
$token = get_option('won_api_token');
if (!empty($token)) {
    $success[] = "✅ Token de API configurado";
} else {
    $errors[] = "❌ Token de API não configurado";
}

// 4. Verificar versão
$version = get_option('won_api_version');
if ($version === '2.1.1') {
    $success[] = "✅ Versão 2.1.1 detectada";
} else {
    $warnings[] = "⚠️ Versão incorreta ou não configurada: " . ($version ?: 'não definida');
}

// 5. Verificar tabelas necessárias do Perfex CRM
$CI = &get_instance();
$required_tables = ['clients', 'contacts', 'projects', 'tasks', 'invoices', 'leads', 'staff'];
foreach ($required_tables as $table) {
    if ($CI->db->table_exists(db_prefix() . 'tbl' . $table)) {
        $success[] = "✅ Tabela tbl{$table} encontrada";
    } else {
        $warnings[] = "⚠️ Tabela tbl{$table} pode não existir";
    }
}

// 6. Verificar extensões PHP
$required_extensions = ['json', 'curl', 'hash'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        $success[] = "✅ Extensão PHP {$ext} disponível";
    } else {
        $errors[] = "❌ Extensão PHP {$ext} não encontrada";
    }
}

// Exibir resultados
echo "<h2>📊 Resultados:</h2>";

if (!empty($success)) {
    echo "<h3 style='color: green;'>✅ Sucessos:</h3><ul>";
    foreach ($success as $item) {
        echo "<li>{$item}</li>";
    }
    echo "</ul>";
}

if (!empty($warnings)) {
    echo "<h3 style='color: orange;'>⚠️ Avisos:</h3><ul>";
    foreach ($warnings as $item) {
        echo "<li>{$item}</li>";
    }
    echo "</ul>";
}

if (!empty($errors)) {
    echo "<h3 style='color: red;'>❌ Erros:</h3><ul>";
    foreach ($errors as $item) {
        echo "<li>{$item}</li>";
    }
    echo "</ul>";
}

// Status final
$error_count = count($errors);
$warning_count = count($warnings);

if ($error_count === 0) {
    echo "<h2 style='color: green;'>🎉 WON API v2.1.1 Easy Install - FUNCIONANDO!</h2>";
    echo "<p>API URL: <code>" . base_url('won_api/won/status') . "</code></p>";
    echo "<p>Token: <code>" . substr($token, 0, 20) . "...</code></p>";
} else {
    echo "<h2 style='color: red;'>🚨 Problemas encontrados: {$error_count} erros, {$warning_count} avisos</h2>";
}

echo "<hr><p><strong>WON API v2.1.1 Easy Install</strong> - Compatível com Perfex CRM Easy Install</p>";
?> 