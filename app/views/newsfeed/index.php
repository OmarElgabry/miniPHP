
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">News Feed</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
			   <div class="col-sm-2 col-lg-2"></div>
               <div class="col-sm-8 col-lg-8">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-rocket"></i> Share something ... 
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form action="#" id="form-create-newsfeed" method="post">
                                        <div class="form-group">
                                            <label>Content <span class="text-danger">*</span></label>
                                            <textarea dir="auto" rows="3" maxlength="300" name="content" class="form-control" required placeholder="What are you thinking?"></textarea>
											<p class="help-block"><em>The maximum number of characters allowed is <strong>300</strong></em></p>
                                        </div>
										<div class="form-group form-actions text-right">
											 <button type="submit" name="submit" value="submit" class="btn btn-md btn-success">
													<i class="fa fa-check"></i> Share
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
					
					<hr>
					<!-- News Feed Block -->
					<div class="panel panel-default">
						<!-- News Feed Title -->
						<div class="panel-heading">
							<i class="fa fa-rss"></i> News Feed
						</div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <ul id="list-newsfeed" class="chat">
                                <?php 
									$newsfeedData = $this->controller->newsfeed->getAll();
									echo $this->render(VIEWS_PATH . "newsfeed/newsfeed.php", array("newsfeed" => $newsfeedData["newsfeed"]));
								?>
                            </ul>

							<hr>
							<div class="text-right">
								<ul class="pagination">
									<?php 
										echo $this->render(VIEWS_PATH . "pagination/default.php", array("pagination" => $newsfeedData["pagination"]));
									?>
								</ul>
							</div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
					<!-- /.panel -->
				</div>
				<!-- END News Feed Block -->
			</div>
			<!-- /.row -->       
		</div>