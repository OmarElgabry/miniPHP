
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Backups</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
			   <div class="col-sm-2 col-lg-2"></div>
               <div class="col-sm-8 col-lg-8">
					<div class="panel panel-default">
                        <div class="panel-heading">
							<i class="fa fa-database"></i> Backups
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id="backups" class="table table-hover">
                                    <thead>
                                        <tr>
											<th>File</th>
											<th>Last Update</th>
											<th>Update</th>
											<th>Restore</th>
										</tr>
                                    </thead>
                                    <tbody>
                                       <tr>
											<?php 
												$data = $this->controller->admin->getBackups();
												$isBackupExists = true;
												if(empty($data["basename"]) || empty($data["filename"])){
													$data["filename"] = "Not Available";
													$data["date"] = "-";
													$isBackupExists = false;
												} 
											?>
											<td>
												<strong class="text-<?= (empty($isBackupExists))? "danger": "primary"; ?>">
													<?= $data["filename"]; ?></strong>
											</td>
											<td><em><?= $data["date"]; ?></em></td>
											<td>
											   <span class="btn-group btn-group-sm">
													<a href="<?= PUBLIC_ROOT . "Admin/updateBackup" . 
														"?csrf_token=" . urlencode(Session::generateCsrfToken()); ?>" 
														class="btn btn-success update-backup" >
														<i class="fa fa-refresh"></i>
													</a>
												</span>
										   </td>
										   <td>
											   <span class="btn-group btn-group-sm">
													<a href="<?= PUBLIC_ROOT . "Admin/restoreBackup" . 
														"?csrf_token=" . urlencode(Session::generateCsrfToken()); ?>"  
														class="btn btn-danger restore-backup" >
														<i class="fa fa-rotate-left"></i>
													</a>
												</span>
										   </td>
										</tr>
                                    </tbody>
                                </table>
                                <?php 
									if(!empty(Session::get('backup-errors'))){
										echo $this->renderErrors(Session::getAndDestroy('backup-errors'));
									}else if(!empty(Session::get('backup-success'))){
										echo $this->renderSuccess(Session::getAndDestroy('backup-success'));
									}
								?>
                            </div>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
				</div>
			<!-- END Newsfeed Block -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /#page-wrapper -->

