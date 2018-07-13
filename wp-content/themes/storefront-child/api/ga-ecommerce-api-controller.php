<?php
 
class GaEcommerceAPIController extends WP_REST_Controller {

  /**
   * Constructor.
   *
   * @access public
   *
   */
  public function __construct() {
    require_once get_stylesheet_directory() . '/inc/GoogleAnalyticsAPI.class.php';
    $this->ga = new GoogleAnalyticsAPI( 'service' );
  }
 
  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    $version = '1';
    $namespace = 'ga/v' . $version;
    $base = 'product';
    register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', array(
      array(
        'methods'     => WP_REST_Server::READABLE,
        'callback'    => array( $this, 'get_product_analytics' ),
        // 'permission_callback' => array( $this, 'get_item_permissions_check' ),
        'args'        => $this->get_collection_params()
      ),
    ) );
  }

  private function get_ga_auth() {
    $auth = get_transient( 'ga_auth' );
    if( ! $auth ) {
      $this->ga->auth->setClientId('0e2150fdee9f4dbd52e95bd31d452841df8b4598'); // From the APIs console
      $this->ga->auth->setEmail('google-anlytics-api@heyshopper-210022.iam.gserviceaccount.com'); // From the APIs console
      $this->ga->auth->setPrivateKey(get_stylesheet_directory() . '/heyshopper-210022-0e2150fdee9f.p12'); // Path to the .p12 file
      $auth = $this->ga->auth->getAccessToken();
      if ( $auth['http_code'] == 200 ) {
        set_transient( 'ga_auth', $auth, 3600 );
      }
    }
    return $auth;
  }

  public function get_product_analytics( $request ) {

    $auth = $this->get_ga_auth();
    // Try to get the AccessToken
    if ( $auth['http_code'] == 200 ) {
      $accessToken  = $auth['access_token'];
      
      $tokenExpires = $auth['expires_in'];
      $tokenCreated = time();
      
      $this->ga->setAccessToken($accessToken);
      $this->ga->setAccountId('ga:178457267');

      $include_empty = 'true';

      // Query by period argument, default 1W
      switch ( $request['period'] ) {

        case '1D':
          $start_date     = 'yesterday';
          $date_dimension = 'ga:dateHour';
          break;

        case '1M':
          $start_date     = date('Y-m-d', strtotime('-1 month'));
          $date_dimension = 'ga:date';
          break;

        case '1Y':
          $start_date     = date('Y-m-d', strtotime('-1 year'));
          $date_dimension = 'ga:month';
          break;

        case 'ALL':
          $start_date     = '2005-01-01'; // based on documentation, this is the earlist date
          $date_dimension = 'ga:date';
          $include_empty  = 'false';
          break;

        default:
          $start_date     = date('Y-m-d', strtotime('-1 week'));
          $date_dimension = 'ga:date';
          break;
      }

      // Set the default params. For example the start/end dates and max-results
      $defaults = array(
        'start-date'    => $start_date,
        'end-date'      => 'today'
      );

      $this->ga->setDefaultQueryParams($defaults);

      // Example1: Get visits by date
      $params = array(
        'metrics'     => 'ga:itemQuantity,ga:localItemRevenue',
        'dimensions'  => $date_dimension,
        'filters'     => 'ga:productSku==' . $request['id'],
        'sort'        => $date_dimension,
        'include-empty-rows' => $include_empty
      );
      
      $sales = $this->ga->query($params);

      $result = array(
        'dates'       => array(),
        'quantities'  => array(),
        'revenues'    => array()
      );

      if ( $sales['http_code'] == 200 && isset( $sales['rows'] ) ) {
        foreach ($sales['rows'] as $row) {
          array_push( $result['dates'], $row[0] );
          array_push( $result['quantities'], (int) $row[1] );
          array_push( $result['revenues'], (float) $row[2] );
        }
        
        // Getting totals
        $result['total_quantity'] = (int) $sales['totalsForAllResults']['ga:itemQuantity'];
        $result['total_revenues'] = (float) $sales['totalsForAllResults']['ga:localItemRevenue'];
      }
      
      $result['period'] = $request['period'];

      return new WP_REST_Response( $result, 200 );;
    } else {
      return new WP_Error( 'code', __( 'GA authentification error. code:' . $auth['http_code'], 'storefront' ) );
    }
  }
 
  /**
   * Check if a given request has access to get items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check( $request ) {
    //return true; <--use to make readable by all
    return current_user_can( 'edit_something' );
  }
 
  /**
   * Get the query params for collections
   *
   * @return array
   */
  public function get_collection_params() {
    $query_params = array();

    $query_params['context']['default'] = 'view';
    $query_params['period'] = array(
      'description'       => 'Period of time to get request.',
      'type'              => 'string',
      'default'           => '1W',
      'enum'              => array( '1D', '1W', '1M', '1Y', 'ALL' )
    );
    return $query_params;
  }
}