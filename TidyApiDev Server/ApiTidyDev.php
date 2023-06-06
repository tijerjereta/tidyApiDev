<?php
/*
Template Name: Tidy Api Call
Description: this is a custom page template which will use a third party API
  to pull a list of up to 100 items released on Netflix within the last 7 days.
*/
//This is used to tell the API what we want to retrieve
if ( is_plugin_active('TidyStockApi/TidyStockApi.php') ) {
//  function cronJobBene( $mensaje ) {	
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://user.tidystock.com/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_CONNECTTIMEOUT => 0,
        CURLOPT_TIMEOUT => 160000,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "host: user.tidystock.com",
          "Authorization: Basic xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"                  
        ),     
      ));
      set_time_limit(0);
      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);
      if ($err) {
        if ($debug) echo "cURL Error #:" . $err;
        else echo "<p>There's an error with TidyStock connection. This Site won't show updated information.</p>";
      } else {
            //Create an array of objects from the JSON returned by the API
            $jsonObj = json_decode($response, true);    
            // $jsonObj = explode(" ", $jsonObj); 
            //var_dump($response);
            //print_r($response); 
            $x = 0;
            $pricingCustom = array();
            foreach( $jsonObj as $attribute ) {
                $pricingCustom[$x] = $attribute['pricing'];
            $x++;    
            }
            //$productsTidy = explode('"}', $response); // SAVE EACH PRODUCT INTO AN ARRAY POS. 
            $productsTidy = explode('"updatedUtc"', $response);
            $productsTidy = str_replace("{", "", $productsTidy );
            $productsTidy = str_replace("}", "", $productsTidy );
            $productsTidy = str_replace("[", "", $productsTidy );
            $productsTidy = str_replace("]", "", $productsTidy ); 
            //$productsTidy = str_replace('ing":"ma', 'ingMa', $productsTidy );
            $canProd = count($productsTidy);
            if( $productsTidy[$canProd - 1] === '' ) {
                unset($productsTidy[$canProd - 1]);
                $canProd = count($productsTidy); 
            }
            echo "<p class='total-amount-p'><br>Total amount of 'TidyStock' products found: ".$canProd."<br></p><hr />";
            for( $i = 0; $i < $canProd; $i++ ) {
                //PROCCESING CUSTOMER PRICING DATA
                //END PROCCESING CUSTOMER PRICING DATA
                $productsTidy[$i] = explode(',"', $productsTidy[$i]); 
                $productCountProp = count($productsTidy[$i]);
                for( $e = 0; $e < $productCountProp; $e++ ) { 
                    $lines = explode('":', $productsTidy[$i][$e]);
                    $lines[0] = str_replace('"', "", $lines[0] ); // Quito las comillas para poder usar un array asociativo;
                    $lines[1] = str_replace('"', "", $lines[1] );
                    $producto[$i][$lines[0]] = $lines[1]; 
                }
            }
            global $wpdb;
		  // PHP LINES TO EREASE DATABASE PRODUCTS AND THEIR VARIATIONS!!! ============ 
		  
//       	$wpdb->query("ALTER TABLE $wpdb->posts DROP material_id");
//          $wpdb->query("ALTER TABLE $wpdb->posts DROP tidy_code");
// // 		//DELETE ATTRIB
// 		    $wpdb->query( $wpdb->prepare("DELETE FROM wp_terms WHERE term_id IN (SELECT term_id FROM wp_term_taxonomy WHERE taxonomy LIKE 'pa_%') ") );
// 		    $wpdb->query( $wpdb->prepare("DELETE FROM wp_term_taxonomy WHERE taxonomy LIKE 'pa_%' ") );
// 		    $wpdb->query( $wpdb->prepare("DELETE FROM wp_term_relationships WHERE term_taxonomy_id not IN (SELECT term_taxonomy_id FROM wp_term_taxonomy) ") );
// 		    //DELETE ALL WOOCOMMERCE PRODUCTS
// 		    $wpdb->query( $wpdb->prepare("DELETE FROM wp_term_relationships WHERE object_id IN (SELECT ID FROM wp_posts WHERE post_type IN ('product','product_variation'))") );
// 		    $wpdb->query( $wpdb->prepare("DELETE FROM wp_postmeta WHERE post_id IN (SELECT ID FROM wp_posts WHERE post_type IN ('product','product_variation'))") );
// 		    $wpdb->query( $wpdb->prepare("DELETE FROM wp_posts WHERE post_type IN ('product','product_variation')") );
// 		    //DELETE ALL OPHANED POSTMETA
//             $wpdb->query( $wpdb->prepare("DELETE pm FROM wp_postmeta pm LEFT JOIN wp_posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL") );

		  // END PHP LINES TO EREASE DATABASE PRODUCTS AND THEIR VARIATIONS!!! ============ 

	  		  
		    $opts = [];
            $producRprice = [];
            $skuProduct = []; 
            $productImgVar = [];
            $arrayProductCode = [];
            $stockStatusArray = [];
		    include(get_stylesheet_directory().'/TidyApiDev/cronRun.php'); 		
            for( $i = $cronNumStart; $i < $cronNum; $i++ ) {
//              echo "<br>".$i."<br>".$producto[$i]['name']."<br>MI:".$producto[$i]['materialItemId']."<br>";
//              " ".$producto[$i]['name']."<br>MI:".$producto[$i]['materialItemId']."<br>";
// 				$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->posts WHERE post_title = 'GRISPORT DAKAR HERCULES BROWN' ") );
// 				$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->posts WHERE material_id = '$mib' ") );
// 				if( $producto[$i]['materialItemId'] == '6f5f2e59-4507-407e-be5e-6dacd70a0726' ) {
// 					echo "elimina MI";
// 					$mib = $producto[$i]['materialItemId'];
// 					$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->posts WHERE material_id = '$mib' ") );
// 				}
	
				$bardCode = $producto[$i]['barcode']; 
                $bardCode = strtoupper($bardCode); 
                if( $bardCode != 'N' ) {
                    $row = $wpdb->get_results(  "SELECT tidy_code FROM $wpdb->posts WHERE table_name = 'wp_posts' AND column_name = 'tidy_code'"  );
                    if(empty($row)){
                        $wpdb->query("ALTER TABLE $wpdb->posts ADD tidy_code VARCHAR(20) NULL");
                    }
                    $rowS = $wpdb->get_results(  "SELECT material_id FROM $wpdb->posts WHERE table_name = 'wp_posts' AND column_name = 'material_id'"  );
                    if(empty($rowS)){
                        $wpdb->query("ALTER TABLE $wpdb->posts ADD material_id VARCHAR(80) NOT NULL DEFAULT 1");
                    }
                    $materialId = $producto[$i]['materialItemId'];
                    $codeUniq = explode('-', $producto[$i]['code']);
                    $uniqueCode = $codeUniq[0];
                    $desc = $producto[$i]['description'];      
                    if( $desc == '' || $desc == ' ' || $desc == null || $desc == 'null' ) {
                        $desc = "Product Code ".$producto[$i]['code']." Description";
                    }
                    include(get_stylesheet_directory().'/TidyApiDev/nameProccesing.php');   
                    $regVariationPrice[] = $producto[$i]['charge']; 
                    $strVarPrices = implode(' ', $regVariationPrice);
                    $productAtrib = [
                        [
                            'name' => 'Size',
                            'slug'=> 'size',
                            'position' => 0,
                            'visible' => true,
                            'variation' => true,
                            'options' => $opts,
                        ]
                    ];
                    // END PROCCESSING & GROUPING PRODUCTS BY NAME
                    $productPrice = $producto[$i]['charge']; //cost
                    if( $productPrice == '' || $productPrice == ' ' || $productPrice == 'null' || $productPrice == 'Null' ) {
                        $productPrice = 0;
                    }
                    $sku = $producto[$i]['sku'];
                    $skuSize = strlen($sku);
                    if( $skuSize > 2 ) {
                    	$sku = explode(',', $sku);
                      	for($f=0; $f < count($sku); $f++) {
							$sku[$f] = strtoupper($sku[$f]);
							$sku[$f] = trim($sku[$f]);
							switch ($sku[$f]) {
								case "F":
									$sku[$f] = 120;
								break;
								case "H":
									$sku[$f] = 121;
								break;
								case "MR":
									$sku[$f] = 122;
								break;
								case "SR":
									$sku[$f] = 123;
								break;
								case "SS":
									$sku[$f] = 124;
								break;
								case "W":
									$sku[$f] = 125;
								break;
								case "A":
									$sku[$f] = 126;
								break;
								case "B":
									$sku[$f] = 127;
								break;
								case "C":
									$sku[$f] = 128;
								break;
								case "M":
									$sku[$f] = 129;
								break; 
								case "E":
									$sku[$f] = 130;
								break;
								case "O":
									$sku[$f] = 131;
								break;
								case "S":
									$sku[$f] = 132;
								break;                            
								default:
									$sku[$f] = 187;
							}
						}   
                	}else{    
                    	$skuStr = strtoupper($sku);
                        $skuStr = trim($skuStr);
                        switch ( $skuStr ) {
                            case "F":
                                $skuStr = 120;
                            break;
                            case "H":
                                $skuStr = 121;
                            break;
                            case "MR":
                                $skuStr = 122;
                            break;
                            case "SR":
                                $skuStr = 123;
                            break;
                            case "SS":
                                $skuStr = 124;
                            break;
                            case "W":
                                $skuStr = 125;
                            break;
                            case "A":
                                $skuStr = 126;
                            break;
                            case "B":
                                $skuStr = 127;
                            break;
                            case "C":
                                $skuStr = 128;
                            break;
                            case "M":
                                $skuStr = 129;
                            break; 
                            case "E":
                                $skuStr = 130;
                            break;
                            case "O":
                                $skuStr = 131;
                            break;
                            case "S":
                                $skuStr = 132;
                            break;                            
                            default:
                            $skuStr = 187;
                        } 
                	}
                    if( $producto[$i]['stockLevel'] > 0 ) {
                        $stockStatus = 'instock';
                    }else{
                        $stockStatus = 'outofstock';
                    }
                    $product_name = $wpdb->get_var(
                        $wpdb->prepare("SELECT post_title FROM $wpdb->posts WHERE post_title='$name' AND post_content='$desc' AND post_status='publish' LIMIT 1")
                    );
                    $product_code = $wpdb->get_var(
                        $wpdb->prepare("SELECT tidy_code FROM $wpdb->posts WHERE tidy_code = '$uniqueCode' LIMIT 1")
                    );
                    $product_id = $wpdb->get_var(
                        $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE material_id = '$materialId' AND post_status='publish' LIMIT 1")
                    );
                    $material_id = $wpdb->get_var(
                        $wpdb->prepare("SELECT material_id FROM $wpdb->posts WHERE material_id = '$materialId' /* AND post_status='publish' */ LIMIT 1")
                    );
                    $normal_p = $wpdb->get_var(
                        $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id='$product_id' AND meta_key='_price' LIMIT 1")
                    );
                    $regular_p= $wpdb->get_var(
                        $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id='$product_id' AND meta_key='_regular_price' LIMIT 1")
                    );
                    if( $skuSize > 2 ) {
                        $categSku = [
                            [
                                'id' => $sku[0]  
                            ],
                            [
                                'id' => $sku[1]  
                            ],
                            [
                                'id' => $sku[2]  
                            ],
                            [
                                'id' => $sku[3]  
                            ],
                            [
                                'id' => $sku[4]  
                            ],
                            [
                                'id' => $sku[5]  
                            ],
                            [
                                'id' => $sku[6]  
                            ],
                            [
                                'id' => $sku[7]  
                            ],
                            [
                                'id' => $sku[8]  
                            ],
                            [
                                'id' => $sku[9]  
                            ],
                            [
                                'id' => $sku[10]  
                            ],
                            [
                                'id' => $sku[11]  
                            ],
                            [
                                'id' => $sku[12]  
                            ]

                            ];
                    }else{
                        $categSku = [
                            [
                                'id' => $skuStr  
                            ]
                            ];  
                    }
                    $sitePath = get_bloginfo('wpurl');
                    $imgUrl = $producto[$i]['imagePath'];
                    $imgUrlA = $producto[$i - 1]['imagePath'];
                    $post_idi = $product_id; 
                    if( $imgUrl == '' || $imgUrl == ' ' ) {
                        $imagesUpl = [];
                    }else{
                        if( $imgUrl == $imgUrlA  && $product_id != '' && $product_id != ' ' && $product_id != 'null' ) { // CHEQUEAR ESTA LOGICA, PUEDE SER QUE HAYAN PRODUCTOS CON MISMA FOTO EN DISTINTO ORDEN !!!! 
                            $attachmentss = get_posts( array(
                                'post_type' => 'attachment',
                                'number_posts' => 1,
                                'post_status' => null,
                                'post_parent' => $post_idi,
                                'orderby' => 'post_date',
                                'order' => 'DESC',) 
                            );
                            $imageIDi = $attachmentss[0]->ID;
                            // IMAGE OBJECT CREATED BY ID 
                            $imagesUpl = [
                                [
                                    'id' => $imageIDi, 
                                    'position' => 0
                                ]
                            ];
                        }else{    
                            // IMAGE OBJECT CREATED BY SRC (SO IS A NEW IMAGE)!  
                            $imagesUpl = [
                                [
                                    'src' => $imgUrl,
                                    'position' => 0
                                ]
                            ];
                        }
                    }
                    $producRprice[] = $producto[$i]['charge'];
                    $skuProductTrim = trim($producto[$i]['code']);
                    $finalSku = str_replace('.','', $skuProductTrim);
                    $finalSku = str_replace('(','', $finalSku);
                    $finalSku = str_replace(')','', $finalSku);
                    $finalSku = str_replace('-','', $finalSku);
                    $finalSku = str_replace('/','', $finalSku);
                    $finalSku = trim($finalSku);
                    $skuProduct[] = $finalSku;
                    $arrayProductCode[] = $finalSku;
                    
                    $generalSkuPro = explode('-', $producto[$i]['code']);
                    $generalSku = implode('', $generalSkuPro);
                    $generalSku = str_replace('-','', $generalSku);
                    $generalSku = trim($generalSku);
					
                    $stockStatusArray[] = $stockStatus;

                    if( $generalSku == '' || $generalSku == ' ' || $generalSku == 'null') {
                        $generalSku = '-';
                    }
                    $productImgVar[] =  $imagesUpl;   
                    $productVariations = [ 
                            'image' => $productImgVar,
                            'regular_price' => $producRprice,  
                            'sku' => $skuProduct,
                            'statusVar' => $stockStatusArray,
                            'attributes' => [ 
                                [
                                    'slug'=>'size',
                                    'name'=>'Size',
                                    'option'=> $opts
                                ]
                            ] 
                    ];   
                    $tgName = $producto[$i]['materialCategoryName'];  
                    $tgName = trim($tgName);
                    $tgName = strtoupper($tgName);
                    if( $tgName == '' || $tgName == ' ' || $tgName == 'null' ) {
                        $tagName = "Accessories";
                    }else{
                        $tagName = $tgName;
                    }
                    $tag = [
                        [
                            'id' => $product_id, 
                            'name' => $tagName,
                            'slug' => $tagName,
                        ]
                    ];  
                    $metaKey = '_custom_pricing';
                    $pricingCustomStrn = $pricingCustom[$i];
                    $pricingCustomStrn = json_encode($pricingCustomStrn);    
					
					if( !$material_id  ) { //&& $name != 'FENICE CREAM BULK' 
						 //echo "<br> Entra a MI!!!!!!<br>";
						if( $name != $nameNext ) {
							//echo "<br>Entra a Name!!!<br>";	
                            // CHECK IF NAME ALREADY EXIST, IF IS TRUE THEN WE SAVE THE RECORD AS A VARIATION
                          
							$productRecordname = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM $wpdb->posts WHERE post_status='publish' AND post_title = '$name' LIMIT 1"));
                            if( $productRecordname ) {
								echo "<br>".$producto[$i]['name']." Entra a Name<br>";
                                // CREATES A NEW VARIATION RECORD FROM TIDY
                                // echo "<br>Entra a New Variation Product <br>"; 
                                include(get_stylesheet_directory().'/TidyApiDev/newVariationProduct.php'); 
                            }else{ 
                                $codeAmount = count($arrayProductCode); 
                                if( $codeAmount > 1 ) {
                                    $typeData = 'variable';
                                }else{
                                    $typeData = 'simple';
                            	}
                                //var_dump($generalSku);
								$data = [
                                    'name' => $name,
                                    'regular_price' => $productPrice, //$producto[$i]['cost'],
                                    //'sku' => $producto[$i]['sku'],
                                    'sku' => "'".$generalSku."'",
                                    'type' => $typeData, //simple or variable
                                    'description' => $desc,
                                    'short_description' => $desc,
                                    'stock_status'  => '$stockStatus',
                                    //'stock_quantity' => $producto[$i]['stockLevel'],
                                    'categories' => $categSku,
                                    'images' => $imagesUpl,
                                    'tags' => $tag,
                                    'attributes' => $productAtrib, 
                                    'variations' => $productVariations, 
                                ];
                                $wp_rest_request = new WP_REST_Request( 'POST' );
                                $wp_rest_request->set_body_params( $data );
                                $products_controller = new WC_REST_Products_Controller;
                                $res = $products_controller->create_item( $wp_rest_request );
                                $res = $res->data;
								if( $res ) {
									echo "<br>-New Product Added: ".$name."<br>";
								}
// 								else{
// 									echo "<br><br><br><br>Not Saved:<br><br><br><br>";
// 									print_r($name);
// 									echo "<br> Product price: ";
//                                     print_r($productPrice);
// 									echo "<br>";
// 									echo "General SKU: ";
//                                     print_r($generalSku);
// 									echo "<br>";
// 									echo "Tipo: ";
//                                     print_r($typeData);
//                                     echo "<br>";
// 									echo "Descripcion: ";
// 									print_r($desc);
// 									echo "<br>";
// 									echo "Stock Stauts: ";
//                                     print_r($stockStatus);
// 									echo "<br>";
// 									echo "Cat SKU: ";
// 									print_r($categSku);
// 									echo "<br>";
// 									echo "Imagenes: ";
//                                     print_r($imagesUpl);
// 									echo "<br>";
// 									echo "Tags: ";
//                                     print_r($tag);
// 									echo "<br>";
// 									echo "Product Atribut: ";
// 									print_r($productAtrib);
// 									echo "<br>";
// 									echo "Product Variations: ";
//                                     print_r($productVariations);
// 									echo "<br>";
// 									echo "<br>";
// 									echo "<br>";
// 								}
                                // ADD THE TIDY PRODUCT CODE, TO BE USED AS AN UNIQUE CODE THAT ALWAYS IS SENT BY TIDY.
                                //$wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET tidy_code = '$uniqueCode' ") );
                                $wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET tidy_code = '$uniqueCode' WHERE post_title='$name' ") );
                                // ADD THE TIDY MATERIAL ID CODE, TO BE USED AS AN UNIQUE CODE THAT ALWAYS IS SENT BY TIDY.
                                $wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET material_id = '$materialId' WHERE post_title='$name' ") );
// 								if( !( $wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET material_id = '$materialId' WHERE post_title='$name' AND post_content='$desc'  ")) ) ) {
							      	
// 									echo "<br>No guarda MATERIAL ID: ".$materialId." Del producto: ".$producto[$i]['name']."<br>";
// 									$wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET material_id = '$materialId' WHERE post_title='$name' AND post_content='$desc'  ") );
// 									$wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET material_id = '$materialId' WHERE post_title='$name' ") );
									
// 								}else{
// 									echo "<br>GUARDA MI!<br>";
// 								}
                                // The created product must have variations
                                // If it doesn't, it's the new WC3+ API which forces us to build those manually

                                //CREATE A CODE COUNTER TO BE USED AS A COUNTER WHEN WE CREATE THE VARIATIONS
                                if ( !isset( $res['variations'] ) ){
                                    $res['variations'] = array();
                                }
                                if ( count( $res['variations'] ) == 0 && count( $data['variations'] ) > 0 ) {
                                    if ( ! isset( $variations_controler ) ) {
                                        $variations_controler = new WC_REST_Product_Variations_Controller();
                                    }                                 
                                        $arrayAtrib = $data['variations']['regular_price'];
                                        $skuProductArrat = $data['variations']['sku']; 
                                        $productAtribNew = $data['variations']['attributes'];
                                        $productAtrImg = $data['variations']['image'];
                                        $statusVa = $data['variations']['statusVar']; 
                                        for ($r=0; $r<$codeAmount; $r++ ) {
                                            $skuProductArrat[$r] = "'".$skuProductArrat[$r]."'";
                                            $wp_rest_request = new WP_REST_Request( 'POST' );
                                            $variation_rest = array(
                                                'product_id' => $res['id'],
                                                // 'regular_price' => $strVarPrices,
                                                'regular_price' => $arrayAtrib[$r],
                                                'sku' => $skuProductArrat[$r], //.'.'
                                                'stock_status' => $statusVa[$r],
                                                //'regular_price' => $variation['regular_price'],
                                                //'regular_price' => $producto[$i]['charge'],
                                                'attributes' => [ 
                                                    [
                                                        'slug'=>'size',
                                                        'name'=>'Size',
                                                        'option'=> $opts[$r],
                                                    ]
                                                ],
                                                'image' => $productAtrImg[$r][0],
                                            );
                                            // EL ERROR PUEDE QUE SE DEBA A RECORRER ESTE UCLE CONTANDO EL ARRAY DE PRECIOS $ARRAYATRIB....
                                            $wp_rest_request->set_body_params( $variation_rest );
                                            $new_variation = $variations_controler->create_item( $wp_rest_request );
                                            $res['variations'][] = $new_variation->data;
                                        }
                                } 
                                // CLEAN PRODUCT TAG NAME STRING
                                $tgName = '';
                                //CLEAN ARRAY TO NEXT PRODUCT DATA (THIS SHOULD BE AT THE END OF ADDING A NEW PRODUCT) ==================
                                $mesuareSize = array();
                                //CLEAN ARRAY TO NEXT PRODUCT DATA (THIS SHOULD BE AT THE END OF ADDING A NEW PRODUCT) ==================
                                // RESET VARIATIONS AND ATTRIBUTES //
                                $nOpts = [];
                                $opts = [];
                                $producRprice = [];
                                $skuProduct = [];
                                $productImgVar = [];
                                $imagesUpl = [];
                                $arrayProductCode = []; 
                                $skuProductArrat = [];  
                                $stockStatusArray = [];
                                $statusVa = [];
                                $categSku = [];
                                $tag = [];
                                $productAtrib = [];
                                $productVariations = [];

                                // END RESET VARIATIONS AND ATTRIBUTES //
                            }
                        
                        }    
                        // GET THE ID OF THE PRODUCT JUST RECIENTLY CREATED AND SAVE THE CUSTOM PRICING ARRAY
                        $product_id = $wpdb->get_var(
                            $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE material_id = '$materialId' AND post_status='publish' LIMIT 1")
                        );
                        if( $product_id != '' & $product_id != ' ' && $product_id != 'null' && $product_id != 0 ) {
                            update_post_meta($product_id , $metaKey, $pricingCustomStrn);                     
                        }
                    }else{ 
						echo "<br>-PRODUCT CHECKED:  ".$name." | TIDY RECORD N:".$i;
                        include(get_stylesheet_directory().'/TidyApiDev/productsUpdate.php'); 

                    } // END IF CREATING A PRODUCT
                }else{
                    // IS N
                    // IF N IS TRUE AND THE MI/NAME EXIST, EREASE THE PRODUCT FROM WP
                    if( $bardCode == 'N'  ) { 
                        include(get_stylesheet_directory().'/TidyApiDev/nameProccesing.php');   
                        $product_name = $wpdb->get_var(
                            $wpdb->prepare("SELECT post_title FROM $wpdb->posts WHERE post_title='$name' AND post_status='publish' LIMIT 1")
                        );
                        if( $product_name ){
                            echo "<br>-Product: '".$name."' Deleted!<br>";
                            $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->posts WHERE post_title = '$name' ") ); 
                        }
                        //echo "<br>MI: ".$material_id."<br>";
                        //$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->posts WHERE material_id = '$material_id' ") );
                    	// RESET VARIATIONS AND ATTRIBUTES //
                                $nOpts = [];
                                $opts = [];
                                $producRprice = [];
                                $skuProduct = [];
                                $productImgVar = [];
                                $imagesUpl = [];
                                $arrayProductCode = []; 
                                $skuProductArrat = [];  
                                $stockStatusArray = [];
                                $statusVa = [];
                                $categSku = [];
                                $tag = [];
                                $productAtrib = [];
                                $productVariations = [];
                                // END RESET VARIATIONS AND ATTRIBUTES //
					}
                }//END IF BARCODE != N; (Don't process any PRODUC with N value).    
            } // END FOR - RECORRIENDO PRODUCTO POR PRODUCTO 
        } // IF API CONNECTION IS NOT WRONG ====================
        include(get_stylesheet_directory().'/TidyApiDev/countAndDelete.php');  
	    $mensaje = "FINAl";
    	wp_mail("testsysbene@gmail.com",$cronNumStart." - ".$cronNum,$mensaje);
//  }//END CRONJOB FUNCTION 
}else{
	echo "<h6>TidyStockApi Disable for the moment.</h6>";
}     
?>
