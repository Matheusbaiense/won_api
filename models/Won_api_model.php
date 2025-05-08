<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Won_api_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtém todas as configurações de token da API
     */
    public function get_configs()
    {
        $this->db->where('name', 'won_api_token');
        $result = $this->db->get(db_prefix() . 'options')->row();
        if ($result) {
            return [
                'id' => 1, // ID fixo para simplificar, já que há apenas 1 token
                'token' => $result->value
            ];
        }
        return [];
    }

    /**
     * Adiciona ou atualiza o token da API
     */
    public function add_config($data)
    {
        $this->db->where('name', 'won_api_token');
        $existing = $this->db->get(db_prefix() . 'options')->row();
        
        if ($existing) {
            // Atualiza o token existente
            $this->db->where('name', 'won_api_token');
            return $this->db->update(db_prefix() . 'options', ['value' => $data['token']]);
        } else {
            // Insere um novo token
            return $this->db->insert(db_prefix() . 'options', [
                'name' => 'won_api_token',
                'value' => $data['token']
            ]);
        }
    }

    /**
     * Obtém uma configuração específica pelo ID
     * (Neste caso, há apenas 1 token, mas mantemos a estrutura para consistência)
     */
    public function get_config($id)
    {
        $this->db->where('name', 'won_api_token');
        $result = $this->db->get(db_prefix() . 'options')->row();
        if ($result) {
            return [
                'id' => 1, // ID fixo
                'token' => $result->value
            ];
        }
        return null;
    }

    /**
     * Atualiza o token da API
     */
    public function update_config($id, $data)
    {
        $this->db->where('name', 'won_api_token');
        return $this->db->update(db_prefix() . 'options', ['value' => $data['token']]);
    }

    /**
     * Exclui o token da API
     */
    public function delete_config($id)
    {
        $this->db->where('name', 'won_api_token');
        return $this->db->delete(db_prefix() . 'options');
    }
}