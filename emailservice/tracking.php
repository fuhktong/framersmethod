<?php
/**
 * Email Tracking System
 * Handles open tracking (pixel) and click tracking
 */
require_once 'database.php';

// Handle open tracking
if (isset($_GET['open'])) {
    handleOpenTracking();
}

// Handle click tracking
if (isset($_GET['click'])) {
    handleClickTracking();
}

/**
 * Handle email open tracking via 1x1 pixel
 */
function handleOpenTracking() {
    $token = $_GET['open'] ?? '';
    
    if (empty($token)) {
        outputTrackingPixel();
        return;
    }
    
    try {
        $pdo = getDatabaseConnection();
        
        // Decode tracking token (format: base64(campaign_id:subscriber_id))
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);
        
        if (count($parts) !== 2) {
            outputTrackingPixel();
            return;
        }
        
        $campaign_id = (int)$parts[0];
        $subscriber_id = (int)$parts[1];
        
        // Check if this send exists
        $stmt = $pdo->prepare("
            SELECT id FROM campaign_sends 
            WHERE campaign_id = ? AND subscriber_id = ? AND status = 'sent'
        ");
        $stmt->execute([$campaign_id, $subscriber_id]);
        $send = $stmt->fetch();
        
        if (!$send) {
            outputTrackingPixel();
            return;
        }
        
        // Update open tracking if not already opened
        $stmt = $pdo->prepare("
            UPDATE campaign_sends 
            SET opened_at = NOW() 
            WHERE campaign_id = ? AND subscriber_id = ? AND opened_at IS NULL
        ");
        $stmt->execute([$campaign_id, $subscriber_id]);
        
        // Update campaign stats if this was a new open
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("
                UPDATE campaigns 
                SET total_opened = (
                    SELECT COUNT(*) FROM campaign_sends 
                    WHERE campaign_id = ? AND opened_at IS NOT NULL
                )
                WHERE id = ?
            ");
            $stmt->execute([$campaign_id, $campaign_id]);
        }
        
    } catch (Exception $e) {
        error_log("Open tracking error: " . $e->getMessage());
    }
    
    outputTrackingPixel();
}

/**
 * Handle click tracking and redirect
 */
function handleClickTracking() {
    $token = $_GET['click'] ?? '';
    $url = $_GET['url'] ?? '';
    
    if (empty($token) || empty($url)) {
        http_response_code(400);
        echo 'Invalid tracking parameters';
        return;
    }
    
    try {
        $pdo = getDatabaseConnection();
        
        // Decode tracking token (format: base64(campaign_id:subscriber_id))
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);
        
        if (count($parts) !== 2) {
            redirectToUrl($url);
            return;
        }
        
        $campaign_id = (int)$parts[0];
        $subscriber_id = (int)$parts[1];
        
        // Check if this send exists
        $stmt = $pdo->prepare("
            SELECT id FROM campaign_sends 
            WHERE campaign_id = ? AND subscriber_id = ? AND status = 'sent'
        ");
        $stmt->execute([$campaign_id, $subscriber_id]);
        $send = $stmt->fetch();
        
        if (!$send) {
            redirectToUrl($url);
            return;
        }
        
        // Update click tracking if not already clicked
        $stmt = $pdo->prepare("
            UPDATE campaign_sends 
            SET clicked_at = NOW() 
            WHERE campaign_id = ? AND subscriber_id = ? AND clicked_at IS NULL
        ");
        $stmt->execute([$campaign_id, $subscriber_id]);
        
        // Update campaign stats if this was a new click
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("
                UPDATE campaigns 
                SET total_clicked = (
                    SELECT COUNT(*) FROM campaign_sends 
                    WHERE campaign_id = ? AND clicked_at IS NOT NULL
                )
                WHERE id = ?
            ");
            $stmt->execute([$campaign_id, $campaign_id]);
        }
        
        // Log click event
        $stmt = $pdo->prepare("
            INSERT INTO click_tracking (campaign_id, subscriber_id, url, clicked_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$campaign_id, $subscriber_id, $url]);
        
    } catch (Exception $e) {
        error_log("Click tracking error: " . $e->getMessage());
    }
    
    redirectToUrl($url);
}

/**
 * Output 1x1 transparent tracking pixel
 */
function outputTrackingPixel() {
    header('Content-Type: image/gif');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // 1x1 transparent GIF
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

/**
 * Redirect to the original URL
 */
function redirectToUrl($url) {
    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo 'Invalid URL';
        return;
    }
    
    // Redirect
    header('Location: ' . $url, true, 302);
    exit;
}

/**
 * Generate tracking token for campaign and subscriber
 */
function generateTrackingToken($campaign_id, $subscriber_id) {
    return base64_encode($campaign_id . ':' . $subscriber_id);
}

/**
 * Add tracking pixel to HTML email content
 */
function addTrackingPixel($content, $campaign_id, $subscriber_id) {
    $token = generateTrackingToken($campaign_id, $subscriber_id);
    $base_url = $_ENV['BASE_URL'] ?? 'https://localhost';
    $tracking_url = rtrim($base_url, '/') . "/emailservice/tracking.php?open=" . urlencode($token);
    
    $pixel = '<img src="' . htmlspecialchars($tracking_url) . '" width="1" height="1" style="display:none;" alt="">';
    
    // Try to add before closing body tag, otherwise append
    if (stripos($content, '</body>') !== false) {
        $content = str_ireplace('</body>', $pixel . '</body>', $content);
    } else {
        $content .= $pixel;
    }
    
    return $content;
}

/**
 * Wrap links in email content for click tracking
 */
function wrapLinksForTracking($content, $campaign_id, $subscriber_id, $is_html = true) {
    $token = generateTrackingToken($campaign_id, $subscriber_id);
    $base_url = $_ENV['BASE_URL'] ?? 'https://localhost';
    $tracking_base = rtrim($base_url, '/') . "/emailservice/tracking.php?click=" . urlencode($token) . "&url=";
    
    if ($is_html) {
        // HTML content - wrap all <a> tags
        $content = preg_replace_callback(
            '/<a\s+([^>]*?)href=["\']([^"\']*?)["\']([^>]*?)>/i',
            function($matches) use ($tracking_base) {
                $before = $matches[1];
                $url = $matches[2];
                $after = $matches[3];
                
                // Skip if already a tracking link or anchor link
                if (strpos($url, 'tracking.php') !== false || strpos($url, '#') === 0) {
                    return $matches[0];
                }
                
                $tracked_url = $tracking_base . urlencode($url);
                return '<a ' . $before . 'href="' . htmlspecialchars($tracked_url) . '"' . $after . '>';
            },
            $content
        );
    } else {
        // Plain text - wrap URLs
        $content = preg_replace_callback(
            '/https?:\/\/[^\s]+/',
            function($matches) use ($tracking_base) {
                $url = $matches[0];
                return $tracking_base . urlencode($url);
            },
            $content
        );
    }
    
    return $content;
}
?>