-- Email Service Database Schema
-- Run this SQL to create the required tables

-- Table: subscribers
-- Stores email addresses and subscriber information
CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'unsubscribed', 'paused') DEFAULT 'active',
    email_frequency ENUM('daily', 'weekly', 'biweekly', 'monthly') DEFAULT 'daily',
    unsubscribe_token VARCHAR(64) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resume_date DATETIME NULL DEFAULT NULL,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_unsubscribe_token (unsubscribe_token),
    INDEX idx_resume_date (resume_date)
);

-- Table: campaigns  
-- Stores email campaign information
CREATE TABLE IF NOT EXISTS campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(500) NOT NULL,
    content LONGTEXT NOT NULL,
    content_type ENUM('html', 'plain') DEFAULT 'html',
    from_name VARCHAR(255) DEFAULT 'The Framers Method',
    status ENUM('draft', 'scheduled', 'sending', 'sent', 'failed', 'cancelled') DEFAULT 'draft',
    total_recipients INT DEFAULT 0,
    total_sent INT DEFAULT 0,
    total_opened INT DEFAULT 0,
    total_clicked INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    scheduled_at TIMESTAMP NULL DEFAULT NULL,
    timezone VARCHAR(50) NULL DEFAULT NULL,
    
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_sent_at (sent_at),
    INDEX idx_scheduled_at (scheduled_at)
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

-- Table: click_tracking
-- Tracks individual link clicks for detailed analytics
CREATE TABLE IF NOT EXISTS click_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    subscriber_id INT NOT NULL,
    url VARCHAR(2048) NOT NULL,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE,
    
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_subscriber_id (subscriber_id),
    INDEX idx_clicked_at (clicked_at)
);

-- Table: email_templates
-- Stores reusable email templates
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    category ENUM('newsletter', 'announcement', 'welcome', 'marketing', 'custom') DEFAULT 'custom',
    template_html LONGTEXT NOT NULL,
    template_variables JSON DEFAULT NULL,
    thumbnail VARCHAR(500) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category (category),
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at)
);

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

-- Sample email templates
INSERT IGNORE INTO email_templates (name, description, category, template_html, template_variables) VALUES
('Simple Newsletter', 'Clean, professional newsletter template', 'newsletter', 
'<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{subject}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #45698c; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #45698c; }
        .content { padding: 20px 0; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{logo_text}}</div>
        </div>
        <div class="content">
            <h1>{{headline}}</h1>
            <p>{{content}}</p>
        </div>
        <div class="footer">
            <p>{{footer_text}}</p>
        </div>
    </div>
</body>
</html>', 
''{"logo_text": "The Framers Method", "headline": "Newsletter Title", "content": "Your newsletter content goes here...", "footer_text": "Thank you for being a subscriber!"}''),

('Welcome Email', 'Warm welcome message for new subscribers', 'welcome',
'<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{subject}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f9f9f9; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #45698c, #6b8db5); color: white; text-align: center; padding: 40px 20px; }
        .welcome-icon { font-size: 48px; margin-bottom: 20px; }
        .content { padding: 40px 20px; text-align: center; }
        .cta-button { display: inline-block; background: #45698c; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="welcome-icon">🎉</div>
            <h1>{{welcome_title}}</h1>
            <p>{{welcome_subtitle}}</p>
        </div>
        <div class="content">
            <h2>{{main_heading}}</h2>
            <p>{{main_content}}</p>
            <a href="{{cta_link}}" class="cta-button">{{cta_text}}</a>
        </div>
        <div class="footer">
            <p>{{footer_text}}</p>
        </div>
    </div>
</body>
</html>',
''{"welcome_title": "Welcome!", "welcome_subtitle": "We''re excited to have you on board", "main_heading": "Here''s what to expect...", "main_content": "You''ll receive valuable insights about constitutional principles and governance.", "cta_text": "Visit Our Website", "cta_link": "https://example.com", "footer_text": "Questions? Just reply to this email!"}''),

('Announcement', 'Bold announcement template for important news', 'announcement',
'<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{subject}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .alert-banner { background: #dc3545; color: white; text-align: center; padding: 10px; font-weight: bold; }
        .header { background: #45698c; color: white; text-align: center; padding: 30px 20px; }
        .content { padding: 30px 20px; }
        .highlight-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="alert-banner">{{alert_text}}</div>
        <div class="header">
            <h1>{{announcement_title}}</h1>
        </div>
        <div class="content">
            <p>{{opening_text}}</p>
            <div class="highlight-box">
                <strong>{{highlight_title}}</strong><br>
                {{highlight_content}}
            </div>
            <p>{{closing_text}}</p>
        </div>
        <div class="footer">
            <p>{{footer_text}}</p>
        </div>
    </div>
</body>
</html>',
''{"alert_text": "IMPORTANT ANNOUNCEMENT", "announcement_title": "Breaking News", "opening_text": "We have important news to share with you...", "highlight_title": "Key Information:", "highlight_content": "Details about the announcement go here.", "closing_text": "Thank you for your attention to this matter.", "footer_text": "Stay informed with The Framers Method"}'');