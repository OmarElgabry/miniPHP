 
 
		<span class="chat-img pull-left">
			<img src="<?= PUBLIC_ROOT . "img/profile_pictures/" . $newsfeed["profile_picture"]; ?>" alt="User Picture" class="img-circle profile-pic-sm">
		</span>
		<div class="chat-body clearfix">
			<div class="header">
				<strong class="primary-font"><?= $newsfeed["user_name"]; ?></strong>
				<small class="text-muted"><i class="fa fa-clock-o fa-fw"></i> <?= $this->timestamp($newsfeed["date"]);?> </small>
			</div>
			<form action="#" id="<?= "form-update-feed-" . Encryption::encryptId($newsfeed["id"]);?>" method="post" >
				<div class="form-group">
					<label>Content <span class="text-danger">*</span></label>
					<textarea dir="auto" rows="3" maxlength="300" name="content" class="form-control" required 
						placeholder="What are you thinking?"> <?= $this->encodeHTML($newsfeed["content"]); ?></textarea>
					<p class="help-block"><em>The maximum number of characters allowed is <strong>300</strong></em></p>
				</div>
				<div class="form-group form-actions text-right">
					<button type='button' name='cancel' value='cancel' class="btn btn-sm btn-default"><i class="fa fa-times"></i> Cancel</button>
					<button type='submit' name='edit' value='edit' class="btn btn-sm btn-primary"><i class="fa fa-pencil"></i> Edit</button>
				</div>
			</form>
		</div>
	
				



		
 
