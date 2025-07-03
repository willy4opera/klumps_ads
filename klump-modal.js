/**
 * Klump Modal JavaScript - YouTube Video Modal Handler
 * Handles YouTube video modal display when Klump ad is clicked
 */

(function($) {
    'use strict';
    
    // Check if jQuery is available
    if (typeof $ === "undefined") {
        $ = jQuery;
    }
    
    // Modal state and settings
    let modalInitialized = false;
    let modalElement = null;
    let isModalOpen = false;
    
    // Initialize when document is ready
    $(document).ready(function() {
        initializeKlumpModal();
    });
    
    /**
     * Initialize the Klump modal system
     */
    function initializeKlumpModal() {
        try {
            // Check if we have modal data
            if (typeof klump_modal_data === 'undefined') {
                logError('Klump modal data not available', 'initialization');
                return;
            }
            
            // Create modal HTML structure
            createModalStructure();
            
            // Bind click events to Klump ads
            bindClickEvents();
            
            // Bind modal close events
            bindCloseEvents();
            
            // Bind keyboard events
            bindKeyboardEvents();
            
            modalInitialized = true;
            logDebug('Klump modal initialized successfully');
            
        } catch (error) {
            logError(error, 'initialization');
        }
    }
    
    /**
     * Create the modal HTML structure and append to body
     */
    function createModalStructure() {
        if (modalElement) {
            return; // Already created
        }
        
        const modalHTML = `
            <div id="klump-youtube-modal" class="klump-modal-overlay" style="display: none;">
                <div class="klump-modal-container">
                    <div class="klump-modal-header">
                        <h3 class="klump-modal-title">Learn More About Klump</h3>
                        <button type="button" class="klump-modal-close" aria-label="Close modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="klump-modal-body">
                        <div class="klump-loading-indicator">
                            <div class="klump-spinner"></div>
                            <p>Loading video...</p>
                        </div>
                        <div class="klump-video-container" style="display: none;">
                            <!-- YouTube iframe will be inserted here -->
                        </div>
                        <div class="klump-error-container" style="display: none;">
                            <p class="klump-error-message">Unable to load video. Please try again later.</p>
                            <button type="button" class="klump-retry-btn">Retry</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHTML);
        modalElement = $('#klump-youtube-modal');
    }
    
    /**
     * Bind click events to Klump ad elements
     */
    function bindClickEvents() {
        // Use event delegation for dynamic elements
        $(document).on('click', '.klump-modal-trigger, .klump-product-ad', function(e) {
            e.preventDefault();
            
            try {
                const element = $(this);
                const youtubeUrl = element.data('youtube-url') || klump_modal_data.youtube_url;
                
                if (!youtubeUrl) {
                    logDebug('No YouTube URL available, modal not shown');
                    return;
                }
                
                openModal(youtubeUrl);
                
            } catch (error) {
                logError(error, 'click_handler');
            }
        });
    }
    
    /**
     * Bind modal close events
     */
    function bindCloseEvents() {
        // Close button
        $(document).on('click', '.klump-modal-close', function(e) {
            e.preventDefault();
            closeModal();
        });
        
        // Overlay click (outside modal)
        $(document).on('click', '.klump-modal-overlay', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Retry button
        $(document).on('click', '.klump-retry-btn', function(e) {
            e.preventDefault();
            const youtubeUrl = klump_modal_data.youtube_url;
            if (youtubeUrl) {
                loadVideo(youtubeUrl);
            }
        });
    }
    
    /**
     * Bind keyboard events
     */
    function bindKeyboardEvents() {
        $(document).on('keydown', function(e) {
            if (isModalOpen && e.key === 'Escape') {
                closeModal();
            }
        });
    }
    
    /**
     * Open the modal with YouTube video
     */
    function openModal(youtubeUrl) {
        if (!modalElement) {
            logError('Modal element not found', 'open_modal');
            return;
        }
        
        try {
            // Validate YouTube URL
            const embedUrl = convertToEmbedUrl(youtubeUrl);
            if (!embedUrl) {
                logError('Invalid YouTube URL: ' + youtubeUrl, 'open_modal');
                showError('Invalid video URL');
                return;
            }
            
            // Show modal
            modalElement.fadeIn(300);
            isModalOpen = true;
            
            // Prevent body scroll
            $('body').addClass('klump-modal-open');
            
            // Show loading state
            showLoading();
            
            // Load video
            loadVideo(embedUrl);
            
            logDebug('Modal opened with URL: ' + embedUrl);
            
        } catch (error) {
            logError(error, 'open_modal');
            showError('Unable to open video');
        }
    }
    
    /**
     * Close the modal
     */
    function closeModal() {
        if (!modalElement || !isModalOpen) {
            return;
        }
        
        try {
            modalElement.fadeOut(300);
            isModalOpen = false;
            
            // Restore body scroll
            $('body').removeClass('klump-modal-open');
            
            // Clear video to stop playback
            $('.klump-video-container').html('').hide();
            
            // Reset to loading state for next open
            showLoading();
            
            logDebug('Modal closed');
            
        } catch (error) {
            logError(error, 'close_modal');
        }
    }
    
    /**
     * Load YouTube video in modal
     */
    function loadVideo(embedUrl) {
        try {
            const iframe = `
                <iframe width="100%" height="400"
                        src="${embedUrl}?autoplay=1&rel=0&modestbranding=1"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                </iframe>
            `;
            
            // Hide loading and error states
            $('.klump-loading-indicator').hide();
            $('.klump-error-container').hide();
            
            // Show video container and insert iframe
            $('.klump-video-container').html(iframe).show();
            
            logDebug('Video loaded successfully: ' + embedUrl);
            
        } catch (error) {
            logError(error, 'load_video');
            showError('Failed to load video');
        }
    }
    
    /**
     * Show loading state
     */
    function showLoading() {
        $('.klump-video-container').hide();
        $('.klump-error-container').hide();
        $('.klump-loading-indicator').show();
    }
    
    /**
     * Show error state
     */
    function showError(message) {
        $('.klump-loading-indicator').hide();
        $('.klump-video-container').hide();
        $('.klump-error-message').text(message);
        $('.klump-error-container').show();
    }
    
    /**
     * Convert YouTube URL to embed format
     */
    function convertToEmbedUrl(url) {
        if (!url) {
            return null;
        }
        
        // If already embed format, return as is
        if (url.includes('/embed/')) {
            return url;
        }
        
        // Extract video ID from various YouTube URL formats
        let videoId = null;
        
        // Standard YouTube URLs
        let match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/);
        if (match) {
            videoId = match[1];
        }
        
        if (!videoId) {
            return null;
        }
        
        return `https://www.youtube.com/embed/${videoId}`;
    }
    
    /**
     * Log debug message
     */
    function logDebug(message) {
        if (klump_modal_data && klump_modal_data.debug_mode) {
            console.log('Klump Modal Debug:', message);
        }
    }
    
    /**
     * Log error message
     */
    function logError(error, context) {
        console.error('Klump Modal Error [' + context + ']:', error);
        
        // Send to server if debug mode is enabled
        if (klump_modal_data && klump_modal_data.debug_mode && klump_modal_data.ajax_url) {
            sendErrorToServer(error, context);
        }
    }
    
    /**
     * Send error to server for logging
     */
    function sendErrorToServer(error, context) {
        try {
            const errorMessage = error.message || error.toString();
            
            $.ajax({
                url: klump_modal_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'klump_log_modal_error',
                    error: errorMessage,
                    context: context,
                    nonce: klump_modal_data.nonce
                },
                timeout: 5000
            }).fail(function() {
                // Silent fail for error logging
            });
        } catch (e) {
            // Silent fail
        }
    }
    
    // Expose public methods for debugging
    if (klump_modal_data && klump_modal_data.debug_mode) {
        window.KlumpModal = {
            open: openModal,
            close: closeModal,
            initialized: function() { return modalInitialized; }
        };
    }
    
})(jQuery);
