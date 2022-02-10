<?php
/**
 * Installation related functions and actions.
 *
 * @author 		Shirin Niki
 * @category 	Admin
 * @package 	Wplms-WooCommerce/Includes/admin
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( !class_exists( 'Wplms_Woo_Renew_Front' ) ) {
class Wplms_Woo_Renew_Front{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Woo_Renew_Front();
        return self::$instance;
    }

	private function __construct(){

		add_filter('wplms_take_course_button_html',array(__CLASS__,'woocommerce_just_renew_form'),999,2);
		
		add_filter('wplms_expired_course_button',array(__CLASS__,'renew_form'),99,2);
		add_filter('wplms_start_course_button',array(__CLASS__,'course_hide_price_option'),999,2);
		add_filter('wplms_continue_course_button',array(__CLASS__,'course_hide_price_option'),999,2);
	}

	function woocommerce_just_renew_form($return,$course_id){
		$product_id = get_post_meta($course_id,'vibe_product',true);
		if(is_array($product_id)){
			$product_id = $product_id[0];
		}
		global $woocommerce,$product;
		$product = wc_get_product($product_id);
		if(empty($product)) 
			return $return;
		
		$just_renew = get_post_meta($product_id,'_just_sell_renew',true);
		if(!isset($just_renew) || $just_renew != "yes")
			return $return;
		
		if(!is_user_logged_in() && isset($just_renew) && $just_renew == "yes"){
			$return = apply_filters('wplms_private_course_button_html','<a href="'.apply_filters('wplms_private_course_button','#',$course_id).'" class="'.((isset($id) && $id )?'':'course_button full ').'button">'. apply_filters('wplms_private_course_button_label',__('PRIVATE COURSE','vibe'),$course_id).'</a>',$course_id); 
			$return .= '<style>li.course_price{display: none !important;}</style>';
			return $return;

		}else if( is_user_logged_in() && isset($just_renew) && $just_renew == "yes"){
			$status = '';
			$user_id = 0;
			$user_id = get_current_user_id();
			if(!empty($user_id) && function_exists('bp_course_get_user_course_status')){
				$status = bp_course_get_user_course_status($user_id,$course_id);
			}
			if(empty($status)){
				$return = apply_filters('wplms_private_course_button_html','<a href="'.apply_filters('wplms_private_course_button','#',$course_id).'" class="'.((isset($id) && $id )?'':'course_button full ').'button">'. apply_filters('wplms_private_course_button_label',__('PRIVATE COURSE','vibe'),$course_id).'</a>',$course_id); 
				$return .= '<style>li.course_price{display: none !important;}</style>';
				return $return;
			}

		}
		return $return;
	}

    function renew_form($html,$course_id){ 
       
        $product_id = get_post_meta($course_id,'vibe_product',true);
		if(is_array($product_id)){
			$product_id = $product_id[0];
		}
		global $woocommerce,$product;
		$product = wc_get_product($product_id);
		if(empty($product))
			return $html;

		$check_renew = get_post_meta($product_id,'_check_renew',true);
		if(!isset($check_renew) || $check_renew != "yes")
			return $html;

		$user_id = 0;
		$status = '';
		if(is_user_logged_in()){
			$user_id = get_current_user_id();
		}

		if(!empty($user_id) && function_exists('bp_course_get_user_course_status')){
			$status = bp_course_get_user_course_status($user_id,$course_id);
		}
        
		if(!empty($status)){
			
            if(is_numeric($product_id)){
              $pid=get_permalink($product_id);
              $check=vibe_get_option('direct_checkout');
              $check =intval($check);
              if(isset($check) &&  $check){
                $pid .= '?redirect';
              }
            }
			
			
			$time = get_post_meta($product_id,'_renew_time',true);
			$price = get_post_meta($product_id,'_renew_price',true);
			$product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400,$product_id);
			$total_duration = tofriendlytime($time * $product_duration_parameter);

            $html='<div>
            <a href="'. $pid .'" class="course_button full button">زمان دوره به پایان رسیده برای تمدید دوره کلیک کنید.</a>
			<p> تمدید '.$total_duration.' - '.$price.' تومان</p>
            </div>';
			return $html;
		}

    }
	function course_hide_price_option($html,$course_id){
		$html .= '<style>li.course_price{display: none !important;}</style>';
		return $html;
	}

}

Wplms_Woo_Renew_Front::init();
}