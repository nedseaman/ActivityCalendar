<?php

header("Content-Type: application/json"); // Since we are sending a JSON response here (not an HTML document), set the MIME Type to application/json

//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');
//This will store the data into an associative array
$json_obj = json_decode($json_str, true);


//the user and pass from the login5.html page form, also making sure that the variables are the correct data type.
$user = (String) $json_obj['user'];
$pass = (String) $json_obj['pass'];

require 'database.php';

//query to select all columns from users table where the username will equal $user
$stmt = $mysqli->prepare("select * from users where username=?");
if(!$stmt){
	echo json_encode(array(
        "success" => false,
        "message" => `Query Prep Failed: ${$mysqli->error}\n`
    ));
	exit;
}

//making sure the variable type is consistent ($user is a String).
$stmt->bind_param('s',$user);
$stmt->execute();
//bind results to variables that we can use to compare to results from query
$stmt->bind_result($username, $password);

//check if username and password matches any of the usernames and passwords in from database
$stmt->fetch();

//password security things.
if(password_verify($pass, $password)){
    ini_set("session.cookie_httponly", 1);
    session_start();
    $_SESSION['username'] = htmlentities($username);
    $_SESSION['token'] = bin2hex(random_bytes(32));
    echo json_encode(array(
		"success" => true,
        "token" => $_SESSION['token']
	));
    exit;
} else {
    echo json_encode(array(
        "success" => false,
        "message" => "Incorrect Username or Password"
    ));
    exit;
}

$stmt->close();
exit;

?>