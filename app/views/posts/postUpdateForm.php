
		<div dir='auto' class="panel-heading">
			<h5><?= $post["title"]; ?></h5>
		</div>
		
		<div class="panel-body">
			<div class="row">
				<div class="col-lg-12">
					<table class="table table-borderless table-vcenter remove-margin-bottom">
						<tbody>
							<tr>
								<td class="text-center" style="width: 80px;">
									<img src="<?= PUBLIC_ROOT . "img/profile_pictures/" . $post["profile_picture"]; ?>" alt="User Picture" class="img-circle profile-pic-sm">
								</td>
								<td>
									By <strong class="text-primary"><?= $post["user_name"]; ?></strong><br>
									<strong><?= $this->timestamp($post["date"]);?></strong>
								</td>
							</tr>
						</tbody>
					</table>
					<hr>
					<form action="<?php echo PUBLIC_ROOT; ?>Posts/update" id='form-update-post' method="post">
						<div class="form-group">
							<label>Title</label>
							<input dir="auto" type="text" name="title" value = "<?= $this->encodeHTML($post["title"]); ?>" class="form-control" required maxlength="80" placeholder="Title">
						</div>
						<div class="form-group">
							<label>Content</label>
							<textarea dir="auto" rows="20" maxlength="1800" name="content" class="form-control" required > <?= $this->encodeHTML($post["content"]); ?></textarea>
							<p class="help-block"><em>The maximum number of characters allowed is <strong>1800</strong></em></p>
						</div>
						<div class="form-group">
                            <input type="hidden" name="post_id" value="<?= $this->encodeHTML(Encryption::encryptId($post["id"])); ?>" />
                        </div>
						<div class="form-group">
                            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken(); ?>" />
                        </div>
						<div class="form-group form-actions text-right">
							<a href="<?= PUBLIC_ROOT . "Posts/View/" . urlencode(Encryption::encryptId($post["id"])); ?>">
								<button type='button' name='cancel' value='cancel' class="btn btn-md btn-default"><i class="fa fa-times"></i> Cancel</button>
							</a>
							<button type='submit' name='submit' value='edit' class="btn btn-md btn-primary"><i class="fa fa-pencil"></i> Edit</button>
						</div>
					</form>
					<?php 
						if(!empty(Session::get('posts-errors'))){
							echo $this->renderErrors(Session::getAndDestroy('posts-errors'));
						}
					?>
				</div>
				<!-- /.col-lg-6 (nested) -->
			</div>
			<!-- /.row (nested) -->
		</div>
		<!-- /.panel-body -->

			
