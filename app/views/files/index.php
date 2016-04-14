
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Files</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
			   <div class="col-sm-2 col-lg-2"></div>
               <div class="col-sm-8 col-lg-8">
					<!-- Files Block -->
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-upload"></i> Upload File
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form id="form-upload-file" method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label>File <span class="text-danger">*</span></label>
                                            <input type="file" name="file" id="file" required>
											<p class="help-block"><em> Only PDF, PPT, DOCX & ZIP Files</em></p>
											<p class="help-block"><em> Max File Size: 5MB</em></p>
                                        </div>
										<div class="progress progress-striped active display-none">
											<div class="progress-bar progress-bar-success" style="width: 0%"></div>
										</div>
										<div class="form-group form-actions text-right">
											 <button type="submit" value="submit" class="btn btn-md btn-success">
												<i class="fa fa-upload"></i> Upload
											</button>
										</div>
                                    </form>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                            <!-- /.row (nested) -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
					
					<div class="panel panel-default">
                        <div class="panel-heading">
							<i class="fa fa-files-o"></i> Files
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id="list-files" class="table table-hover">
                                    <thead>
                                        <tr>
											<th>Author</th>
											<th>File</th>
											<th>Format</th>
											<th class="text-center"><i class="fa fa-cog"></i></th>
										</tr>
                                    </thead>
                                    <tbody>
                                        <?php 
											$filesData = $this->controller->file->getAll(empty($pageNum)? 1: $pageNum);
											echo $this->render(Config::get('VIEWS_PATH') . "files/files.php", array("files" => $filesData["files"]));
										?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
							<hr>
							<div class="text-right">
								<ul class="pagination">
									<?php 
										echo $this->render(Config::get('VIEWS_PATH') . "pagination/default.php", 
                                            ["pagination" => $filesData["pagination"], "link" => "Files"]);
									?>
								</ul>
							</div>
                        </div>
                    <!-- /.panel-body -->
                </div>
			</div>
			<!-- END Files Block -->
		</div>
    </div>
    <!-- /.row -->
