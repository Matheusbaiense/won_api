<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * WON API Routes v2.1.1 - APENAS ROTAS COM MÉTODOS IMPLEMENTADOS
 * Zero rotas fantasma - cada rota verificada no controller
 */

// Rotas da API (implementadas em Won.php)
$route['won_api/won/api/(:any)/(:num)'] = 'won_api/won/api/$1/$2';
$route['won_api/won/api/(:any)'] = 'won_api/won/api/$1';
$route['won_api/won/join'] = 'won_api/won/join';
$route['won_api/won/status'] = 'won_api/won/status';

// Rotas administrativas (implementadas em Won_api.php)
$route['admin/won_api/settings'] = 'won_api/won_api/settings';
$route['admin/won_api/docs'] = 'won_api/won_api/docs';
$route['admin/won_api/logs'] = 'won_api/won_api/logs';
$route['admin/won_api/regenerate_token'] = 'won_api/won_api/regenerate_token';

// Rotas para endpoints especializados (novos na v2.1.2)
$route['won_api/won/estimate/convert/(:num)'] = 'won_api/won/estimate_convert/$1';
$route['won_api/won/invoice/send/(:num)'] = 'won_api/won/invoice_send/$1';
$route['won_api/won/task/complete/(:num)'] = 'won_api/won/task_complete/$1';
$route['won_api/won/task/comment/(:num)'] = 'won_api/won/task_comment/$1';
$route['won_api/won/lead/create'] = 'won_api/won/lead_create';
$route['won_api/won/dashboard/stats'] = 'won_api/won/dashboard_stats';

// Compatibilidade com outras integrações
$route['api/won/(:any)'] = 'won_api/won/api/$1';
$route['api/won/(:any)/(:num)'] = 'won_api/won/api/$1/$2'; 