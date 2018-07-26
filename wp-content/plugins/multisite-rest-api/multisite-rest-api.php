<?php
/*
 * Plugin Name: Multisite REST API
 * Plugin URI: http://ooqia.com
 * Description: Handlers of multisite API for heyshopper.co.
 * Author: Ooqia
 * Author URI: http://ooqia.com
 * Version: 1.0.0
 *
 */

add_action('init', 'load_controllers');
function load_controllers() {
    include_once dirname( __FILE__ ) . '/controllers/multisite-controller.php';
    $controller = new MultisiteController;
    $controller->register_routes();
}
