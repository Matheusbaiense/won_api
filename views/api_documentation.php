<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Documentação da API WON v2.1.0</h4>
                        <hr class="hr-panel-heading" />
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>🔐 Autenticação</h5>
                                <p>Inclua o header Authorization:</p>
                                <pre>Authorization: <?php echo !empty($token) ? $token : 'seu_token_aqui'; ?></pre>
                            </div>
                            <div class="col-md-6">
                                <h5>🌐 Base URL</h5>
                                <p>Todas as requisições devem usar:</p>
                                <pre><?php echo !empty($base_url) ? $base_url : site_url('won_api/won/api/'); ?></pre>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>📋 Endpoints Disponíveis</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Método</th>
                                            <th>Endpoint</th>
                                            <th>Descrição</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="label label-info">GET</span></td>
                                            <td><code>/api/{tabela}</code></td>
                                            <td>Listar todos os registros</td>
                                        </tr>
                                        <tr>
                                            <td><span class="label label-info">GET</span></td>
                                            <td><code>/api/{tabela}/{id}</code></td>
                                            <td>Obter registro específico</td>
                                        </tr>
                                        <tr>
                                            <td><span class="label label-success">POST</span></td>
                                            <td><code>/api/{tabela}</code></td>
                                            <td>Criar novo registro</td>
                                        </tr>
                                        <tr>
                                            <td><span class="label label-warning">PUT</span></td>
                                            <td><code>/api/{tabela}/{id}</code></td>
                                            <td>Atualizar registro</td>
                                        </tr>
                                        <tr>
                                            <td><span class="label label-danger">DELETE</span></td>
                                            <td><code>/api/{tabela}/{id}</code></td>
                                            <td>Remover registro</td>
                                        </tr>
                                        <tr>
                                            <td><span class="label label-primary">GET</span></td>
                                            <td><code>/join?vat={cnpj_cpf}</code></td>
                                            <td>Busca por CNPJ/CPF</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>📊 Tabelas Permitidas</h5>
                                <?php if (!empty($allowed_tables)): ?>
                                <ul>
                                    <?php foreach ($allowed_tables as $table): ?>
                                    <li><code><?php echo $table; ?></code></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php else: ?>
                                <ul>
                                    <li><code>clients</code> - Clientes</li>
                                    <li><code>projects</code> - Projetos</li>
                                    <li><code>tasks</code> - Tarefas</li>
                                    <li><code>staff</code> - Funcionários</li>
                                    <li><code>leads</code> - Leads</li>
                                    <li><code>invoices</code> - Faturas</li>
                                </ul>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h5>📄 Parâmetros de Consulta</h5>
                                <ul>
                                    <li><code>page</code> - Número da página (padrão: 1)</li>
                                    <li><code>limit</code> - Registros por página (máx: 100)</li>
                                    <li><code>search</code> - Busca em todas as colunas</li>
                                    <li><code>{campo}=valor</code> - Filtro específico</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>✅ Resposta de Sucesso</h5>
                                <pre>
{
  "success": true,
  "data": [...],
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 100
  }
}</pre>
                            </div>
                            <div class="col-md-6">
                                <h5>❌ Resposta de Erro</h5>
                                <pre>
{
  "success": false,
  "error": "Mensagem de erro",
  "error_code": "CODIGO_ERRO"
}</pre>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>🎯 Exemplos Práticos</h5>
                                
                                <h6>1. Listar clientes com paginação:</h6>
                                <pre>GET <?php echo !empty($base_url) ? $base_url : '/won_api/won/api/'; ?>clients?page=1&limit=10</pre>
                                
                                <h6>2. Criar cliente:</h6>
                                <pre>POST <?php echo !empty($base_url) ? $base_url : '/won_api/won/api/'; ?>clients
Content-Type: application/json

{
  "company": "Minha Empresa LTDA",
  "email": "contato@empresa.com"
}</pre>
                                
                                <h6>3. Buscar por CNPJ:</h6>
                                <pre>GET <?php echo !empty($base_url) ? str_replace('api/', '', $base_url) : '/won_api/won/'; ?>join?vat=12345678000123</pre>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h5>📋 Status HTTP Principais:</h5>
                                    <strong>200</strong> - Sucesso | <strong>400</strong> - Dados inválidos | <strong>401</strong> - Token inválido | 
                                    <strong>404</strong> - Não encontrado | <strong>429</strong> - Rate limit excedido
                                </div>
                                
                                <div class="alert alert-warning">
                                    <h5>⚡ Rate Limiting:</h5>
                                    Limite de <strong>100 requisições por hora</strong> por IP/token
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