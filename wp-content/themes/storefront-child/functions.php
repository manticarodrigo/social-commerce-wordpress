<?php 

/*
 * Automagically authorize every request
 * INSECURE! DANGER! ONLY USE IN LOCAL ENVIRONMENT.
 */
add_filter( 'rest_authentication_errors', function(){
    wp_set_current_user( 1 ); // replace with the ID of a WP user with the authorization you want
}, 101 );


// Set origin for JSON API (not official)
add_action( 'json_api', function( $controller, $method ) {
  // header( "Access-Control-Allow-Origin: *" );
  $http_origin = $_SERVER['HTTP_ORIGIN'];
  if ($http_origin == "http://localhost:3000" || $http_origin == "https://tienda.peritagua.com" || $http_origin == "https://tienda.heyshopper.co") {  
    header( "Access-Control-Allow-Origin: $http_origin" );
		header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
		header( 'Access-Control-Allow-Credentials: true' );
  }
}, 10, 2 );


// Adding custom scripts
add_action( 'wp_enqueue_scripts', 'storefront_enqueue_styles' );
function storefront_enqueue_styles() {
  wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}


// Remove sidebar
add_action( 'get_header', 'remove_storefront_sidebar' );
function remove_storefront_sidebar() {
  if ( is_woocommerce() ) {
    remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
  }
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

/**
 * Display category image on category archive
 */
add_action( 'woocommerce_archive_description', 'woocommerce_category_image', 2 );
function woocommerce_category_image() {
  if ( is_product_category() ){
    global $wp_query;
    $cat = $wp_query->get_queried_object();
    $thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
    $image = wp_get_attachment_url( $thumbnail_id );
    if ( $image ) {
      echo '<div class="cat-banner" style="background-image: url( '. $image . ')"></div>';
    }
  }
}

// Scripts
add_action( 'wp_enqueue_scripts', 'my_assets' );
function my_assets() {
  wp_enqueue_script( 'jqueryflip', 'https://cdn.rawgit.com/nnattawat/flip/master/dist/jquery.flip.min.js', array( 'jquery' ), '1.0', true ); 
  wp_enqueue_script( 'theme-scripts', get_stylesheet_directory_uri() . '/script.js', array('jquery' ), '1.0', true );
}

// Disable woocommerce product detail
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

// Custom footer
add_action( 'init', 'custom_remove_footer_credit', 10 );
function custom_remove_footer_credit () {
  remove_action( 'storefront_footer', 'storefront_credit', 20 );
  remove_action( 'storefront_footer', 'storefront_footer_widgets', 10 );
  add_action( 'storefront_footer', 'custom_storefront_credit', 20 );
}

function custom_storefront_credit() {
  $term = get_queried_object();
  $footer_data = array();
  // @TODO move this to cookies
  $trasient_data = get_transient( 'footer_data' );

  if ( $term->term_id ) {
    $term_id  = $term->term_id;
    $owner_id = get_term_meta( $term_id, '_owner_id', true );
    $owner    = get_userdata( intval($owner_id) );

    $footer_data  = array(
      'term_id'   => $term_id,
      'ruc'       => get_term_meta( $term_id, '_ruc', true ),
      'empresa'   => $term->name,
      'te_vende'  => $owner->first_name
    );
    // Saving in case not in term page
    set_transient( 'footer_data', $footer_data, DAY_IN_SECONDS );
  } else if ( $trasient_data ) {
    $footer_data = $trasient_data;
  }

  ?>
  <ul class="site-info"> 
    <li>
      <strong>RUC: </strong>
      <span><?php echo( $footer_data['ruc'] ) ?></span>
    </li>
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
 * Wrap Archive Product Loop Image WooCommerce
 */
add_action( 'woocommerce_before_shop_loop' , 'woo_wrap_loop_product_image', 3 );
function woo_wrap_loop_product_image() {
  if ( ! class_exists( 'WooCommerce' ) ) return; //* exit early if WooCommerce not active/installed
  add_action( 'woocommerce_before_shop_loop_item_title' , 'woo_product_loop_image_wrapper_open', 9 );
  add_action( 'woocommerce_shop_loop_item_title' , 'woo_product_loop_image_wrapper_close', 9 );
}

//open my-class-name
function woo_product_loop_image_wrapper_open() {
  echo '<div class="card-flip"><div class="front">';
}

//open my-class-name
function woo_product_loop_image_wrapper_close() {
  echo '</div>';
  
  global $product;
  if ( ! $product->get_description() ) return;

  echo '<div class="back">'. apply_filters( 'woocommerce_description', wp_trim_words( $product->get_description(), $num_words = 55, $more = null ) ) .'</div>';
  echo '</div>';
}

// Quantity on product display
add_filter( 'woocommerce_loop_add_to_cart_link', 'woa_add_quantity_fields', 10, 2 );
function woa_add_quantity_fields($html, $product) {
  //add quantity field only to simple products
  if ( $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ) {
    //rewrite form code for add to cart button
    $html = '<form action="' . esc_url( $product->add_to_cart_url() ) . '" class="cart" method="post" enctype="multipart/form-data">';
    $html .= woocommerce_quantity_input( array(), $product, false );
    $html .= '<button type="submit" data-quantity="1" data-product_id="' . $product->id . '" class="button alt ajax_add_to_cart add_to_cart_button product_type_simple">' . esc_html( $product->add_to_cart_text() ) . '</button>';
    $html .= '</form>';
  }
  return $html;
}

// hide coupon field on checkout page
function hide_coupon_field_on_checkout( $enabled ) {
  return false;
}
add_filter( 'woocommerce_coupons_enabled', 'hide_coupon_field_on_checkout' );


//  Custom checkout
add_action( 'init', 'custom_edit_checkout_page', 10 );
function custom_edit_checkout_page () {
  remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
  add_action( 'woocommerce_checkout_after_customer_details', 'woocommerce_checkout_payment', 20 );
}

// Order button text
add_filter( 'woocommerce_order_button_text', 'woo_custom_order_button_text' ); 
function woo_custom_order_button_text() {
    return __( 'Realiza tu pedido', 'woocommerce' ); 
}

// Remove Related products
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

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

add_action('wp_head', 'cat_opengraph_image', 5);
function cat_opengraph_image() {
 
    // If it's not a category, die.
    if ( !is_product_category() ) {
        return;
    }

    global $wp_query;
    $cat = $wp_query->get_queried_object();
    $thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
    $image = wp_get_attachment_url( $thumbnail_id );
    if ( $image ) {
      echo '<meta property="og:image" content="'.$image.'" />';
    }

}

// add_action( 'init', 'setting_category_cookie' );
// function setting_category_cookie() {
//   setcookie( $v_username, $v_value, 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
// }

add_filter( 'woocommerce_email_recipient_customer_processing_order', 'add_recipient', 20, 2 );
add_filter( 'woocommerce_email_recipient_customer_completed_order', 'add_recipient', 20, 2 );
add_filter( 'woocommerce_email_recipient_customer_note', 'add_recipient', 20, 2 );
/**
 * Add recipient to emails
 *
 * @var  str $email, comma-delimited list of addresses
 * @return  str
 */
function add_recipient( $email, $order ) {
  // @TODO This must be with cookies
  $trasient_data = get_transient( 'footer_data' );
  if ( $trasient_data && isset( $trasient_data['term_id'] ) ) {
    $term_id = $trasient_data['term_id'];
    $owner_id = get_term_meta( intval($term_id), '_owner_id', true );
    if ( $owner_id ) {
      $owner = get_userdata( intval($owner_id) );
      $additional_email = $owner->user_email;

      if ( $additional_email && is_email( $additional_email ) ) {
        $email = explode( ',', $email );
        array_push( $email, $additional_email );
        $email = implode( ',', $email );
      }
    }
  }
  return $email;
}


add_filter( 'get_the_archive_title', 'remove_category_prefix_from_archive_title' );
function remove_category_prefix_from_archive_title( $title ) {
  if ( is_category() || is_product_category() ) {
    $title = single_cat_title( '', false );
  } elseif ( is_tag() ) {
    $title = single_tag_title( '', false );
  } elseif ( is_author() ) {
    $title = '<span class="vcard">' . get_the_author() . '</span>' ;
  }
  return $title;
}


// Woocommerce API stuff

if ( class_exists( 'WooCommerce' ) ) {
	include_once dirname( __FILE__ ) . '/api/wc-product-cat-custom.php';
  $controller = new WC_REST_Product_Categories_Custom_Controller;
  $controller->register_routes();

  include_once dirname( __FILE__ ) . '/api/ga-ecommerce-api-controller.php';
  $controller = new GaEcommerceAPIController;
  $controller->register_routes();
}

// Set this at the end or any custom endpoint will work
add_filter( 'rest_endpoints', function( $endpoints ){
  // Disabling endpoints
  if ( isset( $endpoints['/wc/v2/products/categories'] ) ) {
    unset( $endpoints['/wc/v2/products/categories'] );
  }
  if ( isset( $endpoints['/wp/v2/products/categrories/(?P<id>[\d]+)'] ) ) {
    unset( $endpoints['/wp/v2/products/categrories/(?P<id>[\d]+)'] );
  }
  return $endpoints;
});


?>
