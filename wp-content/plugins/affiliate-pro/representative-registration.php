<?php

/**
 * Plugin Name: Representative Registration
 * Description: A plugin for registering representatives and managing their coupons.
 * Version: 1.0
 * Author: Your Name
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Include required files
require_once plugin_dir_path(__FILE__) . 'includes/roles.php';
require_once plugin_dir_path(__FILE__) . 'includes/coupon-functions.php';

add_action('wp_enqueue_scripts', 'representative_enqueue_styles');
function representative_enqueue_styles()
{
    wp_enqueue_style('representative-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
}

// Shortcode function for the representative registration form
function representative_registration_form()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['representative_registration_nonce']) && wp_verify_nonce($_POST['representative_registration_nonce'], 'representative_registration')) {
        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);

        // Check if username or email already exists
        if (username_exists($username) || email_exists($email)) {
            return '<p class="error">Username or email already exists.</p>';
        }

        // Create the user
        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            return '<p class="error">Error creating user: ' . $user_id->get_error_message() . '</p>';
        }

        // Assign the "Representative" role
        $user = get_user_by('ID', $user_id);
        $user->set_role('representative');

        // Generate unique coupon
        $coupon_code = 'REP-' . strtoupper(wp_generate_password(8, false));
        create_representative_coupon($coupon_code, $user_id);

        return '<p class="success">Registration successful! Your coupon code is: ' . esc_html($coupon_code) . '</p>';
    }

    // Registration form HTML
    ob_start();
?>
    <div class="form-wrapper">
        <div class="form-card">
            <h1>Formulaire d'inscription du représentant</h1>
            <form method="POST" class="representative-registration-form">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>

                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>

                <?php wp_nonce_field('representative_registration', 'representative_registration_nonce'); ?>
                <button type="submit">Register</button>
            </form>
        </div>
    </div>
<?php
    return ob_get_clean();
}

add_shortcode('representative_registration_form', 'representative_registration_form');


// Add admin menu
add_action('admin_menu', 'rc_add_representative_menu');

function rc_add_representative_menu()
{
    add_menu_page(
        'Affiliate Management',
        'Affiliate Management',
        'manage_options',
        'affiliate-management',
        '', // No main page
        'dashicons-groups',
        6
    );

    add_submenu_page(
        'affiliate-management',
        'Representatives List',
        'Representatives List',
        'manage_options',
        'representatives-list',
        'rc_display_representatives_list'
    );

    add_submenu_page(
        'affiliate-management',
        'Representative Earnings',
        'Earnings',
        'manage_options',
        'representative-earnings',
        'rc_display_representative_earnings'
    );
}


function rc_display_representative_earnings()
{
    // Check for permissions
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap"><h1>Representative Earnings</h1>';

    // Fetch all representatives
    $representatives = get_users(['role' => 'representative']);

    // echo '<pre>';
    // print_r($representatives);
    // echo '</pre>'; die();

    if (empty($representatives)) {
        echo '<p>No representatives found.</p>';
        return;
    }

    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead><tr>';
    echo '<th>ID</th><th>Username</th><th>Email</th><th>Total Earnings</th><th>Sales Count</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($representatives as $representative) {
        $user_meta = get_userdata($representative->ID);
        $earnings = get_user_meta($representative->ID, '_representative_earnings', true);
        $earnings = $earnings ? wc_price($earnings) : wc_price(0);

         echo '<pre>';
    print_r($earnings   );
    echo '</pre>';

        // Count the completed orders linked to the representative
        $orders = get_posts([
            'post_type'   => 'shop_order',
            'post_status' => 'wc-completed',
            'meta_key'    => '_representative_id',
            'meta_value'  => $representative->ID,
        ]);
        $sales_count = count($orders);

        echo '<tr>';
        echo '<td>' . esc_html($representative->ID) . '</td>';
        echo '<td>' . esc_html($representative->user_login) . '</td>';
        echo '<td>' . esc_html($representative->user_email) . '</td>';
        echo '<td>' . esc_html(strip_tags($earnings)) . '</td>';

        echo '<td>' . esc_html($sales_count) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

// Callback to display representatives      
function rc_display_representatives_list()
{
    // Fetch all users with the "representative" role
    $representatives = get_users(['role' => 'representative']);

    echo '<div class="wrap"><h1>Representatives List</h1>';

    if (empty($representatives)) {
        echo '<p>No representatives found.</p>';
        return;
    }

    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead><tr>';
    echo '<th>ID</th><th>Username</th><th>Email</th><th>Coupon Code</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($representatives as $representative) {
        // Display representative data
        $user_meta = get_userdata($representative->ID);
        $coupon_code = get_user_meta($representative->ID, 'representative_coupon_code', true);

        echo '<tr>';
        echo '<td>' . esc_html($representative->ID) . '</td>';
        echo '<td>' . esc_html($representative->user_login) . '</td>';
        echo '<td>' . esc_html($representative->user_email) . '</td>';
        echo '<td>' . esc_html($coupon_code ? $coupon_code : 'Not Assigned') . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

// Add shortcode for registration form
add_shortcode('representative_registration_form', 'representative_registration_form');



// Your custom plugin file
function rc_update_representative_earnings($order_id) {
    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    // Fetch the applied coupons for the order
    $coupons = $order->get_coupon_codes();

    // Log the coupons for debugging
    error_log('Order ID: ' . $order_id);
    error_log('Coupons Used: ' . print_r($coupons, true));

    foreach ($coupons as $coupon_code) {
        // Get the representative linked to this coupon code
        $args = [
            'meta_key'   => 'representative_coupon_code', // The meta key for the coupon code
            'meta_value' => $coupon_code, // The coupon code used
            'number'     => 1, // Limit results to 1 user
            'fields'     => ['ID'], // Only fetch the ID
        ];
        
        $users = get_users($args);

        // Check if any user is found
        if (!empty($users)) {
            $representative_id = $users[0]->ID;

            // Calculate order earnings for this representative
            $order_total = $order->get_total();
            $commission_rate = 0.1; // Example: 10% commission
            $commission = $order_total * $commission_rate;

            // Ensure the current earnings are treated as a float
            $current_earnings = get_user_meta($representative_id, '_representative_earnings', true);
            $current_earnings = $current_earnings ? (float) $current_earnings : 0.0; // Cast to float if not empty, else 0.0
            $new_earnings = $current_earnings + $commission;

            // Update representative earnings
            update_user_meta($representative_id, '_representative_earnings', $new_earnings);

            // Increment sales count
            $current_sales_count = get_user_meta($representative_id, '_representative_sales_count', true);
            $current_sales_count = $current_sales_count ? (int) $current_sales_count : 0; // Cast to int if not empty, else 0
            $new_sales_count = $current_sales_count + 1;
            update_user_meta($representative_id, '_representative_sales_count', $new_sales_count);

            // Log earnings and sales count for debugging
            error_log('New Earnings for Representative ID ' . $representative_id . ': ' . $new_earnings);
            error_log('New Sales Count for Representative ID ' . $representative_id . ': ' . $new_sales_count);
        } else {
            error_log('No representative found for coupon: ' . $coupon_code);
        }
    }
}
add_action('woocommerce_order_status_completed', 'rc_update_representative_earnings');









