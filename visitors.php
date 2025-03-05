<?php
include 'header.php';
include_once 'db.php';
?>
<div style="display: flex;">
    <div style="flex: 1; padding: 10px;">
        <h2>Visitor Form</h2>
        <form id="visitorForm" method="post" class=visitor-form>
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname">
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname">
            <label for="reason">Reason of Visit:</label>
            <input type="text" id="reason" name="reason">
            <label for="location">Location in School:</label>
            <select id="location" name="location">
                <?php
                $locationResult = $db_conn->query("SELECT id, location FROM locations");
                if ($locationResult->num_rows > 0) {
                while ($row = $locationResult->fetch_assoc()) {
                        $location = $row['location'];
                        $locationId = $row['id'];
                        echo "<option value=\"$location\">$location</option>";
                    }
                } else {
                    echo "<option value=\"\">No locations available</option>";
                }
                ?>
            </select>
            <label for="phone">Telephone Number:</label>
            <input type="tel" id="phone" name="phone">
            <label for="internet_access">Request Internet Access:</label>
            <input type="checkbox" id="internet_access" name="internet_access"> I consent to the <a id=terms-link>terms and conditions</a> </input>
            <hr>
            <input type="submit" value="Submit">
        </form>
        <div id="formResponse"></div>
    </div>
    <div style="flex: 1; padding: 10px;">
        <h2>Active Visitors</h2>
        <ul id="visitorList" class=visitor-list>
            <?php
            $visitorResult = $db_conn->query("SELECT id, firstname, lastname, reason, location, date_time_in FROM presence_visitors WHERE active=true AND DATE(date_time_in) = CURDATE() ORDER BY date_time_in Asc");
            if ($visitorResult->num_rows > 0) {
                while ($row = $visitorResult->fetch_assoc()) {
                    $visitorId = $row['id'];
                    $firstname = $row['firstname'];
                    $lastname = $row['lastname'];
                    $reason = $row['reason'];
                    $location = $row['location'];
                    $datetime = $row['date_time_in'];

                    echo "<li><button class=list-button data-visitor-id=\"$visitorId\"><span class=name>$firstname $lastname</span><span class=datetime>$datetime</span><span class=location>$location</span></button></li>";
                }
            } else {
                echo "<li>No active visitors</li>";
            }
            ?>
        </ul>
    </div>
</div>

<!-- Modal -->
<div id="successModal" class="modal">
</div>
<div id="termModal" class="modal">
</div>

<script>
    
const successmodal = new Modal(document.querySelector('#successModal'),true);
const termmodal = new Modal(document.querySelector('#termModal'));

function createVisitor(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    const url = `./action.php?action=visitor`;

    Ajax.post(url, data)
        .then(response => {
            // Handle the set presence response here
            console.log(response);

            document.getElementById('formResponse').innerText = response.message;
            let modalMessage = 'Visitor added successfully.';
            if (response.internet_code) {
                modalMessage += `<br><br>Connect to <strong>CanSchool_Guest</strong> and enter the code<br><span style="font-size: 1.5em; font-weight: bold;">${response.internet_code.match(/.{1,5}/g).join('-')}</span>`;
            }
            successmodal.openModal(modalMessage);
        })
        .catch(error => {
            document.getElementById('formResponse').innerText = 'Error submitting form';
        });
}

function exitVisitor(button) {
    
    const id = button.getAttribute('data-visitor-id');
    const url = `./action.php?action=visitor&id=${id}`;

    Ajax.get(url)
        .then(response => {
            location.reload();
        })
        .catch(error => {
            console.error(error);
        });
}

function openTerm() {
    terms = '<h2>Terms and conditions</h2>';
    terms += '<p>By accessing the guest network, you agree to the following terms and conditions: <br><br>';
    terms += '1. The network is provided for educational and administrative purposes only. <br>';
    terms += '2. Unauthorized access to any system or network is prohibited. <br>';
    terms += '3. Users must not engage in any activity that could harm the network or its users. <br>';
    terms += '4. The school reserves the right to monitor and log all network activity. <br>';
    terms += '5. Users must not share their access credentials with others. <br>';
    terms += '6. The school is not responsible for any loss or damage resulting from the use of the network. <br>';
    terms += '7. Violation of these terms may result in loss of network access and disciplinary action.</p>'
    termmodal.openModal(terms);
}

// Attach event listeners to the buttons
function attachEventListeners() {
    document.getElementById('visitorForm').addEventListener('submit', createVisitor);
    document.getElementById('terms-link').addEventListener('click', openTerm); 

    const buttons = document.querySelectorAll('button[data-visitor-id]');
    for (const button of buttons) {
        button.addEventListener('click',  function() {
            exitVisitor(button);
        });
    }
}

// Call the function to attach event listeners when the page is loaded
window.addEventListener('DOMContentLoaded', function() {
    attachEventListeners();
});

</script>
<?php
include 'footer.php';
?>
