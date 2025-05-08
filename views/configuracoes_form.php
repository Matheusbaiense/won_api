<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open(admin_url('won_api/' . (isset($config) ? 'edit/' . $config['id'] : 'add'))); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="token" class="control-label">Token da API</label>
                                        <input type="text" class="form-control" name="token" id="token" value="<?php echo isset($config['token']) ? $config['token'] : ''; ?>">
                                        <small class="text-muted">Insira um token seguro para proteger as requisições da API.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-info">Salvar</button>
                                    <a href="<?php echo admin_url('won_api/configuracoes'); ?>" class="btn btn-default">Cancelar</a>
                                </div>
                            </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>