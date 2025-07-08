<?php
/**
 * Analytics API for Email Service
 * Provides campaign performance data and reports
 */
require_once 'database.php';

header('Content-Type: application/json');

// Get the action from request
$action = $_GET['action'] ?? '';
$campaign_id = (int)($_GET['campaign_id'] ?? 0);

try {
    $pdo = getDatabaseConnection();
    $response = ['success' => true];
    
    switch ($action) {
        case 'campaign_report':
            if ($campaign_id > 0) {
                $response['data'] = getCampaignReport($pdo, $campaign_id);
            } else {
                throw new Exception('Campaign ID required');
            }
            break;
            
        case 'overview':
            $response['data'] = getOverviewStats($pdo);
            break;
            
        case 'top_campaigns':
            $limit = (int)($_GET['limit'] ?? 10);
            $response['data'] = getTopCampaigns($pdo, $limit);
            break;
            
        case 'subscriber_engagement':
            $response['data'] = getSubscriberEngagement($pdo);
            break;
            
        case 'click_analytics':
            if ($campaign_id > 0) {
                $response['data'] = getClickAnalytics($pdo, $campaign_id);
            } else {
                $response['data'] = getAllClickAnalytics($pdo);
            }
            break;
            
        case 'performance_trends':
            $days = (int)($_GET['days'] ?? 30);
            $response['data'] = getPerformanceTrends($pdo, $days);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);

/**
 * Get detailed campaign report
 */
function getCampaignReport($pdo, $campaign_id) {
    // Get campaign details
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COUNT(DISTINCT cs.subscriber_id) as actual_recipients,
               COUNT(DISTINCT CASE WHEN cs.status = 'sent' THEN cs.subscriber_id END) as successful_sends,
               COUNT(DISTINCT CASE WHEN cs.status = 'failed' THEN cs.subscriber_id END) as failed_sends,
               COUNT(DISTINCT CASE WHEN cs.opened_at IS NOT NULL THEN cs.subscriber_id END) as unique_opens,
               COUNT(DISTINCT CASE WHEN cs.clicked_at IS NOT NULL THEN cs.subscriber_id END) as unique_clicks
        FROM campaigns c
        LEFT JOIN campaign_sends cs ON c.id = cs.campaign_id
        WHERE c.id = ?
        GROUP BY c.id
    ");
    $stmt->execute([$campaign_id]);
    $campaign = $stmt->fetch();
    
    if (!$campaign) {
        throw new Exception('Campaign not found');
    }
    
    // Calculate rates
    $open_rate = $campaign['successful_sends'] > 0 ? 
        round(($campaign['unique_opens'] / $campaign['successful_sends']) * 100, 2) : 0;
    $click_rate = $campaign['successful_sends'] > 0 ? 
        round(($campaign['unique_clicks'] / $campaign['successful_sends']) * 100, 2) : 0;
    $click_to_open_rate = $campaign['unique_opens'] > 0 ? 
        round(($campaign['unique_clicks'] / $campaign['unique_opens']) * 100, 2) : 0;
    
    $campaign['open_rate'] = $open_rate;
    $campaign['click_rate'] = $click_rate;
    $campaign['click_to_open_rate'] = $click_to_open_rate;
    
    // Get hourly performance data for the first 24 hours
    $stmt = $pdo->prepare("
        SELECT 
            HOUR(sent_at) as hour,
            COUNT(CASE WHEN opened_at IS NOT NULL THEN 1 END) as opens,
            COUNT(CASE WHEN clicked_at IS NOT NULL THEN 1 END) as clicks
        FROM campaign_sends
        WHERE campaign_id = ? AND sent_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY HOUR(sent_at)
        ORDER BY hour
    ");
    $stmt->execute([$campaign_id]);
    $hourly_data = $stmt->fetchAll();
    
    // Get top clicked links
    $stmt = $pdo->prepare("
        SELECT url, COUNT(*) as click_count
        FROM click_tracking
        WHERE campaign_id = ?
        GROUP BY url
        ORDER BY click_count DESC
        LIMIT 10
    ");
    $stmt->execute([$campaign_id]);
    $top_links = $stmt->fetchAll();
    
    // Get engagement timeline
    $stmt = $pdo->prepare("
        SELECT 
            DATE(sent_at) as date,
            COUNT(CASE WHEN opened_at IS NOT NULL THEN 1 END) as opens,
            COUNT(CASE WHEN clicked_at IS NOT NULL THEN 1 END) as clicks
        FROM campaign_sends
        WHERE campaign_id = ?
        GROUP BY DATE(sent_at)
        ORDER BY date
    ");
    $stmt->execute([$campaign_id]);
    $timeline = $stmt->fetchAll();
    
    // Get subscriber engagement levels
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN opened_at IS NOT NULL AND clicked_at IS NOT NULL THEN 'High'
                WHEN opened_at IS NOT NULL THEN 'Medium'
                WHEN status = 'sent' THEN 'Low'
                ELSE 'Failed'
            END as engagement_level,
            COUNT(*) as count
        FROM campaign_sends
        WHERE campaign_id = ?
        GROUP BY engagement_level
    ");
    $stmt->execute([$campaign_id]);
    $engagement_levels = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    return [
        'campaign' => $campaign,
        'hourly_performance' => $hourly_data,
        'top_links' => $top_links,
        'timeline' => $timeline,
        'engagement_levels' => $engagement_levels
    ];
}

/**
 * Get overview statistics
 */
function getOverviewStats($pdo) {
    $stats = [];
    
    // Total campaigns
    $stmt = $pdo->query("SELECT COUNT(*) FROM campaigns");
    $stats['total_campaigns'] = $stmt->fetchColumn();
    
    // Total subscribers
    $stmt = $pdo->query("SELECT COUNT(*) FROM subscribers WHERE status = 'active'");
    $stats['total_subscribers'] = $stmt->fetchColumn();
    
    // Total emails sent
    $stmt = $pdo->query("SELECT COUNT(*) FROM campaign_sends WHERE status = 'sent'");
    $stats['total_emails_sent'] = $stmt->fetchColumn();
    
    // Average open rate
    $stmt = $pdo->query("
        SELECT AVG(
            CASE 
                WHEN total_sent > 0 THEN (total_opened / total_sent) * 100
                ELSE 0
            END
        ) as avg_open_rate
        FROM campaigns 
        WHERE status = 'sent' AND total_sent > 0
    ");
    $stats['avg_open_rate'] = round($stmt->fetchColumn() ?? 0, 2);
    
    // Average click rate
    $stmt = $pdo->query("
        SELECT AVG(
            CASE 
                WHEN total_sent > 0 THEN (total_clicked / total_sent) * 100
                ELSE 0
            END
        ) as avg_click_rate
        FROM campaigns 
        WHERE status = 'sent' AND total_sent > 0
    ");
    $stats['avg_click_rate'] = round($stmt->fetchColumn() ?? 0, 2);
    
    // Recent activity (last 30 days)
    $stmt = $pdo->query("
        SELECT 
            DATE(sent_at) as date,
            COUNT(*) as emails_sent,
            COUNT(CASE WHEN opened_at IS NOT NULL THEN 1 END) as opens,
            COUNT(CASE WHEN clicked_at IS NOT NULL THEN 1 END) as clicks
        FROM campaign_sends
        WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(sent_at)
        ORDER BY date DESC
        LIMIT 30
    ");
    $stats['recent_activity'] = $stmt->fetchAll();
    
    return $stats;
}

/**
 * Get top performing campaigns
 */
function getTopCampaigns($pdo, $limit = 10) {
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.subject,
            c.sent_at,
            c.total_sent,
            c.total_opened,
            c.total_clicked,
            CASE 
                WHEN c.total_sent > 0 THEN ROUND((c.total_opened / c.total_sent) * 100, 2)
                ELSE 0
            END as open_rate,
            CASE 
                WHEN c.total_sent > 0 THEN ROUND((c.total_clicked / c.total_sent) * 100, 2)
                ELSE 0
            END as click_rate
        FROM campaigns c
        WHERE c.status = 'sent' AND c.total_sent > 0
        ORDER BY open_rate DESC, click_rate DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    
    return $stmt->fetchAll();
}

/**
 * Get subscriber engagement statistics
 */
function getSubscriberEngagement($pdo) {
    // Engagement levels
    $stmt = $pdo->query("
        SELECT 
            s.id,
            s.email,
            s.name,
            COUNT(cs.id) as emails_received,
            COUNT(CASE WHEN cs.opened_at IS NOT NULL THEN 1 END) as emails_opened,
            COUNT(CASE WHEN cs.clicked_at IS NOT NULL THEN 1 END) as emails_clicked,
            CASE 
                WHEN COUNT(cs.id) > 0 THEN ROUND((COUNT(CASE WHEN cs.opened_at IS NOT NULL THEN 1 END) / COUNT(cs.id)) * 100, 2)
                ELSE 0
            END as open_rate,
            CASE 
                WHEN COUNT(cs.id) > 0 THEN ROUND((COUNT(CASE WHEN cs.clicked_at IS NOT NULL THEN 1 END) / COUNT(cs.id)) * 100, 2)
                ELSE 0
            END as click_rate
        FROM subscribers s
        LEFT JOIN campaign_sends cs ON s.id = cs.subscriber_id AND cs.status = 'sent'
        WHERE s.status = 'active'
        GROUP BY s.id
        ORDER BY open_rate DESC, click_rate DESC
        LIMIT 100
    ");
    
    return $stmt->fetchAll();
}

/**
 * Get click analytics
 */
function getClickAnalytics($pdo, $campaign_id) {
    $stmt = $pdo->prepare("
        SELECT 
            ct.url,
            COUNT(*) as total_clicks,
            COUNT(DISTINCT ct.subscriber_id) as unique_clicks,
            MIN(ct.clicked_at) as first_click,
            MAX(ct.clicked_at) as last_click
        FROM click_tracking ct
        WHERE ct.campaign_id = ?
        GROUP BY ct.url
        ORDER BY total_clicks DESC
    ");
    $stmt->execute([$campaign_id]);
    
    return $stmt->fetchAll();
}

/**
 * Get all click analytics
 */
function getAllClickAnalytics($pdo) {
    $stmt = $pdo->query("
        SELECT 
            ct.url,
            COUNT(*) as total_clicks,
            COUNT(DISTINCT ct.subscriber_id) as unique_subscribers,
            COUNT(DISTINCT ct.campaign_id) as campaigns_used,
            MIN(ct.clicked_at) as first_click,
            MAX(ct.clicked_at) as last_click
        FROM click_tracking ct
        GROUP BY ct.url
        ORDER BY total_clicks DESC
        LIMIT 50
    ");
    
    return $stmt->fetchAll();
}

/**
 * Get performance trends over time
 */
function getPerformanceTrends($pdo, $days = 30) {
    $stmt = $pdo->prepare("
        SELECT 
            DATE(cs.sent_at) as date,
            COUNT(DISTINCT cs.campaign_id) as campaigns,
            COUNT(cs.id) as emails_sent,
            COUNT(CASE WHEN cs.opened_at IS NOT NULL THEN 1 END) as opens,
            COUNT(CASE WHEN cs.clicked_at IS NOT NULL THEN 1 END) as clicks,
            CASE 
                WHEN COUNT(cs.id) > 0 THEN ROUND((COUNT(CASE WHEN cs.opened_at IS NOT NULL THEN 1 END) / COUNT(cs.id)) * 100, 2)
                ELSE 0
            END as open_rate,
            CASE 
                WHEN COUNT(cs.id) > 0 THEN ROUND((COUNT(CASE WHEN cs.clicked_at IS NOT NULL THEN 1 END) / COUNT(cs.id)) * 100, 2)
                ELSE 0
            END as click_rate
        FROM campaign_sends cs
        WHERE cs.sent_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        GROUP BY DATE(cs.sent_at)
        ORDER BY date DESC
    ");
    $stmt->execute([$days]);
    
    return $stmt->fetchAll();
}
?>