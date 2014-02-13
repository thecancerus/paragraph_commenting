<?php

/**
 * @todo Ajax crawling support -- https://developers.google.com/webmasters/ajax-crawling/docs/getting-started
 * @todo https://developers.google.com/webmasters/ajax-crawling/
 */


/**
 * Perform the following actions/filters when plugins are loaded
 *
 * @since 0.1-alpha
 */
function inline_comments_loaded(){
    add_action( 'wp_ajax_inline_comments_add_comment', 'inline_comments_add_comment' );
    add_action( 'wp_ajax_nopriv_inline_comments_add_comment', 'inline_comments_add_comment' );
    add_action( 'wp_ajax_nopriv_inline_comments_load_template', 'inline_comments_load_template' );
    add_action( 'wp_ajax_inline_comments_load_template', 'inline_comments_load_template' );
    add_filter( 'template_redirect', 'inline_comments_template_redirect' );
}
add_action('plugins_loaded', 'inline_comments_loaded');


/**
 * Load our JavaScript and Stylesheet on single page only
 *
 * @since 0.1-alpha
 */
function inline_comments_template_redirect() {
    if ( is_singular() || is_page() ) {
        add_action( 'wp_enqueue_scripts', 'inline_comments_scripts');
        add_action( 'wp_head', 'inline_comments_head');
    }
}


/**
 * Load our JavaScript and Stylesheet, we include the login-register script only if it is installed.
 *
 * @uses wp_enqueue_script()
 * @uses wp_enqueue_style()
 *
 * @since 0.1-alpha
 */
function inline_comments_scripts(){
    wp_enqueue_script( 'inline-ajax-comments-script' );
    wp_enqueue_style( 'inline-ajax-comments-style' );
}


/**
 * Print our AJAX URL
 *
 * @since 0.1-alpha
 */
function inline_comments_head(){
    print '<script type="text/javascript"> var ajaxurl = "'. admin_url("admin-ajax.php") .'";</script>';
    print '<style type="text/css">'.get_option('additional_styling').'</style>';
}


/**
 * Inserts a comment for the current post if the user is logged in.
 *
 * @since 0.1-alpha
 * @uses check_ajax_referer()
 * @uses is_user_logged_in()
 * @uses wp_insert_comment()
 * @uses wp_get_current_user()
 * @uses current_time()
 * @uses wp_kses()
 * @uses get_option()
 */
function inline_comments_add_comment(){

    check_ajax_referer('inline_comments_nonce', 'security');

    $comment = trim(
            wp_kses( $_POST['comment'],
            array(
                'a' => array(
                    'href'  => array(),
                    'title' => array()
                ),
                'br'         => array(),
                'em'         => array(),
                'strong'     => array(),
                'blockquote' => array(),
                'code'       => array()
            )
        )
    );

    if ( empty( $comment ) ) die();

    if ( get_option('comment_registration') == 1 && ! is_user_logged_in() ) die();

    $data = array(
        'comment_post_ID' => (int)$_POST['post_id'],
        'comment_content' => $comment,
        'comment_type' => '',
        'comment_parent' => 0,
        'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
        'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
        'comment_date' => current_time('mysql'),
        'comment_approved' => 1
    );

    if ( is_user_logged_in() ){
        $current_user = wp_get_current_user();

        $author_email = $current_user->user_email;
        $author_url = $current_user->user_url;
        $author_name = $current_user->user_nicename;

        $data['user_id'] = $current_user->ID;
    } else {
        $author_email = empty( $_POST['user_email'] ) ? null : esc_attr( $_POST['user_email'] );
        $author_url = empty( $_POST['user_url'] ) ? null : esc_url( $_POST['user_url'], array('http','https') );
        $author_name = empty( $_POST['user_name'] ) ? null : esc_attr( $_POST['user_name'] );
    }

    $data['comment_author'] = $author_name;
    $data['comment_author_email'] = $author_email;
    $data['comment_author_url'] = $author_url;

	// ck - catch the new comment id for updating comment meta
    $comment_id = wp_insert_comment( $data );
    
    // ck - now add the para-id to the comment meta
    add_comment_meta( $comment_id, 'para_id' , $_POST['para_id'] ); 
    
    die();
}


/**
 * Load comments and comment form
 *
 * @since 0.1-alpha
 */
function inline_comments_load_template(){

    check_ajax_referer('inline_comments_nonce', 'security');

    $comments = get_comments( array(
        'post_id' => (int)$_POST['post_id'],
        'number'  => 100,
        'status'  => 'approve',
        'order'   => 'ASC'
    ) );

    ?>
    <div class="inline-comments-container" id="comments_target">
        <?php if ( $comments ) : foreach( $comments as $comment) : ?>
            <?php
            
            $para_id = get_comment_meta( $comment->comment_ID, 'para_id', true );
            
            $user = new WP_User( $comment->user_id );
            $class = null;
            if ( ! empty( $user->roles ) && is_array( $user->roles ) ) {
                foreach ( $user->roles as $role ){
                    $class = $role;
                }
            } else {
                $class = 'annon';
            }
            
            // ck -added data-comment-para-id to div
            ?>
            <div class="orphan-comment comment-para-id-<?php echo $para_id ?> inline-comments-content inline-comments-<?php echo $class; ?>" id="comment-<?php echo $comment->comment_ID; ?>">
                <div class="inline-comments-p">
                    <?php inline_comments_profile_pic( $comment->comment_author_email ); ?>
                    <?php print $comment->comment_content; ?><br />
                    <time class="meta">
                        <strong><?php $user = get_user_by('login', $comment->comment_author ); if ( ! empty( $user->user_url ) ) : ?><a href="<?php print $user->user_url; ?>" target="_blank"><?php print $comment->comment_author; ?></a><?php else : ?><?php print $comment->comment_author; ?><?php endif; ?></strong>
                        <a href="<?php echo get_permalink( $comment->comment_post_ID); ?>#<?php echo $comment->comment_ID; ?>" class="inline-comments-time-handle" data-comment_id="<?php echo $comment->comment_ID; ?>"><?php print human_time_diff( strtotime( $comment->comment_date ), current_time('timestamp') ); ?> ago.</a>
                    </time>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
    <?php die();
}


/**
 * Determine the profile pic for a user, either the FB pic or
 * the gravatar pic. If no ID is passed uses the current logged
 * in user.
 *
 * @uses get_user_meta()
 * @uses get_avatar();
 */
function inline_comments_profile_pic( $id_or_email=null, $email=null ){

    if ( is_null( $id_or_email ) ) {
        global $current_user;
        get_currentuserinfo();
        $id_or_email = $current_user->ID;
    }

    $html = get_avatar( $id_or_email, 32 );

    print '<span class="inline-comments-profile-pic-container">' . $html . '</span>';
}


function inline_comments_tempalte( $file ){
    return plugin_dir_path( dirname( __FILE__ ) ) . 'templates/comments.php';
}
add_filter('comments_template', 'inline_comments_tempalte');