<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * WON API Module Info - Versão Simplificada
 */

// Informações essenciais
$module = [
    'name' => 'won_api',
    'version' => '2.1.0',
    'description' => 'API RESTful para Perfex CRM com autenticação, rate limiting e logs',
    'author' => 'Matheus Baiense',
    'requires' => [
        'perfex_version' => '2.9.2',
        'php_version' => '7.4'
    ]
];

// Registrar hooks essenciais
hooks()->add_action('admin_init', 'won_api_init_admin_menu');

/**
 * Inicializar menu administrativo
 */
function won_api_init_admin_menu()
{
    if (has_permission('view', '', 'won_api')) {
        $CI = &get_instance();
        
        $CI->app_menu->add_sidebar_menu_item('won_api', [
            'name' => 'WON API',
            'href' => admin_url('won_api/configuracoes'),
            'icon' => 'fa fa-code',
            'position' => 50,
        ]);
        
        $CI->app_menu->add_sidebar_children_item('won_api', [
            'slug' => 'won_api_config',
            'name' => 'Configurações',
            'href' => admin_url('won_api/configuracoes'),
            'icon' => 'fa fa-cog'
        ]);
        
        $CI->app_menu->add_sidebar_children_item('won_api', [
            'slug' => 'won_api_docs',
            'name' => 'Documentação',
            'href' => admin_url('won_api/documentation'),
            'icon' => 'fa fa-book'
        ]);
        
        $CI->app_menu->add_sidebar_children_item('won_api', [
            'slug' => 'won_api_logs',
            'name' => 'Logs',
            'href' => admin_url('won_api/logs'),
            'icon' => 'fa fa-list'
        ]);
    }
}

/**
 * Verificar compatibilidade
 */
function won_api_check_compatibility()
{
    $errors = [];
    
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        $errors[] = 'PHP 7.4+ obrigatório';
    }
    
    $extensions = ['json', 'curl', 'openssl'];
    foreach ($extensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = "Extensão {$ext} ausente";
        }
    }
    
    return $errors;
} 