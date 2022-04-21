<?php

header("Content-Type: application/json"); // Since we are sending a JSON response here (not an HTML document), set the MIME Type to application/json

ini_set("session.cookie_httponly", 1);
session_start();
require 'database.php';

$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

// Check if user is logged in; if not, then they can't create an event.
if(isset($_SESSION['token']) && $json_obj['token'] != null) {
    if(!hash_equals($_SESSION['token'], $json_obj['token'])) {
        // preventing CSRF attack
        echo json_encode(array(
            "success" => false,
            "message" => "Failed to edit event. Log in and try again."
        ));
        exit;
    }
} else {
    // user failed to log in
    echo json_encode(array(
        "success" => false,
        "message" => "Failed to edit event. Log in and try again."
    ));
    exit;
}

$title = (String)$json_obj['title'];
$id = (Int)$json_obj['event_id'];
$tag = (String)$json_obj['tag'];

//since datetime arrives in this format: y-m-dT00:00, we need to replace that T with a space so that the DateTime object works correctly
if(!preg_match('/^[a-zA-Z0-9\s]+$/', $title)) {
	echo json_encode(array(
		"success" => false,
		"message" => "Invalid event title. Please try again."
	));
	exit;
}

if(!preg_match('/^[0-9]{4}-/', (String)$json_obj['datetime'])) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid date. Please try again."
    ));
    exit;
}

$datetime = (String) DateTime::createFromFormat('Y-m-d\TH:i', (String) $json_obj['datetime'])->format('Y-m-d H:i:s');

//query to update event rather than add a new one the table.
$stmt = $mysqli->prepare('update events set date_time=?, title=?, tag=? where event_id=?');
if(!$stmt){
	echo json_encode(array(
		"success" => false,
		"message" => `Query Prep Failed: ${$mysqli->error}\n`
	));
	exit;
}

//making sure the variables types are consistent.
$stmt->bind_param('sssi', $datetime, $title, $tag, $id);
$stmt->execute();
$stmt->close();

//echo info back to page
echo json_encode(array(
    "success" => true
));

exit;

?>