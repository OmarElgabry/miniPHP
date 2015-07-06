
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
                        <form action="#" id="form-login" method="post" >
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
						
						<form action="#" id="form-forgot-password" method="post" class="display-none">
                            <fieldset>
                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" required placeholder="E-mail" autofocus >
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
						
						<form action="#" id="form-register" method="post" class="display-none">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
	
	
