<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Classe para gerenciar mensagens de erro e validação
 * WON API v2.1.2 - Sistema de Erros Profissional
 */
class Won_error_handler
{
    protected $ci;
    protected $lang;
    
    // Códigos de erro padronizados
    protected $error_codes = [
        // Erros de autenticação (1xx)
        'AUTH_MISSING' => 101,
        'AUTH_INVALID' => 102,
        'AUTH_EXPIRED' => 103,
        'AUTH_NOT_CONFIGURED' => 104,
        'BRUTE_FORCE_PROTECTION' => 105,
        
        // Erros de validação (2xx)
        'VALIDATION_ERROR' => 201,
        'REQUIRED_FIELD_MISSING' => 202,
        'INVALID_FORMAT' => 203,
        'INVALID_VALUE' => 204,
        'INVALID_DATE' => 205,
        'INVALID_EMAIL' => 206,
        'INVALID_PHONE' => 207,
        'INVALID_CPF_CNPJ' => 208,
        'FIELD_TOO_SHORT' => 209,
        'FIELD_TOO_LONG' => 210,
        'DUPLICATE_ENTRY' => 211,
        
        // Erros de recursos (3xx)
        'NOT_FOUND' => 301,
        'TABLE_MISSING' => 302,
        'TABLE_NOT_SUPPORTED' => 303,
        'INVALID_ID' => 304,
        'MISSING_VAT' => 305,
        
        // Erros de requisição (4xx)
        'INVALID_DATA' => 401,
        'INVALID_CONTENT_TYPE' => 402,
        'INVALID_METHOD' => 403,
        'MISSING_PARAMETER' => 404,
        'INVALID_PARAMETER' => 405,
        
        // Erros de banco de dados (5xx)
        'DB_ERROR' => 501,
        'DB_CONNECTION_ERROR' => 502,
        'DB_QUERY_ERROR' => 503,
        'NO_CHANGES' => 504,
        
        // Erros de sistema (9xx)
        'SYSTEM_ERROR' => 901,
        'MAINTENANCE_MODE' => 902,
        'RATE_LIMIT_EXCEEDED' => 903
    ];
    
    // Mensagens em português
    protected $messages_pt = [
        'AUTH_MISSING' => 'Token de API obrigatório no header X-API-TOKEN.',
        'AUTH_INVALID' => 'Token de API inválido.',
        'AUTH_NOT_CONFIGURED' => 'Token da API não configurado no sistema.',
        'VALIDATION_ERROR' => 'Erro de validação nos dados fornecidos.',
        'REQUIRED_FIELD_MISSING' => 'Campo obrigatório não fornecido: {field}.',
        'INVALID_EMAIL' => 'Endereço de e-mail inválido.',
        'INVALID_CPF_CNPJ' => 'CPF ou CNPJ inválido.',
        'NOT_FOUND' => 'Registro não encontrado.',
        'TABLE_NOT_SUPPORTED' => 'Tabela não suportada.',
        'INVALID_DATA' => 'Dados inválidos ou mal formatados.',
        'DB_ERROR' => 'Erro no banco de dados.',
        'RATE_LIMIT_EXCEEDED' => 'Limite de requisições excedido.',
    ];
    
    // Sugestões em português
    protected $suggestions_pt = [
        'AUTH_MISSING' => 'Adicione o header X-API-TOKEN com o token de API válido.',
        'AUTH_INVALID' => 'Verifique se o token está correto e tente novamente.',
        'AUTH_NOT_CONFIGURED' => 'Configure o token da API nas configurações do sistema.',
        'VALIDATION_ERROR' => 'Verifique os dados fornecidos e corrija os campos com erro.',
        'REQUIRED_FIELD_MISSING' => 'Adicione o campo {field} à sua requisição.',
        'INVALID_EMAIL' => 'Forneça um endereço de e-mail válido.',
        'INVALID_CPF_CNPJ' => 'Forneça um CPF ou CNPJ válido, apenas números.',
        'NOT_FOUND' => 'Verifique o ID fornecido e tente novamente.',
        'TABLE_NOT_SUPPORTED' => 'Consulte a documentação para ver as tabelas suportadas.',
        'INVALID_DATA' => 'Verifique o formato dos dados enviados (JSON válido).',
        'DB_ERROR' => 'Tente novamente mais tarde. Se o problema persistir, entre em contato com o suporte.',
        'RATE_LIMIT_EXCEEDED' => 'Aguarde alguns minutos antes de fazer novas requisições.',
    ];
    
    public function __construct()
    {
        $this->ci =& get_instance();
        $this->lang = 'pt'; // Idioma padrão português
    }
    
    /**
     * Obtém o código numérico para um código de erro
     */
    public function get_error_code($error_code)
    {
        return isset($this->error_codes[$error_code]) ? $this->error_codes[$error_code] : 999;
    }
    
    /**
     * Obtém a mensagem de erro traduzida
     */
    public function get_error_message($error_code, $params = [])
    {
        $message = isset($this->messages_pt[$error_code]) ? $this->messages_pt[$error_code] : 'Erro desconhecido.';
        
        // Substituir parâmetros na mensagem
        foreach ($params as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        
        return $message;
    }
    
    /**
     * Obtém a sugestão de correção traduzida
     */
    public function get_error_suggestion($error_code, $params = [])
    {
        $suggestion = isset($this->suggestions_pt[$error_code]) ? $this->suggestions_pt[$error_code] : '';
        
        // Substituir parâmetros na sugestão
        foreach ($params as $key => $value) {
            $suggestion = str_replace('{' . $key . '}', $value, $suggestion);
        }
        
        return $suggestion;
    }
    
    /**
     * Gera uma resposta de erro padronizada
     */
    public function generate_error_response($error_code, $params = [], $status = 400, $additional_data = [])
    {
        $numeric_code = $this->get_error_code($error_code);
        $message = $this->get_error_message($error_code, $params);
        $suggestion = $this->get_error_suggestion($error_code, $params);
        
        $response = [
            'success' => false,
            'error' => [
                'code' => $error_code,
                'numeric_code' => $numeric_code,
                'message' => $message,
                'suggestion' => $suggestion
            ],
            'timestamp' => time(),
            'version' => '2.1.2'
        ];
        
        // Adicionar dados adicionais se fornecidos
        if (!empty($additional_data)) {
            $response['data'] = $additional_data;
        }
        
        return $response;
    }
    
    /**
     * Gera uma resposta de erro de validação
     */
    public function generate_validation_error_response($validation_errors)
    {
        $errors = [];
        
        foreach ($validation_errors as $field => $error) {
            $errors[] = [
                'field' => $field,
                'message' => $error,
                'suggestion' => $this->get_error_suggestion('VALIDATION_ERROR', ['field' => $field])
            ];
        }
        
        return $this->generate_error_response('VALIDATION_ERROR', [], 400, ['validation_errors' => $errors]);
    }
} 