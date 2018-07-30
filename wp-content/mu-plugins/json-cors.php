<?php

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

?>