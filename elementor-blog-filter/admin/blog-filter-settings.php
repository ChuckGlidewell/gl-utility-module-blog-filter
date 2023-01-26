<?php
namespace Glidewell\Elementor\Admin;
if (!defined('ABSPATH')) {exit;}

use \Gdwl_Admin_Tab;
use \Gdwl_Setting_Numeric_Int;

/**
 * Filter for adding the blog filter settings to the admin dashboard
 * @param $settings
 * @return array|mixed
 */
function populate_settings($settings)
{
    if (is_array($settings))
    {
        $settings[] = new Gdwl_Setting_Numeric_Int('Blog Post Template ID', \Glidewell\Elementor\GDWL_OPTION_BLOG_WIDGET_ID, 'gdwl_set_widget_id', 0, 'The Elementor Template ID of the template used to display the blog posts archive.', 'Blog');
    }

    return $settings;
}
add_filter('gdwl_init_settings', 'Glidewell\\Elementor\\Admin\\populate_settings', 10, 1);

/**
 * Filter to handle adding the Blog tab for the settings page.
 * @param $tabs
 * @return array|mixed
 */
function populate_tabs($tabs)
{
    if (is_array($tabs))
    {
        if (!isset($tabs['blog']))
        {
            $tabs['blog'] = new Gdwl_Admin_Tab('Blog', 'gdwl-tab-blog', 'Glidewell\\Elementor\\Admin\\display_blog_tab');
        }
    }

    return $tabs;
}
add_filter('gdwl_admin_page_settings_tabs', 'Glidewell\\Elementor\\Admin\\populate_tabs', 10, 1);

/**
 * Displays the "Blog" tab on the settings page.
 */
function display_blog_tab()
{
    ?>
    <h2>Blog Settings</h2>
<?php
    gdwl()->settings()->output_controls('Blog');
}