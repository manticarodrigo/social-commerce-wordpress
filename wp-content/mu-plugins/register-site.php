<?php
/**
 *
 * @global type $wpdb
 */

// add_action( 'user_register', 'register_user_site', 10, 1 );

function register_user_site( $user_id ) {

  $user = get_userdata( $user_id ); // get user object
  $domain = get_site_url( 1, '', null ); // get main site url
  $path = 'ooqia'; // strtolower( $user->site_name ); // get lowercase site name
  $title = 'OOQIA'; // $user->site_name; // get site title

  // Create user site in multisite
  $site_id = wpmu_create_blog( $domain, $path, $title, $user_id , array( 'public' => 1 ), null );

  // Update user site id in meta
  update_user_meta( $user_id, 'site_id', $site_id );

}
?>