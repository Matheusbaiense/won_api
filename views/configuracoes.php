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
                        <a href="<?php echo admin_url('won_api/add'); ?>" class="btn btn-info mbot20">Adicionar Token</a>
                        <table class="table dt-table">
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
                                        <td><?php echo $configs['token']; ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('won_api/edit/' . $configs['id']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil"></i></a>
                                            <a href="<?php echo admin_url('won_api/delete/' . $configs['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-trash"></i></a>
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
<?php init_tail(); ?>