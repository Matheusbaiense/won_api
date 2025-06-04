<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Classe para validação específica por entidade
 * WON API v2.1.2 - Sistema de Validação Profissional
 */
class Won_validator
{
    protected $ci;
    protected $error_handler;
    
    // Configurações de validação por tabela
    protected $validations = [
        'clients' => [
            'required_fields' => ['company'],
            'readonly_fields' => ['userid', 'datecreated'],
            'validation_rules' => [
                'company' => ['required', 'min_length[3]', 'max_length[150]'],
                'vat' => ['validate_cpf_cnpj'],
                'email' => ['valid_email'],
                'phonenumber' => ['validate_phone'],
                'country' => ['exact_length[2]'],
                'zip' => ['validate_zipcode']
            ]
        ],
        'projects' => [
            'required_fields' => ['name', 'clientid'],
            'readonly_fields' => ['id', 'datecreated'],
            'validation_rules' => [
                'name' => ['required', 'min_length[3]', 'max_length[150]'],
                'clientid' => ['required', 'numeric', 'client_exists'],
                'start_date' => ['valid_date'],
                'deadline' => ['valid_date', 'date_after[start_date]'],
                'status' => ['in_list[1,2,3,4,5]']
            ]
        ],
        'tasks' => [
            'required_fields' => ['name'],
            'readonly_fields' => ['id', 'datecreated'],
            'validation_rules' => [
                'name' => ['required', 'min_length[3]', 'max_length[150]'],
                'priority' => ['in_list[1,2,3,4,5]'],
                'startdate' => ['valid_date'],
                'duedate' => ['valid_date', 'date_after[startdate]'],
                'staffid' => ['numeric', 'staff_exists'],
                'status' => ['in_list[1,2,3,4,5]']
            ]
        ],
        'invoices' => [
            'required_fields' => ['clientid'],
            'readonly_fields' => ['id', 'datecreated'],
            'validation_rules' => [
                'clientid' => ['required', 'numeric', 'client_exists'],
                'date' => ['valid_date'],
                'duedate' => ['valid_date', 'date_after[date]'],
                'currency' => ['exact_length[3]'],
                'status' => ['in_list[1,2,3,4,5,6]']
            ]
        ]
    ];
    
    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->database();
        
        // Carregar gerenciador de erros
        require_once(APPPATH . 'modules/won_api/libraries/Won_error_handler.php');
        $this->error_handler = new Won_error_handler();
    }
    
    /**
     * Validação de dados robusta com regras específicas por entidade
     */
    public function validate_data($data, $table_name, $is_update = false)
    {
        // Verificar se existem validações para esta tabela
        if (!isset($this->validations[$table_name])) {
            return $data; // Sem validações específicas, retorna os dados como estão
        }
        
        $validation_config = $this->validations[$table_name];
        $errors = [];
        
        // Verificar campos obrigatórios (apenas para criação)
        if (!$is_update && isset($validation_config['required_fields'])) {
            foreach ($validation_config['required_fields'] as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = $this->error_handler->get_error_message('REQUIRED_FIELD_MISSING', ['field' => $field]);
                }
            }
        }
        
        // Remover campos somente leitura
        if (isset($validation_config['readonly_fields'])) {
            foreach ($validation_config['readonly_fields'] as $field) {
                if (isset($data[$field])) {
                    unset($data[$field]);
                }
            }
        }
        
        // Aplicar regras de validação específicas
        if (isset($validation_config['validation_rules'])) {
            foreach ($validation_config['validation_rules'] as $field => $rules) {
                if (!isset($data[$field]) && !in_array('required', $rules)) {
                    continue; // Campo não obrigatório e não fornecido
                }
                
                $value = $data[$field] ?? '';
                
                foreach ($rules as $rule) {
                    $rule_parts = explode('[', $rule);
                    $rule_name = $rule_parts[0];
                    $rule_param = isset($rule_parts[1]) ? trim($rule_parts[1], '[]') : null;
                    
                    $error = $this->apply_rule($field, $value, $rule_name, $rule_param, $data);
                    if ($error) {
                        $errors[$field] = $error;
                        break; // Parar na primeira validação que falhar
                    }
                }
            }
        }
        
        // Se houver erros, retornar array de erros para identificação
        if (!empty($errors)) {
            return ['validation_errors' => $errors];
        }
        
        return $data;
    }
    
    /**
     * Aplica uma regra de validação específica
     */
    private function apply_rule($field, $value, $rule_name, $rule_param = null, $data = [])
    {
        switch ($rule_name) {
            case 'required':
                if (empty($value)) {
                    return $this->error_handler->get_error_message('REQUIRED_FIELD_MISSING', ['field' => $field]);
                }
                break;
                
            case 'min_length':
                if (strlen($value) < $rule_param) {
                    return "Campo '{$field}' deve ter no mínimo {$rule_param} caracteres";
                }
                break;
                
            case 'max_length':
                if (strlen($value) > $rule_param) {
                    return "Campo '{$field}' deve ter no máximo {$rule_param} caracteres";
                }
                break;
                
            case 'exact_length':
                if (strlen($value) != $rule_param) {
                    return "Campo '{$field}' deve ter exatamente {$rule_param} caracteres";
                }
                break;
                
            case 'valid_email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return $this->error_handler->get_error_message('INVALID_EMAIL');
                }
                break;
                
            case 'numeric':
                if (!is_numeric($value)) {
                    return "Campo '{$field}' deve ser numérico";
                }
                break;
                
            case 'in_list':
                $allowed_values = explode(',', $rule_param);
                if (!in_array($value, $allowed_values)) {
                    return "Campo '{$field}' deve ser um dos valores: {$rule_param}";
                }
                break;
                
            case 'valid_date':
                if (!$this->is_valid_date($value)) {
                    return "Campo '{$field}' deve ser uma data válida (YYYY-MM-DD)";
                }
                break;
                
            case 'date_after':
                $compare_field = $rule_param;
                if (isset($data[$compare_field]) && 
                    $this->is_valid_date($value) && 
                    $this->is_valid_date($data[$compare_field]) &&
                    strtotime($value) <= strtotime($data[$compare_field])) {
                    return "Campo '{$field}' deve ser uma data posterior ao campo '{$compare_field}'";
                }
                break;
                
            case 'validate_cpf_cnpj':
                if (!empty($value) && !$this->validate_cpf_cnpj($value)) {
                    return $this->error_handler->get_error_message('INVALID_CPF_CNPJ');
                }
                break;
                
            case 'validate_phone':
                if (!empty($value) && !$this->validate_phone($value)) {
                    return "Campo '{$field}' não contém um telefone válido";
                }
                break;
                
            case 'client_exists':
                if (!$this->check_client_exists($value)) {
                    return "O cliente informado não existe";
                }
                break;
                
            case 'staff_exists':
                if (!$this->check_staff_exists($value)) {
                    return "O funcionário informado não existe";
                }
                break;
        }
        
        return null; // Sem erro
    }
    
    /**
     * Verifica se uma string é uma data válida
     */
    private function is_valid_date($date)
    {
        if (empty($date)) {
            return false;
        }
        
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Valida CPF ou CNPJ
     */
    private function validate_cpf_cnpj($value)
    {
        // Remove caracteres não numéricos
        $value = preg_replace('/[^0-9]/', '', $value);
        
        // Verifica se é CPF (11 dígitos) ou CNPJ (14 dígitos)
        if (strlen($value) === 11) {
            return $this->validate_cpf($value);
        }
        
        if (strlen($value) === 14) {
            return $this->validate_cnpj($value);
        }
        
        return false;
    }
    
    /**
     * Valida CPF
     */
    private function validate_cpf($cpf)
    {
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Calcula primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        // Calcula segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return $cpf[9] == $digit1 && $cpf[10] == $digit2;
    }
    
    /**
     * Valida CNPJ
     */
    private function validate_cnpj($cnpj)
    {
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        
        // Calcula primeiro dígito verificador
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weights1[$i];
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        // Calcula segundo dígito verificador
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weights2[$i];
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return $cnpj[12] == $digit1 && $cnpj[13] == $digit2;
    }
    
    /**
     * Valida número de telefone
     */
    private function validate_phone($phone)
    {
        // Remove caracteres não numéricos
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Verifica se tem entre 10 e 15 dígitos
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }
    
    /**
     * Verifica se um cliente existe
     */
    private function check_client_exists($client_id)
    {
        return $this->ci->db->where('userid', $client_id)->count_all_results(db_prefix() . 'clients') > 0;
    }
    
    /**
     * Verifica se um funcionário existe
     */
    private function check_staff_exists($staff_id)
    {
        return $this->ci->db->where('staffid', $staff_id)->count_all_results(db_prefix() . 'staff') > 0;
    }
} 