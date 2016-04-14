<?php 

		$html = "";
		
		if(!empty($pagination)){
		
			$totalPages  = $pagination->totalPages();
			$currentPage = $pagination->currentPage;

			$linkExist   = empty($link)? false: true; 
			$url 		 = empty($link)? "": PUBLIC_ROOT . $link . "?page=";

			if($totalPages > 1) {
				// 1.
				if($pagination->hasPreviousPage()) {

					$link = ($linkExist == false)? "javascript:void(0)": $url . ($currentPage - 1);
					$html .= "<li><a href='" . $link  . "' class='prev' ><i class='fa fa-angle-left'></i></a></li>";
				}
				
				// 2.
				$i = (($currentPage - 4) > 1)? ($currentPage - 4): 1;
				$end = (($currentPage + 4) < $totalPages)? ($currentPage + 4): $totalPages;
				for(; $i <= $end; $i++) {

					$link = ($linkExist == false)? "javascript:void(0)": $url . ($i);

					if($i == $currentPage) {
						$html .= "<li class='active'><a href='" . $link . "' >".$i."</a></li>";
					} else {
						$html .= "<li><a href='" . $link . "'>".$i."</a></li>";
					}
				}
				
				// 3.
				if($pagination->hasNextPage()) {

					$link = ($linkExist == false)? "javascript:void(0)": $url . ($currentPage + 1);
					$html .= "<li><a href='" . $link . "' class='next' ><i class='fa fa-angle-right'></i></a></li>";
				}
			}
		
		}
		
		echo $html;
		
?>