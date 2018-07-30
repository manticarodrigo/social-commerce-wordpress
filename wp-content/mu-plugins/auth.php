<?php

/*
 * Automagically authorize every request
 * INSECURE! DANGER! ONLY USE IN LOCAL ENVIRONMENT.
 */
add_filter( 'rest_authentication_errors', function(){
  wp_set_current_user( 1 ); // replace with the ID of a WP user with the authorization you want
}, 101 );

?>