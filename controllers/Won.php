<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * WON API Controller v2.1.1 - Versão Profissional com CORS + Rate Limiting Avançado
 */
class Won extends APP_Controller
{
    private $config;
    private $start_time;
    
    public function __construct()
    {
        parent::__construct();
        
        // Marcar tempo de início para logs de performance
        $this->start_time = microtime(true);
        
        // Carregar configurações
        $this->load->config('won_api_tables');
        $this->config = $this->config->item('won_api_tables');
        
        // Setup CORS e headers
        $this->setup_cors();
        $this->setup_headers();
    }

    /**
     * Configurar CORS para integrações front-end
     */
    private function setup_cors()
    {
        $cors_enabled = get_option('won_api_cors_enabled') ?: 'true';
        
        if ($cors_enabled === 'true') {
            $allowed_origins = get_option('won_api_cors_origins') ?: '*';
            
            header('Access-Control-Allow-Origin: ' . $allowed_origins);
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400'); // 24 horas
            
            // Responder a requests OPTIONS (preflight)
            if ($this->input->method() === 'options') {
                http_response_code(200);
                exit;
            }
        }
    }

    /**
     * Configurar headers padrão da API
     */
    private function setup_headers()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Robots-Tag: noindex');
        header('X-WON-API-Version: 2.1.1');
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
    }

    /**
     * Resposta JSON padronizada e segura
     */
    private function json_response($success, $data = null, $message = '', $error_code = '', $status = 200)
    {
        // Calcular tempo de resposta
        $response_time = round((microtime(true) - $this->start_time) * 1000, 2);
        
        // Log da requisição
        $this->log_api_request($success, $status, $response_time, $error_code);
        
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => $success,
                'data' => $data,
                'message' => $message,
                'error_code' => $error_code,
                'timestamp' => time(),
                'response_time_ms' => $response_time
            ]));
    }

    /**
     * Log detalhado das requisições da API
     */
    private function log_api_request($success, $status, $response_time, $error_code = '')
    {
        $ip = $this->input->ip_address();
        $endpoint = $this->uri->uri_string();
        $method = $this->input->method();
        $user_agent = $this->input->user_agent() ?: 'Unknown';
        
        // Log no banco de dados
        $log_data = [
            'endpoint' => $endpoint,
            'method' => strtoupper($method),
            'ip_address' => $ip,
            'user_agent' => $user_agent,
            'status' => $status,
            'response_time' => $response_time,
            'error_message' => $error_code ? $error_code : null,
            'date' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert(db_prefix() . 'won_api_logs', $log_data);
        
        // Log no arquivo para debug
        $log_level = $success ? 'info' : 'error';
        log_message($log_level, "[WON API v2.1.1] {$method} {$endpoint} - {$status} - {$response_time}ms - IP: {$ip}");
    }

    /**
     * Validação de autenticação robusta
     */
    private function validate_authentication()
    {
        $token = $this->input->get_request_header('Authorization', TRUE);
        $expected_token = get_option('won_api_token');

        if (empty($token)) {
            $this->json_response(false, null, 'Token de autorização obrigatório', 'AUTH_MISSING', 401);
            return false;
        }
        
        if (empty($expected_token) || !hash_equals($expected_token, $token)) {
            $this->json_response(false, null, 'Token inválido', 'AUTH_INVALID', 401);
            return false;
        }

        return $this->check_rate_limit();
    }

    /**
     * Rate limiting robusto com headers informativos
     */
    private function check_rate_limit()
    {
        $ip = $this->input->ip_address();
        $current_hour = floor(time() / 3600);
        $rate_limit = (int)(get_option('won_api_rate_limit') ?: 100);
        
        // Criar tabela se não existir
        if (!$this->db->table_exists(db_prefix() . 'won_api_rate_limit')) {
            $this->create_rate_limit_table();
        }
        
        // Limpeza automática de dados antigos (> 48h)
        $this->cleanup_old_rate_limits($current_hour - 48);
        
        // Verificar rate limit atual
        $this->db->where('ip_address', $ip);
        $this->db->where('hour_window', $current_hour);
        $rate_record = $this->db->get(db_prefix() . 'won_api_rate_limit')->row();
        
        $remaining = $rate_limit;
        $reset_time = ($current_hour + 1) * 3600;
        
        if ($rate_record) {
            $remaining = max(0, $rate_limit - $rate_record->request_count);
            
            if ($rate_record->request_count >= $rate_limit) {
                // Headers informativos mesmo quando bloqueado
                header('X-RateLimit-Limit: ' . $rate_limit);
                header('X-RateLimit-Remaining: 0');
                header('X-RateLimit-Reset: ' . $reset_time);
                
                $this->json_response(false, null, 'Rate limit excedido. Tente novamente em ' . 
                    round(($reset_time - time()) / 60) . ' minutos', 'RATE_LIMIT_EXCEEDED', 429);
                return false;
            }
            
            // Incrementar contador
            $this->db->where('id', $rate_record->id);
            $this->db->set('request_count', 'request_count + 1', FALSE);
            $this->db->set('last_request', 'NOW()', FALSE);
            $this->db->set('user_agent', $this->input->user_agent() ?: 'Unknown');
            $this->db->update(db_prefix() . 'won_api_rate_limit');
            
            $remaining--;
        } else {
            // Primeiro request desta hora
            $this->db->insert(db_prefix() . 'won_api_rate_limit', [
                'ip_address' => $ip,
                'hour_window' => $current_hour,
                'request_count' => 1,
                'last_request' => date('Y-m-d H:i:s'),
                'user_agent' => $this->input->user_agent() ?: 'Unknown',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $remaining = $rate_limit - 1;
        }
        
        // Headers informativos de rate limiting
        header('X-RateLimit-Limit: ' . $rate_limit);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: ' . $reset_time);
        
        return true;
    }

    /**
     * Criar tabela de rate limit otimizada
     */
    private function create_rate_limit_table()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . db_prefix() . "won_api_rate_limit` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ip_address` VARCHAR(45) NOT NULL,
            `hour_window` BIGINT NOT NULL,
            `request_count` INT DEFAULT 1,
            `last_request` DATETIME NOT NULL,
            `user_agent` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            UNIQUE KEY `ip_hour` (`ip_address`, `hour_window`),
            INDEX `hour_idx` (`hour_window`),
            INDEX `last_request_idx` (`last_request`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->db->query($sql);
    }

    /**
     * Limpeza automática de dados antigos
     */
    private function cleanup_old_rate_limits($cutoff_hour)
    {
        $this->db->where('hour_window <', $cutoff_hour);
        $this->db->delete(db_prefix() . 'won_api_rate_limit');
    }

    /**
     * Validação segura de tabela
     */
    private function validate_table($table_name)
    {
        if (!isset($this->config[$table_name])) {
            $this->json_response(false, null, 'Tabela não permitida', 'INVALID_TABLE', 400);
            return false;
        }
        
        return $this->config[$table_name];
    }

    /**
     * Validação robusta de ID numérico
     */
    private function validate_id($id)
    {
        if (!ctype_digit((string)$id) || $id <= 0) {
            $this->json_response(false, null, 'ID deve ser um número positivo', 'INVALID_ID', 400);
            return false;
        }
        
        return (int)$id;
    }

    /**
     * Validação robusta de CPF/CNPJ
     */
    private function validate_cpf_cnpj($value)
    {
        // Limpar formatação
        $value = preg_replace('/[^0-9]/', '', $value);
        
        // Verificar comprimento
        if (!in_array(strlen($value), [11, 14])) {
            return false;
        }
        
        // Verificar se todos os dígitos são iguais
        if (preg_match('/^(\d)\1+$/', $value)) {
            return false;
        }
        
        return $value;
    }

    /**
     * Validação robusta de email
     */
    private function validate_email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) && 
               preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
    }

    /**
     * Validação e sanitização de dados
     */
    private function validate_data($data, $table_config, $is_update = false)
    {
        $errors = [];
        
        // Verificar campos obrigatórios (apenas para criação)
        if (!$is_update) {
            foreach ($table_config['required_fields'] as $field) {
                if (empty($data[$field])) {
                    $errors[] = "Campo {$field} é obrigatório";
                }
            }
        }
        
        // Remover campos readonly
        foreach ($table_config['readonly_fields'] as $field) {
            unset($data[$field]);
        }
        
        // Validações específicas robustas
        if (isset($table_config['validation'])) {
            foreach ($table_config['validation'] as $field => $rules) {
                if (!isset($data[$field]) && strpos($rules, 'required') === false) {
                    continue;
                }
                
                $value = $data[$field] ?? '';
                
                if (strpos($rules, 'valid_email') !== false && !empty($value)) {
                    if (!$this->validate_email($value)) {
                        $errors[] = "Email inválido no campo {$field}";
                    }
                }
                
                if (strpos($rules, 'numeric') !== false && !empty($value)) {
                    if (!is_numeric($value) || $value < 0) {
                        $errors[] = "Campo {$field} deve ser um número positivo";
                    }
                }
                
                // Validação de CPF/CNPJ
                if ($field === 'vat' && !empty($value)) {
                    $cleaned_vat = $this->validate_cpf_cnpj($value);
                    if ($cleaned_vat === false) {
                        $errors[] = "CPF/CNPJ inválido";
                    } else {
                        $data[$field] = $cleaned_vat;
                    }
                }
            }
        }
        
        if (!empty($errors)) {
            $this->json_response(false, null, implode(', ', $errors), 'VALIDATION_ERROR', 422);
            return false;
        }
        
        return $data;
    }

    /**
     * Busca segura com paginação
     */
    private function secure_search($table_config, $filters = [])
    {
        $table_name = $table_config['table_name'];
        $page = max(1, (int)$this->input->get('page') ?: 1);
        $limit = min(100, max(1, (int)$this->input->get('limit') ?: 20));
        $offset = ($page - 1) * $limit;
        
        // Construir WHERE clause segura
        $this->db->from($table_name);
        
        foreach ($filters as $field => $value) {
            if (in_array($field, $table_config['searchable_fields'])) {
                $this->db->like($field, $value);
            }
        }
        
        // Contar total
        $total = $this->db->count_all_results('', false);
        
        // Buscar dados com paginação
        $data = $this->db->limit($limit, $offset)->get()->result_array();
        
        return [
            'data' => $data,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * Endpoint de status da API (público)
     */
    public function status()
    {
        $status_data = [
            'api_name' => 'WON API',
            'version' => '2.1.1',
            'status' => 'online',
            'timestamp' => date('c'),
            'server_time' => date('Y-m-d H:i:s'),
            'endpoints' => [
                'base_url' => site_url('won_api/won/api/'),
                'authentication' => 'Header: Authorization',
                'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
                'format' => 'JSON'
            ],
            'rate_limiting' => [
                'limit' => (int)(get_option('won_api_rate_limit') ?: 100),
                'window' => '1 hour',
                'headers' => ['X-RateLimit-Limit', 'X-RateLimit-Remaining', 'X-RateLimit-Reset']
            ],
            'cors' => [
                'enabled' => get_option('won_api_cors_enabled') === 'true',
                'origins' => get_option('won_api_cors_origins') ?: '*'
            ],
            'tables' => array_keys($this->config),
            'health' => [
                'database' => $this->db->conn_id ? 'connected' : 'disconnected',
                'logs_table' => $this->db->table_exists(db_prefix() . 'won_api_logs'),
                'rate_limit_table' => $this->db->table_exists(db_prefix() . 'won_api_rate_limit')
            ]
        ];
        
        // Adicionar informações de debug se habilitado
        if (get_option('won_api_debug_mode') === 'true') {
            $status_data['debug'] = [
                'php_version' => PHP_VERSION,
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                'request_method' => $this->input->method(),
                'user_agent' => $this->input->user_agent(),
                'ip_address' => $this->input->ip_address()
            ];
        }
        
        echo json_encode($status_data, JSON_PRETTY_PRINT);
    }

    /**
     * Endpoint principal da API
     */
    public function api($table_name = null, $id = null)
    {
        // Validar autenticação
        if (!$this->validate_authentication()) {
            return;
        }
        
        // Validar tabela
        $table_config = $this->validate_table($table_name);
        if (!$table_config) {
            return;
        }
        
        $method = strtolower($this->input->method());
        
        try {
            switch ($method) {
                case 'get':
                    $this->handle_get($table_config, $id);
                    break;
                    
                case 'post':
                    $this->handle_post($table_config);
                    break;
                    
                case 'put':
                    $this->handle_put($table_config, $id);
                    break;
                    
                case 'delete':
                    $this->handle_delete($table_config, $id);
                    break;
                    
                default:
                    $this->json_response(false, null, 'Método não suportado', 'METHOD_NOT_ALLOWED', 405);
            }
        } catch (Exception $e) {
            log_message('error', '[WON API v2.1.1] Erro crítico: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            $this->json_response(false, null, 'Erro interno do servidor', 'INTERNAL_ERROR', 500);
        }
    }

    /**
     * Handler para GET requests
     */
    private function handle_get($table_config, $id)
    {
        if ($id) {
            $id = $this->validate_id($id);
            if ($id === false) return;
            
            $result = $this->db->get_where($table_config['table_name'], [
                $table_config['primary_key'] => $id
            ])->row_array();
            
            if ($result) {
                $this->json_response(true, $result, 'Registro encontrado');
            } else {
                $this->json_response(false, null, 'Registro não encontrado', 'NOT_FOUND', 404);
            }
        } else {
            // Buscar com filtros seguros
            $filters = [];
            foreach ($table_config['searchable_fields'] as $field) {
                $value = $this->input->get($field);
                if (!empty($value)) {
                    $filters[$field] = $value;
                }
            }
            
            $result = $this->secure_search($table_config, $filters);
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'data' => $result['data'],
                    'meta' => $result['meta'],
                    'timestamp' => time()
                ]));
            return;
        }
    }

    /**
     * Handler para POST requests
     */
    private function handle_post($table_config)
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            $this->json_response(false, null, 'JSON inválido', 'INVALID_JSON', 400);
            return;
        }
        
        $validated_data = $this->validate_data($data, $table_config);
        if ($validated_data === false) return;
        
        if ($this->db->insert($table_config['table_name'], $validated_data)) {
            $this->json_response(true, [
                'id' => $this->db->insert_id()
            ], 'Registro criado com sucesso', '', 201);
        } else {
            $this->json_response(false, null, 'Erro ao criar registro', 'CREATE_ERROR', 500);
        }
    }

    /**
     * Handler para PUT requests
     */
    private function handle_put($table_config, $id)
    {
        if (!$id) {
            $this->json_response(false, null, 'ID obrigatório para atualização', 'ID_REQUIRED', 400);
            return;
        }
        
        $id = $this->validate_id($id);
        if ($id === false) return;
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            $this->json_response(false, null, 'JSON inválido', 'INVALID_JSON', 400);
            return;
        }
        
        $validated_data = $this->validate_data($data, $table_config, true);
        if ($validated_data === false) return;
        
        $this->db->where($table_config['primary_key'], $id);
        if ($this->db->update($table_config['table_name'], $validated_data)) {
            $this->json_response(true, null, 'Registro atualizado com sucesso');
        } else {
            $this->json_response(false, null, 'Erro ao atualizar registro', 'UPDATE_ERROR', 500);
        }
    }

    /**
     * Handler para DELETE requests
     */
    private function handle_delete($table_config, $id)
    {
        if (!$id) {
            $this->json_response(false, null, 'ID obrigatório para exclusão', 'ID_REQUIRED', 400);
            return;
        }
        
        $id = $this->validate_id($id);
        if ($id === false) return;
        
        $this->db->where($table_config['primary_key'], $id);
        if ($this->db->delete($table_config['table_name'])) {
            $this->json_response(true, null, 'Registro removido com sucesso');
        } else {
            $this->json_response(false, null, 'Erro ao remover registro', 'DELETE_ERROR', 500);
        }
    }

    /**
     * Endpoint para busca por CNPJ/CPF (melhorado)
     */
    public function join()
    {
        if (!$this->validate_authentication()) {
            return;
        }
        
        $vat = $this->input->get('vat');
        
        if (!$vat) {
            $this->json_response(false, null, 'Parâmetro vat obrigatório', 'VAT_REQUIRED', 400);
            return;
        }
        
        $cleaned_vat = $this->validate_cpf_cnpj($vat);
        if ($cleaned_vat === false) {
            $this->json_response(false, null, 'CPF/CNPJ inválido', 'INVALID_VAT', 400);
            return;
        }
        
        $this->db->select('c.*, p.name as project_name, i.number as invoice_number');
        $this->db->from('tblclients c');
        $this->db->join('tblprojects p', 'p.clientid = c.userid', 'left');
        $this->db->join('tblinvoices i', 'i.clientid = c.userid', 'left');
        $this->db->where('c.vat', $cleaned_vat);
        
        $result = $this->db->get()->result_array();
        
        if ($result) {
            $this->json_response(true, $result, 'Dados encontrados');
        } else {
            $this->json_response(false, null, 'Nenhum dado encontrado para o CPF/CNPJ informado', 'NOT_FOUND', 404);
        }
    }
}