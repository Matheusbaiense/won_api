<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Configuração Centralizada de Tabelas - WON API v2.1.0
 */

// Tabelas permitidas com configurações completas
$config['won_api_tables'] = [
    'clients' => [
        'table_name' => 'tblclients',
        'primary_key' => 'userid',
        'required_fields' => ['company'],
        'readonly_fields' => ['userid', 'datecreated'],
        'searchable_fields' => ['company', 'email', 'phonenumber', 'city'],
        'validation' => [
            'email' => 'valid_email',
            'vat' => 'numeric'
        ]
    ],
    'projects' => [
        'table_name' => 'tblprojects',
        'primary_key' => 'id',
        'required_fields' => ['name', 'clientid'],
        'readonly_fields' => ['id', 'datecreated'],
        'searchable_fields' => ['name', 'description'],
        'validation' => [
            'clientid' => 'numeric|required'
        ]
    ],
    'tasks' => [
        'table_name' => 'tbltasks',
        'primary_key' => 'id',
        'required_fields' => ['name'],
        'readonly_fields' => ['id', 'datecreated'],
        'searchable_fields' => ['name', 'description'],
        'validation' => []
    ],
    'staff' => [
        'table_name' => 'tblstaff',
        'primary_key' => 'staffid',
        'required_fields' => ['firstname', 'lastname', 'email'],
        'readonly_fields' => ['staffid', 'datecreated'],
        'searchable_fields' => ['firstname', 'lastname', 'email'],
        'validation' => [
            'email' => 'valid_email|required'
        ]
    ],
    'leads' => [
        'table_name' => 'tblleads',
        'primary_key' => 'id',
        'required_fields' => ['name'],
        'readonly_fields' => ['id', 'datecreated'],
        'searchable_fields' => ['name', 'email', 'company'],
        'validation' => [
            'email' => 'valid_email'
        ]
    ],
    'invoices' => [
        'table_name' => 'tblinvoices',
        'primary_key' => 'id',
        'required_fields' => ['clientid'],
        'readonly_fields' => ['id', 'datecreated'],
        'searchable_fields' => ['number', 'clientid'],
        'validation' => [
            'clientid' => 'numeric|required'
        ]
    ]
];

// Rate limiting configurações
$config['won_api_rate_limit'] = 100;
$config['won_api_rate_limit_window'] = 3600; // 1 hora em segundos
$config['won_api_pagination_default'] = 20;
$config['won_api_pagination_max'] = 100; 