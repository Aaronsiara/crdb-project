<?php
/**
 * dashboard.php
 *
 * Simple PHP dashboard to display customer segmentation results produced by
 * segment_any_data.py (or segmentation.py). Point $outputDir at the folder
 * that contains segmented_data.csv, segment_profiles.csv, elbow_plot.png,
 * and pca_clusters.png.
 *
 * Run locally with PHP's built-in server:
 *     php -S localhost:8000
 * then open http://localhost:8000/dashboard.php
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
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($headers, $row);
        }
        fclose($handle);
    }
    return ['headers' => $headers ?? [], 'rows' => $rows];
}

$profiles = readCsv($profilesPath);
$data     = readCsv($dataPath);

// Preview only the first N rows of the raw segmented data (it can be large)
$previewLimit = 25;
$dataPreviewRows = $data ? array_slice($data['rows'], 0, $previewLimit) : [];
$totalRecords = $data ? count($data['rows']) : 0;

function fmtNum($val) {
    if (is_numeric($val)) {
        return number_format((float)$val, 2);
    }
    return htmlspecialchars($val);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CRDB Customer Segmentation Dashboard</title>
<style>
     body {
        margin: 0;
        font-family: Arial, sans-serif;
    }

    /* Sidebar styling */
    .sidebar {
        height: 100%; /* Full height */
        width: 220px; /* Sidebar width */
        position: fixed; /* Stay in place */
        top: 0;
        left: 0;
        background-color: #111; /* Dark background */
        padding-top: 20px;
        overflow-x: hidden; /* Disable horizontal scroll */
    }

    /* Sidebar links */
    .sidebar a {
        padding: 12px 16px;
        text-decoration: none;
        font-size: 18px;
        color: white;
        display: block;
        transition: background 0.3s;
    }

    /* Hover effect */
    .sidebar a:hover {
        background-color: #575757;
    }

    /* Main content */
    .main-content {
        margin-left: 220px; /* Same as sidebar width */
        padding: 20px;
    }

    /* Responsive: stack sidebar on top for small screens */
    @media screen and (max-width: 600px) {
        .sidebar {
            width: 100%;
            height: auto;
            position: relative;
        }
        .main-content {
            margin-left: 0;
        }
    }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <a href="#home">Home</a>
    <a href="#services">Services</a>
    <a href="#about">About</a>
    <a href="#contact">Contact</a>
</div>

    :root {
        --crdb-green: #357600;
        --crdb-gold: #f2a900;
        --bg: #f5f7fa;
        --card-bg: #ffffff;
        --border: #e1e5eb;
        --text: #1a1a1a;
        --text-muted: #5c6470;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        background: var(--bg);
        color: var(--text);
    }
    header {
        background: var(--crdb-blue);
        color: white;
        padding: 24px 32px;
        border-bottom: 4px solid var(--crdb-gold);
    }
    header h1 {
        margin: 0 0 4px 0;
        font-size: 22px;
    }
    header p {
        margin: 0;
        color: #cfe0f5;
        font-size: 14px;
    }
    main {
        max-width: 1100px;
        margin: 0 auto;
        padding: 24px 32px 64px;
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
        color: var(--crdb-blue);
        border-bottom: 1px solid var(--border);
        padding-bottom: 10px;
    }
    .stat-row {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }
    .stat-box {
        background: #f0f4f9;
        border-radius: 6px;
        padding: 12px 18px;
        flex: 1;
        min-width: 140px;
    }
    .stat-box .label {
        font-size: 12px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .stat-box .value {
        font-size: 22px;
        font-weight: 600;
        color: var(--crdb-blue);
    }
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 13px;
    }
    th, td {
        padding: 8px 10px;
        border-bottom: 1px solid var(--border);
        text-align: left;
        white-space: nowrap;
    }
    th {
        background: #f0f4f9;
        color: var(--crdb-blue);
        font-weight: 600;
        position: sticky;
        top: 0;
    }
    tr:hover { background: #fafbfc; }
    .table-wrap {
        overflow-x: auto;
        max-height: 480px;
        overflow-y: auto;
        border: 1px solid var(--border);
        border-radius: 6px;
    }
    .img-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .img-grid img {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 6px;
    }
    .missing {
        color: #b23b3b;
        background: #fdecec;
        border: 1px solid #f5c2c2;
        border-radius: 6px;
        padding: 14px 18px;
        font-size: 14px;
    }
    .segment-pill {
        display: inline-block;
        background: var(--crdb-gold);
        color: #3a2a00;
        font-weight: 600;
        border-radius: 12px;
        padding: 2px 10px;
        font-size: 12px;
    }
    footer {
        text-align: center;
        color: var(--text-muted);
        font-size: 12px;
        padding: 20px;
    }
    @media (max-width: 700px) {
        .img-grid { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>

<header>
    <h1>CRDB Bank Tanzania — Customer Segmentation Dashboard</h1>
    <p>Data Department Field Work · PCA + K-Means Segmentation Results</p>
</header>

<main>

    <div class="card">
        <h2>Overview</h2>
        <div class="stat-row">
            <div class="stat-box">
                <div class="label">Total Records</div>
                <div class="value"><?= $totalRecords ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Segments Found</div>
                <div class="value"><?= $profiles ? count($profiles['rows']) : '—' ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Output Folder</div>
                <div class="value" style="font-size:14px;"><?= htmlspecialchars($outputDir) ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Segment Profiles</h2>
        <?php if ($profiles && count($profiles['rows']) > 0): ?>
            <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <?php foreach ($profiles['headers'] as $h): ?>
                            <th><?= htmlspecialchars($h) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($profiles['rows'] as $row): ?>
                        <tr>
                            <?php foreach ($profiles['headers'] as $h): ?>
                                <td>
                                    <?php if ($h === 'segment'): ?>
                                        <span class="segment-pill">Segment <?= htmlspecialchars($row[$h]) ?></span>
                                    <?php else: ?>
                                        <?= fmtNum($row[$h]) ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php else: ?>
            <div class="missing">
                segment_profiles.csv not found in <code><?= htmlspecialchars($outputDir) ?></code>.
                Run segment_any_data.py first.
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Visualizations</h2>
        <div class="img-grid">
            <div>
                <?php if (file_exists($elbowImg)): ?>
                    <img src="<?= htmlspecialchars($elbowImg) ?>" alt="Elbow plot">
                <?php else: ?>
                    <div class="missing">elbow_plot.png not found.</div>
                <?php endif; ?>
            </div>
            <div>
                <?php if (file_exists($pcaImg)): ?>
                    <img src="<?= htmlspecialchars($pcaImg) ?>" alt="PCA cluster plot">
                <?php else: ?>
                    <div class="missing">pca_clusters.png not found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Segmented Data Preview <?= $totalRecords > $previewLimit ? "(first {$previewLimit} of {$totalRecords} rows)" : '' ?></h2>
        <?php if ($data && count($dataPreviewRows) > 0): ?>
            <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <?php foreach ($data['headers'] as $h): ?>
                            <th><?= htmlspecialchars($h) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataPreviewRows as $row): ?>
                        <tr>
                            <?php foreach ($data['headers'] as $h): ?>
                                <td><?= htmlspecialchars($row[$h]) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php else: ?>
            <div class="missing">
                segmented_data.csv not found in <code><?= htmlspecialchars($outputDir) ?></code>.
                Run segment_any_data.py first.
            </div>
        <?php endif; ?>
    </div>

</main>

<footer>
    Generated by dashboard.php — CRDB Data Department Field Work Project
</footer>

</body>
</html>
