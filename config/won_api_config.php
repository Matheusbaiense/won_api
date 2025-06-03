<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Configurações do Módulo WON API
 * 
 * Este arquivo contém todas as configurações padrão e constantes
 * utilizadas pelo módulo WON API
 */

// Versão do módulo
$config['won_api_version'] = '2.1.0';
$config['won_api_debug'] = false;

// Configurações de segurança
$config['won_api_require_https'] = true;
$config['won_api_token_length'] = 64;
$config['won_api_session_timeout'] = 3600; // 1 hora

// Configurações de rate limiting
$config['won_api_rate_limit_enabled'] = true;
$config['won_api_rate_limit_requests'] = 100;
$config['won_api_rate_limit_window'] = 3600; // 1 hora em segundos
$config['won_api_rate_limit_method'] = 'sliding_window'; // fixed_window, sliding_window

// Configurações de cache
$config['won_api_cache_enabled'] = true;
$config['won_api_cache_duration'] = 300; // 5 minutos
$config['won_api_cache_method'] = 'file'; // file, database, redis
$config['won_api_cache_prefix'] = 'won_api_';

// Configurações de logs
$config['won_api_log_enabled'] = true;
$config['won_api_log_level'] = 'basic'; // none, basic, detailed, debug
$config['won_api_log_retention_days'] = 30;
$config['won_api_log_max_size'] = 10485760; // 10MB
$config['won_api_log_rotate'] = true;

// Configurações de CORS
$config['won_api_cors_enabled'] = true;
$config['won_api_cors_origins'] = ['*'];
$config['won_api_cors_methods'] = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
$config['won_api_cors_headers'] = [
    'Authorization',
    'Content-Type',
    'X-Requested-With',
    'Accept',
    'Origin',
    'Access-Control-Request-Method',
    'Access-Control-Request-Headers'
];
$config['won_api_cors_credentials'] = false;

// Configurações de paginação
$config['won_api_pagination_default'] = 20;
$config['won_api_pagination_max'] = 100;
$config['won_api_pagination_key'] = 'page';
$config['won_api_pagination_size_key'] = 'limit';

// Tabelas permitidas (lista branca)
$config['won_api_allowed_tables'] = [
    'clients' => [
        'fields' => ['userid', 'company', 'phonenumber', 'country', 'city', 'address', 'website', 'datecreated'],
        'required' => ['company'],
        'readonly' => ['userid', 'datecreated'],
        'relations' => ['contacts', 'projects', 'invoices']
    ],
    'projects' => [
        'fields' => ['id', 'name', 'description', 'status', 'start_date', 'deadline', 'clientid'],
        'required' => ['name', 'clientid'],
        'readonly' => ['id', 'datecreated'],
        'relations' => ['client', 'tasks']
    ],
    'tasks' => [
        'fields' => ['id', 'name', 'description', 'status', 'startdate', 'duedate', 'priority', 'rel_id', 'rel_type'],
        'required' => ['name'],
        'readonly' => ['id', 'datecreated'],
        'relations' => ['project', 'assignees']
    ],
    'staff' => [
        'fields' => ['staffid', 'firstname', 'lastname', 'email', 'phonenumber', 'active'],
        'required' => ['firstname', 'lastname', 'email'],
        'readonly' => ['staffid', 'datecreated'],
        'sensitive' => ['password'],
        'relations' => ['departments', 'roles']
    ],
    'leads' => [
        'fields' => ['id', 'name', 'title', 'company', 'description', 'country', 'city', 'phonenumber', 'email'],
        'required' => ['name'],
        'readonly' => ['id', 'datecreated'],
        'relations' => ['source', 'status']
    ],
    'estimates' => [
        'fields' => ['id', 'sent', 'datesend', 'clientid', 'deleted_customer_name', 'project_id', 'number', 'prefix', 'number_format', 'hash', 'datecreated', 'date', 'expirydate', 'subtotal', 'total_tax', 'total', 'currency', 'status'],
        'required' => ['clientid'],
        'readonly' => ['id', 'datecreated', 'hash'],
        'relations' => ['client', 'items']
    ],
    'invoices' => [
        'fields' => ['id', 'sent', 'datesend', 'clientid', 'deleted_customer_name', 'number', 'prefix', 'number_format', 'datecreated', 'date', 'duedate', 'subtotal', 'total_tax', 'total', 'currency', 'status'],
        'required' => ['clientid'],
        'readonly' => ['id', 'datecreated', 'hash'],
        'relations' => ['client', 'items', 'payments']
    ],
    'payments' => [
        'fields' => ['id', 'invoiceid', 'amount', 'paymentmode', 'paymentmethod', 'date', 'note', 'transactionid'],
        'required' => ['invoiceid', 'amount'],
        'readonly' => ['id', 'daterecorded'],
        'relations' => ['invoice']
    ],
    'expenses' => [
        'fields' => ['id', 'category', 'currency', 'amount', 'tax', 'tax2', 'reference_no', 'note', 'expense_name', 'clientid', 'project_id', 'billable', 'invoiceid', 'paymentmode', 'date'],
        'required' => ['amount', 'category'],
        'readonly' => ['id', 'daterecorded'],
        'relations' => ['client', 'project', 'invoice']
    ],
    'contracts' => [
        'fields' => ['id', 'content', 'description', 'subject', 'client', 'datestart', 'dateend', 'contract_type', 'project_id', 'addedfrom', 'isexpirynotified', 'contract_value', 'trash', 'not_visible_to_client', 'hash', 'signed', 'signature', 'marked_as_signed', 'acceptance_firstname', 'acceptance_lastname', 'acceptance_email', 'acceptance_date', 'acceptance_ip', 'short_link'],
        'required' => ['subject', 'client'],
        'readonly' => ['id', 'datecreated', 'hash'],
        'relations' => ['client', 'project']
    ],
    'proposals' => [
        'fields' => ['id', 'subject', 'content', 'addedfrom', 'datecreated', 'total', 'subtotal', 'total_tax', 'discount_percent', 'discount_total', 'discount_type', 'show_quantity_as', 'currency', 'open_till', 'date', 'rel_id', 'rel_type', 'assigned', 'hash', 'proposal_to', 'project_id', 'country', 'zip', 'state', 'city', 'address', 'email', 'phone', 'allow_comments', 'status', 'estimate_id', 'invoice_id', 'date_converted', 'pipeline_order', 'is_expiry_notified'],
        'required' => ['subject', 'rel_id', 'rel_type'],
        'readonly' => ['id', 'datecreated', 'hash'],
        'relations' => ['items', 'comments']
    ],
    'tickets' => [
        'fields' => ['ticketid', 'adminreplying', 'userid', 'contactid', 'merged_ticket_id', 'email', 'name', 'department', 'priority', 'status', 'service', 'project_id', 'lastreply', 'clientread', 'adminread', 'assigned', 'staff_id_replying', 'date', 'subject', 'message', 'admin', 'attachment', 'cc'],
        'required' => ['subject', 'department'],
        'readonly' => ['ticketid', 'date'],
        'relations' => ['client', 'department', 'replies']
    ],
    'knowledge_base' => [
        'fields' => ['articleid', 'articlegroup', 'subject', 'description', 'slug', 'active', 'datecreated', 'article_order', 'staff_article'],
        'required' => ['subject', 'description', 'articlegroup'],
        'readonly' => ['articleid', 'datecreated', 'slug'],
        'relations' => ['group']
    ]
];

// Configurações de validação
$config['won_api_validation_rules'] = [
    'email' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
    'phone' => '/^[\+]?[1-9][\d]{0,15}$/',
    'cpf' => '/^\d{3}\.\d{3}\.\d{3}-\d{2}$|^\d{11}$/',
    'cnpj' => '/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$|^\d{14}$/',
    'url' => '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
    'date' => '/^\d{4}-\d{2}-\d{2}$/',
    'datetime' => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
    'currency' => '/^\d+(\.\d{2})?$/'
];

// Códigos de status HTTP utilizados
$config['won_api_status_codes'] = [
    'success' => [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content'
    ],
    'client_error' => [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        409 => 'Conflict',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests'
    ],
    'server_error' => [
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout'
    ]
];

// Mensagens de erro padronizadas
$config['won_api_error_messages'] = [
    'E001' => 'Token de autenticação inválido ou ausente',
    'E002' => 'Limite de requisições excedido',
    'E003' => 'Tabela não permitida ou não encontrada',
    'E004' => 'Campos obrigatórios ausentes',
    'E005' => 'Formato de dados inválido',
    'E006' => 'Registro não encontrado',
    'E007' => 'Erro de validação de dados',
    'E008' => 'Permissão insuficiente',
    'E009' => 'Método HTTP não permitido',
    'E010' => 'Erro interno do servidor',
    'E011' => 'Parâmetros de paginação inválidos',
    'E012' => 'Tamanho de página excede o limite máximo',
    'E013' => 'Conexão HTTPS obrigatória',
    'E014' => 'IP não autorizado',
    'E015' => 'Dados duplicados ou conflito'
];

// Configurações de webhook
$config['won_api_webhook_enabled'] = false;
$config['won_api_webhook_events'] = [
    'create' => 'Registro criado',
    'update' => 'Registro atualizado', 
    'delete' => 'Registro excluído'
];

// Configurações de backup e exportação
$config['won_api_export_formats'] = ['json', 'csv', 'xml'];
$config['won_api_export_max_records'] = 1000;

// Configurações de monitoramento
$config['won_api_monitoring_enabled'] = true;
$config['won_api_alert_thresholds'] = [
    'error_rate' => 0.05, // 5% de taxa de erro
    'response_time' => 2000, // 2 segundos
    'requests_per_minute' => 1000
];

// Configurações de desenvolvimento
$config['won_api_dev_mode'] = false;
$config['won_api_test_mode'] = false;
$config['won_api_mock_responses'] = false;

// Versões da API suportadas
$config['won_api_supported_versions'] = ['v1', 'v2'];
$config['won_api_default_version'] = 'v2';
$config['won_api_deprecated_versions'] = ['v1'];

// Configurações de documentação
$config['won_api_docs_enabled'] = true;
$config['won_api_docs_auth_required'] = false;
$config['won_api_swagger_enabled'] = true; 