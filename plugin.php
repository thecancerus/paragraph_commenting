<?php

/**
 * Plugin Name: Paragraph Commenting
 * Plugin URI: http://premium.wpmudev.org/blog/how-to-have-comments-just-like-medium
 * Description: Comment on individual paragraphs in WordPress content
 * Tags: comments, ajax, security, ajax comments, comment, inline, comment form, medium, paragraph comment, annotations
 * Author: Chris Knowles
 * Author URI: http://premium.wpmudev.org
 * Version: 1.0
 * Original Plugin Name: Inline Ajax Comments
 * Original Plugin URI: http://zanematthew.com/blog/plugins/inline-comments/
 * Original Author: ZaneMatthew
 * Original Author URI: http://zanematthew.com/
 * License: GPL
 */


/**
 * From the WordPress plugin headers above we derive the version number, and plugin name
 */
$plugin_headers = get_file_data( __FILE__, array( 'Version' => 'Version', 'Name' => 'Plugin Name' ) );


/**
 * We store our plugin data in the following global array.
 * $my_unique_name with your unique name
 */
global $my_unique_name;
$my_unique_name = array();
$my_unique_name['version_key'] = strtolower( str_replace( ' ', '_', $plugin_headers['Name'] ) ) . '_version';
$my_unique_name['version_value'] = $plugin_headers['Version'];


/**
 * When the user activates the plugin we add the version number to the
 * options table as "my_plugin_name_version" only if this is a newer version.
 */
function inline_comments_acitvation(){

    global $my_unique_name;

    if ( get_option( $my_unique_name['version_key'] ) && get_option( $my_unique_name['version_key'] ) > $my_unique_name['version_value'] )
        return;

    update_option( $my_unique_name['version_key'], $my_unique_name['version_value'] );

}
register_activation_hook( __FILE__, 'inline_comments_acitvation' );


/**
 * Delete our version number from the database when the plugin is activated.
 */
function inline_comments_deactivate(){
    global $my_unique_name;
    delete_option( $my_unique_name['version_key'] );
}
register_deactivation_hook( __FILE__, 'inline_comments_deactivate' );


if ( is_admin() )
    require_once plugin_dir_path( __FILE__ ) . 'admin/admin-tags.php';

/**
 * Theme only functions
 */
require_once plugin_dir_path( __FILE__ ) . 'inc/template-tags.php';


function inline_comments_enqueue_scripts(){

    $plugin_headers = get_file_data( __FILE__, array( 'Version' => 'Version', 'Name' => 'Original Plugin Name' ) );
    $clean_name = strtolower( str_replace( ' ', '-', $plugin_headers['Name'] ) );

    wp_register_style( $clean_name . '-style', plugin_dir_url( __FILE__ ) . 'inc/css/style.css' );
    wp_register_script( 'textarea_auto_expand-script', plugin_dir_url( __FILE__ ) . 'vendor/textarea-auto-expand/jquery.textarea_auto_expand.js' );
    wp_register_script( $clean_name . '-script', plugin_dir_url( __FILE__ ) . 'inc/js/script.js', array('jquery', 'textarea_auto_expand-script') );
}

add_action('wp_enqueue_scripts', 'inline_comments_enqueue_scripts', 2);
