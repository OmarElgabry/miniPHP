
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
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-edit"></i> New Post
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">

                                    <?php if(empty(Session::get('posts-success'))){ ?>
                                    <form action="<?php echo PUBLIC_ROOT; ?>Posts/create" id="form-create-post" method="post">
                                        <div class="form-group">
                                            <label>Title <span class='text-danger'>*</span></label>
                                            <input dir="auto" type="text" name="title" class="form-control" required maxlength="60" placeholder="Title">
                                        </div>
										<div class="form-group">
                                            <label>Content <span class='text-danger'>*</span></label>
                                            <textarea dir="auto" class="form-control" name="content" required rows="20" maxlength="1800"></textarea>
											<p class="help-block"><em>The maximum number of characters allowed is <strong>1800</strong></em></p>
                                        </div>
                                        <div class="form-group">
                                            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken(); ?>" />
                                        </div>
										<div class="form-group form-actions text-right">
											 <button type="submit" name="submit" value="submit" class="btn btn-md btn-success">
												<i class="fa fa-check"></i> Post
											</button>
										</div>
                                    </form>
                                    <?php } else { echo $this->renderSuccess(Session::getAndDestroy('posts-success')); } ?>
                                    <?php 
                                        if(!empty(Session::get('posts-errors'))){
                                            echo $this->renderErrors(Session::getAndDestroy('posts-errors'));
                                        }
                                    ?>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                            <!-- /.row (nested) -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
				</div>
			<!-- END Newsfeed Block -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /#page-wrapper -->

