<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controlador Administrativo WON API v2.1.0 - Ultra Simplificado
 */
class Won_api extends AdminController
{
    private $configs;
    
    public function __construct()
    {
        parent::__construct();
        $this->_load_configs();
    }
    
    /**
     * Carrega configurações uma única vez
     */
    private function _load_configs()
    {
        $this->configs = [
            'token' => get_option('won_api_token'),
            'rate_limit' => get_option('won_api_rate_limit') ?: '100'
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