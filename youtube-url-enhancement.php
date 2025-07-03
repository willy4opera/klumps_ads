<?php
/**
 * YouTube URL validation and handling functions for Klump Product Ads
 * This will be integrated into the main admin-interface.php
 */

// Add to get_current_settings() method
'youtube_url' => get_option('klump_ads_youtube_url', ''),

// Add to save_settings() method  
'klump_ads_youtube_url' => $this->sanitize_youtube_url($_POST['klump_ads_youtube_url']),

// YouTube URL validation and sanitization method
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
        return '';
    }
    
    // Convert to embed format for consistency
    return 'https://www.youtube.com/embed/' . $validation['video_id'];
}

private function get_youtube_video_id($url) {
    $validation = $this->validate_youtube_url($url);
    return $validation['video_id'];
}

// HTML for YouTube URL field (to be inserted around line 143)
?>
                        
                        <!-- YouTube Video Section -->
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

