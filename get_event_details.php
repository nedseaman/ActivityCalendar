<?php
header("Content-Type: application/json"); // Since we are sending a JSON response here (not an HTML document), set the MIME Type to application/json

//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');

//This will store the data into an associative array
$json_obj = json_decode($json_str, true);

require 'database.php';

$stmt = $mysqli->prepare('select * from events where event_id=?');
if(!$stmt){
	echo json_encode(array(
        "success" => false,
        "message" => `Query Prep Failed: ${$mysqli->error}\n`
    ));
	exit;
}
$stmt->bind_param('i', $json_obj['event_id']);
$stmt->execute();

$stmt->bind_result($event_id, $author, $date_time, $title, $tag);
$stmt->fetch();

echo json_encode(array(
    "success" => true,
    "event_id" => htmlentities($event_id),
    "author" => htmlentities($author), 
    "date_time" => htmlentities($date_time),
    "title" => htmlentities($title),
    "tag" => $tag
));
$stmt->close();
exit;

?>