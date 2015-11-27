
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Users</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
			   <div class="col-sm-2 col-lg-2"></div>
               <div class="col-sm-8 col-lg-8">
					<div class="panel panel-default">
						<?php 
							$info = $this->controller->user->getProfileInfo($userId);
						?>
                        <div class="panel-heading">
                            Update Profile
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form action="#" id="form-update-user-info" method="post" >
										<div class="form-group">
											<div class="block-section text-center">
												<img src="<?= $info["image"];?>" class="img-circle profile-pic-lg">
												<h3><strong><?= $info["name"];?></strong></h3>
												<h4>
													<span class="label label-default"><?= ucfirst($info["role"]);?></span>
												</h4>
											</div>
										</div>
                                        <div class="form-group">
                                            <label>User Name</label>
                                            <input dir="auto" type="text" name="name" value="<?= $info["name"];?>" class="form-control" required maxlength="30" placeholder="Name..">
											<p class="help-block"><em>The maximum number of characters allowed is <strong>30</strong></em></p>
                                        </div>
										<div class="form-group">
                                            <label>Email</label>
                                             <input type="email" value="<?= $this->encodeHTML($info["email"]); ?>" disabled class="form-control" maxlength="50" placeholder="Email..">
											
											<!--- for users who registered but didn't verify their account -->
											<?php if(empty($info["is_email_activated"])) {?>
												<p class="text-danger"><em>Email is not activated</em></p>
											<?php } else { ?>
												<p class="text-success"><em>Email has been verified</em></p>
											<?php } ?>
											
                                        </div>
										<div class="form-group">
                                            <label>Password</label>
                                            <input type="password" name="password" class="form-control" placeholder="Password">
                                            <p class="help-block">Please enter a complex password</p>
                                        </div>
										<div class="form-group">
                                            <label>Role</label>
                                            <select name="role" class="form-control" size="1">
												<option value="">Select Role</option>
                                                <?php foreach(['admin', 'user'] as $role){ ?>
                                                    <?php if($role === $info['role']) { ?>
                                                        <option selected value="<?= $info['role']; ?>"><?= ucfirst($info['role']); ?></option>
                                                    <?php } else { ?>
                                                        <option  value="<?= $info['role']; ?>"><?= ucfirst($info['role']); ?></option>
                                                    <?php } ?>
                                                <?php } ?>
                                            </select>
                                        </div>
										<div class="form-group form-actions text-right">
											<button type="submit" name="submit" value="submit" class="btn btn-md btn-primary">
												<i class="fa fa-check"></i> Update
											</button>
										</div>
                                    </form>
									<!-- END Update Profile -->
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                            <!-- /.row (nested) -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
				</div>
			<!-- END Profile Block -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /#page-wrapper -->
