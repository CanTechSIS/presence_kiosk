<?php
include 'header.php';
include_once 'db.php';

// Retrieve the list of staff from the database
$sql = "SELECT id, firstname, lastname FROM presence_staff ORDER BY firstname, id";
$result = $db_conn->query($sql);

if ($result->num_rows > 0) {
    // Generate a list of buttons with staff' names
    echo "<ul class=staff-list>";
    while ($row = $result->fetch_assoc()) {
        $staffId = $row['id'];
        $firstname = $row['firstname'];
        $lastname = $row['lastname'];

        echo "<li><button class=list-button data-staff-id=\"$staffId\"><span class=name>$firstname $lastname</span><span class=datetime></span></button></li>";
    }
    echo "</ul>";
} else {
    echo "No staff found.";
}

$db_conn->close();
?>

<script>

function getPresence(button, id) {
    const url = `./action.php?action=presence&id=${id}`;

    Ajax.get(url)
        .then(response => {
            // Handle the presence status response here
            console.log(response);

            // Add an attribute to store the presence status
            button.setAttribute('data-staff-presence', response.presence);

            // Change the button's background color based on the presence status
            button.classList.remove('presence-true');
            button.classList.remove('presence-false');
            button.classList.add('presence-' + response.presence);
            // Add the response.time value next to the button if not null, else blank the current value
            const datetimeElement = button.parentNode.querySelector('.datetime');
            if (datetimeElement !== null) {
                datetimeElement.textContent = response.time !== null ? response.time : '';
            }
        })
        .catch(error => {
            console.error(error);
        });
}

function setPresence(button) {
    const id = button.getAttribute('data-staff-id');
    const currentPresence = button.getAttribute('data-staff-presence');
    // Invert the current presence status
    const newPresence = currentPresence === 'true' ? 'false' : 'true';

    const url = `./action.php?action=presence&id=${id}`;

    Ajax.post(url, {presence:newPresence})
        .then(response => {
            // Handle the set presence response here
            console.log(response);
            
            if (response.presence === null) {
                throw new Error('Presence status is null.');
            }
            // Add an attribute to store the presence status
            button.setAttribute('data-staff-presence', response.presence);

            // Change the button's background color based on the presence status
            button.classList.remove('presence-true');
            button.classList.remove('presence-false');
            button.classList.add('presence-' + response.presence);

            // Add the response.time value next to the button if not null, else blank the current value
            const datetimeElement = button.parentNode.querySelector('.datetime');
            if (datetimeElement !== null) {
                datetimeElement.textContent = response.time !== null ? response.time : '';
            }
        })
        .catch(error => {
            console.error(error);
        });
}

// Attach event listeners to the buttons
function attachEventListeners() {
    const buttons = document.querySelectorAll('button[data-staff-id]');
    for (const button of buttons) {
        button.addEventListener('click', function() {
            setPresence(button);
        });
    }
}

// Loads the presence of staff.
function loadPresence() {
    const buttons = document.querySelectorAll('button[data-staff-id]');
    for (const button of buttons) {
        const id = button.getAttribute('data-staff-id');
        getPresence(button, id);
    }
}

// Call the function to attach event listeners when the page is loaded
window.addEventListener('DOMContentLoaded', function() {
    loadPresence();
    setInterval(loadPresence, 60*1000); // Execute loadPresence every minute (60000 milliseconds)
    attachEventListeners();
});



</script>

<?php
include 'footer.php';
?>