
    <div class="container">
        <div class="row">
			<div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
						<div class="pull-right">
							<button id="link-register" class="btn btn-success btn-xs"> <i class="fa fa-plus"></i> New Account</button>
						</div>
                        <h3 class="panel-title">Login</h3>
                    </div>
                    <div class="panel-body">

                    <?php $display_form = Session::getAndDestroy('display-form'); ?>
                    
                        <form action="<?php echo PUBLIC_ROOT; ?>Login/login" id="form-login" method="post" 
                            <?php if(!empty($display_form)){ echo "class='display-none'"; } ?> >
                            <fieldset>
                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" required placeholder="E-mail" autofocus>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control" required placeholder="Password">
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input name="remember_me" type="checkbox" value="rememberme">Remember Me
                                    </label>
                                </div>
                                <?php if (!empty($redirect)) { ?>
                                    <div class="form-group">
                                        <input type="hidden" name="redirect" value="<?= $this->encodeHTML($redirect); ?>" />
                                    </div>
                                <?php } ?>
                                <div class="form-group">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken(); ?>" />
                                </div>
								<div class="form-group form-actions text-right">
                                   <button type="submit" name="submit" value="submit" class="btn btn-sm btn-success">
										<i class="fa fa-check"></i> Login
									</button>
                                </div>			   
                                <div class="form-group">
									Forgot your password? <a id="link-forgot-password" href="javascript:void(0)">Restore it</a>
                                </div>
                            </fieldset>
                        </form>
						<?php 
                            if(!empty(Session::get('login-errors'))){
                                echo $this->renderErrors(Session::getAndDestroy('login-errors'));
                            }
                        ?>

                        <?php if(empty(Session::get('forgot-password-success'))){ ?>
						<form action="<?php echo PUBLIC_ROOT; ?>Login/forgotPassword" id="form-forgot-password" method="post" 
                            <?php if($display_form !== "forgot-password"){ echo "class='display-none'"; } ?> >
                            <fieldset>
                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" required placeholder="E-mail" autofocus >
                                </div>
								<div class="form-group">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken(); ?>" />
                                </div>
								<div class="form-group form-actions text-right">
                                   <button type="submit" name="submit" value="submit" class="btn btn-sm btn-success">
										<i class="fa fa-check"></i> Send
									</button>
                                </div>	
								<div class="form-group">
									Did you remember your password? <a id="link-login" href="javascript:void(0)">Login</a>
                                </div>
                            </fieldset>
                        </form>
						<?php } else { echo $this->renderSuccess(Session::getAndDestroy('forgot-password-success')); } ?>
                        <?php 
                            if(!empty(Session::get('forgot-password-errors'))){
                                echo $this->renderErrors(Session::getAndDestroy('forgot-password-errors'));
                            }
                        ?>

                        <?php if(empty(Session::get('register-success'))){ ?>
						<form action="<?php echo PUBLIC_ROOT; ?>Login/register" id="form-register" method="post" 
                                <?php if($display_form !== "register"){ echo "class='display-none'"; } ?> >
                            <fieldset>
								<div class="form-group">
                                    <input class="form-control" placeholder="User Name" required name="name" type="text" autofocus >
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="E-mail" required name="email" type="email">
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Password" required name="password" type="password">
                                    <p class="help-block">Please enter a complex password</p>
                                </div>
								<div class="form-group">
                                    <input class="form-control" placeholder="Confirm Password" required name="confirm_password" type="password">
                                </div>
								<div class="form-group">
                                    <input class="form-control" placeholder="Please enter below characters" required name="captcha" type="text">
									<br>
									<?php $captcha = $this->controller->getCaptcha(); ?>
									<img src="<?= $captcha->inline();?>">
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken(); ?>" />
                                </div>
                                <div class="form-group form-actions text-right">
                                   <button type="submit" name="submit" value="submit" class="btn btn-sm btn-success">
										<i class="fa fa-check"></i> Resiger
									</button>
                                </div>	
								
								<div class="form-group">
									Have an account? <a id="link-login" href="javascript:void(0)">Login</a>
                                </div>
								
                            </fieldset>
                        </form>
                        <?php } else { echo $this->renderSuccess(Session::getAndDestroy('register-success')); } ?>
                        <?php 
                            if(!empty(Session::get('register-errors'))){
                                echo $this->renderErrors(Session::getAndDestroy('register-errors'));
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
	
	
