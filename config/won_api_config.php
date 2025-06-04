<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Configurações Essenciais do WON API v2.1.0 - Versão Corrigida
 */

// Informações básicas
$config['won_api_version'] = '2.1.0';
$config['won_api_debug'] = false;

// Segurança
$config['won_api_require_https'] = true;
$config['won_api_rate_limit'] = 100;
$config['won_api_rate_limit_window'] = 3600; // 1 hora

// Paginação
$config['won_api_pagination_default'] = 20;
$config['won_api_pagination_max'] = 100;

// Logging
$config['won_api_log_requests'] = true;
$config['won_api_log_errors'] = true; 