<?php

/**
 * Plugin Name: Ajax H2 Search
 * Description: Search H2 headings across all pages with Ajax functionality.
 * Version: 1.0
 * Author: Trifecta Medias
 */

if (! defined('ABSPATH')) exit; // Exit if accessed directly

function ajax_h2_search_styles()
{
    wp_enqueue_style('ajax-search-styles', plugins_url('/css/ajax-search.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'ajax_h2_search_styles');



// Add ID to H2 headings dynamically
function ajax_h2_add_anchors($content)
{
    return preg_replace_callback(
        '/<h2>(.*?)<\/h2>/i',
        function ($matches) {
            $id = sanitize_title($matches[1]);
            return '<h2 id="' . esc_attr($id) . '">' . $matches[1] . '</h2>';
        },
        $content
    );
}
add_filter('the_content', 'ajax_h2_add_anchors');

// Handle Ajax Search Request
function ajax_h2_search() {
    global $wpdb;
    $search_query = sanitize_text_field($_POST['query']);
    $table_name = $wpdb->prefix . 'headings_index';

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT DISTINCT 
                REGEXP_REPLACE(heading_text, '<[^>]*>', '') AS clean_heading_text, 
                page_url, 
                anchor_id 
             FROM $table_name 
             WHERE heading_text LIKE %s 
             LIMIT 7",
            '%' . $wpdb->esc_like($search_query) . '%'
        )
    );

    if ($results) {
        foreach ($results as $result) {
            // Add the sanitized anchor ID logic here
            $sanitized_anchor_id = !empty($result->anchor_id) 
                ? sanitize_title($result->anchor_id) 
                : sanitize_title($result->clean_heading_text);

            // Output the result with the sanitized anchor ID
            echo '<li><a href="' . esc_url($result->page_url) . '#' . esc_attr($sanitized_anchor_id) . '">' . esc_html($result->clean_heading_text) . '</a></li>';
        }
    } else {
        echo '<li>No results found.</li>';
    }
    wp_die();
}

add_action('wp_ajax_h2_search', 'ajax_h2_search');
add_action('wp_ajax_nopriv_h2_search', 'ajax_h2_search');

// Enqueue Scripts
function ajax_h2_enqueue_scripts()
{
    wp_enqueue_script('ajax-search', plugins_url('/js/ajax-search.js', __FILE__), ['jquery'], null, true);
    wp_localize_script('ajax-search', 'ajax_search_params', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'ajax_h2_enqueue_scripts');


// Add the Search Form Shortcode
function ajax_h2_search_form()
{
    ob_start();
?>
    <div class="ajax-search-wrapper">
        <div class="ajax-search-container">
            <input type="text" id="ajax-h2-search" class="ajax-search-box" placeholder="Search ....">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
            <i class="fas fa-search"></i>


        </div>
        <ul id="ajax-h2-results" class="ajax-search-results"></ul>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('ajax_h2_search', 'ajax_h2_search_form');
