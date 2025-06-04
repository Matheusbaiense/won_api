<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><i class="fa fa-cog"></i> <?php echo $title; ?></h4>
                        <hr>
                        
                        <div class="alert alert-success">
                            <strong>✅ WON API v2.1.1 Ativo</strong><br>
                            Sistema simplificado com CORS habilitado
                        </div>
                        
                        <div class="form-group">
                            <label>Token da API (X-API-TOKEN)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="api-token" 
                                       value="<?php echo $token; ?>" readonly>
                                <span class="input-group-btn">
                                    <button class="btn btn-success" onclick="copyToken()">
                                        <i class="fa fa-copy"></i> Copiar
                                    </button>
                                    <button class="btn btn-warning" onclick="regenerateToken()">
                                        <i class="fa fa-refresh"></i> Novo
                                    </button>
                                </span>
                            </div>
                        </div>
                        
                        <h5>📡 Endpoints Disponíveis:</h5>
                        <ul>
                            <li><code>/won_api/won/status</code> - Status da API (público)</li>
                            <li><code>/won_api/won/api/clients</code> - Gerenciar clientes</li>
                            <li><code>/won_api/won/api/projects</code> - Gerenciar projetos</li>
                            <li><code>/won_api/won/api/tasks</code> - Gerenciar tarefas</li>
                            <li><code>/won_api/won/api/invoices</code> - Gerenciar faturas</li>
                            <li><code>/won_api/won/api/leads</code> - Gerenciar leads</li>
                            <li><code>/won_api/won/api/staff</code> - Gerenciar funcionários</li>
                            <li><code>/won_api/won/join?vat=CPF</code> - Buscar por CPF/CNPJ</li>
                        </ul>
                        
                        <div class="text-center">
                            <a href="<?php echo admin_url('won_api/docs'); ?>" class="btn btn-primary">
                                <i class="fa fa-book"></i> Ver Documentação
                            </a>
                            <a href="<?php echo base_url('won_api/won/status'); ?>" 
                               class="btn btn-success" target="_blank">
                                <i class="fa fa-external-link"></i> Testar API
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToken() {
    document.getElementById('api-token').select();
    document.execCommand('copy');
    alert('Token copiado!');
}

function regenerateToken() {
    if (confirm('Gerar novo token? O atual será invalidado.')) {
        $.post('<?php echo admin_url('won_api/regenerate_token'); ?>', function(data) {
            if (data.success) {
                $('#api-token').val(data.token);
                alert('Novo token gerado!');
            }
        });
    }
}
</script>

<?php init_tail(); ?>