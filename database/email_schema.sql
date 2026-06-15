-- Email service schema. Runs against the same `framersmethod` database as
-- database/schema.sql (the app shares one connection via database/db.php).
--   mysql -u root -p framersmethod < database/email_schema.sql
--
-- Bounce-tracking columns are folded directly into the base tables below, so
-- this single file is the complete, authoritative email schema.

-- Subscribers ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscribers (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    email             VARCHAR(255) NOT NULL UNIQUE,
    name              VARCHAR(255) DEFAULT NULL,
    status            ENUM('active', 'unsubscribed', 'paused') DEFAULT 'active',
    email_frequency   ENUM('daily', 'weekly', 'biweekly', 'monthly') DEFAULT 'daily',
    unsubscribe_token VARCHAR(64) NOT NULL UNIQUE,
    subscribed_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resume_date       DATETIME NULL DEFAULT NULL,
    bounce_count      INT DEFAULT 0,
    last_bounce_at    TIMESTAMP NULL DEFAULT NULL,
    bounce_status     ENUM('active', 'soft_bounce', 'hard_bounce', 'complaint') DEFAULT 'active',

    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_unsubscribe_token (unsubscribe_token),
    INDEX idx_resume_date (resume_date),
    INDEX idx_bounce_status (bounce_status)
);

-- Auto-generate an unsubscribe token when one is not supplied
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

-- Campaigns -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS campaigns (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    subject          VARCHAR(500) NOT NULL,
    content          LONGTEXT NOT NULL,
    content_type     ENUM('html', 'plain') DEFAULT 'html',
    from_name        VARCHAR(255) DEFAULT 'The Framers Method',
    status           ENUM('draft', 'scheduled', 'sending', 'sent', 'failed', 'cancelled') DEFAULT 'draft',
    list_id          INT DEFAULT NULL,
    total_recipients INT DEFAULT 0,
    total_sent       INT DEFAULT 0,
    total_opened     INT DEFAULT 0,
    total_clicked    INT DEFAULT 0,
    total_bounced    INT DEFAULT 0,
    total_complaints INT DEFAULT 0,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at          TIMESTAMP NULL DEFAULT NULL,
    scheduled_at     TIMESTAMP NULL DEFAULT NULL,
    timezone         VARCHAR(50) NULL DEFAULT NULL,

    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_sent_at (sent_at),
    INDEX idx_scheduled_at (scheduled_at)
);

-- Subscriber lists ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscriber_lists (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS subscriber_list_memberships (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    subscriber_id INT NOT NULL,
    list_id       INT NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE,
    FOREIGN KEY (list_id)       REFERENCES subscriber_lists(id) ON DELETE CASCADE,

    UNIQUE KEY unique_subscriber_list (subscriber_id, list_id),
    INDEX idx_subscriber_id (subscriber_id),
    INDEX idx_list_id (list_id)
);

-- The application always expects a default "All Subscribers" list to exist
INSERT IGNORE INTO subscriber_lists (name, description)
VALUES ('All Subscribers', 'Every active subscriber');

-- Campaign sends (per-recipient delivery + engagement) ----------------------
CREATE TABLE IF NOT EXISTS campaign_sends (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id   INT NOT NULL,
    subscriber_id INT NOT NULL,
    status        ENUM('sent', 'failed', 'bounced') DEFAULT 'sent',
    sent_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    opened_at     TIMESTAMP NULL DEFAULT NULL,
    clicked_at    TIMESTAMP NULL DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    bounce_type   ENUM('hard', 'soft', 'complaint') DEFAULT NULL,
    bounce_reason VARCHAR(500) DEFAULT NULL,
    bounced_at    TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (campaign_id)   REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE,

    INDEX idx_campaign_id (campaign_id),
    INDEX idx_subscriber_id (subscriber_id),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at),

    UNIQUE KEY unique_campaign_subscriber (campaign_id, subscriber_id)
);

-- Unsubscribe history -------------------------------------------------------
CREATE TABLE IF NOT EXISTS unsubscribes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(255) NOT NULL,
    reason          VARCHAR(500) DEFAULT NULL,
    campaign_id     INT DEFAULT NULL,
    unsubscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL,

    INDEX idx_email (email),
    INDEX idx_unsubscribed_at (unsubscribed_at)
);

-- Click tracking ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS click_tracking (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id   INT NOT NULL,
    subscriber_id INT NOT NULL,
    url           VARCHAR(2048) NOT NULL,
    clicked_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (campaign_id)   REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE,

    INDEX idx_campaign_id (campaign_id),
    INDEX idx_subscriber_id (subscriber_id),
    INDEX idx_clicked_at (clicked_at)
);

-- Bounce events -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_bounces (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id   INT NOT NULL,
    subscriber_id INT NOT NULL,
    email         VARCHAR(255) NOT NULL,
    bounce_type   ENUM('hard', 'soft', 'complaint') NOT NULL,
    bounce_reason VARCHAR(500) DEFAULT NULL,
    bounce_code   VARCHAR(50) DEFAULT NULL,
    bounce_message TEXT DEFAULT NULL,
    bounced_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (campaign_id)   REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE,

    INDEX idx_campaign_id (campaign_id),
    INDEX idx_subscriber_id (subscriber_id),
    INDEX idx_email (email),
    INDEX idx_bounce_type (bounce_type),
    INDEX idx_bounced_at (bounced_at)
);

-- Reusable email templates --------------------------------------------------
CREATE TABLE IF NOT EXISTS email_templates (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(255) NOT NULL,
    description        TEXT DEFAULT NULL,
    category           ENUM('newsletter', 'announcement', 'welcome', 'marketing', 'custom') DEFAULT 'custom',
    template_html      LONGTEXT NOT NULL,
    template_variables JSON DEFAULT NULL,
    thumbnail          VARCHAR(500) DEFAULT NULL,
    is_active          BOOLEAN DEFAULT TRUE,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_category (category),
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at)
);
