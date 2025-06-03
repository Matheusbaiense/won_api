<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php echo $title; ?></h4>
                        <hr />
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Endpoint</th>
                                        <th>Método</th>
                                        <th>IP</th>
                                        <th>Status</th>
                                        <th>Tempo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($log['date'])); ?></td>
                                            <td><?php echo htmlspecialchars($log['endpoint']); ?></td>
                                            <td><span class="label label-info"><?php echo $log['method']; ?></span></td>
                                            <td><?php echo $log['ip_address']; ?></td>
                                            <td>
                                                <span class="label label-<?php echo $log['status'] < 400 ? 'success' : 'danger'; ?>">
                                                    <?php echo $log['status']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo round($log['response_time'], 3) . 's'; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Nenhum log encontrado</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (isset($pagination)): ?>
                            <div class="text-center">
                                <?php echo $pagination; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?> 