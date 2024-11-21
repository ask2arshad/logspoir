<?php
// Add a new endpoint for "Earnings"
add_action('init', 'add_representative_earnings_endpoint');
function add_representative_earnings_endpoint() {
    add_rewrite_endpoint('earnings', EP_ROOT | EP_PAGES);
}

// Add the "Earnings" tab to the My Account menu
add_filter('woocommerce_account_menu_items', 'add_earnings_to_menu');
function add_earnings_to_menu($items) {
    // Only add the tab for users with the "representative" role
    if (current_user_can('representative')) {
        $items['earnings'] = 'Earnings';
    }
    return $items;
}

// Display content for the "Earnings" tab
add_action('woocommerce_account_earnings_endpoint', 'display_representative_earnings');
function display_representative_earnings() {
    // Get the current user ID
    $user_id = get_current_user_id();

    // Retrieve earnings data
    $earnings = get_user_meta($user_id, '_representative_earnings', true);
    $earnings = $earnings ? wc_price($earnings) : wc_price(0);

    // Display the earnings
    echo '<h3>Your Total Earnings</h3>';
    echo '<p>Total Earnings: <strong>' . $earnings . '</strong></p>';

    // Optional: List completed orders linked to the representative
    $orders = get_posts([
        'post_type'   => 'shop_order',
        'post_status' => 'wc-completed',
        'meta_key'    => '_representative_id',
        'meta_value'  => $user_id,
    ]);

    if (!empty($orders)) {
        echo '<h4>Your Sales:</h4>';
        echo '<ul>';
        foreach ($orders as $order_post) {
            $order = wc_get_order($order_post->ID);
            echo '<li>Order #' . esc_html($order->get_id()) . ' - ' . esc_html($order->get_formatted_order_total()) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No sales found yet.</p>';
    }
}
