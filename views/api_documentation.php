<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Documentação da API WON</h4>
                        <hr class="hr-panel-heading" />
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Autenticação</h5>
                                <p>Todas as requisições devem incluir o header <code>Authorization</code> com o token da API.</p>
                                <pre>Authorization: seu_token_aqui</pre>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Endpoints Disponíveis</h5>
                                
                                <h6>1. Operações CRUD</h6>
                                <p><code>GET /won_api/won/api/{tabela}</code> - Lista todos os registros</p>
                                <p><code>GET /won_api/won/api/{tabela}/{id}</code> - Obtém um registro específico</p>
                                <p><code>POST /won_api/won/api/{tabela}</code> - Cria um novo registro</p>
                                <p><code>PUT /won_api/won/api/{tabela}/{id}</code> - Atualiza um registro existente</p>
                                <p><code>DELETE /won_api/won/api/{tabela}/{id}</code> - Remove um registro</p>
                                
                                <h6>2. Operação JOIN</h6>
                                <p><code>GET /won_api/won/join?vat={cnpj_cpf}</code> - Busca dados relacionados por CNPJ/CPF</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Tabelas Permitidas</h5>
                                <p>As seguintes tabelas estão disponíveis para operações CRUD:</p>
                                <ul>
                                    <li><code>clients</code> - Clientes</li>
                                    <li><code>contacts</code> - Contatos</li>
                                    <li><code>leads</code> - Leads</li>
                                    <li><code>projects</code> - Projetos</li>
                                    <li><code>tasks</code> - Tarefas</li>
                                    <li><code>invoices</code> - Faturas</li>
                                    <li><code>staff</code> - Funcionários</li>
                                    <li><code>tickets</code> - Tickets</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Paginação</h5>
                                <p>Para endpoints de listagem, você pode usar os parâmetros:</p>
                                <ul>
                                    <li><code>page</code>: Número da página (padrão: 1)</li>
                                    <li><code>limit</code>: Registros por página (padrão: 20)</li>
                                </ul>
                                <p>Exemplo: <code>GET /won_api/won/api/clients?page=2&limit=10</code></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Busca</h5>
                                <p>Você pode usar o parâmetro <code>search</code> para buscar em todas as colunas da tabela:</p>
                                <p>Exemplo: <code>GET /won_api/won/api/clients?search=empresa</code></p>
                                
                                <p>Ou usar filtros específicos por campo:</p>
                                <p>Exemplo: <code>GET /won_api/won/api/clients?company=teste&active=1</code></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Formato das Respostas</h5>
                                <h6>Sucesso:</h6>
                                <pre>
{
    "success": true,
    "data": [...],
    "message": "Operação realizada com sucesso",
    "meta": {
        "page": 1,
        "limit": 20,
        "total": 100,
        "total_pages": 5
    }
}
                                </pre>
                                
                                <h6>Erro:</h6>
                                <pre>
{
    "success": false,
    "error": "Mensagem de erro",
    "error_code": "CODIGO_ERRO"
}
                                </pre>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Códigos de Status HTTP</h5>
                                <ul>
                                    <li><code>200</code> - OK: Operação realizada com sucesso</li>
                                    <li><code>400</code> - Bad Request: Dados inválidos ou parâmetros incorretos</li>
                                    <li><code>401</code> - Unauthorized: Token de autenticação inválido ou ausente</li>
                                    <li><code>403</code> - Forbidden: Operação não permitida</li>
                                    <li><code>404</code> - Not Found: Recurso não encontrado</li>
                                    <li><code>405</code> - Method Not Allowed: Método HTTP não suportado</li>
                                    <li><code>422</code> - Unprocessable Entity: Dados com formato inválido</li>
                                    <li><code>429</code> - Too Many Requests: Limite de requisições excedido</li>
                                    <li><code>500</code> - Internal Server Error: Erro interno do servidor</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Códigos de Erro</h5>
                                <ul>
                                    <li><code>AUTH_MISSING</code> - Token de autenticação não fornecido</li>
                                    <li><code>AUTH_INVALID</code> - Token de autenticação inválido</li>
                                    <li><code>RATE_LIMIT_EXCEEDED</code> - Limite de requisições excedido</li>
                                    <li><code>INVALID_TABLE</code> - Tabela inválida ou não permitida</li>
                                    <li><code>INVALID_ID</code> - ID inválido</li>
                                    <li><code>INVALID_DATA</code> - Dados inválidos</li>
                                    <li><code>INVALID_COLUMN</code> - Coluna inválida</li>
                                    <li><code>MISSING_REQUIRED_FIELD</code> - Campo obrigatório não fornecido</li>
                                    <li><code>INVALID_EMAIL_FORMAT</code> - Formato de email inválido</li>
                                    <li><code>INVALID_VAT_FORMAT</code> - Formato de CPF/CNPJ inválido</li>
                                    <li><code>NOT_FOUND</code> - Registro não encontrado</li>
                                    <li><code>ID_REQUIRED</code> - ID obrigatório</li>
                                    <li><code>METHOD_NOT_ALLOWED</code> - Método não suportado</li>
                                    <li><code>SERVER_ERROR</code> - Erro interno do servidor</li>
                                    <li><code>FORBIDDEN</code> - Operação não permitida</li>
                                    <li><code>BAD_REQUEST</code> - Requisição inválida</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Validações</h5>
                                <h6>Campos Obrigatórios por Tabela:</h6>
                                <ul>
                                    <li><strong>clients:</strong> company</li>
                                    <li><strong>contacts:</strong> firstname, email, userid</li>
                                    <li><strong>leads:</strong> name</li>
                                </ul>
                                
                                <h6>Validações de Formato:</h6>
                                <ul>
                                    <li><strong>email:</strong> Deve ser um email válido</li>
                                    <li><strong>vat (CPF/CNPJ):</strong> Deve ter 11 dígitos (CPF) ou 14 dígitos (CNPJ)</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Rate Limiting</h5>
                                <p>A API possui um limite de <strong>100 requisições por hora</strong> por endereço IP e token.</p>
                                <p>Quando o limite é excedido, a API retorna status <code>429</code> com o código de erro <code>RATE_LIMIT_EXCEEDED</code>.</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Exemplos de Uso</h5>
                                
                                <h6>1. Listar clientes com paginação:</h6>
                                <pre>
GET /won_api/won/api/clients?page=1&limit=10
Authorization: seu_token_aqui
                                </pre>
                                
                                <h6>2. Criar um novo cliente:</h6>
                                <pre>
POST /won_api/won/api/clients
Authorization: seu_token_aqui
Content-Type: application/json

{
    "company": "Minha Empresa LTDA",
    "vat": "12345678000123",
    "phonenumber": "(11) 99999-9999",
    "email": "contato@minhaempresa.com",
    "website": "https://minhaempresa.com"
}
                                </pre>
                                
                                <h6>3. Atualizar um cliente:</h6>
                                <pre>
PUT /won_api/won/api/clients/1
Authorization: seu_token_aqui
Content-Type: application/json

{
    "company": "Minha Empresa Atualizada LTDA",
    "phonenumber": "(11) 88888-8888"
}
                                </pre>
                                
                                <h6>4. Buscar dados relacionados por CNPJ:</h6>
                                <pre>
GET /won_api/won/join?vat=12345678000123
Authorization: seu_token_aqui
                                </pre>
                                
                                <h6>5. Excluir um cliente:</h6>
                                <pre>
DELETE /won_api/won/api/clients/1
Authorization: seu_token_aqui
                                </pre>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong>Dica:</strong> Todos os logs das operações da API são registrados no sistema. 
                                    Consulte os logs do Perfex CRM para monitorar o uso e depurar problemas.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?> 