<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); 
$http_host = 'https://' . $_SERVER['HTTP_HOST']. '/';
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
                                <p>Esta página lista todas as tabelas disponíveis no banco de dados que podem ser acessadas via API. Use o endpoint base <code><?php echo $http_host; ?>won_api/won/api/{tabela}</code> com o método apropriado.</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <table class="table dt-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome da Tabela</th>
                                            <th>Endpoint Exemplo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($tables)) { 
                                            $id = 1;
                                            foreach ($tables as $table) { ?>
                                                <tr>
                                                    <td><?php echo $id++; ?></td>
                                                    <td><?php echo str_replace('tbl', '', $table['TABLE_NAME']); ?></td>
                                                    <td><code><?php echo $http_host; ?>won_api/won/api/<?php echo str_replace('tbl', '', $table['TABLE_NAME']); ?></code></td>
                                                </tr>
                                            <?php } 
                                        } else { ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Nenhuma tabela encontrada.</td>
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
</div>
<?php init_tail(); ?>