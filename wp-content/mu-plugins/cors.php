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

add_action( 'init', 'handle_preflight' );
function handle_preflight() {
  header("Access-Control-Allow-Origin: " . get_http_origin());
  header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Credentials: true");
  header("Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept");
  if ( 'OPTIONS' == $_SERVER['REQUEST_METHOD'] ) {
    status_header(200);
    exit();
  }
}

?>