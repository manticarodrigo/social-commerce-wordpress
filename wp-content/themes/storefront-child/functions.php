<?php 
add_action( 'wp_enqueue_scripts', 'storefront_enqueue_styles' );
function storefront_enqueue_styles() {
  wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}

add_action( 'get_header', 'remove_storefront_sidebar' );
function remove_storefront_sidebar() {
  if ( is_woocommerce() ) {
    remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
  }
}

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

?>
