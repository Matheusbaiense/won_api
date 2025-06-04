<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix"></div>
                        
                        <!-- Header -->
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg">
                                    <i class="fa fa-cog text-success"></i>
                                    WON API v2.1.1 - Configurações Easy Install
                                </h4>
                                <hr class="hr-panel-heading" />
                            </div>
                        </div>

                        <!-- Status API -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <strong><i class="fa fa-check-circle"></i> API Ativa</strong><br>
                                    Versão: 2.1.1 Easy Install | CORS: Habilitado | Rate Limiting: Removido
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <strong><i class="fa fa-info-circle"></i> Endpoint Base:</strong><br>
                                    <code><?php echo base_url('won_api/won/'); ?></code>
                                </div>
                            </div>
                        </div>

                        <!-- Token de Autenticação -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h5><i class="fa fa-key"></i> Token de Autenticação</h5>
                                    </div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label for="api_token">Token da API (X-API-TOKEN)</label>
                                            <div class="input-group">
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="api_token" 
                                                       value="<?php echo get_option('won_api_token'); ?>" 
                                                       readonly
                                                       style="font-family: monospace;">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-success" 
                                                            type="button" 
                                                            onclick="copyToken()" 
                                                            title="Copiar Token">
                                                        <i class="fa fa-copy"></i> Copiar
                                                    </button>
                                                    <button class="btn btn-warning" 
                                                            type="button" 
                                                            onclick="regenerateToken()" 
                                                            title="Gerar Novo Token">
                                                        <i class="fa fa-refresh"></i> Novo
                                                    </button>
                                                </span>
                                            </div>
                                            <small class="help-block">
                                                Use este token no header <strong>X-API-TOKEN</strong> de suas requisições
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Exemplo de Uso -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <h5><i class="fa fa-code"></i> Exemplo de Uso</h5>
                                    </div>
                                    <div class="panel-body">
                                        <h6>Listar Clientes:</h6>
                                        <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;">curl -X GET "<?php echo base_url('won_api/won/api/clients'); ?>" \
     -H "X-API-TOKEN: <?php echo substr(get_option('won_api_token'), 0, 20); ?>..."</pre>

                                        <h6>Criar Cliente:</h6>
                                        <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;">curl -X POST "<?php echo base_url('won_api/won/api/clients'); ?>" \
     -H "X-API-TOKEN: <?php echo substr(get_option('won_api_token'), 0, 20); ?>..." \
     -H "Content-Type: application/json" \
     -d '{"company": "Empresa LTDA"}'</pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabelas Disponíveis -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h5><i class="fa fa-table"></i> Endpoints Disponíveis</h5>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <h6>Principais:</h6>
                                                <ul class="list-unstyled">
                                                    <li><i class="fa fa-users text-primary"></i> <code>/clients</code> - Clientes</li>
                                                    <li><i class="fa fa-star text-warning"></i> <code>/leads</code> - Leads</li>
                                                    <li><i class="fa fa-user-tie text-info"></i> <code>/staff</code> - Funcionários</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-4">
                                                <h6>Projetos & Tarefas:</h6>
                                                <ul class="list-unstyled">
                                                    <li><i class="fa fa-briefcase text-success"></i> <code>/projects</code> - Projetos</li>
                                                    <li><i class="fa fa-tasks text-primary"></i> <code>/tasks</code> - Tarefas</li>
                                                    <li><i class="fa fa-file-text text-info"></i> <code>/invoices</code> - Faturas</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-4">
                                                <h6>Especiais:</h6>
                                                <ul class="list-unstyled">
                                                    <li><i class="fa fa-search text-success"></i> <code>/join?vat=CPF/CNPJ</code></li>
                                                    <li><i class="fa fa-heartbeat text-danger"></i> <code>/status</code> (público)</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Links Úteis -->
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a href="<?php echo admin_url('won_api/documentation'); ?>" 
                                   class="btn btn-primary">
                                    <i class="fa fa-book"></i> Documentação Completa
                                </a>
                                <a href="<?php echo admin_url('won_api/logs'); ?>" 
                                   class="btn btn-info">
                                    <i class="fa fa-list-alt"></i> Ver Logs
                                </a>
                                <a href="<?php echo base_url('modules/won_api/won/status'); ?>" 
                                   class="btn btn-success" 
                                   target="_blank">
                                    <i class="fa fa-external-link"></i> Testar API
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToken() {
    const tokenField = document.getElementById('api_token');
    tokenField.select();
    tokenField.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        alert_float('success', 'Token copiado para a área de transferência!');
    } catch (err) {
        alert_float('danger', 'Erro ao copiar token. Selecione e copie manualmente.');
    }
}

function regenerateToken() {
    if (confirm('Tem certeza que deseja gerar um novo token? O token atual será invalidado.')) {
        $.post('<?php echo admin_url('won_api/regenerate_token'); ?>', function(response) {
            if (response.success) {
                $('#api_token').val(response.new_token);
                alert_float('success', 'Novo token gerado com sucesso!');
            } else {
                alert_float('danger', 'Erro ao gerar novo token.');
            }
        }).fail(function() {
            alert_float('danger', 'Erro na comunicação com o servidor.');
        });
    }
}
</script>

<?php init_tail(); ?>
<?php init_tail(); ?>