<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); 
$http_host = 'https://' . $_SERVER['HTTP_HOST'].'/won_api';
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="no-margin"><?php echo $title; ?></h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?php echo admin_url('won_api/documentation'); ?>" class="btn btn-info">
                                    <i class="fa fa-book"></i> Documentação Completa
                                </a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-success">
                                    <strong>🎉 Versão 2.0 Atualizada!</strong> Esta API foi completamente renovada com melhorias de segurança, 
                                    paginação, validação de dados e compatibilidade total com padrões REST.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>✨ Novos Recursos da Versão 2.0</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul>
                                            <li>🔒 <strong>Segurança Avançada:</strong> Rate limiting e proteção SQL injection</li>
                                            <li>📄 <strong>Paginação Automática:</strong> Parâmetros page e limit</li>
                                            <li>✅ <strong>Validação de Dados:</strong> Campos obrigatórios e formatos</li>
                                            <li>📊 <strong>Respostas Padronizadas:</strong> Formato JSON consistente</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul>
                                            <li>🔍 <strong>Logs Detalhados:</strong> Monitoramento completo</li>
                                            <li>⚡ <strong>Performance:</strong> Consultas otimizadas</li>
                                            <li>🌐 <strong>Compatibilidade REST:</strong> Padrões modernos</li>
                                            <li>🧪 <strong>Testes Automatizados:</strong> Script de validação</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>🔑 Autenticação</h5>
                                <p>Todas as requisições devem incluir o token de autenticação no header <code>Authorization</code>:</p>
                                <pre><code>Authorization: seu_token_aqui</code></pre>
                                <p>Configure seu token em <a href="<?php echo admin_url('won_api/configuracoes'); ?>"><strong>Configurações</strong></a>.</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>🚀 Endpoints da API</h5>
                                
                                <h6>📋 Operações CRUD</h6>
                                <table class="table table-striped">
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
                                            <td><code>/won/api/{tabela}</code></td>
                                            <td>Lista registros com paginação</td>
                                        </tr>
                                        <tr>
                                            <td><span class="label label-info">GET</span></td>
                                            <td><code>/won/api/{tabela}/{id}</code></td>
                                            <td>Obtém um registro específico</td>
                                        </tr>
                                        <tr>
                                            <td><span class="label label-success">POST</span></td>
                                            <td><code>/won/api/{tabela}</code></td>
                                            <td>Cria novo registro</td>
                                        </tr>
                                        <tr>
                                            <td><span class="label label-warning">PUT</span></td>
                                            <td><code>/won/api/{tabela}/{id}</code></td>
                                            <td>Atualiza registro existente</td>
                                        </tr>
                                        <tr>
                                            <td><span class="label label-danger">DELETE</span></td>
                                            <td><code>/won/api/{tabela}/{id}</code></td>
                                            <td>Remove um registro</td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <h6>🔗 Consulta JOIN</h6>
                                <p><code>GET /won/join?vat={cnpj_cpf}</code> - Busca dados relacionados entre clientes, contatos, faturas e pagamentos</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>🗂️ Tabelas Permitidas</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul>
                                            <li><code>clients</code> - Clientes</li>
                                            <li><code>contacts</code> - Contatos</li>
                                            <li><code>leads</code> - Leads</li>
                                            <li><code>projects</code> - Projetos</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul>
                                            <li><code>tasks</code> - Tarefas</li>
                                            <li><code>invoices</code> - Faturas</li>
                                            <li><code>staff</code> - Funcionários</li>
                                            <li><code>tickets</code> - Tickets</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>📄 Paginação e Busca</h5>
                                <p><strong>Paginação:</strong></p>
                                <ul>
                                    <li><code>page</code>: Número da página (padrão: 1)</li>
                                    <li><code>limit</code>: Registros por página (padrão: 20)</li>
                                </ul>
                                <p><strong>Exemplo:</strong> <code>GET <?php echo $http_host; ?>/won/api/clients?page=2&limit=10</code></p>
                                
                                <p><strong>Busca Global:</strong></p>
                                <p><code>GET <?php echo $http_host; ?>/won/api/clients?search=empresa</code></p>
                                
                                <p><strong>Filtros Específicos:</strong></p>
                                <p><code>GET <?php echo $http_host; ?>/won/api/clients?company=teste&active=1</code></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>📋 Formato das Respostas</h5>
                                
                                <h6>✅ Sucesso</h6>
                                <pre><code>{
    "success": true,
    "data": [...],
    "message": "Operação realizada com sucesso",
    "meta": {
        "page": 1,
        "limit": 20,
        "total": 100,
        "total_pages": 5
    }
}</code></pre>
                                
                                <h6>❌ Erro</h6>
                                <pre><code>{
    "success": false,
    "error": "Mensagem de erro",
    "error_code": "CODIGO_ERRO"
}</code></pre>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>⚠️ Códigos de Erro Principais</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul>
                                            <li><code>AUTH_MISSING</code> - Token não fornecido</li>
                                            <li><code>AUTH_INVALID</code> - Token inválido</li>
                                            <li><code>RATE_LIMIT_EXCEEDED</code> - Limite excedido</li>
                                            <li><code>INVALID_TABLE</code> - Tabela não permitida</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul>
                                            <li><code>MISSING_REQUIRED_FIELD</code> - Campo obrigatório</li>
                                            <li><code>INVALID_EMAIL_FORMAT</code> - Email inválido</li>
                                            <li><code>INVALID_VAT_FORMAT</code> - CPF/CNPJ inválido</li>
                                            <li><code>NOT_FOUND</code> - Registro não encontrado</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>✅ Validações de Dados</h5>
                                <p><strong>Campos Obrigatórios:</strong></p>
                                <ul>
                                    <li><strong>clients:</strong> company</li>
                                    <li><strong>contacts:</strong> firstname, email, userid</li>
                                    <li><strong>leads:</strong> name</li>
                                </ul>
                                
                                <p><strong>Validações de Formato:</strong></p>
                                <ul>
                                    <li><strong>email:</strong> Formato de email válido</li>
                                    <li><strong>vat:</strong> CPF (11 dígitos) ou CNPJ (14 dígitos)</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>🔒 Rate Limiting</h5>
                                <div class="alert alert-warning">
                                    <strong>Limite:</strong> 100 requisições por hora por IP + token.<br>
                                    <strong>Resposta:</strong> Status HTTP 429 quando excedido.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>💻 Exemplos de Uso</h5>
                                
                                <h6>1. Listar Clientes com Paginação</h6>
                                <pre><code>curl -X GET "<?php echo $http_host; ?>/won/api/clients?page=1&limit=10" \
     -H "Authorization: seu_token_aqui"</code></pre>
                                
                                <h6>2. Criar Cliente</h6>
                                <pre><code>curl -X POST "<?php echo $http_host; ?>/won/api/clients" \
     -H "Authorization: seu_token_aqui" \
     -H "Content-Type: application/json" \
     -d '{
       "company": "Minha Empresa LTDA",
       "vat": "12345678000123",
       "email": "contato@empresa.com"
     }'</code></pre>
                                
                                <h6>3. Buscar por CNPJ (JOIN)</h6>
                                <pre><code>curl -X GET "<?php echo $http_host; ?>/won/join?vat=12345678000123" \
     -H "Authorization: seu_token_aqui"</code></pre>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5>🧪 Script de Testes</h5>
                                <p>O módulo inclui um script de testes em <code>tests/api_test.php</code> para validar todos os endpoints:</p>
                                <pre><code>php modules/won_api/tests/api_test.php</code></pre>
                                <p>Configure <code>$base_url</code> e <code>$token</code> no arquivo antes de executar.</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong>📖 Documentação Completa</strong><br>
                                    Para informações detalhadas, códigos de status HTTP, exemplos avançados e guia completo, 
                                    acesse a <a href="<?php echo admin_url('won_api/documentation'); ?>"><strong>Documentação Completa</strong></a>.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-success">
                                    <strong>🎯 Dicas Importantes:</strong>
                                    <ul class="mb-0">
                                        <li>Sempre use HTTPS em produção</li>
                                        <li>Monitore os logs do sistema para auditoria</li>
                                        <li>Mantenha o token de API seguro</li>
                                        <li>Execute os testes após atualizações</li>
                                    </ul>
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