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
                                <a href="<?php echo admin_url('won_api/documentation'); ?>" class="btn btn-info">
                                    <i class="fa fa-book"></i> Documentação da API
                                </a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong>Importante:</strong> O token da API é usado para autenticar todas as requisições.
                                    Mantenha-o seguro e não o compartilhe com pessoas não autorizadas.
                                </div>
                            </div>
                        </div>
                        
                        <?php echo form_open(admin_url('won_api/regenerate_token')); ?>
                        <button type="submit" class="btn btn-warning mbot20" onclick="return confirm('Tem certeza que deseja regenerar o token? O token atual será invalidado.');">
                            <i class="fa fa-refresh"></i> Regenerar Token
                        </button>
                        <?php echo form_close(); ?>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Token da API</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($configs) && !empty($configs['token'])) { ?>
                                    <tr>
                                        <td><?php echo $configs['id']; ?></td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="api-token" value="<?php echo $configs['token']; ?>" readonly>
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default" type="button" onclick="copyToken()">
                                                        <i class="fa fa-copy"></i> Copiar
                                                    </button>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="label label-success">Ativo</span>
                                        </td>
                                    </tr>
                                    <?php } else { ?>
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            <p>Nenhum token configurado.</p>
                                            <p>Use o botão "Regenerar Token" acima para criar um novo token.</p>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToken() {
    var tokenInput = document.getElementById("api-token");
    tokenInput.select();
    tokenInput.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Token copiado para a área de transferência!");
}
</script>

<?php init_tail(); ?>