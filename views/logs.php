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
                                    <i class="fa fa-list-alt text-info"></i>
                                    <?php echo $title; ?>
                                </h4>
                                <hr class="hr-panel-heading" />
                            </div>
                        </div>

                        <!-- Informações dos Logs -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <strong>Logs da WON API v2.1.1</strong><br>
                                    Exibindo os últimos 20 logs relacionados à API. Os logs são armazenados no sistema padrão do Perfex CRM.
                                </div>
                            </div>
                        </div>

                        <!-- Tabela de Logs -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h5><i class="fa fa-list"></i> Logs Recentes</h5>
                                    </div>
                                    <div class="panel-body">
                                        <?php if (empty($logs)): ?>
                                            <div class="alert alert-warning">
                                                <i class="fa fa-exclamation-triangle"></i>
                                                Nenhum log encontrado. Isso pode significar que:
                                                <ul class="tw-mt-2">
                                                    <li>A API ainda não foi utilizada hoje</li>
                                                    <li>Os logs foram limpos automaticamente</li>
                                                    <li>O sistema de log não está ativo</li>
                                                </ul>
                                            </div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th width="150">Timestamp</th>
                                                            <th width="100">Nível</th>
                                                            <th>Mensagem</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($logs as $log): ?>
                                                            <tr>
                                                                <td>
                                                                    <small class="text-muted">
                                                                        <?php echo $log['timestamp']; ?>
                                                                    </small>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    $label_class = 'default';
                                                                    switch ($log['level']) {
                                                                        case 'error':
                                                                            $label_class = 'danger';
                                                                            break;
                                                                        case 'warning':
                                                                            $label_class = 'warning';
                                                                            break;
                                                                        case 'info':
                                                                            $label_class = 'info';
                                                                            break;
                                                                        case 'debug':
                                                                            $label_class = 'default';
                                                                            break;
                                                                    }
                                                                    ?>
                                                                    <span class="label label-<?php echo $label_class; ?>">
                                                                        <?php echo strtoupper($log['level']); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <code style="background: none; color: inherit; font-size: 12px;">
                                                                        <?php echo htmlspecialchars($log['message']); ?>
                                                                    </code>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informações Técnicas -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h5><i class="fa fa-cogs"></i> Informações Técnicas</h5>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <h6>Localização dos Logs:</h6>
                                                <code>application/logs/log-<?php echo date('Y-m-d'); ?>.php</code>
                                            </div>
                                            <div class="col-md-4">
                                                <h6>Filtro de Logs:</h6>
                                                <code>[WON API]</code>
                                            </div>
                                            <div class="col-md-4">
                                                <h6>Níveis de Log:</h6>
                                                <span class="label label-danger">ERROR</span>
                                                <span class="label label-warning">WARNING</span>
                                                <span class="label label-info">INFO</span>
                                                <span class="label label-default">DEBUG</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ações -->
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a href="<?php echo admin_url('won_api/settings'); ?>" 
                                   class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> Voltar às Configurações
                                </a>
                                <a href="<?php echo admin_url('won_api/docs'); ?>" 
                                   class="btn btn-primary">
                                    <i class="fa fa-book"></i> Ver Documentação
                                </a>
                                <button onclick="location.reload()" class="btn btn-success">
                                    <i class="fa fa-refresh"></i> Atualizar Logs
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?> 