<?php 
    //echo "<br>Es el mismo  <br>";
    //UPDATE CUSTOMERS CUSTOM PRICING
    $pricingCustomMeta = get_post_meta( $product_id, $metaKey, true );
    if( $pricingCustomStrn != $pricingCustomMeta ) {
        update_post_meta($product_id , $metaKey, $pricingCustomStrn); 
    }
    //UPDATE PRODUCT TAG
    $terms = wp_get_post_terms(  $product_id, 'product_tag', ['fields' => 'names'] );
    $recordedTag = implode(', ', $terms);
    if( $tagName != $recordedTag ) {
        wp_set_object_terms($product_id, $tagName, 'product_tag');
    }
    //UPDATE PRODUCT TAG
    $product_id = $wpdb->get_var(
        $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE material_id = '$materialId' AND post_status='publish' LIMIT 1")
    ); 
    // UPDATE NAME
    $product_name = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM $wpdb->posts WHERE post_status='publish' AND material_id = '$materialId' LIMIT 1"));
    if( $name != $product_name ) {
        $wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET post_title = '$name' WHERE ID='$product_id' AND material_id = '$materialId' ") );
        //echo "<br>Actualiza Nombre <br>";
    }
    // END UPDATE NAME
    //UPDATE MAIN SKU 
    $productSkuRec = get_post_meta( $product_id, '_sku', true );
    if( $generalSku != $productSkuRec ) {
        update_post_meta($product_id, '_sku', $generalSku);
        //echo "<br>Actualiza SKU <br>";
    }
    //END UPDATE MAIN SKU 
    //UPDATE CATEGORIES
    $term_names = wp_get_post_terms( $product_id, 'product_cat', ['fields' => 'names'] );
    $recordedCat = implode(', ', $term_names);
    $categories = $producto[$i]['sku'];
    $string = str_replace(',', ' ', $categories); 
    //convert string to array
    $str_arr = explode(' ', $string);
    //sort into alphabetical order using sort() php function
    sort($str_arr);
    //convert array to string again
    $categories = implode(',',$str_arr);
    $categories = trim($categories);
    $recordedCat = trim($recordedCat);
    // REMOVE ALL W. SPACES
    $categories = preg_replace('/\s+/', '', $categories); 
    $recordedCat = preg_replace('/\s+/', '', $recordedCat);
    // echo $categories."<br>";
    // echo $recordedCat."<br>";
    if( $categories != $recordedCat ) { 
        wp_set_object_terms( $product_id, $categories, 'product_cat' ); // UPDATE CATEGORIES
        // echo "<br>Actualiza CATEGORIA<br>";
    }
    //END UPDATE CATEGORIES
    //UPDATE IMAGE 
    $imgMomento = $producto[$i]['imagePath'];
    $productId = $product_id;
    $product = wc_get_product( $productId );
    $imageSaved = wp_get_attachment_image_src( get_post_thumbnail_id( $productId ), 'single-post-thumbnail' ); 
    // calculo el nombre de la imagen, antes del guion medio
    $imgEnt = explode('/', $imgMomento); 
    $imgEntLast = explode('-', $imgEnt[7]);
    $imgEntFin = $imgEntLast[0]; 
    // end calculo el nombre de la imagen, antes del guion medio
    // calculo el nombre de la imagen guardada en WordPress, antes del guion medio
    $imgSaved = explode('/', $imageSaved[0]); 
    $imgSavedLast = explode('-', $imgSaved[7]);
    $imgSavedFin = $imgSavedLast[0];
    // end calculo el nombre de la imagen guardada en WordPress, antes del guion medio
    // 
    //     $imageID = get_post_thumbnail_id( $product_id ); // Image ID
    //     set_post_thumbnail( $product_id, $imageID );
    // } 
    // echo '1: '.$imgEntFin."<br>";
    // echo '2: '.$imgSavedFin."<br>";
    if( $imgEntFin != $imgSavedFin) {
        //echo "<br>Actualiza IMAGEN<br>";
        $post_id = $product_id;
        require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
        require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
        require_once(ABSPATH . 'wp-admin' . '/includes/media.php');
        // upload image to server
        if( $imgMomento != '' && $imgMomento != ' ' ) { // IF IMAGE FROM TYDY IS NOT EMPTY 
            media_sideload_image($imgMomento, $post_id);
            // get the newly uploaded image
            $attachments = get_posts( array(
                'post_type' => 'attachment',
                'number_posts' => 1,
                'post_status' => null,
                'post_parent' => $post_id,
                'orderby' => 'post_date',
                'order' => 'DESC',) 
            );
            $imageID = $attachments[0]->ID;
            set_post_thumbnail( $post_id, $imageID );
        }else{
            // $imgUrl = $sitePath."/wp-content/uploads/woocommerce-placeholder-600x600.png";
            $imgUrl = " ";

            // get the newly uploaded image
            $attachments = get_posts( array(
                'post_type' => 'attachment',
                'number_posts' => 1,
                'post_status' => null,
                'post_parent' => $post_id,
                'orderby' => 'post_date',
                'order' => 'DESC',) 
            );
            // returns the id of the image
            $imageID = $attachments[0]->ID;
            set_post_thumbnail( $post_id, '5190' ); // REPLACE WITH DEFAULT PRODUCT IMAGE ! =====================================
        }
    } 
    //END UPDATE IMAGE
    //UPDATE MAIN PRODUCT PRICES
    if( $productPrice != $normal_p) {
        //echo "<br>Actualiza PRODUCT PRICE<br>";
        //$table_name  = $wpdb->prefix."postmeta"; // TABLA QUE GUARDA LOS PRECIOS DE WOOCOMMERCE. 
        $wpdb->query( $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = '$productPrice' WHERE post_id='$product_id' AND meta_key='_price' ") );
        $wpdb->query( $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = '$productPrice' WHERE post_id='$product_id' AND meta_key='_regular_price' ") );
        $wpdb->query( $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = '$productPrice' WHERE post_id='$product_id' AND meta_key='_sale_price' ") );             
    }
    //END UPDATE MAIN PRODUCT PRICES
    // UPDATE REGULAR STOCK            


    $regular_stock = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id='$product_id' AND meta_key='_stock_status' LIMIT 1"));
    if( $stockStatus != $regular_stock ) {
        //echo "<br>Actualiza STOCKSTATUS<br>";
        $wpdb->query( $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = '$stockStatus' WHERE post_id='$product_id' AND meta_key='_stock_status' ") );
    } 


    // END UPDATE REGULAR STOCK
    // UPDATE REGULAR DESC.
    $regular_desc = $wpdb->get_var($wpdb->prepare("SELECT post_content FROM $wpdb->posts WHERE material_id = '$materialId' "));
    if( $desc != $regular_desc ) {
        //echo "<br>Actualiza DESCRIPCION<br>";
        $wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET post_content = '$desc' WHERE material_id = '$materialId' ") );
        // UPDATE OF SHORTER DESC
        $wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET post_excerpt = '$desc' WHERE material_id = '$materialId' ") );
    }
    // END UPDATE REGULAR DESC.

    $customAttributes = [
        [
            'name' => 'Size',
                'slug'=> 'size',
                'position' => 0,
                'visible' => true,
                'variation' => true,
    //                            'options' => $opts[0],
        ]
    ];
    $customVariations = [ 
        'image' => $productImgVar,
        'regular_price' => $producRprice,  
        'sku' => $skuProduct,
        'stock_status' => $stockStatusArray,
        'attributes' => [ 
            [
                'name'=>'Size',
                'slug'=>'size',
                'option'=> $opts,
            ]
        ] 
    ];
    // UPDATE PRODUCTs ATTRIBUTES
        // Get product attributes
        $product_attributes = get_post_meta( $product_id ,'_product_attributes', true);
        $optStr = implode("|", $opts);
        // Loop through product attributes
        foreach( $product_attributes as $attribute => $attribute_data ) {
            // Target specif attribute  by its name
            if( 'Size' === $attribute_data['name'] ) {
                // Set the new value in the array
                $product_attributes[$attribute]['value'] = $optStr; 
                break; // stop the loop
            }
        }
        // Set updated attributes back in database
        update_post_meta( $product_id ,'_product_attributes', $product_attributes );
        $opts = []; // RESET THE ATTRIBUTES OPTIONS
    // END UPDATE PRODUCTs ATTRIBUTES
    // UPDATE PRODUCTs VARIATIONS
        // Get product VARIATIONS
        $product_variations = get_post_meta( $product_id ,'_product_variations', true);
        // print_r($product_variations);
        global $woocommerce, $product, $post;
        // test if product is variable
        if ($product->is_type( 'variable' )) 
        {
            $available_variations = $product->get_available_variations();
            $f = 0;
            //print_r($available_variations);
            foreach ($available_variations as $variation_values ){
                $variation_id = $variation_values['variation_id']; // variation id
                // Updating active price and regular price & SKU
                // print_r( $available_variations);
                $savedVariationPrc = $available_variations[$f]['display_regular_price'];
                $savedVariationSku = $variation_values['sku']; //$available_variations[$f]['sku'];
                $savedStock = $available_variations[$f]['is_in_stock'];
                if( $savedVariationPrc != $producRprice[$f]) {
                    update_post_meta( $variation_id, '_regular_price', $producRprice[$f] );
                    update_post_meta( $variation_id, '_price', $producRprice[$f] );
                }
                // echo "ID DEL PROD.".$product_id."<br>";
                // echo "ID de la variacion: ".$variation_id."<br>";
                // echo "Array SKU act: <br>";
                // echo "<br><br><br><br><br><br><br>";

                 
                // echo "<br><br><br><br><br><br><br>";
                // $skuProductUp = trim($producto[$i]['code']);
                // $finalSkuUp = str_replace('.','', $skuProductUp);
                // $finalSkuUp = str_replace('(','', $finalSkuUp);
                // $finalSkuUp = str_replace(')','', $finalSkuUp);
                // $finalSkuUp = str_replace('-','', $finalSkuUp);
                // $finalSkuUp = str_replace('/','', $finalSkuUp);
                // $finalSkuUp = trim($finalSkuUp);
                
                // print_r($finalSkuUp);
                // echo "<br>SKU GGUARDADO ".$f."<br>";
                // echo $savedVariationSku."<br>";
                // // echo "<br>SKU A COMPARAR<br>";
                // // print_r($skuProduct[$f]);
                // // echo "CON SKU GUARDADO: <br>";
                // // print_r($savedVariationSku);
                // echo "<br><br><br><br><br><br><br>";
                // echo "SKU GGUARDADO ".$f."<br>";
                // echo $savedVariationSku."<br>";
                // print_r($skuProduct[$f]);
                // echo "<br>"; 


                // echo "<br><br><br><br><br><br><br>";





                // if( $skuProduct[$f]!= $savedVariationSku ) {
                //     echo "skU a re escribir: <br>";
                //     print_r($skuProduct[$f]);
                //     echo "<br>";
                //     //update_post_meta( $variation_id, '_sku', $skuProduct[$f] );
                // }else{
                //     echo "SKU IGUALES!";
                // }
                if( $stockStatusArray[$f]!= $savedStock ) {
                    update_post_meta( $variation_id, '_stock_status', $stockStatusArray[$f] );
                }    
                // END Updating active price and regular price & SKU
                //UPDATE VARIATIONS IMGs
            
                if( $productImgVar[$f][0] != '' && $productImgVar[$f][0] != ' ' && $productImgVar[$f][0] != 'null' ) { 
                    $prodUrl = $productImgVar[$f][0];
                    $url = $prodUrl['src'];
                    $urlDef = $prodUrl['src']; 
                    //echo "ERROR IMG:"; 
                    //print_r($productImgVar[$f][0]);
                    $imgMomento = implode(' ', $productImgVar[$f][0]);
                    $productId = $variation_id;
                    $product = wc_get_product( $productId );
                    $imageSaved = wp_get_attachment_image_src( get_post_thumbnail_id( $productId ), 'single-post-thumbnail' ); 
                    // calculo el nombre de la imagen, antes del guion medio
                    $imgEnt = explode('/', $imgMomento); 
                    $imgEntLast = explode('-', $imgEnt[7]);
                    $imgEntFin = $imgEntLast[0]; 
                    $imgSaved = explode('/', $imageSaved[0]); 
                    $imgSavedLast = explode('-', $imgSaved[7]);
                    $imgSavedFin = $imgSavedLast[0];
                    if( $imgEntFin != $imgSavedFin) {
                        require_once(ABSPATH . 'wp-admin/includes/media.php');
                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        // sideload the image --- requires the files above to work correctly
                        if( $urlDef != '' && $urlDef != '' && $urlDef && 'null' ){
                            //echo "<br>Actualiza IMAGEN!<br>";
                            $src = media_sideload_image( $urlDef, null, null, 'src' );
                        // convert the url to image id
                            
                            $image_id = attachment_url_to_postid( $src );
                            //$imageID = $productImgVar[$f]->ID;
                            set_post_thumbnail( $variation_id, $image_id);
                        }    
                    }
                    //END UPDATE VARIATIONS IMGs 
                    wc_delete_product_transients( $variation_id ); // Clear/refresh the variation cache
                    $f++;
                }else{
                    $f++;
                }    
            }
            $f = 0;
            // Clear/refresh the variable product cache
            wc_delete_product_transients( $product_id  );
            $producRprice = []; //RESET THE PRICE VALUE;
            $skuProduct = [];
            $savedVariationSku = [];
            $productImgVar = [];
            $stockStatusArray = [];

        }