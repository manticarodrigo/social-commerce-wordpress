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
  if ($http_origin == "http://localhost:3000" || $http_origin == "http://socialecommerce.southcentralus.cloudapp.azure.com:81") {  
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
      echo '<img src="' . $image . '" alt="' . $cat->name . '" />';
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

// Cusotm footer
add_action( 'init', 'custom_remove_footer_credit', 10 );
function custom_remove_footer_credit () {
  remove_action( 'storefront_footer', 'storefront_credit', 20 );
  remove_action( 'storefront_footer', 'storefront_footer_widgets', 10 );
  add_action( 'storefront_footer', 'custom_storefront_credit', 20 );
}

function custom_storefront_credit() {
  ?>
  <ul class="site-info"> 
    <li>
      <strong>RUC: </strong>
      <span>12345678910111213</span>
    </li>
    <li>
      <strong>Empresa: </strong>
      <span>Medco</span></span>
    </li>
    <li>
      <strong>Te Vende: </strong>
      <span>José Lopéz</span>
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


// Small product categories API

function profile_endpoint( $request_data ) {

  $taxonomy     = 'product_cat';
  $orderby      = 'name';  
  $show_count   = 0;      // 1 for yes, 0 for no
  $pad_counts   = 0;      // 1 for yes, 0 for no
  $hierarchical = 1;      // 1 for yes, 0 for no  
  $title        = '';  
  $empty        = 0;

  // setup query argument
  $args = array(
    'taxonomy'     => $taxonomy,
    'orderby'      => $orderby,
    'show_count'   => $show_count,
    'pad_counts'   => $pad_counts,
    'hierarchical' => $hierarchical,
    'title_li'     => $title,
    'hide_empty'   => $empty
  );

  // get categories
  $categories = get_categories( $args );

  $data = array();
  // add custom field data to posts array 
  foreach ( $categories as $category ) {
    $category_id              = $category->term_id;
    $category->link           = get_permalink( $category_id );
    $category->image          = get_the_post_thumbnail_url( $category_id );
    $category->dni            = get_woocommerce_term_meta( $category_id, '_dni', true );
    $category->ruc            = get_woocommerce_term_meta( $category_id, '_ruc', true );
    $category->phone          = get_woocommerce_term_meta( $category_id, '_phone', true );
    $category->bank_account   = get_woocommerce_term_meta( $category_id, '_bank_account', true );
    $category->logistic_provider = get_woocommerce_term_meta( $category_id, '_logistic_provider', true );
    $category->owner_id       = get_woocommerce_term_meta( $category_id, '_owner_id', true );
    array_push($data, $category);
  }
  return $data;
}

// register the endpoint
add_action( 'rest_api_init', function () {
  register_rest_route( 'socialcommerce/v1', '/profiles/', array(
      'methods' =>  'GET',
      'callback' => 'profile_endpoint',
    )
  );
});

function profile_create( $request_data ) {
  $data               = array();
  $taxonomy           = 'product_cat';
  
  // Fetching values from API
  $parameters         = $request_data->get_params();
  
  $term_name          = $parameters['businessName'];
  $term_logo          = $parameters['businessLogo'];
  $dni                = $parameters['dni'];
  $ruc                = $parameters['ruc'];
  $phone              = $parameters['phone'];
  $bank_account       = $parameters['bankAccount'];
  $logistic_provider  = $parameters['logisticProvider'];
  
  // We'll need to find a way to validate this.
  $owner_id = $parameters['ownerId'];

  if ( $term_name && $owner_id ) {

      // Create term object
      $term = wp_insert_term( $term_name, $taxonomy );
      
      if ( is_wp_error( $term ) ) {
        $term_id  = $term->error_data['term_exists'] ?? null;
      } else {
        $term_id  = $term['term_id'];
      }

      if ( $term_id) {
        add_woocommerce_term_meta( $term_id, '_dni', $dni, true );
        add_woocommerce_term_meta( $term_id, '_ruc', $ruc, true );
        add_woocommerce_term_meta( $term_id, '_phone', $phone, true );
        add_woocommerce_term_meta( $term_id, '_bank_account', $bank_account, true );
        add_woocommerce_term_meta( $term_id, '_logistic_provider', $logistic_provider, true );
        
        // Not defined yet, we should confirm if is the same of user_id
        add_woocommerce_term_meta($term_id, '_owner_id', $owner_id, true );
        
        $data['status']   = 'Profile added Successfully.';  
        $data['term_id']  = $term_id;
        $data['term_link'] = get_term_link( $term_id, $taxonomy );

        // Set featured Image
        if ( $term_logo ) {
          
          include_once( ABSPATH . 'wp-admin/includes/image.php' );

          $filename   = strtok( basename( $term_logo ), '?' );
          $uploaddir  = wp_upload_dir();
          $uploadfile = $uploaddir['path'] . '/' . $filename;

          $contents = file_get_contents($term_logo);
          $savefile = fopen($uploadfile, 'w');
          fwrite($savefile, $contents);
          fclose($savefile);

          $wp_filetype = wp_check_filetype(basename($filename), null );

          $attachment = array(
            'post_mime_type'  => $wp_filetype['type'],
            'post_title'      => $filename,
            'post_content'    => '',
            'post_status'     => 'inherit'
          );

          $attach_id = wp_insert_attachment( $attachment, $uploadfile );
          $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
          wp_update_attachment_metadata( $attach_id, $attach_data );

          update_woocommerce_term_meta( $term_id, 'thumbnail_id', absint( $attach_id ) );
        }

      }
      else {
        $data['status'] = 'request failed..';
      }

  } else {
    $data['status'] = ' Please provide correct post parameters.';
    $data['parameters'] = $parameters;
  }
  return $data;
}

add_action( 'rest_api_init', function () {
  register_rest_route( 'socialcommerce/v1', '/profiles/create/', array(
      'methods'   => 'POST',
      'callback'  => 'profile_create',
    )
  );
});

?>
