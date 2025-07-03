-- Email Service Database Schema
-- Run this SQL to create the required tables

-- Table: subscribers
-- Stores email addresses and subscriber information
CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    unsubscribe_token VARCHAR(64) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_unsubscribe_token (unsubscribe_token)
);

-- Table: campaigns  
-- Stores email campaign information
CREATE TABLE IF NOT EXISTS campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(500) NOT NULL,
    content LONGTEXT NOT NULL,
    content_type ENUM('html', 'plain') DEFAULT 'html',
    from_name VARCHAR(255) DEFAULT 'The Framers Method',
    status ENUM('draft', 'sending', 'sent', 'failed') DEFAULT 'draft',
    total_recipients INT DEFAULT 0,
    total_sent INT DEFAULT 0,
    total_opened INT DEFAULT 0,
    total_clicked INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_sent_at (sent_at)
);

-- Table: campaign_sends
-- Tracks individual email sends for analytics
CREATE TABLE IF NOT EXISTS campaign_sends (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    subscriber_id INT NOT NULL,
    status ENUM('sent', 'failed', 'bounced') DEFAULT 'sent',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    opened_at TIMESTAMP NULL DEFAULT NULL,
    clicked_at TIMESTAMP NULL DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE,
    
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_subscriber_id (subscriber_id),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at),
    
    UNIQUE KEY unique_campaign_subscriber (campaign_id, subscriber_id)
);

-- Table: unsubscribes
-- Tracks unsubscribe history and reasons
CREATE TABLE IF NOT EXISTS unsubscribes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    reason VARCHAR(500) DEFAULT NULL,
    campaign_id INT DEFAULT NULL,
    unsubscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL,
    
    INDEX idx_email (email),
    INDEX idx_unsubscribed_at (unsubscribed_at)
);

-- Create triggers to automatically generate unsubscribe tokens
DELIMITER //

CREATE TRIGGER before_insert_subscribers
BEFORE INSERT ON subscribers
FOR EACH ROW
BEGIN
    IF NEW.unsubscribe_token = '' OR NEW.unsubscribe_token IS NULL THEN
        SET NEW.unsubscribe_token = SHA2(CONCAT(NEW.email, NOW(), RAND()), 256);
    END IF;
END//

DELIMITER ;

-- Insert sample data (optional - remove if not needed)
-- Sample subscribers
INSERT IGNORE INTO subscribers (email, name, status) VALUES
('test1@example.com', 'Test User 1', 'active'),
('test2@example.com', 'Test User 2', 'active'),
('admin@framersmethod.com', 'Admin User', 'active');

-- Sample campaign
INSERT IGNORE INTO campaigns (subject, content, content_type, status, total_recipients) VALUES
('Welcome to The Framers Method', 
'<h1>Welcome!</h1><p>Thank you for subscribing to The Framers Method newsletter.</p>', 
'html', 
'draft', 
0);