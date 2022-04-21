<?php
ini_set("session.cookie_httponly", 1);
session_start();
require 'database.php';
header("Content-Type: application/json"); // Since we are sending a JSON response here (not an HTML document), set the MIME Type to application/json


$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

// check to see is user is logged in; if not logged in, can't delete the event
if(isset($_SESSION['token']) && $json_obj['token'] != null) {
    if(!hash_equals($_SESSION['token'], $json_obj['token'])) {
        // preventing CSRF attack
        echo json_encode(array(
            "success" => false,
            "message" => "Failed to delete event. Log in and try again."
        ));
        exit;
    }
} else {
    // user failed to log in
    echo json_encode(array(
        "success" => false,
        "message" => "Failed to delete event. Log in and try again."
    ));
    exit;
}

$id = (Int)$json_obj['event_id'];

$stmt = $mysqli->prepare('delete from events where event_id=?');
if(!$stmt){
	echo json_encode(array(
		"success" => false,
		"message" => `Query Prep Failed: ${$mysqli->error}\n`
	));
	exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();


//echo info back to page
$array = array(
	"success" => true,
);

echo json_encode($array);

?>