		
			<div dir='auto' class="panel-heading">
				<?php if(Session::getUserId() === (int) $post["user_id"] || Session::getUserRole() === "admin"){?>
					<div class="pull-right">
						<a href="<?= PUBLIC_ROOT . "Posts/View/" . urlencode(Encryption::encryptId($post["id"])) . "?action=update"; ?>">
							<button type="button" class="btn btn-default btn-circle edit"><i class="fa fa-pencil"></i></button>
						</a>
						<a href="<?= PUBLIC_ROOT . "Posts/delete/" . urlencode(Encryption::encryptId($post["id"])) . "?csrf_token=" . urlencode(Session::generateCsrfToken()); ?>">
							<button type="button" class="btn btn-danger btn-circle delete"><i class="fa fa-times"></i></button>
						</a>
					</div>
				<?php }?>
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
						<p dir="auto"><?= $this->autoLinks($this->encodeHTMLWithBR($post["content"]));?></p>
					</div>
					<!-- /.col-lg-6 (nested) -->
				</div>
				<!-- /.row (nested) -->
			</div>
			<!-- /.panel-body -->
