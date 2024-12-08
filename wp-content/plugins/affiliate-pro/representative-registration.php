<?php

/**
 * Plugin Name: Affiliate Management
 * Description: A plugin for registering representatives and managing their coupons.
 * Version: 1.0
 * Author: Trifecta Medias
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
        // Sanitize basic fields
        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);

        // Sanitize additional fields
        $full_name = sanitize_text_field($_POST['full_name']);
        $address = sanitize_text_field($_POST['address']);
        $city = sanitize_text_field($_POST['city']);
        $postal_code = sanitize_text_field($_POST['postal_code']);
        $phone = sanitize_text_field($_POST['phone']);
        $experience_years = intval($_POST['experience_years']);
        $real_estate_experience = sanitize_text_field($_POST['real_estate_experience']);
        $sector_details = sanitize_textarea_field($_POST['sector_details']);
        $education = sanitize_text_field($_POST['education']);
        $certifications = sanitize_textarea_field($_POST['certifications']);
        $target_client_experience = sanitize_text_field($_POST['target_client_experience']);
        $target_client_details = sanitize_textarea_field($_POST['target_client_details']);
        $french = sanitize_text_field($_POST['french']);
        $english = sanitize_text_field($_POST['english']);
        $other_languages = sanitize_text_field($_POST['other_languages']);
        $criminal_offense = sanitize_text_field($_POST['criminal_offense']);
        $criminal_details = sanitize_textarea_field($_POST['criminal_details']);
        $conflict_of_interest = sanitize_text_field($_POST['conflict_of_interest']);
        $conflict_details = sanitize_textarea_field($_POST['conflict_details']);
        $background_check = sanitize_text_field($_POST['background_check']);
        $digital_skills = sanitize_text_field($_POST['digital_skills']);
        $presentations = sanitize_text_field($_POST['presentations']);
        $conflict_resolution = sanitize_text_field($_POST['conflict_resolution']);
        $availability = sanitize_text_field($_POST['availability']);
        $weekend_availability = sanitize_text_field($_POST['weekend_availability']);
        $reference1_name = sanitize_text_field($_POST['reference1_name']);
        $reference1_phone = sanitize_text_field($_POST['reference1_phone']);
        $reference1_relationship = sanitize_text_field($_POST['reference1_relationship']);
        $reference2_name = sanitize_text_field($_POST['reference2_name']);
        $reference2_phone = sanitize_text_field($_POST['reference2_phone']);
        $reference2_relationship = sanitize_text_field($_POST['reference2_relationship']);

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

        // Save additional metadata
        update_user_meta($user_id, 'full_name', $full_name);
        update_user_meta($user_id, 'address', $address);
        update_user_meta($user_id, 'city', $city);
        update_user_meta($user_id, 'postal_code', $postal_code);
        update_user_meta($user_id, 'phone', $phone);
        update_user_meta($user_id, 'experience_years', $experience_years);
        update_user_meta($user_id, 'real_estate_experience', $real_estate_experience);
        update_user_meta($user_id, 'sector_details', $sector_details);
        update_user_meta($user_id, 'education', $education);
        update_user_meta($user_id, 'certifications', $certifications);
        update_user_meta($user_id, 'target_client_experience', $target_client_experience);
        update_user_meta($user_id, 'target_client_details', $target_client_details);
        update_user_meta($user_id, 'french', $french);
        update_user_meta($user_id, 'english', $english);
        update_user_meta($user_id, 'other_languages', $other_languages);
        update_user_meta($user_id, 'criminal_offense', $criminal_offense);
        update_user_meta($user_id, 'criminal_details', $criminal_details);
        update_user_meta($user_id, 'conflict_of_interest', $conflict_of_interest);
        update_user_meta($user_id, 'conflict_details', $conflict_details);
        update_user_meta($user_id, 'background_check', $background_check);
        update_user_meta($user_id, 'digital_skills', $digital_skills);
        update_user_meta($user_id, 'presentations', $presentations);
        update_user_meta($user_id, 'conflict_resolution', $conflict_resolution);
        update_user_meta($user_id, 'availability', $availability);
        update_user_meta($user_id, 'weekend_availability', $weekend_availability);
        update_user_meta($user_id, 'reference1_name', $reference1_name);
        update_user_meta($user_id, 'reference1_phone', $reference1_phone);
        update_user_meta($user_id, 'reference1_relationship', $reference1_relationship);
        update_user_meta($user_id, 'reference2_name', $reference2_name);
        update_user_meta($user_id, 'reference2_phone', $reference2_phone);
        update_user_meta($user_id, 'reference2_relationship', $reference2_relationship);

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
            <h1>Formulaire d'inscription d'un représentant LOGESPOIR</h1>
            <form method="POST" class="representative-registration-form">
                <!-- Informations personnelles -->
                <h2>Informations personnelles</h2>
                <label for="full_name">Nom complet :</label>
                <input type="text" name="full_name" id="full_name" required>

                <label for="address">Adresse :</label>
                <input type="text" name="address" id="address" required>

                <label for="city">Ville :</label>
                <input type="text" name="city" id="city" required>

                <label for="postal_code">Code postal :</label>
                <input type="text" name="postal_code" id="postal_code" required>

                <label for="phone">Téléphone :</label>
                <input type="tel" name="phone" id="phone" required>

                <label for="email">Courriel :</label>
                <input type="email" name="email" id="email" required>

                <!-- Informations professionnelles -->
                <h2>Informations professionnelles</h2>
                <label for="experience_years">Années d'expérience (service à la clientèle ou ventes) :</label>
                <input type="number" name="experience_years" id="experience_years" required>

                <label for="real_estate_experience">Expérience dans l'immobilier ou le secteur financier :</label>
                <select name="real_estate_experience" id="real_estate_experience" required>
                    <option value="yes">Oui</option>
                    <option value="no">Non</option>
                </select>

                <label for="sector_details">Si oui, précisez :</label>
                <textarea name="sector_details" id="sector_details"></textarea>

                <label for="education">Niveau d'éducation atteint :</label>
                <input type="text" name="education" id="education" required>

                <label for="certifications">Formations ou certifications pertinentes :</label>
                <textarea name="certifications" id="certifications"></textarea>

                <label for="target_client_experience">Avez-vous travaillé avec des clientèles cibles (nouveaux arrivants, deuxième chance) :</label>
                <select name="target_client_experience" id="target_client_experience" required>
                    <option value="yes">Oui</option>
                    <option value="no">Non</option>
                </select>

                <label for="target_client_details">Si oui, détaillez votre expérience :</label>
                <textarea name="target_client_details" id="target_client_details"></textarea>

                <!-- Compétences linguistiques -->
                <h2>Compétences linguistiques</h2>
                <label for="french">Français :</label>
                <select name="french" id="french" required>
                    <option value="yes">Oui</option>
                    <option value="no">Non</option>
                </select>

                <label for="english">Anglais :</label>
                <select name="english" id="english" required>
                    <option value="yes">Oui</option>
                    <option value="no">Non</option>
                </select>

                <label for="other_languages">Autres langues (précisez) :</label>
                <input type="text" name="other_languages" id="other_languages">

                <!-- Conformité et déclarations judiciaires -->
                <h2>Conformité et déclarations judiciaires</h2>
                <label for="criminal_offense">Avez-vous été reconnu coupable d'un acte criminel ou êtes-vous sous enquête ?</label>
                <select name="criminal_offense" id="criminal_offense" required>
                    <option value="yes">Oui</option>
                    <option value="no">Non</option>
                </select>

                <label for="criminal_details">Si oui, fournissez des détails :</label>
                <textarea name="criminal_details" id="criminal_details"></textarea>

                <label for="conflict_of_interest">Avez-vous des engagements qui pourraient entrer en conflit avec la mission ou les valeurs de LOGESPOIR ?</label>
                <select name="conflict_of_interest" id="conflict_of_interest" required>
                    <option value="yes">Oui</option>
                    <option value="no">Non</option>
                </select>

                <label for="conflict_details">Si oui, précisez :</label>
                <textarea name="conflict_details" id="conflict_details"></textarea>

                <label for="background_check">Êtes-vous prêt à fournir un certificat d'antécédents judiciaires si nécessaire ?</label>
                <select name="background_check" id="background_check" required>
                    <option value="yes">Oui</option>
                    <option value="no">Non</option>
                </select>

                <!-- Compétences clés et disponibilités -->
                <h2>Compétences clés et disponibilités</h2>
                <label for="digital_skills">À l'aise avec les outils numériques (CRM, gestion de dossiers) :</label>
                <select name="digital_skills" id="digital_skills" required>
                    <option value="yes">Oui</option>
                    <option value="no">Non</option>
                </select>

                <label for="presentations">Capacité à mener des présentations ou formations :</label>
                <select name="presentations" id="presentations" required>
                    <option value="yes">Oui</option>
                    <option value="no">Non</option>
                </select>

                <label for="conflict_resolution">Expérience en médiation ou résolution de conflits :</label>
                <select name="conflict_resolution" id="conflict_resolution" required>
                    <option value="yes">Oui</option>
                    <option value="no">Non</option>
                </select>

                <label for="availability">Disponibilité (temps plein / temps partiel) :</label>
                <input type="text" name="availability" id="availability" required>

                <label for="weekend_availability">Disponibilité à travailler le soir ou les fins de semaine :</label>
                <select name="weekend_availability" id="weekend_availability" required>
                    <option value="yes">Oui</option>
                    <option value="no">Non</option>
                </select>

                <!-- Références -->
                <h2>Références</h2>
                <label for="reference1_name">Nom de la référence 1 :</label>
                <input type="text" name="reference1_name" id="reference1_name" required>

                <label for="reference1_phone">Téléphone de la référence 1 :</label>
                <input type="tel" name="reference1_phone" id="reference1_phone" required>

                <label for="reference1_relationship">Relation avec la référence 1 :</label>
                <input type="text" name="reference1_relationship" id="reference1_relationship" required>

                <label for="reference2_name">Nom de la référence 2 :</label>
                <input type="text" name="reference2_name" id="reference2_name" required>

                <label for="reference2_phone">Téléphone de la référence 2 :</label>
                <input type="tel" name="reference2_phone" id="reference2_phone" required>

                <label for="reference2_relationship">Relation avec la référence 2 :</label>
                <input type="text" name="reference2_relationship" id="reference2_relationship" required>

                <!-- Déclaration -->
                <h2>Déclaration</h2>
                <label>
                    <input type="checkbox" name="declaration" required>
                    Je certifie que les informations fournies sont exactes et complètes.
                </label>

                <?php wp_nonce_field('representative_registration', 'representative_registration_nonce'); ?>
                <button type="submit">S'inscrire</button>
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

    add_submenu_page(
        'affiliate-management',
        'Timesheets',
        'Timesheets',
        'manage_options',
        'timesheets',
        'render_timesheets_admin_page',
        'dashicons-calendar-alt',
        20
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
                    <th scope="col" class="manage-column">Interac Email</th>
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
                            <td><?php echo esc_html($request->interac_transfer_email); ?></td>
                            <td><?php echo esc_html($request->reason); ?></td>
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
    echo '<th>
IDENTIFIANT</th><th>Nom d utilisateur</th><th>E-mail</th><th>Gains totaux</th><th>Nombre de ventes</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($representatives as $representative) {
        // Fetch earnings
        $earnings = get_user_meta($representative->ID, '_representative_earnings', true);
        $earnings = $earnings ? wc_price($earnings) : wc_price(0);

        // Query completed orders linked to the representative
        $orders = get_posts([
            // 'post_type'   => 'any',
            'post_status' => 'publish',
            // 'meta_key'    => '_representative_id',
            // 'meta_value'  => $representative->ID,
            'post_name' => 'rep-alpfud3y'
            // 'numberposts' => -1,  // Retrieve all relevant orders
        ]);

        // Debug: Print orders data to check the result
        // echo '<pre>';
        // print_r($orders);  // Check if orders are being returned
        // echo '</pre>';

        // Count the orders
        $sales_count = count($orders); // This should give the correct count of orders

        // Output representative data
        echo '<tr>';
        echo '<td>' . esc_html($representative->ID) . '</td>';
        echo '<td>' . esc_html($representative->user_login) . '</td>';
        echo '<td>' . esc_html($representative->user_email) . '</td>';
        echo '<td>' . esc_html(strip_tags($earnings)) . '</td>';
        echo '<td>' . esc_html($sales_count) . '</td>';  // Display the sales count here
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
    echo '<th>ID</th><th>Nom d utilisateur</th><th>E-mail</th><th>Code promo</th>';
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

            global $wpdb;

            // Update post_title and post_status in wp_posts table
            $sql = $wpdb->prepare(
                "UPDATE {$wpdb->posts}
                 SET post_title = %s, post_status = %s
                 WHERE ID = %d",
                $coupon_code, 'publish', $order_id
            );

            $wpdb->query($sql); // Execute the query

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
                <h2>Demande de paiement représentant</h2>

                <label for="representative_name">Nom:</label>
                <input type="text" id="representative_name" name="representative_name" required>

                <label for="representative_email"> E-mail:</label>
                <input type="email" id="representative_email" name="representative_email" required>

                <label for="payment_amount">Montant du paiement :</label>
                <input type="number" id="payment_amount" name="payment_amount" required>

                <label for="payment_reason">Raison du paiement :</label>
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
        echo '<p>Merci pour votre demande de paiement. Nous la traiterons prochainement.</p>';
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
        interac_transfer_email VARCHAR(255),
        date_submitted DATETIME NOT NULL,
        PRIMARY KEY (id)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'rc_create_payment_request_table');

//Representative Login

// Create a shortcode for the custom login form
function rc_representative_login_form() {
    if (is_user_logged_in()) {
        return '<p>You are already logged in.</p>';
    }

    ob_start();

    // Get current page URL
    $current_url = esc_url(home_url(add_query_arg(null, null)));

    // Check if the user is on the "Forgot Password" action
    if (isset($_GET['action']) && $_GET['action'] === 'forgot_password') {
        ?>
        <div class="form-wrapper">
            <div class="form-card">
                <h2> Mot de passe oublié</h2>
                <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                    <?php wp_nonce_field('rc_forgot_password', 'rc_forgot_password_nonce'); ?>
                    <label for="user_email">Entrez votre adresse email :</label>
                    <input type="email" id="user_email" name="user_email" required>
                    <br><br>
                    <input type="submit" name="reset_password" value="Reset Password">
                </form>
            </div>
        </div>
        <?php

        if (isset($_POST['reset_password'])) {
            if (!isset($_POST['rc_forgot_password_nonce']) || !wp_verify_nonce($_POST['rc_forgot_password_nonce'], 'rc_forgot_password')) {
                die('Security check failed.');
            }

            $user_email = sanitize_email($_POST['user_email']);
            if (!email_exists($user_email)) {
                echo '<p>Adresse e-mail introuvable. Veuillez réessayer.</p>';
            } else {
                $user = get_user_by('email', $user_email);
                $reset_key = get_password_reset_key($user);

                if (!is_wp_error($reset_key)) {
                    $reset_link = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login));
                    $subject = 'Demande de réinitialisation du mot de passe';
                    $message = 'Click the link below to reset your password:' . "\r\n" . $reset_link;

                    if (wp_mail($user_email, $subject, $message)) {
                        echo '<p>Password reset email has been sent. Please check your inbox.</p>';
                    } else {
                        echo '<p>Error sending password reset email. Please try again later.</p>';
                    }
                } else {
                    echo '<p>Error generating reset link. Please try again.</p>';
                }
            }
        }
    } else {
        ?>
        <div class="form-wrapper">
            <div class="form-card">
                <h2>Representative Login</h2>
                <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                    <?php wp_nonce_field('rc_representative_login', 'rc_login_nonce'); ?>
                    <label for="representative_username">Username:</label>
                    <input type="text" id="representative_username" name="representative_username" required>

                    <label for="representative_password">Password:</label>
                    <input type="password" id="representative_password" name="representative_password" required>
                    <br> <br>
                    <input type="submit" name="representative_login" value="Login">
                </form>
                <p><a href="?action=forgot_password" id="forgot-password-link">Forgot Password?</a></p>
            </div>
        </div>
        <?php

        if (isset($_POST['representative_login'])) {
            if (!isset($_POST['rc_login_nonce']) || !wp_verify_nonce($_POST['rc_login_nonce'], 'rc_representative_login')) {
                die('Security check failed.');
            }

            $username = sanitize_text_field($_POST['representative_username']);
            $password = $_POST['representative_password'];

            $credentials = array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => true,
            );

            $user = wp_signon($credentials, false);

            if (!is_wp_error($user)) {
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID);
                wp_redirect($current_url); // Redirect to the same page
                exit;
            } else {
                echo '<p>' . $user->get_error_message() . '</p>';
            }
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
        //$total_earnings = get_user_meta($user_id, 'total_earnings', true); // Replace with actual earnings data
        $total_earnings = get_user_meta($user_id, '_representative_earnings', true);
        print_r($total_earnings);

        // Fetch Payment Requests
        global $wpdb;
        $table_name = $wpdb->prefix . 'representative_payment_requests';
        $payment_requests = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id");

        ob_start();
    ?>
        <div class="rc-dashboard">
            <h2>Bienvenue sur votre tableau de bord,<?php echo esc_html($user_name); ?></h2>
            <p> E-mail: <?php echo esc_html($user_email); ?></p>

            <div class="rc-dashboard-menu">
                <ul>
                    <li><a href="#profile-tab" class="active-tab">Profil</a></li>
                    <li><a href="#earnings-tab">Gains totaux</a></li>
                    <li><a href="#payment-tab">Demande de paiement</a></li>
                    <li><a href="#payment-status-tab">Statut de la demande de paiement</a></li>
                    <li><a href="#timesheet-tab">feuille de temps</a></li>

                    <a href="<?php echo wp_logout_url(home_url('/representative-login')); ?>">Logout</a>
                </ul>
            </div>

            <div class="rc-dashboard-content">
                <!-- Profile Tab -->
                <div id="profile-tab" class="rc-tab-content active">
                    <form method="post" action="">
                        <label for="user_name">Nom:</label>
                        <input type="text" id="user_name" name="user_name" value="<?php echo esc_attr($user_name); ?>" required>

                        <label for="user_email">E-mail:</label>
                        <input type="email" id="user_email" name="user_email" value="<?php echo esc_attr($user_email); ?>" required>

                        <input type="submit" name="update_profile" value="Mettre à jour le profil">
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
                    <h3>Gains totaux</h3>

                    <p>
                        Vos gains totaux : <?php echo esc_html($total_earnings ? $total_earnings : 'Not available'); ?></p>
                </div>

                <!-- Payment Request Tab -->
                <div id="payment-tab" class="rc-tab-content">
                    <h3>Soumettre une demande de paiement</h3>
                    <form method="post" action="" enctype="multipart/form-data">
                        <label for="interac_transfer_email_label">Courriel pour virement Interac :</label>
                        <input type="email" id="interac_transfer_email" name="interac_transfer_email" required>
                        <label for="payment_amount">Montant du paiement :</label>
                        <input type="number" id="payment_amount" name="payment_amount" required>

                        <label for="payment_reason">Raison du paiement :</label>
                        <textarea id="payment_reason" name="payment_reason" required></textarea>
                        <label for="address_label">Adresse:</label>
                        <textarea id="address" name="address" required></textarea>

                        <label for="payment_proof">
                            Raison du paiement :</label>
                        <input type="file" id="payment_proof" name="payment_proof" accept="image/*,application/pdf" required>

                        <input type="submit" name="submit_payment_request" value="Soumettre une demande de paiement">
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
                                $interac_transfer_email = sanitize_text_field($_POST['interac_transfer_email']);

                                $invoice_url = generate_invoice_pdf($user_name, $user_email, $payment_amount, $payment_reason, $target_url);

                                // Insert the payment request into the database
                                $wpdb->insert(
                                    $table_name,
                                    array(
                                        'user_id' => $user_id,
                                        'email' => $user_email, // Store the user email in the payment request
                                        'name' => $user_name, // Store the user email in the payment request
                                        'interac_transfer_email' => $interac_transfer_email,
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
                                    'interac_transfer_email' => $interac_transfer_email,
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
                    <h3>Demandes de paiement et statut</h3>
                    <?php if (!empty($payment_requests)) : ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Montante</th>
                                    <th>Courriel pour virement interac</th>
                                    <th>Soumis le</th>
                                    <th>Statut</th>
                                    <th>Facture</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payment_requests as $request) : ?>
                                    <tr>
                                        <td><?php echo esc_html($request->id); ?></td>
                                        <td><?php echo esc_html($request->amount); ?></td>
                                        <td><?php echo esc_html($request->interac_transfer_email); ?></td>
                                        <td><?php echo esc_html($request->date_submitted); ?></td>
                                        <td><?php echo esc_html($request->status); ?></td>
                                        <td>
                                            <a href="<?php echo esc_url($request->invoice_url); ?>" target="_blank" download>
                                               
                                            Afficher la facture
                                            </a>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p>Aucune demande de paiement trouvée.</p>
                    <?php endif; ?>
                </div>

                <div id="timesheet-tab" class="rc-tab-content">
                    <h3>Soumettre une feuille de temps</h3>
                    <form method="post" action="" enctype="multipart/form-data">
                        <label for="task_date">Date :</label>
                        <input type="date" id="task_date" name="task_date" required>

                        <label for="task_name">Nom de la tâche :</label>
                        <input type="text" id="task_name" name="task_name" required>

                        <label for="hours_spent">Heures passées :</label>
                        <input type="number" id="hours_spent" name="hours_spent" required min="1">

                        <input type="submit" name="submit_timesheet" value="Soumettre la feuille de temps">
                    </form>

                    <?php
                    // Handle timesheet submission
                    if (isset($_POST['submit_timesheet'])) {
                        global $wpdb;
                        $table_name = $wpdb->prefix . "timesheets";

                        // Sanitize and validate inputs
                        $task_date = sanitize_text_field($_POST['task_date']);
                        $task_name = sanitize_text_field($_POST['task_name']);
                        $hours_spent = intval($_POST['hours_spent']);
                        $user_id = get_current_user_id();

                        if ($task_date && $task_name && $hours_spent > 0) {
                            // Insert into database
                            $wpdb->insert(
                                $table_name,
                                array(
                                    'user_id' => $user_id,
                                    'task_date' => $task_date,
                                    'task_name' => $task_name,
                                    'hours_spent' => $hours_spent
                                )
                            );

                            echo '<p>Votre feuille de temps a été soumise avec succès!</p>';
                        } else {
                            echo '<p>Erreur: Veuillez vérifier les champs du formulaire.</p>';
                        }
                    }
                    ?>
                </div>

            </div>
        </div>

        <style>
            /* Dashboard Container */
            .rc-dashboard {
                width: 100%;
                max-width: 70%;
                margin: 0 auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 8px;
                background-color: #fff;
                /* Added white background for better contrast */
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                /* Subtle shadow for depth */
            }

            /* Dashboard Menu */
            .rc-dashboard-menu ul {
                list-style: none;
                background-color: #005177;
                /* Dark gray background */
                padding: 15px;
                /* Add padding around the list */
                margin: 0;
                display: flex;
                flex-wrap: wrap;
                /* Allow wrapping for better responsiveness */
                gap: 15px;
                /* Space between items */
                border-radius: 8px;
                /* Optional: Add rounded corners */
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                /* Optional: Add a subtle shadow */

            }


            .rc-dashboard-menu ul li {
                margin: 0;
                /* Removed specific margin-right for consistency */
            }

            .rc-dashboard-menu ul li a {
                text-decoration: none;
                color: white;
                font-weight: bold;
                padding: 8px 12px;
                /* Added padding for better clickability */
                border-radius: 4px;
                /* Rounded corners for a modern look */
                transition: background-color 0.3s ease, color 0.3s ease;
                /* Smooth hover effects */
            }

            .rc-dashboard-menu a {
                text-decoration: none;
                color: white;
                font-weight: bold;
                padding: 8px 12px;
                /* Added padding for better clickability */
                border-radius: 4px;
                /* Rounded corners for a modern look */
                transition: background-color 0.3s ease, color 0.3s ease;
                /* Smooth hover effects */
            }

            .rc-dashboard-menu ul li a:hover {
                background-color: #e9f4fb;
                /* Light blue background on hover */
                color: #005f8c;
            }

            .rc-dashboard-menu ul li a.active-tab {
                color: #000;
                border-bottom: 2px solid #0073aa;
                padding-bottom: 6px;
                /* Added spacing to align with border */
            }

            /* Tab Content */
            .rc-tab-content {
                display: none;
                padding-top: 20px;
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
                padding: 12px;
                margin: 10px 0;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
                /* Ensures padding doesn't affect width */
                transition: border-color 0.3s ease;
            }

            .rc-tab-content input[type="text"]:focus,
            .rc-tab-content input[type="email"]:focus,
            .rc-tab-content input[type="number"]:focus,
            .rc-tab-content input[type="file"]:focus,
            .rc-tab-content textarea:focus {
                border-color: #0073aa;
                /* Highlight border on focus */
                outline: none;
                /* Remove default outline */
            }

            .rc-tab-content input[type="submit"] {
                background-color: #0073aa;
                color: #fff;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                transition: background-color 0.3s ease;
            }

            .rc-tab-content input[type="submit"]:hover {
                background-color: #005177;
            }

            /* Table Styling */
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-size: 16px;
                text-align: left;
                background-color: #fff;
                /* Added background for clarity */
                border: 1px solid #ddd;
            }

            thead {
                background-color: #0073aa;
                color: white;
            }

            thead th {
                padding: 12px;
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

            /* Responsive Adjustments */
            @media screen and (max-width: 768px) {
                .rc-dashboard {
                    padding: 15px;
                }

                .rc-dashboard-menu ul {
                    gap: 10px;
                    justify-content: center;
                    /* Center menu items on smaller screens */
                }

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
       // return do_shortcode('[representative_login_form]');  // Or your custom login form shortcode
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
function submit_payment_request($request_data)
{
    global $wpdb;

    // Prepare email notification
    $admin_email = get_option('admin_email'); // Retrieves the admin's email
    $subject = 'New Payment Request Submitted';
    $message = "
        A new payment request has been submitted. Details are as follows:<br>
        <strong>Amount:</strong> {$request_data['amount']}<br>
        <strong>Reason:</strong> {$request_data['reason']}<br>
        <strong>Submitted On:</strong> {$request_data['date_submitted']}<br>
        <strong>Invoice URL:</strong> <a href='{$request_data['invoice_url']}'>View Invoice</a><br>
        <br>
        Please log in to the admin panel to review the request.
    ";

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    // Send email to the admin
    wp_mail($admin_email, $subject, nl2br($message), $headers);

    return true;
}


add_action('woocommerce_payment_complete', 'process_order_and_update_representative', 10, 1);

function process_order_and_update_representative($order_id)
{
    $order = wc_get_order($order_id);

    if ($order && $order->get_status() == 'pending') {
        // Check if the order has a representative ID
        $representative_id = get_post_meta($order_id, '_representative_id', true);

        if ($representative_id) {
            // Update the order status to completed
            $order->update_status('completed');

            // Add additional logic to handle representative earnings, notifications, etc.
        }
    }
}

// Timesheet plugin 

// Add shortcode to display the timesheet form
add_shortcode('timesheet_form', 'render_timesheet_form');

function render_timesheet_form()
{
    ob_start();
    ?>
    <form id="timesheet-form" method="post" action="">
        <p>
            <label for="task_date">Date:</label>
            <input type="date" id="task_date" name="task_date" required>
        </p>
        <p>
            <label for="task_name">Task Name:</label>
            <input type="text" id="task_name" name="task_name" required>
        </p>
        <p>
            <label for="hours_spent">Hours Spent:</label>
            <input type="number" id="hours_spent" name="hours_spent" required>
        </p>
        <p>
            <input type="submit" name="submit_timesheet" value="Submit Timesheet">
        </p>
    </form>
<?php
    handle_timesheet_submission(); // Process the form submission
    return ob_get_clean();
}

// Handle form submission
function handle_timesheet_submission()
{
    if (isset($_POST['submit_timesheet'])) {
        global $wpdb;

        $task_date = sanitize_text_field($_POST['task_date']);
        $task_name = sanitize_text_field($_POST['task_name']);
        $hours_spent = intval($_POST['hours_spent']);

        $table_name = $wpdb->prefix . "timesheets";

        $wpdb->insert($table_name, [
            'task_date' => $task_date,
            'task_name' => $task_name,
            'hours_spent' => $hours_spent,
            'user_id' => get_current_user_id(),
        ]);

        echo "<p>Timesheet submitted successfully!</p>";
    }
}

// Create timesheets table on plugin activation
register_activation_hook(__FILE__, 'create_timesheets_table');

function create_timesheets_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "timesheets";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) NOT NULL,
        task_date date NOT NULL,
        task_name text NOT NULL,
        hours_spent int NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Create timesheets table on plugin activation
register_activation_hook(__FILE__, 'create_timesheets_table');

// Add a custom admin menu
add_action('admin_menu', 'add_timesheets_admin_page');

function add_timesheets_admin_page()
{
    add_menu_page(
        'Timesheets',
        'Timesheets',
        'manage_options',
        'timesheets',
        'render_timesheets_admin_page',
        'dashicons-calendar-alt',
        20
    );
}

// Render the timesheet admin page
function render_timesheets_admin_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "timesheets";

    $timesheets = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<div class="wrap"><h1>Timesheets</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>User</th><th>Date</th><th>Task Name</th><th>Hours Spent</th></tr></thead>';
    echo '<tbody>';

    foreach ($timesheets as $timesheet) {
        $user_info = get_userdata($timesheet->user_id);
        echo '<tr>';
        echo '<td>' . $timesheet->id . '</td>';
        echo '<td>' . ($user_info ? $user_info->user_login : 'Unknown') . '</td>';
        echo '<td>' . $timesheet->task_date . '</td>';
        echo '<td>' . $timesheet->task_name . '</td>';
        echo '<td>' . $timesheet->hours_spent . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
}
add_action('admin_menu', 'add_representative_report_page');

function add_representative_report_page() {
    add_menu_page(
        'Representative Coupon Report', // Page title
        'Rep Coupon Report',           // Menu title
        'manage_options',              // Capability
        'representative_coupon_report', // Menu slug
        'display_representative_coupon_report', // Callback function
        'dashicons-chart-bar',         // Icon
        20                             // Position
    );
}

function display_representative_coupon_report() {
    // Check if the user has the required capability
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;

    // Query WooCommerce orders for representative coupon codes and their usage
    $results = $wpdb->get_results("
        SELECT 
            meta_value AS coupon_code,
            COUNT(post_id) AS usage_count,
            GROUP_CONCAT(post_id) AS order_ids
        FROM {$wpdb->prefix}postmeta pm
        JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID
        WHERE meta_key = '_used_coupons'
          AND p.post_type = 'shop_order'
          AND p.post_status IN ('wc-completed', 'wc-processing') -- Only count completed/processing orders
        GROUP BY meta_value
        ORDER BY usage_count DESC
    ");

    print_r($results);

    // Output the data in a table
    echo '<div class="wrap"><h1>Representative Coupon Report</h1>';

    if ($results) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>
                <tr>
                    <th>Coupon Code</th>
                    <th>Usage Count</th>
                    <th>Orders</th>
                </tr>
              </thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            // Get representative user associated with the coupon
            $representative_user = get_user_by('login', $row->coupon_code);
            $representative_name = $representative_user ? $representative_user->display_name : 'N/A';

            // Link to orders in WooCommerce
            $order_links = array_map(function ($order_id) {
                return '<a href="' . admin_url('post.php?post=' . $order_id . '&action=edit') . '">' . $order_id . '</a>';
            }, explode(',', $row->order_ids));

            echo '<tr>';
            echo '<td>' . esc_html($row->coupon_code) . ' (' . esc_html($representative_name) . ')</td>';
            echo '<td>' . esc_html($row->usage_count) . '</td>';
            echo '<td>' . implode(', ', $order_links) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No data found.</p>';
    }

    echo '</div>';
}

