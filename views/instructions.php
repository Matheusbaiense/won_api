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
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Visão Geral</h5>
                                <p>A API do Won API permite a integração com o Perfex CRM para realizar operações CRUD em tabelas do banco de dados e consultas combinadas entre tabelas. Todas as requisições devem incluir um token de autenticação válido no cabeçalho <code>Authorization</code>.</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5>Endpoint Base para Operações CRUD</h5>
                                <p><code><?php echo $http_host; ?>/won/api/{tabela}/{id}</code></p>
                                <ul>
                                    <li><strong>{tabela}</strong>: Nome da tabela a ser acessada (ex.: "contacts" será convertido para "tblcontacts").</li>
                                    <li><strong>{id}</strong>: ID do registro (opcional, dependendo do método).</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5>Métodos Suportados (CRUD)</h5>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Método</th>
                                            <th>Descrição</th>
                                            <th>Exemplo de Uso</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>GET</code></td>
                                            <td>Recupera registros de uma tabela. Suporta busca por ID ou filtro com parâmetro <code>search</code>.</td>
                                            <td>
                                                <code>GET <?php echo $http_host; ?>/won/api/contacts</code><br>
                                                <code>GET <?php echo $http_host; ?>/won/api/contacts/?search=joao</code><br>
                                                <strong>Cabeçalho:</strong> <code>Authorization: seu_token_aqui</code>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><code>POST</code></td>
                                            <td>Cria um novo registro em uma tabela.</td>
                                            <td>
                                                <code>POST <?php echo $http_host; ?>/won/api/contacts</code><br>
                                                <strong>Corpo:</strong> <code>{"firstname": "João", "lastname": "Silva"}</code><br>
                                                <strong>Cabeçalho:</strong> <code>Authorization: seu_token_aqui</code>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><code>PUT</code></td>
                                            <td>Atualiza um registro existente.</td>
                                            <td>
                                                <code>PUT <?php echo $http_host; ?>/won/api/contacts/1</code><br>
                                                <strong>Corpo:</strong> <code>{"firstname": "João Atualizado"}</code><br>
                                                <strong>Cabeçalho:</strong> <code>Authorization: seu_token_aqui</code>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><code>DELETE</code></td>
                                            <td>Exclui um registro.</td>
                                            <td>
                                                <code>DELETE <?php echo $http_host; ?>/won/api/contacts/1</code><br>
                                                <strong>Cabeçalho:</strong> <code>Authorization: seu_token_aqui</code>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5>Endpoint para Consulta com JOIN</h5>
                                <p><code><?php echo $http_host; ?>/won/join?vat={cnpj_ou_cpf}</code></p>
                                <p>Este endpoint retorna dados combinados das tabelas <code>clients</code>, <code>contacts</code>, <code>invoices</code> e <code>invoicepaymentrecords</code> usando JOINs. Use o parâmetro <code>vat</code> para filtrar por CNPJ ou CPF. Exemplo de uso:</p>
                                <pre><code>curl -X GET "<?php echo $http_host; ?>/won/join?vat=12345678901234" \
                                -H "Authorization: seu_token_aqui" \
                                -H "Content-Type: application/json"</code></pre>
                                <p><strong>Campos retornados:</strong></p>
                                <ul>
                                    <li><code>client_company</code>: Nome da empresa do cliente</li>
                                    <li><code>client_vat</code>: CNPJ ou CPF do cliente</li>
                                    <li><code>client_active_status</code>: Status do cliente ("Ativo" ou "Inativo")</li>
                                    <li><code>contact_firstname</code>: Primeiro nome do contato</li>
                                    <li><code>contact_lastname</code>: Sobrenome do contato</li>
                                    <li><code>contact_email</code>: E-mail do contato</li>
                                    <li><code>invoice_number</code>: Número da fatura</li>
                                    <li><code>invoice_total</code>: Total da fatura</li>
                                    <li><code>invoice_date</code>: Data da fatura (formato dd/mm/yyyy)</li>
                                    <li><code>invoice_duedate</code>: Data de vencimento da fatura (formato dd/mm/yyyy)</li>
                                    <li><code>invoice_status</code>: Status da fatura ("Pendente", "Pago" ou "Vencido")</li>
                                    <li><code>payment_amount</code>: Valor do pagamento</li>
                                    <li><code>payment_daterecorded</code>: Data do pagamento (formato dd/mm/yyyy)</li>
                                    <li><code>payment_method</code>: Método de pagamento</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5>Autenticação</h5>
                                <p>Para autenticar as requisições, adicione o token gerado na seção de Configurações no cabeçalho <code>Authorization</code>. Exemplo:</p>
                                <pre><code>Authorization: seu_token_aqui</code></pre>
                                <p>O token pode ser gerado ou editado em <a href="<?php echo admin_url('won_api/configuracoes'); ?>">Configurações</a>.</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5>Exemplo de Requisição com cURL (CRUD)</h5>
                                <pre><code>curl -X GET "<?php echo $http_host; ?>/won/api/contacts" \
-H "Authorization: seu_token_aqui" \
-H "Content-Type: application/json"</code></pre>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5>Respostas</h5>
                                <p>Todas as respostas são retornadas em formato JSON. Exemplos:</p>
                                <ul>
                                    <li><strong>Sucesso (GET - CRUD):</strong> <code>[{"id": 1, "firstname": "João", "lastname": "Silva"}]</code></li>
                                    <li><strong>Sucesso (GET - Join):</strong> <code>[{"client_userid": 1, "client_company": "Empresa XYZ", "contact_firstname": "João", "invoice_number": 1001, ...}]</code></li>
                                    <li><strong>Erro (401):</strong> <code>{"erro": "Token inválido ou não fornecido"}</code></li>
                                    <li><strong>Sucesso (POST):</strong> <code>{"id": 2}</code></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>