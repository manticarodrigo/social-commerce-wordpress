<?php
/**
 *
 * @global type $wpdb
 * Display a list of sites in network for marketplace
 */

add_shortcode( 'wpmu_sites', 'wpmu_list_sites' );

function wpmu_list_sites() {
	
	$subsites = get_sites();
	
	if ( ! empty ( $subsites ) ) {
    
    wp_enqueue_style('sitedirectory-styles', plugin_dir_url( __FILE__ ) . './sitedirectory.css' );
    
		echo '<section class="subsites-container">';
		
			echo '<ul class="subsites">';
	
			foreach( $subsites as $subsite ) {
			
				$subsite_id = get_object_vars( $subsite )["blog_id"];
				$subsite_name = get_blog_details( $subsite_id )->blogname;
				$subsite_link = get_blog_details( $subsite_id )->siteurl;
				echo '<li class="site-' . $subsite_id . '"><a href="' . $subsite_link . '">' . $subsite_name . '</a></li>';
		
			}
			
			echo '</ul>';
			
		echo '</section>';
	
	}
	
}

?>