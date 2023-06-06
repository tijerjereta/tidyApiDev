<?php 

    // echo "<br>CONTEO MATERIAL ID: <br>";
    $material_idconteo = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE material_id != '1' AND material_id != '' AND material_id != ' ' ")
    );
        // SCRIPT TO DELETE PRODUCTS (MATERIAL IDS THAT ARE NOT INSIDE TIDY
        // SELECT AL METERIAL IDS FROM WP
        $prepared_statement = $wpdb->prepare("SELECT material_id FROM $wpdb->posts WHERE material_id != '1' AND material_id != '' ");
        $value = array();
        $values = $wpdb->get_col( $prepared_statement );
        $valueCount = count($values)."<br>";
        $materialidm = array();
        for ( $j=0; $j < $canProd; $j++ ) {
            $materialidm[$j] = $producto[$j]['materialItemId'];  
        }
        $deletedP = array();
        $deletedP = array_diff($values, $materialidm);
        if ( count($deletedP) > 1 ) {
            for ( $f=0; $f < $valueCount; $f++ ) { 
                $del = $deletedP[$f];
                // DELET STAT     
                $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->posts WHERE material_id = '$del' ") );       
            }
        }else{
            $deletedP = implode($deletedP);
            // DELET STAT    
            $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->posts WHERE material_id = '$deletedP' ") );
        }
        // END SCRIPT TO DELETE PRODUCTS (MATERIAL IDS THAT ARE NOT INSIDE TIDY