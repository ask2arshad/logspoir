<?php
/**
 * Plugin Name: AJAX H2 Search Cron Job
 * Description: Adds a custom cron job to index H2 headings daily.
 * Version: 1.0
 * Author: Your Name
 * License: GPL2
 */
// Add a custom cron interval for daily cron job to run at midnight UTC
function custom_cron_intervals( $schedules ) {
    $schedules['midnight'] = array(
        'interval' => 86400, // 86400 seconds = 24 hours
        'display'  => 'Midnight UTC'
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'custom_cron_intervals' );

// Schedule the cron job to run at midnight UTC (tomorrow's midnight)
if ( ! wp_next_scheduled( 'ajax_h2_index_cron_job' ) ) {
    wp_schedule_event( strtotime('tomorrow midnight'), 'midnight', 'ajax_h2_index_cron_job' );
}

// Hook the custom cron job to your function
add_action( 'ajax_h2_index_cron_job', 'ajax_h2_index_headings' );

function ajax_h2_index_headings() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'headings_index';

    // Clear old data
    $wpdb->query( "TRUNCATE TABLE $table_name" );

    // Get all pages
    $pages = get_pages();
    foreach ( $pages as $page ) {
        $content = $page->post_content;
        preg_match_all( '/<h2[^>]*>(.*?)<\/h2>/i', $content, $matches );

        foreach ( $matches[1] as $heading ) {
            $anchor_id = sanitize_title( $heading );
            $wpdb->insert( $table_name, [
                'heading_text' => $heading,
                'page_url' => get_permalink( $page->ID ),
                'anchor_id'   => $anchor_id,
            ]);
        }
    }
}
