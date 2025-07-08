-- Bounce Handling Database Schema
-- Add these tables to handle email bounces

-- Table: email_bounces
-- Tracks individual bounce events
CREATE TABLE IF NOT EXISTS email_bounces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    subscriber_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    bounce_type ENUM('hard', 'soft', 'complaint') NOT NULL,
    bounce_reason VARCHAR(500) DEFAULT NULL,
    bounce_code VARCHAR(50) DEFAULT NULL,
    bounce_message TEXT DEFAULT NULL,
    bounced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE,
    
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_subscriber_id (subscriber_id),
    INDEX idx_email (email),
    INDEX idx_bounce_type (bounce_type),
    INDEX idx_bounced_at (bounced_at)
);

-- Add bounce tracking fields to subscribers table
ALTER TABLE subscribers 
ADD COLUMN bounce_count INT DEFAULT 0,
ADD COLUMN last_bounce_at TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN bounce_status ENUM('active', 'soft_bounce', 'hard_bounce', 'complaint') DEFAULT 'active',
ADD INDEX idx_bounce_status (bounce_status);

-- Add bounce tracking fields to campaign_sends table
ALTER TABLE campaign_sends 
ADD COLUMN bounce_type ENUM('hard', 'soft', 'complaint') DEFAULT NULL,
ADD COLUMN bounce_reason VARCHAR(500) DEFAULT NULL,
ADD COLUMN bounced_at TIMESTAMP NULL DEFAULT NULL;

-- Update campaigns table to track bounces
ALTER TABLE campaigns 
ADD COLUMN total_bounced INT DEFAULT 0,
ADD COLUMN total_complaints INT DEFAULT 0;