
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
					<div id="view-post" class="panel panel-default">
                       <?php 
                            $post = $this->controller->post->getById($postId);

                       		if(empty($action)){
								echo $this->render(Config::get('VIEWS_PATH') . "posts/post.php", array("post" => $post));
                       		}else if($action === "update"){
                       			echo $this->render(Config::get('VIEWS_PATH') . 'posts/postUpdateForm.php', array("post" => $post));
                       		}
						?>
                    </div>
					
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-comment"></i> Comments
                        </div>
                        <div class="panel-body">
                            <ul id="list-comments" class="chat">
                                <?php 
									$commentsData = $this->controller->comment->getAll($postId);
									echo $this->render(Config::get('VIEWS_PATH') . "posts/comments.php", array("comments" => $commentsData["comments"]));
								?>
                            </ul>
							
							<hr>
							<form action="#" id="form-create-comment" method="post">
								<div class="form-group">
									<textarea dir="auto" rows="3" maxlength="300" name="content" required class="form-control" placeholder="Write your Comment"></textarea>
									<p class="help-block"><em>The maximum number of characters allowed is <strong>300</strong></em></p>
								</div>
								<div class="form-group form-actions text-right">
									<button type="submit" name="submit" value="submit" class="btn btn-sm btn-success">
										<i class="fa fa-check"></i> Comment
									</button>
								</div>
                            </form>
							<!-- View More -->
								<div class="text-center">
									<ul class="pagination">
									<?php 
										echo $this->render(Config::get('VIEWS_PATH') . "pagination/comments.php", array("pagination" => $commentsData["pagination"]));
									?>
									</ul>
								</div>
							<!-- END View More -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
				</div>
			<!-- END Post Block -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /#page-wrapper -->

