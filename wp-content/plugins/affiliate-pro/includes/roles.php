<?php

// Add the "Representative" role
function add_representative_role() {
    add_role('representative', 'Representative', [
        'read' => true,
        'edit_posts' => false,
        'delete_posts' => false,
    ]);
}

// Remove the "Representative" role
function remove_representative_role() {
    remove_role('representative');
}

// Make sure to add the representative role on plugin activation
register_activation_hook(__FILE__, 'add_representative_role');

// And remove it when the plugin is deactivated
register_deactivation_hook(__FILE__, 'remove_representative_role');
