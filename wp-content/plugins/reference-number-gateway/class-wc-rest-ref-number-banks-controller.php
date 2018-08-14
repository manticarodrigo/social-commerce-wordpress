<?php
/**
 * REST API WC Ref number banks controller
 *
 * Handles requests to the /ref_number/bansk endpoint.
 *
 * @package ref_number
 * @since   1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Payment ref number banks controller class.
 *
 * @package ref_number
 * @extends WC_REST_Controller
 */
class WC_REST_Ref_Number_Banks_Controller extends WC_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'ref_numbers';

	/**
	 * Register the route for /ref_numbers
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace, '/' . $this->rest_base, array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					// 'args'                => $this->get_collection_params(),
                ),
                array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_items' ),
					'permission_callback' => array( $this, 'update_items_permissions_check' ),
					// 'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Check whether a given request has permission to view payment gateways.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'payment_gateways', 'read' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check whether a given request has permission to edit payment gateways.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function update_items_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'payment_gateways', 'edit' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_edit', __( 'Sorry, you are not allowed to edit this resource.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Get banks.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$banks = array();
		$response = array();
		foreach ( $banks as $bank ) {
			$bank_             = $this->prepare_item_for_response( $bank, $request );
			$bank_             = $this->prepare_response_for_collection( $bank_ );
			$response[]        = $bank_;
		}
		return rest_ensure_response( $response );
	}

	/**
	 * Update A Single Payment Method.
	 *
	 * @param WP_REST_Request $request Request data.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_items( $request ) {
		$banks = $this->get_banks( $request );
		foreach ( $banks as $bank ) {
			$bank_             = $this->prepare_item_for_response( $bank, $request );
			$bank_             = $this->prepare_response_for_collection( $bank_ );
			$response[]        = $bank_;
		}
		return rest_ensure_response( $banks );
	}

	public function prepare_item_for_response( $bank, $request ) {
		$order = (array) get_option( 'woocommerce_gateway_order' );
        $item  = $bank;

        // Check this
		$data    = $this->add_additional_fields_to_object( $item, $request );
		$response = rest_ensure_response( $data );
		return $response;
	}


	/**
	 * Get any query params needed.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}

}
