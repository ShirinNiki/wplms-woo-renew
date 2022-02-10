<?php
/**
 * Proccessing functions and actions.
 *
 * @author 		shirin
 * @category 	Admin
 * @package 	Wplms-WooCommerce/Includes/
 * @version     1.0
 */
/////////////////////////////////////////////////////////////////////
////
///...\wp-content\plugins\vibe-course-module\includes\bp-course-actions.php
///line:1414
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( !class_exists( 'Wplms_Woo_Renew_Process' ) ) {
class Wplms_Woo_Renew_Process{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Woo_Renew_Process();
        return self::$instance;
    }

	private function __construct(){
        
       
       //add_action('woocommerce_order_status_completed',array($this,'bp_course_enable_access_r'));
        //add_action('woocommerce_order_status_completed',array($this,'bp_course_enable_access_d'),99);

        ///remove_action('woocommerce_order_status_completed',array(BP_Course_Action(),'bp_course_enable_access'));
    }
    function bp_course_enable_access_d($order_id){
        $order = new WC_Order( $order_id );
        $items = $order->get_items();
        $user_id=$order->get_user_id();
        foreach($items as $item_id=>$item){
            $courses=get_post_meta($item['product_id'],'vibe_courses',true);
            $string =$item['name'];
            if (str_contains($string, 'تمدید') && isset($courses) && is_array($courses)){
                $product_id = apply_filters('bp_course_product_id',$item['product_id'],$item);    
                $subscribed=get_post_meta($product_id,'vibe_subscription',true);
                    if(vibe_validate($subscribed) ){ 
                        
                        $process_item = apply_filters('bp_course_order_complete_item_subscribe_user',true,$item_id,$product_id,$item,$order_id);
                        $duration = get_post_meta($product_id,'vibe_duration',true);
                        //var_dump($duration);
                        $product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400,$product_id);
                        //$total_duration = 0;
                        if(is_numeric($duration) && is_numeric($product_duration_parameter)){ 
                            
                            //$total_duration = $duration*$product_duration_parameter;
                        
                                $renew_time = get_post_meta($product_id,'_renew_time',true);
                                $renew = $renew_time*$product_duration_parameter;                        
                            
                            foreach($courses as $course){
                                if($process_item && isset($renew)){ 
                                   
                                       
                                }
                                
                            }
                            
                        }
                    }
            }
        }

    }
    function bp_course_enable_access_r($order_id){ 

        $order = new WC_Order( $order_id );
        $items = $order->get_items();
        $user_id=$order->get_user_id();
        $order_total = $order->get_total();
        $commission_array=array();
        $currency = '';
        if(method_exists($order,'get_currency'))
            $currency = $order->get_currency();
        foreach($items as $item_id=>$item){

          $instructors=array();
        
          $courses=get_post_meta($item['product_id'],'vibe_courses',true);
          $product_id = apply_filters('bp_course_product_id',$item['product_id'],$item);
          $subscribed=get_post_meta($product_id,'vibe_subscription',true);
          //var_dump($item);

          if(isset($courses) && is_array($courses)){
            
            $process_item = apply_filters('bp_course_order_complete_item_subscribe_user',true,$item_id,$product_id,$item,$order_id);

            if(vibe_validate($subscribed) ){ 

                $duration = get_post_meta($product_id,'vibe_duration',true);
                //var_dump($duration);
                $product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400,$product_id);
                $total_duration = 0;
                if(is_numeric($duration) && is_numeric($product_duration_parameter)){ 
                    
                    $total_duration = $duration*$product_duration_parameter;
                    $string =$item['name'];
                    $renew_time = get_post_meta($product_id,'_renew_time',true);
                    if (str_contains($string, 'تمدید') && isset($renew_time)){
                        $total_duration = $renew_time*$product_duration_parameter;                        
                    }
                    
                }
                foreach($courses as $course){
                    if($process_item){ // gift course 
                        bp_course_add_user_to_course($user_id,$course,$total_duration,1);    
                    }
                    $instructors[$course]=apply_filters('wplms_course_instructors',get_post_field('post_author',$course),$course);
                    do_action('wplms_course_product_puchased',$course,$user_id,$total_duration,1,$product_id,$item_id);
                }
            }else{   
                if(isset($courses) && is_array($courses)){   
                foreach($courses as $course){
                        if($process_item){ //Gift course
                            bp_course_add_user_to_course($user_id,$course,'',1);   
                        }
                        $instructors[$course]=apply_filters('wplms_course_instructors',get_post_field('post_author',$course,'raw'),$course);
                        do_action('wplms_course_product_puchased',$course,$user_id,0,0,$product_id,$item_id);
                    }
                }
            }//End Else
               
                $line_total=$item['line_total'];

            //Commission Calculation
            $commission_array[$item_id]=array(
                'instructor'=>$instructors,
                'course'=>$courses,
                'total'=>$line_total,
                'currency'=>$currency
            );

          }//End If courses
        }// End Item for loop
        
        if(function_exists('vibe_get_option'))
          $instructor_commission = vibe_get_option('instructor_commission');
        
        if($instructor_commission == 0)
                return;
            
        if(!isset($instructor_commission) || !$instructor_commission)
          $instructor_commission = 70;

        $commissions = get_option('instructor_commissions');
        foreach($commission_array as $item_id=>$commission_item){

            foreach($commission_item['course'] as $course_id){
                
                if(!empty($commission_item['instructor'][$course_id]) && is_Array($commission_item['instructor'][$course_id]) && count($commission_item['instructor'][$course_id]) > 1){     // Multiple instructors
                    
                    $calculated_commission_base=round(($commission_item['total']*($instructor_commission/100)/count($commission_item['instructor'][$course_id])),0); // Default Slit equal propertion

                    foreach($commission_item['instructor'][$course_id] as $instructor){
                        if(empty($commissions[$course_id][$instructor]) && !is_numeric($commissions[$course_id][$instructor])){
                            $calculated_commission_base = round(($commission_item['total']*$instructor_commission/100),2);
                        }else{
                            $calculated_commission_base = round(($commission_item['total']*$commissions[$course_id][$instructor]/100),2);
                        }
                        $calculated_commission_base = apply_filters('wplms_calculated_commission_base',$calculated_commission_base,$instructor);

                        bp_course_record_instructor_commission($instructor,$calculated_commission_base,$course_id,array('origin'=>'woocommerce','order_id'=>$order_id,'item_id'=>$item_id,'currency'=>$commission_item['currency'],'user_id'=>$user_id));
                        
                    }
                }else{
                    if(is_array($commission_item['instructor'][$course_id]))                                    // Single Instructor
                        $instructor=$commission_item['instructor'][$course_id][0];
                    else
                        $instructor=$commission_item['instructor'][$course_id]; 
                    
                    if(isset($commissions[$course_id][$instructor]) && is_numeric($commissions[$course_id][$instructor]))
                        $calculated_commission_base = round(($commission_item['total']*$commissions[$course_id][$instructor]/100),2);
                    else
                        $calculated_commission_base = round(($commission_item['total']*$instructor_commission/100),2);

                    $calculated_commission_base = apply_filters('wplms_calculated_commission_base',$calculated_commission_base,$instructor);

                    bp_course_record_instructor_commission($instructor,$calculated_commission_base,$course_id,array('origin'=>'woocommerce','order_id'=>$order_id,'item_id'=>$item_id,'currency'=>$commission_item['currency'],'user_id'=>$user_id));
                }   
            }

        } // End Commissions_array  
       
    }
    
}
Wplms_Woo_Renew_Process::init();

}