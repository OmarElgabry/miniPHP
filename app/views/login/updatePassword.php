

    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-lock"></i> Update Your Password</h3>
                    </div>
                    <div class="panel-body">
                        <form action="<?php echo PUBLIC_ROOT; ?>Login/updatePassword" id="form-update-password" method="post">
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Password" required name="password" type="password">
                                </div>
								<div class="form-group">
                                    <input class="form-control" placeholder="Confirm Password" required name="confirm_password" type="password">
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="id" value="<?= $this->encodeHTML($this->controller->request->query("id")); ?>" />
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="token" value="<?= $this->encodeHTML($this->controller->request->query("token")); ?>" />
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken(); ?>" />
                                </div>
								<div class="form-group form-actions text-right">
                                    <button type="submit" name="submit" value="submit" class="btn btn-md btn-success">
										<i class="fa fa-check"></i> Update
									</button>
                                </div>
                            </fieldset>
                        </form>
                        <?php 
                            if(!empty(Session::get('update-password-errors'))){
                                echo $this->renderErrors(Session::getAndDestroy('update-password-errors'));
                            }
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

