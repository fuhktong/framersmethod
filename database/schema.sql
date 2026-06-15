-- Run against the `framersmethod` database
-- mysql -u root -p framersmethod < database/schema.sql

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100)    NOT NULL UNIQUE,
    password_hash VARCHAR(255)    NOT NULL,
    created_at    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS posts (
    id           INT UNSIGNED                    AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255)                    NOT NULL,
    slug         VARCHAR(255)                    NOT NULL UNIQUE,
    excerpt      TEXT                            DEFAULT NULL,
    body         LONGTEXT                        NOT NULL,
    category     VARCHAR(100)                    DEFAULT NULL,
    status       ENUM('draft', 'published')      NOT NULL DEFAULT 'draft',
    author_id    INT UNSIGNED                    NOT NULL,
    published_at TIMESTAMP                       NULL DEFAULT NULL,
    created_at   TIMESTAMP                       DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP                       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_slug   (slug),
    INDEX idx_feed   (status, published_at),
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);
