<?php
// Define the path to the SQLite database
$dbPath = "/var/www/html/database.db"; // Adjust the path as needed

// Create the database file if it doesn't exist
if (!file_exists($dbPath)) {
    // Attempt to create the database file
    touch($dbPath);
}

// Try to open the SQLite database
if ($db = new SQLite3($dbPath)) {
    // Create the table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS list (ip TEXT NOT NULL, port INTEGER NOT NULL, is_official BOOL NOT NULL)");

    // Query the database for all servers
    $q = $db->query("SELECT * FROM list");
    if ($q) {
        $result = array('success' => true, 'servers' => array());
        
        // Fetch the results and prepare them for output
        for ($i = 0; $item = $q->fetchArray(SQLITE3_ASSOC); $i++) {
            $item['is_official'] = ($item['is_official'] != 0);
            $result['servers'][$i] = $item;
        }
        
        // Output the result as JSON
        echo json_encode($result);
        
        // Finalize the query
        $q->finalize();
        exit();
    }
}

// If we reach this point, there was an error
header("HTTP/1.0 500 Internal Server Error");
$result = array('success' => false, 'errorCode' => 1, 'error' => 'Failed to query the masterlist database');
echo json_encode($result);
?>
