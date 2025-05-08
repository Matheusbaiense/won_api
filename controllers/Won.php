<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Won extends APP_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function api($tabela = null, $id = null)
    {
        header('Content-Type: application/json');

        // Verificar autenticação
        $token = $this->input->get_request_header('Authorization', TRUE);
        $expected_token = get_option('won_api_token');
        if (empty($expected_token) || $token !== $expected_token) {
            $this->output->set_status_header(401);
            echo json_encode(['erro' => 'Token inválido ou não fornecido']);
            exit;
        }

        // Obter o método HTTP
        $method = $this->input->method();

        // Adicionar prefixo 'tbl' à tabela
        $tabela = 'tbl' . $tabela;

        // Validar o nome da tabela
        if (!$tabela || !preg_match('/^[a-zA-Z0-9_]+$/', $tabela)) {
            $this->output->set_status_header(400);
            echo json_encode(['erro' => 'Tabela inválida ou não especificada']);
            exit;
        }

        // Verificar se a tabela existe
        $table_exists = $this->db->query("SHOW TABLES LIKE '$tabela'")->num_rows() > 0;
        if (!$table_exists) {
            $this->output->set_status_header(400);
            echo json_encode(['erro' => 'Tabela não encontrada']);
            exit;
        }

        switch ($method) {
            case 'get':
                if (!$id) {
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
                        if ($where) {
                            $sql .= " WHERE " . implode(" OR ", $where);
                        }

                        $query = $this->db->query($sql, $valores);
                        echo json_encode($query->result_array());
                    } else {
                        $campos = $this->input->get();
                        $where = [];
                        $valores = [];

                        foreach ($campos as $col => $val) {
                            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) continue;
                            $where[] = "`$col` LIKE ?";
                            $valores[] = "%$val%";
                        }

                        $sql = "SELECT * FROM `$tabela`";
                        if ($where) {
                            $sql .= " WHERE " . implode(" AND ", $where);
                        }

                        $query = $this->db->query($sql, $valores);
                        echo json_encode($query->result_array());
                    }
                } else {
                    if (!is_numeric($id)) {
                        $this->output->set_status_header(400);
                        echo json_encode(['erro' => 'ID inválido']);
                        exit;
                    }

                    $query = $this->db->query("SELECT * FROM `$tabela` WHERE id = ?", [$id]);
                    $result = $query->row_array();
                    if ($result) {
                        echo json_encode($result);
                    } else {
                        $this->output->set_status_header(404);
                        echo json_encode(['erro' => 'Registro não encontrado']);
                    }
                }
                break;

            case 'post':
                $data = json_decode(file_get_contents('php://input'), true);
                if (empty($data) || !is_array($data)) {
                    $this->output->set_status_header(400);
                    echo json_encode(['erro' => 'Dados inválidos']);
                    exit;
                }

                $cols = array_keys($data);
                $vals = array_values($data);

                foreach ($cols as $col) {
                    if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                        $this->output->set_status_header(400);
                        echo json_encode(['erro' => 'Coluna inválida: ' . $col]);
                        exit;
                    }
                }

                $placeholders = implode(',', array_fill(0, count($cols), '?'));
                $sql = "INSERT INTO `$tabela` (`" . implode('`,`', $cols) . "`) VALUES ($placeholders)";
                $this->db->query($sql, $vals);
                echo json_encode(['id' => $this->db->insert_id()]);
                break;

            case 'put':
                if (!$id) {
                    $this->output->set_status_header(400);
                    echo json_encode(['erro' => 'ID obrigatório']);
                    exit;
                }

                if (!is_numeric($id)) {
                    $this->output->set_status_header(400);
                    echo json_encode(['erro' => 'ID inválido']);
                    exit;
                }

                $data = json_decode(file_get_contents('php://input'), true);
                if (empty($data) || !is_array($data)) {
                    $this->output->set_status_header(400);
                    echo json_encode(['erro' => 'Dados inválidos']);
                    exit;
                }

                $cols = array_keys($data);
                $vals = array_values($data);

                foreach ($cols as $col) {
                    if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                        $this->output->set_status_header(400);
                        echo json_encode(['erro' => 'Coluna inválida: ' . $col]);
                        exit;
                    }
                }

                $set = implode(',', array_map(fn($col) => "`$col` = ?", $cols));
                $sql = "UPDATE `$tabela` SET $set WHERE id = ?";
                $this->db->query($sql, [...$vals, $id]);

                $affected_rows = $this->db->affected_rows();
                if ($affected_rows > 0) {
                    echo json_encode(['status' => 'Atualizado']);
                } else {
                    $this->output->set_status_header(404);
                    echo json_encode(['erro' => 'Registro não encontrado']);
                }
                break;

            case 'delete':
                if (!$id) {
                    $this->output->set_status_header(400);
                    echo json_encode(['erro' => 'ID obrigatório']);
                    exit;
                }

                if (!is_numeric($id)) {
                    $this->output->set_status_header(400);
                    echo json_encode(['erro' => 'ID inválido']);
                    exit;
                }

                $this->db->query("DELETE FROM `$tabela` WHERE id = ?", [$id]);
                $affected_rows = $this->db->affected_rows();
                if ($affected_rows > 0) {
                    echo json_encode(['status' => 'Removido']);
                } else {
                    $this->output->set_status_header(404);
                    echo json_encode(['erro' => 'Registro não encontrado']);
                }
                break;

            default:
                $this->output->set_status_header(405);
                echo json_encode(['erro' => 'Método não suportado']);
                break;
        }
    }

    public function join()
    {
        header('Content-Type: application/json');

        // Verificar autenticação
        $token = $this->input->get_request_header('Authorization', TRUE);
        $expected_token = get_option('won_api_token');
        if (empty($expected_token) || $token !== $expected_token) {
            $this->output->set_status_header(401);
            echo json_encode(['erro' => 'Token inválido ou não fornecido']);
            exit;
        }

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
                echo json_encode(['erro' => 'Erro ao executar a consulta']);
                exit;
            }

            // Retornar os resultados
            $results = $query->result_array();
            echo json_encode($results);
        } catch (Exception $e) {
            $this->output->set_status_header(500);
            echo json_encode(['erro' => 'Ocorreu um erro inesperado: ' . $e->getMessage()]);
            exit;
        }
    }

}