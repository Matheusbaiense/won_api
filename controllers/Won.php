<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * WON API Controller v2.1.1 - Versão Profissional
 * Mantém X-API-TOKEN como padrão Perfex CRM
 */
class Won extends APP_Controller
{
    private $config;
    
    public function __construct()
    {
        parent::__construct();
        
        // Carregar configurações
        $this->load->config('won_api_tables');
        $this->config = $this->config->item('won_api_tables');
        
        // Headers básicos
        header('Content-Type: application/json');
        header('X-Robots-Tag: noindex');
        header('X-WON-API-Version: 2.1.1');
        
        // Suporte para CORS
        $this->setup_cors();
    }

    /**
     * Configuração de CORS
     */
    private function setup_cors()
    {
        if (get_option('won_api_cors_enabled') !== '0') {
            $allowed_origins = get_option('won_api_cors_origins') ?: '*';
            header('Access-Control-Allow-Origin: ' . $allowed_origins);
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: X-API-TOKEN, Content-Type, Accept, X-Requested-With');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
            
            if ($this->input->method() === 'options') {
                $this->output->set_status_header(204);
                log_message('debug', '[WON API] CORS preflight request handled');
                exit;
            }
        }
    }

    /**
     * Adicionar headers informativos de rate limiting
     */
    private function add_rate_limit_headers($current_count = null, $window_reset = null)
    {
        $rate_limit = get_option('won_api_rate_limit') ?: 100;
        
        header('X-RateLimit-Limit: ' . $rate_limit);
        header('X-RateLimit-Window: 3600');
        
        if ($current_count !== null) {
            $remaining = max(0, $rate_limit - $current_count);
            header('X-RateLimit-Remaining: ' . $remaining);
        }
        
        if ($window_reset !== null) {
            header('X-RateLimit-Reset: ' . $window_reset);
        }
    }

    /**
     * Resposta JSON padronizada
     */
    private function json_response($success, $data = null, $message = '', $error_code = '', $status = 200)
    {
        $log_data = [
            'success' => $success,
            'status' => $status,
            'error_code' => $error_code,
            'endpoint' => $this->uri->uri_string(),
            'method' => $this->input->method(),
            'ip' => $this->input->ip_address()
        ];
        
        if (!$success) {
            log_message('error', '[WON API] Error Response: ' . json_encode($log_data));
        } else {
            log_message('info', '[WON API] Success Response: ' . json_encode($log_data));
        }

        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => $success,
                'data' => $data,
                'message' => $message,
                'error_code' => $error_code,
                'timestamp' => time(),
                'version' => '2.1.1'
            ]));
    }

    /**
     * MANTÉM X-API-TOKEN como padrão Perfex CRM
     */
    private function validate_authentication()
    {
        // USAR X-API-TOKEN (padrão Perfex CRM)
        $token = $this->input->get_request_header('X-API-TOKEN', TRUE);
        $expected_token = get_option('won_api_token');

        log_message('debug', '[WON API] Auth attempt from IP: ' . $this->input->ip_address());
        log_message('debug', '[WON API] X-API-TOKEN received: ' . (empty($token) ? 'EMPTY' : substr($token, 0, 10) . '...'));

        if (empty($token)) {
            log_message('warning', '[WON API] Authentication failed: No X-API-TOKEN provided');
            $this->json_response(false, null, 'Token de API obrigatório no header X-API-TOKEN', 'AUTH_MISSING', 401);
            return false;
        }
        
        if (empty($expected_token)) {
            log_message('error', '[WON API] Authentication failed: No token configured');
            $this->json_response(false, null, 'Token da API não configurado no sistema', 'AUTH_NOT_CONFIGURED', 500);
            return false;
        }
        
        if (!hash_equals($expected_token, $token)) {
            log_message('warning', '[WON API] Authentication failed: Invalid X-API-TOKEN from IP ' . $this->input->ip_address());
            $this->json_response(false, null, 'Token inválido', 'AUTH_INVALID', 401);
            return false;
        }

        log_message('info', '[WON API] Authentication successful for IP: ' . $this->input->ip_address());
        return $this->check_rate_limit();
    }

    /**
     * Rate limiting com headers informativos
     */
    private function check_rate_limit()
    {
        $ip = $this->input->ip_address();
        $current_hour = floor(time() / 3600);
        $rate_limit = get_option('won_api_rate_limit') ?: 100;
        $window_reset = ($current_hour + 1) * 3600;
        
        if (!$this->db->table_exists(db_prefix() . 'won_api_rate_limit')) {
            $this->create_rate_limit_table();
        }
        
        // Limpar registros antigos
        $this->db->where('hour_window <', $current_hour - 1);
        $this->db->delete(db_prefix() . 'won_api_rate_limit');
        
        $this->db->where('ip_address', $ip);
        $this->db->where('hour_window', $current_hour);
        $rate_record = $this->db->get(db_prefix() . 'won_api_rate_limit')->row();
        
        if ($rate_record) {
            $this->add_rate_limit_headers($rate_record->request_count, $window_reset);
            
            if ($rate_record->request_count >= $rate_limit) {
                log_message('warning', "[WON API] Rate limit exceeded for IP: {$ip}");
                header('Retry-After: ' . ($window_reset - time()));
                
                $this->json_response(
                    false, 
                    null, 
                    "Rate limit excedido: {$rate_record->request_count}/{$rate_limit} requisições por hora. Tente novamente em " . ($window_reset - time()) . " segundos.", 
                    'RATE_LIMIT_EXCEEDED', 
                    429
                );
                return false;
            }
            
            $this->db->where('id', $rate_record->id);
            $this->db->set('request_count', 'request_count + 1', FALSE);
            $this->db->set('last_request', 'NOW()', FALSE);
            $this->db->update(db_prefix() . 'won_api_rate_limit');
        } else {
            $this->db->insert(db_prefix() . 'won_api_rate_limit', [
                'ip_address' => $ip,
                'hour_window' => $current_hour,
                'request_count' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'last_request' => date('Y-m-d H:i:s')
            ]);
            
            $this->add_rate_limit_headers(1, $window_reset);
        }
        
        return true;
    }

    /**
     * Criar tabela de rate limit
     */
    private function create_rate_limit_table()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . db_prefix() . "won_api_rate_limit` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ip_address` VARCHAR(45) NOT NULL,
            `hour_window` BIGINT NOT NULL,
            `request_count` INT DEFAULT 1,
            `created_at` DATETIME NOT NULL,
            `last_request` DATETIME NOT NULL,
            `user_agent` VARCHAR(500) NULL,
            UNIQUE KEY `ip_hour` (`ip_address`, `hour_window`),
            INDEX `hour_idx` (`hour_window`),
            INDEX `ip_idx` (`ip_address`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->db->query($sql);
        log_message('info', '[WON API] Rate limit table created');
    }

    /**
     * Validação de tabela
     */
    private function validate_table($table_name)
    {
        if (!isset($this->config[$table_name])) {
            $this->json_response(false, null, "Tabela '{$table_name}' não permitida", 'INVALID_TABLE', 400);
            return false;
        }
        
        return $this->config[$table_name];
    }

    /**
     * Validação de ID
     */
    private function validate_id($id)
    {
        if (!ctype_digit((string)$id)) {
            $this->json_response(false, null, 'ID deve ser numérico positivo', 'INVALID_ID', 400);
            return false;
        }
        
        return (int)$id;
    }

    /**
     * Validação de dados robusta
     */
    private function validate_data($data, $table_config, $is_update = false)
    {
        $errors = [];
        
        if (!$is_update) {
            foreach ($table_config['required_fields'] as $field) {
                if (empty($data[$field])) {
                    $errors[] = "Campo '{$field}' é obrigatório";
                }
            }
        }
        
        foreach ($table_config['readonly_fields'] as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }
        
        if (isset($table_config['validation'])) {
            foreach ($table_config['validation'] as $field => $rules) {
                if (!isset($data[$field]) && strpos($rules, 'required') === false) {
                    continue;
                }
                
                $value = $data[$field] ?? '';
                
                if (strpos($rules, 'valid_email') !== false && !empty($value)) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Email inválido no campo '{$field}': {$value}";
                    }
                }
                
                if (strpos($rules, 'numeric') !== false && !empty($value)) {
                    if (!is_numeric($value)) {
                        $errors[] = "Campo '{$field}' deve ser numérico: {$value}";
                    }
                }
                
                // Validação CPF/CNPJ melhorada
                if ($field === 'vat' && !empty($value)) {
                    $clean_vat = preg_replace('/\D/', '', $value);
                    
                    if (!in_array(strlen($clean_vat), [11, 14])) {
                        $errors[] = "CPF/CNPJ deve ter 11 ou 14 dígitos: {$value}";
                    } elseif (preg_match('/^(\d)\1+$/', $clean_vat)) {
                        $errors[] = "CPF/CNPJ não pode ter todos os dígitos iguais: {$value}";
                    } else {
                        $data[$field] = $clean_vat;
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
     * Busca com paginação
     */
    private function secure_search($table_config, $filters = [])
    {
        $table_name = $table_config['table_name'];
        $page = max(1, (int)$this->input->get('page') ?: 1);
        $limit = min(100, max(1, (int)$this->input->get('limit') ?: 20));
        $offset = ($page - 1) * $limit;
        
        $this->db->from($table_name);
        
        foreach ($filters as $field => $value) {
            if (in_array($field, $table_config['searchable_fields'])) {
                $this->db->like($field, $value);
            }
        }
        
        $total = $this->db->count_all_results('', false);
        $data = $this->db->limit($limit, $offset)->get()->result_array();
        
        return [
            'data' => $data,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_next_page' => $page < ceil($total / $limit),
                'has_prev_page' => $page > 1
            ]
        ];
    }

    /**
     * Endpoint principal da API
     */
    public function api($table_name = null, $id = null)
    {
        if (!$this->validate_authentication()) {
            return;
        }
        
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
                    $this->json_response(false, null, "Método '{$method}' não suportado", 'METHOD_NOT_ALLOWED', 405);
            }
        } catch (Exception $e) {
            log_message('error', '[WON API] Erro: ' . $e->getMessage());
            $this->json_response(false, null, 'Erro interno do servidor', 'INTERNAL_ERROR', 500);
        }
    }

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
                    'timestamp' => time(),
                    'version' => '2.1.1'
                ]));
        }
    }

    private function handle_post($table_config)
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            $this->json_response(false, null, 'JSON inválido ou vazio', 'INVALID_JSON', 400);
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
            $this->json_response(false, null, 'JSON inválido ou vazio', 'INVALID_JSON', 400);
            return;
        }
        
        $validated_data = $this->validate_data($data, $table_config, true);
        if ($validated_data === false) return;
        
        $this->db->where($table_config['primary_key'], $id);
        if ($this->db->update($table_config['table_name'], $validated_data)) {
            $this->json_response(true, ['id' => $id], 'Registro atualizado com sucesso');
        } else {
            $this->json_response(false, null, 'Erro ao atualizar registro', 'UPDATE_ERROR', 500);
        }
    }

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
            $this->json_response(true, ['id' => $id], 'Registro removido com sucesso');
        } else {
            $this->json_response(false, null, 'Erro ao remover registro', 'DELETE_ERROR', 500);
        }
    }

    /**
     * Endpoint JOIN melhorado
     */
    public function join()
    {
        if (!$this->validate_authentication()) {
            return;
        }
        
        $vat = $this->input->get('vat');
        
        if (!$vat) {
            $this->json_response(false, null, 'Parâmetro "vat" obrigatório', 'VAT_REQUIRED', 400);
            return;
        }
        
        $clean_vat = preg_replace('/\D/', '', $vat);
        
        if (!preg_match('/^\d{11}$|^\d{14}$/', $clean_vat)) {
            $this->json_response(false, null, 'CPF/CNPJ deve conter 11 ou 14 dígitos numéricos', 'INVALID_VAT', 400);
            return;
        }
        
        if (preg_match('/^(\d)\1+$/', $clean_vat)) {
            $this->json_response(false, null, 'CPF/CNPJ inválido: todos os dígitos são iguais', 'INVALID_VAT', 400);
            return;
        }
        
        $this->db->select('c.*, p.name as project_name, i.number as invoice_number');
        $this->db->from('tblclients c');
        $this->db->join('tblprojects p', 'p.clientid = c.userid', 'left');
        $this->db->join('tblinvoices i', 'i.clientid = c.userid', 'left');
        $this->db->where('c.vat', $clean_vat);
        
        $result = $this->db->get()->result_array();
        
        if ($result) {
            $this->json_response(true, $result, 'Dados encontrados via CNPJ/CPF');
        } else {
            $this->json_response(false, null, 'Nenhum cliente encontrado com este CNPJ/CPF', 'NOT_FOUND', 404);
        }
    }

    /**
     * Endpoint de status da API
     */
    public function status()
    {
        $status_data = [
            'api_version' => '2.1.1',
            'status' => 'online',
            'timestamp' => time(),
            'authentication' => 'X-API-TOKEN',
            'endpoints' => ['clients', 'projects', 'tasks', 'staff', 'leads', 'invoices'],
            'features' => [
                'cors' => get_option('won_api_cors_enabled') !== '0',
                'rate_limiting' => true,
                'join_operation' => true
            ]
        ];
        
        $this->json_response(true, $status_data, 'API Status');
    }
}