<?php
/**
 * Installation related functions and actions.
 *
 * @author 		Shirin Niki
 * @category 	Admin
 * @package 	Wplms-Woo-Renew/Includes/admin
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( !class_exists( 'Wplms_Woo_Renew_Admin' ) ) {

class Wplms_Woo_Renew_Admin{

	public static $instance;
    
    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Woo_Renew_Admin();
        return self::$instance;
    }
    private function __construct(){
        add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'renew_option_tab' ) );
        add_action( 'woocommerce_product_data_panels', array(__CLASS__,'renew_options_product_tab_content') );
        add_action( 'admin_footer', array(__CLASS__, 'renew_option_custom_js'));
        add_action( 'woocommerce_process_product_meta_simple',array( __CLASS__, 'save_renew_option_fields' ) );
        add_action( 'woocommerce_process_product_meta_variable',array( __CLASS__, 'save_renew_option_fields' ) );
      
    }
 
    public function renew_option_tab( $tabs) {
        // Key should be exactly the same as in the class product_type
        $tabs['renew'] = array(
            'label'	 => __( 'تمدید دوره', '' ),
            'target' => 'renew_options',
            'class'  => ('show_if_wplms_checked'),
        );
        return $tabs;
    }
    public function renew_options_product_tab_content(){
        $vibe_courses = get_post_meta(get_the_ID(),'wplms-woo',true); 
		if(empty($vibe_courses)){
			$display = 'hide';
		}else{
            $display = 'show';
        }?>
        <div id='renew_options' class='panel woocommerce_options_panel'><?php
            ?><div class='options_group'><?php
                woocommerce_wp_checkbox( array(
                    'id'        => '_check_renew', 
                    'label'     => __('فروش تمدید'), 
                    'description' => __("فعال کردن گزینه تمدید", '')
                ));
                woocommerce_wp_text_input( array(
                           'id'          => '_renew_time',
                           'label'       => __( 'مدت زمان تمدید'),
                           'placeholder' => '',
                           'desc_tip'    => 'true',
                           'description' => __( 'مدت زمان تمدید را به روز وارد کنید' ),
                   ));
                woocommerce_wp_text_input( array(
                    'id'          => '_renew_price',
                    'label'       => __( 'هزینه تمدید'),
                    'placeholder' => '',
                    'desc_tip'    => 'true',
                    'description' => __( 'مدت زمان تمدید را وارد کنید' ),
                ));  
                woocommerce_wp_checkbox( array(
                    'id'        => '_just_sell_renew', 
                    'label'     => __('فقط اجازه تمدید'), 
                    'description' => __("این گزینه را برای دوره های هوشمند تعاملی فعال کنید", '')
                ));

            ?></div>
        </div><?php
    }
    public function renew_option_custom_js(){
        if ( 'product' != get_post_type() ) :
            return;
        endif; 
        
        ?>
        <style>
        .renew_options{display:none !important;}
        .renew_options.show_if_wplms_checked{display:block !important;}
        </style>
        <script>jQuery(document).ready(function($){
            if(jQuery('#vibe_wplms').is(':checked')){
                jQuery('.renew_options').addClass('show_if_wplms_checked');
            }else{
                jQuery('.renew_options').removeClass('show_if_wplms_checked');
            }
            jQuery('#vibe_wplms').on('change',function(){
                if(jQuery(this).is(':checked')){
                    jQuery('.renew_options').addClass('show_if_wplms_checked');
                    
                }else{
                    jQuery('.renew_options').removeClass('show_if_wplms_checked');
                }
            });
            if(jQuery('#_check_renew').is(':checked')){
                jQuery("#_renew_time").prop('required',true);
                jQuery("#_renew_price").prop('required',true);
            }else{
                jQuery("#_renew_time").prop('required',false);
                jQuery("#_renew_price").prop('required',false);
            }
            jQuery('#_check_renew').on('change',function(){
                if(jQuery(this).is(':checked')){
                    jQuery("#_renew_time").prop('required',true);
                    jQuery("#_renew_price").prop('required',true);
                }else{
                    jQuery("#_renew_time").prop('required',false);
                    jQuery("#_renew_price").prop('required',false);
            }
            });
        });</script>
    <?php
    }
    public function save_renew_option_fields( $post_id ) {
    //   // var_dump($_POST);
    //    $courses = $_POST['_renew_price'];
    //    var_dump($courses);
    //    DIE;
        $is_renew = isset( $_POST['_check_renew'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_check_renew', $is_renew );
        if ( isset( $_POST['_renew_time'] ) ) :
            update_post_meta( $post_id, '_renew_time', sanitize_text_field( $_POST['_renew_time'] ) );
        endif;
    
        if ( isset( $_POST['_renew_price'] ) ) :
            update_post_meta( $post_id, '_renew_price', sanitize_text_field( $_POST['_renew_price'] ) );
        endif;
        $is_sellrenew = isset( $_POST['_just_sell_renew'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_just_sell_renew', $is_sellrenew );
    }
}
Wplms_Woo_Renew_Admin::init();
}