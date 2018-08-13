<?php
/*
 * Plugin Name: Multisite REST API
 * Plugin URI: http://ooqia.com
 * Description: Handlers of multisite API for heyshopper.co.
 * Author: Ooqia
 * Author URI: http://ooqia.com
 * Version: 1.0.0
 *
 */

add_action('init', 'msrest_load_controllers');
function msrest_load_controllers() {
    include_once dirname( __FILE__ ) . '/controllers/multisite-controller.php';
    $controller = new MultisiteController;
    $controller->register_routes();
}


function msrest_set_woocommerce_options() {
    if ( class_exists( 'WC_Install' ) ) {
        $state = 'LIM'; // Lima
        $country = 'PE'; // Peru
        $currency_code = 'PEN'; // Peruvian Soles
        // update_option( 'woocommerce_store_address', $address );
        // update_option( 'woocommerce_store_address_2', $address_2 );
        // update_option( 'woocommerce_store_city', $city );
        update_option( 'woocommerce_default_country', $country . ':' . $state );
        // update_option( 'woocommerce_store_postcode', $postcode );
        update_option( 'woocommerce_currency', $currency_code );
        // update_option( 'woocommerce_product_type', $product_type );
        // update_option( 'woocommerce_sell_in_person', $sell_in_person );
        update_option( 'woocommerce_email_from_address', 'noreply@heyshopper.co' );
        $locale_info = include WC()->plugin_path() . '/i18n/locale-info.php';
        
        if ( isset( $locale_info[ $country ] ) ) {
            update_option( 'woocommerce_weight_unit', $locale_info[ $country ]['weight_unit'] );
            update_option( 'woocommerce_dimension_unit', $locale_info[ $country ]['dimension_unit'] );
            // Set currency formatting options based on chosen location and currency.
            if ( $locale_info[ $country ]['currency_code'] === $currency_code ) {
                update_option( 'woocommerce_currency_pos', $locale_info[ $country ]['currency_pos'] );
                update_option( 'woocommerce_price_decimal_sep', $locale_info[ $country ]['decimal_sep'] );
                update_option( 'woocommerce_price_num_decimals', $locale_info[ $country ]['num_decimals'] );
                update_option( 'woocommerce_price_thousand_sep', $locale_info[ $country ]['thousand_sep'] );
            }
        }
        delete_option( 'woocommerce_admin_notice_install' );
        WC_Install::create_pages();
    }
}

function msrest_set_storefront_options() {
    $shop_page_id = wc_get_page_id( 'shop' );
    switch_theme( 'storefront-child' );
    update_option( 'page_on_front', $shop_page_id );
    update_option( 'show_on_front', 'page' );
    update_option( 'storefront_nux_dismissed', true);
    $banner_id = get_option( 'banner_id' );
    if ( $banner_id )
        set_post_thumbnail( $shop_page_id, $banner_id );
    return $shop_page_id;
}

add_filter( 'bulk_actions-sites-network', 'msrest_register_bulk_actions' );
function msrest_register_bulk_actions($bulk_actions) {
    $bulk_actions['reset_settings'] = __( 'Reset Site Settings', 'reset_settings');
    return $bulk_actions;
}

add_filter( 'handle_network_bulk_actions-sites-network', 'msrest_bulk_action_handler', 10, 4 );
function msrest_bulk_action_handler( $redirect_to, $doaction, $blogs, $id ) {
    if ( $doaction !== 'reset_settings' ) {
        return $redirect_to;
    }
    foreach ( $blogs as $blog_id ) {
        switch_to_blog( $blog_id );
        msrest_set_woocommerce_options();
        msrest_set_storefront_options();
        restore_current_blog();
    }
    $redirect_to = add_query_arg( 'updated', 'reset_settings', $redirect_to );
    return $redirect_to;
}

add_filter( 'network_sites_updated_message_reset_settings', 'msrest_action_admin_notice' );
function msrest_action_admin_notice() {
    return __( 'Sites updated.' );
}