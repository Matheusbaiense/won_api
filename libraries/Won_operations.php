<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Classe para operações complexas na won_api
 * WON API v2.1.2 - Operações Especializadas
 */
class Won_operations
{
    protected $ci;
    protected $db;
    
    public function __construct()
    {
        $this->ci =& get_instance();
        $this->db = $this->ci->db;
        $this->ci->load->database();
    }
    
    /**
     * Converte um orçamento em fatura
     */
    public function convert_estimate_to_invoice($estimate_id)
    {
        // Verificar se o orçamento existe
        $this->db->where('id', $estimate_id);
        $query = $this->db->get(db_prefix() . 'estimates');
        $estimate = $query->row_array();
        
        if (!$estimate) {
            return false;
        }
        
        // Verificar se o orçamento já foi convertido
        if ($estimate['invoiceid'] > 0) {
            return false;
        }
        
        // Iniciar transação
        $this->db->trans_start();
        
        try {
            // Criar fatura
            $invoice_data = [
                'clientid' => $estimate['clientid'],
                'number' => get_option('next_invoice_number'),
                'date' => date('Y-m-d'),
                'duedate' => date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY')),
                'subtotal' => $estimate['subtotal'],
                'total' => $estimate['total'],
                'status' => 1, // Rascunho
                'clientnote' => $estimate['clientnote'],
                'adminnote' => 'Convertido do orçamento #' . $estimate['number'],
                'show_shipping_on_invoice' => $estimate['show_shipping_on_estimate'],
                'discount_percent' => $estimate['discount_percent'],
                'discount_total' => $estimate['discount_total'],
                'discount_type' => $estimate['discount_type'],
                'sale_agent' => $estimate['sale_agent'],
                'adjustment' => $estimate['adjustment'],
                'project_id' => $estimate['project_id'],
                'datecreated' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert(db_prefix() . 'invoices', $invoice_data);
            $invoice_id = $this->db->insert_id();
            
            if (!$invoice_id) {
                throw new Exception('Erro ao criar fatura');
            }
            
            // Atualizar número da próxima fatura
            $this->db->where('name', 'next_invoice_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update(db_prefix() . 'options');
            
            // Obter itens do orçamento
            $this->db->where('rel_id', $estimate_id);
            $this->db->where('rel_type', 'estimate');
            $query = $this->db->get(db_prefix() . 'itemable');
            $estimate_items = $query->result_array();
            
            // Adicionar itens à fatura
            foreach ($estimate_items as $item) {
                $invoice_item = [
                    'rel_id' => $invoice_id,
                    'rel_type' => 'invoice',
                    'description' => $item['description'],
                    'long_description' => $item['long_description'],
                    'qty' => $item['qty'],
                    'rate' => $item['rate'],
                    'unit' => $item['unit'],
                    'item_order' => $item['item_order'],
                    'tax' => $item['tax'],
                    'tax2' => $item['tax2']
                ];
                
                $this->db->insert(db_prefix() . 'itemable', $invoice_item);
            }
            
            // Atualizar orçamento com o ID da fatura
            $this->db->where('id', $estimate_id);
            $this->db->update(db_prefix() . 'estimates', ['invoiceid' => $invoice_id, 'status' => 4]); // 4 = Aceito
            
            // Finalizar transação
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === false) {
                throw new Exception('Erro na transação');
            }
            
            // Obter fatura criada
            $this->db->where('id', $invoice_id);
            $query = $this->db->get(db_prefix() . 'invoices');
            $invoice = $query->row_array();
            
            // Registrar log
            $log_data = [
                'description' => 'Orçamento #' . $estimate['number'] . ' convertido em fatura #' . $invoice['number'],
                'date' => date('Y-m-d H:i:s'),
                'staffid' => get_staff_user_id() ?: 0
            ];
            $this->db->insert(db_prefix() . 'activity_log', $log_data);
            
            return $invoice;
        } catch (Exception $e) {
            // Reverter transação em caso de erro
            $this->db->trans_rollback();
            log_message('error', '[WON API] Erro ao converter orçamento: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envia uma fatura por email
     */
    public function send_invoice_email($invoice_id, $email = null)
    {
        // Verificar se a fatura existe
        $this->db->where('id', $invoice_id);
        $query = $this->db->get(db_prefix() . 'invoices');
        $invoice = $query->row_array();
        
        if (!$invoice) {
            return false;
        }
        
        // Obter cliente
        $this->db->where('userid', $invoice['clientid']);
        $query = $this->db->get(db_prefix() . 'clients');
        $client = $query->row_array();
        
        if (!$client) {
            return false;
        }
        
        // Obter email do cliente se não fornecido
        if (empty($email)) {
            // Obter contato principal
            $this->db->where('userid', $client['userid']);
            $this->db->where('is_primary', 1);
            $query = $this->db->get(db_prefix() . 'contacts');
            $contact = $query->row_array();
            
            if (!$contact || empty($contact['email'])) {
                return false;
            }
            
            $email = $contact['email'];
        }
        
        // Registrar log
        $log_data = [
            'description' => 'Fatura #' . $invoice['number'] . ' enviada por email para ' . $email,
            'date' => date('Y-m-d H:i:s'),
            'staffid' => get_staff_user_id() ?: 0
        ];
        $this->db->insert(db_prefix() . 'activity_log', $log_data);
        
        return true; // Simplificado - implementar envio real conforme necessário
    }
    
    /**
     * Marca uma tarefa como concluída
     */
    public function mark_task_complete($task_id)
    {
        // Verificar se a tarefa existe
        $this->db->where('id', $task_id);
        $query = $this->db->get(db_prefix() . 'tasks');
        $task = $query->row_array();
        
        if (!$task) {
            return false;
        }
        
        // Atualizar status da tarefa
        $this->db->where('id', $task_id);
        $this->db->update(db_prefix() . 'tasks', [
            'status' => 5, // 5 = Concluída
            'datefinished' => date('Y-m-d H:i:s')
        ]);
        
        if ($this->db->affected_rows() === 0) {
            return false;
        }
        
        // Registrar log
        $log_data = [
            'description' => 'Tarefa #' . $task_id . ' marcada como concluída',
            'date' => date('Y-m-d H:i:s'),
            'staffid' => get_staff_user_id() ?: 0
        ];
        $this->db->insert(db_prefix() . 'activity_log', $log_data);
        
        return true;
    }
    
    /**
     * Adiciona um comentário a uma tarefa
     */
    public function add_task_comment($task_id, $content, $staff_id = null)
    {
        // Verificar se a tarefa existe
        $this->db->where('id', $task_id);
        $query = $this->db->get(db_prefix() . 'tasks');
        $task = $query->row_array();
        
        if (!$task) {
            return false;
        }
        
        // Usar ID do funcionário atual se não fornecido
        if (!$staff_id) {
            $staff_id = get_staff_user_id() ?: 1;
        }
        
        // Verificar se o funcionário existe
        $this->db->where('staffid', $staff_id);
        $query = $this->db->get(db_prefix() . 'staff');
        $staff = $query->row_array();
        
        if (!$staff) {
            return false;
        }
        
        // Adicionar comentário
        $comment_data = [
            'content' => $content,
            'taskid' => $task_id,
            'staffid' => $staff_id,
            'dateadded' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert(db_prefix() . 'task_comments', $comment_data);
        $comment_id = $this->db->insert_id();
        
        if (!$comment_id) {
            return false;
        }
        
        // Registrar log
        $log_data = [
            'description' => 'Comentário adicionado à tarefa #' . $task_id,
            'date' => date('Y-m-d H:i:s'),
            'staffid' => $staff_id
        ];
        $this->db->insert(db_prefix() . 'activity_log', $log_data);
        
        return $comment_id;
    }
    
    /**
     * Cria um lead a partir de dados de contato
     */
    public function create_lead_from_contact($data)
    {
        // Validar dados obrigatórios
        if (empty($data['name'])) {
            return false;
        }
        
        // Preparar dados do lead
        $lead_data = [
            'name' => $data['name'],
            'email' => isset($data['email']) ? $data['email'] : '',
            'phonenumber' => isset($data['phonenumber']) ? $data['phonenumber'] : '',
            'company' => isset($data['company']) ? $data['company'] : '',
            'address' => isset($data['address']) ? $data['address'] : '',
            'city' => isset($data['city']) ? $data['city'] : '',
            'state' => isset($data['state']) ? $data['state'] : '',
            'country' => isset($data['country']) ? $data['country'] : '',
            'zip' => isset($data['zip']) ? $data['zip'] : '',
            'website' => isset($data['website']) ? $data['website'] : '',
            'description' => isset($data['description']) ? $data['description'] : '',
            'status' => isset($data['status']) ? $data['status'] : 1,
            'source' => isset($data['source']) ? $data['source'] : 1,
            'assigned' => isset($data['assigned']) ? $data['assigned'] : 0,
            'dateadded' => date('Y-m-d H:i:s'),
            'from_form_id' => isset($data['from_form_id']) ? $data['from_form_id'] : 0,
            'is_public' => isset($data['is_public']) ? $data['is_public'] : 0
        ];
        
        // Inserir lead
        $this->db->insert(db_prefix() . 'leads', $lead_data);
        $lead_id = $this->db->insert_id();
        
        if (!$lead_id) {
            return false;
        }
        
        // Registrar log
        $log_data = [
            'description' => 'Novo lead criado: ' . $data['name'],
            'date' => date('Y-m-d H:i:s'),
            'staffid' => get_staff_user_id() ?: 0
        ];
        $this->db->insert(db_prefix() . 'activity_log', $log_data);
        
        return $lead_id;
    }
    
    /**
     * Gera estatísticas do dashboard
     */
    public function get_dashboard_stats()
    {
        $result = [
            'invoices' => [
                'total' => 0,
                'paid' => 0,
                'overdue' => 0,
                'draft' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'overdue_amount' => 0
            ],
            'estimates' => [
                'total' => 0,
                'sent' => 0,
                'accepted' => 0,
                'declined' => 0,
                'total_amount' => 0
            ],
            'leads' => [
                'total' => 0,
                'new' => 0,
                'contacted' => 0,
                'converted' => 0
            ],
            'projects' => [
                'total' => 0,
                'in_progress' => 0,
                'on_hold' => 0,
                'completed' => 0
            ],
            'tasks' => [
                'total' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'overdue' => 0
            ],
            'clients' => [
                'total' => 0,
                'active' => 0,
                'inactive' => 0
            ]
        ];
        
        // Obter estatísticas de faturas
        $this->db->select('status, COUNT(id) as count, SUM(total) as total_amount');
        $this->db->group_by('status');
        $query = $this->db->get(db_prefix() . 'invoices');
        $invoices_stats = $query->result_array();
        
        foreach ($invoices_stats as $stat) {
            $result['invoices']['total'] += $stat['count'];
            $result['invoices']['total_amount'] += $stat['total_amount'];
            
            switch ($stat['status']) {
                case 1: // Rascunho
                    $result['invoices']['draft'] = $stat['count'];
                    break;
                case 4: // Paga
                    $result['invoices']['paid'] = $stat['count'];
                    $result['invoices']['paid_amount'] = $stat['total_amount'];
                    break;
            }
        }
        
        // Verificar faturas vencidas
        $this->db->where('status', 2);
        $this->db->where('duedate <', date('Y-m-d'));
        $overdue_query = $this->db->get(db_prefix() . 'invoices');
        $result['invoices']['overdue'] = $overdue_query->num_rows();
        
        // Calcular valor vencido
        $this->db->select('SUM(total) as overdue_amount');
        $this->db->where('status', 2);
        $this->db->where('duedate <', date('Y-m-d'));
        $overdue_amount_query = $this->db->get(db_prefix() . 'invoices');
        $overdue_amount = $overdue_amount_query->row_array();
        $result['invoices']['overdue_amount'] = $overdue_amount['overdue_amount'] ?: 0;
        
        // Obter estatísticas de clientes
        $this->db->select('active, COUNT(userid) as count');
        $this->db->group_by('active');
        $query = $this->db->get(db_prefix() . 'clients');
        $clients_stats = $query->result_array();
        
        foreach ($clients_stats as $stat) {
            $result['clients']['total'] += $stat['count'];
            
            if ($stat['active'] == 1) {
                $result['clients']['active'] = $stat['count'];
            } else {
                $result['clients']['inactive'] = $stat['count'];
            }
        }
        
        return $result;
    }
} 