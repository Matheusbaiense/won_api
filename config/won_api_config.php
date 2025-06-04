<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Configurações Avançadas do WON API v2.1.1 - Versão Profissional
 */

// Informações básicas
$config['won_api_version'] = '2.1.1';
$config['won_api_debug'] = false;

// Segurança
$config['won_api_require_https'] = true;
$config['won_api_rate_limit'] = 100;
$config['won_api_rate_limit_window'] = 3600; // 1 hora

// CORS - Cross-Origin Resource Sharing
$config['won_api_cors_enabled'] = true;
$config['won_api_cors_origins'] = '*'; // Para produção, especificar domínios específicos
$config['won_api_cors_methods'] = 'GET, POST, PUT, DELETE, OPTIONS';
$config['won_api_cors_headers'] = 'Content-Type, Authorization, X-Requested-With';
$config['won_api_cors_credentials'] = true;
$config['won_api_cors_max_age'] = 86400; // 24 horas

// Performance e Timeouts
$config['won_api_timeout'] = 30; // segundos
$config['won_api_memory_limit'] = '256M';
$config['won_api_max_execution_time'] = 30;

// Paginação
$config['won_api_pagination_default'] = 20;
$config['won_api_pagination_max'] = 100;

// Logging e Debug
$config['won_api_log_requests'] = true;
$config['won_api_log_errors'] = true;
$config['won_api_debug_mode'] = false; // true para desenvolvimento
$config['won_api_log_level'] = 'info'; // debug, info, warning, error

// Rate Limiting Avançado
$config['won_api_rate_limit_cleanup_interval'] = 24; // horas
$config['won_api_rate_limit_headers'] = true;
$config['won_api_rate_limit_per_user'] = false; // Por usuário autenticado

// Validações
$config['won_api_strict_validation'] = true;
$config['won_api_allow_empty_strings'] = false;
$config['won_api_validate_cpf_cnpj'] = true;
$config['won_api_email_validation'] = 'strict';

// Cache (futura implementação)
$config['won_api_cache_enabled'] = false;
$config['won_api_cache_ttl'] = 300; // 5 minutos
$config['won_api_cache_driver'] = 'file';

// Monitoramento
$config['won_api_health_check'] = true;
$config['won_api_status_endpoint'] = true;
$config['won_api_metrics_enabled'] = false;

// Integração
$config['won_api_webhook_enabled'] = false;
$config['won_api_webhook_url'] = '';
$config['won_api_webhook_events'] = ['create', 'update', 'delete'];

// Backup e Restore
$config['won_api_backup_enabled'] = false;
$config['won_api_backup_retention_days'] = 30; 