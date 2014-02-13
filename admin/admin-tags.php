<?php

function inline_comments_admin_menu(){
    add_submenu_page(
                'options-general.php',
                'Settings',
                'Inline Comments',
                'manage_options',
                'inline-comments-settings',
                'inline_comments_menu_fn'
            );
}
add_action('admin_menu','inline_comments_admin_menu');


function inline_comments_admin_init(){
    $fields = array(
            'additional_styling',
            'keep_open'
            );

    foreach( $fields as $field ) {
        register_setting('inline_comments_settings', $field );
    }
}
add_action('admin_init', 'inline_comments_admin_init');

function inline_comments_menu_fn(){
    load_template( plugin_dir_path( __FILE__ ) . 'templates/settings.php' );
}