<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

if (!$CI->db->conn_id) {
    log_message('error', '[Won API] Falha ao conectar ao banco de dados.');
    show_error('Erro ao conectar ao banco de dados. Verifique as configurações do banco.');
}

$CI->db->where('name', 'won_api_token');
$existing = $CI->db->get(db_prefix() . 'options')->row();

if (!$existing) {
    $data = [
        'name'  => 'won_api_token',
        'value' => '',
    ];
    if ($CI->db->insert(db_prefix() . 'options', $data)) {
        log_message('info', '[Won API] Configuração de token criada com sucesso.');
    } else {
        log_message('error', '[Won API] Erro ao criar configuração de token: ' . $CI->db->error()['message']);
    }
} else {
    log_message('info', '[Won API] Configuração de token já existe.');
}