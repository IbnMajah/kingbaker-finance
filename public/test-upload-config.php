<?php

/**
 * Upload Configuration Test Script
 * 
 * This script helps test if the server configuration is properly set up
 * for handling multiple file uploads.
 * 
 * Access this file via: https://yourdomain.com/test-upload-config.php
 * 
 * IMPORTANT: Remove this file after testing for security reasons!
 */

// Security check - only allow access from localhost or specific IPs
$allowed_ips = ['127.0.0.1', '::1', 'localhost'];
$client_ip = $_SERVER['REMOTE_ADDR'] ?? '';

if (!in_array($client_ip, $allowed_ips) && !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'])) {
    die('Access denied. This script should only be accessed from localhost.');
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Upload Configuration Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .config {
            background: #f5f5f5;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .success {
            color: green;
        }

        .warning {
            color: orange;
        }

        .error {
            color: red;
        }

        .test-form {
            background: #e8f4f8;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <h1>Upload Configuration Test</h1>

    <h2>Current PHP Configuration</h2>
    <div class="config">
        <strong>upload_max_filesize:</strong>
        <span class="<?= ini_get('upload_max_filesize') >= '10M' ? 'success' : 'error' ?>">
            <?= ini_get('upload_max_filesize') ?>
        </span>
        <?= ini_get('upload_max_filesize') < '10M' ? ' (Should be at least 10M)' : '' ?>
    </div>

    <div class="config">
        <strong>post_max_size:</strong>
        <span class="<?= ini_get('post_max_size') >= '50M' ? 'success' : 'error' ?>">
            <?= ini_get('post_max_size') ?>
        </span>
        <?= ini_get('post_max_size') < '50M' ? ' (Should be at least 50M)' : '' ?>
    </div>

    <div class="config">
        <strong>max_file_uploads:</strong>
        <span class="<?= ini_get('max_file_uploads') >= 20 ? 'success' : 'error' ?>">
            <?= ini_get('max_file_uploads') ?>
        </span>
        <?= ini_get('max_file_uploads') < 20 ? ' (Should be at least 20)' : '' ?>
    </div>

    <div class="config">
        <strong>max_execution_time:</strong>
        <span class="<?= ini_get('max_execution_time') >= 300 ? 'success' : 'warning' ?>">
            <?= ini_get('max_execution_time') ?> seconds
        </span>
        <?= ini_get('max_execution_time') < 300 ? ' (Should be at least 300)' : '' ?>
    </div>

    <div class="config">
        <strong>memory_limit:</strong>
        <span class="<?= ini_get('memory_limit') >= '256M' ? 'success' : 'warning' ?>">
            <?= ini_get('memory_limit') ?>
        </span>
        <?= ini_get('memory_limit') < '256M' ? ' (Should be at least 256M)' : '' ?>
    </div>

    <div class="config">
        <strong>max_input_time:</strong>
        <span class="<?= ini_get('max_input_time') >= 300 ? 'success' : 'warning' ?>">
            <?= ini_get('max_input_time') ?> seconds
        </span>
        <?= ini_get('max_input_time') < 300 ? ' (Should be at least 300)' : '' ?>
    </div>

    <h2>Server Information</h2>
    <div class="config">
        <strong>PHP Version:</strong> <?= PHP_VERSION ?><br>
        <strong>Server Software:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?><br>
        <strong>Document Root:</strong> <?= $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' ?><br>
        <strong>Script Name:</strong> <?= $_SERVER['SCRIPT_NAME'] ?? 'Unknown' ?>
    </div>

    <h2>Upload Test</h2>
    <div class="test-form">
        <form method="post" enctype="multipart/form-data">
            <p><strong>Test multiple file upload:</strong></p>
            <input type="file" name="test_files[]" multiple accept="image/*,.pdf">
            <br><br>
            <button type="submit" name="test_upload">Test Upload</button>
        </form>

        <?php if (isset($_POST['test_upload'])): ?>
            <h3>Upload Test Results</h3>
            <?php if (isset($_FILES['test_files'])): ?>
                <p><strong>Files received:</strong> <?= count($_FILES['test_files']['name']) ?></p>
                <ul>
                    <?php foreach ($_FILES['test_files']['name'] as $index => $name): ?>
                        <li>
                            <strong><?= htmlspecialchars($name) ?></strong>
                            (<?= number_format($_FILES['test_files']['size'][$index] / 1024, 2) ?> KB)
                            -
                            <?php if ($_FILES['test_files']['error'][$index] === UPLOAD_ERR_OK): ?>
                                <span class="success">Upload successful</span>
                            <?php else: ?>
                                <span class="error">Upload failed (Error: <?= $_FILES['test_files']['error'][$index] ?>)</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="error">No files were uploaded.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <h2>Recommendations</h2>
    <div class="config">
        <?php if (ini_get('upload_max_filesize') < '10M' || ini_get('post_max_size') < '50M'): ?>
            <p class="error">
                <strong>Action Required:</strong> Your upload limits are too low for handling multiple image uploads.
                Please update your PHP configuration or .htaccess file as described in PRODUCTION_CONFIG.md
            </p>
        <?php elseif (ini_get('max_file_uploads') < 20): ?>
            <p class="warning">
                <strong>Warning:</strong> Your max_file_uploads setting may limit the number of files that can be uploaded at once.
            </p>
        <?php else: ?>
            <p class="success">
                <strong>Good:</strong> Your configuration appears to be suitable for handling multiple file uploads.
            </p>
        <?php endif; ?>
    </div>

    <p><strong>Note:</strong> Remember to delete this test file after checking your configuration!</p>
</body>

</html>