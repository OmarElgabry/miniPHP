 

	<?php if(empty($posts)){ ?>
		<tr class='no-data'><td colspan='3' class='text-muted text-center'>There is no posts!</td></tr>
	
	<?php }else{
			foreach($posts as $post){?>
				<tr>
					<td style="width: 20%;"><strong><?= $post["user_name"];?></strong><br><em><?= $this->timestamp($post["date"]); ?></em><br></td>
					<td>
						<a href="<?= PUBLIC_ROOT . "Posts/View/" . urlencode(Encryption::encryptId($post["id"])); ?>">
							<strong><?= $this->truncate($this->encodeHTML($post["title"]),25); ?></strong>
						</a><br>
						<span class="text-muted"><?= $this->truncate($this->encodeHTML($post["content"]),30); ?></span>
					</td>
					<td class="text-center"><h5><strong class="text-primary"><?= $post["comments"]; ?></strong></h5></td>
				</tr>
	<?php   }
		}?>


		
