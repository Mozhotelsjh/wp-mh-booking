<?php
/*
Plugin Name: Post Types for Themeton theme
Plugin URI: http://www.themeton.com
Description: Post Types for ThemeTon Let's travel theme
Author: ThemeTon
Version: 1.0
Author URI: http://www.themeton.com
*/



/*
  ==========================================================================
  LetsTravel Post Types
  ==========================================================================
*/
class TT_Travel_PT{

    private $post_types = array(
                'country' => 'Country',
                'tour' => 'Tour',
                'room' => 'Hotel Room',
                'flight' => 'Flight',
                'car' => 'Car',
                'cruise' => 'Cruise',
            );

    function __construct(){
        add_action('init', array($this, 'register_pt'));
        add_action('admin_init', array($this, 'settings_flush_rewrite'));
        foreach ($this->post_types as $slug => $label ) {
            add_filter('manage_edit-'.$slug.'_columns', array($this, $slug.'_edit_columns'));

            if( $this->admin_post_type() == $slug ){
                add_action("manage_posts_custom_column", array($this, $slug.'_custom_columns'));
            }
        }

    }

    // register post type
    public function register_pt(){

        foreach ($this->post_types as $slug => $label) {

            $labels = array(
                'name'          => $label,
                'singular_name' => $label,
                'edit_item'     => sprintf(__('Edit %s', 'themeton'), $label),
                'new_item'      => sprintf(__('New %s', 'themeton'), $label),
                'all_items'     => sprintf(__('All %s', 'themeton'), $label),
                'view_item'     => sprintf(__('View %s', 'themeton'), $label),
                'menu_name'     => sprintf(__('%s', 'themeton'), $label)
            );
            if($slug == 'country') {
                $args = array(
                    'labels'            => $labels,
                    'public'            => true,
                    '_builtin'          => false,
                    'capability_type'   => 'post',
                    'hierarchical'      => true,
                    'rewrite'           => array('slug' => $slug),
                    'supports'          => array('page-attributes', 'title', 'thumbnail', 'editor')
                );
            } else {
                $args = array(
                    'labels'            => $labels,
                    'public'            => true,
                    '_builtin'          => false,
                    'capability_type'   => 'post',
                    'hierarchical'      => false,
                    'rewrite'           => array('slug' => $slug),
                    'supports'          => array('title', 'editor', 'thumbnail', 'comments', 'excerpt', 'custom-fields')
                );
            }

            register_post_type($slug, $args);


            if( $slug!='country' ){
                $tax = array(
                    "hierarchical"  => true,
                    "label"         => __("Categories", 'themeton'),
                    "labels"        => array(
                            "menu_name" => $label . __(" Categories", 'themeton'),
                            "name" => $label . __(" Categories", 'themeton'),
                            "all_items" => $label . __("    Categories", 'themeton'),
                        ),
                    "singular_label"=> sprintf(__('%s Category', 'themeton'), $label),
                    "rewrite"       => true
                );

                register_taxonomy($slug.'_category', $slug, $tax);
            }
            
        }
    }

    // re-flush rewrite
    public function settings_flush_rewrite(){
        flush_rewrite_rules();
    }


    // edit country columns
    public function country_edit_columns($columns) {
        $columns = array(
            "cb"        => "<input type=\"checkbox\" />",
            "thumbnail column-comments" => "Image",
            "title"     => __("Country", 'themeton'),
            "category"  => __("Categories", 'themeton'),
            "date"      => __("Date", 'themeton'),
        );
        return $columns;
    }

    // custom country columns
    public function country_custom_columns($column) {
        global $post;
        switch ($column) {
            case "thumbnail column-comments":
                if (has_post_thumbnail($post->ID)) {
                    echo get_the_post_thumbnail($post->ID, array(45,45));
                }
                break;
            case "category":
                echo get_the_term_list($post->ID, 'country_category', '', ', ', '');
                break;
        }
    }


    // edit tour columns
    public function tour_edit_columns($columns) {
        $columns = array(
            "cb"        => "<input type=\"checkbox\" />",
            "thumbnail column-comments" => "Image",
            "title"     => __("Tour", 'themeton'),
            "category"  => __("Categories", 'themeton'),
            "date"      => __("Date", 'themeton'),
        );
        return $columns;
    }

    // custom tour columns
    public function tour_custom_columns($column) {
        global $post;
        switch ($column) {
            case "thumbnail column-comments":
                if (has_post_thumbnail($post->ID)) {
                    echo get_the_post_thumbnail($post->ID, array(45,45));
                }
                break;
            case "category":
                echo get_the_term_list($post->ID, 'tour_category', '', ', ', '');
                break;
        }
    }


    // edit room columns
    public function room_edit_columns($columns) {
        $columns = array(
            "cb"        => "<input type=\"checkbox\" />",
            "thumbnail column-comments" => "Image",
            "title"     => __("Room", 'themeton'),
            "category"  => __("Categories", 'themeton'),
            "date"      => __("Date", 'themeton'),
        );
        return $columns;
    }

    // custom room columns
    public function room_custom_columns($column) {
        global $post;
        switch ($column) {
            case "thumbnail column-comments":
                if (has_post_thumbnail($post->ID)) {
                    echo get_the_post_thumbnail($post->ID, array(45,45));
                }
                break;
            case "category":
                echo get_the_term_list($post->ID, 'room_category', '', ', ', '');
                break;
        }
    }


    // edit flight columns
    public function flight_edit_columns($columns) {
        $columns = array(
            "cb"        => "<input type=\"checkbox\" />",
            "thumbnail column-comments" => "Image",
            "title"     => __("Flight", 'themeton'),
            "category"  => __("Categories", 'themeton'),
            "date"      => __("Date", 'themeton'),
        );
        return $columns;
    }

    // custom flight columns
    public function flight_custom_columns($column) {
        global $post;
        switch ($column) {
            case "thumbnail column-comments":
                if (has_post_thumbnail($post->ID)) {
                    echo get_the_post_thumbnail($post->ID, array(45,45));
                }
                break;
            case "category":
                echo get_the_term_list($post->ID, 'flight_category', '', ', ', '');
                break;
        }
    }


    // edit car columns
    public function car_edit_columns($columns) {
        $columns = array(
            "cb"        => "<input type=\"checkbox\" />",
            "thumbnail column-comments" => "Image",
            "title"     => __("Car", 'themeton'),
            "category"  => __("Categories", 'themeton'),
            "date"      => __("Date", 'themeton'),
        );
        return $columns;
    }

    // custom car columns
    public function car_custom_columns($column) {
        global $post;
        switch ($column) {
            case "thumbnail column-comments":
                if (has_post_thumbnail($post->ID)) {
                    echo get_the_post_thumbnail($post->ID, array(45,45));
                }
                break;
            case "category":
                echo get_the_term_list($post->ID, 'car_category', '', ', ', '');
                break;
        }
    }


    // edit cruise columns
    public function cruise_edit_columns($columns) {
        $columns = array(
            "cb"        => "<input type=\"checkbox\" />",
            "thumbnail column-comments" => "Image",
            "title"     => __("Cruise", 'themeton'),
            "category"  => __("Categories", 'themeton'),
            "date"      => __("Date", 'themeton'),
        );
        return $columns;
    }

    // custom cruise columns
    public function cruise_custom_columns($column) {
        global $post;
        switch ($column) {
            case "thumbnail column-comments":
                if (has_post_thumbnail($post->ID)) {
                    echo get_the_post_thumbnail($post->ID, array(45,45));
                }
                break;
            case "category":
                echo get_the_term_list($post->ID, 'cruise_category', '', ', ', '');
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

new TT_Travel_PT();



?>