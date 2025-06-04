<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Configurações WON API v2.1.1 - Versão Melhorada
 */

// Informações básicas
$config['won_api_version'] = '2.1.1';
$config['won_api_debug'] = false;

// Segurança
$config['won_api_require_https'] = true;
$config['won_api_rate_limit'] = 100;
$config['won_api_rate_limit_window'] = 3600; // 1 hora

// CORS
$config['won_api_cors_enabled'] = true;
$config['won_api_cors_origins'] = '*'; // Para desenvolvimento
$config['won_api_cors_max_age'] = 86400;

// Performance
$config['won_api_timeout'] = 30;
$config['won_api_max_execution_time'] = 60;
$config['won_api_memory_limit'] = '256M';

// Paginação
$config['won_api_pagination_default'] = 20;
$config['won_api_pagination_max'] = 100;

// Logging
$config['won_api_log_requests'] = true;
$config['won_api_log_errors'] = true;
$config['won_api_log_debug'] = false;

// Cache
$config['won_api_cache_enabled'] = false;
$config['won_api_cache_ttl'] = 300;

// Validação
$config['won_api_strict_validation'] = true;
$config['won_api_validate_ssl'] = true;

// Headers de resposta
$config['won_api_include_version_header'] = true;
$config['won_api_include_timing_header'] = false;

// Manutenção
$config['won_api_cleanup_logs_days'] = 30;
$config['won_api_cleanup_rate_limit_hours'] = 2; 