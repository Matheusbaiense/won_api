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

// Tabelas permitidas
$config['won_api_allowed_tables'] = [
    'clients' => ['required' => ['company'], 'readonly' => ['userid', 'datecreated']],
    'projects' => ['required' => ['name', 'clientid'], 'readonly' => ['id', 'datecreated']],
    'tasks' => ['required' => ['name'], 'readonly' => ['id', 'datecreated']],
    'staff' => ['required' => ['firstname', 'lastname', 'email'], 'readonly' => ['staffid']],
    'leads' => ['required' => ['name'], 'readonly' => ['id', 'datecreated']],
    'invoices' => ['required' => ['clientid'], 'readonly' => ['id', 'datecreated']],
    'payments' => ['required' => ['invoiceid', 'amount'], 'readonly' => ['id']],
    'tickets' => ['required' => ['subject', 'department'], 'readonly' => ['ticketid']]
];

// Validações
$config['won_api_validation_rules'] = [
    'email' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
    'cpf' => '/^\d{11}$/',
    'cnpj' => '/^\d{14}$/',
    'date' => '/^\d{4}-\d{2}-\d{2}$/'
];

// Códigos de erro
$config['won_api_error_codes'] = [
    'E001' => 'Token inválido',
    'E002' => 'Rate limit excedido',
    'E003' => 'Tabela não permitida',
    'E004' => 'Campos obrigatórios ausentes',
    'E005' => 'Dados inválidos',
    'E006' => 'Registro não encontrado'
];

// Paginação
$config['won_api_pagination_default'] = 20;
$config['won_api_pagination_max'] = 100; 