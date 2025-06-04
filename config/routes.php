<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * WON API Routes Configuration v2.1.2
 * Rotas para endpoints especializados e operações complexas
 */

// Rotas principais da API (mantidas)
$route['won_api/won/api/(:any)/(:num)'] = 'won_api/won/api/$1/$2';
$route['won_api/won/api/(:any)'] = 'won_api/won/api/$1';
$route['won_api/won/join'] = 'won_api/won/join';
$route['won_api/won/status'] = 'won_api/won/status';

// Rotas para endpoints especializados (novos na v2.1.2)
$route['won_api/won/estimate/convert/(:num)'] = 'won_api/won/estimate_convert/$1';
$route['won_api/won/invoice/send/(:num)'] = 'won_api/won/invoice_send/$1';
$route['won_api/won/task/complete/(:num)'] = 'won_api/won/task_complete/$1';
$route['won_api/won/task/comment/(:num)'] = 'won_api/won/task_comment/$1';
$route['won_api/won/lead/create'] = 'won_api/won/lead_create';
$route['won_api/won/dashboard/stats'] = 'won_api/won/dashboard_stats';

// Rotas administrativas
$route['admin/won_api/configuracoes'] = 'won_api/won_api/configuracoes';
$route['admin/won_api/documentation'] = 'won_api/won_api/documentation';
$route['admin/won_api/logs'] = 'won_api/won_api/logs';
$route['admin/won_api/regenerate_token'] = 'won_api/won_api/regenerate_token';

// Compatibilidade com outras integrações
$route['api/won/(:any)'] = 'won_api/won/api/$1';
$route['api/won/(:any)/(:num)'] = 'won_api/won/api/$1/$2'; 