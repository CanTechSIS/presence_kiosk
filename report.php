<?php
include 'header.php';
include_once 'db.php';

$sql = "SELECT id, firstname, lastname, phone, reason, location, internet_code, date_time_in, date_time_out, active,  DATE(date_time_in) = CURDATE() as today
    FROM presence_visitors 
    WHERE date_time_in >= DATE_SUB(NOW(), INTERVAL 5 DAY) 
    ORDER BY date_time_in desc";
$result = $db_conn->query($sql);

echo "<h1>Visitor Report</h1>";
if ($result->num_rows > 0) {
    // Generate a list of buttons with staff' names
    echo "<ul>";
    $currentStaffId = null;
    while ($row = $result->fetch_assoc()) {
        $visitorId = $row['id'];
        $firstname = $row['firstname'];
        $lastname = $row['lastname'];
        $phone = $row['phone'];
        $reason = $row['reason'];
        $location = $row['location'];
        $internet_code = !empty($row['internet_code']) ? preg_replace("/(\d{1,5})(\d{1,5})/", "$1-$2", str_pad($row['internet_code'], 10, "0", STR_PAD_LEFT)) : 'no_access';
        $date_time_in = $row['date_time_in'];
        $date_time_out = $row['date_time_out'];
        $active = $row['active'];
        $today = $row['today'];
        
            echo "<li class='visitor-list-report presence-". ($active ? 'true' :  ($today ? 'false' : 'none')) ."'  data-visitor-id=\"$visitorId\">
            <span class=name>$firstname $lastname</span><br>
            <strong>Internet Code:</strong>  $internet_code &nbsp;&nbsp;&nbsp;<strong>Phone:</strong> $phone <br>
            <strong>Location:</strong> $location &nbsp;&nbsp;&nbsp;<strong>Reason:</strong> $reason <br>
            <strong>Check-in Time:</strong> $date_time_in &nbsp;&nbsp;&nbsp;<strong>Check-out Time:</strong> $date_time_out </li>";
        
    }
    
    echo '</ul>';
} else {
    echo 'No staff record found.';
}

$result = null;

$sql = "SELECT s.id, s.firstname, s.lastname, st.date_time, st.status 
    FROM presence_staff as s 
    INNER JOIN presence_staff_time as st 
    ON s.id = st.staff_id 
    WHERE st.date_time >= DATE_SUB(NOW(), INTERVAL 5 DAY) 
    ORDER BY s.firstname, s.id, st.date_time desc";
$result = $db_conn->query($sql);

echo "<h1>Staff Report</h1>";
if ($result->num_rows > 0) {
    // Generate a list of buttons with staff' names
    echo "<ul>";
    $currentStaffId = null;
    while ($row = $result->fetch_assoc()) {
        $staffId = $row['id'];
        $firstname = $row['firstname'];
        $lastname = $row['lastname'];
        $date_time = $row['date_time'];
        $status = $row['status'];
        
        if ($staffId != $currentStaffId) {
            if ($currentStaffId !== null) {
                echo '</ul></li>';
            }
            echo "<li class=staff-list-report data-staff-id=\"$staffId\"><a class=hidden href='report-staff-detail.php?id=$staffId'>$firstname $lastname</a> <br>"; 
            echo '<ul>';
        }
            
        if ($status === 'true') {
            echo "<li class=presence-true>Heure d'arrivée: &nbsp;&nbsp;&nbsp;$date_time</li>";
        } else {
            echo "<li class=presence-false>Heure de départ: &nbsp;&nbsp;&nbsp;$date_time</li>";
        }
        $currentStaffId = $staffId;
    }
    
    echo '</ul></li></ul>';
} else {
    echo 'No staff record found.';
}

$db_conn->close();

include 'footer.php';
?>