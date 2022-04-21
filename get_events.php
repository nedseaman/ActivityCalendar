<?php
ini_set("session.cookie_httponly", 1);
session_start();
header("Content-Type: application/json"); // Since we are sending a JSON response here (not an HTML document), set the MIME Type to application/json

//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');

//This will store the data into an associative array
$json_obj = json_decode($json_str, true);

require 'database.php';

if(!isset($_SESSION['username'])) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;

}

$stmt = $mysqli->prepare('select * from events where (date_time between ? and ?) and tag in (?,?,?,?,?,?) and author=? order by date_time asc');
if(!$stmt){
	echo json_encode(array(
        "success" => false,
        "message" => `Query Prep Failed: ${$mysqli->error}\n`
    ));
	exit;
}

$tags = $json_obj['tags'];

$stmt->bind_param('sssssssss', $json_obj['begin'], $json_obj['end'], $tags[0], $tags[1], $tags[2], $tags[3], $tags[4], $tags[5], $_SESSION['username']);
$stmt->execute();

//bind results to variables that we can use to compare to results from query
$stmt->bind_result($event_id, $author, $date_time, $title, $tag);

$events = array("success" => true);

while($stmt->fetch()) {
    array_push($events, array("event_id" => htmlentities($event_id), "author" => htmlentities($author), "date_time" => htmlentities($date_time), "title" => htmlentities($title), "tag" => $tag));
}

$stmt->close();

echo json_encode($events);
exit;

?>