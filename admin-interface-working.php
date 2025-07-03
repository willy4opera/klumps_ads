<?php
/**
 * Enhanced Admin Interface for Klump Product Ads
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
        
        // Basic validation for Klump merchant key format
        $is_valid = $this->is_valid_klump_key($merchant_key);
        
        wp_send_json(array(
            'valid' => $is_valid,
            'message' => $is_valid ? 'Valid Klump merchant key format' : 'Invalid Klump merchant key format'
        ));
    }
    
    private function is_valid_klump_key($key) {
        // Klump keys typically start with 'klp_pk_' for public keys
        return preg_match('/^klp_pk_[a-zA-Z0-9]{20,}$/', $key);
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
                                        <span class="klump-toggle-label">Enable Klump Ads on Product Pages</span>
                                    </label>
                                    <p class="klump-field-description">Turn this on to display Klump payment options on all WooCommerce product pages.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Merchant Key Section -->
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
                        
                        
                        <!-- Klump Logo Management Section -->
                        <div class="klump-card">
                            <div class="klump-card-header">
                                <h3><span class="dashicons dashicons-format-image"></span> Klump Logo</h3>
                            </div>
                            <div class="klump-card-body">
                                <p class="klump-field-description">Upload a custom Klump logo to replace the default SVG logo.</p>
                                <div class="klump-field-group klump-icon-field">
                                    <label for="klump_logo" class="klump-field-label">Klump Logo</label>
                                    <input type="hidden" 
                                           id="klump_logo" 
                                           name="klump_ads_logo" 
                                           value="<?php echo esc_attr($settings['logo']); ?>" 
                                           class="klump-icon-input">
                                    
                                    <div class="klump-icon-controls">
                                        <button type="button" 
                                                class="button-primary klump-icon-upload-btn" 
                                                data-target="klump_logo" 
                                                data-icon-type="logo">
                                            <span class="dashicons dashicons-upload"></span> Upload Logo
                                        </button>
                                        <button type="button" 
                                                class="button-secondary klump-icon-remove-btn" 
                                                data-target="klump_logo" 
                                                data-icon-type="logo">
                                            <span class="dashicons dashicons-trash"></span> Remove
                                        </button>
                                    </div>
                                    
                                    <div id="klump_logo_display" class="klump-icon-display">
                                        <?php if (!empty($settings['logo'])): ?>
                                            <img src="<?php echo esc_url($settings['logo']); ?>" 
                                                 alt="Klump Logo" 
                                                 class="klump-icon-preview" style="max-width: 120px; max-height: 48px;">
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (empty($settings['logo'])): ?>
                                    <div class="klump-icon-placeholder">
                                        <span class="dashicons dashicons-format-image"></span>
                                        <p>No custom logo selected - using default SVG</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Icon Management Section -->
                        <div class="klump-card">
                            <div class="klump-card-header">
                                <h3><span class="dashicons dashicons-admin-customizer"></span> Payment Icons</h3>
                            </div>
                            <div class="klump-card-body">
                                <p class="klump-field-description">Upload custom icons for different payment methods that will appear in your ads.</p>
                                <div class="klump-icon-grid">
                                    <?php 
                                    $icon_labels = array(
                                        1 => 'Credit Card',
                                        2 => 'Mobile Payment',
                                        3 => 'Bank Transfer',
                                        4 => 'Digital Wallet'
                                    );
                                    for ($i = 1; $i <= 4; $i++): 
                                    ?>
                                    <div class="klump-field-group klump-icon-field">
                                        <label for="klump_icon_<?php echo $i; ?>" class="klump-field-label">
                                            Icon <?php echo $i; ?> (<?php echo $icon_labels[$i]; ?>)
                                        </label>
                                        <input type="hidden" 
                                               id="klump_icon_<?php echo $i; ?>" 
                                               name="klump_ads_icon_<?php echo $i; ?>" 
                                               value="<?php echo esc_attr($settings['icon_' . $i]); ?>" 
                                               class="klump-icon-input">
                                        
                                        <div class="klump-icon-controls">
                                            <button type="button" 
                                                    class="button-primary klump-icon-upload-btn" 
                                                    data-target="klump_icon_<?php echo $i; ?>" 
                                                    data-icon-type="icon_<?php echo $i; ?>">
                                                <span class="dashicons dashicons-upload"></span> Upload Icon
                                            </button>
                                            <button type="button" 
                                                    class="button-secondary klump-icon-remove-btn" 
                                                    data-target="klump_icon_<?php echo $i; ?>" 
                                                    data-icon-type="icon_<?php echo $i; ?>">
                                                <span class="dashicons dashicons-trash"></span> Remove
                                            </button>
                                        </div>
                                        
                                        <div id="klump_icon_<?php echo $i; ?>_display" class="klump-icon-display">
                                            <?php if (!empty($settings['icon_' . $i])): ?>
                                                <img src="<?php echo esc_url($settings['icon_' . $i]); ?>" 
                                                     alt="Icon <?php echo $i; ?>" 
                                                     class="klump-icon-preview">
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (empty($settings['icon_' . $i])): ?>
                                        <div class="klump-icon-placeholder">
                                            <span class="dashicons dashicons-format-image"></span>
                                            <p>No icon selected</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                                
                                <div class="klump-icon-presets">
                                    <h4>Quick Presets</h4>
                                    <div class="klump-preset-buttons">
                                        <button type="button" class="button klump-icon-preset-btn" data-preset="payment_cards">
                                            Payment Cards
                                        </button>
                                        <button type="button" class="button klump-icon-preset-btn" data-preset="modern_minimal">
                                            Modern Minimal
                                        </button>
                                        <button type="button" class="button klump-icon-preset-btn" data-preset="colorful">
                                            Colorful
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Color Customization Section -->
                        <div class="klump-card">
                            <div class="klump-card-header">
                                <h3><span class="dashicons dashicons-art"></span> Color Customization</h3>
                            </div>
                            <div class="klump-card-body">
                                <div class="klump-color-grid">
                                    <div class="klump-field-group">
                                        <label for="klump_primary_color" class="klump-field-label">Primary Color</label>
                                        <div class="klump-color-input-wrapper">
                                            <input type="color" 
                                                   id="klump_primary_color" 
                                                   name="klump_ads_primary_color" 
                                                   value="<?php echo esc_attr($settings['primary_color']); ?>" 
                                                   class="klump-color-input">
                                            <input type="text" 
                                                   class="klump-color-text" 
                                                   value="<?php echo esc_attr($settings['primary_color']); ?>" 
                                                   readonly>
                                        </div>
                                        <p class="klump-field-description">Main brand color for titles and buttons</p>
                                    </div>
                                    <div class="klump-field-group">
                                        <label for="klump_secondary_color" class="klump-field-label">Secondary Color</label>
                                        <div class="klump-color-input-wrapper">
                                            <input type="color" 
                                                   id="klump_secondary_color" 
                                                   name="klump_ads_secondary_color" 
                                                   value="<?php echo esc_attr($settings['secondary_color']); ?>" 
                                                   class="klump-color-input">
                                            <input type="text" 
                                                   class="klump-color-text" 
                                                   value="<?php echo esc_attr($settings['secondary_color']); ?>" 
                                                   readonly>
                                        </div>
                                        <p class="klump-field-description">Accent color for gradients and highlights</p>
                                    </div>
                                    <div class="klump-field-group">
                                        <label for="klump_background_color" class="klump-field-label">Background Color</label>
                                        <div class="klump-color-input-wrapper">
                                            <input type="color" 
                                                   id="klump_background_color" 
                                                   name="klump_ads_background_color" 
                                                   value="<?php echo esc_attr($settings['background_color']); ?>" 
                                                   class="klump-color-input">
                                            <input type="text" 
                                                   class="klump-color-text" 
                                                   value="<?php echo esc_attr($settings['background_color']); ?>" 
                                                   readonly>
                                        </div>
                                        <p class="klump-field-description">Background color of the ad container</p>
                                    </div>
                                    <div class="klump-field-group">
                                        <label for="klump_text_color" class="klump-field-label">Text Color</label>
                                        <div class="klump-color-input-wrapper">
                                            <input type="color" 
                                                   id="klump_text_color" 
                                                   name="klump_ads_text_color" 
                                                   value="<?php echo esc_attr($settings['text_color']); ?>" 
                                                   class="klump-color-input">
                                            <input type="text" 
                                                   class="klump-color-text" 
                                                   value="<?php echo esc_attr($settings['text_color']); ?>" 
                                                   readonly>
                                        </div>
                                        <p class="klump-field-description">Color for description text</p>
                                    </div>
                                    <div class="klump-field-group">
                                        <label for="klump_price_color" class="klump-field-label">Price Color</label>
                                        <div class="klump-color-input-wrapper">
                                            <input type="color" 
                                                   id="klump_price_color" 
                                                   name="klump_ads_price_color" 
                                                   value="<?php echo esc_attr($settings['price_color']); ?>" 
                                                   class="klump-color-input">
                                            <input type="text" 
                                                   class="klump-color-text" 
                                                   value="<?php echo esc_attr($settings['price_color']); ?>" 
                                                   readonly>
                                        </div>
                                        <p class="klump-field-description">Color for price information</p>
                                    </div>
                                    <div class="klump-field-group">
                                        <label for="klump_border_color" class="klump-field-label">Border Color</label>
                                        <div class="klump-color-input-wrapper">
                                            <input type="color" 
                                                   id="klump_border_color" 
                                                   name="klump_ads_border_color" 
                                                   value="<?php echo esc_attr($settings['border_color']); ?>" 
                                                   class="klump-color-input">
                                            <input type="text" 
                                                   class="klump-color-text" 
                                                   value="<?php echo esc_attr($settings['border_color']); ?>" 
                                                   readonly>
                                        </div>
                                        <p class="klump-field-description">Border color of the ad container</p>
                                    </div>
                                </div>
                                <div class="klump-color-presets">
                                    <h4>Color Presets</h4>
                                    <div class="klump-preset-buttons">
                                        <button type="button" class="klump-preset-btn" data-preset="default">Default</button>
                                        <button type="button" class="klump-preset-btn" data-preset="purple">Purple</button>
                                        <button type="button" class="klump-preset-btn" data-preset="blue">Blue</button>
                                        <button type="button" class="klump-preset-btn" data-preset="green">Green</button>
                                        <button type="button" class="klump-preset-btn" data-preset="orange">Orange</button>
                                        <button type="button" class="klump-preset-btn" data-preset="dark">Dark</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="klump-card">
                            <div class="klump-card-header">
                                <h3><span class="dashicons dashicons-edit"></span> Display Settings</h3>
                            </div>
                            <div class="klump-card-body">
                                <div class="klump-field-group">
                                    <label for="klump_title_text" class="klump-field-label">Ad Title Text</label>
                                    <input type="text" 
                                           id="klump_title_text" 
                                           name="klump_ads_title_text" 
                                           value="<?php echo esc_attr($settings['title_text']); ?>" 
                                           class="klump-text-input">
                                </div>
                                <div class="klump-field-group">
                                    <label for="klump_description_text" class="klump-field-label">Ad Description Text</label>
                                    <input type="text" 
                                           id="klump_description_text" 
                                           name="klump_ads_description_text" 
                                           value="<?php echo esc_attr($settings['description_text']); ?>" 
                                           class="klump-text-input">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Preview Section -->
                        <div class="klump-card">
                            <div class="klump-card-header">
                                <h3><span class="dashicons dashicons-visibility"></span> Live Preview</h3>
                            </div>
                            <div class="klump-card-body">
                                <p class="klump-field-description">This is how the Klump ad will appear on your product pages:</p>
                                <div id="klump_preview" class="klump-preview">
                                    <div id="klump__ad" class="klump-ad-preview">
                                        <div class="klump-ad-content">
                                            <div class="klump-text-section">
                                                <div class="klump-title">💳 <span id="preview_title"><?php echo esc_html($settings['title_text']); ?></span></div>
                                                <div class="klump-description"><span id="preview_description"><?php echo esc_html($settings['description_text']); ?></span></div>
                                                <div class="klump-price-info">As low as <strong id="preview_price"><?php echo esc_html($settings['currency']) . ' ' . esc_html(number_format(floatval($settings['price']) / 4, 2)); ?></strong> per month</div>
                                            </div>
                                            <div class="klump-logo-section">
                                                <div class="klump-logo">
                                                    <?php if (!empty($settings['logo'])): ?>
                                                        <img src="<?php echo esc_url($settings['logo']); ?>" alt="Klump" style="max-width: 120px; max-height: 48px;">
                                                    <?php else: ?>
                                                        <svg width="60" height="24" viewBox="0 0 120 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M20 8L40 8C44.4183 8 48 11.5817 48 16V32C48 36.4183 44.4183 40 40 40H20C15.5817 40 12 36.4183 12 32V16C12 11.5817 15.5817 8 20 8Z" fill="url(#klump-gradient)"/>
                                                            <path d="M24 20H36V28H24V20Z" fill="white"/>
                                                            <text x="52" y="32" font-family="Arial, sans-serif" font-size="20" font-weight="bold" fill="#2e08f4">klump</text>
                                                            <defs>
                                                                <linearGradient id="klump-gradient" x1="12" y1="8" x2="48" y2="40" gradientUnits="userSpaceOnUse">
                                                                    <stop stop-color="#2e08f4"/>
                                                                    <stop offset="1" stop-color="#cf13e4"/>
                                                                </linearGradient>
                                                            </defs>
                                                        </svg>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="klump-payment-icons">
                                                    <div class="payment-icon selected" data-icon="card">💳</div>
                                                    <div class="payment-icon" data-icon="mobile">📱</div>
                                                    <div class="payment-icon" data-icon="bank">🏦</div>
                                                    <div class="payment-icon" data-icon="wallet">👛</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        
                        <div class="klump-submit-section">
                            <?php submit_button('Save Settings', 'primary klump-save-btn', 'submit', false); ?>
                            <button type="button" class="button klump-reset-btn" onclick="return confirm('Are you sure you want to reset all settings to default values?');">Reset to Defaults</button>
                        </div>
                    </form>
                </div>
                
                <div class="klump-sidebar">
                    <div class="klump-card">
                        <div class="klump-card-header">
                            <h3><span class="dashicons dashicons-info"></span> About Klump</h3>
                        </div>
                        <div class="klump-card-body">
                            <p>Klump is a leading buy-now-pay-later service that helps increase conversion rates by offering flexible payment plans to your customers.</p>
                            <div class="klump-links">
                                <a href="https://klump.co" target="_blank" class="klump-link">
                                    <span class="dashicons dashicons-external"></span> Visit Klump.co
                                </a>
                                <a href="https://klump.co/dashboard" target="_blank" class="klump-link">
                                    <span class="dashicons dashicons-admin-users"></span> Merchant Dashboard
                                </a>
                                <a href="https://docs.klump.co" target="_blank" class="klump-link">
                                    <span class="dashicons dashicons-book-alt"></span> Documentation
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="klump-card">
                        <div class="klump-card-header">
                            <h3><span class="dashicons dashicons-sos"></span> Need Help?</h3>
                        </div>
                        <div class="klump-card-body">
                            <p>Having trouble with the integration? We're here to help!</p>
                            <div class="klump-links">
                                <a href="mailto:support@klump.co" class="klump-link">
                                    <span class="dashicons dashicons-email-alt"></span> Email Support
                                </a>
                                <a href="https://klump.co/contact" target="_blank" class="klump-link">
                                    <span class="dashicons dashicons-phone"></span> Contact Us
                                </a>
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
                'klump_ads_border_color' => sanitize_hex_color($_POST['klump_ads_border_color'])
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
            
            // Log results
            error_log("Klump Product Ads: Save complete - Success: {$success_count}, Errors: {$error_count}");
            
            if ($error_count > 0) {
                error_log("Klump Product Ads: Errors encountered: " . implode(', ', $errors));
                // Set error transient for display
                set_transient('klump_save_errors', $errors, 30);
                set_transient('klump_save_status', 'error', 30);
            echo "<!-- klump-save-error -->";
            } else {
                // Set success transient for display
                set_transient('klump_save_status', 'success', 30);
            echo "<!-- klump-save-success -->";
            }
            
        } catch (Exception $e) {
            error_log("Klump Product Ads: Exception during save: " . $e->getMessage());
            set_transient('klump_save_errors', array('Unexpected error: ' . $e->getMessage()), 30);
            set_transient('klump_save_status', 'error', 30);
            echo "<!-- klump-save-error -->";
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
}

// Initialize admin interface
new KlumpProductAdsAdmin();


// Instantiate admin interface
if (is_admin()) {
    new KlumpProductAdsAdmin();
}
