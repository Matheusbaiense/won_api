<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Won extends APP_Controller
{
    private $allowed_tables = [
        'tblclients', 'tblcontacts', 'tblleads', 'tblprojects', 
        'tbltasks', 'tblinvoices', 'tblstaff', 'tbltickets'
    ];

    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
    }

    /**
     * Helper: Resposta JSON padronizada
     */
    private function response($success, $data = null, $message = '', $error_code = '', $status = 200)
    {
        $this->output->set_status_header($status);
        $response = ['success' => $success];
        
        if ($success) {
            if ($data !== null) $response['data'] = $data;
            if ($message) $response['message'] = $message;
        } else {
            $response['error'] = $message;
            if ($error_code) $response['error_code'] = $error_code;
        }
        
        echo json_encode($response);
        exit;
    }

    /**
     * Helper: Validar autenticação
     */
    private function validateAuth()
    {
        $token = $this->input->get_request_header('Authorization', TRUE);
        $expected = get_option('won_api_token');

        if (empty($token)) {
            $this->response(false, null, 'Token não fornecido', 'AUTH_MISSING', 401);
        }
        
        if (empty($expected) || $token !== $expected) {
            $this->response(false, null, 'Token inválido', 'AUTH_INVALID', 401);
        }

        // Rate limiting
        $ip = $this->input->ip_address();
        $rate_key = 'api_rate_' . md5($ip . $token);
        $rate_count = $this->session->userdata($rate_key) ?: 0;
        
        if ($rate_count >= 100) {
            $this->response(false, null, 'Limite de requisições excedido', 'RATE_LIMIT_EXCEEDED', 429);
        }
        
        $this->session->set_userdata($rate_key, $rate_count + 1);
    }

    /**
     * Helper: Validar tabela
     */
    private function validateTable($table)
    {
        $full_table = 'tbl' . $table;
        if (!in_array($full_table, $this->allowed_tables)) {
            $this->response(false, null, 'Tabela não permitida', 'INVALID_TABLE', 400);
        }
        return $full_table;
    }

    /**
     * Helper: Validar ID
     */
    private function validateId($id)
    {
        if (!is_numeric($id)) {
            $this->response(false, null, 'ID inválido', 'INVALID_ID', 400);
        }
        return (int)$id;
    }

    /**
     * Helper: Buscar com paginação
     */
    private function searchWithPagination($table, $where_clause, $values)
    {
        $page = (int)$this->input->get('page') ?: 1;
        $limit = (int)$this->input->get('limit') ?: 20;
        $offset = ($page - 1) * $limit;

        // Contar total
        $count_sql = "SELECT COUNT(*) as total FROM `$table`" . $where_clause;
        $total = $this->db->query($count_sql, $values)->row()->total;

        // Buscar dados
        $sql = "SELECT * FROM `$table`" . $where_clause . " LIMIT ? OFFSET ?";
        $values[] = $limit;
        $values[] = $offset;
        $data = $this->db->query($sql, $values)->result_array();

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
     * API RESTful Principal
     */
    public function api($table = null, $id = null)
    {
        $this->validateAuth();
        $table = $this->validateTable($table);
        $method = $this->input->method();

        switch ($method) {
            case 'get':
                if ($id) {
                    $id = $this->validateId($id);
                    $result = $this->db->query("SELECT * FROM `$table` WHERE id = ?", [$id])->row_array();
                    if ($result) {
                        $this->response(true, $result, 'Sucesso');
                    } else {
                        $this->response(false, null, 'Registro não encontrado', 'NOT_FOUND', 404);
                    }
                } else {
                    // Busca com filtros
                    $where = [];
                    $values = [];
                    
                    if ($search = $this->input->get('search')) {
                        $columns = $this->db->query("SHOW COLUMNS FROM `$table`")->result_array();
                        foreach ($columns as $col) {
                            if (preg_match('/^[a-zA-Z0-9_]+$/', $col['Field'])) {
                                $where[] = "`{$col['Field']}` LIKE ?";
                                $values[] = "%$search%";
                            }
                        }
                        $where_clause = $where ? ' WHERE ' . implode(' OR ', $where) : '';
                    } else {
                        foreach ($this->input->get() as $col => $val) {
                            if (preg_match('/^[a-zA-Z0-9_]+$/', $col) && !in_array($col, ['page', 'limit'])) {
                                $where[] = "`$col` LIKE ?";
                                $values[] = "%$val%";
                            }
                        }
                        $where_clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
                    }

                    $result = $this->searchWithPagination($table, $where_clause, $values);
                    echo json_encode([
                        'success' => true,
                        'data' => $result['data'],
                        'meta' => $result['meta']
                    ]);
                    exit;
                }
                break;

            case 'post':
                $data = json_decode(file_get_contents('php://input'), true);
                if (!$data) {
                    $this->response(false, null, 'Dados inválidos', 'INVALID_DATA', 400);
                }

                // Validações específicas
                if ($table === 'tblclients' && empty($data['company'])) {
                    $this->response(false, null, 'Campo company obrigatório', 'MISSING_REQUIRED_FIELD', 422);
                }

                if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $this->response(false, null, 'Email inválido', 'INVALID_EMAIL_FORMAT', 422);
                }

                if ($this->db->insert($table, $data)) {
                    $this->response(true, ['id' => $this->db->insert_id()], 'Criado com sucesso', '', 201);
                } else {
                    $this->response(false, null, 'Erro ao criar registro', 'SERVER_ERROR', 500);
                }
                break;

            case 'put':
                if (!$id) {
                    $this->response(false, null, 'ID obrigatório', 'ID_REQUIRED', 400);
                }
                $id = $this->validateId($id);
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$data) {
                    $this->response(false, null, 'Dados inválidos', 'INVALID_DATA', 400);
                }

                if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $this->response(false, null, 'Email inválido', 'INVALID_EMAIL_FORMAT', 422);
                }

                if ($this->db->where('id', $id)->update($table, $data)) {
                    $this->response(true, null, 'Atualizado com sucesso');
                } else {
                    $this->response(false, null, 'Erro ao atualizar', 'SERVER_ERROR', 500);
                }
                break;

            case 'delete':
                if (!$id) {
                    $this->response(false, null, 'ID obrigatório', 'ID_REQUIRED', 400);
                }
                $id = $this->validateId($id);
                
                if ($this->db->where('id', $id)->delete($table)) {
                    $this->response(true, null, 'Removido com sucesso');
                } else {
                    $this->response(false, null, 'Erro ao remover', 'SERVER_ERROR', 500);
                }
                break;

            default:
                $this->response(false, null, 'Método não suportado', 'METHOD_NOT_ALLOWED', 405);
        }
    }

    /**
     * Busca por CNPJ/CPF
     */
    public function join()
    {
        $this->validateAuth();
        $vat = $this->input->get('vat');
        
        if (!$vat || !preg_match('/^\d{11}$|^\d{14}$/', $vat)) {
            $this->response(false, null, 'CPF/CNPJ inválido', 'INVALID_VAT_FORMAT', 400);
        }

        $sql = "SELECT c.*, p.name as project_name, i.number as invoice_number 
                FROM tblclients c 
                LEFT JOIN tblprojects p ON p.clientid = c.userid 
                LEFT JOIN tblinvoices i ON i.clientid = c.userid 
                WHERE c.vat = ?";
        
        $result = $this->db->query($sql, [$vat])->result_array();
        
        if ($result) {
            $this->response(true, $result, 'Dados encontrados');
        } else {
            $this->response(false, null, 'Nenhum dado encontrado', 'NOT_FOUND', 404);
        }
    }
}