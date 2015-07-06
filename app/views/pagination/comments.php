<?php 

		$html = "";
		if(!empty($pagination)){
			$totalPages = $pagination->totalPages();
			$currentPage = $pagination->currentPage;

			if($totalPages > 1 && $pagination->hasNextPage()) {
				$html .= "<li class='media text-center'><a href='javascript:void(0)' class='btn btn-xs btn-primary push'>View more..</a></li>";
			}
		}
		echo $html;
?>