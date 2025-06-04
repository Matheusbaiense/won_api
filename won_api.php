<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: WON API
Description: API RESTful simples para Perfex CRM - Easy Install
Version: 2.1.1
Requires at least: 2.9.2
Author: Matheus Baiense
Author URI: https://github.com/Matheusbaiense
*/

define('WON_API_MODULE_NAME', 'won_api');
define('WON_API_MODULE_VERSION', '2.1.1');

// Hook do menu administrativo
hooks()->add_action('admin_init', 'won_api_init_menu');

function won_api_init_menu()
{
    if (!has_permission('modules', '', 'view') && !is_admin()) {
        return;
    }
    
    $CI = &get_instance();
    
    $CI->app_menu->add_sidebar_menu_item('won_api', [
        'name'     => 'WON API',
        'collapse' => true,
        'position' => 10,
        'icon'     => 'fa fa-code',
    ]);

    $CI->app_menu->add_sidebar_children_item('won_api', [
        'slug' => 'won_api_settings',
        'name' => 'Configurações',
        'href' => admin_url('won_api/settings'),
        'position' => 5,
    ]);

    $CI->app_menu->add_sidebar_children_item('won_api', [
        'slug' => 'won_api_docs',
        'name' => 'Documentação',
        'href' => admin_url('won_api/docs'),
        'position' => 10,
    ]);
}

// Hook de ativação
register_activation_hook(WON_API_MODULE_NAME, 'won_api_activation');
function won_api_activation()
{
    $CI = &get_instance();
    
    // Criar token se não existir
    if (!get_option('won_api_token')) {
        $token = bin2hex(random_bytes(32));
        add_option('won_api_token', $token);
    }
    
    // Configurações básicas
    add_option('won_api_version', '2.1.1', 1);
    add_option('won_api_cors_enabled', '1', 1);
    
    log_message('info', '[WON API] Módulo ativado v2.1.1');
}

// Hook de desinstalação
register_uninstall_hook(WON_API_MODULE_NAME, 'won_api_uninstall');
function won_api_uninstall()
{
    delete_option('won_api_token');
    delete_option('won_api_version');
    delete_option('won_api_cors_enabled');
    
    log_message('info', '[WON API] Módulo desinstalado');
}