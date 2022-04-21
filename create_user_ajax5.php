<?php
ini_set("session.cookie_httponly", 1);
session_start();
header("Content-Type: application/json"); // Since we are sending a JSON response here (not an HTML document), set the MIME Type to application/json

//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');
//This will store the data into an associative array
$json_obj = json_decode($json_str, true);


//the user and pass from the login5.html page form, also making sure that the variables are the correct data type.
$user = (String) $json_obj['user'];
$pass = (String) $json_obj['pass'];

if(!preg_match('/^[0-9a-zA-Z]+$/', $user)) {
	echo json_encode(array(
		"success" => false,
		"message" => "Invalid username or password. Please try again."
	));
	exit;
}

if(!preg_match('/^[0-9a-zA-Z!@#$%&*]+$/', $pass)) {
	echo json_encode(array(
		"success" => false,
		"message" => "Invalid username or password. Please try again."
	));
	exit;
}

require 'database.php';

//querying database for usernames
$stmt = $mysqli->prepare('select username from users');
if(!$stmt){
	echo json_encode(array(
		"success" => false,
		"message" => `Query Prep Failed: ${$mysqli->error}\n`
	));
	exit;
}

$stmt->execute();
$stmt->bind_result($username);

// checking that new username is not duplicate of other username already in use
while($stmt->fetch()) {
	if($user == $username) {
		echo json_encode(array(
			"success" => false,
			"message" => "Username already in use. Please try again."
		));
		exit;
	}
}

//if username not in use, query to insert new user into users table
$stmt = $mysqli->prepare('insert into users (username, password) values (?,?)');
if(!$stmt){
	echo json_encode(array(
		"success" => false,
		"message" => `Query Prep Failed: ${$mysqli->error}\n`
	));
	exit;
}

$pass = password_hash($pass,PASSWORD_DEFAULT);

//making sure the variables types are consistent; also hashing the password
$stmt->bind_param('ss', $user, $pass);
$stmt->execute();
$stmt->close();

//echo information back
$array = array(
	"success" => true,
);

echo json_encode($array);
exit;

?>