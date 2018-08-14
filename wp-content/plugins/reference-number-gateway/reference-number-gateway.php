<?php
/*
 * Plugin Name: WooCommerce Reference Number Gateway
 * Plugin URI: http://ooqia.com
 * Description: Accept a reference number from a direct bank transfer as payment.
 * Author: Ooqia
 * Author URI: http://ooqia.com
 * Version: 1.0.0
 *
 */

add_action('plugins_loaded', 'ref_number_init_plugin');
function ref_number_init_plugin() {
    load_plugin_textdomain( 'ref_number', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
    require 'class-wc-ref-number-gateway.php';
}

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'ref_number_add_gateway_class' );
function ref_number_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Ref_Number_Gateway';
	return $gateways;
}

/**
 * Register meta box(es).
 */
add_action( 'add_meta_boxes', 'ref_number_metabox' );
function ref_number_metabox() {
    add_meta_box( 'ref-box-id', __( 'Reference Number', 'ref_number' ), 'display_ref_number', 'shop_order', 'side' );
}
 
/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function display_ref_number( $post ) {
    // Display code/markup goes here. Don't forget to include nonces!
    $ref_number = get_post_meta( $post->ID, 'ref_number', true);
    echo(__("Reference Number", 'ref_number') . ": <strong>{$ref_number}</strong>");
}

/*
 * @param $order_obj Order Object
 * @param $sent_to_admin If this email is for administrator or for a customer
 * @param $plain_text HTML or Plain text (can be configured in WooCommerce > Settings > Emails)
 */
add_action( 'woocommerce_email_order_meta', 'ref_number_add_email_order_meta', 10, 3 );
function ref_number_add_email_order_meta( $order_obj, $sent_to_admin, $plain_text ){

	$ref_number = get_post_meta( $order_obj->get_order_number(), 'ref_number', true );

	if( empty( $ref_number ) )
		return;

	// we will add the separate version for plaintext emails
	if ( $plain_text === false ) {
		// you shouldn't have to worry about inline styles, WooCommerce adds them itself depending on the theme you use
		echo '<h2>' . _e('Reference Number', 'ref_number') . '</h2>
		<ul>
		<li><strong>' . __('Reference Number', 'ref_number') .':</strong> ' . $ref_number . '</li>
		</ul>';
 
	} else {
		echo __('Reference Number', 'ref_number') . $ref_number;
	}
}

/**
 * Display the extra data on order recieved page and my-account order review.
 */
function ref_number_display_order_data( $order_id ) {  
    $ref_number = get_post_meta( $order_id, 'ref_number', true );
    if ( ! $ref_number ) {
        return;
    }
    ?>
    <h2><?php _e( 'Reference Number', 'ref_number' ); ?></h2>
    <table class="shop_table additional_info">
        <tbody>
            <tr>
                <th><?php _e('Reference Number', 'ref_number'); ?></th>
                <td><?php echo $ref_number; ?></td>
            </tr>
        </tbody>
    </table>
<?php }
add_action( 'woocommerce_thankyou', 'ref_number_display_order_data', 20 );
add_action( 'woocommerce_view_order', 'ref_number_display_order_data', 20 );