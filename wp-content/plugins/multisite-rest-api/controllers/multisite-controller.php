<?php

class MultisiteController extends WP_REST_Controller {
    
    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        $version   = '1';
        $namespace = 'multisite/v' . $version;
        $base      = 'sites';
        register_rest_route($namespace, '/' . $base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'get_items'
                ),
                'args' => array()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array(
                    $this,
                    'create_item'
                ),
                'args' => $this->get_endpoint_args_for_item_schema(true)
            )
        ));
        register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'get_item'
                ),
                'args' => array(
                    'context' => array(
                        'default' => 'view'
                    )
                )
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array(
                    $this,
                    'update_item'
                ),
                'args' => $this->get_endpoint_args_for_item_schema(false)
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array(
                    $this,
                    'delete_item'
                ),
                'args' => array(
                    'force' => array(
                        'default' => false
                    )
                )
            )
        ));
        register_rest_route($namespace, '/' . $base . '/schema', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array(
                $this,
                'get_public_item_schema'
            )
        ));
    }

    public function full_path($sitename, $current_site = null) {
		if (empty($current_site))
			$current_site = get_current_site();
		if (is_subdomain_install()) {
			$path = $current_site->path;
		} else {
			$path = $current_site->path . $sitename . '/';
		}
		return $path;
    }
    
    public function full_domain($sitename, $current_site = null) {
		if (empty($current_site))
			$current_site = get_current_site();
		if (is_subdomain_install()) {
			$newdomain = $sitename . '.' . preg_replace( '|^www\.|', '', $current_site->domain );
		} else {
			$newdomain = $current_site->domain;
		}
		return $newdomain;
    }
    
    /**
	 * Creates a new site.
	 * @param title string The title of the site
	 * @param site_name string The sitename used for the site, will become the path or the subdomain
	 * @param user_id The ID of the admin user for this site
	 * @return site Object An objectified version of the site
	 */
	public function create_site($title, $site_name, $user_id) {
		$current_site = get_current_site();
		$site_id = wpmu_create_blog($this->full_domain($site_name, $current_site),
			$this->full_path($site_name, $current_site),
			$title,
			$user_id,
			array('public' => true),
			$current_site->id);
        if(!is_numeric($site_id))
            return new WP_Error('cant-create', __(
                'Error creating site: ' . $site_id->get_error_message()), array(
                'status' => 500
            ));
		else
			return $this->get_blog_details($site_id);
	}
    
    /**
     * Get a collection of items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_items($request) {
        $blogs = get_sites(array(
            'public' => 1,
        ));
        $data  = array();
        foreach ($blog as $blog) {
            $itemdata = $this->prepare_item_for_response($site, $request);
            $data[]   = $this->prepare_response_for_collection($itemdata);
        }
        
        return new WP_REST_Response($data, 200);
    }
    
    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_item($request) {
        //get parameters from request
        $params = $request->get_params();
        $blog   = get_blog_details( $params['id'] );
        $data   = $this->prepare_item_for_response($blog, $request);
        
        //return a response or error based on some conditional
        if ( $blog ) {
            return new WP_REST_Response($data, 200);
        } else {
            return new WP_Error( 'rest_blog_invalid_id', __( 'Invalid blog ID.' ), array( 'status' => 404 ) );
        }
    }
    
    /**
     * Create one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function create_item($request) {
        $item = $this->prepare_item_for_database($request);
        if (!is_wp_error($item) ) {
            $data = $this->create_site(
                $item['title'],
                $item['site_name'],
                $item['user_id']
            );
        } else {
            return $item;
        }

        if ($data) {
            return new WP_REST_Response($data, 200);
        }
    }
    
    /**
     * Update one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function update_item($request) {
        $item = $this->prepare_item_for_database($request);
        
        if (function_exists('slug_some_function_to_update_item')) {
            $data = slug_some_function_to_update_item($item);
            if (is_array($data)) {
                return new WP_REST_Response($data, 200);
            }
        }
        
        return new WP_Error('cant-update', __('message', 'text-domain'), array(
            'status' => 500
        ));
    }
    
    /**
     * Delete one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function delete_item($request) {
        $item = $this->prepare_item_for_database($request);
        
        if (function_exists('slug_some_function_to_delete_item')) {
            $deleted = slug_some_function_to_delete_item($item);
            if ($deleted) {
                return new WP_REST_Response(true, 200);
            }
        }
        
        return new WP_Error('cant-delete', __('message', 'text-domain'), array(
            'status' => 500
        ));
    }
    
    /**
     * Prepare the item for create or update operation
     *
     * @param WP_REST_Request $request Request object
     * @return WP_Error|object $prepared_item
     */
    protected function prepare_item_for_database($request) {
        $title = $request['title'];
        $site_name = $request['site_name'];
        $user_id = $request['user_id'];

        // @TODO validate the previous fields

        return array();
    }
    
    /**
     * Prepare the item for the REST response
     *
     * @param mixed $item WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     * @return mixed
     */
    public function prepare_item_for_response($item, $request) {
        return $item;
    }
    
    /**
     * Get the query params for collections
     *
     * @return array
     */
    public function get_collection_params() {
        return array(
            'page' => array(
                'description' => 'Current page of the collection.',
                'type' => 'integer',
                'default' => 1,
                'sanitize_callback' => 'absint'
            ),
            'per_page' => array(
                'description' => 'Maximum number of items to be returned in result set.',
                'type' => 'integer',
                'default' => 10,
                'sanitize_callback' => 'absint'
            ),
            'user_id' => array(
                'description' => 'User Id of site owner.',
                'type' => 'integer',
                'sanitize_callback' => 'absint'
            )
        );
    }
}