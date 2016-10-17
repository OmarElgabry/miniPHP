<?php 
	
	$notifications = $this->controller->user->getNotifications(Session::getUserId());
	$newsfeed = $posts = $files = "";
	foreach($notifications as $notification){
		if($notification["count"] > 0){
            // $$notification["target"] = $notification["count"];        // DEPRECATED IN PHP 7
			${$notification["target"]} = $notification["count"];
		}
	}
	
	$info = $this->controller->user->getProfileInfo(Session::getUserId());

?>

		<!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                
            </div>
            <!-- /.navbar-header -->
			
            <ul class="nav navbar-top-links navbar-right">
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        Hello,<strong> <?= $info["name"]; ?></strong> <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="<?= PUBLIC_ROOT . "User/Profile"; ?>"><i class="fa fa-user fa-fw"></i> Profile</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="<?= PUBLIC_ROOT . "Login/logOut"; ?>"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
			
            <!-- /.navbar-top-links -->

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
						<li id="logo" class="text-center">
                            <a href="">
								<img src="<?= PUBLIC_ROOT;?>img/backgrounds/background.png" class="img-circle" style="width: 220px; height: 150px;">
							</a>
                        </li>
                        <li id="dashboard">
                            <a href="<?= PUBLIC_ROOT . "User"; ?>"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                        </li>
                        <li id="newsfeed">
                            <a href="<?= PUBLIC_ROOT . "NewsFeed"; ?>"><i class="fa fa-rss fa-fw"></i> News Feed 
								<span class="label label-danger"><?= $newsfeed;?></span></a>
                        </li>
                        <li id="posts">
                            <a href="<?= PUBLIC_ROOT . "Posts"; ?>"><i class="fa fa-wechat fa-fw"></i> Posts
								<span class="label label-danger"><?= $posts;?></span></a>
                        </li>
                        <li id="files">
                            <a href="<?= PUBLIC_ROOT . "Files"; ?>"><i class="fa fa-cloud-upload fa-fw"></i> Files
								<span class="label label-danger"><?= $files;?></span></a>
                        </li>
						<li id="bugs">
                            <a href="<?= PUBLIC_ROOT . "User/Bugs"; ?>"><i class="fa fa-bug fa-fw"></i> Bugs</a>
                        </li>
						<?php if(Session::getUserRole()  === "admin") {?>
							<li id="users">
								<a href="<?= PUBLIC_ROOT . "Admin/Users"; ?>"><i class="fa fa-users fa-fw"></i> Users</a>
							</li>
							<li id="backups">
								<a href="<?= PUBLIC_ROOT . "Admin/Backups"; ?>"><i class="fa fa-database fa-fw"></i> Backups</a>
							</li>
						<?php } ?>
						<li>
                            <a href="https://github.com/OmarElGabry/miniPHP" target="_blank" class="btn btn-social-icon btn-github"><i class="fa fa-github fa-2x"></i><br>Support!</a>
                        </li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>