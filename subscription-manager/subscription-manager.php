<?php

/**
 * @wordpress-plugin
 * Plugin Name: Subscription Manager
 * Plugin URI:
 * Description: Manage subscriptions for new users and cron jobs
 * Version:     0.0.1
 * Author:      Muzammal Rasool
 * Author URI:  #
 * Text Domain: mu-sub-manager
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires Plugins: woocommerce-subscriptions
 */

// Block direct access to file
defined( 'ABSPATH' ) or die( 'Not Authorized!' );
//check if woocommerce-subscriptions is active or not

function muz_check_woo_subscription_hook() {
    if( !is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ){
        function muz__is_subscription_plugin_active() {
            ?>
                <div class="error notice">
                    <p><?php esc_html_e( 'Subscriptions Manager is inactive. WooCommerce Subscriptions plugin must be active for Subscriptions Manager to work. Please install & activate WooCommerce Subscriptions »'); ?></p>
                </div>
                <?php
            }
            add_action( 'admin_notices', 'muz__is_subscription_plugin_active' );
            deactivate_plugins(plugin_basename(__FILE__));
            unset($_GET['activate']);
            return;
    }
}
register_activation_hook( __FILE__, 'muz_check_woo_subscription_hook' );

// assign subscription product to new user if enabled from backend
if( get_option('muz_sub_add_pro') ){
    add_action('user_register', 'muz_auto_create_subscription_for_new_user',99);
}
function muz_auto_create_subscription_for_new_user( $user_id ) {

    if( function_exists( 'wcs_create_subscription' ) ){
        $product_id = get_option('muz_sub_add_pro_id'); //subscription product ID
        $product = wc_get_product($product_id); //get product
        if (!$product || !$product->is_type('subscription')) {
            return;
        }
        //create order
        $order = wc_create_order( array( 
            'customer_id' => $user_id ,
            'created_via' => 'subscription manager plugin',
        ) );
        if( is_wp_error( $order ) ){
            return false;
        }
        //add product to order
        $order->add_product($product , 1);
        $order->calculate_totals(  );
        $note="";
        $order->update_status( 'completed', $note, true );
        $order->save();
        //get date
        $now   = gmdate( 'Y-m-d H:i:s' );
        $args=array(
                'status'             => apply_filters( 'woocommerce_default_subscription_status', 'active' ),
                'order_id'           => $order->get_id(),
                'billing_period'     => WC_Subscriptions_Product::get_period( $product ),
                'billing_interval'   => WC_Subscriptions_Product::get_interval( $product ) ,
                'customer_note'      => '',
                'customer_id'        => $user_id,
                'start_date'         => $now,
                'date_created'       => $now,
                'created_via'        => '',
                'currency'           => get_woocommerce_currency(),
                'prices_include_tax' => get_option( 'woocommerce_prices_include_tax' )
        );
        // create subscription
        $subscription=wcs_create_subscription( $args ) ;
        // add product to subscription
        $subscription->add_product( $product, 1 );
        //calculate totals
        $subscription->calculate_totals();
        //save subscriptions
        $subscription->save();
    }
}

// remove notification action from subscription renewal reminder
if( get_option('muz_sub_reminder') ){
    add_action('plugins_loaded','muz_remove_reminder_notfication', 11); 
}
function muz_remove_reminder_notfication(){
    if (
        class_exists('SPRRSendNotifications') &&
        isset(SPRRSendNotifications::$instance)
    ) {        
        $instance = SPRRSendNotifications::$instance;
        if (method_exists($instance, 'sprr_send_subscriber_notification_emaill')) {
            if( !remove_action('renewal_reminders', [$instance, 'sprr_send_subscriber_notification_emaill'])){
                error_log(' Could not remove plugin’s renewal_reminders callback.');
            }
        }else{
            error_log(' Could not get sprr_send_subscriber_notification_emaill function to remove renewal_reminders callback.');
        }
    }
}


add_action('admin_enqueue_scripts', 'muz_my_enqueue');
function muz_add_sub_setting_page()
{
    add_menu_page(
        'Subscription Manager Settings',     // page title
        'Subscription Manager Settings',     // menu title
        'manage_options',   // capability
        'subscription-manager-settings',     // menu slug
        'muz_add_sub_setting_page_render' // callback function
    );
}
function muz_add_sub_setting_page_render()
{
    global $title;

    print '<div class="wrap">';
    print "<h1>$title</h1>";

    $file = plugin_dir_path( __FILE__ ) . "/includes/settings-page.php";

    if ( file_exists( $file ) )
        require $file;
}
add_action( 'admin_menu', 'muz_add_sub_setting_page',99 );
function muz_my_enqueue($hook) {
    wp_enqueue_script('my_custom_script', plugin_dir_url(__FILE__) . '/assets/myscript.js');
}
// --------------
// handle form from admin page
add_action('admin_init', 'myplugin_handle_form');

function myplugin_handle_form() {
    if (
        isset($_POST['myplugin_form_nonce']) &&
        wp_verify_nonce($_POST['myplugin_form_nonce'], 'myplugin_form_action')
    ) {
        if (current_user_can('manage_options')) {
            $muz_sub_reminder = sanitize_text_field($_POST['muz_sub_reminder']);
            $muz_sub_add_pro = sanitize_text_field($_POST['muz_sub_add_pro']);
            $muz_sub_add_pro_id = sanitize_text_field($_POST['muz_sub_add_pro_id']);
           
            // Do something with the value (e.g., save to database)
            update_option('muz_sub_reminder', $muz_sub_reminder);
            update_option('muz_sub_add_pro', $muz_sub_add_pro);
            if( $muz_sub_add_pro_id ){
                update_option('muz_sub_add_pro_id', $muz_sub_add_pro_id);
            }
                

            // Optional: redirect to avoid resubmission
            wp_redirect(admin_url('admin.php?page=subscription-manager-settings&updated=true'));
            exit;
        }
    }
}