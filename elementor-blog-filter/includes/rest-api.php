<?php
namespace Glidewell\Elementor\Rest;
if(!defined('ABSPATH')) {exit;}

use Glidewell\Debug;
use Glidewell\Rest;
use Glidewell\Elementor;

/**
 * Initializes rest api endpoints
 * @return void
 */
function init_rest_api()
{
    //$namespace = 'grdc/v1';
    register_rest_route(Rest\REST_NAMESPACE, '/blog/refresh',
        array(
            'methods'   => 'GET',
            'callback'  => 'Glidewell\\Elementor\\Rest\\rest_reload_post_widget',
            'permission_callback' => '__return_true'
        ));

}
add_action('rest_api_init', 'Glidewell\\Elementor\\Rest\\init_rest_api', 10, 0);

/**
 * Refreshes the Elementor Posts widget for displaying the blog post archive. This is called whenever the category
 * dropdown is changed or a page number is clicked on the pagination section.
 * @param \WP_REST_Request $request
 * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function rest_reload_post_widget(\WP_REST_Request $request)
{
    $html = '';

    //Debug::log(__METHOD__ . '() - Request Data: ' . "\n" . gdwl()->dump_var($request));

    if (class_exists('\ElementorPro\Plugin'))
    {
        $html = Elementor\get_blog_posts_html(Elementor\blog_filter()->blog_posts_widget_id);
    }

    return rest_ensure_response($html);
}