<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * WON API Controller v2.1.1 - VERSÃO SIMPLIFICADA E FUNCIONAL
 */
class Won extends APP_Controller
{
    private $allowed_tables = [
        'clients' => [
            'table' => 'tblclients',
            'primary_key' => 'userid',
            'required' => ['company'],
            'readonly' => ['userid', 'datecreated']
        ],
        'projects' => [
            'table' => 'tblprojects', 
            'primary_key' => 'id',
            'required' => ['name', 'clientid'],
            'readonly' => ['id', 'datecreated']
        ],
        'tasks' => [
            'table' => 'tbltasks',
            'primary_key' => 'id', 
            'required' => ['name'],
            'readonly' => ['id', 'datecreated']
        ],
        'invoices' => [
            'table' => 'tblinvoices',
            'primary_key' => 'id',
            'required' => ['clientid'],
            'readonly' => ['id', 'datecreated']
        ],
        'leads' => [
            'table' => 'tblleads',
            'primary_key' => 'id',
            'required' => ['name'], 
            'readonly' => ['id', 'datecreated']
        ],
        'staff' => [
            'table' => 'tblstaff',
            'primary_key' => 'staffid',
            'required' => ['firstname', 'lastname', 'email'],
            'readonly' => ['staffid', 'datecreated']
        ]
    ];
    
    public function __construct()
    {
        parent::__construct();
        $this->setup_cors();
        $this->set_headers();
    }
    
    private function setup_cors()
    {
        if (!headers_sent()) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: X-API-TOKEN, Content-Type, Accept');
            header('Access-Control-Max-Age: 86400');
            
            if ($this->input->method() === 'options') {
                $this->output->set_status_header(204);
                exit;
            }
        }
    }
    
    private function set_headers()
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('X-WON-API-Version: 2.1.1');
            header('X-Robots-Tag: noindex');
        }
    }
    
    private function json_response($success, $data = null, $message = '', $status = 200, $meta = null)
    {
        $response = [
            'success' => $success,
            'data' => $data,
            'message' => $message,
            'timestamp' => time(),
            'version' => '2.1.1'
        ];
        
        if ($meta) {
            $response['meta'] = $meta;
        }
        
        log_message('info', '[WON API] ' . ($success ? 'Success' : 'Error') . ': ' . $this->uri->uri_string());
        
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
    
    private function validate_auth()
    {
        $token = $this->input->get_request_header('X-API-TOKEN', TRUE);
        $expected_token = get_option('won_api_token');
        
        if (empty($token)) {
            $this->json_response(false, null, 'Token X-API-TOKEN obrigatório', 401);
            return false;
        }
        
        if (empty($expected_token)) {
            $this->json_response(false, null, 'Token não configurado no sistema', 500);
            return false;
        }
        
        if (!hash_equals($expected_token, $token)) {
            $this->json_response(false, null, 'Token inválido', 401);
            return false;
        }
        
        return true;
    }
    
    public function status()
    {
        $data = [
            'api_version' => '2.1.1',
            'status' => 'online',
            'timestamp' => time(),
            'authentication' => 'X-API-TOKEN',
            'endpoints' => array_keys($this->allowed_tables),
            'features' => [
                'cors' => true,
                'easy_install' => true,
                'join_operation' => true
            ]
        ];
        
        $this->json_response(true, $data, 'WON API Online');
    }
    
    public function api($table = null, $id = null)
    {
        if (!$this->validate_auth()) {
            return;
        }
        
        if (!$table || !isset($this->allowed_tables[$table])) {
            $this->json_response(false, null, 'Tabela não suportada', 400);
            return;
        }
        
        $config = $this->allowed_tables[$table];
        
        switch ($this->input->method()) {
            case 'get':
                $this->handle_get($config, $id);
                break;
            case 'post':
                $this->handle_post($config);
                break;
            case 'put':
                $this->handle_put($config, $id);
                break;
            case 'delete':
                $this->handle_delete($config, $id);
                break;
            default:
                $this->json_response(false, null, 'Método não suportado', 405);
        }
    }
    
    private function handle_get($config, $id)
    {
        if ($id) {
            // Obter registro específico
            $this->db->where($config['primary_key'], $id);
            $query = $this->db->get(db_prefix() . $config['table']);
            $record = $query->row_array();
            
            if (!$record) {
                $this->json_response(false, null, 'Registro não encontrado', 404);
                return;
            }
            
            $this->json_response(true, $record, 'Registro encontrado');
        } else {
            // Listar registros
            $page = (int)$this->input->get('page') ?: 1;
            $limit = min((int)$this->input->get('limit') ?: 20, 100);
            $offset = ($page - 1) * $limit;
            
            // Busca
            $search = $this->input->get('search');
            if ($search) {
                $this->db->like('company', $search); // Campo padrão para busca
            }
            
            // Total de registros
            $total_query = clone $this->db;
            $total = $total_query->count_all_results(db_prefix() . $config['table']);
            
            // Aplicar paginação
            $this->db->limit($limit, $offset);
            $query = $this->db->get(db_prefix() . $config['table']);
            $records = $query->result_array();
            
            $meta = [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ];
            
            $this->json_response(true, $records, 'Registros recuperados', 200, $meta);
        }
    }
    
    private function handle_post($config)
    {
        $data = json_decode($this->input->raw_input_stream, true);
        
        if (!$data) {
            $this->json_response(false, null, 'Dados JSON inválidos', 400);
            return;
        }
        
        // Verificar campos obrigatórios
        foreach ($config['required'] as $field) {
            if (empty($data[$field])) {
                $this->json_response(false, null, "Campo obrigatório: {$field}", 400);
                return;
            }
        }
        
        // Remover campos somente leitura
        foreach ($config['readonly'] as $field) {
            unset($data[$field]);
        }
        
        $data['datecreated'] = date('Y-m-d H:i:s');
        
        $this->db->insert(db_prefix() . $config['table'], $data);
        $insert_id = $this->db->insert_id();
        
        if (!$insert_id) {
            $this->json_response(false, null, 'Erro ao criar registro', 500);
            return;
        }
        
        // Retornar registro criado
        $this->db->where($config['primary_key'], $insert_id);
        $query = $this->db->get(db_prefix() . $config['table']);
        $record = $query->row_array();
        
        $this->json_response(true, $record, 'Registro criado', 201);
    }
    
    private function handle_put($config, $id)
    {
        if (!$id) {
            $this->json_response(false, null, 'ID obrigatório para atualização', 400);
            return;
        }
        
        // Verificar se existe
        $this->db->where($config['primary_key'], $id);
        if ($this->db->count_all_results(db_prefix() . $config['table']) === 0) {
            $this->json_response(false, null, 'Registro não encontrado', 404);
            return;
        }
        
        $data = json_decode($this->input->raw_input_stream, true);
        
        if (!$data) {
            $this->json_response(false, null, 'Dados JSON inválidos', 400);
            return;
        }
        
        // Remover campos somente leitura
        foreach ($config['readonly'] as $field) {
            unset($data[$field]);
        }
        
        $this->db->where($config['primary_key'], $id);
        $this->db->update(db_prefix() . $config['table'], $data);
        
        // Retornar registro atualizado
        $this->db->where($config['primary_key'], $id);
        $query = $this->db->get(db_prefix() . $config['table']);
        $record = $query->row_array();
        
        $this->json_response(true, $record, 'Registro atualizado');
    }
    
    private function handle_delete($config, $id)
    {
        if (!$id) {
            $this->json_response(false, null, 'ID obrigatório para exclusão', 400);
            return;
        }
        
        $this->db->where($config['primary_key'], $id);
        $affected = $this->db->delete(db_prefix() . $config['table']);
        
        if ($this->db->affected_rows() === 0) {
            $this->json_response(false, null, 'Registro não encontrado', 404);
            return;
        }
        
        $this->json_response(true, null, 'Registro excluído');
    }
    
    public function join()
    {
        if (!$this->validate_auth()) {
            return;
        }
        
        $vat = $this->input->get('vat');
        if (!$vat) {
            $this->json_response(false, null, 'Parâmetro vat obrigatório', 400);
            return;
        }
        
        // Limpar CPF/CNPJ
        $vat = preg_replace('/[^0-9]/', '', $vat);
        
        $this->db->where('vat', $vat);
        $query = $this->db->get(db_prefix() . 'clients');
        $client = $query->row_array();
        
        if (!$client) {
            $this->json_response(false, null, 'Cliente não encontrado', 404);
            return;
        }
        
        $this->json_response(true, $client, 'Cliente encontrado');
    }
}