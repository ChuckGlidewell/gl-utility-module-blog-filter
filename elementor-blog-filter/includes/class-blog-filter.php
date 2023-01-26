<?php
namespace Glidewell\Elementor;
if(!defined('ABSPATH')) {exit;}

use Glidewell\StringUtils as Strings;
use Glidewell\Debug;
use Glidewell\Parsing;
use \WP_Query;


class Blog_Filter
{
    //--------------------------------------------
    // Fields
    //--------------------------------------------
    //<editor-fold desc="Fields">
    /** @var \WP_Query */
    public $blog_query = null;

    public $blog_page_limit = 5;

    /** @var \ElementorPro\Modules\Posts\Widgets\Posts */
    public $blog_post_widget = null;

    /** @var int Post ID of the Elementor Template containing the Elementor Pro Posts widget */
    public $blog_posts_widget_id = 0;
    //</editor-fold> Fields

    //--------------------------------------------
    // Initialization
    //--------------------------------------------
    //<editor-fold desc="Initialization">
    /**
     * Main plugin class constructor.
     */
    public function __construct()
    {
        if (!class_exists('\ElementorPro\Plugin'))
        {
            Debug::log_error(__METHOD__ . '() - The Elementor Pro Blog Filter module is being loaded, but Elementor Pro is not installed!!!');
        }

        add_action('gdwl_settings_loaded', array($this, 'load_settings'));
    }
    //</editor-fold> Initialization

    //--------------------------------------------
    // Methods
    //--------------------------------------------
    //<editor-fold desc="Methods">

    /**
     * Loads the settings from the plugin
     * @return void
     */
    public function load_settings() {
        $this->blog_posts_widget_id = $this->get_blog_post_widget_id();
    }

    /**
     * Returns the Post ID of the Elementor Template containing the Blog Posts Widget.
     * @return int
     */
    public function get_blog_post_widget_id() : int
    {
        return Parsing\sanitize_int(gdwl()->settings()->get_value_int(GDWL_OPTION_BLOG_WIDGET_ID, 0));
    }

    /**
     * Sets the Post ID of the Elementor Template containing the Blog Posts Widget for use with outputting blog posts.
     *
     * @param int $id
     */
    public function set_blog_post_widget_id(int $id)
    {
        gdwl()->update_option(GDWL_OPTION_BLOG_WIDGET_ID, $id);
    }
    //</editor-fold> Methods


    //----------------------------------------------------------
    // Singleton Methods
    //----------------------------------------------------------
    // <editor-fold desc="Singleton Methods">

    /** @var Blog_Filter */
    private static $_instance = null;


    /**
     * The main instance of the plugin class
     * @return Blog_Filter
     */
    public static function instance()
    {
        if (is_null(self::$_instance))
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     */
    public function __clone()
    {
        _doing_it_wrong(__METHOD__, 'Cloning is forbidden.', '1.0');
    }

    /**
     * Deserializing instances of this class is forbidden.
     */
    public function __wakeup()
    {
        _doing_it_wrong(__METHOD__, 'Deserializing instances of this class is forbidden.', '1.0');
    }
    // </editor-fold> Singleton Methods


}

/**
 *
 * @return Blog_Filter
 */
function blog_filter() : Blog_Filter
{
    return Blog_Filter::instance();
}

blog_filter();