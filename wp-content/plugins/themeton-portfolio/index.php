<?php
/*
Plugin Name: Portfolio Post Type for ThemeTon themes
Plugin URI: http://www.themeton.com
Description: Portfolio Post Type for ThemeTon themes
Author: ThemeTon
Version: 1.0
Author URI: http://www.themeton.com
*/

class TT_Portfolio_PT{

    function __construct(){
        add_action('init', array($this, 'register_portfolio'));
        add_action('admin_init', array($this, 'settings_flush_rewrite'));

        add_filter('manage_edit-portfolio_columns', array($this, 'portfolio_edit_columns'));

        if( $this->admin_post_type()=="portfolio" ){
            add_action("manage_posts_custom_column", array($this, 'portfolio_custom_columns'));
        }
    }

    // register portfolio post type
    public function register_portfolio(){
        $label = esc_html(get_theme_mod('portfolio_label'));
        $label = !empty($label) ? $label : __('Portfolio', 'themeton');
        $labels = array(
            'name'          => $label,
            'singular_name' => $label,
            'edit_item'     => sprintf(__('Edit %s', 'themeton'), $label),
            'new_item'      => sprintf(__('New %s', 'themeton'), $label),
            'all_items'     => sprintf(__('All %s', 'themeton'), $label),
            'view_item'     => sprintf(__('View %s', 'themeton'), $label),
            'menu_name'     => sprintf(__('%s', 'themeton'), $label)
        );
        $slug = get_theme_mod('portfolio_slug');
        $slug = !empty($slug) ? $slug : __('portfolio-item', 'themeton');
        $args = array(
            'labels'            => $labels,
            'public'            => true,
            '_builtin'          => false,
            'capability_type'   => 'post',
            'hierarchical'      => false,
            'rewrite'           => array('slug' => $slug),
            'supports'          => array('title', 'editor', 'thumbnail', 'comments', 'excerpt', 'custom-fields')
        );

        register_post_type('portfolio', $args);

        $tax = array(
            "hierarchical"  => true,
            "label"         => __("Categories", 'themeton'),
            "singular_label"=> sprintf(__('%s Category', 'themeton'), $label),
            "rewrite"       => true
        );

        register_taxonomy('portfolio_entries', 'portfolio', $tax);
    }

    // re-flush rewrite
    public function settings_flush_rewrite(){
        flush_rewrite_rules();
    }


    // portfolio edit columns
    public function portfolio_edit_columns($columns) {
        $columns = array(
            "cb"        => "<input type=\"checkbox\" />",
            "thumbnail column-comments" => "Image",
            "title"     => __("Portfolio Title", 'themeton'),
            "category"  => __("Categories", 'themeton'),
            "date"      => __("Date", 'themeton'),
        );
        return $columns;
    }

    // portfolio custom columns
    public function portfolio_custom_columns($column) {
        global $post;
        switch ($column) {
            case "thumbnail column-comments":
                if (has_post_thumbnail($post->ID)) {
                    echo get_the_post_thumbnail($post->ID, array(45,45));
                }
                break;
            case "category":
                echo get_the_term_list($post->ID, 'portfolio_entries', '', ', ', '');
                break;
            case "team":
                echo get_the_term_list($post->ID, 'position', '', ', ', '');
                break;
            case "testimonial":
                echo get_the_term_list($post->ID, 'testimonials', '', ', ', '');
                break;
        }
    }


    // Get admin post type for current page
    public static function admin_post_type(){
        global $post, $typenow, $current_screen;

        // Check to see if a post object exists
        if ($post && $post->post_type)
            return $post->post_type;

        // Check if the current type is set
        elseif ($typenow)
            return $typenow;

        // Check to see if the current screen is set
        elseif ($current_screen && $current_screen->post_type)
            return $current_screen->post_type;

        // Finally make a last ditch effort to check the URL query for type
        elseif (isset($_REQUEST['post_type']))
            return sanitize_key($_REQUEST['post_type']);
     
        return '-1';
    }
}

new TT_Portfolio_PT();

?>