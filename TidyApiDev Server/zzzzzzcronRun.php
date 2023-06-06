<?php 
    $cronNumStart = 0; 
    $cronNum = $canProd; 
    $canProdOne = round($canProd/8);
       $cronStart = $_GET['cronNumStart'];
    if( $cronStart == 0 ) {
        $cronNumStart = $cronStart;
        $cronNum = $canProdOne;
    }
      if( $cronStart == 1 ) {
        $cronNumStart = $canProdOne - 10; 
        $cronNum = $canProdOne * 2; 
    }
    if( $cronStart == 2 ) {
        $cronNumStart = ($canProdOne * 2) - 10; 
        $cronNum = $canProdOne * 3; 
    }
    if( $cronStart == 3 ) {
            $cronNumStart = ($canProdOne * 3) - 10; 
            $cronNum = $canProdOne * 4; 
    }
    if( $cronStart == 4 ) {
            $cronNumStart = ($canProdOne * 4) - 10; 
            $cronNum = $canProdOne * 5; 
    }
    if( $cronStart == 5 ) {
                $cronNumStart = ($canProdOne * 5) - 10; 
                $cronNum = $canProdOne * 6; 
    }
    if( $cronStart == 6 ) {
                $cronNumStart = ($canProdOne * 6) - 10; 
                $cronNum = $canProdOne * 7; 
    }
	if( $cronStart == 7 ) {
                $cronNumStart = ($canProdOne * 7) - 10; 
                $cronNum = $canProdOne * 8; 
    }
	if( $cronStart == 8 ) {
                $cronNumStart = ($canProdOne * 8) - 10; 
                $cronNum = $canProdOne * 9; 
    }
    echo "<h1>START: ".$cronNumStart." END: ".$cronNum."</h1>";