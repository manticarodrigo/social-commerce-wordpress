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
  $http_origin = $_SERVER['HTTP_ORIGIN'];
  if ($http_origin == "http://localhost:3000" || $http_origin == "https://tiendas.peritagua.com") {  
    header( "Access-Control-Allow-Origin: $http_origin" );
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


// remove product-category from categories
add_filter('request', function( $vars ) {
	global $wpdb;
	if( ! empty( $vars['pagename'] ) || ! empty( $vars['category_name'] ) || ! empty( $vars['name'] ) || ! empty( $vars['attachment'] ) ) {
		$slug = ! empty( $vars['pagename'] ) ? $vars['pagename'] : ( ! empty( $vars['name'] ) ? $vars['name'] : ( !empty( $vars['category_name'] ) ? $vars['category_name'] : $vars['attachment'] ) );
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT t.term_id FROM $wpdb->terms t LEFT JOIN $wpdb->term_taxonomy tt ON tt.term_id = t.term_id WHERE tt.taxonomy = 'product_cat' AND t.slug = %s" ,array( $slug )));
		if( $exists ){
			$old_vars = $vars;
			$vars = array('product_cat' => $slug );
			if ( !empty( $old_vars['paged'] ) || !empty( $old_vars['page'] ) )
				$vars['paged'] = ! empty( $old_vars['paged'] ) ? $old_vars['paged'] : $old_vars['page'];
			if ( !empty( $old_vars['orderby'] ) )
	 	        	$vars['orderby'] = $old_vars['orderby'];
      			if ( !empty( $old_vars['order'] ) )
 			        $vars['order'] = $old_vars['order'];	
		}
	}
	return $vars;
});

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
  $trasient_data = get_transient( 'footer_data' );

  if ( $term->term_id ) {
    $term_id  = $term->term_id;
    $owner_id = get_term_meta( $term_id, '_owner_id', true );
    $owner    = get_userdata( intval($owner_id) );

    $footer_data  = array(
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

  echo '<div class="back">'. apply_filters( 'woocommerce_description', $product->get_description() ) .'</div>';
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

// API stuff
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

include_once dirname( __FILE__ ) . '/api/wc-product-cat-custom.php';
$controller = new WC_REST_Product_Categories_Custom_Controller;
$controller->register_routes();

?>
