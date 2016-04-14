
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Posts</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
			   <div class="col-sm-2 col-lg-2"></div>
               <div class="col-sm-8 col-lg-8">
					<!-- Newsfeed Block -->
					<div class="panel panel-default">
                        <div class="panel-heading">
							<i class="fa fa-wechat"></i> Posts
							<div class="pull-right">
								<button 
									class="btn btn-success btn-xs" onclick="window.location='<?= PUBLIC_ROOT . "Posts/newPost";?>';">
									<i class="fa fa-plus"></i> New Post
								</button>
							</div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id="list-posts" class="table table-hover">
                                    <thead>
                                        <tr>
											<th>Author</th>
											<th>Post</th>
											<th  class="text-center"><i class="fa fa-comment"></i></th>
										</tr>
                                    </thead>
                                    <tbody>
                                       <?php 
											$postsData = $this->controller->post->getAll(empty($pageNum)? 1: $pageNum);
											echo $this->render(Config::get('VIEWS_PATH') . "posts/posts.php", array("posts" => $postsData["posts"]));
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
                                            ["pagination" => $postsData["pagination"], "link"=> "Posts"]);
									?>
								</ul>
							</div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
				</div>
			<!-- END Newsfeed Block -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /#page-wrapper -->

