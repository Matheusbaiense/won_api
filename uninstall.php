<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

$CI->db->where('name', 'won_api_token');
if ($CI->db->delete(db_prefix() . 'options')) {
    log_message('info', '[Won API] Configuração de token removida com sucesso.');
} else {
    log_message('error', '[Won API] Erro ao remover configuração de token: ' . $CI->db->error()['message']);
}