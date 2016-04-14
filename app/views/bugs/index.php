
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Bugs</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
			   <div class="col-sm-2 col-lg-2"></div>
               <div class="col-sm-8 col-lg-8">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-edit"></i> Report Bugs, Features & Enhancements
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">

                                <?php if(empty(Session::get('report-bug-success'))){ ?>
                                <form action="<?php echo PUBLIC_ROOT; ?>User/reportBug" id="form-bug" method="post">
                                        <div class="form-group">
                                            <label>Subject <span class="text-danger">*</span></label>
											<input type="text" name="subject" class="form-control" required maxlength="80" placeholder="Write the subject">
                                        </div>
										
										<div class="form-group">
                                            <label>Bug, Feature or Enhancement? <span class="text-danger">*</span></label>
                                            <select name="label" class="form-control" size="1">
                                                <option value="bug">Bug</option>
                                                <option value="feature">Feature</option>
                                                <option value="enhancement">Enhancement</option>
                                            </select>
                                            <p class="help-block">Bug is an error you encountered</p>
                                            <p class="help-block">Feature is a new functionality you suggest to add</p>
                                            <p class="help-block">Enhancement is an existing feature, but you want to improve</p>
                                        </div>
										
										<div class="form-group">
											<label>Message <span class="text-danger">*</span></label>
											<textarea class="form-control" name="message" required rows="20" maxlength="1800"></textarea>
											<p class="help-block"><em>The maximum number of characters allowed is <strong>1800</strong></em></p>
                                        </div>
                                        <div class="form-group">
                                            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken(); ?>" />
                                        </div>
										<div class="form-group form-actions text-right">
											<button type="submit" name="submit" value="submit" class="btn btn-md btn-success">
												<i class="fa fa-check"></i> Send
											</button>
										</div>
                                    </form>
                                    <?php } else { echo $this->renderSuccess(Session::getAndDestroy('report-bug-success')); } ?>
                                    <?php 
                                        if(!empty(Session::get('report-bug-errors'))){
                                            echo $this->renderErrors(Session::getAndDestroy('report-bug-errors'));
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
