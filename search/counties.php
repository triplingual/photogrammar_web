<?php
header('Content-type: application/json; charset=utf-8');

require_once 'login.php';
require_once 'functions-search.php';

$ERROR_MSG = "An error occurred. Please try again or send a message to <a href='mailto:thephotogrammar@gmail.com'>thephotogrammar@gmail.com</a> describing what happened.";

$mysqli = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if ($mysqli->connect_error) {
	if ($DEBUGGING)	{
	    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
	}
	else {
	    die($ERROR_MSG);
	}
}
if(!$mysqli) {
	if ($DEBUGGING)	{
		die("Unable to connect to MySQL: " .mysql_error());
	}
	else {
		die($ERROR_MSG);
	}
}

$state = get_post('state');

// BUILD QUERYSTRING, ABSTRACTING VALUES AS APPROPRIATE
$countyquery = "SELECT DISTINCT county FROM photo WHERE state = ? ORDER BY county ASC LIMIT 0,99";

// PREPARE QUERY
if (!($stmt = $mysqli->prepare($countyquery))) {
	if ($DEBUGGING)	{
		die("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
	}
	else {
		die($ERROR_MSG);
	}
}

// BIND VARIABLES
if (!$stmt->bind_param( 's', $state )) {
	if ($DEBUGGING)	{
		die("Binding parameters failed: (ERROR #" . $stmt->errno . ", ERROR MESSAGE: " . $stmt->error . " )<br />\n");
	}
	else {
		die($ERROR_MSG);
	}
}
// EXECUTE QUERY
if (!$stmt->execute()) {
	if ($DEBUGGING)	{
		die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	else {
		die($ERROR_MSG);
	}
}
// GET QUERY RESULTS
/*
	NOTE: Our webhost's PHP is not compiled with MySQL Native Driver (mysqlnd)
	so we can't use mysqli_stmt::get_result(). To allow us to stick with 'SELECT *', 
	though, we iterate through the returned fields in the metadata and bind that way.
*/
$stmt->store_result();
$stmt->bind_result($county);

$stmt->data_seek(0);

while ($stmt->fetch()) {
	$answer[] = array("id"=>$county, "text"=>$county);
}

// finally encode the answer to json and send back the result.
echo '{ "results":';
echo json_encode($answer);
echo '}';
/*
echo '{ "results":[{"id":"","text":""},{"id":"Fairfield","text":"Fairfield"},{"id":"Hartford","text":"Hartford"},{"id":"Litchfield","text":"Litchfield"},{"id":"Middlesex","text":"Middlesex"},{"id":"New Haven","text":"New Haven"},{"id":"New London","text":"New London"},{"id":"Tolland","text":"Tolland"},{"id":"Windham","text":"Windham"}]}';


echo '{ "results":[{"id":"","text":""},{"id":"Fairfield","text":"Fairfield"},{"id":"Hartford","text":"Hartford"},{"id":"Litchfield","text":"Litchfield"},{"id":"Middlesex","text":"Middlesex"},{"id":"New Haven","text":"New Haven"},{"id":"New London","text":"New London"},{"id":"Tolland","text":"Tolland"},{"id":"Windham","text":"Windham"}]}';
*/
?>