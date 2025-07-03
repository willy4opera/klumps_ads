/**
 * Klump Product Ads Admin JavaScript - Enhanced with Icon Management and SweetAlert
 */

(function($) {
    'use strict';
    
    // URL Helper Functions
    function makeUrlRelative(url) {
        if (!url) return url;
        
        // If it's already a relative URL, return as is
        if (url.startsWith('/')) {
            return url;
        }
        
        // If it's a full URL, extract the path part after the domain
        if (url.includes('://')) {
            try {
                const urlObj = new URL(url);
                return urlObj.pathname;
            } catch (e) {
                console.warn('Could not parse URL:', url);
                return url;
            }
        }
        
        return url;
    }
    
    function makeUrlAbsolute(url) {
        if (!url) return url;
        
        // If it's already absolute, return as is
        if (url.startsWith('http')) {
            return url;
        }
        
        // If it's relative, prepend the site URL
        if (url.startsWith('/')) {
            return window.location.origin + url;
        }
        
        return url;
    }
    
    // Brand colors for consistency
    const brandColors = {
        main: '#2e08f4',
        sub: '#cf13e4',
        gradient: 'linear-gradient(135deg, #2e08f4 0%, #cf13e4 100%)'
    };
    
    $(document).ready(function() {
        initializeAdmin();
    });
    
    function initializeAdmin() {
        // Initialize all admin functionality
        initializeIconUpload();
        initializePreview();
        initializeValidation();
        initializeFormSubmission();
        initializeColorPicker();
        
        // Show success message if form was just submitted
        if (window.location.search.includes('settings-updated=true') || $('.notice-success').length > 0) {
            showNotification('Settings saved successfully!', 'success');
        }
    }

    function initializeIconUpload() {
        // Ensure WordPress media scripts are available
        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            console.warn('WordPress media library not available');
            return;
        }

        // Icon upload buttons
        $('.klump-icon-upload-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = $(this);
            const iconFieldId = button.data('target');
            const iconType = button.data('icon-type');

            // Add loading state
            button.addClass('loading').prop('disabled', true);

            // Create the media frame
            const file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Select or Upload Payment Icon',
                button: {
                    text: 'Use this icon'
                },
                multiple: false,
                library: {
                    type: ['image']
                }
            });

            // When an image is selected
            file_frame.on('select', function() {
                const attachment = file_frame.state().get('selection').first().toJSON();
                
                // Validate image
                if (!isValidImage(attachment)) {
                    showNotification('Please select a valid image file (JPG, PNG, GIF, SVG)', 'error');
                    button.removeClass('loading').prop('disabled', false);
                    return;
                }

                // Convert absolute URL to relative for storage
                const relativeUrl = makeUrlRelative(attachment.url);
                
                // Update the input field with relative URL
                $('#' + iconFieldId).val(relativeUrl);
                
                // Update display
                updateIconDisplay(iconFieldId, relativeUrl);
                
                // Update preview if applicable
                updatePreviewIcon(iconType, relativeUrl);
                
                // Show success notification
                showNotification('Icon uploaded successfully!', 'success');
                
                // Remove loading state
                button.removeClass('loading').prop('disabled', false);
            });

            // Handle frame close without selection
            file_frame.on('close', function() {
                button.removeClass('loading').prop('disabled', false);
            });

            // Open the modal
            file_frame.open();
        });

        // Icon remove buttons
        $('.klump-icon-remove-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = $(this);
            const iconFieldId = button.data('target');
            const iconType = button.data('icon-type');

            // Clear the input field
            $('#' + iconFieldId).val('');
            
            // Update display
            updateIconDisplay(iconFieldId, '');
            
            // Update preview
            updatePreviewIcon(iconType, '');
            
            // Show success notification
            showNotification('Icon removed successfully!', 'success');
        });

        // Icon preset buttons
        $('.klump-icon-preset-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const preset = $(this).data('preset');
            applyIconPreset(preset);
        });
    }

    function isValidImage(attachment) {
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        return validTypes.includes(attachment.mime) || 
               validTypes.includes(attachment.type) ||
               /\.(jpg|jpeg|png|gif|svg)$/i.test(attachment.filename || attachment.url);
    }

    function updateIconDisplay(iconFieldId, iconUrl) {
        const displayElement = $('#' + iconFieldId + '_display');
        const placeholderElement = displayElement.siblings('.klump-icon-placeholder');

        if (iconUrl) {
            // Convert to absolute URL for display
            const displayUrl = makeUrlAbsolute(iconUrl);
            displayElement.html('<img src="' + displayUrl + '" alt="Payment Icon" class="klump-icon-preview" />');
            displayElement.addClass('success');
            placeholderElement.hide();
            
            // Remove success class after animation
            setTimeout(() => {
                displayElement.removeClass('success');
            }, 500);
        } else {
            displayElement.empty();
            placeholderElement.show();
        }
    }

    function updatePreviewIcon(iconType, iconUrl) {
        // Update the preview section if it exists
        const previewIcons = $('#klump_preview .klump-payment-icons .payment-icon');
        
        if (previewIcons.length > 0) {
            // Map icon types to preview elements
            const iconMap = {
                'icon_1': 'card',
                'icon_2': 'mobile', 
                'icon_3': 'bank',
                'icon_4': 'wallet',
                'logo': 'logo'
            };
            
            const previewType = iconMap[iconType];
            if (previewType === 'logo') {
                // Update logo in preview
                const logoElement = $('#klump_preview .klump-logo');
                if (iconUrl) {
                    // Convert to absolute URL for display
                    const displayUrl = makeUrlAbsolute(iconUrl);
                    logoElement.html('<img src="' + displayUrl + '" alt="Klump Logo" style="max-width: 120px; max-height: 48px;">');
                } else {
                    // Reset to default SVG
                    logoElement.html(getDefaultKlumpSVG());
                }
            } else if (previewType) {
                const previewIcon = previewIcons.filter('[data-icon="' + previewType + '"]');
                if (iconUrl) {
                    // Convert to absolute URL for display
                    const displayUrl = makeUrlAbsolute(iconUrl);
                    previewIcon.html('<img src="' + displayUrl + '" alt="' + previewType + '" style="width: 20px; height: 20px;">');
                } else {
                    // Reset to default emoji
                    const defaultIcons = {
                        'card': '💳',
                        'mobile': '📱',
                        'bank': '🏦',
                        'wallet': '👛'
                    };
                    previewIcon.html(defaultIcons[previewType] || '💳');
                }
            }
        }
    }

    function getDefaultKlumpSVG() {
        return '<svg width="60" height="24" viewBox="0 0 120 48" fill="none" xmlns="http://www.w3.org/2000/svg">' +
               '<path d="M20 8L40 8C44.4183 8 48 11.5817 48 16V32C48 36.4183 44.4183 40 40 40H20C15.5817 40 12 36.4183 12 32V16C12 11.5817 15.5817 8 20 8Z" fill="url(#klump-gradient)"/>' +
               '<path d="M24 20H36V28H24V20Z" fill="white"/>' +
               '<text x="52" y="32" font-family="Arial, sans-serif" font-size="20" font-weight="bold" fill="#2e08f4">klump</text>' +
               '<defs>' +
               '<linearGradient id="klump-gradient" x1="12" y1="8" x2="48" y2="40" gradientUnits="userSpaceOnUse">' +
               '<stop stop-color="#2e08f4"/>' +
               '<stop offset="1" stop-color="#cf13e4"/>' +
               '</linearGradient>' +
               '</defs>' +
               '</svg>';
    }

    function applyIconPreset(preset) {
        const presets = {
            payment_cards: {
                icon_1: 'https://via.placeholder.com/24x24/2e08f4/FFFFFF?text=💳',
                icon_2: 'https://via.placeholder.com/24x24/cf13e4/FFFFFF?text=📱',
                icon_3: 'https://via.placeholder.com/24x24/2e08f4/FFFFFF?text=🏦',
                icon_4: 'https://via.placeholder.com/24x24/cf13e4/FFFFFF?text=👛'
            },
            modern_minimal: {
                icon_1: 'https://via.placeholder.com/24x24/6c757d/FFFFFF?text=C',
                icon_2: 'https://via.placeholder.com/24x24/6c757d/FFFFFF?text=M',
                icon_3: 'https://via.placeholder.com/24x24/6c757d/FFFFFF?text=B',
                icon_4: 'https://via.placeholder.com/24x24/6c757d/FFFFFF?text=W'
            },
            colorful: {
                icon_1: 'https://via.placeholder.com/24x24/3b82f6/FFFFFF?text=💳',
                icon_2: 'https://via.placeholder.com/24x24/10b981/FFFFFF?text=📱',
                icon_3: 'https://via.placeholder.com/24x24/f59e0b/FFFFFF?text=🏦',
                icon_4: 'https://via.placeholder.com/24x24/8b5cf6/FFFFFF?text=👛'
            }
        };
        
        const icons = presets[preset];
        if (icons) {
            Object.keys(icons).forEach(function(iconKey) {
                const iconField = 'klump_' + iconKey;
                const iconUrl = icons[iconKey];
                
                // Update input field
                $('#' + iconField).val(iconUrl);
                
                // Update display
                updateIconDisplay(iconField, iconUrl);
                
                // Update preview
                updatePreviewIcon(iconKey, iconUrl);
            });
            
            showNotification('Icon preset "' + preset.replace('_', ' ') + '" applied successfully!', 'success');
        }
    }

    function initializePreview() {
        // Update preview when text fields change
        $('#klump_title_text').on('input', function() {
            $('#preview_title').text($(this).val() || 'Pay in installments with Klump');
        });
        
        $('#klump_description_text').on('input', function() {
            $('#preview_description').text($(this).val() || 'Split your payment into flexible installments');
        });
        
        // Update price preview when price or currency changes
        $('#klump_default_price, #klump_currency').on('change input', function() {
            updatePricePreview();
        });
        
        // Initial price preview update
        updatePricePreview();
    }

    function updatePricePreview() {
        const price = parseFloat($('#klump_default_price').val()) || 2000;
        const currency = $('#klump_currency').val() || 'NGN';
        const monthlyPayment = (price / 4).toFixed(2);
        $('#preview_price').text(currency + ' ' + monthlyPayment);
    }

    function initializeValidation() {
        // Real-time merchant key validation
        $('#klump_merchant_key').on('input', function() {
            const key = $(this).val();
            if (key.length > 10) {
                validateKeyFormat(key);
            } else {
                clearValidationResult();
            }
        });
        
        // Validate button click
        $('#validate_key_btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const key = $('#klump_merchant_key').val();
            
            if (!key) {
                showNotification('Please enter a merchant key first', 'error');
                return;
            }
            
            validateMerchantKey(key, $(this));
        });
        
        // YouTube URL validate button click
        $('#validate_youtube_btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const youtubeUrl = $('#klump_youtube_url').val();
            
            if (!youtubeUrl) {
                showNotification('Please enter a YouTube URL first', 'error');
                return;
            }
            
            validateYouTubeUrl(youtubeUrl, $(this));
        });
    }

    function validateKeyFormat(key) {
        const isValid = /^klp_pk_[a-zA-Z0-9]{20,}$/.test(key);
        const $result = $('#key_validation_result');
        
        if (isValid) {
            $result.removeClass('invalid').addClass('valid')
                   .html('<i class="dashicons dashicons-yes-alt"></i> Valid Klump key format');
        } else {
            $result.removeClass('valid').addClass('invalid')
                   .html('<i class="dashicons dashicons-dismiss"></i> Invalid key format. Should start with "klp_pk_"');
        }
    }

    function validateMerchantKey(key, $btn) {
        const originalHtml = $btn.html();
        
        // Show loading state
        $btn.html('<span class="dashicons dashicons-update klump-loading"></span> Validating...')
            .prop('disabled', true);
        
        $.ajax({
            url: klump_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'klump_validate_key',
                merchant_key: key,
                nonce: klump_admin_ajax.nonce
            },
            success: function(response) {
                console.log('AJAX response received', response.length, 'characters');
                if (response.valid) {
                    showValidationSuccess(response.message);
                    showNotification('Merchant key validation successful!', 'success');
                } else {
                    showValidationError(response.message);
                    showNotification('Merchant key validation failed: ' + response.message, 'error');
                }
            },
            error: function() {
                showValidationError('Unable to validate key. Please try again.');
                showNotification('Connection error during validation', 'error');
            },
            complete: function() {
                // Restore button state
                $btn.html(originalHtml).prop('disabled', false);
            }
        });
    }

    function showValidationSuccess(message) {
        const $result = $('#key_validation_result');
        $result.removeClass('invalid').addClass('valid')
               .html('<i class="dashicons dashicons-yes-alt"></i> ' + message);
    }

    function showValidationError(message) {
        const $result = $('#key_validation_result');
        $result.removeClass('valid').addClass('invalid')
               .html('<i class="dashicons dashicons-dismiss"></i> ' + message);
    }

    function clearValidationResult() {
        $('#key_validation_result').removeClass('valid invalid').empty();
    }

    function validateYouTubeUrl(url, $btn) {
        const originalHtml = $btn.html();
        
        // Show loading state
        $btn.html('<span class="dashicons dashicons-update klump-loading"></span> Validating...')
            .prop('disabled', true);
        
        $.ajax({
            url: klump_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'klump_validate_youtube_url',
                youtube_url: url,
                nonce: klump_admin_ajax.nonce
            },
            success: function(response) {
                console.log('YouTube validation response:', response);
                if (response.success) {
                    showYouTubeValidationSuccess(response.data.message, response.data.video_id);
                    showNotification('YouTube URL validation successful!', 'success');
                } else {
                    showYouTubeValidationError(response.data.message);
                    showNotification('YouTube URL validation failed: ' + response.data.message, 'error');
                }
            },
            error: function() {
                showYouTubeValidationError('Unable to validate YouTube URL. Please try again.');
                showNotification('Connection error during YouTube validation', 'error');
            },
            complete: function() {
                // Restore button state
                $btn.html(originalHtml).prop('disabled', false);
            }
        });
    }

    function showYouTubeValidationSuccess(message, videoId) {
        const $result = $('#youtube_validation_result');
        let displayMessage = '<i class="dashicons dashicons-yes-alt"></i> ' + message;
        if (videoId) {
            displayMessage += ' (Video ID: ' + videoId + ')';
        }
        $result.removeClass('invalid').addClass('valid')
               .html(displayMessage);
    }

    function showYouTubeValidationError(message) {
        const $result = $('#youtube_validation_result');
        $result.removeClass('valid').addClass('invalid')
               .html('<i class="dashicons dashicons-dismiss"></i> ' + message);
    }

    function initializeColorPicker() {
        // Initialize WordPress color picker if available
        if ($.fn.wpColorPicker) {
            $('.klump-color-input').wpColorPicker({
                change: function() {
                    updatePreviewColors();
                },
                clear: function() {
                    updatePreviewColors();
                }
            });
        }
        
        // Color preset handlers
        $('.klump-preset-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const preset = $(this).data('preset');
            applyColorPreset(preset);
        });
    }

    function applyColorPreset(preset) {
        const presets = {
            default: {
                primary: '#2e08f4',
                secondary: '#cf13e4',
                background: '#f8f9ff',
                text: '#6c757d',
                price: '#2e08f4',
                border: '#e9ecef'
            },
            purple: {
                primary: '#8b5cf6',
                secondary: '#a855f7',
                background: '#faf5ff',
                text: '#6b7280',
                price: '#7c3aed',
                border: '#e5e7eb'
            },
            blue: {
                primary: '#3b82f6',
                secondary: '#1d4ed8',
                background: '#eff6ff',
                text: '#64748b',
                price: '#2563eb',
                border: '#e2e8f0'
            }
        };
        
        const colors = presets[preset];
        if (colors) {
            $('#klump_primary_color').val(colors.primary).trigger('change');
            $('#klump_secondary_color').val(colors.secondary).trigger('change');
            $('#klump_background_color').val(colors.background).trigger('change');
            $('#klump_text_color').val(colors.text).trigger('change');
            $('#klump_price_color').val(colors.price).trigger('change');
            $('#klump_border_color').val(colors.border).trigger('change');
            
            showNotification(preset.charAt(0).toUpperCase() + preset.slice(1) + ' color preset applied!', 'success');
        }
    }

    function updatePreviewColors() {
        // This function would update the preview colors in real-time
        // Implementation depends on the preview structure
    }

    function initializeFormSubmission() {
        $('.klump-form').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const form = this;
            console.log('Form submission intercepted', form);
            const merchantKey = $('#klump_merchant_key').val();
            
            // Basic validation before submission
            if (!merchantKey || !merchantKey.startsWith('klp_pk_')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Merchant Key',
                    text: 'Please enter a valid Klump merchant key before saving.',
                    confirmButtonColor: brandColors.main
                });
                return false;
            }
            
            // Show confirmation dialog
            Swal.fire({
                title: 'Save Settings?',
                text: 'Are you sure you want to save these Klump configuration settings?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: brandColors.main,
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Save Settings',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitFormWithProgress(form);
                }
            });
        });
    }
    
    function submitFormWithProgress(form) {
        // Show loading dialog
        const loadingSwal = Swal.fire({
            title: 'Saving Settings...',
            text: 'Please wait while we save your configuration.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Convert to jQuery object if needed
        const $form = $(form);
        console.log('Starting form submission via AJAX', $form);
        
        // Submit via AJAX
        // Prepare data with nonce
        const formData = $form.serializeArray();
        formData.push({name: 'action', value: 'klump_save_settings'});
        formData.push({name: 'nonce', value: klump_admin_ajax.nonce});
        
        $.ajax({
            url: klump_admin_ajax.ajax_url,
            type: 'POST',
            data: $.param(formData),
            beforeSend: function() {
                console.log('Sending AJAX request to:', klump_admin_ajax.ajax_url);
            },
            success: function(response) {
                console.log('AJAX response received:', response);
                loadingSwal.close();
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Settings Saved!',
                        text: response.data.message || 'Your Klump configuration has been updated successfully.',
                        confirmButtonColor: brandColors.main,
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        // Reload to show updated settings
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Save Failed',
                        text: response.data.message || 'There was an error saving your settings.',
                        confirmButtonColor: '#ef4444'
                    });
                    console.error('Save failed:', response.data);
                }
            },
            error: function(xhr, status, error) {
                loadingSwal.close();
                console.error('Klump save error:', error, xhr.responseText);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Unable to save settings. Please check your connection and try again.',
                    confirmButtonColor: '#ef4444'
                });
            }
        });
    }

    function showNotification(message, type = 'info') {
        const iconClass = type === 'success' ? 'dashicons-yes-alt' : 
                         type === 'error' ? 'dashicons-dismiss' : 'dashicons-info';
        
        const $notification = $('<div class="klump-notification klump-notification-' + type + '">')
            .html('<span class="dashicons ' + iconClass + '"></span> ' + message)
            .appendTo('body');
        
        // Auto remove after 4 seconds
        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 4000);
    }

})(jQuery);

// Add notification styles
const notificationCSS = `
<style>
.klump-notification {
    position: fixed;
    top: 32px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 6px;
    color: white;
    font-weight: 500;
    z-index: 9999;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 300px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.klump-notification-success {
    background: #10b981;
}

.klump-notification-error {
    background: #ef4444;
}

.klump-notification-info {
    background: #3b82f6;
}

.klump-notification .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.klump-loading {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.klump-icon-upload-btn.loading {
    opacity: 0.7;
    cursor: not-allowed;
}

.klump-icon-upload-btn.loading::before {
    content: '';
    display: inline-block;
    width: 12px;
    height: 12px;
    border: 2px solid #fff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 0.8s linear infinite;
    margin-right: 5px;
}
</style>
`;

jQuery(document).ready(function() {
    jQuery('head').append(notificationCSS);
});
