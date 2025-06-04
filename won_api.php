<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: WON API
Description: API RESTful profissional para integração com Perfex CRM v2.1.1
Version: 2.1.1
Requires at least: 2.9.2
Author: Matheus Baiense
Author URI: https://github.com/Matheusbaiense
*/

define('WON_API_MODULE_NAME', 'won_api');
define('WON_API_MODULE_VERSION', '2.1.1');

// HOOKS SIMPLES - PADRÃO PERFEX CRM
hooks()->add_action('admin_init', 'won_api_module_init_menu_items');

/**
 * Menu administrativo - SIMPLIFICADO e DEFENSIVO
 */
function won_api_module_init_menu_items()
{
    $CI = &get_instance();
    
    // Verificar se o usuário tem permissão
    if (!has_permission('modules', '', 'view') && !is_admin()) {
        return;
    }
    
    // Menu apenas se módulo estiver configurado
    if (get_option('won_api_token')) {
        // Menu principal
        $CI->app_menu->add_sidebar_menu_item('won_api', [
            'name'     => 'WON API',
            'collapse' => true,
            'position' => 10,
            'icon'     => 'fa fa-code',
        ]);

        // Submenus
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
 * Hook de ativação - ULTRA SIMPLES para Easy Install
 */
register_activation_hook(WON_API_MODULE_NAME, 'won_api_module_activation_hook');
function won_api_module_activation_hook()
{
    // Instalação direta e simples
    $CI = &get_instance();
    $CI->load->database();
    
    try {
        // 1. APENAS criar o token se não existir
        if (!get_option('won_api_token')) {
            $token = bin2hex(random_bytes(32));
            add_option('won_api_token', $token);
            log_message('info', '[WON API] Token criado: Easy Install');
        }
        
        // 2. Configurações básicas mínimas
        $basic_options = [
            'won_api_rate_limit' => '100',
            'won_api_version' => '2.1.1',
            'won_api_cors_enabled' => '1'
        ];
        
        foreach ($basic_options as $name => $value) {
            if (!get_option($name)) {
                add_option($name, $value);
            }
        }
        
        log_message('info', '[WON API] Módulo ativado com sucesso - v2.1.1 Easy Install');
        
    } catch (Exception $e) {
        log_message('error', '[WON API] Erro na ativação Easy Install: ' . $e->getMessage());
    }
}

/**
 * Hook de desinstalação - SIMPLES
 */
register_uninstall_hook(WON_API_MODULE_NAME, 'won_api_module_uninstall_hook');
function won_api_module_uninstall_hook()
{
    $CI = &get_instance();
    $CI->load->database();
    
    // Remover apenas as opções do módulo
    $options_to_remove = [
        'won_api_token',
        'won_api_rate_limit', 
        'won_api_version',
        'won_api_cors_enabled'
    ];
    
    foreach ($options_to_remove as $option) {
        delete_option($option);
    }
    
    // Remover tabela rate limit se existir
    $rate_limit_table = db_prefix() . 'won_api_rate_limit';
    if ($CI->db->table_exists($rate_limit_table)) {
        $CI->db->query("DROP TABLE `{$rate_limit_table}`");
    }
    
    log_message('info', '[WON API] Módulo desinstalado');
}