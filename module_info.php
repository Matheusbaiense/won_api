<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Informações do Módulo WON API
 * 
 * Este arquivo contém as informações essenciais para o registro
 * e funcionamento do módulo no Perfex CRM
 */

// Informações básicas do módulo
$module_name = 'won_api';
$module_version = '2.1.0';
$module_description = 'API RESTful avançada para integração com Perfex CRM. Fornece endpoints seguros para CRUD de dados, autenticação robusta, rate limiting, logs detalhados e interface administrativa completa.';
$module_lang_name = 'won_api';
$module_lang_folder = 'portuguese_br';

// Autor e informações de contato
$module_author = 'Matheus Baiense';
$module_author_email = 'matheusbaiense@example.com';
$module_author_url = 'https://github.com/Matheusbaiense/won_api';

// Compatibilidade e requisitos
$perfex_min_version = '2.9.2';
$perfex_max_version = '3.0.0';
$php_min_version = '7.4';

// Recursos do módulo
$module_features = [
    'API RESTful completa',
    'Autenticação segura com tokens',
    'Rate limiting configurável',
    'Logs detalhados de acesso',
    'Interface administrativa',
    'Validação robusta de dados',
    'Suporte a paginação',
    'Cache inteligente',
    'Documentação interativa',
    'Testes automatizados'
];

// Tabelas que o módulo pode acessar (lista branca)
$allowed_tables = [
    'clients' => 'Clientes',
    'projects' => 'Projetos', 
    'tasks' => 'Tarefas',
    'staff' => 'Funcionários',
    'leads' => 'Leads',
    'estimates' => 'Orçamentos',
    'invoices' => 'Faturas',
    'payments' => 'Pagamentos',
    'expenses' => 'Despesas',
    'contracts' => 'Contratos',
    'proposals' => 'Propostas',
    'tickets' => 'Tickets',
    'knowledge_base' => 'Base de Conhecimento'
];

// Permissões necessárias
$required_permissions = [
    'view_api_logs' => 'Visualizar logs da API',
    'manage_api_settings' => 'Gerenciar configurações da API',
    'regenerate_api_token' => 'Regenerar token da API'
];

// URLs e endpoints do módulo
$module_urls = [
    'admin' => 'admin/won_api',
    'api_base' => 'api/won',
    'documentation' => 'admin/won_api/documentation',
    'logs' => 'admin/won_api/logs',
    'settings' => 'admin/won_api/settings'
];

// Configurações padrão que serão criadas na instalação
$default_options = [
    'won_api_token' => '',
    'won_api_rate_limit' => '100',
    'won_api_cache_duration' => '300',
    'won_api_log_level' => 'basic',
    'won_api_cors_enabled' => '1',
    'won_api_cors_origins' => '*',
    'won_api_cors_methods' => 'GET,POST,PUT,DELETE,OPTIONS',
    'won_api_cors_headers' => 'Authorization,Content-Type,X-Requested-With',
    'won_api_whitelist_tables' => 'clients,projects,tasks,staff,leads,estimates,invoices,payments,expenses,contracts,proposals,tickets,knowledge_base',
    'won_api_max_page_size' => '100',
    'won_api_default_page_size' => '20',
    'won_api_cache_enabled' => '1',
    'won_api_debug_mode' => '0',
    'won_api_require_https' => '1',
    'won_api_allowed_ips' => '',
    'won_api_webhook_url' => '',
    'won_api_webhook_secret' => ''
];

// Funções de validação
function validate_module_requirements() {
    $errors = [];
    
    // Verificar versão do PHP
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        $errors[] = 'PHP 7.4 ou superior é obrigatório. Versão atual: ' . PHP_VERSION;
    }
    
    // Verificar extensões PHP necessárias
    $required_extensions = ['json', 'curl', 'openssl', 'mbstring'];
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = "Extensão PHP '{$ext}' não encontrada.";
        }
    }
    
    // Verificar permissões de diretórios
    $required_dirs = [
        FCPATH . 'modules/won_api/',
        FCPATH . 'modules/won_api/logs/'
    ];
    
    foreach ($required_dirs as $dir) {
        if (!is_writable($dir)) {
            $errors[] = "Diretório '{$dir}' não tem permissões de escrita.";
        }
    }
    
    return $errors;
}

// Informações de changelog
$changelog = [
    '2.1.0' => [
        'date' => '2024-01-15',
        'changes' => [
            'Implementação de rate limiting avançado',
            'Sistema de logs aprimorado',
            'Interface administrativa redesenhada',
            'Validação robusta de dados',
            'Suporte a cache inteligente',
            'Documentação interativa',
            'Testes automatizados completos',
            'Melhorias de segurança'
        ]
    ],
    '2.0.0' => [
        'date' => '2024-01-01', 
        'changes' => [
            'Reescrita completa da API',
            'Padronização REST',
            'Sistema de autenticação robusto',
            'Paginação automática',
            'CORS configurável'
        ]
    ]
];

// Configurações de menu administrativo
$admin_menu = [
    'name' => 'WON API',
    'icon' => 'fa fa-code',
    'position' => 50,
    'submenu' => [
        [
            'name' => 'Configurações',
            'url' => 'admin/won_api/settings',
            'icon' => 'fa fa-cog',
            'permission' => 'manage_api_settings'
        ],
        [
            'name' => 'Logs',
            'url' => 'admin/won_api/logs', 
            'icon' => 'fa fa-list-alt',
            'permission' => 'view_api_logs'
        ],
        [
            'name' => 'Documentação',
            'url' => 'admin/won_api/documentation',
            'icon' => 'fa fa-book',
            'permission' => 'view'
        ],
        [
            'name' => 'Testes',
            'url' => 'admin/won_api/tests',
            'icon' => 'fa fa-check-circle',
            'permission' => 'manage_api_settings'
        ]
    ]
];

// Verificar se este é o arquivo de informações correto
if (!defined('WON_API_MODULE_INFO')) {
    define('WON_API_MODULE_INFO', true);
} 