<?php
/**
 * dashboard.php
 *
 * Enhanced PHP dashboard with sidebar navigation and a direct CSV data ingestion engine.
 *
 * Run locally:
 *     php -S localhost:8000
 */

// ---- Configuration: point this at your output folder ----
$outputDir = 'customers_synthetic_segmentation_output';

$profilesPath = $outputDir . '/segment_profiles.csv';
$dataPath     = $outputDir . '/segmented_data.csv';
$elbowImg     = $outputDir . '/elbow_plot.png';
$pcaImg       = $outputDir . '/pca_clusters.png';

/**
 * Read a CSV file into an associative array (first row = headers).
 */
function readCsv($path) {
    if (!file_exists($path)) {
        return null;
    }
    $rows = [];
    if (($handle = fopen($path, 'r')) !== false) {
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            return null;
        }
        while (($row = fgetcsv($handle)) !== false) {
            if (count($headers) === count($row)) {
                $rows[] = array_combine($headers, $row);
            }
        }
        fclose($handle);
    }
    return ['headers' => $headers ?? [], 'rows' => $rows];
}

$profiles = readCsv($profilesPath);
$data     = readCsv($dataPath);

// ---- Direct CSV Row Ingestion Engine ----
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert_data') {
    if ($data && !empty($data['headers'])) {
        $newRow = [];
        foreach ($data['headers'] as $header) {
            if ($header === 'segment') {
                // Default manually inserted records to -1 (Unclustered) until re-segmented via Python
                $newRow[] = -1;
            } else {
                // Sanitize input text, set blank if not provided
                $newRow[] = isset($_POST['field_' . str_replace(' ', '_', $header)]) ? trim($_POST['field_' . str_replace(' ', '_', $header)]) : '';
            }
        }
        
        if (file_exists($dataPath) && is_writable($dataPath)) {
            $handle = fopen($dataPath, 'a');
            if ($handle !== false) {
                fputcsv($handle, $newRow);
                fclose($handle);
                $successMessage = "Record inserted successfully! Refreshing dataset...";
                // Reload dataset to display newly added row in grid layout
                $data = readCsv($dataPath);
            } else {
                $errorMessage = "Failed to access system file data streams.";
            }
        } else {
            $errorMessage = "Target dataset file path is non-writable or does not exist.";
        }
    } else {
        $errorMessage = "Cannot insert record. Base dataset headers could not be determined.";
    }
}

// Preview only the first N rows of the raw segmented data
$previewLimit = 25;
$dataPreviewRows = $data ? array_slice($data['rows'], 0, $previewLimit) : [];
$totalRecords = $data ? count($data['rows']) : 0;

function fmtNum($val) {
    if (is_numeric($val)) {
        return number_format((float)$val, 2);
    }
    return htmlspecialchars($val ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CRDB Customer Segmentation Dashboard</title>
<style>
    :root {
        --crdb-green: #357600;
        --crdb-gold: #f2a900;
        --bg: #f5f7fa;
        --card-bg: #ffffff;
        --border: #e1e5eb;
        --text: #1a1a1a;
        --text-muted: #5c6470;
        --sidebar-width: 320px;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        background: var(--bg);
        color: var(--text);
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    header {
        background: var(--crdb-green);
        color: white;
        padding: 20px 32px;
        border-bottom: 4px solid var(--crdb-gold);
        z-index: 10;
    }
    header h1 { margin: 0 0 4px 0; font-size: 22px; }
    header p { margin: 0; color: #cfe0f5; font-size: 14px; }
    
    /* Layout Container splits Sidebar and Main Panels */
    .app-container {
        display: flex;
        flex: 1;
        position: relative;
    }
    
    /* Responsive Collapsible Sidebar */
    aside {
        width: var(--sidebar-width);
        background: #ffffff;
        border-right: 1px solid var(--border);
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 24px;
        overflow-y: auto;
    }
    
    main {
        flex: 1;
        padding: 24px 32px 64px;
        overflow-x: hidden;
    }
    
    .card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 20px 24px;
        margin-bottom: 24px;
    }
    .card h2 {
        margin-top: 0;
        font-size: 16px;
        color: var(--crdb-green);
        border-bottom: 1px solid var(--border);
        padding-bottom: 10px;
    }
    
    /* Sidebar Widgets & Input Form Elements */
    .stat-box {
        background: #f0f4f9;
        border-radius: 6px;
        padding: 12px 18px;
        margin-bottom: 12px;
    }
    .stat-box .label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .stat-box .value { font-size: 20px; font-weight: 600; color: var(--crdb-green); word-break: break-all; }
    
    .form-group {
        margin-bottom: 14px;
    }
    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
        color: var(--text);
        text-transform: capitalize;
    }
    .form-group input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 13px;
    }
    .form-group input:focus {
        border-color: var(--crdb-green);
        outline: none;
    }
    .btn-submit {
        background: var(--crdb-green);
        color: white;
        border: none;
        padding: 10px 16px;
        font-weight: 600;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
        font-size: 13px;
        transition: background 0.2s;
    }
    .btn-submit:hover { background: #285700; }
    
    .alert {
        padding: 10px 14px;
        border-radius: 4px;
        font-size: 13px;
        margin-bottom: 14px;
    }
    .alert-success { background: #e6f4ea; color: #137333; border: 1px solid #ceead6; }
    .alert-error { background: #fce8e6; color: #c5221f; border: 1px solid #fad2cf; }

    table { border-collapse: collapse; width: 100%; font-size: 13px; }
    th, td { padding: 8px 10px; border-bottom: 1px solid var(--border); text-align: left; white-space: nowrap; }
    th { background: #f0f4f9; color: var(--crdb-green); font-weight: 600; position: sticky; top: 0; }
    tr:hover { background: #fafbfc; }
    
    .table-wrap {
        overflow-x: auto;
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid var(--border);
        border-radius: 6px;
    }
    .img-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .img-grid img { width: 100%; border: 1px solid var(--border); border-radius: 6px; }
    .missing { color: #b23b3b; background: #fdecec; border: 1px solid #f5c2c2; border-radius: 6px; padding: 14px 18px; font-size: 14px; }
    .segment-pill { display: inline-block; background: var(--crdb-gold); color: #3a2a00; font-weight: 600; border-radius: 12px; padding: 2px 10px; font-size: 12px; }
    
    footer { text-align: center; color: var(--text-muted); font-size: 12px; padding: 20px; border-top: 1px solid var(--border); background: #ffffff; }
    
    @media (max-width: 900px) {
        .app-container { flex-direction: column; }
        aside { width: 100%; border-right: none; border-bottom: 1px solid var(--border); }
        .img-grid { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>

<header>
    <h1>CRDB Bank Tanzania — Customer Segmentation Dashboard</h1>
    <p>Data Department Field Work · PCA + K-Means Segmentation Results</p>
</header>

<div class="app-container">

    <!-- SIDEBAR NAVIGATION PANEL -->
    <aside>
        <div>
            <div class="stat-box">
                <div class="label">Total Records</div>
                <div class="value"><?= $totalRecords ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Segments Found</div>
                <div class="value"><?= $profiles ? count($profiles['rows']) : '—' ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Output Directory</div>
                <div class="value" style="font-size:12px; font-weight:normal; color:var(--text-muted);"><?= htmlspecialchars($outputDir) ?></div>
            </div>
        </div>

        <!-- NEW RECORD DATA INGESTION FORM -->
        <div style="border-top: 1px solid var(--border); padding-top: 20px;">
            <h3 style="margin-top:0; font-size:14px; color:var(--crdb-green); text-transform:uppercase;">Insert New Record</h3>
            
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success"><?= $successMessage ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-error"><?= $errorMessage ?></div>
            <?php endif; ?>

            <?php if ($data && !empty($data['headers'])): ?>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="insert_data">
                    
                    <?php foreach ($data['headers'] as $header): ?>
                        <?php if ($header === 'segment') continue; // Hidden field handled by server system logic ?>
                        <div class="form-group">
