<?php
/**
 * Email Templates API
 * Manages email templates for campaigns
 */
require_once 'database.php';

header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getDatabaseConnection();
    $response = ['success' => true];
    
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? 'list';
            
            if ($action === 'list') {
                $category = $_GET['category'] ?? 'all';
                $response['data'] = getTemplates($pdo, $category);
            } elseif ($action === 'get') {
                $id = (int)($_GET['id'] ?? 0);
                if ($id > 0) {
                    $response['data'] = getTemplate($pdo, $id);
                } else {
                    throw new Exception('Template ID required');
                }
            } elseif ($action === 'preview') {
                $id = (int)($_GET['id'] ?? 0);
                $variables = $_GET['variables'] ?? '{}';
                if ($id > 0) {
                    $response['data'] = previewTemplate($pdo, $id, $variables);
                } else {
                    throw new Exception('Template ID required');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;
            
        case 'POST':
            $response['data'] = createTemplate($pdo, $input);
            break;
            
        case 'PUT':
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $response['data'] = updateTemplate($pdo, $id, $input);
            } else {
                throw new Exception('Template ID required for update');
            }
            break;
            
        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $response['data'] = deleteTemplate($pdo, $id);
            } else {
                throw new Exception('Template ID required for delete');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    http_response_code(400);
}

echo json_encode($response);

/**
 * Get list of templates
 */
function getTemplates($pdo, $category = 'all') {
    $where = $category !== 'all' ? 'WHERE category = ? AND is_active = TRUE' : 'WHERE is_active = TRUE';
    $params = $category !== 'all' ? [$category] : [];
    
    $stmt = $pdo->prepare("
        SELECT id, name, description, category, thumbnail, created_at, updated_at
        FROM email_templates 
        $where
        ORDER BY category, name
    ");
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

/**
 * Get single template
 */
function getTemplate($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT * FROM email_templates WHERE id = ? AND is_active = TRUE
    ");
    $stmt->execute([$id]);
    $template = $stmt->fetch();
    
    if (!$template) {
        throw new Exception('Template not found');
    }
    
    // Parse JSON variables
    if ($template['template_variables']) {
        $template['template_variables'] = json_decode($template['template_variables'], true);
    }
    
    return $template;
}

/**
 * Preview template with variables
 */
function previewTemplate($pdo, $id, $variablesJson) {
    $template = getTemplate($pdo, $id);
    $variables = json_decode($variablesJson, true) ?: [];
    
    // Merge with default variables
    $defaultVariables = $template['template_variables'] ?: [];
    $allVariables = array_merge($defaultVariables, $variables);
    
    // Replace template variables
    $html = replaceTemplateVariables($template['template_html'], $allVariables);
    
    return [
        'html' => $html,
        'variables' => $allVariables
    ];
}

/**
 * Create new template
 */
function createTemplate($pdo, $data) {
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $category = $data['category'] ?? 'custom';
    $templateHtml = trim($data['template_html'] ?? '');
    $templateVariables = $data['template_variables'] ?? [];
    $thumbnail = $data['thumbnail'] ?? null;
    
    if (empty($name)) {
        throw new Exception('Template name is required');
    }
    
    if (empty($templateHtml)) {
        throw new Exception('Template HTML is required');
    }
    
    if (!in_array($category, ['newsletter', 'announcement', 'welcome', 'marketing', 'custom'])) {
        throw new Exception('Invalid category');
    }
    
    // Insert template
    $stmt = $pdo->prepare("
        INSERT INTO email_templates (name, description, category, template_html, template_variables, thumbnail, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $name, 
        $description, 
        $category, 
        $templateHtml, 
        json_encode($templateVariables),
        $thumbnail
    ]);
    
    $id = $pdo->lastInsertId();
    
    return getTemplate($pdo, $id);
}

/**
 * Update template
 */
function updateTemplate($pdo, $id, $data) {
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $category = $data['category'] ?? 'custom';
    $templateHtml = trim($data['template_html'] ?? '');
    $templateVariables = $data['template_variables'] ?? [];
    $thumbnail = $data['thumbnail'] ?? null;
    
    if (empty($name)) {
        throw new Exception('Template name is required');
    }
    
    if (empty($templateHtml)) {
        throw new Exception('Template HTML is required');
    }
    
    // Update template
    $stmt = $pdo->prepare("
        UPDATE email_templates 
        SET name = ?, description = ?, category = ?, template_html = ?, template_variables = ?, thumbnail = ?, updated_at = NOW()
        WHERE id = ? AND is_active = TRUE
    ");
    $stmt->execute([
        $name,
        $description,
        $category,
        $templateHtml,
        json_encode($templateVariables),
        $thumbnail,
        $id
    ]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Template not found or update failed');
    }
    
    return getTemplate($pdo, $id);
}

/**
 * Delete template (soft delete)
 */
function deleteTemplate($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE email_templates SET is_active = FALSE WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Template not found');
    }
    
    return ['message' => 'Template deleted successfully'];
}

/**
 * Replace template variables in HTML
 */
function replaceTemplateVariables($html, $variables) {
    foreach ($variables as $key => $value) {
        $html = str_replace('{{' . $key . '}}', htmlspecialchars($value), $html);
    }
    
    // Remove any unreplaced variables
    $html = preg_replace('/\{\{[^}]+\}\}/', '', $html);
    
    return $html;
}

/**
 * Apply template to campaign content
 */
function applyCampaignTemplate($templateId, $campaignData) {
    global $pdo;
    
    $template = getTemplate($pdo, $templateId);
    
    // Extract variables from campaign data
    $variables = [
        'subject' => $campaignData['subject'] ?? '',
        'content' => $campaignData['content'] ?? '',
        'from_name' => $campaignData['from_name'] ?? 'The Framers Method'
    ];
    
    // Merge with template variables
    if ($template['template_variables']) {
        $variables = array_merge($template['template_variables'], $variables);
    }
    
    // Replace variables in template
    return replaceTemplateVariables($template['template_html'], $variables);
}
?>