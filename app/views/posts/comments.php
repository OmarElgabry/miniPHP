

	<?php if(empty($comments)){ ?>
		<li class='no-data'><div class='text-center'><span class='text-muted'>There is no comments!</span></div></li>
	
	<?php } else{
			foreach($comments as $comment){?>
			
				<li id="<?= "comment-" . Encryption::encryptId($comment["id"]); ?>" class="left clearfix">
					<span class="chat-img pull-left">
						<img src="<?= PUBLIC_ROOT . "img/profile_pictures/" . $comment["profile_picture"]; ?>" alt="User Picture" class="img-circle profile-pic-sm">
					</span>
					
					<div class="chat-body clearfix">
						<div class="header">
							<strong class="primary-font"><?= $comment["user_name"]; ?></strong>
							<small class="text-muted"><i class="fa fa-clock-o fa-fw"></i><?= $this->timestamp($comment["date"]) ?></small>
							<?php if(Session::getUserId() === (int) $comment["user_id"] || Session::getUserRole() === "admin"){ ?>
								<span class="pull-right btn-group btn-group-xs">
									<a class="btn btn-default edit"><i class="fa fa-pencil"></i></a>
									<a class="btn btn-danger delete"><i class="fa fa-times"></i></a>
								</span>
							<?php }?>
						</div>
						<p><?= $this->autoLinks($this->encodeHTMLWithBR($comment["content"])); ?></p>
					</div>
				 </li>
	<?php   }
		}?>
