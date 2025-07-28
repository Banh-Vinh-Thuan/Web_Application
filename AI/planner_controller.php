<?php
// Set a default timezone to avoid warnings
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Include required files
require_once __DIR__ . '/AIPlannerService.php';
require_once __DIR__ . '/ExcelExporter.php';

// Get the requested action from the query string
$action = $_GET['action'] ?? '';

// Simple router based on the action
switch ($action) {
    case 'generate':
        handle_generate_plan();
        break;
    case 'export':
        handle_export_plan();
        break;
    default:
        header('Content-Type: application/json');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Invalid action specified.']);
        break;
}

/**
 * Handles the logic for generating a travel plan.
 */
function handle_generate_plan() {
    header('Content-Type: application/json');
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body, true);

    if (empty($data['query'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Query cannot be empty.']);
        return;
    }

    $planner_service = new AIPlannerService();
    $result = $planner_service->generatePlan($data['query']);
    
    // Log the request for debugging (optional)
    file_put_contents(
        __DIR__ . '/logs/llm_requests.log',
        date('Y-m-d H:i:s') . ' - Query: ' . $data['query'] . ' - Response: ' . json_encode($result) . "\n",
        FILE_APPEND
    );
    
    echo json_encode($result);
}

/**
 * Handles the logic for exporting the plan to CSV.
 */
function handle_export_plan() {
    $request_body = file_get_contents('php://input');
    $plan_data = json_decode($request_body, true);

    if (empty($plan_data) || empty($plan_data['plan'])) {
        header('Content-Type: application/json');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'No plan data provided for export.']);
        return;
    }
    
    $exporter = new ExcelExporter();
    $exporter->exportAsCsv($plan_data);
}
?>