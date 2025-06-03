<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controlador Administrativo WON API - Versão Otimizada
 * Gerencia configurações e documentação do módulo
 */
class Won_api extends AdminController
{
    private $configs;
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('won_api_model');
        $this->_load_configs();
    }
    
    /**
     * Carrega configurações uma única vez
     */
    private function _load_configs()
    {
        $this->configs = [
            'token' => get_option('won_api_token'),
            'rate_limit' => get_option('won_api_rate_limit') ?: '100',
            'log_level' => get_option('won_api_log_level') ?: 'basic'
        ];
    }
    
    /**
     * Página principal de configurações
     */
    public function configuracoes()
    {
        $data = [
            'title' => 'WON API - Configurações',
            'configs' => [
                'id' => 1,
                'token' => $this->configs['token'] ?: 'Token não configurado'
            ]
        ];
        $this->load->view('won_api/configuracoes', $data);
    }
    
    /**
     * Regenerar token da API
     */
    public function regenerate_token()
    {
        if ($this->input->post()) {
            $new_token = bin2hex(random_bytes(32));
            update_option('won_api_token', $new_token);
            set_alert('success', 'Token regenerado com sucesso!');
            log_message('info', '[Won API] Token regenerado por usuário: ' . get_staff_user_id());
        }
        redirect(admin_url('won_api/configuracoes'));
    }
    
    /**
     * Documentação da API
     */
    public function documentation()
    {
        $data = [
            'title' => 'Documentação WON API',
            'token' => $this->configs['token'],
            'base_url' => site_url('won_api/won/api/'),
            'allowed_tables' => explode(',', get_option('won_api_whitelist_tables') ?: 'clients,projects,tasks')
        ];
        $this->load->view('won_api/api_documentation', $data);
    }
    
    /**
     * Instruções detalhadas
     */
    public function instructions()
    {
        $data = [
            'title' => 'Instruções WON API',
            'version' => '2.1.0'
        ];
        $this->load->view('won_api/instructions', $data);
    }
    
    /**
     * Executar diagnóstico do sistema
     */
    public function diagnostic()
    {
        if (file_exists(FCPATH . 'modules/won_api/diagnostic.php')) {
            include FCPATH . 'modules/won_api/diagnostic.php';
            $diagnostic = new Won_Api_Diagnostic();
            $results = $diagnostic->run_all_checks();
            
            $data = [
                'title' => 'Diagnóstico WON API',
                'results' => $results,
                'report_html' => $diagnostic->generate_html_report($results)
            ];
            $this->load->view('won_api/diagnostic_results', $data);
        } else {
            set_alert('danger', 'Script de diagnóstico não encontrado.');
            redirect(admin_url('won_api/configuracoes'));
        }
    }
    
    /**
     * Exibir logs da API
     */
    public function logs()
    {
        $this->load->library('pagination');
        
        $config = [
            'base_url' => admin_url('won_api/logs'),
            'total_rows' => $this->db->count_all(db_prefix() . 'won_api_logs'),
            'per_page' => 50,
            'uri_segment' => 4
        ];
        
        $this->pagination->initialize($config);
        
        $offset = $this->uri->segment(4) ?: 0;
        $logs = $this->db
            ->order_by('date', 'DESC')
            ->limit($config['per_page'], $offset)
            ->get(db_prefix() . 'won_api_logs')
            ->result_array();
        
        $data = [
            'title' => 'Logs WON API',
            'logs' => $logs,
            'pagination' => $this->pagination->create_links()
        ];
        $this->load->view('won_api/logs', $data);
    }
}