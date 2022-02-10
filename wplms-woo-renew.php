<?php
/*
Plugin Name: WPLMS WooCommerce Course Renew
Plugin URI: 
Description: This plugin add renew option to the course
Version: 1.0
Author: Shirin Niki
Author URI: 
Text Domain: wplms-woo-renew
Domain Path: /languages/
*/
if ( !defined( 'ABSPATH' ) ) exit;


include_once 'includes/admin/admin.php';
include_once 'includes/front/front.php';
//include_once 'includes/class.process.php';



//redefine this function in class.process.php
//add_action( 'init', 'renew_remove_hooks', 11 );
function renew_remove_hooks(){
    if ( class_exists( 'BP_Course_Action' ) ) {
    $class = BP_Course_Action::init();
   // remove_action('woocommerce_order_status_completed',array($class,'bp_course_enable_access'));
    }
}

add_action( 'woocommerce_before_calculate_totals', 'add_renew_info_to_order_item', 10, 1 );
function add_renew_info_to_order_item( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) 
       return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) 
       return;
    if(is_user_logged_in()){
        $user_id = get_current_user_id();
    }else{
        return;
    }

    // Loop through cart items
    foreach ( $cart->get_cart() as $cart_item ) {
        
        // Get an instance of the WC_Product object
        $product = $cart_item['data'];

        // Get product id
        $product_id = $cart_item['product_id'];
        if(function_exists('bp_course_get_user_course_status')){
            $course_id  = get_post_meta( $product_id,'vibe_courses',true);
            if(is_array($course_id))
                $course_id = $course_id[0];
               
			if(empty($course_id) || $course_id == '' ){   
               continue;  
            } 
            $status = '';
            if(!empty($user_id) && function_exists('bp_course_get_user_course_status')){
                $status = bp_course_get_user_course_status($user_id,$course_id);
            }
            if(empty($status)){
                continue;
            }
            
            $is_renewable = get_post_meta($product_id,'_check_renew',true);
            if(empty($is_renewable) || $is_renewable == 'no')
                continue;
            $new_price = get_post_meta($product_id,'_renew_price',true);
            $renew_time = get_post_meta($product_id,'_renew_time',true);
            if(empty($new_price) || empty($renew_time))
                continue;
            $expiry = bp_course_get_user_expiry_time($user_id,$course_id);
            
           //  $product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400,$product_id);
           // $duration = get_post_meta($product_id,'vibe_duration',true);
           

            //var_dump($duration);die;
            if(time()>$expiry){
                $product->set_name( $product->get_name() . '(تمدید)' );

            // Get cart item price
            //$price  = method_exists( $product, 'get_price' ) ? floatval($product->get_price()) : floatval($product->price);    
                // Set the new price
            if( method_exists( $product, 'set_price' ) ) 
                    $product->set_price( $new_price );
                else
                    $product->price = $new_price;
                }
	
		}
       
    }
}




