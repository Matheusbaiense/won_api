<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Instalação ULTRA-MÍNIMA para Easy Install do Perfex CRM
 * WON API v2.1.1 - Compatível com Easy Install
 */

$CI = &get_instance();

try {
    // APENAS token básico - NADA MAIS
    if (!get_option('won_api_token')) {
        $token = bin2hex(random_bytes(32));
        add_option('won_api_token', $token);
        log_message('info', '[WON API] Token criado via Easy Install');
    }
    
    // Configurações básicas mínimas
    if (!get_option('won_api_rate_limit')) {
        add_option('won_api_rate_limit', '100');
    }
    
    if (!get_option('won_api_version')) {
        add_option('won_api_version', '2.1.1');
    }
    
    if (!get_option('won_api_cors_enabled')) {
        add_option('won_api_cors_enabled', '1');
    }
    
    // Sinalizar sucesso
    log_message('info', '[WON API] Easy Install concluído com sucesso');
    return true;
    
} catch (Exception $e) {
    log_message('error', '[WON API] Erro Easy Install: ' . $e->getMessage());
    return false;
}
?>