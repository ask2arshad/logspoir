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
    // Add the main "Affiliate Management" menu
    add_menu_page(
        'Affiliate Management',
        'Affiliate Management',
        'manage_options',
        'affiliate-management',
        '', // No main page content
        'dashicons-groups',
        6
    );

    // Add the "Representatives List" submenu
    add_submenu_page(
        'affiliate-management',
        'Representatives List',
        'Representatives List',
        'manage_options',
        'representatives-list',
        'rc_display_representatives_list'
    );

    // Add the "Representative Earnings" submenu
    add_submenu_page(
        'affiliate-management',
        'Representative Earnings',
        'Earnings',
        'manage_options',
        'representative-earnings',
        'rc_display_representative_earnings'
    );

    // Add the new submenu for "Representative Payment Request"
    add_submenu_page(
        'affiliate-management',  // This submenu is under the "Affiliate Management" menu
        'Representative Payment Request',
        'Representative Payment Request List',
        'manage_options',
        'representative-payment-request',
        'rc_representative_payment_request'
    );
}


function rc_representative_payment_request()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'representative_payment_requests';

    // Fetch all payment requests from the database
    $payment_requests = $wpdb->get_results("SELECT * FROM $table_name");

?>
    <div class="wrap">
        <h2>All Payment Requests</h2>

        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th scope="col" class="manage-column">User Name</th>
                    <th scope="col" class="manage-column">User Email</th>
                    <th scope="col" class="manage-column">Amount</th>
                    <th scope="col" class="manage-column">Reason</th>
                    <th scope="col" class="manage-column">Payment Proof</th>
                    <th scope="col" class="manage-column">Status</th>
                    <th scope="col" class="manage-column">Date Submitted</th>
                    <th scope="col" class="manage-column">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($payment_requests) {
                    foreach ($payment_requests as $request) {
                ?>
                        <tr>
                            <td><?php echo esc_html($request->user_id); ?></td>
                            <td><?php echo esc_html($request->email); ?></td>
                            <td><?php echo esc_html($request->amount); ?></td>
                            <td><?php echo esc_html($request->reason); ?></td>
                            <td><?php echo '<a href="' . esc_url($request->invoice_url) . '" target="_blank">View Invoice</a>'; ?></td>
                            <td><a href="#" class="view-proof" data-proof-url="<?php echo esc_url($request->payment_proof_url); ?>">View Proof</a></td>
                            <td><?php echo esc_html($request->status); ?></td>
                            <td><?php echo esc_html($request->date_submitted); ?></td>
                            <td>
                                <!-- Example Action: You can add buttons for Approve/Reject -->
                                <a href="?page=rc-payment-requests&action=approve&request_id=<?php echo esc_attr($request->id); ?>" class="button">Approve</a>
                                <a href="?page=rc-payment-requests&action=reject&request_id=<?php echo esc_attr($request->id); ?>" class="button">Reject</a>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="8">No payment requests found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Viewing Payment Proof -->
    <div id="proofModal" style="display:none;">
        <div class="modal-content">
            <span id="closeModal" style="cursor:pointer;">&times;</span>
            <h3>Payment Proof</h3>
            <img id="proofImage" src="" alt="Payment Proof" style="width:100%; max-width: 600px;" />
        </div>
    </div>

    <style>
        /* Modal styles */
        #proofModal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            max-width: 700px;
            border-radius: 10px;
        }

        #closeModal {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 25px;
            color: #aaa;
        }

        #closeModal:hover {
            color: #000;
        }
    </style>

    <script>
        // JavaScript for modal functionality
        document.querySelectorAll('.view-proof').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var proofUrl = link.getAttribute('data-proof-url');
                document.getElementById('proofImage').src = proofUrl;
                document.getElementById('proofModal').style.display = 'block';
            });
        });

        // Close modal when clicking the close button
        document.getElementById('closeModal').addEventListener('click', function() {
            document.getElementById('proofModal').style.display = 'none';
        });

        // Close modal when clicking outside of the modal content
        window.onclick = function(event) {
            if (event.target == document.getElementById('proofModal')) {
                document.getElementById('proofModal').style.display = 'none';
            }
        }
    </script>
<?php
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
        // Fetch earnings
        $earnings = get_user_meta($representative->ID, '_representative_earnings', true);
        $earnings = $earnings ? wc_price($earnings) : wc_price(0);
        // echo "r ID";
        // echo $representative->ID;
        // echo "<br>";
        // Query completed orders linked to the representative
        $orders = get_posts([
            'post_type'   => 'shop_order',
            'post_status' => 'wc-completed',
            'meta_key'    => '_representative_id',
            'meta_value'  => $representative->ID,
            'numberposts' => -1,  // Retrieve all relevant orders
        ]);
        // print_r($orders);
        $sales_count = count($orders);
        // print_r($sales_count);

        // Output representative data
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


function testCallback()
{
    return 'callback';
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
function rc_update_representative_earnings($order_id)
{
    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    // Get coupons used in the order
    $coupons = $order->get_coupon_codes();

    foreach ($coupons as $coupon_code) {
        // Fetch representative linked to this coupon
        $representative = get_users([
            'meta_key'   => 'representative_coupon_code',
            'meta_value' => $coupon_code,
            'number'     => 1,
            'fields'     => ['ID'],
        ]);

        if (!empty($representative)) {
            $representative_id = $representative[0]->ID;

            // Save the representative ID in the order meta
            update_post_meta($order_id, '_representative_id', $representative_id);

            // Calculate earnings (commission example: 10% of the order total)
            $order_total = $order->get_total();
            $commission = $order_total * 0.10;

            // Update representative earnings
            $current_earnings = get_user_meta($representative_id, '_representative_earnings', true);
            $new_earnings = $current_earnings ? $current_earnings + $commission : $commission;
            update_user_meta($representative_id, '_representative_earnings', $new_earnings);
        }
    }
}
add_action('woocommerce_order_status_completed', 'rc_update_representative_earnings');



add_action('woocommerce_checkout_create_order', 'assign_representative_to_order', 10, 2);

function assign_representative_to_order($order, $data)
{
    // Get the applied coupons in the order
    $coupons = $order->get_used_coupons();

    // Loop through each coupon to find the representative
    foreach ($coupons as $coupon_code) {
        // Find the representative user by coupon code (assuming you store the coupon code in user meta)
        $representative_user = get_user_by('login', $coupon_code);

        if ($representative_user) {
            // Get the representative ID
            $representative_id = $representative_user->ID;

            // Update the order meta with the representative ID
            update_post_meta($order->get_id(), '_representative_id', $representative_id);
            error_log('Representative ID: ' . $representative_id);
        }
    }
}




//Representative Payment Request Form

// Add this code to your theme's functions.php or a custom plugin

// Create the form shortcode
function rc_representative_payment_request_form()
{
    ob_start();
?>
    <div class="form-wrapper">
        <div class="form-card">
            <form method="post" action="">
                <h2>Representative Payment Request</h2>

                <label for="representative_name">Name:</label>
                <input type="text" id="representative_name" name="representative_name" required>

                <label for="representative_email">Email:</label>
                <input type="email" id="representative_email" name="representative_email" required>

                <label for="payment_amount">Payment Amount:</label>
                <input type="number" id="payment_amount" name="payment_amount" required>

                <label for="payment_reason">Reason for Payment:</label>
                <textarea id="payment_reason" name="payment_reason" required></textarea>

                <input type="submit" name="submit_payment_request" value="Submit Payment Request">
            </form>

        </div>
    </div>

    <?php

    // Handle the form submission
    if (isset($_POST['submit_payment_request'])) {
        $name = sanitize_text_field($_POST['representative_name']);
        $email = sanitize_email($_POST['representative_email']);
        $amount = floatval($_POST['payment_amount']);
        $reason = sanitize_textarea_field($_POST['payment_reason']);

        // Store the data in the WordPress database (or any other method you'd prefer)
        global $wpdb;
        $table_name = $wpdb->prefix . 'representative_payment_requests';

        // Insert into the database
        $wpdb->insert($table_name, array(
            'name' => $name,
            'email' => $email,
            'amount' => $amount,
            'reason' => $reason,
            'date_submitted' => current_time('mysql'),
        ));

        // Show a success message
        echo '<p>Thank you for your payment request. We will process it soon.</p>';
    }

    return ob_get_clean();
}

// Register the shortcode
function rc_register_payment_request_shortcode()
{
    add_shortcode('representative_payment_request_form', 'rc_representative_payment_request_form');
}
add_action('init', 'rc_register_payment_request_shortcode');

// Create a custom table for storing payment requests when the plugin is activated
function rc_create_payment_request_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'representative_payment_requests';

    // SQL to create the table
    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        reason TEXT NOT NULL,
        date_submitted DATETIME NOT NULL,
        PRIMARY KEY (id)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'rc_create_payment_request_table');

//Representative Login

// Create a shortcode for the custom login form
function rc_representative_login_form()
{
    if (is_user_logged_in()) {
        return '<p>You are already logged in.</p>';
    }

    ob_start();
    ?>
    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
        <label for="representative_username">Username:</label>
        <input type="text" id="representative_username" name="representative_username" required>

        <label for="representative_password">Password:</label>
        <input type="password" id="representative_password" name="representative_password" required>

        <input type="submit" name="representative_login" value="Login">
    </form>
    <?php
    // Handle the form submission
    if (isset($_POST['representative_login'])) {
        $username = sanitize_text_field($_POST['representative_username']);
        $password = sanitize_text_field($_POST['representative_password']);

        // Attempt to log in
        $user = wp_authenticate($username, $password);

        if (!is_wp_error($user)) {
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            wp_redirect(home_url()); // Redirect after successful login
            exit;
        } else {
            echo '<p>Invalid username or password.</p>';
        }
    }

    return ob_get_clean();
}
add_shortcode('representative_login_form', 'rc_representative_login_form');


//representative dashbaord

function rc_representative_dashboard()
{
    // Check if the user is logged in
    if (is_user_logged_in()) {
        // Get the current user's details
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $user_name = $current_user->display_name;
        $user_email = $current_user->user_email;  // Get the user's email

        // Fetch Total Earnings (for simplicity, just an example)
        $total_earnings = get_user_meta($user_id, 'total_earnings', true); // Replace with actual earnings data

        // Fetch Payment Requests
        global $wpdb;
        $table_name = $wpdb->prefix . 'representative_payment_requests';
        $payment_requests = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id");

        ob_start();
    ?>
        <div class="rc-dashboard">
            <h2>Welcome to your Dashboard, <?php echo esc_html($user_name); ?></h2>
            <p>Email: <?php echo esc_html($user_email); ?></p>

            <div class="rc-dashboard-menu">
                <ul>
                    <li><a href="#profile-tab" class="active-tab">Profile</a></li>
                    <li><a href="#earnings-tab">Total Earnings</a></li>
                    <li><a href="#payment-tab">Payment Request</a></li>
                    <li><a href="#payment-status-tab">Payment Request Status</a></li>
                  
                    <a href="<?php echo wp_logout_url(home_url('/representative-login')); ?>">Logout</a>
                </ul>
            </div>

            <div class="rc-dashboard-content">
                <!-- Profile Tab -->
                <div id="profile-tab" class="rc-tab-content active">
                    <h3>Your Profile</h3>
                    <form method="post" action="">
                        <label for="user_name">Name:</label>
                        <input type="text" id="user_name" name="user_name" value="<?php echo esc_attr($user_name); ?>" required>

                        <label for="user_email">Email:</label>
                        <input type="email" id="user_email" name="user_email" value="<?php echo esc_attr($user_email); ?>" required>

                        <input type="submit" name="update_profile" value="Update Profile">
                    </form>
                    <?php
                    if (isset($_POST['update_profile'])) {
                        $updated_name = sanitize_text_field($_POST['user_name']);
                        $updated_email = sanitize_email($_POST['user_email']);

                        // Update the user profile details
                        wp_update_user(array(
                            'ID' => $user_id,
                            'display_name' => $updated_name,
                            'user_email' => $updated_email,
                        ));

                        echo '<p>Profile updated successfully!</p>';
                    }
                    ?>
                </div>

                <!-- Total Earnings Tab -->
                <div id="earnings-tab" class="rc-tab-content">
                    <h3>Total Earnings</h3>
                    <p>Your total earnings: <?php echo esc_html($total_earnings ? $total_earnings : 'Not available'); ?></p>
                </div>

                <!-- Payment Request Tab -->
                <div id="payment-tab" class="rc-tab-content">
                    <h3>Submit Payment Request</h3>
                    <form method="post" action="" enctype="multipart/form-data">
                        <label for="payment_amount">Payment Amount:</label>
                        <input type="number" id="payment_amount" name="payment_amount" required>

                        <label for="payment_reason">Reason for Payment:</label>
                        <textarea id="payment_reason" name="payment_reason" required></textarea>

                        <label for="payment_proof">Payment Proof (Upload Receipt):</label>
                        <input type="file" id="payment_proof" name="payment_proof" accept="image/*,application/pdf" required>

                        <input type="submit" name="submit_payment_request" value="Submit Payment Request">
                    </form>
                    <?php
                    if (isset($_POST['submit_payment_request'])) {
                        // Handle file upload
                        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
                            $uploaded_file = $_FILES['payment_proof'];
                            $upload_dir = wp_upload_dir(); // Get the uploads directory

                            // Set the destination path for the uploaded file
                            $target_path = $upload_dir['path'] . '/' . basename($uploaded_file['name']);
                            $target_url = $upload_dir['url'] . '/' . basename($uploaded_file['name']);

                            // Move the file to the uploads directory
                            if (move_uploaded_file($uploaded_file['tmp_name'], $target_path)) {
                                // Insert payment request with file URL and user email into the database
                                $payment_amount = sanitize_text_field($_POST['payment_amount']);
                                $payment_reason = sanitize_textarea_field($_POST['payment_reason']);
                                $date_submitted = current_time('mysql');

                                $invoice_url = generate_invoice_pdf($user_name, $user_email, $payment_amount, $payment_reason, $target_url);

                                // Insert the payment request into the database
                                $wpdb->insert(
                                    $table_name,
                                    array(
                                        'user_id' => $user_id,
                                        'email' => $user_email, // Store the user email in the payment request
                                        'name' => $user_name, // Store the user email in the payment request
                                        'amount' => $payment_amount,
                                        'reason' => $payment_reason,
                                        'payment_proof_url' => $target_url, // Save the URL of the uploaded file
                                        'date_submitted' => $date_submitted,
                                        'status' => 'Pending', // Default status
                                        'invoice_url' => $invoice_url, // Save the generated invoice URL    
                                    )
                                );
                                $request_data = array(
                                    'user_id' => $user_id,
                                    'email' => $user_email, // Store the user email in the payment request
                                    'name' => $user_name, // Store the user email in the payment request
                                    'amount' => $payment_amount,
                                    'reason' => $payment_reason,
                                    'payment_proof_url' => $target_url, // Save the URL of the uploaded file
                                    'date_submitted' => $date_submitted,
                                    'status' => 'Pending', // Default status
                                    'invoice_url' => $invoice_url, // Save the generated invoice URL    
                                );

                                submit_payment_request($request_data);



                                echo '<p>Your payment request has been submitted successfully!</p>';
                            } else {
                                echo '<p>There was an error uploading the payment proof. Please try again.</p>';
                            }
                        } else {
                            echo '<p>Please upload a valid payment proof file.</p>';
                        }
                    }
                    ?>
                </div>

                <!-- Payment Status Tab -->
                <div id="payment-status-tab" class="rc-tab-content">
                    <h3>Payment Requests and Status</h3>
                    <?php if (!empty($payment_requests)) : ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Amount</th>
                                    <th>Reason</th>
                                    <th>Submitted On</th>
                                    <th>Status</th>
                                    <th>Invoice</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payment_requests as $request) : ?>
                                    <tr>
                                        <td><?php echo esc_html($request->id); ?></td>
                                        <td><?php echo esc_html($request->amount); ?></td>
                                        <td><?php echo esc_html($request->reason); ?></td>
                                        <td><?php echo esc_html($request->date_submitted); ?></td>
                                        <td><?php echo esc_html($request->status); ?></td>
                                        <td>
                                            <a href="<?php echo esc_url($request->invoice_url); ?>" target="_blank" download>
                                                View Invoice
                                            </a>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p>No payment requests found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <style>
            .rc-dashboard {
                width: 100%;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 8px;
            }

            .rc-dashboard-menu ul {
                list-style: none;
                padding: 0;
                display: flex;
            }

            .rc-dashboard-menu ul li {
                margin-right: 20px;
            }

            .rc-dashboard-menu ul li a {
                text-decoration: none;
                color: #0073aa;
                font-weight: bold;
            }

            .rc-dashboard-menu ul li a.active-tab {
                color: #000;
                border-bottom: 2px solid #0073aa;
            }

            .rc-tab-content {
                display: none;
            }

            .rc-tab-content.active {
                display: block;
            }

            .rc-tab-content form {
                margin-top: 20px;
            }

            .rc-tab-content input[type="text"],
            .rc-tab-content input[type="email"],
            .rc-tab-content input[type="number"],
            .rc-tab-content input[type="file"],
            .rc-tab-content textarea {
                width: 100%;
                padding: 10px;
                margin: 8px 0;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            .rc-tab-content input[type="submit"] {
                background-color: #0073aa;
                color: #fff;
                border: none;
                padding: 10px 15px;
                cursor: pointer;
            }

            .rc-tab-content input[type="submit"]:hover {
                background-color: #005177;
            }


            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-size: 16px;
                text-align: left;
            }

            thead {
                background-color: #0073aa;
                color: white;
            }

            thead th {
                padding: 10px;
                font-weight: bold;
            }

            tbody tr {
                border-bottom: 1px solid #ddd;
            }

            tbody tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            tbody tr:hover {
                background-color: #f1f1f1;
            }

            tbody td {
                padding: 10px;
                color: #333;
            }

            tbody td a {
                color: #0073aa;
                text-decoration: none;
                font-weight: bold;
            }

            tbody td a:hover {
                color: #005f8c;
                text-decoration: underline;
            }

            @media screen and (max-width: 768px) {
                table {
                    font-size: 14px;
                }

                thead th,
                tbody td {
                    padding: 8px;
                }
            }
        </style>

        <script>
            // Tab functionality
            document.querySelectorAll('.rc-dashboard-menu ul li a').forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all tabs
                    document.querySelectorAll('.rc-dashboard-menu ul li a').forEach(a => a.classList.remove('active-tab'));

                    // Hide all tab contents
                    document.querySelectorAll('.rc-tab-content').forEach(content => content.classList.remove('active'));

                    // Add active class to clicked tab
                    tab.classList.add('active-tab');

                    // Show the respective tab content
                    const target = document.querySelector(tab.getAttribute('href'));
                    target.classList.add('active');
                });
            });
        </script>

<?php
        return ob_get_clean();
    } else {
        // Display the login form if the user is not logged in
        return do_shortcode('[login_form]');  // Or your custom login form shortcode
    }
}
add_shortcode('representative_dashboard', 'rc_representative_dashboard');


function generate_invoice_pdf($user_name, $user_email, $payment_amount, $payment_reason, $payment_proof_url)
{
    // Include TCPDF library (ensure the autoloader path is correct)
    require_once ABSPATH . 'vendor/autoload.php'; // Path to Composer's autoload.php

    // Create a new PDF document
    $pdf = new TCPDF();

    // Set document information
    $pdf->SetCreator('WordPress');
    $pdf->SetAuthor($user_name);
    $pdf->SetTitle('Payment Request Invoice');
    $pdf->SetSubject('Invoice for Payment Request');
    $pdf->SetKeywords('Invoice, Payment Request, TCPDF');

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Add content
    $html = "
        <h1>Payment Request Invoice</h1>
        <p><strong>User Name:</strong> $user_name</p>
        <p><strong>User Email:</strong> $user_email</p>
        <p><strong>Payment Amount:</strong> $payment_amount</p>
        <p><strong>Reason:</strong> $payment_reason</p>
        <p><strong>Payment Proof:</strong> <a href='$payment_proof_url'>$payment_proof_url</a></p>
    ";

    // Write HTML content to the PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Generate a unique file name
    $upload_dir = wp_upload_dir();
    $file_name = 'payment_request_invoice_' . time() . '.pdf';
    $file_path = $upload_dir['path'] . '/' . $file_name;

    // Output the PDF to the server
    $pdf->Output($file_path, 'F'); // 'F' means save to file

    // Return the URL for download
    return $upload_dir['url'] . '/' . $file_name;
}


// Function to handle payment request submission
function submit_payment_request($request_data) {
    global $wpdb;

    // Prepare email notification
    $admin_email = get_option('admin_email'); // Retrieves the admin's email
    $subject = 'New Payment Request Submitted';
    $message = "
        A new payment request has been submitted. Details are as follows:
        Amount: {$request_data['amount']}
        Reason: {$request_data['reason']}
        Submitted On: {$request_data['date_submitted']}
        Please log in to the admin panel to review the request.
    ";

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    // Send email to the admin
    wp_mail($admin_email, $subject, nl2br($message), $headers);

    return true;
}

