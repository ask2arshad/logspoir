<?php
/**
 * Plugin Name: WooCommerce Payment Confirmations
 * Description: Allows representatives to confirm payments, stores confirmations, and notifies the admin.
 * Version: 1.0
 * Author: Your Name
 * License: GPL2
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Define plugin constants
define( 'WPCP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Hook to create custom database table for payment confirmations
function wpcp_create_payment_confirmation_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'payment_confirmations';

    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // Table doesn't exist, create it
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            order_id INT(11) NOT NULL,
            payment_amount DECIMAL(10, 2) NOT NULL,
            payment_date DATE NOT NULL,
            payment_image_url VARCHAR(255) NOT NULL,
            additional_notes TEXT,
            user_id INT(11) NOT NULL,
            status VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
}

// Create table on plugin activation
register_activation_hook( __FILE__, 'wpcp_create_payment_confirmation_table' );

// Handle the form submission
function wpcp_handle_payment_confirmation_submission() {
    if ( isset($_POST['submit_payment_confirmation']) ) {
        // Sanitize and validate form inputs
        $order_id = sanitize_text_field($_POST['order_id']);
        $payment_amount = sanitize_text_field($_POST['payment_amount']);
        $payment_date = sanitize_text_field($_POST['payment_date']);
        $additional_notes = sanitize_textarea_field($_POST['additional_notes']);
        
        // Handle the file upload
        if ( isset($_FILES['payment_image']) && $_FILES['payment_image']['error'] == 0 ) {
            $uploaded_file = wp_handle_upload($_FILES['payment_image'], ['test_form' => false]);
            $payment_image_url = $uploaded_file['url'];
        }

        // Verify if the order exists in WooCommerce
        $order = wc_get_order($order_id);
        if ($order) {
            // Store the payment confirmation as order note
            $order->add_order_note( "Payment confirmed. Amount: $payment_amount, Date: $payment_date. Receipt: $payment_image_url. Notes: $additional_notes" );

            // Optionally, store it in custom database table
            global $wpdb;
            $table_name = $wpdb->prefix . 'payment_confirmations';
            $wpdb->insert($table_name, [
                'order_id' => $order_id,
                'payment_amount' => $payment_amount,
                'payment_date' => $payment_date,
                'payment_image_url' => $payment_image_url,
                'additional_notes' => $additional_notes,
                'user_id' => get_current_user_id(),
                'status' => 'pending', // status could be pending, confirmed, etc.
                'created_at' => current_time('mysql')
            ]);

            // Notify admin via email
            $admin_email = get_option('admin_email');
            $subject = 'New Payment Confirmation Submitted';
            $message = "A new payment confirmation has been submitted for Order ID: $order_id. Please review the payment details.";
            wp_mail($admin_email, $subject, $message);

            // Display success message
            echo 'Payment confirmation has been submitted successfully.';
        } else {
            echo 'Invalid Order ID.';
        }
    }
}

// Hook to handle form submission
add_action('init', 'wpcp_handle_payment_confirmation_submission');

// Display the payment confirmation form using shortcode
function wpcp_payment_confirmation_form() {
    if ( is_user_logged_in() ) {
        ob_start();
        ?>
        <form id="payment-confirmation-form" method="POST" enctype="multipart/form-data">
            <label for="order_id">Order ID:</label>
            <input type="text" name="order_id" required>

            <label for="payment_amount">Payment Amount:</label>
            <input type="number" name="payment_amount" required>

            <label for="payment_date">Payment Date:</label>
            <input type="date" name="payment_date" required>

            <label for="payment_image">Upload Payment Image (Receipt):</label>
            <input type="file" name="payment_image" required>

            <label for="additional_notes">Additional Notes:</label>
            <textarea name="additional_notes"></textarea>

            <input type="submit" name="submit_payment_confirmation" value="Submit Payment Confirmation">
        </form>
        <?php
        return ob_get_clean();
    } else {
        return 'You must be logged in to submit a payment confirmation.';
    }
}

// Register the shortcode to display the form on a page
add_shortcode('wpcp_payment_confirmation_form', 'wpcp_payment_confirmation_form');

// Display payment confirmation details on WooCommerce order page
function wpcp_display_payment_confirmation_details_on_order_page($order_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'payment_confirmations';
    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE order_id = $order_id");

    if ($results) {
        echo '<h2>Payment Confirmation Details</h2>';
        echo '<ul>';
        foreach ($results as $result) {
            echo '<li>Amount: ' . $result->payment_amount . '</li>';
            echo '<li>Date: ' . $result->payment_date . '</li>';
            echo '<li>Receipt: <a href="' . $result->payment_image_url . '" target="_blank">View Receipt</a></li>';
            echo '<li>Notes: ' . $result->additional_notes . '</li>';
            echo '<li>Status: ' . $result->status . '</li>';
        }
        echo '</ul>';
    }
}
add_action('woocommerce_order_details_after_order_table', 'wpcp_display_payment_confirmation_details_on_order_page');
?>
