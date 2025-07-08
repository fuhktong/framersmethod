<?php
/**
 * Export Subscribers to CSV
 */
require_once 'database.php';

// Get parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';
$format = $_GET['format'] ?? 'csv';
$ids = $_GET['ids'] ?? '';

try {
    $pdo = getDatabaseConnection();
    
    // Build WHERE clause
    $where = [];
    $params = [];
    
    // If specific IDs are provided, use those
    if (!empty($ids)) {
        $idArray = array_map('intval', explode(',', $ids));
        $placeholders = str_repeat('?,', count($idArray) - 1) . '?';
        $where[] = "id IN ($placeholders)";
        $params = array_merge($params, $idArray);
    } else {
        // Otherwise use search and status filters
        if (!empty($search)) {
            $where[] = "(email LIKE ? OR name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($status !== 'all') {
            $where[] = "status = ?";
            $params[] = $status;
        }
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get all subscribers
    $sql = "
        SELECT email, name, status, subscribed_at, updated_at
        FROM subscribers 
        $whereClause
        ORDER BY subscribed_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $subscribers = $stmt->fetchAll();
    
    if (empty($subscribers)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No subscribers to export']);
        exit();
    }
    
    // Generate filename
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "subscribers_export_$timestamp.csv";
    
    // Set headers for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    // Create CSV output
    $output = fopen('php://output', 'w');
    
    // Write CSV header
    fputcsv($output, ['Email', 'Name', 'Status', 'Subscribed Date', 'Last Updated']);
    
    // Write data rows
    foreach ($subscribers as $subscriber) {
        fputcsv($output, [
            $subscriber['email'],
            $subscriber['name'] ?: '',
            $subscriber['status'],
            date('Y-m-d H:i:s', strtotime($subscriber['subscribed_at'])),
            date('Y-m-d H:i:s', strtotime($subscriber['updated_at']))
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>