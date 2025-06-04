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
                            Sistema simplificado - CRUD básico funcional
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
                        
                        <h5>📡 Endpoints Realmente Implementados:</h5>
                        <ul>
                            <li><code>/won_api/won/status</code> - Status da API (público) ✅</li>
                            <li><code>/won_api/won/api/clients</code> - CRUD clientes ✅</li>
                            <li><code>/won_api/won/api/projects</code> - CRUD projetos ✅</li>
                            <li><code>/won_api/won/api/tasks</code> - CRUD tarefas ✅</li>
                            <li><code>/won_api/won/api/invoices</code> - CRUD faturas ✅</li>
                            <li><code>/won_api/won/api/leads</code> - CRUD leads ✅</li>
                            <li><code>/won_api/won/api/staff</code> - CRUD funcionários ✅</li>
                            <li><code>/won_api/won/join?vat=CPF</code> - Buscar por CPF/CNPJ ✅</li>
                        </ul>
                        
                        <div class="alert alert-warning">
                            <strong>⚠️ O que NÃO está implementado:</strong><br>
                            • Rate limiting avançado<br>
                            • Validações robustas de CPF/CNPJ<br>
                            • Endpoints especializados (estimate/convert, etc.)<br>
                            • Sistema de logs avançado<br>
                            • Dashboard de métricas
                        </div>
                        
                        <div class="text-center">
                            <a href="<?php echo admin_url('won_api/docs'); ?>" class="btn btn-primary">
                                <i class="fa fa-book"></i> Ver Documentação
                            </a>
                            <a href="<?php echo admin_url('won_api/logs'); ?>" class="btn btn-info">
                                <i class="fa fa-list"></i> Ver Logs
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