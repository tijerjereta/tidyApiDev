<?php 
                $name = $producto[$i]['name'];
                $nameNext = $producto[$i + 1]['name'];

                $name = strtoupper( $name );
                $nameNext = strtoupper( $nameNext);

                $nameVariant = str_replace("'", "", $name);
                $nameVariantNext = str_replace("'", "", $nameNext);

                $name = str_replace("#", "n", $nameVariant);
                $nameNext = str_replace("#", "n", $nameVariantNext);

                // PROCCESSING & GROUPING PRODUCTS BY NAME
                $nameSizes = explode(' ', $name);
                $nameSizesNext = explode(' ', $nameNext);

                $nameWords = count( $nameSizes );
                $nameWordsNext = count( $nameSizesNext );

                if( $nameWords > 2) {
                    $ifSize = false; 
                    for( $n=0; $n < $nameWords; $n++ ) {
                        if( $nameSizes[$n] == 'SIZE' || $nameSizes[$n] == 'SZ' ){
                            $productSize = $nameSizes[$n + 1].' '.$nameSizes[$n + 2]; 
                            // $nameSizes[$n] = '';
                            // $nameSizes[ $n + 1 ] = '';
                            // $nameSizes [$n + 2 ] = '';
                            \array_splice($nameSizes, $n, 1);
                            \array_splice($nameSizes, $n, 1);
                            \array_splice($nameSizes, $n, 1);
                            $ifSize = true;
                        }
                        if( $ifSize != true) {
                            $numExist = false;
                            for($d=0; $d<strlen($nameSizes[$n]); $d++ ) {
                                if( is_numeric($nameSizes[$n][$d]) ) {
                                    $numExist = true;
                                    //$productMesuare = $nameSizes[$n];
                                }
                            }
                            if( $numExist == true) {
                                $mesuareSize[$n] = $nameSizes[$n];
                                \array_splice($nameSizes, $n, 1);
                                
                            }else{
                                $mesuareSize[$n] = '';
                            }
    
                        }
                    }
                    if( $ifSize != true ) {
                        $productSize = ' ';
                    }
                    if( $numExist == false ) {
                        
                    }
                }
                if( $nameWordsNext > 2) {
                    $ifSizeNext = false; 
                    for( $n=0; $n < $nameWordsNext; $n++ ) {
                        if( $nameSizesNext[$n] == 'SIZE' || $nameSizesNext[$n] == 'SZ' ){
                            $productSizeNext = $nameSizesNext[$n + 1].' '.$nameSizesNext[$n + 2]; 
                            // $nameSizes[$n] = '';
                            // $nameSizes[ $n + 1 ] = '';
                            // $nameSizes [$n + 2 ] = '';
                            \array_splice($nameSizesNext, $n, 1);
                            \array_splice($nameSizesNext, $n, 1);
                            \array_splice($nameSizesNext, $n, 1);
                            $ifSizeNext = true;
                        }
                        if( $ifSizeNext != true) {
                            $numExistNext = false;
                            for($d=0; $d<strlen($nameSizesNext[$n]); $d++ ) {
                                if( is_numeric($nameSizesNext[$n][$d]) ) {
                                    $numExistNext = true;
                                    //$productMesuare = $nameSizes[$n];
                                }
                            }
                            if( $numExistNext == true) {
                                $mesuareSizeNext[$n] = $nameSizesNext[$n];
                                \array_splice($nameSizesNext, $n, 1);
                                
                            }else{
                                $mesuareSizeNext[$n] = '';
                            }
    
                        }
                    }
                    if( $ifSizeNext != true ) {
                        $productSizeNext = ' ';
                    }
                    if( $numExistNext == false ) {
                        
                    }
                }
                $name = implode(" ", $nameSizes);
                $nameNext = implode(" ", $nameSizesNext);
                // echo 'New Name: '.$name;
                // echo '<br>Next Name: '.$nameNext;

                // echo "<br>'Variation: ".$productSize."<br>";
                if ( empty($mesuareSize) ) {
                   $mesSize = '';
                }else{
                    if( is_array($mesuareSize) ) {
                        $mesSize = implode(" ", $mesuareSize);
                    }else{
                        $mesSize = $mesuareSize;
                    }
                    // echo 'Variation: '.$mesSize."<br>";
                }
                if( $productSize == '' || $productSize == ' ' || $productSize == 'null' ){
                    if( is_array($mesuareSize) ) {
                        $mesSize = implode(" ", $mesuareSize);
                    }else{
                        $mesSize = $mesuareSize;
                    }
                    
                    $opts[] = trim($mesSize); 
                    $optUp = trim($mesSize);
                }else{
                    $opts[] = trim($productSize);
                    $optUp = trim($productSize);

                }