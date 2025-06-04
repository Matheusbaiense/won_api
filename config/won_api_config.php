<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Configurações Essenciais do WON API v2.1.0
 */

// Informações básicas
$config['won_api_version'] = '2.1.0';
$config['won_api_debug'] = false;

// Segurança
$config['won_api_require_https'] = true;
$config['won_api_rate_limit'] = 100;
$config['won_api_rate_limit_window'] = 3600; // 1 hora

// Tabelas permitidas com configurações
$config['won_api_allowed_tables'] = [
    'clients' => ['required' => ['company'], 'readonly' => ['userid', 'datecreated']],
    'projects' => ['required' => ['name', 'clientid'], 'readonly' => ['id', 'datecreated']],
    'tasks' => ['required' => ['name'], 'readonly' => ['id', 'datecreated']],
    'staff' => ['required' => ['firstname', 'lastname', 'email'], 'readonly' => ['staffid']],
    'leads' => ['required' => ['name'], 'readonly' => ['id', 'datecreated']],
    'invoices' => ['required' => ['clientid'], 'readonly' => ['id', 'datecreated']]
];

// Paginação
$config['won_api_pagination_default'] = 20;
$config['won_api_pagination_max'] = 100; 