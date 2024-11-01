<?php
/**
 * @package CheetahPay
 * @version 1.0.5
 */
/*
Plugin Name: Cheetahpay Checkout Payment Gateway for Woocommerce
Plugin URI: http://cheetahpay.com.ng/
Description: A woocommerce paymernt gateway to help you receive airtime as payment
Author: Cheetahpay Nigeria
Version: 1.0.5
Author URI: https://github.com/cheetahpay
*/

define("WC_CPPG_DEV", false);
define("WC_CPPG_NAIRA_SIGN", '₦');
define("WC_CPPG_ROOT_FOLDER", WC_CPPG_DEV?'/wordpress/':'/');
$woo_cheetahpay_response = ['Initial value'];

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action( 'plugins_loaded', 'cheetahpay_init', 0 );

function cheetahpay_init() 
{
	// If the parent WC_Payment_Gateway class doesn't exist
	// it means WooCommerce is not installed on the site
	// so do nothing
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
	
	// If we made it this far, then include our Gateway Class
	include_once( 'wc_cheetahpay.php' );

	// Now that we have successfully included our class,
	// Lets add it too WooCommerce
	add_filter( 'woocommerce_payment_gateways', 'add_cppg_cheetahpay_payment_gateway' );

	function add_cppg_cheetahpay_payment_gateway( $methods ) {
		$methods[] = 'WC_Cheetahpay';
		return $methods;
	}
}

// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'cppg_cheetahpay_action_links' );

function cppg_cheetahpay_action_links( $links ) 
{
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'spyr-authorizenet-aim' ) . '</a>',
	);

	// Merge our new link with the default ones
	return array_merge( $plugin_links, $links );	
}

add_filter('woocommerce_thankyou_order_received_text', 'cppg_cheetahpay_change_order_received_text', 10, 2 );
function cppg_cheetahpay_change_order_received_text( $str, $order ) 
{
    $module = $order->get_meta('module');
    $amount = $order->get_meta('total_amount');
    $network = str_ireplace('transfer', '', $order->get_meta('network'));
    $extraMsg = empty($module) ? '' : '<br />
                <div style="background-color: #aab8c1; padding: 10px; border-radius: 5px; 
                       margin-bottom: 10px; border: 1px solid #5e7484;">
                	<div>IMPORTANT!!! Transfer '.WC_CPPG_NAIRA_SIGN.$amount.' amount '
                	    .$network.' airtime now to: </div>
                	<div style="font-size: 22px; text-align: center;"><b>'.$module.'</b></div>
                	<div>This order will be cancelled if your airtime is not received within 15 mins</div>
                </div>';
    
//     dlog($order->get_meta_data());
    
    $new_str = $str . $extraMsg;
    
    return $new_str;
}

// function dlog($msg) {
//     $str = '';
    
//     if (is_array($msg)) $str = json_encode($msg, JSON_PRETTY_PRINT);
    
//     else $str = $msg;
    
//     error_log(
//         '*************************************' . PHP_EOL .
//         '     Date Time: ' . date('Y-m-d h:m:s') . PHP_EOL .
//         '------------------------------------' . PHP_EOL .
//         $str . PHP_EOL . PHP_EOL .
//         '*************************************' . PHP_EOL,
        
//         3, __DIR__ . '/errorlog.txt');
// }