<?php
/**
 * Uninstall script for Klump Product Ads
 * 
 * This file is executed when the plugin is deleted through the WordPress admin.
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('klump_ads_enabled');
delete_option('klump_ads_price');
delete_option('klump_ads_merchant_key');
delete_option('klump_ads_currency');
delete_option('klump_ads_use_product_price');
delete_option('klump_ads_title_text');
delete_option('klump_ads_description_text');

// For multisite installations
if (is_multisite()) {
    global $wpdb;
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    $original_blog_id = get_current_blog_id();
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        delete_option('klump_ads_enabled');
        delete_option('klump_ads_price');
        delete_option('klump_ads_merchant_key');
        delete_option('klump_ads_currency');
        delete_option('klump_ads_use_product_price');
        delete_option('klump_ads_title_text');
        delete_option('klump_ads_description_text');
    }
    
    switch_to_blog($original_blog_id);
}
