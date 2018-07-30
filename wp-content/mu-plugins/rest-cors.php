<?php

// add_filter( 'allowed_http_origin', '__return_true' );

// add_filter('http_origin', function() {
//   return "http://localhost:3000";
// });

// add_action( 'rest_api_init', function() {
// 	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
// 	add_filter( 'rest_pre_serve_request', function( $value ) {
// 		header( 'Access-Control-Allow-Origin: *' );
// 		header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
// 		header( 'Access-Control-Allow-Credentials: true' );
//     header("Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept");
// 		return $value;
// 	});
// }, 15 );

// add_action( 'init', 'handle_preflight' );
// function handle_preflight() {
//   header("Access-Control-Allow-Origin: " . get_http_origin());
//   header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
//   header("Access-Control-Allow-Credentials: true");
//   header("Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept");
//   if ( 'OPTIONS' == $_SERVER['REQUEST_METHOD'] ) {
//     status_header(200);
//     exit();
//   }
// }

// add_filter( 'wp_headers', array( 'eg_send_cors_headers' ), 11, 1 );
// function eg_send_cors_headers( $headers ) {
//   $headers['Access-Control-Allow-Origin']      = get_http_origin(); // Can't use wildcard origin for credentials requests, instead set it to the requesting origin
//   $headers['Access-Control-Allow-Credentials'] = 'true';
//   // Access-Control headers are received during OPTIONS requests
//   if ( 'OPTIONS' == $_SERVER['REQUEST_METHOD'] ) {
//     if ( isset( $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ) ) {
//       $headers['Access-Control-Allow-Methods'] = 'GET, POST, OPTIONS';
//     }
//     if ( isset( $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ) ) {
//       $headers['Access-Control-Allow-Headers'] = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'];
//     }
//   }
//   return $headers;
// }

?>