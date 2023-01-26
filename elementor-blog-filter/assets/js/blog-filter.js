/**
 * Blog Filter Script
 *
 * Handles blog filter form and ajax calls for the blog filter
 */

(function($)
{
    //Main Variables
    let page_pattern = "[^\\/]+(?=\\/$|\\/\\?+)";
    let old_category_value = 'all_cats';

    //DOM Elements
    /**
     * The form element of the blog post filter
     * @type {jQuery}
     */
    let elm_form = null;
    /**
     * The input selector for the category filter
     * @type {jQuery}
     */
    let elm_input_category = null;
    /**
     * The div container where the blog post list is output.
     * @type {jQuery}
     */
    let elm_post_container = null;
    /**
     * The loading box div that is displayed when the posts are being refreshed via AJAX
     * @type {jQuery}
     */
    let elm_loading_box = null;
    /**
     * The Clear button on the blog filter form
     * @type {jQuery}
     */
    let elm_button_clear = null;

    /**
     * The hidden page number field to handle page numbering
     * @type {jQuery}
     */
    let elm_field_page = null;

    //--------------------------------------------
    // Function & Class Definitions
    //--------------------------------------------
    //<editor-fold desc="Function & Class Definitions">

    /**
     * Initializes the blog filter form and assigns the necessary dom element variables.
     */
    function gdwl_init_form()
    {
        //Assign DOM Elements
        elm_form = $("#blog_filter_form");
        elm_input_category = $("#gdwl_field_category");
        elm_post_container = $("#gdwl_blog_posts");
        elm_loading_box = $("#gdwl_post_loading");
        elm_button_clear = $("#gdwl_filter_clear");
        elm_field_page = $("#gdwl_page_num");

        if (gdwl_validate_form())
        {

            gdwl_handle_pagination();

            old_category_value = elm_input_category.val();

            elm_input_category.change(function(){
                if (old_category_value !== elm_input_category.val())
                {
                    old_category_value = elm_input_category.val();
                    elm_field_page.val(1);
                }
                gdwl_category_filter_changed();
                //Run the AJAX method to reload blog posts with filters
                gdwl_get_blog_posts();


            });

            elm_button_clear.click(function()
            {
                //Clear the form fields
                gdwl_clear_form_fields();
                //Run the AJAX method to reload blog posts
                gdwl_get_blog_posts();
            });

            elm_form.submit(function(e)
            {
                e.preventDefault(); //Prevent conventional form submission
            });

            elm_loading_box.hide();
            elm_button_clear.hide();


        }
        else
        {
            console.log('Glidewell Blog Filter Error: Could not validate the blog filter form, some of the elements could not be found!');
        }
    }

    /**
     * Returns true if the blog filter form is valid and all required elements are set.
     * @returns {boolean}
     */
    function gdwl_validate_form()
    {
        return !elm_form.isNull() &&
            !elm_input_category.isNull() &&
            !elm_post_container.isNull() &&
            !elm_loading_box.isNull() &&
            !elm_button_clear.isNull() &&
            !elm_field_page.isNull();
    }

    function gdwl_handle_pagination()
    {
        elm_post_container.find('nav a.page-numbers').each(function(){
            let page_num = $(this).attr('href');
            page_num = page_num.match(page_pattern);
            $(this).attr('data-page-num', $(this).attr('href'));
            $(this).removeAttr('href');
            $(this).addClass('gdwl-page-link');
            $(this).click(function(){
                elm_field_page.val(page_num);
                gdwl_get_blog_posts();
            });
        });
    }

    /**
     * Resets the blog form filter input fields to their default values.
     */
    function gdwl_clear_form_fields()
    {
        elm_input_category.val('all_cats');
        elm_field_page.val(1);
        elm_button_clear.hide();
    }

    /**
     * Called when the user picks a selection from the Category input dropdown
     */
    function gdwl_category_filter_changed()
    {

        if (elm_input_category.val() !== 'all_cats')
        {
            elm_button_clear.show();
        }
        else
        {
            elm_button_clear.hide();
        }
    }

    /**
     * Assigns the HTML of the blog post container div with the given HTML content returned from the AJAX call.
     * @param {string} content HTML content of the blog post archive to output in the blog posts container
     */
    function gdwl_reload_posts(content)
    {
        if (gdwl_is_null(content))
        {
            return;
        }
        if (typeof content === 'string')
        {
            elm_post_container.html(content);
            gdwl_handle_pagination();
        }
        else
        {
            console.log('Glidewell Blog Filter Error: Tried to set post content with non-string parameter (type = ' + typeof content + ').');
        }
    }

    /**
     * Makes the AJAX call to the server to pull the blog posts given the current form data and assigns the returned
     * HTML to the blog post container div.
     */
    function gdwl_get_blog_posts()
    {
        elm_loading_box.show();
        $.ajax({
            method: 'GET',
            url: gdwlData.url_refresh,
            data: elm_form.serialize(),
            beforeSend: function(xhr){
                xhr.setRequestHeader('X-WP-Nonce', gdwlData.nonce);
            }
        }).then(function(r){
            gdwl_reload_posts(r);
            elm_loading_box.hide();
        });
    }

    /**
     * Returns true if a jQuery object is null/undefined/empty
     * @returns {boolean}
     */
    $.fn.isNull = function () {
        return this.length === 0;
    }

    /**
     * Returns true if the given jQuery selector element is null or empty.
     * @param {any} elm Element to check
     * @returns {boolean}
     */
    function gdwl_is_null(elm)
    {
        if (elm === undefined)
        {
            return true;
        }
        if (elm === null)
        {
            return true;
        }
        if (elm.length === undefined || elm.length === 0)
        {
            return true;
        }
        return false;
    }

    //</editor-fold> Function & Class Definitions

    //--------------------------------------------
    // Document Ready
    //--------------------------------------------
    //<editor-fold desc="Document Ready">
    $(function()
    {
        //Initializes the blog filter form
        gdwl_init_form();
    });
    //</editor-fold> Document Ready
}(jQuery));