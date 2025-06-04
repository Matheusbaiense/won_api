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

// Registrar hooks
hooks()->add_action('admin_init', 'won_api_module_init_menu_items');

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

// Hooks de ativação/desinstalação
register_activation_hook(WON_API_MODULE_NAME, 'won_api_module_activation_hook');
function won_api_module_activation_hook()
{
    $CI = &get_instance();
    $install_path = __DIR__ . '/install.php';
    if (file_exists($install_path)) {
        require_once($install_path);
    }
}

register_uninstall_hook(WON_API_MODULE_NAME, 'won_api_module_uninstall_hook');
function won_api_module_uninstall_hook()
{
    $uninstall_path = __DIR__ . '/uninstall.php';
    if (file_exists($uninstall_path)) {
        require_once($uninstall_path);
    }
}