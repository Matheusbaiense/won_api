<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * WON API v2.1.1 - Instalação ULTRA-SIMPLES
 * Apenas o essencial para funcionamento básico
 */

$CI = &get_instance();

try {
    // Token da API
    if (!get_option('won_api_token')) {
        $token = bin2hex(random_bytes(32));
        add_option('won_api_token', $token);
    }
    
    // Configurações básicas
    add_option('won_api_version', '2.1.1', 1);
    add_option('won_api_cors_enabled', '1', 1);
    add_option('won_api_cors_origin', '*', 1); // Configurável para produção
    
    log_message('info', '[WON API] Instalação concluída v2.1.1');
    return true;
    
} catch (Exception $e) {
    log_message('error', '[WON API] Erro na instalação: ' . $e->getMessage());
    return false;
}
?>