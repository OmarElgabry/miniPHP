
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
                        <div class="panel-heading">
							<div class="pull-right">
								<a href="<?= PUBLIC_ROOT . "downloads/users"?>" data-toggle="tooltip" title="Download Users" class="btn btn-alt btn-xs btn-danger excel">
									<i class="fa fa-print"></i>
								</a>
							</div>
                            <i class="fa fa-search"></i> Search
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form action="#" id="form-search-users" method="post" >
                                        <div class="form-group">
                                            <label>User Name</label>
                                            <input dir="auto" type="text" name="name" class="form-control" maxlength="30" placeholder = "User Name">
                                        </div>
										<div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control" maxlength="50" placeholder = "Email">
                                        </div>
										<div class="form-group">
                                            <label>Role</label>
                                            <select name="role" class="form-control" size="1">
												<option value="">Select Role</option>
                                                <option value="admin">Admin</option>
                                                <option value="user">User</option>
                                            </select>
                                        </div>
										<div class="form-group form-actions text-right">
											<button type="submit" name="submit" value="submit" class="btn btn-sm btn-primary">Search</button>
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
					<!-- Users Block -->
					<div class="panel panel-default">
						<!-- Users Title -->
						<div class="panel-heading">
							<i class="fa fa-users"></i> Users
						</div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                           <div class="table-responsive">
                                <table id="list-users" class="table table-hover">
                                    <thead>
                                        <tr>
											<th>Name</th>
											<th>Role</th>
											<th>Email</th>
											<th class="text-center"><i class="fa fa-cog"></i></th>
										</tr>
                                    </thead>
                                    <tbody>
										<?php 
											$usersData = $this->controller->admin->getUsers();
											echo $this->render(Config::get('ADMIN_VIEWS_PATH') . "users/users.php", array("users" => $usersData["users"]));
										?>
                                    </tbody>
                                </table>
                            </div>
							<hr>
							<div class="text-right">
								<ul class="pagination">
									<?php 
										echo $this->render(Config::get('VIEWS_PATH') . "pagination/default.php", array("pagination" => $usersData["pagination"]));
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

