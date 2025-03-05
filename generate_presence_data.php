<?php

include_once 'db.php';
function generateRandomTime($baseTime, $range) {
    $baseTimestamp = strtotime($baseTime);
    $randomOffset = rand(-$range, $range);
    return date("H:i:s", $baseTimestamp + $randomOffset);
}

function generatePresenceData($staffId, $startDate, $days) {
    $presenceData = [];

    for ($i = 0; $i < $days; $i++) {
        $currentDate = date('Y-m-d', strtotime("$startDate -$i days"));
        $dayOfWeek = date('w', strtotime($currentDate));
        if ($dayOfWeek == 0 || $dayOfWeek == 6) {
            continue; // Skip if it's Sunday (0) or Saturday (6)
        }

        // Generate random times
        $arrivalTime = generateRandomTime('07:00:00', 3600); // Random time around 7:50 within +-10 minutes
        $lunchStartTime = generateRandomTime('12:00:00', 600); // Random time around 12:00 within +-10 minutes
        $lunchEndTime = generateRandomTime('12:30:00', 600); // Random time around 12:30 within +-10 minutes
        $leaveTime = generateRandomTime('16:45:00', 3600); // Random time around 16:10 within +-15 minutes

        // Skip if the current time is sooner than the arrival time
        if ( $i==0 && strtotime(date('Y-m-d H:i')) < strtotime("$currentDate $arrivalTime")) {
            continue;
        }
        // Add arrival record
        $presenceData[] = [
            'staff_id' => $staffId,
            'status' => true,
            'date_time' => "$currentDate $arrivalTime"
        ];

        if ( $i==0 && strtotime(date('Y-m-d H:i')) < strtotime("$currentDate $lunchStartTime")) {
            continue;
        }
        // Add lunch start record
        $presenceData[] = [
            'staff_id' => $staffId,
            'status' => false,
            'date_time' => "$currentDate $lunchStartTime"
        ];

        if ( $i==0 && strtotime(date('Y-m-d H:i')) < strtotime("$currentDate $lunchEndTime")) {
            continue;
        }
        // Add lunch end record
        $presenceData[] = [
            'staff_id' => $staffId,
            'status' => true,
            'date_time' => "$currentDate $lunchEndTime"
        ];

        if ( $i==0 && strtotime(date('Y-m-d H:i')) < strtotime("$currentDate $leaveTime")) {
            continue;
        }
        // Add leave record
        $presenceData[] = [
            'staff_id' => $staffId,
            'status' => false,
            'date_time' => "$currentDate $leaveTime"
        ];
    }

    return $presenceData;
}

function generateInsertStatements($presenceData) {
    $insertStatements = "INSERT INTO presence_staff_time (staff_id, status, date_time) VALUES \n";

    $values = [];
    foreach ($presenceData as $record) {
        $staffId = $record['staff_id'];
        $status = $record['status'] ? 'true' : 'false';
        $dateTime = $record['date_time'];

        $values[] = "($staffId, '$status', '$dateTime')";
    }

    $insertStatements .= implode(",\n ", $values) . ";";

    return $insertStatements;
}
// Configuration
$startDate = date('Y-m-d');
$days = 14;

echo "Generating presence data for the next $days days starting from ".date('Y-m-d H:i')." <br>";

// Fetch all staff IDs
$staffIds = [];
$result = $db_conn->query("SELECT id FROM presence_staff");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $staffIds[] = $row['id'];
    }
}

$result = $db_conn->query("DELETE FROM presence_staff_time");


// Generate and insert data for each staff member
foreach ($staffIds as $staffId) {
    // Generate data
    $presenceData = generatePresenceData($staffId, $startDate, $days);

    // Generate SQL insert statements
    $insertStatements = generateInsertStatements($presenceData);

    if ($db_conn->query($insertStatements)) {
        echo "New records created successfully for staff ID: $staffId <br> - $insertStatements <br>" ;
    } else {
        echo "ERROR for staff ID: $staffId <br> - $insertStatements <br> $db_conn->error <br>";
    }
}

$db_conn->close();