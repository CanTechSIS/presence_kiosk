<?php
include_once 'db.php';
include_once 'unifi.php';


class AjaxController {

    public function handleRequest() {
        // Check if the request method is POST
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the data sent via POST
            $getData = $_GET;
            $postData = json_decode(file_get_contents('php://input'), true);
            
            if(isset($getData['action'])) {
                // Perform actions based on the 'action' parameter value
                switch($getData['action']) {
                    case 'presence':
                        $this->setPresence($getData['id'], $postData['presence']);
                        break;
                        
                    case 'visitor':
                        $this->addVisitor($postData);
                        break;
                    default:
                        // Invalid action
                        $response = array('status' => 'error', 'message' => 'Invalid action');
                        echo json_encode($response);
                        return;
                }
            } else {
                // 'action' parameter not set
                $response = array('status' => 'error', 'message' => 'Missing action parameter');
                echo json_encode($response);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Get the data sent via GET
            $getData = $_GET;
            if(isset($getData['action'])) {
                // Perform actions based on the 'action' parameter value
                switch($getData['action']) {
                    case 'presence':
                        $this->getPresence($getData['id']);
                        break;
                    case 'visitor':
                        $this->outVisitor($getData['id']);
                        break;
                    default:
                        // Invalid action
                        $response = array('status' => 'error', 'message' => 'Invalid action');
                        echo json_encode($response);
                        return;
                }
            } else {
                // 'action' parameter not set
                $response = array('status' => 'error', 'message' => 'Missing action parameter');
                echo json_encode($response);
            }
        } else {
            // Invalid request method
            $response = array('status' => 'error', 'message' => 'Invalid request method');
            echo json_encode($response);
        }
    }
    public function getPresence($id) {
        global $db_conn;
        // Query presence value from the database
        $sql = "SELECT status, date_time FROM presence_staff_time WHERE staff_id = $id AND DATE(date_time) = CURDATE() ORDER BY date_time DESC LIMIT 1";

        $result = $db_conn->query($sql);

        if ($result) {
            // Fetch the presence value
            $row = $result->fetch_assoc();
            $presence = isset($row['status']) ? $row['status'] : null;
            $date_time = isset($row['date_time']) ? $row['date_time'] : null;

            // Return the presence value as a response
            $response = array('status' => 'success', 'presence' => $presence, 'time' => $date_time);
            echo json_encode($response);
        } else {
            // Error in executing the query
            $response = array('status' => 'error', 'presence' => null, 'time' => null, 'message' => 'Failed to query presence');
            echo json_encode($response);
        }
    }
    public function setPresence($id, $presence) {
        global $db_conn;
        // Get the current date and time
        $date_time = date('Y-m-d H:i:s');

        // Insert a new entry in presence_staff_time table
        $sql = "INSERT INTO presence_staff_time (staff_id, status, date_time) VALUES ('$id', '$presence', '$date_time')";

        if ($db_conn->query($sql) === TRUE) {
            // Success in inserting the new entry
            $response = array('status' => 'success', 'presence' => $presence, 'time' => $date_time, 'message' => 'Presence set successfully');
            echo json_encode($response);
        } else {
            // Error in executing the query
            $response = array('status' => 'error', 'presence' => null,'time' => null, 'message' => 'Failed to set presence');
            echo json_encode($response);
        }
    }

    public function addVisitor($data) {
        global $db_conn;

        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $reason = $data['reason'];
        $location = $data['location'];
        $internet_access = isset($data['internet_access']) ? 1 : 0;
        $date_time = date('Y-m-d H:i:s');
        $date = date('d/m/Y');
        $phone = $data['phone'];
       
        $internet_code = null;
        if ($internet_access) {
            $internet_code = generateInternetCode("Visitor - $firstname[0]. $lastname - $date");
        }

        $sql = "INSERT INTO presence_visitors (firstname, lastname, reason, location, internet_access, internet_code, date_time_in, active, phone) VALUES ('$firstname', '$lastname', '$reason', '$location', $internet_access, '$internet_code', '$date_time', true, '$phone')";
       
        if ($db_conn->query($sql) === TRUE) { 
            // Success in inserting the new entry
            
            if ($internet_access) {
                $response =  array('status' => 'success', 'internet_code' => $internet_code, 'time' => $date_time, 'message' => 'New visitor added successfully');
            } else {
                $response = array('status' => 'success', 'time' => $date_time, 'message' => 'New visitor added successfully');
            }
            echo json_encode($response);
        } else {
            $response = array('status' => 'error', 'message' => 'Failed to set presence');
            echo json_encode($response);
        }
    }
    
    public function outVisitor($id) {
        global $db_conn;
        // Get the current date and time
        $date_time = date('Y-m-d H:i:s');

        // Insert a new entry in presence_staff_time table
        $sql = "UPDATE presence_visitors SET active = false, date_time_out = '$date_time' WHERE id = '$id' ";

        if ($db_conn->query($sql) === TRUE) {
            // Success in inserting the new entry
            $response = array('status' => 'success', 'time' => $date_time, 'message' => 'Visitor Out successfully');
            echo json_encode($response);
        } else {
            // Error in executing the query
            $response = array('status' => 'error', 'time' => null, 'message' => 'Failed to out visitor ');
            echo json_encode($response);
        }
    }

}

// Create an instance of the AjaxController class
$ajaxController = new AjaxController();
// Handle the AJAX request
$ajaxController->handleRequest();
?>