<?php 

		$html = "";
		
		if(!empty($pagination)){
		
			$totalPages = $pagination->totalPages();
			$currentPage = $pagination->currentPage;
			
			if($totalPages > 1) {
				// 1.
				if($pagination->hasPreviousPage()) {
					$html .= "<li><a href='javascript:void(0)' class='prev' ><i class='fa fa-angle-left'></i></a></li>";
				}
				
				// 2.
				$i = (($currentPage - 4) > 1)? ($currentPage - 4): 1;
				$end = (($currentPage + 4) < $totalPages)? ($currentPage + 4): $totalPages;
				for(; $i <= $end; $i++) {
					if($i == $currentPage) {
						$html .= "<li class='active'><a href='javascript:void(0)' >".$i."</a></li>";
					} else {
						$html .= "<li><a href='javascript:void(0)'>".$i."</a></li>";
					}
				}
				
				// 3.
				if($pagination->hasNextPage()) {
					$html .= "<li><a href='javascript:void(0)' class='next' ><i class='fa fa-angle-right'></i></a></li>";
				}
			}
		
		}
		
		echo $html;
		
?>