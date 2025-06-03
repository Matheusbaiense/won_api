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
                                <a href="<?php echo admin_url('won_api/instructions'); ?>" class="btn btn-info">
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
                        
                        <a href="<?php echo admin_url('won_api/add'); ?>" class="btn btn-primary mbot20">
                            <i class="fa fa-plus"></i> Adicionar Token
                        </a>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dt-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Token da API</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($configs)) { ?>
                                    <tr>
                                        <td><?php echo $configs['id']; ?></td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="api-token" value="<?php echo $configs['token']; ?>" readonly>
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default" type="button" onclick="copyToken()">
                                                        <i class="fa fa-copy"></i>
                                                    </button>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="<?php echo admin_url('won_api/edit/' . $configs['id']); ?>" class="btn btn-default btn-icon">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <a href="<?php echo admin_url('won_api/delete/' . $configs['id']); ?>" class="btn btn-danger btn-icon _delete">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php } else { ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Nenhum token encontrado.</td>
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
    document.execCommand("copy");
    alert("Token copiado para a área de transferência!");
}
</script>

<?php init_tail(); ?>