<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
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
                                <a href="<?php echo admin_url('won_api/documentation'); ?>" class="btn btn-info btn-sm">
                                    <i class="fa fa-book"></i> Documentação
                                </a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="alert alert-info">
                            <i class="fa fa-shield"></i>
                            <strong>Token Seguro:</strong> Mantenha o token em local seguro e regenere periodicamente.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Token da API</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="api-token" value="<?php echo !empty($configs['token']) ? $configs['token'] : 'Token não configurado'; ?>" readonly>
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" onclick="copyToken()" title="Copiar Token">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>&nbsp;</label><br>
                                <a href="<?php echo admin_url('won_api/regenerate_token'); ?>"
                                   class="btn btn-warning" onclick="return confirm('Regenerar token? Isso invalidará o token atual.')">
                                    <i class="fa fa-refresh"></i> Regenerar Token
                                </a>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fa fa-info-circle"></i> Informações da API</h5>
                                <table class="table table-condensed">
                                    <tr><th width="40%">Endpoint Base:</th><td><code><?php echo site_url('won_api/won/api/'); ?></code></td></tr>
                                    <tr><th>Rate Limit:</th><td>100 requisições/hora</td></tr>
                                    <tr><th>Autenticação:</th><td>Header: <code>Authorization: TOKEN</code></td></tr>
                                    <tr><th>Status:</th><td><span class="label label-success">Ativo</span></td></tr>
                        </table>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fa fa-cogs"></i> Ações Rápidas</h5>
                                <div class="btn-group-vertical btn-block">
                                    <a href="<?php echo admin_url('won_api/documentation'); ?>" class="btn btn-default">
                                        <i class="fa fa-book"></i> Ver Documentação
                                    </a>
                                    <a href="<?php echo admin_url('won_api/logs'); ?>" class="btn btn-default">
                                        <i class="fa fa-list"></i> Ver Logs
                                    </a>
                                    <a href="<?php echo site_url('modules/won_api/verify_install.php'); ?>"
                                       class="btn btn-default" target="_blank">
                                        <i class="fa fa-check"></i> Diagnóstico
                                    </a>
                                </div>
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
    var t = document.getElementById("api-token");
    t.select();
    t.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Token copiado!");
}
</script>

<?php init_tail(); ?>