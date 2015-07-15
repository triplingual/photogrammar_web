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
$county = get_post('county');

if ($county != "") {
	// BUILD QUERYSTRING, ABSTRACTING VALUES AS APPROPRIATE
	$cityquery = "SELECT DISTINCT city FROM photo WHERE state = ? AND county = ? AND city !=\"\" ORDER BY city ASC";
	// PREPARE QUERY
	if (!($stmt = $mysqli->prepare($cityquery))) {
		if ($DEBUGGING)	{
			die("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
		}
		else {
			die($ERROR_MSG);
		}
	}

	// BIND VARIABLES
	$stmt->bind_param( 'ss', $state, $county );

} else { 
	// BUILD QUERYSTRING, ABSTRACTING VALUES AS APPROPRIATE
	$cityquery = "SELECT DISTINCT city FROM photo WHERE state = ? AND city !=\"\" ORDER BY city ASC";
	// PREPARE QUERY
	if (!($stmt = $mysqli->prepare($cityquery))) {
		if ($DEBUGGING)	{
			die("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
		}
		else {
			die($ERROR_MSG);
		}
	}

	// BIND VARIABLES
	$stmt->bind_param( 's', $state );
}

// Option for binding to multiple possible values, as seen at 
// https://php.net/manual/en/mysqli-stmt.bind-param.php#104073
/*
$refArr = array($state, $county);
$ref = new ReflectionClass('mysqli_stmt');
$method = $ref->getMethod("bind_param");
$method->invokeArgs($stmt, $refArr);
*/
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
$stmt->bind_result($city);

while ($stmt->fetch()) {
	$answer[] = array("id"=>$city, "text"=>$city);
}
// finally encode the answer to json and send back the result.
echo '{ "results":';
echo json_encode($answer);
echo '}';
?>
