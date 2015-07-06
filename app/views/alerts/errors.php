	
	<div class='error'><div class='alert alert-danger'>
	
		<?php if(empty($errors)){ ?>
			
			<i class='fa fa-times-circle'></i> <strong>System Error!</strong>
			<br/><i class='fa fa-angle-right'></i>
				Oops! There was an error, Please try again later or report a bug
			</div></div>
			
		<?php } else{?>

			<i class='fa fa-times-circle'></i> <strong>Heads Up!</strong>
				<?php foreach((array)$errors as $error){?>
					<br/><i class='fa fa-angle-right'></i> 
					<?= $error; ?>
				<?php }?>
			</div></div>
		<?php }?>