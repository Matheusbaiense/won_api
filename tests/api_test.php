<?php
/**
 * Script para testar os endpoints da API WON
 * 
 * Este script testa todos os endpoints da API para garantir que estão funcionando corretamente.
 * Configure as variáveis $base_url e $token antes de executar.
 */

// Configurações da API
$base_url = 'https://seu-perfex.com'; // Altere para o URL do seu Perfex CRM
$token = 'seu_token_aqui'; // Altere para o seu token da API

/**
 * Função para fazer requisições à API
 * 
 * @param string $endpoint O endpoint da API
 * @param string $method O método HTTP (GET, POST, PUT, DELETE)
 * @param array|null $data Os dados para enviar (para POST e PUT)
 * @return array Array com status e resposta
 */
function api_request($endpoint, $method = 'GET', $data = null) {
    global $base_url, $token;
    
    $ch = curl_init($base_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $token,
        'Content-Type: application/json'
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'status' => 0,
            'response' => ['error' => 'Erro cURL: ' . $error],
            'curl_error' => true
        ];
    }
    
    return [
        'status' => $status,
        'response' => json_decode($response, true),
        'curl_error' => false
    ];
}

/**
 * Função para imprimir resultados do teste
 * 
 * @param string $name Nome do teste
 * @param array $result Resultado da requisição
 */
function print_test_result($name, $result) {
    echo str_repeat('=', 60) . "\n";
    echo "TESTE: {$name}\n";
    echo str_repeat('=', 60) . "\n";
    
    if ($result['curl_error']) {
        echo "❌ ERRO DE CONEXÃO\n";
        echo "Detalhes: " . $result['response']['error'] . "\n\n";
        return;
    }
    
    echo "Status HTTP: {$result['status']}\n";
    
    // Determinar se o teste passou ou falhou
    $success = false;
    if (isset($result['response']['success'])) {
        $success = $result['response']['success'];
    } elseif ($result['status'] >= 200 && $result['status'] < 300) {
        $success = true;
    }
    
    echo ($success ? "✅ SUCESSO" : "❌ FALHA") . "\n";
    echo "Resposta: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
}

// Definir testes
$tests = [
    // Teste 1: Listar clientes
    [
        'name' => 'Listar clientes',
        'endpoint' => '/won_api/won/api/clients',
        'method' => 'GET'
    ],
    
    // Teste 2: Listar clientes com paginação
    [
        'name' => 'Listar clientes com paginação',
        'endpoint' => '/won_api/won/api/clients?page=1&limit=5',
        'method' => 'GET'
    ],
    
    // Teste 3: Buscar clientes
    [
        'name' => 'Buscar clientes',
        'endpoint' => '/won_api/won/api/clients?search=empresa',
        'method' => 'GET'
    ],
    
    // Teste 4: Obter cliente específico (assumindo que existe o ID 1)
    [
        'name' => 'Obter cliente por ID',
        'endpoint' => '/won_api/won/api/clients/1',
        'method' => 'GET'
    ],
    
    // Teste 5: Criar cliente (dados de teste)
    [
        'name' => 'Criar cliente',
        'endpoint' => '/won_api/won/api/clients',
        'method' => 'POST',
        'data' => [
            'company' => 'Empresa Teste API',
            'vat' => '12345678901',
            'phonenumber' => '1234567890',
            'email' => 'teste@empresaapi.com',
            'website' => 'https://empresaapi.com'
        ]
    ],
    
    // Teste 6: Listar contatos
    [
        'name' => 'Listar contatos',
        'endpoint' => '/won_api/won/api/contacts',
        'method' => 'GET'
    ],
    
    // Teste 7: Listar leads
    [
        'name' => 'Listar leads',
        'endpoint' => '/won_api/won/api/leads',
        'method' => 'GET'
    ],
    
    // Teste 8: Listar faturas
    [
        'name' => 'Listar faturas',
        'endpoint' => '/won_api/won/api/invoices',
        'method' => 'GET'
    ],
    
    // Teste 9: Testar JOIN sem parâmetros
    [
        'name' => 'Testar JOIN (todos os dados)',
        'endpoint' => '/won_api/won/join',
        'method' => 'GET'
    ],
    
    // Teste 10: Testar JOIN com CNPJ específico
    [
        'name' => 'Testar JOIN por CNPJ',
        'endpoint' => '/won_api/won/join?vat=12345678901',
        'method' => 'GET'
    ],
    
    // Teste 11: Testar erro de autenticação (sem token)
    [
        'name' => 'Teste de erro de autenticação',
        'endpoint' => '/won_api/won/api/clients',
        'method' => 'GET',
        'no_auth' => true
    ],
    
    // Teste 12: Testar tabela inválida
    [
        'name' => 'Teste de tabela inválida',
        'endpoint' => '/won_api/won/api/tabela_inexistente',
        'method' => 'GET'
    ],
    
    // Teste 13: Testar ID inválido
    [
        'name' => 'Teste de ID inválido',
        'endpoint' => '/won_api/won/api/clients/abc',
        'method' => 'GET'
    ],
    
    // Teste 14: Testar POST com dados inválidos
    [
        'name' => 'Teste POST com dados inválidos',
        'endpoint' => '/won_api/won/api/clients',
        'method' => 'POST',
        'data' => [
            'email' => 'email_invalido' // Email inválido
        ]
    ],
    
    // Teste 15: Testar POST sem campo obrigatório
    [
        'name' => 'Teste POST sem campo obrigatório',
        'endpoint' => '/won_api/won/api/clients',
        'method' => 'POST',
        'data' => [
            'email' => 'teste@teste.com'
            // Faltando o campo 'company' que é obrigatório
        ]
    ]
];

// Função especial para teste sem autenticação
function api_request_no_auth($endpoint, $method = 'GET', $data = null) {
    global $base_url;
    
    $ch = curl_init($base_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
        // Sem Authorization header
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'status' => 0,
            'response' => ['error' => 'Erro cURL: ' . $error],
            'curl_error' => true
        ];
    }
    
    return [
        'status' => $status,
        'response' => json_decode($response, true),
        'curl_error' => false
    ];
}

// Executar testes
echo "🚀 INICIANDO TESTES DA API WON\n";
echo "Base URL: {$base_url}\n";
echo "Token: " . substr($token, 0, 10) . "...\n\n";

$total_tests = count($tests);
$passed_tests = 0;
$failed_tests = 0;

foreach ($tests as $index => $test) {
    echo "Executando teste " . ($index + 1) . " de {$total_tests}...\n";
    
    // Usar função especial para teste sem autenticação
    if (isset($test['no_auth']) && $test['no_auth']) {
        $result = api_request_no_auth(
            $test['endpoint'], 
            $test['method'], 
            isset($test['data']) ? $test['data'] : null
        );
    } else {
        $result = api_request(
            $test['endpoint'], 
            $test['method'], 
            isset($test['data']) ? $test['data'] : null
        );
    }
    
    print_test_result($test['name'], $result);
    
    // Contar sucessos e falhas
    if ($result['curl_error']) {
        $failed_tests++;
    } else {
        $success = false;
        if (isset($result['response']['success'])) {
            $success = $result['response']['success'];
        } elseif ($result['status'] >= 200 && $result['status'] < 300) {
            $success = true;
        }
        
        if ($success) {
            $passed_tests++;
        } else {
            $failed_tests++;
        }
    }
    
    // Pequena pausa entre os testes
    sleep(1);
}

// Resumo final
echo str_repeat('=', 60) . "\n";
echo "📊 RESUMO DOS TESTES\n";
echo str_repeat('=', 60) . "\n";
echo "Total de testes: {$total_tests}\n";
echo "✅ Sucessos: {$passed_tests}\n";
echo "❌ Falhas: {$failed_tests}\n";
echo "📈 Taxa de sucesso: " . round(($passed_tests / $total_tests) * 100, 2) . "%\n";

if ($failed_tests === 0) {
    echo "\n🎉 TODOS OS TESTES PASSARAM! A API está funcionando corretamente.\n";
} else {
    echo "\n⚠️  Alguns testes falharam. Verifique os logs acima para mais detalhes.\n";
}

echo "\n📋 INSTRUÇÕES:\n";
echo "1. Configure corretamente \$base_url e \$token no início do arquivo\n";
echo "2. Certifique-se de que o Perfex CRM está acessível\n";
echo "3. Verifique se o módulo WON API está ativo\n";
echo "4. Para testes de criação, use dados reais conforme sua instalação\n";
echo "5. Alguns testes podem falhar se não houver dados suficientes no sistema\n";
?> 