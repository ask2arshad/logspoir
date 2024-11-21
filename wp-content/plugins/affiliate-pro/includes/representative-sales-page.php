<?php
function rc_representative_sales_page() {
    // Check if the user has the required capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Fetch all representatives
    $representatives = get_users(['role' => 'representative']);

    echo '<div class="wrap">';
    echo '<h1>Representative Sales</h1>';

    if (empty($representatives)) {
        echo '<p>No representatives found.</p>';
        return;
    }

    // Display table
    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead><tr>';
    echo '<th>ID</th><th>Username</th><th>Email</th><th>Total Earnings</th><th>Sales Count</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($representatives as $representative) {
        $user_id = $representative->ID;

        // Fetch representative earnings and sales
        $earnings = get_user_meta($user_id, '_representative_earnings', true);
        $earnings = $earnings ? wc_price($earnings) : '0.00';

        // Count the number of orders linked to this representative
        $sales_count = count(get_posts([
            'post_type'   => 'shop_order',
            'post_status' => 'wc-completed',
            'meta_key'    => '_representative_id',
            'meta_value'  => $user_id,
        ]));

        echo '<tr>';
        echo '<td>' . esc_html($user_id) . '</td>';
        echo '<td>' . esc_html($representative->user_login) . '</td>';
        echo '<td>' . esc_html($representative->user_email) . '</td>';
        echo '<td>' . $earnings . '</td>';
        echo '<td>' . esc_html($sales_count) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
