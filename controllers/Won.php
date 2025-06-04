<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * WON API Controller v2.1.0 - Versão Corrigida e Segura
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
        
        header('Content-Type: application/json');
        header('X-Robots-Tag: noindex');
    }

    /**
     * Resposta JSON padronizada e segura
     */
    private function json_response($success, $data = null, $message = '', $error_code = '', $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => $success,
                'data' => $data,
                'message' => $message,
                'error_code' => $error_code,
                'timestamp' => time()
            ]));
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
     * Rate limiting robusto com database
     */
    private function check_rate_limit()
    {
        $ip = $this->input->ip_address();
        $current_hour = floor(time() / 3600);
        $rate_limit = get_option('won_api_rate_limit') ?: 100;
        
        // Criar tabela de rate limit se não existir
        if (!$this->db->table_exists(db_prefix() . 'won_api_rate_limit')) {
            $this->create_rate_limit_table();
        }
        
        // Verificar rate limit atual
        $this->db->where('ip_address', $ip);
        $this->db->where('hour_window', $current_hour);
        $rate_record = $this->db->get(db_prefix() . 'won_api_rate_limit')->row();
        
        if ($rate_record) {
            if ($rate_record->request_count >= $rate_limit) {
                $this->json_response(false, null, 'Rate limit excedido', 'RATE_LIMIT_EXCEEDED', 429);
                return false;
            }
            
            // Incrementar contador
            $this->db->where('id', $rate_record->id);
            $this->db->set('request_count', 'request_count + 1', FALSE);
            $this->db->update(db_prefix() . 'won_api_rate_limit');
        } else {
            // Primeiro request desta hora
            $this->db->insert(db_prefix() . 'won_api_rate_limit', [
                'ip_address' => $ip,
                'hour_window' => $current_hour,
                'request_count' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
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
            UNIQUE KEY `ip_hour` (`ip_address`, `hour_window`),
            INDEX `hour_idx` (`hour_window`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->db->query($sql);
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
     * Validação de ID numérico
     */
    private function validate_id($id)
    {
        if (!ctype_digit((string)$id)) {
            $this->json_response(false, null, 'ID deve ser numérico', 'INVALID_ID', 400);
            return false;
        }
        
        return (int)$id;
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
        
        // Validações específicas
        if (isset($table_config['validation'])) {
            foreach ($table_config['validation'] as $field => $rules) {
                if (!isset($data[$field]) && strpos($rules, 'required') === false) {
                    continue;
                }
                
                $value = $data[$field] ?? '';
                
                if (strpos($rules, 'valid_email') !== false && !empty($value)) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Email inválido no campo {$field}";
                    }
                }
                
                if (strpos($rules, 'numeric') !== false && !empty($value)) {
                    if (!is_numeric($value)) {
                        $errors[] = "Campo {$field} deve ser numérico";
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
            log_message('error', '[WON API] Erro: ' . $e->getMessage());
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

        if (!$vat || !preg_match('/^\d{11}$|^\d{14}$/', $vat)) {
            $this->json_response(false, null, 'CPF/CNPJ deve conter 11 ou 14 dígitos', 'INVALID_VAT', 400);
            return;
        }
        
        $this->db->select('c.*, p.name as project_name, i.number as invoice_number');
        $this->db->from('tblclients c');
        $this->db->join('tblprojects p', 'p.clientid = c.userid', 'left');
        $this->db->join('tblinvoices i', 'i.clientid = c.userid', 'left');
        $this->db->where('c.vat', $vat);
        
        $result = $this->db->get()->result_array();
        
        if ($result) {
            $this->json_response(true, $result, 'Dados encontrados');
        } else {
            $this->json_response(false, null, 'Nenhum dado encontrado', 'NOT_FOUND', 404);
        }
    }
}