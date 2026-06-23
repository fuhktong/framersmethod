<?php
/**
 * Authenticated image upload endpoint.
 * Accepts a POST field "image", validates it is a real image, stores it under
 * /uploads/YYYY/, and returns { success, url } as JSON.
 */
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json');

/** Smaller of the server's upload_max_filesize / post_max_size, in bytes. */
function max_upload_bytes(): int
{
    $toBytes = static function (string $val): int {
        $val = trim($val);
        if ($val === '') {
            return 0;
        }
        $n = (int) $val;
        switch (strtolower(substr($val, -1))) {
            case 'g': $n *= 1024 * 1024 * 1024; break;
            case 'm': $n *= 1024 * 1024; break;
            case 'k': $n *= 1024; break;
        }
        return $n;
    };
    $limits = array_filter([
        $toBytes((string) ini_get('upload_max_filesize')),
        $toBytes((string) ini_get('post_max_size')),
    ]);
    return $limits ? min($limits) : 2 * 1024 * 1024;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$file = $_FILES['image'] ?? null;

if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
    exit;
}

// PHP rejected the file for exceeding its configured size limit
if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
    echo json_encode(['success' => false, 'message' => 'Image is too large. The server limit is ' . ini_get('upload_max_filesize') . 'B.']);
    exit;
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload failed (error code ' . $file['error'] . ').']);
    exit;
}

if ($file['size'] > max_upload_bytes()) {
    echo json_encode(['success' => false, 'message' => 'Image is too large. The server limit is ' . ini_get('upload_max_filesize') . 'B.']);
    exit;
}

// Confirm the file really is an image, and map it to a safe extension
$info = getimagesize($file['tmp_name']);
$allowed = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG  => 'png',
    IMAGETYPE_GIF  => 'gif',
    IMAGETYPE_WEBP => 'webp',
];
if ($info === false || !isset($allowed[$info[2]])) {
    echo json_encode(['success' => false, 'message' => 'Unsupported file type. Use JPG, PNG, GIF, or WEBP.']);
    exit;
}
$ext = $allowed[$info[2]];

// Build a safe, unique filename from the original name
$base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', pathinfo($file['name'], PATHINFO_FILENAME)));
$base = trim($base, '-');
if ($base === '') {
    $base = 'image';
}
$filename = $base . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

// Store under /uploads/YYYY/
$relDir = '/uploads/' . date('Y');
$absDir = __DIR__ . '/..' . $relDir;
if (!is_dir($absDir) && !mkdir($absDir, 0775, true)) {
    echo json_encode(['success' => false, 'message' => 'Could not create the upload directory.']);
    exit;
}

if (!move_uploaded_file($file['tmp_name'], $absDir . '/' . $filename)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save the uploaded file.']);
    exit;
}

echo json_encode(['success' => true, 'url' => $relDir . '/' . $filename]);
