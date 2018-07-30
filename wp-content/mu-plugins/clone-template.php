<?php
/**
 *
 * @global type $wpdb
 * @param int $clone_from_blog_id the blog id which we are going to clone
 * @param int $clone_to_blog_id the blog id in which we are cloning
 */

// add_action( 'wpmu_new_blog', 'bpdev_clone_on_new_blog_registration',10, 6 );
function bpdev_clone_on_new_blog_registration( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
 
  $specified_blog_id = 2;//change it to the blog you want to clone
  bpdev_clone_blog( $specified_blog_id, $blog_id ); //clone the specified blog to the newly registered blog
 
}

function bpdev_clone_blog( $clone_from_blog_id, $clone_to_blog_id ){
 
    global $wpdb;
    
    //the table prefix for the blog we want to clone
    $old_table_prefix = $wpdb->get_blog_prefix( $clone_from_blog_id );
    
    //the table prefix for the target blog in which we are cloning
    $new_table_prefix = $wpdb->get_blog_prefix( $clone_to_blog_id );
    
    //which tables we want to clone
    //add or remove your table here
    $tables = array( 'posts', 'comments', 'options', 'postmeta',
    'terms', 'term_taxonomy', 'term_relationships', 'commentmeta' );
    
    //the options that we don't want to alter on the target blog
    //we will preserve the values for these in the options table of newly created blog
    $excluded_options = array(
      'siteurl',
      'blogname',
      'blogdescription',
      'home',
      'admin_email',
      'upload_path',
      'upload_url_path',
      // $new_table_prefix.'user_roles' //preserve the roles
      //add your on keys to preserve here
    );
    
    //should we? I don't see any reason to do it, just to avoid any glitch
    $excluded_options = esc_sql( $excluded_options );
    
    //we are going to use II Clause to fetch everything in single query. For this to work, we will need to quote the string
    //
    //not the best way to do it, will improve in future
    //I could not find an elegant way to quote string using sql, so here it is
    $excluded_option_list = "('" . join( "','", $excluded_options ) . "')";
    
    //the options table name for the new blog in which we are going to clone in next few seconds
    $new_blog_options_table = $new_table_prefix.'options';
    
    $excluded_options_query = "SELECT option_name, option_value FROM {$new_blog_options_table} WHERE option_name IN {$excluded_option_list}";
    
    //let us fetch the data
    
    $excluded_options_data = $wpdb->get_results( $excluded_options_query );
    
    //we have got the data which we need to update again later
    
    //now for each table, let us clone
    foreach( $tables as $table ){
    
    //drop table
    //clone table
    $query_drop = "DROP TABLE {$new_table_prefix}{$table}";
    
    $query_copy = "CREATE TABLE {$new_table_prefix}{$table} AS (SELECT * FROM {$old_table_prefix}{$table})" ;
    //drop table
    $wpdb->query( $query_drop );
    //clone table
    $wpdb->query( $query_copy );
    
    }
    
    //update the preserved options to the options table of the clonned blog
    foreach( (array) $excluded_options_data as $excluded_option ){
      update_blog_option( $clone_to_blog_id, $excluded_option->option_name, $excluded_option->option_value );
    }
 }

 ?>