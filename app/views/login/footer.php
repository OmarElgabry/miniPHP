
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<!--<script src="<?= PUBLIC_ROOT; ?>js/jquery.min.js"></script>-->
		<script src="<?= PUBLIC_ROOT; ?>js/bootstrap.min.js"></script>
		<script src="<?= PUBLIC_ROOT; ?>js/sb-admin-2.js"></script>
		<script src="<?= PUBLIC_ROOT; ?>js/main.js"></script>

		<?php $this->controller->addVar('csrfToken', Session::generateCsrfToken()); ?>	
		<script>extract(<?= json_encode($this->controller->vars); ?>);</script>
		
		<?php Database::closeConnection(); ?>
	</body>
</html>
