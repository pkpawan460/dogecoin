<?php
//namespace Dogecoin;
/*
 * Plugin Name: WooCommerce Dogecoin Payment Gateway
 * Plugin URI: https://lapstacks.com
 * Description: Accept Dogecoin payment.
 * Author: Avinash Verma
 * Author URI: https://lapstacks.com
 * Version: 1.0.1
 */



defined( 'ABSPATH' ) or exit;

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action( 'plugins_loaded', 'pwan_dogecoin_init', 0 );
function pwan_dogecoin_init() {
	// If the parent WC_Payment_Gateway class doesn't exist
	// it means WooCommerce is not installed on the site
	// so do nothing
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
	
	// If we made it this far, then include our Gateway Class
	include_once( 'PWN_dogecoin.php' );

	// Now that we have successfully included our class,
	// Lets add it too WooCommerce
	add_filter( 'woocommerce_payment_gateways', 'pwn_dogecoin_gateway' );
	function pwn_dogecoin_gateway( $methods ) {
		$methods[] = 'PWN_Dogecoin';
		return $methods;
	}
}

// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pwn_dogecoin_action_links' );
function pwn_dogecoin_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'pwn-dogecoin-gateway' ) . '</a>',
	);

	// Merge our new link with the default ones
	return array_merge( $plugin_links, $links );	
}



// BACS payement gateway description: Append custom select field
add_filter( 'woocommerce_gateway_description', 'gateway_Dogecoin_custom_fields', 20, 2 );
function gateway_Dogecoin_custom_fields( $description, $payment_id ){
    $my_fields = get_option('woocommerce_pwn_dogecoin_gateway_settings');
	
    if( 'pwn_dogecoin_gateway' === $payment_id ){
		echo '<p>Dogecoin Payment Address</p>';
         echo '<p style="padding:5px;border:1px solid #f2f2f2;">'.$my_fields['apiLogin'].'</p>';
    }
}







//crate page 





add_action('init', 'get_page_title_for_slug'); 

function get_page_title_for_slug() {

     $page = get_page_by_path( 'thanks-order' , OBJECT );

     if ( isset($page) ){
        
     }
     else{
       	$wordpress_page = array(
        	  'post_title'    => 'Thanks Order',
        	  'post_slug'    => 'thanks-order',
        	  'post_content'  => '',
        	  'post_status'   => 'publish',
        	  'post_author'   => 1,
        	  'post_type' => 'page'
        	   );
        	 wp_insert_post( $wordpress_page ); 
     }
}


add_filter( 'page_template', 'wpa3396_page_template' );
function wpa3396_page_template( $page_template )
{
    if ( is_page( 'thanks-order' ) ) {
        $page_template = WP_PLUGIN_DIR . '/dogecoin-payment/include/thanks-template.php';
    }
    return $page_template;
}



/**
 * Add "Custom" template to page attirbute template section.
 */
function wpse_288589_add_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {

    // Add custom template named template-custom.php to select dropdown 
    $post_templates['template-custom.php'] = __('Thanks Template');

    return $post_templates;
}

add_filter( 'theme_page_templates', 'wpse_288589_add_template_to_select', 10, 4 );


/**
 * Check if current page has our custom template. Try to load
 * template from theme directory and if not exist load it 
 * from root plugin directory.
 */
function wpse_288589_load_plugin_template( $template ) {

    if(  get_page_template_slug() === 'thanks-template.php' ) {

        if ( $theme_file = locate_template( array( 'thanks-template.php' ) ) ) {
            $template = $theme_file;
        } else {
            $template =  WP_PLUGIN_DIR . '/dogecoin-payment/thanks-template.php';
        }
    }

    if($template == '') {
        throw new \Exception('No template found');
    }

    return $template;
}

add_filter( 'template_include', 'wpse_288589_load_plugin_template' );









// add currency in woocommerce
add_filter( 'woocommerce_currencies', 'add_cw_currency' );
function add_cw_currency( $cw_currency ) {
     $cw_currency['DOGECOIN'] = __( 'DOGE', 'woocommerce' );
     return $cw_currency;
}
add_filter('woocommerce_currency_symbol', 'add_cw_currency_symbol', 10, 2);
function add_cw_currency_symbol( $custom_currency_symbol, $custom_currency ) {
     switch( $custom_currency ) {
         case 'DOGECOIN': $custom_currency_symbol = 'Ð'; break;
     }
     return $custom_currency_symbol;
}







add_action( 'wc_pip_after_customer_addresses', 'action_after_customer_addresses', 10, 4 );
function action_after_customer_addresses( $type, $action, $document, $order ) {
    if( $ddate = $order->get_meta( 'Transtion_ID' ) ){
        echo '<p>'.__("Transtion ID") . ': ' . $ddate . '</p>';
    }
}


add_filter('woocommerce_thankyou_order_received_text', 'woo_change_order_received_text', 10, 2 );
function woo_change_order_received_text( $str, $order ) {
    $new_str .= '"Thank you for your order, an email will with order details will be provided shortly." or similar.<br />';
     if( $ddate = $order->get_meta( 'Transtion_ID' ) ){
        $new_str.= '<p>'.__("Transtion ID") . ': ' . $ddate . '</p>';
    }
    return $new_str;
}
