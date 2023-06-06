<?php

                                $product_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_status='publish' AND post_title = '$name' LIMIT 1"));
                                global $woocommerce, $product, $post;
                                $id = $product_id;
                                $parent_id = $product_id; // Or get the variable product id dynamically
                                $attributes = get_post_meta($product_id, '_product_attributes', true); // Initializing
                                //$attributes = $product->get_attributes();
                                if(!empty($attributes)){
                                    //$attribute_single = array_keys($attributes);
                                    foreach ( $attributes as $attribute ) {
                                        //$myArray[] = ucfirst($value);
                                        // echo "<br>Va: ";
                                        //print_r($attribute['value']);
                                        $nOpts = $attribute['value'];
                                        // echo "<br> Y TOTAL ATRIBUTTE: <br>";
                                        // print_r($attribute);
                                    }
                                    $nOpts = explode('|' , $nOpts);
                                    array_push($nOpts,$optUp);
                                }
                                $attributes_data = [
                                    [
                                        'name' => 'Size',
                                        'slug'=> 'size',
                                        'position' => 0,
                                        'visible' => true,
                                        'variation' => true,
                                        'options' => $nOpts,
                                    ]
                                ];
                                
                                if( sizeof($attributes_data) > 0 ){
                                    $attributes = get_post_meta($product_id, '_product_attributes', true); // Initializing
                                    // Loop through defined attribute data
                                    if( is_array($attributes) ) {
                                        if( sizeof($attributes) > 0 ){
                                            foreach( $attributes as $attribute => $attributes_data ) {
                                                    $product_attributes[$attribute]['value'] = $product_attributes[$attribute]['value'].'|'.$optUp; 
                                            }
                                        }    
                                    }
                                    // // Set updated attributes back in database
                                    update_post_meta( $product_id ,'_product_attributes', $product_attributes );
                                }
                                //END UPDATE PRODUCT ATTRIBUTES BEFORE ADDING A CUSTOM VARIATION

                                // The variation data
                                $variation_data =  array(
                                    'attributes' => array(
                                        //'size'  => "'".$optUp."'", //'2',
                                        'size'  => $optUp,
                                    ),
                                    'sku' => $finalSku,
                                    'regular_price' => $producto[$i]['charge'],  
                                    'sale_price'    => '',
                                    'stock_qty'     => 10,
                                    'statusVar' => $stockStatus,
                                );  
                                    // Get the Variable product object (parent)
                                    $product = wc_get_product($product_id);
                                
                                    $variation_post = array(
                                        'post_title'  => $product->get_name(),
                                        'post_name'   => 'product-'.$product_id.'-variation',
                                        'post_status' => 'publish',
                                        'post_parent' => $product_id,
                                        'post_type'   => 'product_variation',
                                        'guid'        => $product->get_permalink()
                                    );
                                
                                    // Creating the product variation
                                    $variation_id = wp_insert_post( $variation_post );
                                
                                    // Get an instance of the WC_Product_Variation object
                                    $variation = new WC_Product_Variation( $variation_id );
                                
                                    // Iterating through the variations attributes
                                    foreach ($variation_data['attributes'] as $attribute => $term_name )
                                    {
                                        $taxonomy = 'size'; // The attribute taxonomy
                                
                                        // If taxonomy doesn't exists we create it (Thanks to Carl F. Corneil)
                                        if( ! taxonomy_exists( $taxonomy ) ){
                                            register_taxonomy(
                                                $taxonomy,
                                               'product_variation',
                                                array(
                                                    'hierarchical' => false,
                                                    'label' => ucfirst( $attribute ),
                                                    'query_var' => true,
                                                    'rewrite' => array( 'slug' => sanitize_title($attribute) ), // The base slug
                                                ),
                                            );
                                        }
                                        // Check if the Term name exist and if not we create it.
                                        if( ! term_exists( $term_name, $taxonomy ) )
                                            wp_insert_term( $term_name, $taxonomy ); // Create the term
                                
                                        $term_slug = get_term_by('name', $term_name, $taxonomy )->slug; // Get the term slug
                                        
                                        // Get the post Terms names from the parent variable product.
                                        $post_term_names =  wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'names') );
                                
                                        // Check if the post term exist and if not we set it in the parent variable product.
                                        if( ! in_array( $term_name, $post_term_names ) )
                                            wp_set_post_terms( $product_id, $term_name, $taxonomy, true );
                                
                                        // Set/save the attribute data in the product variation
                                        update_post_meta( $variation_id, 'attribute_'.$taxonomy, $term_slug );
                                    }
                                    ## Set/save all other data
                                    // SKU
                                    // if( ! empty( $variation_data['sku'] ) )
                                    //     $variation->set_sku( $variation_data['sku'] );
                                    // Prices
                                    if( empty( $variation_data['sale_price'] ) ){
                                        $variation->set_price( $variation_data['regular_price'] );
                                    } else {
                                        $variation->set_price( $variation_data['sale_price'] );
                                        $variation->set_sale_price( $variation_data['sale_price'] );
                                    }
                                    $variation->set_regular_price( $variation_data['regular_price'] );
                                    // Stock
                                    if( ! empty($variation_data['stock_qty']) ){
                                        $variation->set_stock_quantity( $variation_data['stock_qty'] );
                                        $variation->set_manage_stock(true);
                                        $variation->set_stock_status('');
                                    } else {
                                        $variation->set_manage_stock(false);
                                    }
                                    $variation->set_weight(''); // weight (reseting)
                                    $variation->save(); // Save the data
                                    //SET CODES TO AVOID THE SCRIPT RUNNING TWICE
                                    $nameVariation = $name.' - '.$optUp;
                                    $postExe = 'Size: '.$optUp;
                                    $prdVar = 	'product-'.$product_id.'-variation';
                                    $wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET material_id = '$materialId' WHERE post_name  = '$prdVar'  ") );
                                    
                                    //RESET VALUES TO DON'T AFFECT 'productsUpdate' functionality                                     
                                    // ATTRIBUTES VALUES
                                    $nOpts = [];
                                    $opts = [];
                                    // VARIATION VALUES
                                    $producRprice = [];
                                    $skuProduct = [];
                                    $stockStatusArray = [];
                                    $productImgVar = [];