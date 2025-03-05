<?php
$config = include 'config.php';

// Database connection
$db_conn = new mysqli($config['db_servername'], $config['db_username'], $config['db_password'], $config['db_dbname']);
if ($db_conn->connect_error) {
    die("Connection failed: " . $db_conn->connect_error);
}
function setupDB() {
    global $db_conn;
    global $dbname;

    // Create or update database if it does not exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($db_conn->query($sql) === TRUE) {
        echo "Database created/updated successfully";
    } else {
        echo "Error creating/updating database: " . $db_conn->error;
    }

    // Select the database
    $db_conn->select_db($dbname);

    // Create or update staff_time table
    $sql = "CREATE TABLE IF NOT EXISTS presence_staff_time (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        staff_id INT(6) NOT NULL,
        status VARCHAR(50) NOT NULL,
        date_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($db_conn->query($sql) === TRUE) {
        echo "Table presence_staff_time created/updated successfully";
    } else {
        echo "Error creating/updating table presence_staff_time: " . $db_conn->error;
    }

    // Create a rule to delete entries older than a year
    $sql = "CREATE EVENT IF NOT EXISTS delete_old_entries
        ON SCHEDULE EVERY 1 DAY
        DO
            DELETE FROM presence_staff_time WHERE date_time < DATE_SUB(NOW(), INTERVAL 1 YEAR)";
    if ($db_conn->query($sql) === TRUE) {
        echo "Event delete_old_entries created/updated successfully";
    } else {
        echo "Error creating/updating event delete_old_entries: " . $db_conn->error;
    }

    // Create or update staff table
    $sql = "CREATE TABLE IF NOT EXISTS presence_staff (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        firstname VARCHAR(30) NOT NULL,
        lastname VARCHAR(30) NOT NULL
    )";
    if ($db_conn->query($sql) === TRUE) {
        echo "Table presence_staff created/updated successfully";
    } else {
        echo "Error creating/updating table presence_staff: " . $db_conn->error;
    }

    // Create or update visitors table
    $sql = "CREATE TABLE IF NOT EXISTS presence_visitors (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        firstname VARCHAR(30) NOT NULL,
        lastname VARCHAR(30) NOT NULL,
        reason VARCHAR(255) NOT NULL,
        location VARCHAR(255) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        internet_access BOOLEAN NOT NULL,
        internet_code VARCHAR(15) DEFAULT NULL,
        date_time_in TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_time_out TIMESTAMP NULL DEFAULT NULL,
        active BOOLEAN NOT NULL DEFAULT TRUE
    )";
    if ($db_conn->query($sql) === TRUE) {
        echo "Table visitors created/updated successfully";
    } else {
        echo "Error creating/updating table visitors: " . $db_conn->error;
    }
    
    // Create or update locations table
    $sql = "CREATE TABLE IF NOT EXISTS locations (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        location VARCHAR(255) NOT NULL
    )";
    if ($db_conn->query($sql) === TRUE) {
        echo "Table locations created/updated successfully";
    } else {
        echo "Error creating/updating table locations: " . $db_conn->error;
    }

    // Insert locations data
    $sql = "INSERT INTO locations (id, location) VALUES
    (1, 'IU.003 - OFFICE'),
    (2, 'IU.013 - MEDIA ROOM'),
    (3, 'IU.015 - GR 45  E'),
    (4, 'IU.017 - STAFF ROOM'),
    (5, 'IU.020 - GR 123 E'),
    (6, 'IU.023 - LOWER COMMS'),
    (7, 'IU.029 - GRAND HALL'),
    (8, 'IU.101 - IT ROOM'),
    (9, 'IU.102 - LIBRARY'),
    (10, 'IU.112 - GR 678 F'),
    (11, 'IU.114 - GR 678 E'),
    (12, 'IU.116 - GR 45  F'),
    (13, 'IU.119 - ARTS'),
    (14, 'IU.121 - UPPER COMMS'),
    (15, 'IU.128 - GR 123 F')";

    if ($db_conn->query($sql) === TRUE) {
        echo "Locations inserted successfully";
    } else {
        echo "Error inserting locations: " . $db_conn->error;
    }
}

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    setupDB();
}

?>