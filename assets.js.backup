/**
 * Klump Product Ads JavaScript - Enhanced with Icon Selection
 * 
 * This file contains interactive functionality for the Klump product ads.
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        initializeKlumpAd();
    });
    
    function initializeKlumpAd() {
        // Check if Klump ad exists
        var $klumpAd = $('#klump__ad');
        
        if ($klumpAd.length) {
            initializePaymentIcons();
            initializeAdClickHandler();
            initializeHoverEffects();
            
            // Log initialization
            console.log('Klump Product Ads initialized');
        }
    }
    
    function initializePaymentIcons() {
        $('.payment-icon').on('click', function(e) {
            e.stopPropagation(); // Prevent ad click event
            
            // Remove selected class from all icons
            $('.payment-icon').removeClass('selected');
            
            // Add selected class to clicked icon
            $(this).addClass('selected');
            
            // Get icon type
            var iconType = $(this).data('icon');
            var iconName = getIconName(iconType);
            
            // Store selected payment method
            $('#klump__ad').attr('data-selected-payment', iconType);
            
            // Visual feedback
            showPaymentSelection(iconName);
            
            // Log selection
            console.log('Payment method selected:', iconName);
        });
        
        // Add hover tooltips
        $('.payment-icon').hover(
            function() {
                $(this).addClass('tooltip-active');
            },
            function() {
                $(this).removeClass('tooltip-active');
            }
        );
    }
    
    function initializeAdClickHandler() {
        $('#klump__ad').on('click', function() {
            // Get the hidden values
            var price = $('#klump__price').val();
            var merchantKey = $('#klump__merchant__public__key').val();
            var currency = $('#klump__currency').val();
            var selectedPayment = $(this).attr('data-selected-payment') || 'card';
            
            // Log the values
            console.log('Klump ad clicked with:', {
                price: price,
                merchantKey: merchantKey,
                currency: currency,
                selectedPayment: selectedPayment
            });
            
            // Show payment modal or redirect
            initiateKlumpPayment({
                price: price,
                merchantKey: merchantKey,
                currency: currency,
                paymentMethod: selectedPayment
            });
        });
    }
    
    function initializeHoverEffects() {
        var $klumpAd = $('#klump__ad');
        
        $klumpAd.hover(
            function() {
                $(this).addClass('klump-ad-hover');
                
                // Animate payment icons
                $('.payment-icon').each(function(index) {
                    setTimeout(() => {
                        $(this).addClass('bounce-in');
                    }, index * 100);
                });
            },
            function() {
                $(this).removeClass('klump-ad-hover');
                
                // Remove animation classes
                $('.payment-icon').removeClass('bounce-in');
            }
        );
        
        // Add cursor pointer
        $klumpAd.css('cursor', 'pointer');
    }
    
    function getIconName(iconType) {
        const iconNames = {
            'card': 'Credit/Debit Card',
            'mobile': 'Mobile Payment',
            'bank': 'Bank Transfer',
            'wallet': 'Digital Wallet'
        };
        return iconNames[iconType] || 'Payment Method';
    }
    
    function showPaymentSelection(paymentName) {
        // Create a temporary notification
        var $notification = $('<div class="klump-selection-notification">' + 
                             '<span>✓</span> ' + paymentName + ' selected' + 
                             '</div>');
        
        // Style the notification
        $notification.css({
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: 'linear-gradient(135deg, #2e08f4, #cf13e4)',
            color: 'white',
            padding: '12px 20px',
            borderRadius: '8px',
            fontSize: '14px',
            fontWeight: '600',
            zIndex: '9999',
            boxShadow: '0 4px 15px rgba(46, 8, 244, 0.3)',
            transform: 'translateX(300px)',
            transition: 'transform 0.3s ease'
        });
        
        // Add to body
        $('body').append($notification);
        
        // Animate in
        setTimeout(() => {
            $notification.css('transform', 'translateX(0)');
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            $notification.css('transform', 'translateX(300px)');
            setTimeout(() => {
                $notification.remove();
            }, 300);
        }, 2500);
    }
    
    function initiateKlumpPayment(paymentData) {
        // This is where you would integrate with Klump's actual payment SDK
        // For now, we'll show a demo modal
        
        console.log('Initiating Klump payment with:', paymentData);
        
        // Show payment initiation feedback
        showPaymentModal(paymentData);
    }
    
    function showPaymentModal(paymentData) {
        // Create modal HTML
        var modalHtml = `
            <div id="klump-payment-modal" class="klump-modal-overlay">
                <div class="klump-modal">
                    <div class="klump-modal-header">
                        <h3>💳 Klump Payment</h3>
                        <button class="klump-modal-close">&times;</button>
                    </div>
                    <div class="klump-modal-body">
                        <div class="payment-summary">
                            <p><strong>Total Amount:</strong> ${paymentData.currency} ${paymentData.price}</p>
                            <p><strong>Monthly Payment:</strong> ${paymentData.currency} ${(parseFloat(paymentData.price) / 4).toFixed(2)}</p>
                            <p><strong>Payment Method:</strong> ${getIconName(paymentData.paymentMethod)}</p>
                            <p><strong>Duration:</strong> 4 months</p>
                        </div>
                        <div class="payment-schedule">
                            <h4>Payment Schedule:</h4>
                            <ul>
                                <li>Today: ${paymentData.currency} ${(parseFloat(paymentData.price) / 4).toFixed(2)}</li>
                                <li>Month 2: ${paymentData.currency} ${(parseFloat(paymentData.price) / 4).toFixed(2)}</li>
                                <li>Month 3: ${paymentData.currency} ${(parseFloat(paymentData.price) / 4).toFixed(2)}</li>
                                <li>Month 4: ${paymentData.currency} ${(parseFloat(paymentData.price) / 4).toFixed(2)}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="klump-modal-footer">
                        <button class="klump-btn klump-btn-secondary" onclick="closeKlumpModal()">Cancel</button>
                        <button class="klump-btn klump-btn-primary" onclick="proceedWithKlump()">Proceed with Klump</button>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal styles
        var modalStyles = `
            <style>
                .klump-modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.8);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                    animation: fadeIn 0.3s ease;
                }
                
                .klump-modal {
                    background: white;
                    border-radius: 12px;
                    max-width: 500px;
                    width: 90%;
                    max-height: 80vh;
                    overflow: auto;
                    animation: slideUp 0.3s ease;
                }
                
                .klump-modal-header {
                    padding: 20px;
                    border-bottom: 1px solid #eee;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .klump-modal-header h3 {
                    margin: 0;
                    color: #2e08f4;
                }
                
                .klump-modal-close {
                    background: none;
                    border: none;
                    font-size: 24px;
                    cursor: pointer;
                    color: #999;
                }
                
                .klump-modal-body {
                    padding: 20px;
                }
                
                .payment-summary {
                    margin-bottom: 20px;
                    padding: 15px;
                    background: #f8f9ff;
                    border-radius: 8px;
                }
                
                .payment-schedule ul {
                    list-style: none;
                    padding: 0;
                }
                
                .payment-schedule li {
                    padding: 8px 0;
                    border-bottom: 1px solid #eee;
                }
                
                .klump-modal-footer {
                    padding: 20px;
                    border-top: 1px solid #eee;
                    display: flex;
                    gap: 10px;
                    justify-content: flex-end;
                }
                
                .klump-btn {
                    padding: 12px 24px;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }
                
                .klump-btn-primary {
                    background: linear-gradient(135deg, #2e08f4, #cf13e4);
                    color: white;
                }
                
                .klump-btn-secondary {
                    background: #f8f9fa;
                    color: #6c757d;
                }
                
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                
                @keyframes slideUp {
                    from { transform: translateY(50px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
            </style>
        `;
        
        // Add modal to page
        $('body').append(modalStyles + modalHtml);
        
        // Add close event handlers
        $('.klump-modal-close, .klump-modal-overlay').on('click', function(e) {
            if (e.target === this) {
                closeKlumpModal();
            }
        });
    }
    
    // Global functions for modal
    window.closeKlumpModal = function() {
        $('#klump-payment-modal').fadeOut(300, function() {
            $(this).remove();
        });
    };
    
    window.proceedWithKlump = function() {
        // Here you would integrate with actual Klump SDK
        alert('Redirecting to Klump payment processor...\n\n(In a real implementation, this would connect to Klump\'s payment API)');
        closeKlumpModal();
    };
    
    // Function to get Klump ad data (for external use)
    window.getKlumpAdData = function() {
        return {
            price: $('#klump__price').val(),
            merchantKey: $('#klump__merchant__public__key').val(),
            currency: $('#klump__currency').val(),
            selectedPayment: $('#klump__ad').attr('data-selected-payment') || 'card'
        };
    };
    
    // Add bounce animation CSS
    var animationStyles = `
        <style>
            .bounce-in {
                animation: bounceIn 0.6s ease;
            }
            
            @keyframes bounceIn {
                0% { transform: scale(0.3); opacity: 0; }
                50% { transform: scale(1.05); }
                70% { transform: scale(0.9); }
                100% { transform: scale(1); opacity: 1; }
            }
            
            .tooltip-active {
                position: relative;
            }
        </style>
    `;
    
    $('head').append(animationStyles);
    
})(jQuery);
