<?php
/**
 * Enhanced Admin Interface for Klump Product Ads with YouTube Modal Support
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class KlumpProductAdsAdmin {
    
    public function __construct() {
        // Constructor - admin assets handled by main plugin
    }
    
    
    public function validate_merchant_key() {
        check_ajax_referer('klump_admin_nonce', 'nonce');
        
        $merchant_key = sanitize_text_field($_POST['merchant_key']);
        
        // Basic format validation
        if (!preg_match('/^klp_pk_[a-zA-Z0-9]{20,}$/', $merchant_key)) {
            wp_send_json(array(
                'valid' => false,
                'message' => 'Invalid key format. Klump keys should start with "klp_pk_"'
            ));
        }
        
        // Here you would typically make an API call to Klump to validate the key
        // For now, we'll just validate the format
        wp_send_json(array(
            'valid' => true,
            'message' => 'Key format is valid. Please test with actual transactions.'
        ));
    }
    
    public function ajax_save_settings() {
        check_ajax_referer('klump_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        $this->save_settings();
        wp_send_json_success(array('message' => 'Settings saved successfully!'));
    }
    
    public function render_enhanced_admin_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['klump_nonce'], 'klump_settings')) {
            $this->save_settings();
        }
        
        $settings = $this->get_current_settings();
        ?>
        <div class="wrap klump-admin-wrap">
            <div class="klump-header">
                <div class="klump-logo">
                    <h1><span class="klump-icon">💳</span> Klump Product Ads</h1>
                    <p class="klump-subtitle">Configure your Klump payment integration settings</p>
                </div>
                <div class="klump-status">
                    <?php echo $this->get_status_indicator($settings); ?>
                </div>
            </div>
            
            <?php 
            $save_status = get_transient('klump_save_status');
            $save_errors = get_transient('klump_save_errors');
            if ($save_status): 
                delete_transient('klump_save_status');
                delete_transient('klump_save_errors');
            ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    <?php if ($save_status === 'success'): ?>
                        Swal.fire({
                            icon: 'success',
                            title: 'Settings Saved!',
                            text: 'Your Klump configuration has been updated successfully.',
                            confirmButtonColor: '#2e08f4',
                            timer: 3000,
                            timerProgressBar: true
                        });
                    <?php elseif ($save_status === 'error'): ?>
                        Swal.fire({
                            icon: 'error',
                            title: 'Save Failed',
                            html: '<div><p>There were errors saving your settings:</p><ul><?php if ($save_errors) foreach ($save_errors as $error) echo '<li>' . esc_html($error) . '</li>'; ?></ul></div>',
                            confirmButtonColor: '#ef4444'
                        });
                    <?php endif; ?>
                });
                </script>
            <?php endif; ?>
            
            <div class="klump-main-content">
                <div class="klump-settings-section">
                    <form method="post" action="" class="klump-form">
                        <?php wp_nonce_field('klump_settings', 'klump_nonce'); ?>
                        
                        <!-- Enable/Disable Section -->
                        <div class="klump-card">
                            <div class="klump-card-header">
                                <h3><span class="dashicons dashicons-admin-settings"></span> General Settings</h3>
                            </div>
                            <div class="klump-card-body">
                                <div class="klump-field-group">
                                    <label class="klump-toggle">
                                        <input type="checkbox" name="klump_ads_enabled" value="yes" <?php checked($settings['enabled'], 'yes'); ?>>
                                        <span class="klump-toggle-slider"></span>
                                        <span class="klump-toggle-label">Enable Klump Product Ads</span>
                                    </label>
                                    <p class="klump-field-description">Toggle to enable or disable the Klump payment ads on your product pages.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- API Configuration Section -->
                        <div class="klump-card">
                            <div class="klump-card-header">
                                <h3><span class="dashicons dashicons-admin-network"></span> API Configuration</h3>
                            </div>
                            <div class="klump-card-body">
                                <div class="klump-field-group">
                                    <label for="klump_merchant_key" class="klump-field-label">
                                        Klump Merchant Public Key <span class="required">*</span>
                                    </label>
                                    <div class="klump-key-input-wrapper">
                                        <input type="text" 
                                               id="klump_merchant_key" 
                                               name="klump_ads_merchant_key" 
                                               value="<?php echo esc_attr($settings['merchant_key']); ?>" 
                                               class="klump-key-input" 
                                               placeholder="klp_pk_your_public_key_here"
                                               autocomplete="off">
                                        <button type="button" id="validate_key_btn" class="klump-validate-btn">
                                            <span class="dashicons dashicons-yes-alt"></span> Validate
                                        </button>
                                    </div>
                                    <div id="key_validation_result" class="klump-validation-result"></div>
                                    <p class="klump-field-description">
                                        Enter your Klump merchant public key. You can find this in your Klump dashboard under API settings.
                                        <a href="https://klump.co/dashboard" target="_blank">Get your key here →</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- YouTube Video Modal Section -->
                        <div class="klump-card">
                            <div class="klump-card-header">
                                <h3><span class="dashicons dashicons-video-alt3"></span> YouTube Video Modal</h3>
                            </div>
                            <div class="klump-card-body">
                                <div class="klump-field-group">
                                    <label for="klump_youtube_url" class="klump-field-label">
                                        YouTube Video URL <span class="optional">(Optional)</span>
                                    </label>
                                    <div class="klump-url-input-wrapper">
                                        <input type="url" 
                                               id="klump_youtube_url" 
                                               name="klump_ads_youtube_url" 
                                               value="<?php echo esc_attr($settings['youtube_url']); ?>" 
                                               class="klump-url-input" 
                                               placeholder="https://youtube.com/watch?v=... or https://youtube.com/shorts/..."
                                               autocomplete="off">
                                        <button type="button" id="validate_youtube_btn" class="klump-validate-btn">
                                            <span class="dashicons dashicons-yes-alt"></span> Validate
                                        </button>
                                    </div>
                                    <div id="youtube_validation_result" class="klump-validation-result"></div>
                                    <p class="klump-field-description">
                                        Enter a YouTube video URL to show in a modal when users click on the Klump ad. 
                                        Supports standard YouTube videos, Shorts, and youtu.be links.
                                        <br><strong>Example:</strong> https://youtube.com/shorts/1Hn8li_8J58
                                    </p>
                                    <?php if (!empty($settings['youtube_url'])): ?>
                                        <div class="klump-youtube-preview">
                                            <p><strong>Current Video Preview:</strong></p>
                                            <iframe width="300" height="169" 
                                                    src="<?php echo esc_url($settings['youtube_url']); ?>" 
                                                    frameborder="0" 
                                                    allowfullscreen>
                                            </iframe>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pricing Section -->
                        <div class="klump-card">
                            <div class="klump-card-header">
                                <h3><span class="dashicons dashicons-money-alt"></span> Pricing Configuration</h3>
                            </div>
                            <div class="klump-card-body">
                                <div class="klump-field-row">
                                    <div class="klump-field-group">
                                        <label for="klump_currency" class="klump-field-label">Currency</label>
                                        <select id="klump_currency" name="klump_ads_currency" class="klump-select">
                                            <option value="NGN" <?php selected($settings['currency'], 'NGN'); ?>>🇳🇬 Nigerian Naira (NGN)</option>
                                            <option value="USD" <?php selected($settings['currency'], 'USD'); ?>>🇺🇸 US Dollar (USD)</option>
                                            <option value="EUR" <?php selected($settings['currency'], 'EUR'); ?>>🇪🇺 Euro (EUR)</option>
                                            <option value="GBP" <?php selected($settings['currency'], 'GBP'); ?>>🇬🇧 British Pound (GBP)</option>
                                        </select>
                                    </div>
                                    <div class="klump-field-group">
                                        <label for="klump_default_price" class="klump-field-label">Default Price</label>
                                        <input type="number" 
                                               id="klump_default_price" 
                                               name="klump_ads_price" 
                                               value="<?php echo esc_attr($settings['price']); ?>" 
                                               class="klump-number-input" 
                                               min="1" 
                                               step="0.01">
                                    </div>
                                </div>
                                <div class="klump-field-group">
                                    <label class="klump-checkbox">
                                        <input type="checkbox" name="klump_ads_use_product_price" value="yes" <?php checked($settings['use_product_price'], 'yes'); ?>>
                                        <span class="klump-checkbox-mark"></span>
                                        <span class="klump-checkbox-label">Use actual product prices instead of default price</span>
                                    </label>
                                    <p class="klump-field-description">When enabled, the plugin will use the actual WooCommerce product price. Otherwise, it will use the default price above.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Text Configuration -->
                        <div class="klump-card">
                            <div class="klump-card-header">
                                <h3><span class="dashicons dashicons-edit-large"></span> Text Configuration</h3>
                            </div>
                            <div class="klump-card-body">
                                <div class="klump-field-group">
                                    <label for="klump_title_text" class="klump-field-label">Title Text</label>
                                    <input type="text" 
                                           id="klump_title_text" 
                                           name="klump_ads_title_text" 
                                           value="<?php echo esc_attr($settings['title_text']); ?>" 
                                           class="klump-text-input" 
                                           placeholder="Pay in installments with Klump">
                                </div>
                                <div class="klump-field-group">
                                    <label for="klump_description_text" class="klump-field-label">Description Text</label>
                                    <textarea id="klump_description_text" 
                                              name="klump_ads_description_text" 
                                              class="klump-textarea" 
                                              rows="3" 
                                              placeholder="Split your payment into flexible installments"><?php echo esc_textarea($settings['description_text']); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Style Configuration -->
                        <div class="klump-card">
                            <div class="klump-card-header">
                                <h3><span class="dashicons dashicons-art"></span> Style Configuration</h3>
                            </div>
                            <div class="klump-card-body">
                                <div class="klump-color-presets">
                                    <label class="klump-field-label">Color Presets</label>
                                    <div class="klump-preset-buttons">
                                        <button type="button" class="klump-preset-btn" data-preset="default">
                                            <span class="preset-colors">
                                                <span style="background: #2e08f4;"></span>
                                                <span style="background: #cf13e4;"></span>
                                            </span>
                                            Default
                                        </button>
                                        <button type="button" class="klump-preset-btn" data-preset="purple">
                                            <span class="preset-colors">
                                                <span style="background: #8b5cf6;"></span>
                                                <span style="background: #a855f7;"></span>
                                            </span>
                                            Purple
                                        </button>
                                        <button type="button" class="klump-preset-btn" data-preset="blue">
                                            <span class="preset-colors">
                                                <span style="background: #3b82f6;"></span>
                                                <span style="background: #1d4ed8;"></span>
                                            </span>
                                            Blue
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="klump-color-grid">
                                    <div class="klump-field-group">
                                        <label for="klump_primary_color" class="klump-field-label">Primary Color</label>
                                        <input type="text" 
                                               id="klump_primary_color" 
                                               name="klump_ads_primary_color" 
                                               value="<?php echo esc_attr($settings['primary_color']); ?>" 
                                               class="klump-color-input">
                                    </div>
                                    <div class="klump-field-group">
                                        <label for="klump_secondary_color" class="klump-field-label">Secondary Color</label>
                                        <input type="text" 
                                               id="klump_secondary_color" 
                                               name="klump_ads_secondary_color" 
                                               value="<?php echo esc_attr($settings['secondary_color']); ?>" 
                                               class="klump-color-input">
                                    </div>
                                    <div class="klump-field-group">
                                        <label for="klump_background_color" class="klump-field-label">Background Color</label>
                                        <input type="text" 
                                               id="klump_background_color" 
                                               name="klump_ads_background_color" 
                                               value="<?php echo esc_attr($settings['background_color']); ?>" 
                                               class="klump-color-input">
                                    </div>
                                    <div class="klump-field-group">
                                        <label for="klump_text_color" class="klump-field-label">Text Color</label>
                                        <input type="text" 
                                               id="klump_text_color" 
                                               name="klump_ads_text_color" 
                                               value="<?php echo esc_attr($settings['text_color']); ?>" 
                                               class="klump-color-input">
                                    </div>
                                    <div class="klump-field-group">
                                        <label for="klump_price_color" class="klump-field-label">Price Color</label>
                                        <input type="text" 
                                               id="klump_price_color" 
                                               name="klump_ads_price_color" 
                                               value="<?php echo esc_attr($settings['price_color']); ?>" 
                                               class="klump-color-input">
                                    </div>
                                    <div class="klump-field-group">
                                        <label for="klump_border_color" class="klump-field-label">Border Color</label>
                                        <input type="text" 
                                               id="klump_border_color" 
                                               name="klump_ads_border_color" 
                                               value="<?php echo esc_attr($settings['border_color']); ?>" 
                                               class="klump-color-input">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="klump-submit-section">
                            <button type="submit" name="submit" class="klump-submit-btn">
                                <span class="dashicons dashicons-yes-alt"></span>
                                Save Settings
                            </button>
                            <p class="klump-submit-description">Save your configuration to update the Klump payment ads display.</p>
                        </div>
                    </form>
                </div>
                
                <!-- Preview Section -->
                <div class="klump-preview-section">
                    <div class="klump-card">
                        <div class="klump-card-header">
                            <h3><span class="dashicons dashicons-visibility"></span> Live Preview</h3>
                        </div>
                        <div class="klump-card-body">
                            <div id="klump_preview" class="klump-ad-preview">
                                <!-- Preview will be generated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function get_current_settings() {
        $icons = [];
        for ($i = 1; $i <= 4; $i++) {
            $icons['icon_' . $i] = get_option('klump_ads_icon_' . $i, '');
        }
        return array_merge(
            array(
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
                'logo' => get_option('klump_ads_logo', ''),
                'youtube_url' => get_option('klump_ads_youtube_url', ''),
            ),
            $icons
        );
    }
    
    private function save_settings() {
        try {
            // Log the save attempt
            error_log('Klump Product Ads: Starting settings save process');
            error_log('Klump Product Ads: POST data: ' . print_r($_POST, true));
            
            $success_count = 0;
            $error_count = 0;
            $errors = array();
            
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
                'klump_ads_youtube_url' => $this->sanitize_youtube_url($_POST['klump_ads_youtube_url'] ?? '')
            );
            
            // Save main settings
            foreach ($settings_to_save as $option_name => $value) {
                $result = update_option($option_name, $value);
                if ($result !== false) {
                    $success_count++;
                    error_log("Klump Product Ads: Successfully saved {$option_name} = {$value}");
                } else {
                    $error_count++;
                    $errors[] = "Failed to save {$option_name}";
                    error_log("Klump Product Ads: Failed to save {$option_name}");
                }
            }
            
            // Save icon URLs
            for ($i = 1; $i <= 4; $i++) {
                if (isset($_POST['klump_ads_icon_' . $i])) {
                    $icon_url = esc_url_raw($_POST['klump_ads_icon_' . $i]);
                    $result = update_option('klump_ads_icon_' . $i, $icon_url);
                    if ($result !== false) {
                        $success_count++;
                        error_log("Klump Product Ads: Successfully saved icon_{$i} = {$icon_url}");
                    } else {
                        $error_count++;
                        $errors[] = "Failed to save icon_{$i}";
                        error_log("Klump Product Ads: Failed to save icon_{$i}");
                    }
                }
            }
            
            // Save logo URL
            if (isset($_POST['klump_ads_logo'])) {
                $logo_url = esc_url_raw($_POST['klump_ads_logo']);
                $result = update_option('klump_ads_logo', $logo_url);
                if ($result !== false) {
                    $success_count++;
                    error_log("Klump Product Ads: Successfully saved logo = {$logo_url}");
                } else {
                    $error_count++;
                    $errors[] = "Failed to save logo";
                    error_log("Klump Product Ads: Failed to save logo");
                }
            }
            
            error_log("Klump Product Ads: Save completed. Success: {$success_count}, Errors: {$error_count}");
            
            if ($error_count > 0) {
                set_transient('klump_save_status', 'error', 30);
                set_transient('klump_save_errors', $errors, 30);
            } else {
                set_transient('klump_save_status', 'success', 30);
            }
            
        } catch (Exception $e) {
            error_log('Klump Product Ads: Exception during save: ' . $e->getMessage());
            set_transient('klump_save_status', 'error', 30);
            set_transient('klump_save_errors', array('Unexpected error: ' . $e->getMessage()), 30);
        }
    }
    
    private function get_status_indicator($settings) {
        if ($settings['enabled'] === 'yes' && !empty($settings['merchant_key']) && $settings['merchant_key'] !== 'klp_pk_abcde12345fghijkl') {
            return '<div class="klump-status-indicator status-active"><span class="dashicons dashicons-yes-alt"></span> Active</div>';
        } elseif ($settings['enabled'] === 'yes') {
            return '<div class="klump-status-indicator status-warning"><span class="dashicons dashicons-warning"></span> Needs Configuration</div>';
        } else {
            return '<div class="klump-status-indicator status-inactive"><span class="dashicons dashicons-dismiss"></span> Inactive</div>';
        }
    }
    
    /**
     * YouTube URL validation and handling functions
     */
    private function validate_youtube_url($url) {
        if (empty($url)) {
            return array('valid' => true, 'message' => '', 'video_id' => '');
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
            'message' => 'Invalid YouTube URL format. Please use a valid YouTube video or Shorts URL.',
            'video_id' => ''
        );
    }

    private function sanitize_youtube_url($url) {
        if (empty($url)) {
            return '';
        }
        
        $validation = $this->validate_youtube_url($url);
        if (!$validation['valid']) {
            error_log('Klump Product Ads: Invalid YouTube URL: ' . $url);
            return '';
        }
        
        // Convert to embed format for consistency
        $embed_url = 'https://www.youtube.com/embed/' . $validation['video_id'];
        error_log('Klump Product Ads: YouTube URL sanitized from ' . $url . ' to ' . $embed_url);
        return $embed_url;
    }

    private function get_youtube_video_id($url) {
        $validation = $this->validate_youtube_url($url);
        return $validation['video_id'];
    }
}

// Initialize admin interface
new KlumpProductAdsAdmin();

// Instantiate admin interface
if (is_admin()) {
    new KlumpProductAdsAdmin();
}
