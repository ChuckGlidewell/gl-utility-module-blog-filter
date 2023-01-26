<?php
namespace Glidewell\Elementor;
if(!defined('ABSPATH')) {exit;}

//Usings
use Glidewell\StringUtils as Strings;
use Glidewell\Debug;
use Glidewell\Parsing;

//--------------------------------------------
// Constants
//--------------------------------------------
//<editor-fold desc="Constants">
const GDWL_OPTION_BLOG_WIDGET_ID = 'blog_widget_id';
//</editor-fold> Constants

//--------------------------------------------
// Includes
//--------------------------------------------
//<editor-fold desc="Includes">
include_once GDWL_PLUGIN_DIR . '/modules/elementor-blog-filter/admin/blog-filter-settings.php'; //Admin settings for the filter
include_once GDWL_PLUGIN_DIR . '/modules/elementor-blog-filter/includes/class-blog-filter.php'; //Blog Filter class
include_once GDWL_PLUGIN_DIR . '/modules/elementor-blog-filter/includes/blog-filter-shortcodes.php'; //Shortcodes for displaying the filter
include_once GDWL_PLUGIN_DIR . '/modules/elementor-blog-filter/includes/rest-api.php'; //Rest API Endpoints
//</editor-fold> Includes

//----------------------------------------------------------
// Action / Filter Hooks
//----------------------------------------------------------
// <editor-fold desc="Action / Filter Hooks">

/**
 * Filters the Elementor Post query for blog posts using the POST information from the blog filter form on the blog
 * posts archive page.
 * @param \WP_Query $query
 */
function blog_query_filter($query)
{
    //Set custom query var so we can grab the returned results query later to output pagination.
    $query->set('gdwl_blog_query', true);

    if (!isset($_POST) && !isset($_REQUEST))
    {
        return; //No need to do anything here if we don't have form data coming in.
    }

    //Setup data array to hold post/request fields
    $data = array();

    //Pull filter form data from either the POST data or the REQUEST data
    if (isset($_POST) && !empty($_POST))
    {
        $data = $_POST;
    }
    else if (isset($_REQUEST) && !empty($_REQUEST))
    {
        $data = $_REQUEST;
    }

    if (isset($data['gdwl_field_category']))
    {
        //Set the category filter
        $cat = Strings\strip_whitespace(sanitize_text_field($data['gdwl_field_category']));
        if ($cat !== 'all_cats')
        {
            $query->set('category_name', $cat);
        }
    }

    if (isset($data['gdwl_page_num']))
    {
        $query->query['paged'] = Parsing\sanitize_int($data['gdwl_page_num']);
        $query->set('paged', Parsing\sanitize_int($data['gdwl_page_num']));
        $query->set('page', Parsing\sanitize_int($data['gdwl_page_num']));
        //Debug::log(__METHOD__ . '() - Page Num is ' . Parsing\sanitize_int($data['gdwl_page_num']));
    }
    else
    {
        //Debug::log(__METHOD__ . '() - No page num');
    }
}
add_action('elementor/query/gdwl_blog_posts', 'Glidewell\\Elementor\\blog_query_filter');

/**
 * Sets up the query data for the blog post archive query to be used for the blog filter later on.
 * @param \WP_Query $query
 * @param \Elementor\Widget_Base $widget
 *
 * @return void
 */
function get_blog_posts_query($query, $widget)
{
    if ($widget instanceof \ElementorPro\Modules\Posts\Widgets\Posts)
    {
        if ($query->get('gdwl_blog_query'))
        {
            //Debug::log(__METHOD__ . '() - IT WORKED! Query Data: ' . "\n" . gdwl()->dump_var($query));
            blog_filter()->blog_query       = $query;
            blog_filter()->blog_post_widget = $widget;
            $page_limit              = $widget->get_settings('pagination_page_limit');
            if (!empty($page_limit))
            {
                blog_filter()->blog_page_limit = Parsing\sanitize_int($page_limit);
            }
        }
    }
}
add_action('elementor/query/query_results', 'Glidewell\\Elementor\\get_blog_posts_query', 10, 2);

// </editor-fold> Action / Filter Hooks

//----------------------------------------------------------
// General Methods
//----------------------------------------------------------
// <editor-fold desc="General Methods">

/**
 * Returns the ID of the Elementor global widget used for displaying the blog posts archive.
 * @return int
 */
function get_blog_widget_id() : int
{
    global $blog_widget_id;

    if (!isset($blog_widget_id))
    {
        return 0;
    }

    if (is_null($blog_widget_id))
    {
        $blog_widget_id = gdwl()->settings()->get_value_int(GDWL_OPTION_BLOG_WIDGET_ID, 0);
    }

    return (is_numeric($blog_widget_id) ? $blog_widget_id : 0);
}

/**
 * Returns the formatted HTML for the RDC blog post list which includes the filter form.
 *
 * @global \WP_Query $wp_query WordPress Query object.
 * @param string|int|null $widget_id Post ID of the global widget template containing the elementor blog posts widget.
 *
 * @return string
 */
function get_blog_posts_html($widget_id = '') : string
{
    global $wp_query;

    if (!is_numeric($widget_id))
    {
        if (Strings\is_null_or_empty($widget_id))
        {
            $widget_id = blog_filter()->blog_posts_widget_id;
        }
        else
        {
            $widget_id = Parsing\sanitize_int($widget_id);
        }
    }

    if ($widget_id <= 0)
    {
        $widget_id = blog_filter()->blog_posts_widget_id;
    }

    if (class_exists('\ElementorPro\Plugin'))
    {

        //Get main posts HTML from widget
        $html = \ElementorPro\Plugin::elementor()->frontend->get_builder_content_for_display($widget_id);
        //Handle Pagination
        //Debug::log(__METHOD__ . '() - The blog query is NOT NULL!' . "\n" . gdwl()->dump_var(blog_filter()->blog_post_widget->get_query()));
        //return $html;

        if (empty(blog_filter()->blog_post_widget))
        {
            return $html;
        }

        //Debug::log(__METHOD__ . '() - Paged = [' . get_query_var('paged') . '] Posts Per Page = [' . get_query_var('posts_per_page') . '] Page = [' . get_query_var( 'page' ) . ']');
        //Debug::log(__METHOD__ . '() - Current Page = [' . $current_page . ']');
        //Debug::log(__METHOD__ . '() - Query Dump: ' . "\n" . gdwl()->dump_var($wp_query));
        if (!empty(blog_filter()->blog_query))
        {
            if ( blog_filter()->blog_query->max_num_pages < 2)
            {
                return $html;
            }

            //Debug::log(__METHOD__ . '() - The blog query is NOT NULL!' . "\n" . gdwl()->dump_var(blog_filter()->blog_post_widget->get_query()));
            $current_page = max(1, blog_filter()->blog_query->get('paged'), blog_filter()->blog_query->get('page'));
            $max_pages = blog_filter()->blog_query->max_num_pages;

            $pagination_args = array(
                'type' => 'array',
                'current' => $current_page,
                'total' => $max_pages,
                'prev_next' => true,
                'show_all' => false,
                'before_page_number' => '<span class="elementor-screen-only">' . __( 'Page', 'elementor-pro' ) . '</span>',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
            );


            $links = paginate_links($pagination_args);
            ob_start();
            ?>
            <nav class="elementor-pagination" role="navigation" aria-label="<?php esc_attr_e( 'Pagination', 'elementor-pro' ); ?>">
                <?php echo implode( PHP_EOL, $links ); ?>
            </nav>
            <?php
            $html .= ob_get_clean();
        }

        return $html;
    }
    else
    {
        Debug::log_error(__METHOD__ . '() - Tried to load the widget ID [' . $widget_id . '] but the Elementor Pro plugin is not active/installed!');
        return '<b>Nothing to see here.</b>';
    }
}
// </editor-fold> General Methods
