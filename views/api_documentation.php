<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><i class="fa fa-book"></i> <?php echo $title; ?></h4>
                        <hr />
                        
                        <div class="alert alert-warning">
                            <i class="fa fa-shield"></i> <strong>Token:</strong>
                            <code id="token-display"><?php echo $token ?: 'Token não configurado'; ?></code>
                            <button onclick="copyText('token-display')" class="btn btn-xs btn-default pull-right">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>📡 Endpoints Disponíveis</h5>
                                <div class="table-responsive">
                                    <table class="table table-condensed">
                                        <thead><tr><th>Método</th><th>Endpoint</th><th>Descrição</th></tr></thead>
                                        <tbody>
                                            <?php foreach($allowed_tables as $table): ?>
                                            <tr>
                                                <td><span class="label label-info">GET</span></td>
                                                <td><code>/api/<?php echo $table; ?></code></td>
                                                <td>Listar <?php echo ucfirst($table); ?></td>
                                            </tr>
                                            <tr>
                                                <td><span class="label label-success">POST</span></td>
                                                <td><code>/api/<?php echo $table; ?></code></td>
                                                <td>Criar <?php echo ucfirst($table); ?></td>
                                            </tr>
                                            <tr>
                                                <td><span class="label label-warning">PUT</span></td>
                                                <td><code>/api/<?php echo $table; ?>/{id}</code></td>
                                                <td>Atualizar <?php echo ucfirst($table); ?></td>
                                            </tr>
                                            <tr>
                                                <td><span class="label label-danger">DELETE</span></td>
                                                <td><code>/api/<?php echo $table; ?>/{id}</code></td>
                                                <td>Deletar <?php echo ucfirst($table); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>🔧 Exemplo de Uso</h5>
                                <pre style="font-size:11px;background:#f8f8f8;padding:10px;border-radius:3px;">
# Listar clientes
curl -X GET "<?php echo $base_url; ?>api/clients" \
     -H "X-API-TOKEN: <?php echo substr($token, 0, 16); ?>..."

# Criar cliente
curl -X POST "<?php echo $base_url; ?>api/clients" \
     -H "X-API-TOKEN: <?php echo substr($token, 0, 16); ?>..." \
     -H "Content-Type: application/json" \
     -d '{"company":"Empresa LTDA"}'

# Obter cliente específico
curl -X GET "<?php echo $base_url; ?>api/clients/1" \
     -H "X-API-TOKEN: <?php echo substr($token, 0, 16); ?>..."

# Endpoints especializados v2.1.2
curl -X POST "<?php echo $base_url; ?>estimate/convert/123" \
     -H "X-API-TOKEN: <?php echo substr($token, 0, 16); ?>..."

curl -X GET "<?php echo $base_url; ?>dashboard/stats" \
     -H "X-API-TOKEN: <?php echo substr($token, 0, 16); ?>..."</pre>
                                
                                <h5>📋 Headers Obrigatórios</h5>
                                <table class="table table-condensed">
                                    <tr><th>X-API-TOKEN:</th><td>Seu token da API</td></tr>
                                    <tr><th>Content-Type:</th><td>application/json (POST/PUT)</td></tr>
                                </table>
                                
                                <h5>⚡ Rate Limit</h5>
                                <p>100 requisições por hora por IP</p>
                                
                                <h5>🔍 Códigos de Resposta</h5>
                                <table class="table table-condensed">
                                    <tr><td><span class="label label-success">200</span></td><td>Sucesso</td></tr>
                                    <tr><td><span class="label label-primary">201</span></td><td>Criado</td></tr>
                                    <tr><td><span class="label label-warning">400</span></td><td>Erro nos dados</td></tr>
                                    <tr><td><span class="label label-danger">401</span></td><td>Token inválido</td></tr>
                                    <tr><td><span class="label label-danger">404</span></td><td>Não encontrado</td></tr>
                                    <tr><td><span class="label label-danger">429</span></td><td>Rate limit excedido</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyText(id) {
    var e = document.getElementById(id);
    var r = document.createRange();
    r.selectNode(e);
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(r);
    document.execCommand('copy');
    window.getSelection().removeAllRanges();
    alert('Copiado!');
}
</script>
<?php init_tail(); ?> 