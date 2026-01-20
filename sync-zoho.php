<?php
/**
 * Standalone Zoho Sync Script
 * Visit this file directly to sync programs from Zoho
 */

// Load WordPress
require_once __DIR__ . '/../../wp-config.php';

// Set execution time
set_time_limit(300);
ini_set('max_execution_time', 300);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Zoho Sync Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .progress { background: #f0f0f0; padding: 20px; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Zoho Data Sync Tool</h1>
    
    <?php if (!isset($_POST['action'])): ?>
        <div class="progress">
            <h3>Choose an action:</h3>
            <form method="post">
                <button type="submit" name="action" value="test">Test Zoho Connection</button>
                <button type="submit" name="action" value="sync" style="margin-left: 10px;">Run Full Sync</button>
            </form>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_POST['action']) && $_POST['action'] === 'test'): ?>
        <div class="progress">
            <h3>Testing Zoho Connection...</h3>
            <?php
            try {
                echo "<p class='info'>Initializing Zoho service...</p>";
                $zoho = new \SIT\Search\Services\Zoho();
                echo "<p class='success'>✓ Zoho service initialized</p>";
                
                echo "<p class='info'>Getting access token...</p>";
                $access_token = $zoho->get_access_token();
                
                if ($access_token) {
                    echo "<p class='success'>✓ Access token obtained: " . substr($access_token, 0, 20) . "...</p>";
                    echo "<p class='success'><strong>Connection test PASSED!</strong></p>";
                    echo "<form method='post'><button type='submit' name='action' value='sync'>Proceed with Sync</button></form>";
                } else {
                    echo "<p class='error'>❌ Failed to get access token</p>";
                }
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
                echo "<p class='error'>Stack trace: " . $e->getTraceAsString() . "</p>";
            }
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_POST['action']) && $_POST['action'] === 'sync'): ?>
        <div class="progress">
            <h3>Running Zoho Sync...</h3>
            <?php
            try {
                echo "<p class='info'>Starting sync process...</p>";
                flush();
                
                echo "<p class='info'>Syncing programs from Zoho CRM...</p>";
                flush();
                
                // Run the sync with smaller batches
                \SIT\Search\Modules\Program::sync(1, 25, 150);
                
                echo "<p class='success'>✓ Sync completed successfully!</p>";
                echo "<p class='success'><strong>New programs have been imported from Zoho!</strong></p>";
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Sync failed: " . $e->getMessage() . "</p>";
                echo "<p class='error'>Stack trace: " . $e->getTraceAsString() . "</p>";
            }
            ?>
            
            <p><a href="sync-zoho.php">Run another sync</a></p>
        </div>
    <?php endif; ?>
    
</body>
</html>
