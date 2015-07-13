<?php
require_once 'login.php';
$page = "search";
$ERROR_MSG = "An error occurred. Please try again or send a message to <a href='mailto:thephotogrammar@gmail.com'>thephotogrammar@gmail.com</a> describing what happened.";
$bodyopts = 'onload="initialize()"';
include '../header.php';

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

$fval['record'] = "";

echo <<<_END

    <style>
#record-content {
	margin: 0 auto;
	padding: 20px 0 0 0;
	width:1100px;
}
#record-meta {
	float: left;
	padding:0 5% 0 0;
	width: 30%;
}
#record-meta p {
	margin:0 0 5px;
}
#record-image {
	float:right;
	text-align:right;
	width:65%;
}
#record-image img {
	max-width:700px;
}
#record-photographer {
	margin:10px 0 0;
}
.record-heading {
	border-bottom:1px dotted #DD4B39;
	display:block;
	font-family:Arial, Helvetica, sans-serif;
	font-size:.9em;
	font-weight:bold;
	margin:15px 0 5px 0;
}
.record-heading.first {
	margin:0 0 5px 0;
}
.record-text {
	color:#333333;
	font-family:Arial, Helvetica, sans-serif;
	font-size:.8em;
}
    </style>

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
    <script type="text/javascript" src="/js/jquery.google_menu.js"></script>
    
    <script>
            $('document').ready(function(){
                $('.menu').fixedMenu();
            });
    </script>
    
    <script type="text/javascript" src="../js/mapping.js"></script>
    <style>
    
    ul {list-style-type:square;
	    padding-left:20px;
	    margin-top:0;
    }
    </style>

<div id="wrapper" class="clearfix" style="padding:75px">

<div id="content-wrapper" class="clearfix">

<div id="record-content" class="clearfix">
_END;

$db_server = mysql_connect($db_hostname, $db_username, $db_password);

if(!$db_server) die("Unable to connect to MySQL: " .mysql_error());

mysql_select_db($db_database) or die('Unable to connect to MySQL: ' . mysql_error());

$fval = array('record'=>'');
$mons = array('1'=>'January ', '2'=>'February ', '3'=>'March ', '4'=>'April ', 
				'5'=>'May ', '6'=>'June ', '7'=>'July ', '8'=>'August ',
                '9'=>'September ', '10'=>'October ', '11'=>'November ', 
                '12'=>'December ', '0'=>'');

if(isset($_GET['record']))
{
	$fval['record'] = get_post('record');

    $query = "SELECT * FROM photo WHERE cnumber= ? ";

	if (!($stmt = $mysqli->prepare($query))) {
		if ($DEBUGGING)	{
			die("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
		}
		else {
			die($ERROR_MSG);
		}
	}
	// BIND VARIABLES
	if (!$stmt->bind_param( 's', $fval['record'] )) {
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
	//	TODO: Only select the fields we need.
	$stmt->store_result();
	$meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field())
    {
        $params[] = &$row[$field->name];
    }

    call_user_func_array(array($stmt, 'bind_result'), $params);
    
	while ($stmt->fetch()) {
		foreach($row as $key => $val)
		{
			$c[$key] = $val;
		}
		$result[] = $c;
    }
	$stmt->data_seek(0);
	$stmt->fetch();

    // STORE DATABASE RESULTS IN VARIABLES
    $ptitle = $row['title'];
    $pnom = $row['pname'];
    $pmon =  intval($row['month']);
    $pyear =  $row['year'];
    $pdate = $mons[$pmon] . $pyear;
    $pstate = $row['state'];
    $pcounty = $row['county'];
    $pcity = $row['city'];
    $pcounty = $row['county'];
    $pstate = $row['state'];
    $pvannum0 = $row['van0'];
    $pvannum1 = $row['van1'];
    $pvannum2 = $row['van2'];
    $plot = intval($row['lotnum']);
    $pcnumber2 = $row['cnumber2'];
    $photourl = $row['large_url'];
    $plargeurl = $row['large_url'];
    $psmallurl = $row['small_url'];

    
    $ploc = '<a class="record-text" href="/search/results.php?start=0&state=' . $pstate . '&year_start=1935&month_start=0&year_stop=1945&month_stop=12" class="record-text">' . $pstate . '</a>';
    if($pcounty != "") $ploc = '<a class="record-text" href="/search/results.php?start=0&county=' . $pcounty . '&state=' . $pstate . '&year_start=1935&month_start=0&year_stop=1945&month_stop=12" class="record-text">' . $pcounty . '</a>' . ", " . $ploc;
    if($pcity != "") $ploc = '<a class="record-text" href="/search/results.php?start=0&city=' . $pcity . '&county=' . $pcounty . '&state=' . $pstate . '&year_start=1935&month_start=0&year_stop=1945&month_stop=12" class="record-text">' . $pcity . '</a>' . ", " . $ploc;
    
    if($ptitle == "") $ptitle = "None";
    if($ploc == "") $ploc = "Unknown";
    if($pvannum0 == "" & $pvannum0 == "" & $pvannum0 == "") $pvannum0 = "None";

    echo '<div id="record-meta"><!--<h2>Record Information</h2>-->';
    echo '<div id="record-title"><h3 class="record-heading first">Caption <span style="font-size:.8em; color:grey; font-weight:normal;">(Original Description)</span></h3><span class="record-text">' . $ptitle . '</div><!--/#record-title-->' . PHP_EOL;
    echo '<div id="record-photographer"><h3 class="record-heading">Photographer</h3>';

	if($pnom != "") {
		echo '<a href="/search/results.php?start=0&pname=' . $pnom . '&year_start=1935&month_start=0&year_stop=1945&month_stop=12" class="record-text">' . $pnom . '</a>';
    }
    if($pnom == "") {
		echo '<span class="record-text">Unknown</span>';
    }    
    
    echo '</div><!--/#record-photographer-->' . PHP_EOL;
    echo '<div id="record-created" class="record-field"><h3 class="record-heading">Created</h3>';
    if($pdate != "0") {
  	  echo '<a class="record-text" href="/search/results.php?start=0&year_start=' . $pyear . '&month_start=' . $pmon . '&year_stop=' . $pyear . '&month_stop=' . $pmon . '">' . $pdate . '</a>';
    }
    if($pdate == "0") { echo '<span class="record-text">Unknown</span>';};
    echo '</div><!--/#record-created-->' . PHP_EOL;

    echo '<div id="record-notes" class="record-field"><h3 class="record-heading">Location</h3><span >' . $ploc . '</span></div><!--/#record-notes-->' . PHP_EOL;

	if ($pvannum1!="") {
	    echo '<div id="record-classification" class="record-field"><h3 class="record-heading">Classification<span style="font-size:.8em;color:grey; font-weight:normal;"> (Original Tagging System)</span></h3><span class="record-text"><ul><li><a class="record-text" style="font-size:1em;" href="/search/results.php?start=0&year_start=1935&month_start=0&year_stop=1945&month_stop=12&van=A' . $pvannum0 . '" >' . $pvannum0 . '</a><ul><li><a class="record-text" style="font-size:1em;" href="/search/results.php?start=0&year_start=1935&month_start=0&year_stop=1945&month_stop=12&van=B' . $pvannum1 . '" >' . $pvannum1 . '</a><ul><li><a class="record-text" style="font-size:1em;" href="/search/results.php?start=0&year_start=1935&month_start=0&year_stop=1945&month_stop=12&van=C' . $pvannum2 . '" >' . $pvannum2 . '</a></li></ul></li></ul></li></ul></span></div><!--/#record-classification-->' . PHP_EOL;
	};
    echo '<div id="record-notes" class="record-field"><h3 class="record-heading">Lot Number<span style="font-size:.8em;color:grey; font-weight:normal;"> (Shooting Assignment)</span></h3>';
    if($plot != "0") {
    	echo '<a class="record-text" href="/search/results.php?start=0&lot=' . $plot . '&year_start=1935&month_start=0&year_stop=1945&month_stop=12">' . $plot . '</a>';
    };
    if($plot == "0") {
    	echo '<span class="record-text">None</span>';
    }
    echo '</div><!--/#record-notes-->' . PHP_EOL;
    echo '<div id="record-digitalid" class="record-field"><h3 class="record-heading">Call Number<span style="font-size:.8em;color:grey; font-weight:normal;"> (Library of Congress)</span></h3><a class="record-text" target="_blank" href="http://www.loc.gov/pictures/item/' . $fval['record'] . '">' . $pcnumber2 . '</a></div><!--/#record-digitalid-->' . PHP_EOL;
    
    echo '</div><!--/#record-meta-->' . PHP_EOL;
    echo '<div id="record-image"><img src="';
    if (substr($photourl, -2) != 'NA') {
		if ($photourl == '') {
			  echo '/images/nophotolarge.png';
		}
		if ($photourl != '') {
			echo 'http://photogrammar.research.yale.edu/photos' . $plargeurl;
		}
    }
    if (substr($photourl, -2) == 'NA') {
		echo 'http://photogrammar.research.yale.edu/photos' . $psmallurl;
    }

    echo '"/></div><!--/#record-image-->' . PHP_EOL;
}

$stmt->free_result();
$stmt->close();
$mysqli->close();

function get_post($var)
{
	return filter_input(INPUT_GET, $var, FILTER_SANITIZE_STRING);
}

?>

</div><!--/#record-content-->

</div><!--/#content-wrapper-->

</div><!--/#wrapper-->

<?php
include '../footer.php'; 
?>
