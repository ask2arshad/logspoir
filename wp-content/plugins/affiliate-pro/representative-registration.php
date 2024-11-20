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
function representative_enqueue_styles() {
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


