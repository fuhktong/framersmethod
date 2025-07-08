<?php
/**
 * CSV Upload Handler for Subscribers
 */
require_once 'database.php';

header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $pdo = getDatabaseConnection();
    
    // Check if file was uploaded
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }
    
    $file = $_FILES['csv_file'];
    
    // Validate file type
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($fileExtension !== 'csv') {
        throw new Exception('Only CSV files are allowed');
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File size too large. Maximum 5MB allowed');
    }
    
    // Read and process CSV file
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        throw new Exception('Failed to read CSV file');
    }
    
    $results = [
        'total_rows' => 0,
        'processed' => 0,
        'added' => 0,
        'skipped' => 0,
        'errors' => []
    ];
    
    $rowNumber = 0;
    $headers = null;
    
    while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
        $rowNumber++;
        $results['total_rows']++;
        
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }
        
        // First row might be headers
        if ($rowNumber === 1) {
            // Check if first row looks like headers
            $firstCell = strtolower(trim($row[0]));
            if (in_array($firstCell, ['email', 'email address', 'e-mail'])) {
                $headers = array_map('strtolower', array_map('trim', $row));
                continue;
            }
        }
        
        $results['processed']++;
        
        try {
            // Parse row data
            $email = '';
            $name = '';
            
            if ($headers) {
                // Use headers to map columns
                $emailIndex = array_search('email', $headers);
                if ($emailIndex === false) {
                    $emailIndex = array_search('email address', $headers);
                }
                if ($emailIndex === false) {
                    $emailIndex = array_search('e-mail', $headers);
                }
                
                $nameIndex = array_search('name', $headers);
                if ($nameIndex === false) {
                    $nameIndex = array_search('full name', $headers);
                }
                if ($nameIndex === false) {
                    $nameIndex = array_search('first name', $headers);
                }
                
                $email = isset($row[$emailIndex]) ? trim($row[$emailIndex]) : '';
                $name = isset($row[$nameIndex]) ? trim($row[$nameIndex]) : '';
            } else {
                // Assume first column is email, second is name
                $email = isset($row[0]) ? trim($row[0]) : '';
                $name = isset($row[1]) ? trim($row[1]) : '';
            }
            
            // Validate email
            if (empty($email)) {
                $results['errors'][] = "Row $rowNumber: Email is required";
                $results['skipped']++;
                continue;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $results['errors'][] = "Row $rowNumber: Invalid email '$email'";
                $results['skipped']++;
                continue;
            }
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM subscribers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $results['errors'][] = "Row $rowNumber: Email '$email' already exists";
                $results['skipped']++;
                continue;
            }
            
            // Generate unsubscribe token
            $unsubscribe_token = hash('sha256', $email . time() . rand());
            
            // Insert subscriber
            $stmt = $pdo->prepare("
                INSERT INTO subscribers (email, name, status, unsubscribe_token, subscribed_at) 
                VALUES (?, ?, 'active', ?, NOW())
            ");
            $stmt->execute([$email, $name ?: null, $unsubscribe_token]);
            
            $results['added']++;
            
        } catch (Exception $e) {
            $results['errors'][] = "Row $rowNumber: " . $e->getMessage();
            $results['skipped']++;
        }
    }
    
    fclose($handle);
    
    // Prepare response message
    $message = "CSV upload completed!\n";
    $message .= "Total rows: {$results['total_rows']}\n";
    $message .= "Processed: {$results['processed']}\n";
    $message .= "Added: {$results['added']}\n";
    $message .= "Skipped: {$results['skipped']}";
    
    if (!empty($results['errors']) && count($results['errors']) <= 10) {
        $message .= "\n\nErrors:\n" . implode("\n", $results['errors']);
    } elseif (!empty($results['errors'])) {
        $message .= "\n\nShowing first 10 errors:\n" . implode("\n", array_slice($results['errors'], 0, 10));
        $message .= "\n... and " . (count($results['errors']) - 10) . " more errors";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $results
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>