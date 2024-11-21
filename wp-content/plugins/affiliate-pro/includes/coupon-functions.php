<?php
// Create a WooCommerce coupon for the representative
function create_representative_coupon($coupon_code, $user_id) {
    if (!class_exists('WC_Coupon')) {
        return; // WooCommerce not active
    }

    $coupon = new WC_Coupon();
    $coupon->set_code($coupon_code);
    $coupon->set_discount_type('fixed_cart'); // Discount type: 'percent' or 'fixed_cart'
    $coupon->set_amount(10); // Discount amount
    $coupon->set_individual_use(true);
    // $coupon->set_email_restrictions([get_userdata($user_id)->user_email]);
    // $coupon->set_usage_limit(1); // Limit coupon usage
    $coupon->set_date_expires(date('Y-m-d', strtotime('+1 year'))); // Expiry date
    $coupon->update_meta_data('_representative_id', $user_id);
    $coupon->save();
    update_user_meta($user_id, 'representative_coupon_code', $coupon_code);
}
