<?php
include 'header.php';
include_once 'db.php';

if (isset($_GET['id'])) {
    
    $staffId = intval($_GET['id']);
    $weekOffset = isset($_GET['wo']) ? intval($_GET['wo']) : 0;

    // Calculate the start and end dates for the current week adjusted with the offset
    $startOfWeek = strtotime("last Sunday +1 day -$weekOffset week");
    $endOfWeek = strtotime("next Sunday +1 day-$weekOffset week");

    $startDate = date('Y-m-d', $startOfWeek);
    $endDate = date('Y-m-d', $endOfWeek);

    $sql = "SELECT s.firstname, s.lastname, st.date_time, st.status 
            FROM presence_staff as s 
            LEFT JOIN presence_staff_time as st 
            ON s.id = st.staff_id 
            AND st.date_time BETWEEN '$startDate' AND '$endDate'
            WHERE s.id = $staffId 
            ORDER BY st.date_time";
    $result = $db_conn->query($sql);

    if ($result->num_rows > 0) {
        $firstname = '';
        $lastname = '';
        $presenceTime = [];
        
        while ($row = $result->fetch_assoc()) {
            $firstname = $row['firstname'];
            $lastname = $row['lastname'];
            $roundedTime = round(strtotime($row['date_time']) / (15 * 60)) * (15 * 60);
            $presenceTime[] = ['time' => $roundedTime, 'status' => $row['status']];
        }

        /*echo "<pre>";
        foreach ($presenceTime as $pTime) {
            print_r(date('Y-m-d H:i:00', $pTime['time']) . " ${pTime['status']}\n");
        }
        echo "</pre>";*/
        
        echo "<h1 class=inline>Staff Details</h1>\t&nbsp;\t&nbsp;\t<h2 class=inline>$firstname  $lastname </h2>";
        echo "<span class=right><a href='report-staff-detail.php?id=$staffId&wo=" . ($weekOffset + 1) . "'>&larr;</a>";
        echo "\t&nbsp;\t&nbsp;\t<span>$startDate - $endDate</span> ";
        if ($weekOffset > 0) {
            echo "\t&nbsp;\t&nbsp;\t<a href='report-staff-detail.php?id=$staffId&wo=" . ($weekOffset - 1) . "'>&rarr;</a>";
        } else {
            echo "\t&nbsp;\t&nbsp;\t<span>&rarr;</span>";
        }
        echo "</span>";
        echo "<table class=week-report-calendar>";
        echo "<tr><th>Time</th>";
        for ($day = 0; $day <= 6; $day++) {
            $currentDay = strtotime("+$day day", $startOfWeek);
            echo "<th>" . date('l', $currentDay) . "<br><small>" . date('j F', $currentDay) . "</small></th>";
        }
        echo "</tr>";
        $startTime = strtotime('00:00');
        $endTime = strtotime('23:45');
        
        for ($time = $startTime; $time <= $endTime; $time = strtotime('+15 minutes', $time)) {
            if (date('i', $time) == '00') {
                echo "<tr class=hour>";
                echo "<td rowspan=4>" . date('H:i', $time) . "</td>";
            } 
            else {
                echo "<tr>";
            }
            
            for ($day = 0; $day <= 6; $day++) {
                $status = '';
                $currentDay = strtotime("+$day day", $startOfWeek);
                if (date('Y-m-d', $currentDay )  > date('Y-m-d')) {
                    $status = 'none';
                } else {
                    foreach ($presenceTime as $pTime) {
                        if (date('N', $pTime['time']) == date('N', $currentDay)  && date('H:i', $pTime['time']) <= date('H:i', $time)) {
                            if (date('Y-m-d', $pTime['time']) == date('Y-m-d') && date('H:i', $time) > date('H:i')) {
                                $status = 'none' ;
                            } else {
                                $status = $pTime['status'] === 'true' ? 'true' : '';
                            }
                        }
                    }
                }
                echo "<td class=presence-$status></td>";
            }
            
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "No details found for this staff member.";
    }
} else {
    echo "Invalid staff ID.";
}

include 'footer.php';

?>
