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

add_action('plugins_loaded', 'ref_number_load_textdomain');
function ref_number_load_textdomain() {
	load_plugin_textdomain( 'ref_number', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'ref_number_add_gateway_class' );
function ref_number_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Ref_Number_Gateway';
	return $gateways;
}
 
/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'ref_number_init_gateway_class' );
function ref_number_init_gateway_class() {
 
	class WC_Ref_Number_Gateway extends WC_Payment_Gateway {
 
 		/**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct() {
            $this->id = 'ref_number'; // payment gateway plugin ID
            $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = __('Reference Number Gateway', 'ref_number');
            $this->method_description = __('Accept a reference number from a direct bank transfer as payment', 'ref_number'); // will be displayed on the options page
         
            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'products'
            );
         
            // Method with all the options fields
            $this->init_form_fields();
         
            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
            $this->enabled = $this->get_option( 'enabled' );
         
            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
 		}
 
		/**
 		 * Plugin options
 		 */
 		public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => __('Enable/Disable', 'ref_number'),
                    'label'       => __('Enable Reference Number Gateway', 'ref_number'),
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => __('Title', 'ref_number'),
                    'type'        => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'ref_number'),
                    'default'     => __('Bank transfer | Reference Number', 'ref_number'),
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => __('Description', 'ref_number'),
                    'type'        => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'ref_number'),
                    'default'     => __('Use a bank transfer reference number', 'ref_number'),
                ),
            );
	 	}
 
		/**
		 * Displays form fields
		 */
		public function payment_fields() {
            if ( $this->description ) {
                $this->description  = trim( $this->description );
                // display the description with <p> tags etc.
                echo wpautop( wp_kses_post( trim( $this->description ) ) );
            }

            echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
        
            echo '<div class="form-row form-row-wide"><label>' . __('Reference Number', 'ref_number') . '<span class="required">*</span></label>
                <input id="refNo" name="refNo" type="text" autocomplete="off">
                </div>
                <div class="clear"></div>';
        
            echo '<div class="clear"></div></fieldset>';
		}
 
		/*
 		 * Fields validation from checkout fields
		 */
		public function validate_fields() {
            // if( !empty( $_POST[ 'refNo' ]) ) {
            //     wc_add_notice(  __('Reference number is required', 'ref_number'), 'error' );
            //     return false;
            // }
            return true;
		}
 
		/*
		 * We're processing the payments here
		 */
		public function process_payment( $order_id ) {
            global $woocommerce;
 
            $ref_number = ( isset( $_POST['refNo'] ) ) ? $_POST['refNo'] : 0;

            $order = wc_get_order( $order_id );
            update_post_meta( $order_id, 'ref_number', $ref_number );

            $order->update_status('on-hold', __( 'Awaiting reference number confirmation', 'ref_number' ));
            $order->reduce_order_stock();
            $woocommerce->cart->empty_cart();

            return array(
				'result' => 'success',
				'redirect' => $this->get_return_url( $order )
			);
	 	}
 
 	}
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

	// ok, we will add the separate version for plaintext emails
	if ( $plain_text === false ) {
		// you shouldn't have to worry about inline styles, WooCommerce adds them itself depending on the theme you use
		echo '<h2>' . _e('Reference Number', 'ref_number') . '</h2>
		<ul>
		<li><strong>' . _e('Reference Number', 'ref_number') .':</strong> ' . $ref_number . '</li>
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