 
	<?php if(empty($newsfeed)){ ?>
		<li class='no-data text-center'><span class='text-muted'>There is no news feed!!</span></li>
	
	<?php } else{
			foreach($newsfeed as $feed){?>
				<li id="<?= "feed-" . Encryption::encryptId($feed["id"]);?>" class="left clearfix">
					<span class="chat-img pull-left">
						<img src="<?= PUBLIC_ROOT . "img/profile_pictures/" . $feed["profile_picture"]; ?>" alt="User Picture" class="img-circle profile-pic-sm">
					</span>
					<div class="chat-body clearfix">
						<div class="header">
							<strong class="primary-font"><?= $feed["user_name"]; ?></strong>
								<small class="text-muted"><i class="fa fa-clock-o fa-fw"></i> <?= $this->timestamp($feed["date"]);?> </small>
								<?php if(Session::getUserId() === (int) $feed["user_id"] || Session::getUserRole() === "admin"){?>
									<span class="pull-right btn-group btn-group-xs">
										<a class="btn btn-default edit"><i class="fa fa-pencil"></i></a>
										<a class="btn btn-danger delete"><i class="fa fa-times"></i></a>
									</span>
								<?php }?>
						</div>
						<p> <?= $this->autoLinks($this->encodeHTMLWithBR($feed["content"])); ?></p>
					</div>
				</li>
	<?php   }
		}?>


		
 
