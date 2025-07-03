<?php
/**
 * Plugin Name: Klump Product Ads
 * Plugin URI: https://biwillz.com
 * Description: Adds Klump payment ad section to WooCommerce product pages after the product title.
 * Version: 3.0.0
 * Author: Flashware
 * License: GPL v2 or later
 * Text Domain: klump-product-ads
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.2
 * WC requires at least: 3.0
 * WC tested up to: 7.8
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KLUMP_PRODUCT_ADS_VERSION', '3.0.0');
define('KLUMP_PRODUCT_ADS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KLUMP_PRODUCT_ADS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include enhanced admin interface
if (is_admin()) {
    require_once KLUMP_PRODUCT_ADS_PLUGIN_DIR . "admin-interface.php";
}

class KlumpProductAds {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_klump_validate_key', array($this, 'validate_merchant_key'));
        add_action('wp_ajax_klump_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_klump_log_modal_error', array($this, 'ajax_log_modal_error'));
        add_action('wp_ajax_klump_validate_youtube_url', array($this, 'validate_youtube_url_ajax'));
        add_action('wp_ajax_nopriv_klump_log_modal_error', array($this, 'ajax_log_modal_error'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
    }
    
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Add the Klump ad div after product title
        add_action('woocommerce_single_product_summary', array($this, 'add_klump_ad_div'), 6);
        
        // Load plugin text domain
        load_plugin_textdomain('klump-product-ads', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    
    public function enqueue_styles() {
        if (is_product()) {
            // Enqueue main styles
            wp_enqueue_style(
                'klump-product-ads-styles',
                KLUMP_PRODUCT_ADS_PLUGIN_URL . 'assets.css',
                array(),
                KLUMP_PRODUCT_ADS_VERSION
            );
            
            // Enqueue modal styles
            wp_enqueue_style(
                'klump-modal-styles',
                KLUMP_PRODUCT_ADS_PLUGIN_URL . 'klump-modal.css',
                array(),
                KLUMP_PRODUCT_ADS_VERSION
            );
            
            // Add dynamic CSS for custom colors
            $custom_colors = array(
                'primary' => get_option('klump_ads_primary_color', '#2e08f4'),
                'secondary' => get_option('klump_ads_secondary_color', '#cf13e4'),
                'background' => get_option('klump_ads_background_color', '#f8f9ff'),
                'text' => get_option('klump_ads_text_color', '#6c757d'),
                'price' => get_option('klump_ads_price_color', '#2e08f4'),
                'border' => get_option('klump_ads_border_color', '#e9ecef')
            );
            
        $custom_css = "
            :root {
                --brand-main: {$custom_colors['primary']};
                --brand-sub: {$custom_colors['secondary']};
                --brand-gradient: linear-gradient(135deg, {$custom_colors['primary']} 30%, {$custom_colors['secondary']} 70%);
                --klump-background: {$custom_colors['background']};
                --klump-text: {$custom_colors['text']};
                --klump-price: {$custom_colors['price']};
                --klump-border: {$custom_colors['border']};
                --klump-bg-start: {$custom_colors['background']};
                --klump-bg-end: {$custom_colors['background']};
                --klump-title: {$custom_colors['primary']};
                --klump-icon-bg: rgba(255, 255, 255, 0.8);
                --klump-icon-hover: {$custom_colors['primary']};
                --klump-shadow: {$custom_colors['primary']};
            }
        ";
            
            wp_add_inline_style('klump-product-ads-styles', $custom_css);
            
            // Enqueue main script
            wp_enqueue_script(
                'klump-product-ads-script',
                KLUMP_PRODUCT_ADS_PLUGIN_URL . 'assets.js',
                array('jquery'),
                KLUMP_PRODUCT_ADS_VERSION,
                true
            );
            
            // Enqueue modal script
            wp_enqueue_script(
                'klump-modal-script',
                KLUMP_PRODUCT_ADS_PLUGIN_URL . 'klump-modal.js',
                array('jquery'),
                KLUMP_PRODUCT_ADS_VERSION,
                true
            );
            
            // Localize script with modal data
            $youtube_url = get_option('klump_ads_youtube_url', '');
            wp_localize_script('klump-modal-script', 'klump_modal_data', array(
                'youtube_url' => $youtube_url,
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('klump_modal_nonce'),
                'debug_mode' => WP_DEBUG || get_option('klump_ads_debug_mode', false)
            ));
        }
    }
    
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_klump-product-ads') {
            return;
        }
        
        // Enqueue SweetAlert2
        wp_enqueue_script(
            'sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
            array(),
            '11.0.0',
            true
        );
        
        wp_enqueue_style(
            'klump-admin-styles',
            KLUMP_PRODUCT_ADS_PLUGIN_URL . 'admin-styles.css',
            array(),
            KLUMP_PRODUCT_ADS_VERSION
        );
        
        wp_enqueue_script(
            'klump-admin-script',
            KLUMP_PRODUCT_ADS_PLUGIN_URL . 'admin-script.js',
            array('jquery', 'sweetalert2'),
            KLUMP_PRODUCT_ADS_VERSION,
            true
        );
        
        wp_localize_script('klump-admin-script', 'klump_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('klump_admin_nonce')
        ));
    }
    
    public function validate_merchant_key() {
        check_ajax_referer('klump_admin_nonce', 'nonce');
        
        $merchant_key = sanitize_text_field($_POST['merchant_key']);
        
        // Basic validation for Klump merchant key format
        $is_valid = $this->is_valid_klump_key($merchant_key);
        
        wp_send_json(array(
            'valid' => $is_valid,
            'message' => $is_valid ? 'Valid Klump merchant key format' : 'Invalid Klump merchant key format. Keys should start with "klp_pk_"'
        ));
    }
    
    public function validate_youtube_url_ajax() {
        check_ajax_referer('klump_admin_nonce', 'nonce');
        
        $youtube_url = sanitize_url($_POST['youtube_url']);
        
        if (!$youtube_url) {
            wp_send_json_error(['message' => 'YouTube URL is required']);
            return;
        }
        
        $validation_result = $this->validate_youtube_url_format($youtube_url);
        
        if ($validation_result['valid']) {
            wp_send_json_success(['message' => 'YouTube URL is valid', 'video_id' => $validation_result['video_id']]);
        } else {
            wp_send_json_error(['message' => $validation_result['message']]);
        }
    }
    
    private function validate_youtube_url_format($url) {
        if (empty($url)) {
            return array('valid' => false, 'message' => 'YouTube URL is required', 'video_id' => '');
        }
        
        $url = trim($url);
        
        // YouTube URL patterns
        $patterns = array(
            '/^https?:\/\/(?:www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/',
            '/^https?:\/\/(?:www\.)?youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
            '/^https?:\/\/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/^https?:\/\/(?:www\.)?youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/'
        );
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return array(
                    'valid' => true, 
                    'message' => 'Valid YouTube URL',
                    'video_id' => $matches[1]
                );
            }
        }
        
        return array(
            'valid' => false,
            'message' => 'Invalid YouTube URL format. Please use a valid YouTube watch, shorts, or embed URL.',
            'video_id' => ''
        );
    }
    
    public function ajax_log_modal_error() {
        check_ajax_referer('klump_modal_nonce', 'nonce');
        
        $error_message = sanitize_text_field($_POST['error_message'] ?? '');
        $error_type = sanitize_text_field($_POST['error_type'] ?? 'general');
        
        if (!empty($error_message)) {
            error_log("Klump Modal Error [{$error_type}]: {$error_message}");
        }
        
        wp_send_json_success(['message' => 'Error logged']);
    }
    
    /**
     * Helper function to convert relative URLs to absolute URLs
     */
    private function make_url_absolute($url) {
        if (empty($url)) {
            return $url;
        }
        
        // If it's already an absolute URL, return as is
        if (strpos($url, 'http') === 0) {
            return $url;
        }
        
        // If it's a relative URL, convert to absolute
        if (strpos($url, '/') === 0) {
            return home_url($url);
        }
        
        return $url;
    }
    
    /**
     * Helper function to render an icon (either as text/emoji or as an image)
     */
    private function render_icon($icon_value, $alt_text = '', $class = '') {
        if (empty($icon_value)) {
            return '';
        }
        
        // Check if it's a URL (contains file extensions or starts with /)
        if (preg_match('/\.(jpg|jpeg|png|gif|svg|webp)$/i', $icon_value) || strpos($icon_value, '/') === 0) {
            // It's an image URL - convert to absolute and render as img tag
            $absolute_url = $this->make_url_absolute($icon_value);
            return '<img src="' . esc_url($absolute_url) . '" alt="' . esc_attr($alt_text) . '" style="width: 20px; height: 20px;" class="' . esc_attr($class) . '">';
        } else {
            // It's text/emoji - render as text
            return esc_html($icon_value);
        }
    }

    private function is_valid_klump_key($key) {
        // Klump keys typically start with 'klp_pk_' for public keys
        return preg_match('/^klp_pk_[a-zA-Z0-9]{20,}$/', $key);
    }

    public function ajax_save_settings() {
        // Check nonce for security
        check_ajax_referer('klump_admin_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            // Save main settings
            $settings_to_save = array(
                'klump_ads_enabled' => isset($_POST['klump_ads_enabled']) ? 'yes' : 'no',
                'klump_ads_merchant_key' => sanitize_text_field($_POST['klump_ads_merchant_key']),
                'klump_ads_price' => floatval($_POST['klump_ads_price']),
                'klump_ads_youtube_url' => esc_url_raw($_POST['klump_ads_youtube_url']),
                'klump_ads_currency' => sanitize_text_field($_POST['klump_ads_currency']),
                'klump_ads_use_product_price' => isset($_POST['klump_ads_use_product_price']) ? 'yes' : 'no',
                'klump_ads_title_text' => sanitize_text_field($_POST['klump_ads_title_text']),
                'klump_ads_description_text' => sanitize_text_field($_POST['klump_ads_description_text']),
                'klump_ads_primary_color' => sanitize_hex_color($_POST['klump_ads_primary_color']),
                'klump_ads_secondary_color' => sanitize_hex_color($_POST['klump_ads_secondary_color']),
                'klump_ads_background_color' => sanitize_hex_color($_POST['klump_ads_background_color']),
                'klump_ads_text_color' => sanitize_hex_color($_POST['klump_ads_text_color']),
                'klump_ads_price_color' => sanitize_hex_color($_POST['klump_ads_price_color']),
                'klump_ads_border_color' => sanitize_hex_color($_POST['klump_ads_border_color']),
            );
            
            foreach ($settings_to_save as $option_name => $value) {
                update_option($option_name, $value);
            }
            
            // Save icon URLs
            for ($i = 1; $i <= 4; $i++) {
                if (isset($_POST['klump_ads_icon_' . $i])) {
                    $icon_url = trim($_POST['klump_ads_icon_' . $i]);
                    if (!empty($icon_url)) {
                        $icon_url = esc_url_raw($icon_url);
                    }
                    update_option('klump_ads_icon_' . $i, $icon_url);
                }
            }
            
            if (isset($_POST['klump_ads_logo'])) {
                $logo_url = trim($_POST['klump_ads_logo']);
                if (!empty($logo_url)) {
                    $logo_url = esc_url_raw($logo_url);
                }
                update_option('klump_ads_logo', $logo_url);
            }
            
            wp_send_json_success(array(
                'message' => 'Settings saved successfully!'
            ));
            
        } catch (Exception $e) {
            error_log('Klump AJAX Save Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'An unexpected error occurred while saving settings.'
            ));
        }
    }
    
    public function add_klump_ad_div() {
        // Check if plugin is enabled
        if (get_option('klump_ads_enabled', 'yes') !== 'yes') {
            return;
        }
        
        // Get the current product
        global $product;
        if (!$product) {
            return;
        }
        
        // Get plugin settings
        $merchant_key = get_option('klump_ads_merchant_key', 'klp_pk_abcde12345fghijkl');
        $use_product_price = get_option('klump_ads_use_product_price', 'yes');
        $fallback_price = get_option('klump_ads_price', '2000');
        $currency = get_option('klump_ads_currency', 'NGN');
        
        // Calculate price to display
        if ($use_product_price === 'yes' && $product->get_price()) {
            $price = $product->get_price();
        } else {
            $price = $fallback_price;
        }
        
        // Get custom settings
        $icon_1 = get_option('klump_ads_icon_1', '💳');
        $icon_2 = get_option('klump_ads_icon_2', '📱');
        $icon_3 = get_option('klump_ads_icon_3', '🏦');
        $icon_4 = get_option('klump_ads_icon_4', '👛');
        $custom_logo = get_option('klump_ads_logo', '');
        $enable_animation = get_option('klump_ads_enable_animation', 'yes');
        $animation_speed = get_option('klump_ads_animation_speed', '4');
        $animation_style = get_option('klump_ads_animation_style', 'corner');
        $title_text = get_option('klump_ads_title_text', 'Pay in installments with Klump');
        $description_text = get_option('klump_ads_description_text', 'Split your payment into flexible installments');
        $youtube_url = get_option('klump_ads_youtube_url', '');
        
        // Build animation classes
        $animation_class = '';
        if ($enable_animation === 'yes') {
            $animation_class = 'klump-animated';
            if ($animation_style === 'smooth') {
                $animation_class .= ' smooth-circle';
            }
        }
        
        // Build modal trigger classes and attributes
        $modal_class = 'klump-product-ad';
        $modal_attributes = '';
        if (!empty($youtube_url)) {
            $modal_class .= ' klump-modal-trigger';
            $modal_attributes = ' data-youtube-url="' . esc_attr($youtube_url) . '"';
        }
        
        echo '<div id="klump__ad" class="' . esc_attr($animation_class . ' ' . $modal_class) . '" data-animation-speed="' . esc_attr($animation_speed) . '"' . $modal_attributes . '>';

        echo '<input type="hidden" value="' . esc_attr($price) . '" id="klump__price">';
        echo '<input type="hidden" value="' . esc_attr($merchant_key) . '" id="klump__merchant__public__key">';
        echo '<input type="hidden" value="' . esc_attr($currency) . '" id="klump__currency">';
        echo '<div class="klump-ad-content">';
        echo '  <div class="klump-text-section">';
        echo '    <div class="klump-title">💳 ' . esc_html($title_text) . '</div>';
        echo '    <div class="klump-description">' . esc_html($description_text) . '</div>';
        echo '    <div class="klump-price-info">As low as <strong>' . esc_html($currency) . ' ' . esc_html(number_format(floatval($price) / 4, 2)) . '</strong> per month</div>';
        echo '  </div>';
        echo '  <div class="klump-logo-section">';
        echo '    <div class="klump-logo">';
        if (!empty($custom_logo)) {
            // Use uploaded custom logo
            echo '      <img src="' . esc_url($custom_logo) . '" alt="Klump" style="max-width: 120px; max-height: 48px;">';
        } else {
            // Use default SVG logo
            echo '      <svg width="60" height="24" viewBox="0 0 120 48" fill="none" xmlns="http://www.w3.org/2000/svg">';
            echo '        <path d="M20 8L40 8C44.4183 8 48 11.5817 48 16V32C48 36.4183 44.4183 40 40 40H20C15.5817 40 12 36.4183 12 32V16C12 11.5817 15.5817 8 20 8Z" fill="url(#klump-gradient)"/>';
            echo '        <path d="M24 20H36V28H24V20Z" fill="white"/>';
            echo '        <text x="52" y="32" font-family="Arial, sans-serif" font-size="20" font-weight="bold" fill="#2e08f4">klump</text>';
            echo '        <defs>';
            echo '          <linearGradient id="klump-gradient" x1="12" y1="8" x2="48" y2="40" gradientUnits="userSpaceOnUse">';
            echo '            <stop stop-color="#2e08f4"/>';
            echo '            <stop offset="1" stop-color="#cf13e4"/>';
            echo '          </linearGradient>';
            echo '        </defs>';
            echo '      </svg>';
        }
        echo '    </div>';
        echo '    <div class="klump-payment-icons">';
        echo '      <div class="payment-icon" data-icon="card">' . $this->render_icon($icon_1, "Card", "payment-icon-img") . '</div>';
        echo '      <div class="payment-icon" data-icon="mobile">' . $this->render_icon($icon_2, "Mobile", "payment-icon-img") . '</div>';
        echo '      <div class="payment-icon" data-icon="bank">' . $this->render_icon($icon_3, "Bank", "payment-icon-img") . '</div>';
        echo '      <div class="payment-icon" data-icon="wallet">' . $this->render_icon($icon_4, "Wallet", "payment-icon-img") . '</div>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';
        echo '</div>';
    }
    
    public function add_admin_menu() {
        add_options_page(
            'Klump Product Ads Settings',
            'Klump Product Ads',
            'manage_options',
            'klump-product-ads',
            array($this, 'admin_page')
        );
    }
    
    public function admin_init() {
        register_setting('klump_ads_settings', 'klump_ads_enabled');
        register_setting('klump_ads_settings', 'klump_ads_price');
        register_setting('klump_ads_settings', 'klump_ads_merchant_key');
        register_setting('klump_ads_settings', 'klump_ads_currency');
        register_setting('klump_ads_settings', 'klump_ads_use_product_price');
        register_setting('klump_ads_settings', 'klump_ads_title_text');
        register_setting('klump_ads_settings', 'klump_ads_description_text');
        register_setting('klump_ads_settings', 'klump_ads_primary_color');
        register_setting('klump_ads_settings', 'klump_ads_secondary_color');
        register_setting('klump_ads_settings', 'klump_ads_background_color');
        register_setting('klump_ads_settings', 'klump_ads_text_color');
        register_setting('klump_ads_settings', 'klump_ads_price_color');
        register_setting('klump_ads_settings', 'klump_ads_border_color');
        register_setting('klump_ads_settings', 'klump_ads_icon_1');
        register_setting('klump_ads_settings', 'klump_ads_icon_2');
        register_setting('klump_ads_settings', 'klump_ads_icon_3');
        register_setting('klump_ads_settings', 'klump_ads_icon_4');
        register_setting('klump_ads_settings', 'klump_ads_enable_animation');
        register_setting('klump_ads_settings', 'klump_ads_animation_speed');
        register_setting('klump_ads_settings', 'klump_ads_animation_style');
        register_setting('klump_ads_settings', 'klump_ads_youtube_url');
        register_setting('klump_ads_settings', 'klump_ads_logo');
    }
    
    public function admin_page() {
        // Use the enhanced admin interface
        if (class_exists("KlumpProductAdsAdmin")) {
            $admin_interface = new KlumpProductAdsAdmin();
            $admin_interface->render_enhanced_admin_page();
        } else {
            echo "<div class=\"wrap\"><h1>Klump Product Ads</h1><p>Error: Enhanced admin interface not found.</p></div>";
        }
    }
    
    private function get_current_settings() {
        return array(
            'enabled' => get_option('klump_ads_enabled', 'yes'),
            'merchant_key' => get_option('klump_ads_merchant_key', 'klp_pk_abcde12345fghijkl'),
            'price' => get_option('klump_ads_price', '2000'),
            'currency' => get_option('klump_ads_currency', 'NGN'),
            'use_product_price' => get_option('klump_ads_use_product_price', 'yes'),
            'title_text' => get_option('klump_ads_title_text', 'Pay in installments with Klump'),
            'description_text' => get_option('klump_ads_description_text', 'Split your payment into flexible installments'),
            'primary_color' => get_option('klump_ads_primary_color', '#2e08f4'),
            'secondary_color' => get_option('klump_ads_secondary_color', '#cf13e4'),
            'background_color' => get_option('klump_ads_background_color', '#f8f9ff'),
            'text_color' => get_option('klump_ads_text_color', '#6c757d'),
            'price_color' => get_option('klump_ads_price_color', '#2e08f4'),
            'border_color' => get_option('klump_ads_border_color', '#e9ecef'),
            'icon_1' => get_option('klump_ads_icon_1', '💳'),
            'icon_2' => get_option('klump_ads_icon_2', '📱'),
            'icon_3' => get_option('klump_ads_icon_3', '🏦'),
            'icon_4' => get_option('klump_ads_icon_4', '👛'),
            'enable_animation' => get_option('klump_ads_enable_animation', 'yes'),
            'animation_speed' => get_option('klump_ads_animation_speed', '4'),
            'animation_style' => get_option('klump_ads_animation_style', 'corner')
        );
    }
    
    private function save_settings() {
        $settings_to_save = array(
            'klump_ads_enabled' => isset($_POST['klump_ads_enabled']) ? 'yes' : 'no',
            'klump_ads_merchant_key' => sanitize_text_field($_POST['klump_ads_merchant_key']),
            'klump_ads_price' => floatval($_POST['klump_ads_price']),
            'klump_ads_currency' => sanitize_text_field($_POST['klump_ads_currency']),
            'klump_ads_use_product_price' => isset($_POST['klump_ads_use_product_price']) ? 'yes' : 'no',
            'klump_ads_title_text' => sanitize_text_field($_POST['klump_ads_title_text']),
            'klump_ads_description_text' => sanitize_text_field($_POST['klump_ads_description_text']),
            'klump_ads_primary_color' => sanitize_hex_color($_POST['klump_ads_primary_color']),
            'klump_ads_secondary_color' => sanitize_hex_color($_POST['klump_ads_secondary_color']),
            'klump_ads_background_color' => sanitize_hex_color($_POST['klump_ads_background_color']),
            'klump_ads_text_color' => sanitize_hex_color($_POST['klump_ads_text_color']),
            'klump_ads_price_color' => sanitize_hex_color($_POST['klump_ads_price_color']),
            'klump_ads_border_color' => sanitize_hex_color($_POST['klump_ads_border_color']),
            'klump_ads_icon_1' => sanitize_text_field($_POST['klump_ads_icon_1']),
            'klump_ads_icon_2' => sanitize_text_field($_POST['klump_ads_icon_2']),
            'klump_ads_icon_3' => sanitize_text_field($_POST['klump_ads_icon_3']),
            'klump_ads_icon_4' => sanitize_text_field($_POST['klump_ads_icon_4']),
            'klump_ads_enable_animation' => isset($_POST['klump_ads_enable_animation']) ? 'yes' : 'no',
            'klump_ads_animation_speed' => floatval($_POST['klump_ads_animation_speed']),
            'klump_ads_animation_style' => sanitize_text_field($_POST['klump_ads_animation_style'])
        );
        
        foreach ($settings_to_save as $option_name => $value) {
            update_option($option_name, $value);
        }
    }
    
    private function get_status_indicator($settings) {
        $is_enabled = $settings['enabled'] === 'yes';
        $has_valid_key = $this->is_valid_klump_key($settings['merchant_key']);
        
        if ($is_enabled && $has_valid_key) {
            return '<div class="klump-status-indicator status-active"><span class="dashicons dashicons-yes-alt"></span> Active</div>';
        } elseif ($is_enabled && !$has_valid_key) {
            return '<div class="klump-status-indicator status-warning"><span class="dashicons dashicons-warning"></span> Needs Configuration</div>';
        } else {
            return '<div class="klump-status-indicator status-inactive"><span class="dashicons dashicons-dismiss"></span> Inactive</div>';
        }
    }
    
    public function woocommerce_missing_notice() {
        ?>
        <div class="error notice">
            <p><?php _e('Klump Product Ads requires WooCommerce to be installed and active.', 'klump-product-ads'); ?></p>
        </div>
        <?php
    }
    
    public function activate() {
        // Set default options
        add_option('klump_ads_enabled', 'yes');
        add_option('klump_ads_price', '2000');
        add_option('klump_ads_merchant_key', 'klp_pk_abcde12345fghijkl');
        add_option('klump_ads_currency', 'NGN');
        add_option('klump_ads_use_product_price', 'yes');
        add_option('klump_ads_title_text', 'Pay in installments with Klump');
        add_option('klump_ads_description_text', 'Split your payment into flexible installments');
        add_option('klump_ads_primary_color', '#2e08f4');
        add_option('klump_ads_secondary_color', '#cf13e4');
        add_option('klump_ads_background_color', '#f8f9ff');
        add_option('klump_ads_text_color', '#6c757d');
        add_option('klump_ads_price_color', '#2e08f4');
        add_option('klump_ads_border_color', '#e9ecef');
        add_option('klump_ads_icon_1', '💳');
        add_option('klump_ads_icon_2', '📱');
        add_option('klump_ads_icon_3', '🏦');
        add_option('klump_ads_icon_4', '👛');
        add_option('klump_ads_enable_animation', 'yes');
        add_option('klump_ads_animation_speed', '4');
        add_option('klump_ads_animation_style', 'corner');
        add_option('klump_ads_youtube_url', '');
        add_option('klump_ads_logo', '');
    }
    
    public function deactivate() {
        // Optional: Clean up options on deactivation
        // delete_option('klump_ads_enabled');
        // delete_option('klump_ads_price');
        // delete_option('klump_ads_merchant_key');
        // delete_option('klump_ads_currency');
        // delete_option('klump_ads_use_product_price');
        // delete_option('klump_ads_title_text');
        // delete_option('klump_ads_description_text');
        // delete_option('klump_ads_primary_color');
        // delete_option('klump_ads_secondary_color');
        // delete_option('klump_ads_background_color');
        // delete_option('klump_ads_text_color');
        // delete_option('klump_ads_price_color');
        // delete_option('klump_ads_border_color');
    }
}

// Initialize the plugin
new KlumpProductAds();
