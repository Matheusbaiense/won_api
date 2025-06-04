<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: WON API
Description: API RESTful para integração completa com Perfex CRM
Version: 2.1.0
Requires at least: 2.9.2
Author: Matheus Baiense
Author URI: https://github.com/Matheusbaiense
*/

define('WON_API_MODULE_NAME', 'won_api');
define('WON_API_MODULE_VERSION', '2.1.0');

/**
 * Registrar hooks do módulo
 */
hooks()->add_action('admin_init', 'won_api_module_init_menu_items');

/**
 * Inicializar menu no painel administrativo
 */
function won_api_module_init_menu_items()
{
    $CI = &get_instance();
    
    if (has_permission('modules', '', 'view') || is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('won_api', [
            'name'     => 'WON API',
            'collapse' => true,
            'position' => 10,
            'icon'     => 'fa fa-code',
        ]);

        $CI->app_menu->add_sidebar_children_item('won_api', [
            'slug'     => 'won_api_config',
            'name'     => 'Configurações',
            'href'     => admin_url('won_api/configuracoes'),
            'position' => 5,
        ]);

        $CI->app_menu->add_sidebar_children_item('won_api', [
            'slug'     => 'won_api_docs',
            'name'     => 'Documentação',
            'href'     => admin_url('won_api/documentation'),
            'position' => 10,
        ]);

        $CI->app_menu->add_sidebar_children_item('won_api', [
            'slug'     => 'won_api_logs',
            'name'     => 'Logs',
            'href'     => admin_url('won_api/logs'),
            'position' => 15,
        ]);
    }
}

/**
 * Hook de ativação do módulo
 */
register_activation_hook(WON_API_MODULE_NAME, 'won_api_module_activation_hook');
function won_api_module_activation_hook()
{
    $CI = &get_instance();
    $CI->load->database();
    
    try {
        // Executar instalação
        $install_path = module_dir_path('won_api', 'install.php');
        if (file_exists($install_path)) {
            require_once($install_path);
        }
        
        // Criar permissões
        if (!$CI->db->get_where(db_prefix() . 'permissions', ['name' => 'won_api'])->row()) {
            $CI->db->insert(db_prefix() . 'permissions', [
                'name' => 'won_api',
                'shortname' => 'won_api'
            ]);
        }
        
        log_message('info', 'WON API: Módulo ativado com sucesso');
        
    } catch (Exception $e) {
        log_message('error', 'WON API: Erro na ativação - ' . $e->getMessage());
    }
}

/**
 * Hook de desinstalação do módulo
 */
register_uninstall_hook(WON_API_MODULE_NAME, 'won_api_module_uninstall_hook');
function won_api_module_uninstall_hook()
{
    $uninstall_path = module_dir_path('won_api', 'uninstall.php');
    if (file_exists($uninstall_path)) {
        require_once($uninstall_path);
    }
}

/**
 * Configuração do módulo para o Perfex
 */
$config['modules']['won_api'] = [
    'version' => WON_API_MODULE_VERSION,
    'name' => 'WON API',
    'description' => 'API RESTful completa para integração com Perfex CRM - Compatível com n8n, Zapier e outras ferramentas',
    'author' => 'Matheus Baiense',
    'requires_php' => '7.4',
    'requires_perfex' => '2.9.2'
];

/**
 * Função helper para compatibilidade
 */
if (!function_exists('module_dir_path')) {
    function module_dir_path($module_name, $file = '') {
        return APPPATH . 'modules/' . $module_name . '/' . $file;
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