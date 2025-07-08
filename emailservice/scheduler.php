<?php
/**
 * Campaign Scheduler
 * Processes scheduled campaigns that are due to be sent
 * This script should be run via cron job every minute
 */
require_once 'database.php';
require_once 'campaign-api.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logFile = __DIR__ . '/scheduler.log';
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
    echo "[$timestamp] $message\n";
}

try {
    $pdo = getDatabaseConnection();
    
    logMessage("Scheduler started - checking for due campaigns...");
    
    // Find campaigns that are scheduled and due to be sent
    $stmt = $pdo->prepare("
        SELECT id, subject, scheduled_at, timezone 
        FROM campaigns 
        WHERE status = 'scheduled' 
        AND scheduled_at IS NOT NULL 
        AND scheduled_at <= NOW()
        ORDER BY scheduled_at ASC
    ");
    $stmt->execute();
    $dueCampaigns = $stmt->fetchAll();
    
    if (empty($dueCampaigns)) {
        logMessage("No campaigns due for sending.");
        exit(0);
    }
    
    logMessage("Found " . count($dueCampaigns) . " campaign(s) due for sending.");
    
    foreach ($dueCampaigns as $campaign) {
        try {
            logMessage("Processing campaign #{$campaign['id']}: '{$campaign['subject']}'");
            
            // Send the campaign
            $result = sendCampaign($pdo, ['campaign_id' => $campaign['id']]);
            
            logMessage("Campaign #{$campaign['id']} sent successfully. Sent: {$result['sent_count']}, Failed: {$result['failed_count']}");
            
        } catch (Exception $e) {
            logMessage("Error sending campaign #{$campaign['id']}: " . $e->getMessage());
            
            // Mark campaign as failed
            $stmt = $pdo->prepare("UPDATE campaigns SET status = 'failed' WHERE id = ?");
            $stmt->execute([$campaign['id']]);
        }
    }
    
    logMessage("Scheduler completed.");
    
} catch (Exception $e) {
    logMessage("Scheduler error: " . $e->getMessage());
    exit(1);
}
?>