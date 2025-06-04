<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * WON API Admin Controller v2.1.1 - Easy Install Compatible
 * Interface administrativa simplificada
 */
class Won_api extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Página de configurações
     */
    public function configuracoes()
    {
        if (!has_permission('modules', '', 'view') && !is_admin()) {
            access_denied('WON API');
        }

        $data['title'] = 'WON API - Configurações';
        $this->load->view('configuracoes', $data);
    }

    /**
     * Regenerar token da API
     */
    public function regenerate_token()
    {
        if (!is_admin()) {
            show_404();
        }

        if ($this->input->is_ajax_request() && $this->input->method() === 'post') {
            try {
                $new_token = bin2hex(random_bytes(32));
                
                $this->db->where('name', 'won_api_token');
                $this->db->update(db_prefix() . 'options', ['value' => $new_token]);
                
                if ($this->db->affected_rows() > 0) {
                    echo json_encode([
                        'success' => true,
                        'new_token' => $new_token,
                        'message' => 'Token regenerado com sucesso'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erro ao atualizar token'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro interno: ' . $e->getMessage()
                ]);
            }
        } else {
            show_404();
        }
    }

    /**
     * Documentação da API
     */
    public function documentation()
    {
        if (!has_permission('modules', '', 'view') && !is_admin()) {
            access_denied('WON API');
        }

        $data['title'] = 'WON API - Documentação';
        $data['token'] = get_option('won_api_token');
        $data['base_url'] = base_url('won_api/won/');
        $data['allowed_tables'] = [
            'clients', 'projects', 'tasks', 'invoices', 
            'estimates', 'leads', 'staff'
        ];
        
        $this->load->view('api_documentation', $data);
    }

    /**
     * Logs da API (simplificado)
     */
    public function logs()
    {
        if (!has_permission('modules', '', 'view') && !is_admin()) {
            access_denied('WON API');
        }

        $data['title'] = 'WON API - Logs';
        $data['logs'] = $this->get_recent_logs();
        $this->load->view('logs', $data);
    }

    /**
     * Obter logs recentes
     */
    private function get_recent_logs()
    {
        // Logs básicos do CodeIgniter
        $log_file = APPPATH . 'logs/log-' . date('Y-m-d') . '.php';
        $logs = [];

        if (file_exists($log_file)) {
            $content = file_get_contents($log_file);
            $lines = explode("\n", $content);
            
            foreach (array_reverse(array_slice($lines, -50)) as $line) {
                if (strpos($line, '[WON API]') !== false) {
                    $logs[] = [
                        'timestamp' => substr($line, 0, 19),
                        'level' => $this->extract_log_level($line),
                        'message' => trim(substr($line, strpos($line, '[WON API]')))
                    ];
                }
            }
        }

        return array_slice($logs, 0, 20); // Últimos 20 logs
    }

    /**
     * Extrair nível do log
     */
    private function extract_log_level($line)
    {
        if (strpos($line, 'ERROR') !== false) return 'error';
        if (strpos($line, 'WARNING') !== false) return 'warning';
        if (strpos($line, 'INFO') !== false) return 'info';
        return 'debug';
    }
}