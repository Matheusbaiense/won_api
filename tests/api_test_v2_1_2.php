<?php
/**
 * Script de Teste WON API v2.1.2
 * Testa todas as funcionalidades incluindo novos endpoints especializados
 * 
 * USO: php api_test_v2_1_2.php
 */

// Configurações de teste
$config = [
    'base_url' => 'https://seu-perfex.com/won_api/won',
    'token' => 'SEU_TOKEN_AQUI', // Substitua pelo token real
    'debug' => true
];

class WonApiTester
{
    private $base_url;
    private $token;
    private $debug;
    private $test_results = [];
    
    public function __construct($config)
    {
        $this->base_url = rtrim($config['base_url'], '/');
        $this->token = $config['token'];
        $this->debug = $config['debug'];
    }
    
    /**
     * Executa todos os testes
     */
    public function run_all_tests()
    {
        echo "🚀 Iniciando Testes WON API v2.1.2\n";
        echo str_repeat("=", 50) . "\n\n";
        
        // Testes básicos
        $this->test_status();
        $this->test_authentication();
        
        // Testes de validação e mensagens de erro
        $this->test_validation_system();
        $this->test_error_messages();
        
        // Testes CRUD básicos
        $this->test_crud_operations();
        
        // Testes de endpoints especializados
        $this->test_specialized_endpoints();
        
        // Relatório final
        $this->print_final_report();
    }
    
    /**
     * Teste de status da API
     */
    private function test_status()
    {
        echo "📊 Testando Status da API...\n";
        
        $response = $this->make_request('GET', '/status');
        
        if ($response && isset($response['success']) && $response['success']) {
            $this->log_success("Status da API", "Versão: " . $response['data']['api_version']);
            
            if (isset($response['data']['specialized_endpoints'])) {
                $this->log_success("Endpoints Especializados", "Detectados: " . count($response['data']['specialized_endpoints']));
            }
        } else {
            $this->log_error("Status da API", "API não respondeu corretamente");
        }
        
        echo "\n";
    }
    
    /**
     * Teste de autenticação
     */
    private function test_authentication()
    {
        echo "🔐 Testando Autenticação...\n";
        
        // Teste sem token
        $response = $this->make_request('GET', '/status', [], false);
        if ($response && isset($response['error']['code']) && $response['error']['code'] === 'AUTH_MISSING') {
            $this->log_success("Autenticação", "Erro sem token detectado corretamente");
        } else {
            $this->log_error("Autenticação", "Não detectou ausência de token");
        }
        
        // Teste com token inválido
        $response = $this->make_request('GET', '/status', [], 'token_invalido');
        if ($response && isset($response['error']['code']) && $response['error']['code'] === 'AUTH_INVALID') {
            $this->log_success("Autenticação", "Token inválido detectado corretamente");
        } else {
            $this->log_error("Autenticação", "Não detectou token inválido");
        }
        
        echo "\n";
    }
    
    /**
     * Teste do sistema de validação
     */
    private function test_validation_system()
    {
        echo "✅ Testando Sistema de Validação...\n";
        
        // Teste de criação de cliente com dados inválidos
        $invalid_client = [
            'company' => 'A', // Muito curto
            'email' => 'email_invalido',
            'vat' => '123' // CPF/CNPJ inválido
        ];
        
        $response = $this->make_request('POST', '/api/clients', $invalid_client);
        
        if ($response && isset($response['error']['code']) && $response['error']['code'] === 'VALIDATION_ERROR') {
            $this->log_success("Validação", "Erros de validação detectados corretamente");
            
            if (isset($response['data']['validation_errors'])) {
                $errors_count = count($response['data']['validation_errors']);
                $this->log_success("Validação", "Detectados {$errors_count} erros específicos");
            }
        } else {
            $this->log_error("Validação", "Sistema de validação não funcionou");
        }
        
        echo "\n";
    }
    
    /**
     * Teste de mensagens de erro melhoradas
     */
    private function test_error_messages()
    {
        echo "💬 Testando Mensagens de Erro...\n";
        
        // Teste de tabela não suportada
        $response = $this->make_request('GET', '/api/tabela_inexistente');
        
        if ($response && isset($response['error'])) {
            $error = $response['error'];
            
            if (isset($error['code']) && isset($error['message']) && isset($error['suggestion'])) {
                $this->log_success("Mensagens de Erro", "Estrutura completa: código, mensagem, sugestão");
            } else {
                $this->log_error("Mensagens de Erro", "Estrutura incompleta");
            }
        } else {
            $this->log_error("Mensagens de Erro", "Não retornou erro estruturado");
        }
        
        echo "\n";
    }
    
    /**
     * Teste de operações CRUD básicas
     */
    private function test_crud_operations()
    {
        echo "🔄 Testando Operações CRUD...\n";
        
        // Teste de listagem com paginação
        $response = $this->make_request('GET', '/api/clients?page=1&limit=5');
        
        if ($response && isset($response['success']) && $response['success']) {
            if (isset($response['meta'])) {
                $meta = $response['meta'];
                $this->log_success("CRUD - Listagem", "Paginação funcionando: {$meta['page']}/{$meta['total_pages']}");
            } else {
                $this->log_error("CRUD - Listagem", "Metadados de paginação ausentes");
            }
        } else {
            $this->log_error("CRUD - Listagem", "Listagem falhou");
        }
        
        // Teste de busca
        $response = $this->make_request('GET', '/api/clients?search=empresa');
        
        if ($response && isset($response['success']) && $response['success']) {
            $this->log_success("CRUD - Busca", "Busca funcionando");
        } else {
            $this->log_error("CRUD - Busca", "Busca falhou");
        }
        
        echo "\n";
    }
    
    /**
     * Teste de endpoints especializados
     */
    private function test_specialized_endpoints()
    {
        echo "⚡ Testando Endpoints Especializados...\n";
        
        // Teste de criação de lead
        $lead_data = [
            'name' => 'Lead Teste API',
            'email' => 'lead@teste.com',
            'company' => 'Empresa Teste',
            'description' => 'Lead criado via teste automatizado'
        ];
        
        $response = $this->make_request('POST', '/lead/create', $lead_data);
        
        if ($response && isset($response['success']) && $response['success']) {
            $this->log_success("Lead Create", "Lead criado com sucesso");
            $lead_id = $response['data']['id'] ?? null;
        } else {
            $this->log_error("Lead Create", "Falha ao criar lead");
            $lead_id = null;
        }
        
        // Teste de estatísticas do dashboard
        $response = $this->make_request('GET', '/dashboard/stats');
        
        if ($response && isset($response['success']) && $response['success']) {
            $stats = $response['data'];
            if (isset($stats['invoices']) && isset($stats['clients'])) {
                $this->log_success("Dashboard Stats", "Estatísticas carregadas corretamente");
            } else {
                $this->log_error("Dashboard Stats", "Estrutura de estatísticas incorreta");
            }
        } else {
            $this->log_error("Dashboard Stats", "Falha ao carregar estatísticas");
        }
        
        // Teste de busca por CPF/CNPJ
        $response = $this->make_request('GET', '/join?vat=12345678901');
        
        if ($response) {
            if (isset($response['error']['code']) && $response['error']['code'] === 'NOT_FOUND') {
                $this->log_success("Join CPF/CNPJ", "Busca funcionando (cliente não encontrado como esperado)");
            } elseif (isset($response['success']) && $response['success']) {
                $this->log_success("Join CPF/CNPJ", "Cliente encontrado via CPF/CNPJ");
            }
        } else {
            $this->log_error("Join CPF/CNPJ", "Endpoint não respondeu");
        }
        
        echo "\n";
    }
    
    /**
     * Faz uma requisição HTTP
     */
    private function make_request($method, $endpoint, $data = [], $custom_token = null)
    {
        $url = $this->base_url . $endpoint;
        $token = $custom_token === false ? null : ($custom_token ?: $this->token);
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($token) {
            $headers[] = 'X-API-TOKEN: ' . $token;
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);
        
        if ($method === 'POST' || $method === 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($this->debug) {
            echo "  🌐 {$method} {$endpoint} -> HTTP {$http_code}\n";
        }
        
        if ($error) {
            echo "  ❌ Erro cURL: {$error}\n";
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Registra um sucesso
     */
    private function log_success($test, $message)
    {
        $this->test_results[] = ['type' => 'success', 'test' => $test, 'message' => $message];
        echo "  ✅ {$test}: {$message}\n";
    }
    
    /**
     * Registra um erro
     */
    private function log_error($test, $message)
    {
        $this->test_results[] = ['type' => 'error', 'test' => $test, 'message' => $message];
        echo "  ❌ {$test}: {$message}\n";
    }
    
    /**
     * Imprime relatório final
     */
    private function print_final_report()
    {
        echo str_repeat("=", 50) . "\n";
        echo "📋 RELATÓRIO FINAL DOS TESTES\n";
        echo str_repeat("=", 50) . "\n\n";
        
        $successes = array_filter($this->test_results, function($r) { return $r['type'] === 'success'; });
        $errors = array_filter($this->test_results, function($r) { return $r['type'] === 'error'; });
        
        $success_count = count($successes);
        $error_count = count($errors);
        $total_count = $success_count + $error_count;
        
        echo "📊 ESTATÍSTICAS:\n";
        echo "  Total de testes: {$total_count}\n";
        echo "  ✅ Sucessos: {$success_count}\n";
        echo "  ❌ Erros: {$error_count}\n";
        echo "  📈 Taxa de sucesso: " . round(($success_count / $total_count) * 100, 1) . "%\n\n";
        
        if ($error_count > 0) {
            echo "🚨 ERROS ENCONTRADOS:\n";
            foreach ($errors as $error) {
                echo "  • {$error['test']}: {$error['message']}\n";
            }
            echo "\n";
        }
        
        if ($success_count >= $total_count * 0.8) {
            echo "🎉 PARABÉNS! A WON API v2.1.2 está funcionando bem!\n";
        } else {
            echo "⚠️  ATENÇÃO: Alguns problemas foram encontrados. Verifique os erros acima.\n";
        }
        
        echo "\n" . str_repeat("=", 50) . "\n";
    }
}

// Executar testes se chamado diretamente
if (php_sapi_name() === 'cli') {
    // Verificar se o token foi configurado
    if ($config['token'] === 'SEU_TOKEN_AQUI') {
        echo "❌ ERRO: Configure o token de API no início do arquivo antes de executar os testes.\n";
        echo "Encontre seu token em: Admin > WON API > Configurações\n";
        exit(1);
    }
    
    $tester = new WonApiTester($config);
    $tester->run_all_tests();
}
?> 