<?php
namespace Glidewell\Elementor\Shortcodes;
if(!defined('ABSPATH')) {exit;}

use Glidewell\StringUtils as Strings;
use Glidewell\Debug;
use Glidewell\Parsing;
use Glidewell\Elementor;

//--------------------------------------------
// Shortcodes
//--------------------------------------------
//<editor-fold desc="Shortcodes">
/**
 * Shortcode to output the Blog Post filter along with the Elementor Pro blog post widget using the Widget ID set in the
 * Glidewell Plugin settings.
 *
 * @param array $atts Shortcode attributes with data for the shortcode
 *
 * @return false|string
 */
function elementor_post_filter($atts = array())
{
    //Commenting this out for now, until we have actual arguments to use
    $atts = array_change_key_case((array) $atts, CASE_LOWER);
    $defaults = array(
        'widget_id' => '',
    );
    $args = shortcode_atts($defaults, $atts);

    //Debug::log(__METHOD__ . '() - Widget ID = ' . Elementor\blog_filter()->blog_posts_widget_id);
    $widget_id = (Strings\is_null_or_empty($args['widget_id']) ? Elementor\blog_filter()->blog_posts_widget_id : Parsing\sanitize_int($args['widget_id']));
    //Debug::log(__METHOD__ . '() - ACTUAL Widget ID = ' . $widget_id);
    gdwl()->load_script('gdwl-blog-filter-script', 'modules/elementor-blog-filter/assets/js/blog-filter.js', array('jquery'), true);

    wp_localize_script('gdwl-blog-filter-script', 'gdwlData', array(
        'nonce'             => wp_create_nonce('wp_rest'),
        'url_refresh'           => esc_url_raw(rest_url(\Glidewell\Rest\REST_NAMESPACE . '/blog/refresh')),
    ));
    $categories = get_categories();
    ob_start();
    ?>
    <form id="blog_filter_form" class="gdwl-filter-form" method="POST" >
        <div class="gdwl-filter-hidden" style="display: none;">
            <input type="hidden" id="gdwl_page_num" name="gdwl_page_num" value="1" />
        </div>
        <div class="gdwl-filter-cont">
            <div class="gdwl-filter-input">
                <i class="fas fa-sort-amount-down"></i>
                <select id="gdwl_field_category" name="gdwl_field_category" value="all_cats">
                    <option value="all_cats">All Categories</option>
                    <?php foreach ($categories as $cat) : ?>
                        <option value="<?php echo $cat->slug; ?>"><?php echo $cat->name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="gdwl-filter-buttons">
                <div id="gdwl_filter_clear" class="gdwl-button gdwl-form-clear">Clear</div>
            </div>
        </div>
    </form>
    <div class="gdwl-blog-post-cont">
        <div id="gdwl_blog_posts" class="gdwl-blog-post-list">
            <?php
            echo Elementor\get_blog_posts_html($widget_id);
            ?>
        </div>
        <div id="gdwl_post_loading" class="gdwl-loading-box">
            <div class="gdwl-loading-spinner">X</div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('gdwl_blog_filter', 'Glidewell\\Elementor\\Shortcodes\\elementor_post_filter');


function render_template()
{
    if (class_exists('\ElementorPro\Plugin'))
    {
        echo \ElementorPro\Plugin::elementor()->frontend->get_builder_content_for_display(Elementor\blog_filter()->blog_posts_widget_id);
    }
}
add_shortcode('gdwl_render_template', 'Glidewell\\Elementor\\Shortcodes\\render_template');
//</editor-fold> Shortcodes
