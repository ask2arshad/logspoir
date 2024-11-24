<?php
/*
Plugin Name: Search Highlighter
Plugin URI: https://yourwebsite.com/
Description: A plugin to search for a string on the page, highlight it, and scroll to the first occurrence.
Version: 1.0
Author: Your Name
Author URI: https://yourwebsite.com/
*/

// Hook to add the script and style
function search_highlighter_enqueue_scripts() {
    wp_enqueue_script('search-highlighter-js', plugin_dir_url(__FILE__) . 'search-highlighter.js', array('jquery'), null, true);
    wp_enqueue_style('search-highlighter-css', plugin_dir_url(__FILE__) . 'search-highlighter.css');
}
add_action('wp_enqueue_scripts', 'search_highlighter_enqueue_scripts');

// Shortcode for displaying search form and content
function search_highlighter_shortcode() {
    ob_start();
    ?>
    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Search for text">
        <button onclick="searchAndHighlight()">Search</button>
    </div>
    <div class="search-content">
        <?php the_content(); ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('search_highlighter', 'search_highlighter_shortcode');
