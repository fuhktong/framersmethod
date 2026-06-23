<?php
/**
 * Double opt-in confirmation landing page.
 * Activates the subscriber whose confirm_token matches the link.
 */
require_once __DIR__ . '/../database/db.php';

$token = $_GET['token'] ?? '';
$confirmed = false;

if ($token !== '') {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id FROM subscribers WHERE confirm_token = ? AND status = 'pending'");
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        if ($row) {
            $pdo->prepare("UPDATE subscribers SET status = 'active', confirm_token = NULL, updated_at = NOW() WHERE id = ?")
                ->execute([$row['id']]);
            $confirmed = true;
        }
    } catch (Throwable $e) {
        error_log('Confirm error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $confirmed ? 'Subscription confirmed' : 'Confirmation'; ?> — The Framers' Method</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            font-family: 'Avenir Next', Futura, sans-serif;
            background: #f5f4ef;
            color: #1a1a1a;
        }
        .confirm-card { max-width: 460px; text-align: center; }
        .confirm-card h1 { font-size: 1.5rem; margin: 0 0 0.75rem; }
        .confirm-card p { color: #555; line-height: 1.6; margin: 0 0 1.5rem; }
        .confirm-card a {
            display: inline-block;
            background: #1a1a1a;
            color: #fff;
            text-decoration: none;
            padding: 0.7rem 1.4rem;
            border-radius: 5px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="confirm-card">
        <?php if ($confirmed): ?>
            <h1>You're subscribed! 🎉</h1>
            <p>Thanks for confirming. You'll now receive new essays from The Framers' Method.</p>
        <?php else: ?>
            <h1>This link isn't valid</h1>
            <p>The confirmation link is invalid or has already been used. Please try subscribing again.</p>
        <?php endif; ?>
        <a href="/">Back to home</a>
    </div>
</body>
</html>
