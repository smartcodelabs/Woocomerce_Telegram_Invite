<?php
/*
Plugin Name: Telegram Invite Generator
Description: A plugin to sell automatic Telegram invites.
Version: 1.0
Author: SmartCodeLabs
Author URI: https://smartcodelabs.de
License: GPL2+
Text Domain: SmartCodeLabs
Requires at least: 6.5
Requires PHP: 7.0
*/

// Ensure WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    // Include necessary files
    if (file_exists(plugin_dir_path(__FILE__) . 'includes/telegram-functions.php')) {
        require_once plugin_dir_path(__FILE__) . 'includes/telegram-functions.php';
    }

    if (file_exists(plugin_dir_path(__FILE__) . 'includes/teleapi.php')) {
        require_once plugin_dir_path(__FILE__) . 'includes/teleapi.php';
    }
    if (file_exists(plugin_dir_path(__FILE__) . 'includes/dbhandler.php')) {
        require_once plugin_dir_path(__FILE__) . 'includes/dbhandler.php';
    }
    if (file_exists(plugin_dir_path(__FILE__) . 'includes/mailgen.php')) {
        require_once plugin_dir_path(__FILE__) . 'includes/mailgen.php';
    }
    if (file_exists(plugin_dir_path(__FILE__) . 'includes/remove_expired_users.php')) {
        require_once plugin_dir_path(__FILE__) . 'includes/remove_expired_users.php';
    }

    // Register the settings page
    require_once plugin_dir_path(__FILE__) . 'admin/settings-page.php';

    // Enqueue admin scripts
    add_action('admin_enqueue_scripts', 'tig_enqueue_admin_scripts');

    function tig_enqueue_admin_scripts()
    {
        wp_enqueue_script('tig-admin-scripts', plugin_dir_url(__FILE__) . 'admin/admin-scripts.js', array('jquery'), '1.0', true);
        wp_localize_script('tig-admin-scripts', 'tig_ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tig_nonce')
        ));
    }

    //woocommerce_payment_complete_order_status_completed   woocommerce_thankyou  woocommerce_order_status_completed
    // Add functionality to generate the invite link after a successful order
    add_action('woocommerce_order_status_completed', 'tig_generate_telegram_invite_link', 10, 1);
}


function tig_generate_unique_code()
{
    return bin2hex(random_bytes(16)); 
}

// Save the unique code in options during plugin activation
function tig_activate_plugin()
{
    // Check if the code already exists
    $existing_code = get_option('tig_unique_code');

    if (!$existing_code) {
        $unique_code = tig_generate_unique_code();

        update_option('tig_unique_code', $unique_code);
        if (tig_install()) {
            // Installation erfolgreich
            echo "Installation erfolgreich!";
        } else {
            // Plugin deaktivieren
            deactivate_plugins(plugin_basename(__FILE__));
            
            // Fehlermeldung anzeigen
            wp_die(
                __('Die Installation des Telegram Invite Generators ist fehlgeschlagen. Das Plugin wurde deaktiviert.', 'telegram-invite-plugin'),
                __('Plugin-Installationsfehler', 'telegram-invite-plugin'),
                ['back_link' => true]
            );
        }
        
    }
}

// Hook into plugin activation
register_activation_hook(__FILE__, 'tig_activate_plugin');

function tig_get_unique_code()
{
    return get_option('tig_unique_code');
}

// Schedule the remove expired users cron event every 60 minutes
function tig_schedule_remove_expired_users() {
    if (!wp_next_scheduled('tig_60min_event')) {
        wp_schedule_event(time(), 'hourly', 'tig_60min_event');
    }
}
add_action('wp', 'tig_schedule_remove_expired_users');



// Remove the scheduled event on plugin deactivation
function tig_deactivate_plugin() {
    $timestamp = wp_next_scheduled('tig_60min_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'tig_60min_event');
    }
}
register_deactivation_hook(__FILE__, 'tig_deactivate_plugin');

removeExpiredSubscriptions();