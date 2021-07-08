<?php

if ( ! defined('ABSPATH')) {
    die('Silence is golden');
}

if ( ! defined('MATERIALIS_GUTENBERG_INTEGRATION_PATH')) {
    define('MATERIALIS_GUTENBERG_INTEGRATION_PATH', dirname(__FILE__) . '/');
}

if ( ! defined('MATERIALIS_GUTENBERG_INTEGRATION_URL')) {
    define('MATERIALIS_GUTENBERG_INTEGRATION_URL', plugin_dir_url(__FILE__));
}

function materialis_gutenberg() {
    
    materialis_do_enqueue_google_fonts();

    wp_register_script(
        'materialis-gutenberg',
        MATERIALIS_GUTENBERG_INTEGRATION_URL . 'assets/materialis.js',
        array( 'wp-blocks', 'wp-element' )
    );

    wp_enqueue_style(
        'materialis-gutenberg',
        get_template_directory_uri() . '/gutenberg-style.min.css',
        array( 'wp-edit-blocks' ),
        materialis_get_version()
    );

    wp_enqueue_style(
        'materialis-companion',
        MATERIALIS_GUTENBERG_INTEGRATION_URL . '../../assets/css/companion.bundle.min.css',
        array( 'wp-edit-blocks' ),
        materialis_get_version()
    );   
    
    wp_enqueue_style(
        'materialis-fontawesome',
        get_template_directory_uri() . '/assets/css/material-icons.min.css',
        array( 'wp-edit-blocks' ),
        materialis_get_version()
    );

    if ( function_exists( 'register_block_type' ) ) {
        register_block_type( 'extend/materialis-gutenberg', array(
            'editor_script' => 'materialis-gutenberg',
        ) );
    }
}

add_action( 'admin_init', 'materialis_gutenberg' );

/*
Fix to keep gutenberg block html comments
*/
function materialis_gutenberg_keep_comment_before($text)
{
    //from <!-- wp:namespace/block {"option1":1,"option2":"2"}  --> to [wp:namespace/block {"option1":1,"option2":"2"}] 
    $gutenbergCommentRegex = '#<!--\s+(\/?)wp:([\w\/]+) (.*?)-->#';
    
    //use callback to escape html entities so that wordpress does not strip block options
    $text = preg_replace_callback(
        $gutenbergCommentRegex,
        function($matches) {

            return '@@' . $matches[1] . 'wp:' . $matches[2] . ' ' . htmlentities($matches[3]) . '@@';
        },
        $text);

    return $text;
}

function materialis_gutenberg_keep_comment_after($text)
{
    //from [wp:namespace/block {"option1":1,"option2":"2"}]  to <!-- wp:namespace/block {"option1":1,"option2":"2"}  -->
    $gutenbergCommentRegex = '#@@(\/?)wp:([\w\/]+)\s+(.*?)( \/ )?@@#';

    $text = preg_replace_callback(
        $gutenbergCommentRegex,
        function($matches) {

            $comment = '<!-- ' . $matches[1] . 'wp:' . $matches[2] . ($matches[3]?' ':'') . trim(html_entity_decode($matches[3])) . ' -->';
            if ( is_customize_preview() ) {
                return $comment;
            }

            $return = '';
            //if not theme block and ordinary gutenberg section then wrap in gridContainer
            if (strpos($matches[2], 'extendstudio') === false) 
            {

            	//single comment close tag
	            if (trim($matches[3]) == '/') {
	            	$return .= '<!-- ' . str_replace('@@', '', $matches[0]) . '-->';
		            //$return = '<div class="gridContainer"> ' . $comment . '</div>';
	            } else //comment close tag
	            if ($matches[1]) {
                    $return .= '</div>' . $comment;
                } else {//comment start tag
                    $return .= $comment .
                        '<div class="gridContainer">';
                }
            } else $return = '';
            
            return $return;
        },
        $text);

    return $text;
}

add_filter( 'the_content', 'materialis_gutenberg_keep_comment_before', 5);
add_filter( 'the_content', 'materialis_gutenberg_keep_comment_after', is_customize_preview() ? 20 : 6);
