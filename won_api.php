<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Won API
Description: Este módulo fornece uma API para integração com o Perfex CRM.
Author: Won Ecosystem
Author URI: https://wonecosystem.com.br
Version: 1.0.0
Requires at least: 2.3.*
*/

define('WON_API_MODULE_NAME', 'won_api');

hooks()->add_action('admin_init', 'won_api_module_init_menu_items');

/**
 * Inicializa os itens do menu no painel administrativo
 */
function won_api_module_init_menu_items()
{
    $CI = &get_instance();

    if (isset($CI->app_menu)) {
        $CI->app_menu->add_sidebar_menu_item('won_api', [
            'name'     => 'Won API',
            'collapse' => true,
            'position' => 10,
            'icon'     => 'fa fa-plug',
        ]);

        $CI->app_menu->add_sidebar_children_item('won_api', [
            'slug'     => 'won_api_config',
            'name'     => 'Configurações',
            'href'     => admin_url('won_api/configuracoes'),
            'position' => 5,
        ]);

        $CI->app_menu->add_sidebar_children_item('won_api', [
            'slug'     => 'won_api_instructions',
            'name'     => 'Instruções da API',
            'href'     => admin_url('won_api/instructions'),
            'position' => 10,
        ]);

        $CI->app_menu->add_sidebar_children_item('won_api', [
            'slug'     => 'won_api_tables',
            'name'     => 'Listar Tabelas',
            'href'     => admin_url('won_api/tables'),
            'position' => 15,
        ]);
    } else {
        log_message('error', '[Won API] Não foi possível carregar o app_menu.');
    }
}

/**
 * Hook de ativação do módulo
 */
register_activation_hook(WON_API_MODULE_NAME, 'won_api_module_activation_hook');
function won_api_module_activation_hook()
{
    $CI = &get_instance();
    $installPath = __DIR__ . '/install.php';
    if (file_exists($installPath)) {
        require_once($installPath);
    } else {
        log_message('error', '[Won API] Arquivo install.php não encontrado.');
    }
}

/**
 * Hook de desinstalação do módulo
 */
register_uninstall_hook(WON_API_MODULE_NAME, 'won_api_uninstall_hook');
function won_api_uninstall_hook()
{
    $uninstallPath = __DIR__ . '/uninstall.php';
    if (file_exists($uninstallPath)) {
        require_once($uninstallPath);
    } else {
        log_message('error', '[Won API] Arquivo uninstall.php não encontrado.');
    }
}