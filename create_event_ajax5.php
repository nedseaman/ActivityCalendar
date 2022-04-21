<?php
ini_set("session.cookie_httponly", 1);
session_start();
header("Content-Type: application/json"); // Since we are sending a JSON response here (not an HTML document), set the MIME Type to application/json

//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');

//This will store the data into an associative array
$json_obj = json_decode($json_str, true);

require 'database.php';

// Check if user is logged in; if not, then they can't create an event.
if(isset($_SESSION['token']) && $json_obj['token'] != null) {
    if(!hash_equals($_SESSION['token'], $json_obj['token'])) {
        // preventing CSRF attack
        echo json_encode(array(
            "success" => false,
            "message" => "Failed to create event. Log in and try again."
        ));
        exit;
    }
} else {
    // user failed to log in
    echo json_encode(array(
        "success" => false,
        "message" => "Failed to create event. Log in and try again."
    ));
    exit;
}

$title = (String)$json_obj['title'];
$author = (String)$_SESSION['username'];
$tag = (String)$json_obj['tag'];

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

$datetime = DateTime::createFromFormat('Y-m-d\TH:i', (String) $json_obj['datetime'])->format('Y-m-d H:i:s');

//query to insert an event into the events table
$stmt = $mysqli->prepare('insert into events (author, date_time, title, tag) values (?,?,?,?)');
if(!$stmt){
	echo json_encode(array(
        "success" => false,
        "message" => `Query Prep Failed: ${$mysqli->error}\n`
    ));
	exit;
}

//making sure the variables types are consistent.
$stmt->bind_param('ssss', $author, $datetime, $title, $tag);
$stmt->execute();
$stmt->close();

//echo information back
echo json_encode(array(
	"success" => true
));
exit;

?>