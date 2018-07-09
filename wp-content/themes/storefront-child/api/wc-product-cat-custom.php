<?php
/**
 * REST API Product Categories controller
 *
 * Handles requests to the products/categories endpoint.
 *
 * @package WooCommerce/API
 * @since   2.6.0
 */
defined( 'ABSPATH' ) || exit;
/**
 * REST API Product Categories controller class.
 *
 * @package WooCommerce/API
 * @extends WC_REST_Product_Categories_V1_Controller
 */
class WC_REST_Product_Categories_Custom_Controller extends WC_REST_Product_Categories_Controller {
    /**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
    
    protected $namespace = 'wc/custom';

    /**
	 * Update term meta fields.
	 *
	 * @param WP_Term         $term    Term object.
	 * @param WP_REST_Request $request Request instance.
	 * @return bool|WP_Error
	 */
	protected function update_term_meta_fields( $term, $request ) {
        parent::update_term_meta_fields( $term, $request );

        $id = (int) $term->term_id;
        $approved = false;
        
        $error_data = array( 'status' => 400 );
        if ( !isset( $request['owner_id'] ) ) {
            return new WP_Error( 'no_owner_id', __( 'owner_id not suplied', 'woocommerce' ), $error_data );
        } else {
            if ( user_id_exists( $request['owner_id'] ) ) {
                update_woocommerce_term_meta( $id, '_owner_id', $request['owner_id'] );
            } else {
                return new WP_Error( 'owner_id_invalid', __( 'Not valid owner_id', 'woocommerce' ), $error_data );
            }
        }
        
        if ( isset( $request['dni'] ) ) {
			update_woocommerce_term_meta( $id, '_dni', $request['dni'] );
        }
        if ( isset( $request['ruc'] ) ) {
			update_woocommerce_term_meta( $id, '_ruc', $request['ruc'] );
        }
        if ( isset( $request['phone'] ) ) {
			update_woocommerce_term_meta( $id, '_phone', $request['phone'] );
        }
        if ( isset( $request['bank_account'] ) ) {
			update_woocommerce_term_meta( $id, '_bank_account', $request['bank_account'] );
        }
        if ( isset( $request['logistic_provider'] ) ) {
			update_woocommerce_term_meta( $id, '_logistic_provider', $request['logistic_provider'] );
        }
        if ( isset( $request['approved'] ) ) { 
            update_woocommerce_term_meta( $id, '_approved', $request['approved'] == 'true' ? true : false );
        }
        
        return true;
    }

    
	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
        $params = parent::get_collection_params();

        $params['owner_id'] = array(
			'description'       => __( 'Limit result to a givin owner id.', 'woocommerce' ),
			'type'              => 'integer',
			'default'           => null,
			'validate_callback' => 'rest_validate_request_arg',
        );
        return $params;
    }
}

add_filter( 'woocommerce_rest_prepare_product_cat', 'custom_products_cat_api_data', 90, 2 );
function custom_products_cat_api_data( $response, $item ) {
  // retrieve a custom fields and add it to API response
  $response->data['dni']                = get_woocommerce_term_meta( $item->term_id, '_dni', true );
  $response->data['ruc']                = get_woocommerce_term_meta( $item->term_id, '_ruc', true );
  $response->data['phone']              = get_woocommerce_term_meta( $item->term_id, '_phone', true );
  $response->data['bank_account']       = get_woocommerce_term_meta( $item->term_id, '_bank_account', true );
  $response->data['logistic_provider']  = get_woocommerce_term_meta( $item->term_id, '_logistic_provider', true );
  $response->data['owner_id']           = get_woocommerce_term_meta( $item->term_id, '_owner_id', true );
  $response->data['term_link']          = get_term_link( $item->term_id, $taxonomy );
  $response->data['approved']           = get_woocommerce_term_meta( $item->term_id, '_approved', true ) == true;
  return $response;
}

add_filter( 'woocommerce_rest_product_cat_query', 'custom_products_cat_api_query', 20, 2 );
function custom_products_cat_api_query( $prepared_args, $request ) {
    if ( isset( $request['owner_id'] ) ) {
        $prepared_args['meta_key'] = '_owner_id';
        $prepared_args['meta_value'] = (int) $request['owner_id'];
    }
  return $prepared_args;
}