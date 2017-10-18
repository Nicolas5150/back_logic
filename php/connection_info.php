<?php
  function connectToDatabase() {
    // Server Info
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "ni927795";

    // Create and check connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
  }
?>
