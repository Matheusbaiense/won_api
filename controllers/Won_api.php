<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Won_api extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('won_api_model');
    }

    public function configuracoes()
    {
        $data['configs'] = $this->won_api_model->get_configs();
        $data['title'] = 'Won API - Configurações';
        $this->load->view('won_api/configuracoes', $data);
    }

    public function add()
    {
        if ($this->input->post()) {
            $data = [
                'token' => $this->input->post('token')
            ];
            if ($this->won_api_model->add_config($data)) {
                set_alert('success', 'Token adicionado com sucesso!');
            } else {
                set_alert('danger', 'Erro ao adicionar o token.');
            }
            redirect(admin_url('won_api/configuracoes'));
        }
        $data['title'] = 'Adicionar Token da API';
        $this->load->view('won_api/configuracoes_form', $data);
    }

    public function edit($id)
    {
        if ($this->input->post()) {
            $data = [
                'token' => $this->input->post('token')
            ];
            $this->won_api_model->update_config($id, $data);
            set_alert('success', 'Token atualizado com sucesso!');
            redirect(admin_url('won_api/configuracoes'));
        }
        $data['config'] = $this->won_api_model->get_config($id);
        $data['title'] = 'Editar Token da API';
        $this->load->view('won_api/configuracoes_form', $data);
    }

    public function delete($id)
    {
        $this->won_api_model->delete_config($id);
        set_alert('success', 'Token excluído com sucesso!');
        redirect(admin_url('won_api/configuracoes'));
    }

    public function instructions()
    {
        $data['title'] = 'Instruções da API - Won API';
        $this->load->view('won_api/instructions', $data);
    }

    /**
     * Página de documentação completa da API
     */
    public function documentation()
    {
        $data['title'] = 'Documentação da API WON';
        $this->load->view('won_api/api_documentation', $data);
    }

    public function tables()
    {
        try {
            $query = $this->db->query('SHOW TABLES');
            if ($query === FALSE) {
                log_message('error', 'Erro na consulta de tabelas: ' . $this->db->error()['message']);
                show_error('Erro ao listar tabelas. Verifique os logs para mais detalhes.');
            }

            $tables = $query->result_array();
            $data['tables'] = array_map(function($row) {
                return ['TABLE_NAME' => $row['Tables_in_' . $this->db->database]];
            }, $tables);
        } catch (Exception $e) {
            log_message('error', 'Exceção ao listar tabelas: ' . $e->getMessage());
            show_error('Ocorreu um erro inesperado. Verifique os logs para mais detalhes.');
        }

        $data['title'] = 'Listar Tabelas - Won API';
        $this->load->view('won_api/tables', $data);
    }
}