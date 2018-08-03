<?php 

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
 * Display site image on front page
 */
// add_action( 'the_content', 'storefront_display_custom_banner' );
// function storefront_display_custom_banner() {
//   if ( !is_home() ) {
//     return;
//   }
//   $blog_id = get_current_blog_id();
//   if ( $blog_id ) {
//     $thumbnail_id = get_blog_option( $blog_id, 'banner_id', true );
//     switch_to_blog( 1 );
//     $image = wp_get_attachment_url( intval( $thumbnail_id ) );
//     restore_current_blog();
//     echo '<h1>' . get_option('blogname') . '</h1>';
//     if ( $image ) {
//       echo '<div class="cat-banner" style="background-image: url( '. $image . ')"></div>';
//     }
//   }
// }

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

// Add custom woocommerce checkout fields
// add_action( 'woocommerce_before_order_notes', 'custom_checkout_field' );
// function custom_checkout_field( $checkout ) {
//     echo '<div id="dni">';
//     woocommerce_form_field( 'dni', array(
//         'type'          => 'text',
//         'class'         => array('form-row-wide'),
//         'label'         => __('Numero DNI'),
//         'placeholder'   => __(''),
//         'required'      => false
//         ), $checkout->get_value( 'dni' ));
//     echo '</div>';
// }

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
    if ( !is_front_page() ) {
        return;
    }

    $blog_id = get_current_blog_id();
    if ( $blog_id ) {
      $thumbnail_id = get_blog_option( $blog_id, 'banner_id', true );
      $image = wp_get_attachment_url( intval( $thumbnail_id ) );
      if ( $image ) {
        echo '<meta property="og:image" content="'.$image.'" />';
      }
    }

}

// add_filter( 'woocommerce_email_recipient_customer_processing_order', 'add_recipient', 20, 2 );
// add_filter( 'woocommerce_email_recipient_customer_completed_order', 'add_recipient', 20, 2 );
// add_filter( 'woocommerce_email_recipient_customer_note', 'add_recipient', 20, 2 );
// /**
//  * Add recipient to emails
//  *
//  * @var  str $email, comma-delimited list of addresses
//  * @return  str
//  */
// function add_recipient( $email, $order ) {
//   $term_id = get_active_term_id();
//   if ( $term_id ) {
//     $owner_id = get_term_meta( intval($term_id), '_owner_id', true );
//     if ( $owner_id ) {
//       $owner = get_userdata( intval($owner_id) );
//       $additional_email = $owner->user_email;

//       if ( $additional_email && is_email( $additional_email ) ) {
//         $email = explode( ',', $email );
//         array_push( $email, $additional_email );
//         $email = implode( ',', $email );
//       }
//     }
//   }
//   return $email;
// }


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
  include_once dirname( __FILE__ ) . '/api/ga-ecommerce-api-controller.php';
  $controller = new GaEcommerceAPIController;
  $controller->register_routes();
}

// Ref number gateway 
add_filter( 'woocommerce_available_payment_gateways', 'add_ref_number_gateway' );
function add_ref_number_gateway( $available_gateways ) {
  if ( class_exists( 'WC_Ref_Number_Gateway' ) ) {
    $all_gateways = WC()->payment_gateways->payment_gateways();
    $allowed_gateways['ref_number'] = $all_gateways['ref_number'];
  }
	return $allowed_gateways;
}

// Remove storefront home sections
add_action( 'wp_head', 'remove_storefront_home_actions' );
function remove_storefront_home_actions() {
	// remove_action( 'homepage', 'storefront_homepage_content',      10 );
  remove_action( 'homepage', 'storefront_product_categories',    20 );
  remove_action( 'homepage', 'storefront_recent_products',       30 );
  remove_action( 'homepage', 'storefront_featured_products',     40 );
  remove_action( 'homepage', 'storefront_popular_products',      50 );
  remove_action( 'homepage', 'storefront_on_sale_products',      60 );
  remove_action( 'homepage', 'storefront_best_selling_products', 70 );
  add_action( 'homepage', 'all_products', 20);
}

function all_products() {
  ?>
    <ul class="products">
    <?php
      $args = array(
        'post_type' => 'product',
        'posts_per_page' => 12
        );
      $loop = new WP_Query( $args );
      if ( $loop->have_posts() ) {
        while ( $loop->have_posts() ) : $loop->the_post();
          wc_get_template_part( 'content', 'product' );
        endwhile;
      } else {
        echo __( 'No products found' );
      }
      wp_reset_postdata();
    ?>
    </ul><!--/.products-->
  <?php
}

?>
