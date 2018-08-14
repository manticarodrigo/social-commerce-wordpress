<?php
/**
 * Class WC_Ref_Number_Gateway file.
 *
 * @package refrence-number-gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Reference Number Payment Gateway.
 *
 * Provides a Reference Number Payment Gateway.
 *
 * @class       WC_Ref_Number_Gateway
 * @extends     WC_Payment_Gateway
 * @version     1.0
 * @package     refrence-number-gateway
 */

class WC_Ref_Number_Gateway extends WC_Payment_Gateway {
 
 /**
  * Class constructor, more about it in Step 3
  */
 public function __construct() {
    $this->id = 'ref_number'; // payment gateway plugin ID
    $this->icon = apply_filters( 'ref_number_display_icon', '' );; // URL of the icon that will be displayed on checkout page near your gateway name
    $this->has_fields = true; // in case you need a custom form
    $this->method_title = __('Reference Number Gateway', 'ref_number');
    $this->method_description = __('Accept a reference number from a direct bank transfer as payment', 'ref_number'); // will be displayed on the options page
 
    // gateways can support subscriptions, refunds, saved payment methods
    $this->supports = array(
        'products'
    );
 
    // Load the settings.
    $this->init_form_fields();
    $this->init_settings();

    // Define user set variables.
    $this->title        = $this->get_option( 'title' );
    $this->description  = $this->get_option( 'description' );
    $this->enabled      = $this->get_option( 'enabled' );

    $this->account_details = get_option(
        'ref_number_bank_accounts',
        array(
            array(
                'bank_name'         => $this->get_option( 'bank_name' ),
                'account_holder'   => $this->get_option( 'account_holder' ),
                'account_number'    => $this->get_option( 'account_number' ),
                'cci'               => $this->get_option( 'cci' ),
            ),
        )
    );
 
    // This action hook saves the settings
    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_account_details' ) );
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
        'account_details' => array(
            'type' => 'account_details',
        ),
    );
}

/**
 * Generate account details html.
 *
 * @return string
 */
public function generate_account_details_html() {
    ob_start();
    ?>
    <tr valign="top">
        <th scope="row" class="titledesc"><?php esc_html_e( 'Account details:', 'ref_number' ); ?></th>
        <td class="forminp" id="ref_number_bank_accounts">
            <div class="wc_input_table_wrapper">
                <table class="widefat wc_input_table sortable" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="sort">&nbsp;</th>
                            <th><?php esc_html_e( 'Bank Name', 'ref_number' ); ?></th>
                            <th><?php esc_html_e( 'Account Holder', 'ref_number' ); ?></th>
                            <th><?php esc_html_e( 'Account number', 'ref_number' ); ?></th>
                            <th><?php esc_html_e( 'CCI', 'ref_number' ); ?></th>
                        </tr>
                    </thead>
                    <tbody class="accounts">
                        <?php
                        $i = -1;
                        if ( $this->account_details ) {
                            foreach ( $this->account_details as $account ) {
                                $i++;

                                echo '<tr class="account">
                                    <td class="sort"></td>
                                    <td><input type="text" value="' . esc_attr( wp_unslash( $account['bank_name'] ) ) . '" name="ref_number_bank_names[' . esc_attr( $i ) . ']" /></td>
                                    <td><input type="text" value="' . esc_attr( wp_unslash( $account['account_holder'] ) ) . '" name="ref_number_account_holder[' . esc_attr( $i ) . ']" /></td>
                                    <td><input type="text" value="' . esc_attr( $account['account_number'] ) . '" name="ref_number_account_number[' . esc_attr( $i ) . ']" /></td>
                                    <td><input type="text" value="' . esc_attr( $account['cci'] ) . '" name="ref_number_cci[' . esc_attr( $i ) . ']" /></td>
                                </tr>';
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7"><a href="#" class="add button"><?php esc_html_e( '+ Add account', 'ref_number' ); ?></a> <a href="#" class="remove_rows button"><?php esc_html_e( 'Remove selected account(s)', 'ref_number' ); ?></a></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <script type="text/javascript">
                jQuery(function() {
                    jQuery('#ref_number_bank_accounts').on( 'click', 'a.add', function(){

                        var size = jQuery('#ref_number_bank_accounts').find('tbody .account').length;

                        jQuery('<tr class="account">\
                                <td class="sort"></td>\
                                <td><input type="text" name="ref_number_account_holder[' + size + ']" /></td>\
                                <td><input type="text" name="ref_number_account_number[' + size + ']" /></td>\
                                <td><input type="text" name="ref_number_bank_names[' + size + ']" /></td>\
                                <td><input type="text" name="ref_number_cci[' + size + ']" /></td>\
                            </tr>').appendTo('#ref_number_bank_accounts table tbody');

                        return false;
                    });
                });
            </script>
        </td>
    </tr>
    <?php
    return ob_get_clean();

}

/**
 * Save account details table.
 */
public function save_account_details() {
    $accounts = array();

    if ( isset( $_POST['ref_number_account_holder'] ) && isset( $_POST['ref_number_account_number'] ) && isset( $_POST['ref_number_bank_names'] )
        && isset( $_POST['ref_number_cci'] ) && isset( $_POST['ref_number_cci'] ) ) {

        $account_holders    = wc_clean( wp_unslash( $_POST['ref_number_account_holder'] ) );
        $account_numbers    = wc_clean( wp_unslash( $_POST['ref_number_account_number'] ) );
        $bank_names         = wc_clean( wp_unslash( $_POST['ref_number_bank_names'] ) );
        $ccis               = wc_clean( wp_unslash( $_POST['ref_number_cci'] ) );

        foreach ( $account_holders as $i => $name ) {
            if ( ! isset( $account_holders[ $i ] ) ) {
                continue;
            }

            $accounts[] = array(
                'account_holder'   => $account_holders[ $i ],
                'account_number' => $account_numbers[ $i ],
                'bank_name'      => $bank_names[ $i ],
                'cci'           => $ccis[ $i ],
            );
        }
    }
    update_option( 'ref_number_bank_accounts', $accounts );
}
 


/**
 * Displays form fields
 */
public function payment_fields() {
    do_action( 'ref_number_before_fields' );
    
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

    $order->update_status('processing', __( 'Awaiting reference number confirmation', 'ref_number' ));
    $order->reduce_order_stock();
    $woocommerce->cart->empty_cart();

    return array(
        'result' => 'success',
        'redirect' => $this->get_return_url( $order )
    );
 }

}