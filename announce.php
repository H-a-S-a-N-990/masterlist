<?php
// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("HTTP/1.0 405 Method Not Allowed");
    exit();
}

// Check if the user agent is valid
if (!isset($_SERVER["HTTP_USER_AGENT"]) || $_SERVER["HTTP_USER_AGENT"] != "VCMP/0.4") {
    header("HTTP/1.0 403 Forbidden");
    exit();
}

// Validate the port parameter
if (!isset($_POST["port"]) || !is_numeric($_POST["port"])) {
    bad_request();
}

$port = (int)$_POST["port"];
if ($port <= 0) {
    bad_request();
}

// Get the client's IP address
$ip = $_SERVER["REMOTE_ADDR"];

// Attempt to open a UDP socket
$socket = @fsockopen("udp://" . $ip, $port);
if ($socket) {
    // Prepare the packet
    $packet  = "VCMP";
    foreach (explode('.', $ip) as $octet) {
        $packet .= chr((int)$octet);
    }
    $packet .= chr($port & 0xFF);
    $packet .= chr(($port >> 8) & 0xFF);
    
    fwrite($socket, $packet . "i");

    // Set a timeout for the socket
    stream_set_timeout($socket, 5);
    $magic = fread($socket, 4);

    // Check if the socket timed out or if the magic number is empty
    $info = stream_get_meta_data($socket);
    fclose($socket);

    if ($info["timed_out"] || empty($magic)) {
        header("HTTP/1.0 408 Request Timeout");
        exit();
    }

    // Validate the magic number
    if ($magic !== "MP04") {
        bad_request();
    }

    // Add the IP and port to the database
    AddToDB($ip, $port);
} else {
    header("HTTP/1.0 500 Internal Server Error");
}

// Function to handle bad requests
function bad_request() {
    header("HTTP/1.0 400 Bad Request");
    exit();
}

// Function to add the IP and port to the database
function AddToDB($ip, $port) {
    // Connect to the SQLite database
    $db = new SQLite3(".database.db");
    if (!$db) {
        header("HTTP/1.0 500 Internal Server Error");
        exit();
    }

    // Create the table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS list (ip TEXT, port INTEGER, is_official BOOL NOT NULL)");

    // Use prepared statements to prevent SQL injection
    $stmt = $db->prepare("SELECT * FROM list WHERE ip = :ip AND port = :port");
    $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
    $stmt->bindValue(':port', $port, SQLITE3_INTEGER);
    $result = $stmt->execute();

    // Check if the record exists
    if (!$result->fetchArray()) {
        // Insert or replace the record
        $stmt = $db->prepare("REPLACE INTO list (ip, port, is_official) VALUES (:ip, :port, 0)");
        $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
        $stmt->bindValue(':port', $port, SQLITE3_INTEGER);
        $stmt->execute();
    }

    // Finalize the statement
    $stmt->close();
}
?>
