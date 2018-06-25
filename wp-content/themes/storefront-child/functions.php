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
        wp_enqueue_script( 'theme-scripts', get_stylesheet_directory_uri() . '/script.js', array('jquery' ), '1.0', true );
    }
?>