<?php 

// Adding custom scripts
add_action( 'wp_enqueue_scripts', 'storefront_enqueue_styles' );
function storefront_enqueue_styles() {
  wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}


// Remove sidebar
add_action( 'get_header', 'remove_storefront_sidebar' );
function remove_storefront_sidebar() {
  remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
}

// Add scripts to wp_head()
add_action( 'wp_head', 'child_theme_head_script' );
function child_theme_head_script() { ?>
	<!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-K8XP8ZX');</script>
  <!-- End Google Tag Manager -->
<?php }

// Add scripts to body
add_action( 'storefront_before_site', 'child_theme_body_script' );
function child_theme_body_script() { ?>
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K8XP8ZX"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
<?php }


// Scripts
add_action( 'wp_enqueue_scripts', 'my_assets' );
function my_assets() { 
  wp_enqueue_script( 'theme-scripts', get_stylesheet_directory_uri() . '/script.js', array('jquery' ), '1.0', true );
}

// Custom header
add_action( 'init', 'remove_storefront_header_hooks' );
function remove_storefront_header_hooks() {
  remove_action( 'storefront_header', 'storefront_header_container',                 0 );
  remove_action( 'storefront_header', 'storefront_skip_links',                       5 );
  remove_action( 'storefront_header', 'storefront_site_branding',                    20 );
  remove_action( 'storefront_header', 'storefront_secondary_navigation',             30 );
  remove_action( 'storefront_header', 'storefront_header_container_close',           41 );
  remove_action( 'storefront_header', 'storefront_primary_navigation_wrapper',       42 );
  remove_action( 'storefront_header', 'storefront_primary_navigation',               50 );
  remove_action( 'storefront_header', 'storefront_primary_navigation_wrapper_close', 68 );
  remove_action( 'storefront_header', 'storefront_product_search', 40 );
  remove_action( 'storefront_header', 'storefront_header_cart',    60 );

  add_action( 'storefront_header', 'storefront_handheld_footer_bar', 0 );
  add_action( 'storefront_before_content', 'custom_banner', 10 );
}

function custom_banner() {
  if( is_front_page() ) {
    $id = get_option( 'page_on_front' );
    $featured_image = get_the_post_thumbnail_url( $id, 'full' );
    if ( $featured_image ) {
      ?>
      <div class="site-banner" style="background-image: url(<?php echo $featured_image; ?>)"/></div>
      <?php
    }
  }
}


// Custom footer
add_action( 'init', 'custom_remove_footer_credit', 10 );
function custom_remove_footer_credit () {
  remove_action( 'storefront_footer', 'storefront_credit', 20 );
  remove_action( 'storefront_footer', 'storefront_footer_widgets', 10 );
  remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );
  
  add_action( 'storefront_footer', 'custom_storefront_credit', 20 );
}

function display_site_title() {
  $title = get_bloginfo( 'name' );
  $link = get_site_url();
  echo "<h1 class='woocommerce-products-header__title alpha page-title'><a href=${link}>${title}</a></>";
}

add_filter( 'storefront_handheld_footer_bar_links', 'footer_bar_links' );
function footer_bar_links( $links ) {
  $new_links = array(
    'site_title' => array(
      'priority' => 10,
      'callback' => 'display_site_title',
    ),
    'cart'       => array(
      'priority' => 30,
      'callback' => 'storefront_handheld_footer_bar_cart_link',
    ),
  );
  return $new_links;
}

// Remove storefront home sections
// add_action( 'init', 'remove_storefront_home_actions' );
// function remove_storefront_home_actions() {
//   // new WP_Query( $args ); 

//   remove_action( 'homepage', 'storefront_homepage_content',      10 );
//   remove_action( 'homepage', 'storefront_product_categories',    20 );
//   remove_action( 'homepage', 'storefront_recent_products',       30 );
//   remove_action( 'homepage', 'storefront_featured_products',     40 );
//   remove_action( 'homepage', 'storefront_popular_products',      50 );
//   remove_action( 'homepage', 'storefront_on_sale_products',      60 );
//   remove_action( 'homepage', 'storefront_best_selling_products', 70 );
//   remove_action( 'storefront_homepage', 'storefront_homepage_header',      10 );
//   remove_action( 'storefront_homepage', 'storefront_page_content',         20 );
//   add_action( 'woocommerce_before_main_content', 'storefront_homepage_content', 10 );
// }

function get_blog_users( $blog_id, $role='administrator' ) {
  $users = get_users( array( 
      'blog_id' => $blog_id,
      'role' => $role 
  ) );
  return $users;
}

function custom_storefront_credit() {
  $footer_data = array();
  $blog_id = get_current_blog_id();

  if ( $blog_id ) {
    $admins = get_blog_users( $blog_id );

    if ( count($admins) > 0 ) {
      $owner    = $admins[0];
      $owner_id = $owner->ID;

      $footer_data  = array(
        'blog_id'   => $blog_id,
        'ruc'       => get_blog_option( $blog_id, 'ruc', true ),
        'empresa'   => get_blog_option( $blog_id, 'blogname', true ),
        'te_vende'  => $owner->display_name
      );
    }
  }

  ?>
  <ul class="site-info">
    <?php if($footer_data['ruc']) : ?>
      <li>
        <strong>RUC: </strong>
        <span><?php echo( $footer_data['ruc'] ) ?></span>
      </li>
    <?php endif; ?>
    <li>
      <strong>Empresa: </strong>
      <span><?php echo( $footer_data['empresa'] ); ?></span></span>
    </li>
    <li>
      <strong>Te Vende: </strong>
      <span><?php echo( $footer_data['te_vende'] ) ?></span>
    </li>
  </ul><!-- .site-info -->
  <?php
}

/** 
 * Adds bank info to ref number fields
 */
add_action( 'ref_number_before_fields', 'add_extra_data_to_ref_number' );
function add_extra_data_to_ref_number() {
  $blog_id = get_current_blog_id();
  if ( $blog_id ) {
    $account_number = get_blog_option( $blog_id, 'bank_account', true );
    $account_number = $account_number ? $account_number : '';
    echo wp_kses_post(__('Número de cuenta de Banco', 'ref_number') . ': <strong>' . $account_number . '</strong>');
  }
}

// Quantity on product display
add_filter( 'woocommerce_loop_add_to_cart_link', 'woa_add_quantity_fields', 10, 2 );
function woa_add_quantity_fields($html, $product) {
  //add quantity field only to simple products
  if ($product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ) {
    //rewrite form code for add to cart button
    $html = '<form action="' . esc_url( $product->add_to_cart_url() ) . '" class="cart" method="post" enctype="multipart/form-data">';
    $html .= woocommerce_quantity_input( array(), $product, false );
    $html .= '<div class="buttons-wrapper">';
    $html .= '<button type="submit" data-quantity="1" data-product_id="' . $product->id . '" class="button alt ajax_add_to_cart add_to_cart_button product_type_simple">' . esc_html( $product->add_to_cart_text() ) . '</button>';
    $html .= '<button type="submit" class="button alt product_type_simple buy_now_button">' . esc_html( 'Comprar ahora' ) . '</button>';
    $html .= '<input type="hidden" name="is_buy_now" class="is_buy_now_input" value="0" />';
    $html .= '</div>';
    $html .= '</form>';
  }
  return $html;
}

add_filter( 'woocommerce_after_add_to_cart_button', 'single_product_add_to_cart', 10, 2 );
function single_product_add_to_cart() {
  global $product;
  $html = '<button type="submit" class="button alt product_type_simple buy_now_button">' . esc_html( 'Comprar ahora' ) . '</button>';
  $html .= '<input type="hidden" name="is_buy_now" class="is_buy_now_input" value="0" />';
  $html .= '<input type="hidden" name="add_to_cart_url" class="add_to_cart_url" value="' . esc_html( $product->add_to_cart_url() ) . '" />';
  echo $html;
}

// Redirect to checkout if is buy now vakue
add_filter( 'woocommerce_add_to_cart_redirect', 'redirect_to_checkout' );
function redirect_to_checkout( $redirect_url ) {
  if ( isset( $_REQUEST['is_buy_now'] ) && $_REQUEST['is_buy_now'] ) {
    global $woocommerce;
    $redirect_url = wc_get_checkout_url();
  }
  return $redirect_url;
}

// hide coupon field on checkout page
add_filter( 'woocommerce_coupons_enabled', 'hide_coupon_field_on_checkout' );
function hide_coupon_field_on_checkout( $enabled ) {
  return false;
}

// Set store title (shop page)
add_filter( 'woocommerce_page_title', 'set_store_title' ); 
function set_store_title() {
  return get_bloginfo( 'name' );
}


//  Custom checkout
add_action( 'init', 'custom_edit_checkout_page', 10 );
function custom_edit_checkout_page () {
  remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
  add_action( 'woocommerce_checkout_after_customer_details', 'woocommerce_checkout_payment', 20 );
}

// Modify woocommerce checkout fields
add_filter( 'woocommerce_checkout_fields' , 'override_checkout_fields' );
function override_checkout_fields( $fields ) {
  unset($fields['billing']['billing_company']);
  unset($fields['billing']['billing_address_2']);
  unset($fields['billing']['billing_city']);
  unset($fields['billing']['billing_postcode']);
  unset($fields['billing']['billing_state']);
  unset($fields['billing']['billing_country']);
  unset($fields['billing']['billing_company']);
  $fields['billing']['billing_address_1']['label'] = 'Dirección completa';
  $fields['billing']['billing_address_1']['placeholder'] = '';
  $fields['billing']['billing_dni'] = array(
    'label'     => __('Numero DNI', 'woocommerce'),
    'placeholder'   => _x('', 'placeholder', 'woocommerce'),
    'required'  => false,
    'class'     => array('form-row-wide'),
    'clear'     => true
    );
  return $fields;
}

add_action( 'woocommerce_checkout_update_order_meta', 'checkout_field_update_order_meta' );
function checkout_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['billing_dni'] ) ) {
        update_post_meta( $order_id, '_billing_dni', sanitize_text_field( $_POST['billing_dni'] ) );
    }
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'checkout_field_display_admin_order_meta', 10, 1 );
function checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('DNI').':</strong> ' . get_post_meta( $order->id, '_billing_dni', true ) . '</p>';
}

// Order button text
add_filter( 'woocommerce_order_button_text', 'woo_custom_order_button_text' ); 
function woo_custom_order_button_text() {
    return __( 'Realiza tu pedido', 'woocommerce' ); 
}

// Single products
add_action('init', 'single_product_hooks');
function single_product_hooks() {
  remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
  remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
  remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
}

/** 
 * Manipulate default state and countries
 */
add_filter( 'default_checkout_country', 'change_default_checkout_country' );
add_filter( 'default_checkout_state', 'change_default_checkout_state' );

function change_default_checkout_country() {
  return 'PE'; // country code
}

function change_default_checkout_state() {
  return 'PE:LIM'; // state code
}

function user_id_exists( $user_id ) {
  global $wpdb;
  $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users WHERE ID = %d", $user_id ) );
  return empty( $count ) || 1 > $count ? false : true;
}

// Woocommerce API stuff

if ( class_exists( 'WooCommerce' ) ) {
  include_once dirname( __FILE__ ) . '/api/ga-ecommerce-api-controller.php';
  $controller = new GaEcommerceAPIController;
  $controller->register_routes();
}

// Default woocommerce payment gateways
add_filter( 'woocommerce_available_payment_gateways', 'add_default_gateways' );
function add_default_gateways( $available_gateways ) {
  if ( class_exists( 'WC_Ref_Number_Gateway' ) ) {
    $all_gateways = WC()->payment_gateways->payment_gateways();
    $allowed_gateways['ref_number'] = $all_gateways['ref_number'];
    $allowed_gateways['cod'] = $all_gateways['cod'];
  }
	return $allowed_gateways;
}

add_filter( 'wpseo_opengraph_image', 'wpseo_change_ogimage' );
add_filter( 'wpseo_twitter_image','wpseo_change_ogimage' );
function wpseo_change_ogimage( $link ) {
  if ( is_front_page() ) {
    $id = get_option( 'page_on_front' );
    return get_the_post_thumbnail_url( $id, 'thumbnail' );
  }
  return $link;
}

// Add short description to products
add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_after_shop_loop_item_title_short_description', 5);
function woocommerce_after_shop_loop_item_title_short_description() {
  global $product;
  $description = $product->get_short_description() ? $product->get_short_description() : wp_trim_words( wp_strip_all_tags( $product->get_description() ), 30 );
  ?>
  <div itemprop="description">
    <?php echo apply_filters( 'woocommerce_short_description', $description ) ?>
  </div>
  <?php
}

?>
