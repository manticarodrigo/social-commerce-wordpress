<?php

class MultisiteController extends WP_REST_Controller {

    public function __construct() {
        require_once( ABSPATH . 'wp-admin/includes/admin.php' ); // To load wpmu_delete_blog
    }
    
    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        $version   = '1';
        $namespace = 'multisite/v' . $version;
        $base      = 'sites';
        register_rest_route( $namespace, '/' . $base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'get_items'
                ),
                'args' => $this->get_collection_params()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array(
                    $this,
                    'create_item'
                ),
                'args' => $this->get_creation_parameters()
            )
        ));
        register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_item' )
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array( $this, 'update_item' ),
                'args' => $this->get_creation_parameters()
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
        register_rest_route( $namespace, '/' . $base . '/schema', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array(
                $this,
                'get_public_item_schema'
            )
        ));
    }

    public function full_path( $sitename, $current_site = null ) {
		if ( empty( $current_site ) )
			$current_site = get_current_site();
		if ( is_subdomain_install() ) {
			$path = $current_site->path;
		} else {
			$path = $current_site->path . $sitename . '/';
		}
		return $path;
    }
    
    public function full_domain( $sitename, $current_site = null ) {
		if ( empty( $current_site) )
			$current_site = get_current_site();
		if ( is_subdomain_install() ) {
			$newdomain = $sitename . '.' . preg_replace( '|^www\.|', '', $current_site->domain );
		} else {
			$newdomain = $current_site->domain;
		}
		return $newdomain;
    }

    /*
     * Wraps the wordpress get_blog_details function.
     * @since '0.5.0'
     */
    public function get_site_by_id( $id ) {
        $site = get_blog_details( $id );
        if ( $site && !is_wp_error( $site ) )
            return $site;
        else
            return $site;
    }
    
    /**
	 * Creates a new site.
	 * @param title string The title of the site
	 * @param site_name string The sitename used for the site, will become the path or the subdomain
	 * @param user_id The ID of the admin user for this site
	 * @return site Object An objectified version of the site
	 */
	public function create_site( $title, $site_name, $user_id ) {
		$current_site = get_current_site();
		$site_id = wpmu_create_blog(
            $this->full_domain( $site_name, $current_site ),
			$this->full_path($site_name, $current_site),
			$title,
			$user_id,
			array('public' => true),
            $current_site->id
        );
        if ( is_wp_error( $site_id ) )
            return new WP_Error(
                'cant-create',
                $site_id->get_error_message(), 
                array( 'status' => 500 )
            );
        else
			return $this->get_site_by_id( $site_id );
    }

    /**
	 * Updates an existing site.
     * @param site_id string The id of the site
	 * @param title string The title of the site
	 * @param site_name string The sitename used for the site, will become the path or the subdomain
	 */
	public function update_site( $id, $title, $site_name, $user_id ) {
        $update_me = $this->get_site_by_id( $id );
        if ( !is_wp_error( $update_me ) && $update_me->blog_id == $id && $id != 1) {
        // TODO: Check if user in site
            update_blog_option( $id, 'blogname', $title );
            update_blog_option( $id, 'home', $site_name );
            update_blog_option( $id, 'siteurl', $site_name );
            update_blog_details( $id, array( 'path' => $site_name ) );
            return $update_me;
        } else {
            return new WP_Error(
                'rest_blog_invalid_id', 
                __( 'Invalid blog ID.' ),
                array( 'status' => 404 )
            );
        }
    }

    /*
     * Wraps the wordpress delete blog function
     * @since '0.5.0'
     */
    public function delete_site($id, $drop = true) {
        $delete_me = $this->get_site_by_id( $id );
        if ( !is_wp_error( $delete_me ) && $delete_me->blog_id == $id ){
            wpmu_delete_blog($id, $drop);
            $delete_me->deleted = true;
            return $delete_me;
        } else {
            return new WP_Error(
                'rest_blog_invalid_id', 
                __( 'Invalid blog ID.' ),
                array( 'status' => 404 )
            );
        }
    }

    public function update_site_meta( $id, $params ) {
        if ( isset( $params['ruc'] ) )
            update_blog_option( $id, 'ruc', $params['ruc'] );
        if ( isset( $params['banner_id'] ) )
            update_blog_option( $id, 'banner_id', $params['banner_id'] );
    }

    /*
     * Checks whether sitename is a valid domain name or site name
     * Works on both domain and subdirectory
     * @since '0.0.1'
     */
    public function is_valid_sitename( $param, $request, $key ) {
        if ( is_subdomain_install() ){
            if ( preg_match( '/^[a-zA-Z0-9][a-zA-Z0-9-]+$/', $param ) )
                return true;
            else
                return false;
        } else {
            if ( preg_match( '/^[a-zA-Z0-9][a-zA-Z0-9_-]+$/', $param ) ) {
                $reserved = apply_filters( 
                    'subdirectory_reserved_names', 
                    array( 
                        'page',
                        'comments',
                        'blog',
                        'files',
                        'feed',
                        'tienda'
                    )
                );
                return !in_array( $param, $reserved );
            } else {
                return false;
            }
        }
    }

    /*
     * Validates that the site title is at least 2 alphanumerics and doesn't start with a space
     * @since '0.0.1'
     */
    public function is_valid_site_title( $param, $request, $key ) {
        // Make sure site title is not empty
        if ( preg_match( '/^[a-zA-Z0-9-_][a-zA-Z0-9-_ ]+/', $param ) )
            return true;
        else
            return false;
    }

    public function is_valid_id($param, $request, $key) {
        return is_numeric( $param );
    }

    public function user_id_exists( $user_id ) {
        global $wpdb;
        $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users WHERE ID = %d", $user_id ) );
        return empty( $count ) || 1 > $count ? false : true;
    }

    public function get_blog_users( $blog_id, $role='administrator' ) {
        $users = get_users( array( 
            'blog_id' => $blog_id,
            'role' => $role 
        ) );
        return $users;
    }
    
    /**
     * Get a collection of items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_items( $request ) {
        $params = $request->get_params();
        // Query by user
        $sites = get_blogs_of_user( $params['user_id'] );

        $data  = array();
        foreach ( $sites as $site ) {
            $itemdata = $this->prepare_item_for_response( $site, $params );
            $data[]   = $this->prepare_response_for_collection( $itemdata );
        }
        
        return new WP_REST_Response( $data, 200 );
    }
    
    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_item( $request ) {
        //get parameters from request
        $params = $request->get_params();
        $blog   = $this->get_site_by_id( $params['id'] );

        $data   = $this->prepare_item_for_response( $blog, $request );
        
        //return a response or error based on some conditional
        if ( $blog )
            return new WP_REST_Response($data, 200);
        else
            return new WP_Error(
                'rest_blog_invalid_id', 
                __( 'Invalid blog ID.' ),
                array( 'status' => 404 )
            );
    }
    
    /**
     * Create one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function create_item( $request ) {
        $params = $request->get_params(); // this include url and body params
        $item = $this->prepare_item_for_database( $params );
        if ( !is_wp_error($item) ) {
            $site = $this->create_site(
                $item['title'],
                $item['site_name'],
                $item['user_id']
            );
            $this->update_site_meta( $item['blog_id'], $params );

            if ( !is_wp_error( $site ) )
                return new WP_REST_Response( 
                    $this->prepare_item_for_response( $site,  $params ),
                    200
                );
            else
                return $site;
        } else
            return $item; // return the error
    }
    
    /**
     * Update one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function update_item( $request ) {
        $params = $request->get_params();
        $item = $this->prepare_item_for_database( $params );
        if ( !is_wp_error($item) ) {
            $data = $this->update_site(
                $item['blog_id'],
                $item['title'],
                $item['site_name'],
                $item['user_id']
            );
            
            if ( $data && !is_wp_error($data) ) {
                $this->update_site_meta( $item['blog_id'], $params );
                return new WP_REST_Response(
                    $this->prepare_item_for_response( $data, $params ), 200 );
            } else {
                return $data;
            }
        } else {
            return $item; // return the error
        }
    }
    
    /**
     * Delete one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function delete_item($request) {
        $deleted = $this->delete_site($request['id']);
        if ($deleted && !is_wp_error($deleted)) {
            return new WP_REST_Response($deleted, 200);
        } else {
            return $deleted;
        }
        
        return new WP_Error('cant-delete', __('an error ocurred', 'text-domain'), array(
            'status' => 500
        ));
    }
    
    /**
     * Prepare the item for create or update operation
     *
     * @param WP_REST_Request $request Request object
     * @return WP_Error|object $prepared_item
     */
    protected function prepare_item_for_database( $params ) {
        
        $blog_id    = $params['id'];
        $title      = $params['title'];
        $site_name  = $params['site_name'];
        $user_id    = $params['user_id'];

        // Next check if user exists
        if ( !$this->user_id_exists($user_id) ) {
            return new WP_Error(
                'user_id_invalid', 
                __("Invalid User Id: '" . $user_id . "'"), array(
                'status' => 400
            ));
        }

        return array(
            'blog_id'   => $blog_id,
            'title'     => $title,
            'site_name' => $site_name,
            'user_id'   => $user_id
        );
    }
    
    /**
     * Prepare the item for the REST response
     *
     * @param mixed $item WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     * @return mixed
     */
    public function prepare_item_for_response( $item, $request ) {
        // Here you can modify the item, before the response
        if ( $item ) {
            $item->ruc = get_blog_option( $item->id, 'ruc' );
            $item->banner_id = get_blog_option( $item->id, 'banner_id' );
        }
        return $item;
    }
    
    /**
     * Get the query params for collections
     *
     * @return array
     */
    public function get_collection_params() {
        $query_params = array();

        $query_params['context']['default'] = 'view';
        $query_params['user_id'] = array(
            'description' => 'User Id of site owner.',
            'type' => 'integer',
            'required' => true,
            'validate_callback' => array( $this, 'is_valid_id' )
        );
        return $query_params;
    }

    /**
     * Update and create params of collection
     *
     * @return array
     */
    public function get_creation_parameters() {
        $query_params = array();
        $query_params['user_id'] = array(
            'description' => 'User Id of site owner.',
            'type' => 'integer',
            'required' => true,
            'validate_callback' => array( $this, 'is_valid_id' )
        );
        $query_params['title'] = array(
            'description' => 'Title of the blog.',
            'required' => true,
            'type' => 'string',
            'validate_callback' => array( $this, 'is_valid_site_title' )
        );
        $query_params['site_name'] = array(
            'description' => 'Site name (path)',
            'required' => true,
            'type' => 'string',
            'validate_callback' => array( $this, 'is_valid_sitename' )
        );
        $query_params['ruc'] = array(
            'description' => 'Business RUC number',
            'type' => 'string'
        );
        $query_params['banner_id'] = array(
            'description' => 'Id of attachment for the banner',
            'type' => 'integer'
        );
        return $query_params;
    }
}