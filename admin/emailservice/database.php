<?php
/**
 * Database connection for the email service.
 *
 * Thin wrapper around the canonical connection in database/db.php so the whole
 * application shares a single PDO factory (DRY). The function name is kept for
 * backward compatibility with the email service callers.
 */
require_once __DIR__ . '/../../database/db.php';

function getDatabaseConnection(): PDO {
    return db();
}
