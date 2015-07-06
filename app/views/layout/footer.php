   
	<footer class="panel-footer clearfix">
		<div class="pull-right">
			Developed <i class="fa fa-heart text-danger"></i> by <a href="https://github.com/OmarElGabry/" target="_blank">Omar El Gabry</a>
		</div>
		<div class="pull-left">
			<?= date('Y');?> &copy; All rights reserved
		</div>
	</footer>
	
   </div>
	<!-- /#wrapper -->

		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<!--<script src="<?= PUBLIC_ROOT; ?>js/jquery.min.js"></script>-->
		<script src="<?= PUBLIC_ROOT; ?>js/bootstrap.min.js"></script>
		<script src="<?= PUBLIC_ROOT; ?>js/sb-admin-2.js"></script>
		<script src="<?= PUBLIC_ROOT; ?>js/main.js"></script>

		<?php $this->controller->addVar('csrfToken', Session::generateCsrfToken()); ?>	
		<script>extract(<?= json_encode($this->controller->vars); ?>);</script>
		
		<?php  if(!empty($this->controller->vars['globalPage'])): ?>
			
			<script>$(function(){ $(".sidebar-nav #"+(globalPage.constructor === Array? globalPage[0]: globalPage)+" a").addClass("active"); });</script>
			<script>$(document).ready(initializePageEvents());</script>

		<?php endif; ?>
		
		<?php Database::closeConnection(); ?>
	</body>
</html>
