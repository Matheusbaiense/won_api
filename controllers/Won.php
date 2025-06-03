<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Won extends APP_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * API RESTful para operações CRUD em tabelas do Perfex CRM
     * 
     * Permite realizar operações de criação, leitura, atualização e exclusão
     * em diversas tabelas do Perfex CRM através de endpoints padronizados.
     * 
     * @param string $tabela Nome da tabela sem o prefixo 'tbl'
     * @param int|null $id ID do registro (opcional)
     * @return void Saída JSON direta
     */
    public function api($tabela = null, $id = null)
    {
        header('Content-Type: application/json');

        // Log de acesso
        log_message('info', 'API acessada: ' . $this->router->fetch_method() . 
            ' - IP: ' . $this->input->ip_address() . 
            ' - Parâmetros: ' . json_encode($this->input->get()) . 
            ' - Método: ' . $this->input->method());

        // Verificar autenticação
        $token = $this->input->get_request_header('Authorization', TRUE);
        $expected_token = get_option('won_api_token');

        // Melhorar validação do token
        if (empty($token)) {
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'error' => 'Token de autenticação não fornecido',
                'error_code' => 'AUTH_MISSING'
            ]);
            exit;
        } else if (empty($expected_token) || $token !== $expected_token) {
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'error' => 'Token de autenticação inválido',
                'error_code' => 'AUTH_INVALID'
            ]);
            exit;
        }

        // Verificar rate limiting
        $ip = $this->input->ip_address();
        $rate_key = 'api_rate_' . md5($ip . '_' . $token);
        $rate_count = $this->session->userdata($rate_key) ?: 0;
        $rate_limit = 100; // Limite de requisições por hora

        if ($rate_count >= $rate_limit) {
            $this->output->set_status_header(429);
            echo json_encode([
                'success' => false,
                'error' => 'Limite de requisições excedido. Tente novamente mais tarde.',
                'error_code' => 'RATE_LIMIT_EXCEEDED'
            ]);
            exit;
        }

        // Incrementar contador
        $this->session->set_userdata($rate_key, $rate_count + 1);

        // Obter o método HTTP
        $method = $this->input->method();

        // Adicionar prefixo 'tbl' à tabela
        $tabela = 'tbl' . $tabela;

        // Lista branca de tabelas permitidas
        $tabelas_permitidas = [
            'tblclients', 'tblcontacts', 'tblleads', 'tblprojects', 
            'tbltasks', 'tblinvoices', 'tblstaff', 'tbltickets'
        ];

        // Validar o nome da tabela usando lista branca
        if (!in_array($tabela, $tabelas_permitidas)) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'error' => 'Tabela inválida ou não permitida',
                'error_code' => 'INVALID_TABLE'
            ]);
            exit;
        }

        switch ($method) {
            case 'get':
                if (!$id) {
                    // Obter parâmetros de paginação
                    $page = (int)$this->input->get('page') ?: 1;
                    $limit = (int)$this->input->get('limit') ?: 20;
                    $offset = ($page - 1) * $limit;

                    if ($this->input->get('search') && !empty($this->input->get('search'))) {
                        $search = $this->input->get('search');

                        // Obter colunas da tabela
                        $columns = $this->db->query("SHOW COLUMNS FROM `$tabela`")->result_array();
                        $columns = array_column($columns, 'Field');

                        $where = [];
                        $valores = [];
                        foreach ($columns as $col) {
                            if (preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                                $where[] = "`$col` LIKE ?";
                                $valores[] = "%$search%";
                            }
                        }

                        $sql = "SELECT * FROM `$tabela`";
                        $count_sql = "SELECT COUNT(*) as total FROM `$tabela`";
                        
                        if ($where) {
                            $where_clause = " WHERE " . implode(" OR ", $where);
                            $sql .= $where_clause;
                            $count_sql .= $where_clause;
                        }

                        // Adicionar contagem total para metadados de paginação
                        $total_count = $this->db->query($count_sql, $valores)->row()->total;

                        // Adicionar paginação à query principal
                        $sql .= " LIMIT ? OFFSET ?";
                        $valores[] = $limit;
                        $valores[] = $offset;

                        $query = $this->db->query($sql, $valores);
                        $result = $query->result_array();

                        // Retornar dados com metadados de paginação
                        echo json_encode([
                            'success' => true,
                            'data' => $result,
                            'message' => 'Operação realizada com sucesso',
                            'meta' => [
                                'page' => $page,
                                'limit' => $limit,
                                'total' => $total_count,
                                'total_pages' => ceil($total_count / $limit)
                            ]
                        ]);
                    } else {
                        $campos = $this->input->get();
                        $where = [];
                        $valores = [];

                        foreach ($campos as $col => $val) {
                            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col) || in_array($col, ['page', 'limit'])) continue;
                            $where[] = "`$col` LIKE ?";
                            $valores[] = "%$val%";
                        }

                        $sql = "SELECT * FROM `$tabela`";
                        $count_sql = "SELECT COUNT(*) as total FROM `$tabela`";
                        
                        if ($where) {
                            $where_clause = " WHERE " . implode(" AND ", $where);
                            $sql .= $where_clause;
                            $count_sql .= $where_clause;
                        }

                        // Adicionar contagem total para metadados de paginação
                        $total_count = $this->db->query($count_sql, $valores)->row()->total;

                        // Adicionar paginação à query principal
                        $sql .= " LIMIT ? OFFSET ?";
                        $valores[] = $limit;
                        $valores[] = $offset;

                        $query = $this->db->query($sql, $valores);
                        $result = $query->result_array();

                        // Retornar dados com metadados de paginação
                        echo json_encode([
                            'success' => true,
                            'data' => $result,
                            'message' => 'Operação realizada com sucesso',
                            'meta' => [
                                'page' => $page,
                                'limit' => $limit,
                                'total' => $total_count,
                                'total_pages' => ceil($total_count / $limit)
                            ]
                        ]);
                    }
                } else {
                    if (!is_numeric($id)) {
                        $this->output->set_status_header(400);
                        echo json_encode([
                            'success' => false,
                            'error' => 'ID inválido',
                            'error_code' => 'INVALID_ID'
                        ]);
                        exit;
                    }

                    $query = $this->db->query("SELECT * FROM `$tabela` WHERE id = ?", [$id]);
                    $result = $query->row_array();
                    if ($result) {
                        echo json_encode([
                            'success' => true,
                            'data' => $result,
                            'message' => 'Operação realizada com sucesso'
                        ]);
                    } else {
                        $this->output->set_status_header(404);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Registro não encontrado',
                            'error_code' => 'NOT_FOUND'
                        ]);
                    }
                }
                break;

            case 'post':
                $data = json_decode(file_get_contents('php://input'), true);
                if (empty($data) || !is_array($data)) {
                    $this->output->set_status_header(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Dados inválidos',
                        'error_code' => 'INVALID_DATA'
                    ]);
                    exit;
                }

                // Validar dados obrigatórios
                $required_fields = [];
                switch ($tabela) {
                    case 'tblclients':
                        $required_fields = ['company'];
                        break;
                    case 'tblcontacts':
                        $required_fields = ['firstname', 'email', 'userid'];
                        break;
                    case 'tblleads':
                        $required_fields = ['name'];
                        break;
                    // Adicionar outros casos conforme necessário
                }

                // Verificar campos obrigatórios
                foreach ($required_fields as $field) {
                    if (!isset($data[$field]) || empty($data[$field])) {
                        $this->output->set_status_header(422);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Campo obrigatório não fornecido: ' . $field,
                            'error_code' => 'MISSING_REQUIRED_FIELD'
                        ]);
                        exit;
                    }
                }

                // Validar formatos específicos
                if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $this->output->set_status_header(422);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Formato de email inválido',
                        'error_code' => 'INVALID_EMAIL_FORMAT'
                    ]);
                    exit;
                }

                // Validar e formatar CNPJ/CPF
                if (isset($data['vat'])) {
                    // Remover caracteres não numéricos
                    $data['vat'] = preg_replace('/\D/', '', $data['vat']);
                    
                    // Validar tamanho (CPF: 11, CNPJ: 14)
                    if (strlen($data['vat']) != 11 && strlen($data['vat']) != 14) {
                        $this->output->set_status_header(422);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Formato de CPF/CNPJ inválido',
                            'error_code' => 'INVALID_VAT_FORMAT'
                        ]);
                        exit;
                    }
                }

                $cols = array_keys($data);
                $vals = array_values($data);

                foreach ($cols as $col) {
                    if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                        $this->output->set_status_header(400);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Coluna inválida: ' . $col,
                            'error_code' => 'INVALID_COLUMN'
                        ]);
                        exit;
                    }
                }

                $placeholders = implode(',', array_fill(0, count($cols), '?'));
                $sql = "INSERT INTO `$tabela` (`" . implode('`,`', $cols) . "`) VALUES ($placeholders)";
                $this->db->query($sql, $vals);
                $insert_id = $this->db->insert_id();

                // Log da operação
                log_message('info', 'Operação POST realizada com sucesso - ' .
                    'Tabela: ' . $tabela . 
                    ' - ID: ' . $insert_id . 
                    ' - Resultado: ' . json_encode(['id' => $insert_id]));

                echo json_encode([
                    'success' => true,
                    'data' => ['id' => $insert_id],
                    'message' => 'Operação realizada com sucesso'
                ]);
                break;

            case 'put':
                if (!$id) {
                    $this->output->set_status_header(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'ID obrigatório',
                        'error_code' => 'ID_REQUIRED'
                    ]);
                    exit;
                }

                if (!is_numeric($id)) {
                    $this->output->set_status_header(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'ID inválido',
                        'error_code' => 'INVALID_ID'
                    ]);
                    exit;
                }

                $data = json_decode(file_get_contents('php://input'), true);
                if (empty($data) || !is_array($data)) {
                    $this->output->set_status_header(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Dados inválidos',
                        'error_code' => 'INVALID_DATA'
                    ]);
                    exit;
                }

                // Validar formatos específicos
                if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $this->output->set_status_header(422);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Formato de email inválido',
                        'error_code' => 'INVALID_EMAIL_FORMAT'
                    ]);
                    exit;
                }

                // Validar e formatar CNPJ/CPF
                if (isset($data['vat'])) {
                    // Remover caracteres não numéricos
                    $data['vat'] = preg_replace('/\D/', '', $data['vat']);
                    
                    // Validar tamanho (CPF: 11, CNPJ: 14)
                    if (strlen($data['vat']) != 11 && strlen($data['vat']) != 14) {
                        $this->output->set_status_header(422);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Formato de CPF/CNPJ inválido',
                            'error_code' => 'INVALID_VAT_FORMAT'
                        ]);
                        exit;
                    }
                }

                $cols = array_keys($data);
                $vals = array_values($data);

                foreach ($cols as $col) {
                    if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                        $this->output->set_status_header(400);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Coluna inválida: ' . $col,
                            'error_code' => 'INVALID_COLUMN'
                        ]);
                        exit;
                    }
                }

                $set = implode(',', array_map(fn($col) => "`$col` = ?", $cols));
                $sql = "UPDATE `$tabela` SET $set WHERE id = ?";
                $this->db->query($sql, [...$vals, $id]);

                $affected_rows = $this->db->affected_rows();
                if ($affected_rows > 0) {
                    // Log da operação
                    log_message('info', 'Operação PUT realizada com sucesso - ' .
                        'Tabela: ' . $tabela . 
                        ' - ID: ' . $id);

                    echo json_encode([
                        'success' => true,
                        'data' => ['status' => 'Atualizado'],
                        'message' => 'Operação realizada com sucesso'
                    ]);
                } else {
                    $this->output->set_status_header(404);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Registro não encontrado',
                        'error_code' => 'NOT_FOUND'
                    ]);
                }
                break;

            case 'delete':
                if (!$id) {
                    $this->output->set_status_header(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'ID obrigatório',
                        'error_code' => 'ID_REQUIRED'
                    ]);
                    exit;
                }

                if (!is_numeric($id)) {
                    $this->output->set_status_header(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'ID inválido',
                        'error_code' => 'INVALID_ID'
                    ]);
                    exit;
                }

                $this->db->query("DELETE FROM `$tabela` WHERE id = ?", [$id]);
                $affected_rows = $this->db->affected_rows();
                if ($affected_rows > 0) {
                    // Log da operação
                    log_message('info', 'Operação DELETE realizada com sucesso - ' .
                        'Tabela: ' . $tabela . 
                        ' - ID: ' . $id);

                    echo json_encode([
                        'success' => true,
                        'data' => ['status' => 'Removido'],
                        'message' => 'Operação realizada com sucesso'
                    ]);
                } else {
                    $this->output->set_status_header(404);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Registro não encontrado',
                        'error_code' => 'NOT_FOUND'
                    ]);
                }
                break;

            default:
                $this->output->set_status_header(405);
                echo json_encode([
                    'success' => false,
                    'error' => 'Método não suportado',
                    'error_code' => 'METHOD_NOT_ALLOWED'
                ]);
                break;
        }
    }

    /**
     * Endpoint para consulta JOIN entre clientes, contatos, faturas e pagamentos
     * 
     * Permite buscar informações relacionadas usando CNPJ/CPF como chave de busca.
     * Retorna dados consolidados de clientes, contatos, faturas e seus status.
     * 
     * @return void Saída JSON direta
     */
    public function join()
    {
        header('Content-Type: application/json');

        // Log de acesso
        log_message('info', 'API JOIN acessada: ' . $this->router->fetch_method() . 
            ' - IP: ' . $this->input->ip_address() . 
            ' - Parâmetros: ' . json_encode($this->input->get()) . 
            ' - Método: ' . $this->input->method());

        // Verificar autenticação
        $token = $this->input->get_request_header('Authorization', TRUE);
        $expected_token = get_option('won_api_token');

        // Melhorar validação do token
        if (empty($token)) {
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'error' => 'Token de autenticação não fornecido',
                'error_code' => 'AUTH_MISSING'
            ]);
            exit;
        } else if (empty($expected_token) || $token !== $expected_token) {
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'error' => 'Token de autenticação inválido',
                'error_code' => 'AUTH_INVALID'
            ]);
            exit;
        }

        // Verificar rate limiting
        $ip = $this->input->ip_address();
        $rate_key = 'api_rate_' . md5($ip . '_' . $token);
        $rate_count = $this->session->userdata($rate_key) ?: 0;
        $rate_limit = 100; // Limite de requisições por hora

        if ($rate_count >= $rate_limit) {
            $this->output->set_status_header(429);
            echo json_encode([
                'success' => false,
                'error' => 'Limite de requisições excedido. Tente novamente mais tarde.',
                'error_code' => 'RATE_LIMIT_EXCEEDED'
            ]);
            exit;
        }

        // Incrementar contador
        $this->session->set_userdata($rate_key, $rate_count + 1);

        try {
            // Obter o parâmetro vat (CNPJ/CPF) da requisição
            $vat = $this->input->get('vat');

            // Construir a consulta com JOINs
            $sql = "
                SELECT 
                    cl.company AS client_company,
                    cl.vat AS client_vat,
                    CASE 
                        WHEN cl.active = 1 THEN 'Ativo'
                        WHEN cl.active = 0 THEN 'Inativo'
                        ELSE 'Desconhecido'
                    END AS client_active_status,
                    co.firstname AS contact_firstname,
                    co.lastname AS contact_lastname,
                    co.email AS contact_email,
                    inv.formatted_number AS invoice_id,
                    inv.number AS invoice_number,
                    inv.total AS invoice_total,
                    DATE_FORMAT(inv.date, '%d/%m/%Y') AS invoice_date,
                    DATE_FORMAT(inv.duedate, '%d/%m/%Y') AS invoice_duedate,
                    CASE 
                        WHEN inv.status = 1 THEN 'Pendente'
                        WHEN inv.status = 2 THEN 'Pago'
                        WHEN inv.status = 4 THEN 'Vencido'
                        ELSE 'Desconhecido'
                    END AS invoice_status,
                    ipr.amount AS payment_amount,
                    DATE_FORMAT(ipr.daterecorded, '%d/%m/%Y') AS payment_daterecorded,
                    ipr.paymentmethod AS payment_method
                FROM tblclients cl
                LEFT JOIN tblcontacts co ON co.userid = cl.userid
                LEFT JOIN tblinvoices inv ON inv.clientid = cl.userid
                LEFT JOIN tblinvoicepaymentrecords ipr ON ipr.invoiceid = inv.id
            ";

            // Adicionar filtro por vat se o parâmetro estiver presente
            if ($vat) {
                $sql .= " WHERE cl.vat = ?";
                $params = [$vat];
            } else {
                $params = [];
            }

            // Executar a consulta
            $query = $this->db->query($sql, $params);
            if ($query === FALSE) {
                $this->output->set_status_header(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Erro ao executar a consulta',
                    'error_code' => 'SERVER_ERROR'
                ]);
                exit;
            }

            // Retornar os resultados
            $results = $query->result_array();
            echo json_encode([
                'success' => true,
                'data' => $results,
                'message' => 'Operação realizada com sucesso'
            ]);
        } catch (Exception $e) {
            // Registrar o erro para depuração
            log_message('error', 'WON API Error: ' . $e->getMessage());
            
            // Determinar o código de status apropriado
            $status_code = 500;
            if (strpos($e->getMessage(), 'not found') !== false) {
                $status_code = 404;
                $error_code = 'NOT_FOUND';
            } elseif (strpos($e->getMessage(), 'permission') !== false) {
                $status_code = 403;
                $error_code = 'FORBIDDEN';
            } elseif (strpos($e->getMessage(), 'invalid') !== false) {
                $status_code = 400;
                $error_code = 'BAD_REQUEST';
            } else {
                $error_code = 'SERVER_ERROR';
            }
            
            $this->output->set_status_header($status_code);
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao processar requisição: ' . $e->getMessage(),
                'error_code' => $error_code
            ]);
        }
    }
}