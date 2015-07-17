<br /><br />
<div>
<?php
require_once "../../pg-config.php";
$ERROR_MSG = "An error occurred. Please try again or send a message to <a href='mailto:thephotogrammar@gmail.com'>thephotogrammar@gmail.com</a> describing what happened.";

$hex=str_replace("#","",$_GET['hex']);


// MYSQLI CONNECTION
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

// UNPREPARED QUERIES TO SET MYSQL PARAMETERS
if (!$mysqli->query("SET NAMES 'utf8'") || !$mysqli->query("SET CHARACTER SET utf8") || !$mysqli->query("SET COLLATION_CONNECTION = 'utf8_general_ci'") ) {
	if ($DEBUGGING)	{
		die("Query error: (" . $mysqli->errno . ") " . $mysqli->error);
	}
	else {
	    die($ERROR_MSG);
	}
}

// COMPOSE QUERY
$hexquery = "SELECT DISTINCT colour.cnumber as cnumber, title, thumb_url FROM colour JOIN photo2 ON photo2.cnumber=colour.cnumber WHERE hex = ?;";

// PREPARE QUERY
if (!($stmt = $mysqli->prepare($hexquery))) {
	if ($DEBUGGING)	{
		die("Prepare error: (" . $mysqli->errno . ") " . $mysqli->error);
	}
	else {
	    die($ERROR_MSG);
	}
}

// BIND PARAMETERS
if (!$stmt->bind_param('s', $hex)) {
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

$stmt->store_result();
$stmt->bind_result($cnumber, $title, $thumb_url );

$rows = $stmt->num_rows;
$stmt->data_seek(0);
while ($stmt->fetch()) {
	echo "<div style='margin-right:1px;width:170px;float:left;'><img style=\"margin-left:auto;margin-bottom:0;margin-right:auto;border:1px solid black;\" src=\"http://maps.library.yale.edu/images/public/photogrammar/" . $thumb_url . "\" /><div>";
	echo '<div class="datef">' .  $title . '</div>';

	// COMPOSE QUERY
	$onecoverquery = "SELECT hex FROM colour WHERE cnumber = ?;";

	// PREPARE QUERY
	if (!($hexstmt = $mysqli->prepare($onecoverquery))) {
		if ($DEBUGGING)	{
			die("Prepare error: (" . $mysqli->errno . ") " . $mysqli->error);
		}
		else {
			die($ERROR_MSG);
		}
	}

	// BIND PARAMETERS
	if (!$hexstmt->bind_param('s', $thumb_url)) {
		if ($DEBUGGING)	{
			die("Binding parameters failed: (ERROR #" . $hexstmt->errno . ", ERROR MESSAGE: " . $hexstmt->error . " )<br />\n");
		}
		else {
			die($ERROR_MSG);
		}
	}

	// EXECUTE QUERY
	if (!$hexstmt->execute()) {
		if ($DEBUGGING)	{
			die("Execute failed: (" . $hexstmt->errno . ") " . $hexstmt->error);
		}
		else {
			die($ERROR_MSG);
		}
	}

	$hexstmt->store_result();
	$hexstmt->bind_result($hexval);

	$rows = $hexstmt->num_rows;
	$hexstmt->data_seek(0);
	while ($hexstmt->fetch()) {
		echo "<div style='float:left;";
		if ($hex==$hexval) {
			echo "border:3px solid black;height:13px;width:13px;";
		}
		else {
			echo "border:1px solid black;height:17px;width:17px;";	
		}
		echo "margin-bottom:1px;margin-left:1px;background-color:#" . $hexval . "' title='#" . $hexval . "'></div> ";
	};
	echo "</div></div>";
	
}
?>
</div>