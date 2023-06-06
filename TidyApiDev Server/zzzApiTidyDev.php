<?php
/*
Template Name: Tidy Api Call
Description: this is a custom page template which will use a third party API
  to pull a list of up to 100 items released on Netflix within the last 7 days.
*/
//This is used to tell the API what we want to retrieve


//Show the header of your WordPress site so the page does not look out of place
// get_header();
// ?>

  <!-- <div id="primary" class="content-area"> -->
    <!-- <main id="main" class="site-main"> -->

    <?php
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://benefootwear3.tidystock.com//api/materialitems?IncludeImages=True&IncludePricing=True",
                      //https://benefootwear3.tidystock.com//api/materialitems?IncludeImages=True
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_CONNECTTIMEOUT => 0,
        CURLOPT_TIMEOUT => 6000,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "host: benefootwear3.tidystock.com",
          "Authorization: Basic UGYrMlFsQ2FLa2pzaTBHemsyb29tTWo4cFB5Ti94K1FscWwxajM1VjNPUT06"                  
        //   "u: 5peEHsF40xvlZrX6ILg9EDpTztPPeYu8d4WJ5jpRde0=ftyygyytfgfyt"
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
            //print_r($productsTidy); 
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
            echo "<p class='total-amount-p'><br>Total amount of 'TidyStock' products found: ".$canProd."<br></p>";
            // var_dump($productsTidy);
            for( $i = 0; $i < $canProd; $i++ ) {
                //PROCCESING CUSTOMER PRICING DATA
                // $productsTidyPricing[$i] = explode('"pricing":', $productsTidy[$i]);
                // $productsTidySplit = explode(',"locationStockLevels"', $productsTidyPricing[$i][1]);
                // $productsTidyPricing[$i] = $productsTidySplit[0];
                //END PROCCESING CUSTOMER PRICING DATA
                $productsTidy[$i] = explode(',"', $productsTidy[$i]); 
                $productCountProp = count($productsTidy[$i]);
                // print_r("<br>".$productCountProp."<br>");
                for( $e = 0; $e < $productCountProp; $e++ ) { 
                    $lines = explode('":', $productsTidy[$i][$e]);
                    $lines[0] = str_replace('"', "", $lines[0] ); // Quito las comillas para poder usar un array asociativo;
                    $lines[1] = str_replace('"', "", $lines[1] );
                    $producto[$i][$lines[0]] = $lines[1]; 
                }
            }
            global $wpdb;
            // $wpdb->query("ALTER TABLE $wpdb->posts DROP material_id");
            // $wpdb->query("ALTER TABLE $wpdb->posts DROP tidy_code");
            $opts = [];
            $producRprice = [];
            $skuProduct = []; 
            $productImgVar = [];
            $arrayProductCode = [];
            $stockStatusArray = [];
            for( $i = 840; $i < 1060;/*$canProd; */ $i++ ) { 
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
                    // $imagesUpl = [
                    //     [
                    //         'id' => 5190, // REPLACE WITH IMAGE ID // http://tidyb.local/wp-content/uploads/woocommerce-placeholder-600x600.png http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_4_front.jpg
                    //         'position' => 0
                    //     ]
                    // ];

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
                                'id' => $imageIDi, // http://tidyb.local/wp-content/uploads/woocommerce-placeholder-600x600.png http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_4_front.jpg
                                'position' => 0
                            ]
                        ];
                    }else{    
                        // IMAGE OBJECT CREATED BY SRC (SO IS A NEW IMAGE)!  
                        $imagesUpl = [
                            [
                                'src' => $imgUrl, // http://tidyb.local/wp-content/uploads/woocommerce-placeholder-600x600.png http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_4_front.jpg
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
                //$generalSku = $finalSku;


                $stockStatusArray[] = $stockStatus;

                if( $generalSku == '' || $generalSku == ' ' || $generalSku == 'null') {
                    $generalSku = '-';
                }
                $productImgVar[] =  $imagesUpl;   
                //$productR = implode(" ", $producRprice);
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
                $tgName = '';
                    for( $s=0; $s<2;$s++ ) {
                        $tgName = trim($tgName);
                        $tgName = $tgName.$generalSku[$s];
                    }
                    $tgName = trim($tgName);
                    $tgName = strtoupper($tgName);
                    switch ( $tgName  ) {
                        case "GR":
                            $tagName = "Grisport";
                        break;
                        case "AN":
                            $tagName = "Andrew";
                        break;
                        case "CR":
                            $tagName = "Crispi";
                        break;                           
                        default:
                        $tagName = "Accessories";
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
                if( !$material_id ) {
                    if( $name != $nameNext ) {
//                  

                           echo "<br>No Es el mismo<br>";
//                         echo "<br>-Product Added: ";
//                         print_r($name);
//                         echo "CODIGO UNICO: ";
//                         echo $uniqueCode;
//                         echo "<br>";
//                         echo "<br>";
//                         print_r($product_id);
//                         echo "<br>";
//                         echo "STOCK: ".$stockStatus."<br>";
//                         echo "<br>ID: ";
//                         echo $post_idi;
//                         echo "<br>IMG IDi: ";
//                         print_r($imageIDi);
//                         echo "ATTACHMEN DE ID: ";
//                         print_r($attachmentss[0]);
//                         echo "<br>";
// 						print_r($productPrice);
// 						echo "<br>";
                        //simple variable
                        

                        // echo "<br>";
                          
                        $codeAmount = count($arrayProductCode); 
                        if( $codeAmount > 1 ) {
                            $typeData = 'variable';
                        }else{
                            $typeData = 'simple';
                        }
                        // Si no existe Agrego el Producto en la Base de Datos de WP y WC.
                        $data = [
                            'name' => $name,
                            'regular_price' => $productPrice, //$producto[$i]['cost'],
                            //'sku' => $producto[$i]['sku'],
                            'sku' => $generalSku,
                            'type' => $typeData, //simple or variable
                            'description' => $desc,
                            'short_description' => $desc,
                            'stock_status'  => $stockStatus,
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
                            
                            echo 'LO CREA --------------------<br>';
                             echo "Nombre: ";
                             print_r($name);
                             echo "<br>";
                              


                            
                            echo "Precio: ";
                            print_r($productPrice);
                            echo "<br>";
                            echo "SKU: ";
                            print_r($generalSku);
                            echo "<br>";
                            echo "Descripcion: ";
                            print_r($desc);
                            echo "<br>";
                            echo "Descripcion: ";
                            print_r($desc);
                            echo "<br>";
                            echo "Stock Status: ";
                            print_r($stockStatus);
                            echo "<br>";
                            echo "Categorias: ";
                            print_r($categSku);
                            echo "<br>";
                            echo "Imagenes: ";
                            print_r($imagesUpl);
                            echo "<br>";
                            echo "Atributos: ";
                            print_r($productAtrib);
                            echo "<br>";
                            echo "Variaciones: ";
                            print_r($productVariations);
                            echo "<br>";
                        }else{
                            echo 'NO LO CREA------------<br>';
                            echo "Nombre: ";
                            print_r($name);
                            echo "<br>";
                            // echo "Precio: ";
                            // print_r($productPrice);
                            // echo "<br>";
                            // echo "SKU: ";
                            // print_r($generalSku);
                            // echo "<br>";
                            // echo "Descripcion: ";
                            // print_r($desc);
                            // echo "<br>";
                            // echo "Descripcion: ";
                            // print_r($desc);
                            // echo "<br>";
                            // echo "Stock Status: ";
                            // print_r($stockStatus);
                            // echo "<br>";
                            // echo "Categorias: ";
                            // print_r($categSku);
                            // echo "<br>";
                            // echo "Imagenes: ";
                            // print_r($imagesUpl);
                            // echo "<br>";
                            // echo "Atributos: ";
                            // print_r($productAtrib);
                            // echo "<br>";
                            // echo "Variaciones: ";
                            // print_r($productVariations);
                            // echo "<br>";

                        }


                        // ADD THE TIDY PRODUCT CODE, TO BE USED AS AN UNIQUE CODE THAT ALWAYS IS SENT BY TIDY.
                        //$wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET tidy_code = '$uniqueCode' ") );
                        $wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET tidy_code = '$uniqueCode' WHERE post_title='$name' AND post_content='$desc'  ") );
                        // ADD THE TIDY MATERIAL ID CODE, TO BE USED AS AN UNIQUE CODE THAT ALWAYS IS SENT BY TIDY.
                        $wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET material_id = '$materialId' WHERE post_title='$name' AND post_content='$desc'  ") );
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
                            //foreach ( $data['variations'] as $variation ) {
                            // echo "COUNT DATA VARIATIONS: ".count($data['variations']);
                            // echo "<br>";
                            // echo "<br>"; 
                           // for($d=0; $d < count($data['variations']); $d++ ) {  
                                $arrayAtrib = $data['variations']['regular_price'];
                                $skuProductArrat = $data['variations']['sku']; 
                                $productAtribNew = $data['variations']['attributes'];
                                $productAtrImg = $data['variations']['image'];
                                $statusVa = $data['variations']['statusVar']; 
                                // echo "COUNT ARRAYAtrib: ".count($arrayAtrib)."<br>";
                                // echo "VARIATION REGULAR PRICE ARRAY: <br>";
                                // print_r($arrayAtrib);
                                // echo "<br>";
                                // echo "<br>"; 
                                // echo "CANTIDAD DE CHILD PRODUCTS DADOS POR EL CODIGO: ".count($arrayProductCode)."<br>";
                                // print_r($arrayProductCode);
                                // echo "<br>";
                                // echo "OPTS: ";
                                // print_r($opts[$r]);
                                // echo "<br>";
                                
                                
                                for ($r=0; $r<$codeAmount; $r++ ) {
                                    // echo "<br>".$name."<br>";
                                    // echo "ANTES DEL AGREGADO<br>";
                                    // var_dump($skuProductArrat[$r]);
                                    $skuProductArrat[$r] = "'".$skuProductArrat[$r]."'";
                                    // echo "DESPUES DEL AGREGADO<br>";
                                    // var_dump($skuProductArrat[$r]);



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
                                    //  "Product ID: ";
                                    // print_r($res['id']);
                                    // echo "<br>";
                                    // echo "SKU: <br>";
                                    // print_r($skuProductArrat[$r]);
                                    // var_dump($skuProductArrat[$r]);
                                    // echo "<br>";
                                    // echo "EL Array precio: <br>";
                                    // echo print_r($arrayAtrib[$r]);
                                    // echo "<br>";
                                    // echo "Imagen ARRAY: <br>";
                                    // print_r($productAtrImg[$r][0]);
                                    // echo "<br>";echo
                                    // echo "OPTS A VERRRRR: <br>";
                                    // print_r($opts[$r]);
                                    // // echo "<br><br>";
                                    // echo "OPTS: ";
                                    // print_r($opts);
                                    // echo "<br>";
                                    // echo "Imagen: <br>";
                                    // print_r($productAtrImg[$r][0]);
                                    // echo "<br>";
            
                                    // echo "PRICE <br>";
                                    // print_r($arrayAtrib[$r]);
                                    // echo "<br>";
                                    // echo "SKU: <br>";
                                    // print_r($skuProductArrat[$r]);
                                    // echo "Status: <br>";
                                    // print_r($statusVa[$r]);
                                    // echo "<br>";




                                    $wp_rest_request->set_body_params( $variation_rest );
                                    $new_variation = $variations_controler->create_item( $wp_rest_request );
                                    $res['variations'][] = $new_variation->data;

//                                     if( $res['variations'][] = $new_variation->data) {
//                                         echo "crea la variacion ".$r."<br>";
//                                     }else{
//                                         echo "NO crea la variacion ".$r."<br>";
//                                     }


                                }
                           // }
                        } 
                        
                        // CLEAN PRODUCT TAG NAME STRING
                        $tgName = '';
                        //CLEAN ARRAY TO NEXT PRODUCT DATA (THIS SHOULD BE AT THE END OF ADDING A NEW PRODUCT) ==================
                        $mesuareSize = array();
                        //CLEAN ARRAY TO NEXT PRODUCT DATA (THIS SHOULD BE AT THE END OF ADDING A NEW PRODUCT) ==================
                        // RESET VARIATIONS AND ATTRIBUTES //
                        $opts = [];
                        $producRprice = [];
                        $skuProduct = [];
                        $productImgVar = [];
                        $imagesUpl = [];
                        $arrayProductCode = []; 
                        $skuProductArrat = [];  
                        $stockStatusArray = [];
                        $statusVa = [];
                        // END RESET VARIATIONS AND ATTRIBUTES //
                    }
                    // GET THE ID OF THE PRODUCT JUST RECIENTLY CREATED AND SAVE THE CUSTOM PRICING ARRAY
                    $product_id = $wpdb->get_var(
                        $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE material_id = '$materialId' AND post_status='publish' LIMIT 1")
                    );
                    if( $product_id != '' & $product_id != ' ' && $product_id != 'null' && $product_id != 0 ) {
                        update_post_meta($product_id , $metaKey, $pricingCustomStrn);                     
                    }
                }else{ 
                    include(get_stylesheet_directory().'/TidyApiDev/productsUpdate.php');   
                } // END IF CREATING A PRODUCT
                // END OF ADDING A NEW PRODUCT BY MARTERIAL ID CODE TO THE DB------------------------
                
                // UPDATE PRODUCTS SCRIPT
                //include(get_stylesheet_directory().'/TidyApiDev/productsUpdate.php');

            } // END FOR - RECORRIENDO PRODUCTO POR PRODUCTO
        } // IF API CONNECTION IS WRONG ====================
        
        include(get_stylesheet_directory().'/TidyApiDev/countAndDelete.php');   

      ?>
    <!-- </main>#main -->
  <!-- </div>#primary -->

<?php
//Show the footer of the WordPress site to keep the page in context
// get_footer();