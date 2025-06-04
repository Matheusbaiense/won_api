<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * WON API Controller v2.1.1 - DEFENSIVO para Easy Install
 * Sem dependências externas - Configurações hardcoded
 * Rate Limiting removido conforme solicitado - Mantendo X-API-TOKEN
 */
class Won extends APP_Controller
{
    private $allowed_tables = [];
    
    public function __construct()
    {
        parent::__construct();
        
        // Headers básicos - SEM FALHAS
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('X-Robots-Tag: noindex');
            header('X-WON-API-Version: 2.1.1');
        }
        
        // Configurar tabelas permitidas - HARDCODED (sem dependências)
        $this->allowed_tables = [
            'clients' => [
                'table' => 'tblclients',
                'primary_key' => 'userid',
                'search_fields' => ['company', 'vat', 'email'],
                'filters' => ['active', 'country'],
                'required' => ['company'],
                'readonly' => ['userid', 'datecreated']
            ],
            'contacts' => [
                'table' => 'tblcontacts',
                'primary_key' => 'id',
                'search_fields' => ['firstname', 'lastname', 'email'],
                'filters' => ['active', 'userid'],
                'required' => ['userid', 'firstname', 'lastname'],
                'readonly' => ['id', 'datecreated']
            ],
            'projects' => [
                'table' => 'tblprojects',
                'primary_key' => 'id',
                'search_fields' => ['name', 'description'],
                'filters' => ['status', 'clientid'],
                'required' => ['name', 'clientid'],
                'readonly' => ['id', 'datecreated']
            ],
            'tasks' => [
                'table' => 'tbltasks',
                'primary_key' => 'id',
                'search_fields' => ['name', 'description'],
                'filters' => ['status', 'priority', 'rel_type'],
                'required' => ['name'],
                'readonly' => ['id', 'datecreated']
            ],
            'invoices' => [
                'table' => 'tblinvoices',
                'primary_key' => 'id',
                'search_fields' => ['number', 'clientnote'],
                'filters' => ['status', 'clientid'],
                'required' => ['clientid'],
                'readonly' => ['id', 'datecreated']
            ],
            'estimates' => [
                'table' => 'tblestimates',
                'primary_key' => 'id',
                'search_fields' => ['number', 'clientnote'],
                'filters' => ['status', 'clientid'],
                'required' => ['clientid'],
                'readonly' => ['id', 'datecreated']
            ],
            'leads' => [
                'table' => 'tblleads',
                'primary_key' => 'id',
                'search_fields' => ['name', 'company', 'email'],
                'filters' => ['status', 'source'],
                'required' => ['name'],
                'readonly' => ['id', 'datecreated']
            ],
            'staff' => [
                'table' => 'tblstaff',
                'primary_key' => 'staffid',
                'search_fields' => ['firstname', 'lastname', 'email'],
                'filters' => ['active', 'role'],
                'required' => ['firstname', 'lastname', 'email'],
                'readonly' => ['staffid', 'datecreated']
            ]
        ];
        
        // CORS simples - SEM DEPENDÊNCIAS
        $this->setup_cors();
    }

    /**
     * Configuração de CORS básica - SEM DEPENDÊNCIAS
     */
    private function setup_cors()
    {
        if (!headers_sent()) {
            // CORS sempre habilitado para máxima compatibilidade
            header('Access-Control-Allow-Origin: *');
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
     * Resposta JSON padronizada - SIMPLIFICADA
     */
    private function json_response($success, $data = null, $message = '', $error_code = '', $status = 200, $meta = null)
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
        
        $response = [
            'success' => $success,
            'data' => $data,
            'message' => $message,
            'timestamp' => time(),
            'version' => '2.1.1'
        ];
        
        if (!$success && !empty($error_code)) {
            $response['error_code'] = $error_code;
        }
        
        // Adicionar metadados se fornecidos
        if ($meta !== null) {
            $response['meta'] = $meta;
        }
        
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * X-API-TOKEN como padrão Perfex CRM - SEM RATE LIMITING
     * Autenticação simplificada conforme solicitado
     */
    private function validate_authentication()
    {
        // USAR X-API-TOKEN (padrão Perfex CRM)
        $token = $this->input->get_request_header('X-API-TOKEN', TRUE);
        $expected_token = get_option('won_api_token');
        
        log_message('debug', '[WON API] Auth attempt from IP: ' . $this->input->ip_address());
        
        if (empty($token)) {
            log_message('warning', '[WON API] Authentication failed: No X-API-TOKEN provided');
            $this->json_response(false, null, 'Token de API obrigatório no header X-API-TOKEN.', 'AUTH_MISSING', 401);
            return false;
        }
        
        if (empty($expected_token)) {
            log_message('error', '[WON API] Authentication failed: No token configured');
            $this->json_response(false, null, 'Token da API não configurado no sistema.', 'AUTH_NOT_CONFIGURED', 500);
            return false;
        }
        
        if (!hash_equals($expected_token, $token)) {
            log_message('warning', '[WON API] Authentication failed: Invalid X-API-TOKEN from IP ' . $this->input->ip_address());
            $this->json_response(false, null, 'Token de API inválido.', 'AUTH_INVALID', 401);
            return false;
        }
        
        log_message('info', '[WON API] Authentication successful for IP: ' . $this->input->ip_address());
        return true;
    }

    /**
     * Validação de tabela - SIMPLIFICADA
     */
    private function validate_table($table)
    {
        if (empty($table)) {
            $this->json_response(false, null, 'Nome da tabela é obrigatório.', 'TABLE_MISSING', 400);
            return false;
        }
        
        if (!isset($this->allowed_tables[$table])) {
            $this->json_response(false, null, 'Tabela não suportada: ' . $table, 'TABLE_NOT_SUPPORTED', 400);
            return false;
        }
        
        return $this->allowed_tables[$table];
    }

    /**
     * Validação de ID - SIMPLIFICADA
     */
    private function validate_id($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            $this->json_response(false, null, 'ID deve ser um número válido maior que zero.', 'INVALID_ID', 400);
            return false;
        }
        
        return $id;
    }

    /**
     * Endpoint API principal - SIMPLIFICADO
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
        
        // Método GET - Listar ou obter registro específico
        if ($this->input->method() === 'get') {
            return $this->handle_get($table_config, $id, $table_name);
        }
        
        // Método POST - Criar novo registro
        if ($this->input->method() === 'post') {
            return $this->handle_post($table_config, $table_name);
        }
        
        // Método PUT - Atualizar registro
        if ($this->input->method() === 'put') {
            return $this->handle_put($table_config, $id, $table_name);
        }
        
        // Método DELETE - Excluir registro
        if ($this->input->method() === 'delete') {
            return $this->handle_delete($table_config, $id);
        }
        
        // Método não suportado
        $this->json_response(false, null, 'Método HTTP não suportado.', 'INVALID_METHOD', 405);
    }

    /**
     * Handle GET requests - SIMPLIFICADO
     */
    private function handle_get($table_config, $id, $table_name)
    {
        if ($id !== null) {
            // Obter registro específico
            $id = $this->validate_id($id);
            if (!$id) {
                return;
            }
            
            $this->db->where($table_config['primary_key'], $id);
            $query = $this->db->get(db_prefix() . $table_config['table']);
            $record = $query->row_array();
            
            if (!$record) {
                return $this->json_response(false, null, 'Registro não encontrado.', 'NOT_FOUND', 404);
            }
            
            return $this->json_response(true, $record, 'Registro encontrado com sucesso.');
        }
        
        // Listar registros com paginação
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $limit = $this->input->get('limit') ? min((int)$this->input->get('limit'), 100) : 25;
        $offset = ($page - 1) * $limit;
        
        // Ordenação
        $sort = $this->input->get('sort');
        $order = $this->input->get('order') === 'desc' ? 'desc' : 'asc';
        
        if ($sort) {
            $this->db->order_by($sort, $order);
        }
        
        // Busca
        $search = $this->input->get('search');
        if ($search && isset($table_config['search_fields'])) {
            $this->db->group_start();
            foreach ($table_config['search_fields'] as $field) {
                $this->db->or_like($field, $search);
            }
            $this->db->group_end();
        }
        
        // Filtros
        if (isset($table_config['filters'])) {
            foreach ($table_config['filters'] as $filter) {
                $filter_value = $this->input->get($filter);
                if ($filter_value !== null) {
                    $this->db->where($filter, $filter_value);
                }
            }
        }
        
        // Contar total de registros
        $total_query = clone $this->db;
        $total = $total_query->count_all_results(db_prefix() . $table_config['table']);
        
        // Aplicar limit e offset
        $this->db->limit($limit, $offset);
        
        // Executar consulta
        $query = $this->db->get(db_prefix() . $table_config['table']);
        $records = $query->result_array();
        
        // Calcular metadados de paginação
        $total_pages = ceil($total / $limit);
        $meta = [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => $total_pages,
            'has_next_page' => $page < $total_pages,
            'has_prev_page' => $page > 1
        ];
        
        return $this->json_response(true, $records, 'Registros recuperados com sucesso.', '', 200, $meta);
    }

    /**
     * Handle POST requests - SIMPLIFICADO
     */
    private function handle_post($table_config, $table_name)
    {
        // Obter dados do corpo da requisição
        $data = json_decode($this->input->raw_input_stream, true);
        if (empty($data)) {
            return $this->json_response(false, null, 'Dados JSON válidos são obrigatórios.', 'INVALID_DATA', 400);
        }
        
        // Validação básica de campos obrigatórios
        if (isset($table_config['required'])) {
            foreach ($table_config['required'] as $field) {
                if (empty($data[$field])) {
                    return $this->json_response(false, null, "Campo obrigatório: {$field}", 'REQUIRED_FIELD_MISSING', 400);
                }
            }
        }
        
        // Remover campos somente leitura
        if (isset($table_config['readonly'])) {
            foreach ($table_config['readonly'] as $field) {
                if (isset($data[$field])) {
                    unset($data[$field]);
                }
            }
        }
        
        // Adicionar campos de auditoria
        if (!isset($data['datecreated'])) {
            $data['datecreated'] = date('Y-m-d H:i:s');
        }
        
        // Inserir registro
        $this->db->insert(db_prefix() . $table_config['table'], $data);
        $insert_id = $this->db->insert_id();
        
        if (!$insert_id) {
            return $this->json_response(false, null, 'Erro ao criar registro no banco de dados.', 'DB_ERROR', 500);
        }
        
        // Obter registro criado
        $this->db->where($table_config['primary_key'], $insert_id);
        $query = $this->db->get(db_prefix() . $table_config['table']);
        $record = $query->row_array();
        
        return $this->json_response(true, $record, 'Registro criado com sucesso.', '', 201);
    }

    /**
     * Handle PUT requests - SIMPLIFICADO
     */
    private function handle_put($table_config, $id, $table_name)
    {
        // Validar ID
        $id = $this->validate_id($id);
        if (!$id) {
            return;
        }
        
        // Verificar se o registro existe
        $this->db->where($table_config['primary_key'], $id);
        $query = $this->db->get(db_prefix() . $table_config['table']);
        $record = $query->row_array();
        
        if (!$record) {
            return $this->json_response(false, null, 'Registro não encontrado para atualização.', 'NOT_FOUND', 404);
        }
        
        // Obter dados do corpo da requisição
        $data = json_decode($this->input->raw_input_stream, true);
        if (empty($data)) {
            return $this->json_response(false, null, 'Dados JSON válidos são obrigatórios.', 'INVALID_DATA', 400);
        }
        
        // Remover campos somente leitura
        if (isset($table_config['readonly'])) {
            foreach ($table_config['readonly'] as $field) {
                if (isset($data[$field])) {
                    unset($data[$field]);
                }
            }
        }
        
        // Adicionar campos de auditoria
        $data['dateupdated'] = date('Y-m-d H:i:s');
        
        // Atualizar registro
        $this->db->where($table_config['primary_key'], $id);
        $this->db->update(db_prefix() . $table_config['table'], $data);
        
        if ($this->db->affected_rows() === 0) {
            return $this->json_response(true, null, 'Nenhuma alteração foi feita.', 'NO_CHANGES', 200);
        }
        
        // Obter registro atualizado
        $this->db->where($table_config['primary_key'], $id);
        $query = $this->db->get(db_prefix() . $table_config['table']);
        $updated_record = $query->row_array();
        
        return $this->json_response(true, $updated_record, 'Registro atualizado com sucesso.');
    }

    /**
     * Handle DELETE requests - SIMPLIFICADO
     */
    private function handle_delete($table_config, $id)
    {
        // Validar ID
        $id = $this->validate_id($id);
        if (!$id) {
            return;
        }
        
        // Verificar se o registro existe
        $this->db->where($table_config['primary_key'], $id);
        $query = $this->db->get(db_prefix() . $table_config['table']);
        $record = $query->row_array();
        
        if (!$record) {
            return $this->json_response(false, null, 'Registro não encontrado para exclusão.', 'NOT_FOUND', 404);
        }
        
        // Excluir registro
        $this->db->where($table_config['primary_key'], $id);
        $this->db->delete(db_prefix() . $table_config['table']);
        
        if ($this->db->affected_rows() === 0) {
            return $this->json_response(false, null, 'Erro ao excluir registro.', 'DB_ERROR', 500);
        }
        
        return $this->json_response(true, null, 'Registro excluído com sucesso.');
    }

    /**
     * Endpoint para busca por CPF/CNPJ - SIMPLIFICADO
     */
    public function join()
    {
        // Validar autenticação
        if (!$this->validate_authentication()) {
            return;
        }
        
        // Obter CPF/CNPJ
        $vat = $this->input->get('vat');
        if (empty($vat)) {
            return $this->json_response(false, null, 'Parâmetro vat (CPF/CNPJ) é obrigatório.', 'MISSING_VAT', 400);
        }
        
        // Remover caracteres não numéricos
        $vat_clean = preg_replace('/[^0-9]/', '', $vat);
        
        // Buscar cliente por CPF/CNPJ
        $this->db->where('vat', $vat_clean);
        $query = $this->db->get(db_prefix() . 'clients');
        $client = $query->row_array();
        
        if (!$client) {
            return $this->json_response(false, null, 'Cliente não encontrado com o CPF/CNPJ informado.', 'NOT_FOUND', 404);
        }
        
        return $this->json_response(true, $client, 'Cliente encontrado com sucesso.');
    }

    /**
     * Endpoint de status da API - SIMPLIFICADO
     */
    public function status()
    {
        $status_data = [
            'api_version' => '2.1.1',
            'status' => 'online',
            'timestamp' => time(),
            'authentication' => 'X-API-TOKEN',
            'endpoints' => array_keys($this->allowed_tables),
            'features' => [
                'cors' => true,
                'join_operation' => true,
                'easy_install' => true,
                'rate_limiting_removed' => true
            ],
            'installation_method' => 'Easy Install Compatible'
        ];
        
        $this->json_response(true, $status_data, 'WON API Online - Easy Install');
    }
}