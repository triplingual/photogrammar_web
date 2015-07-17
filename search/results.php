<?php
// TODO : Results paging/buffering

require_once 'login.php';
$page = "search"; 
$ERROR_MSG = "An error occurred. Please try again or send a message to <a href='mailto:thephotogrammar@gmail.com'>thephotogrammar@gmail.com</a> describing what happened.";
$bodyopts = 'onload="initialize()"';
include '../header.php';
?>

<style>
#results-content {
	margin:0 auto;
	padding:0;
}
#results-header {
	border-bottom:1px dotted #DD4B39;
	font-family:Arial, Helvetica, sans-serif;
	margin:20px 0 0;
	padding:0;
}
#results-footer {
	font-family:Arial, Helvetica, sans-serif;
	margin:20px 0 0;
	padding:0;
}
#results-total {
	display:inline;
}
#results-total h2 {
	display:inline;
}
#results-total span {
	display:inline-table;
}
#results-pager {
	display:inline;
	float:right;
	margin:8px 0 0;
}
#results-pager span{
	font-weight:bold;
}
#results-pager a, #results-pager a:visited {
	color:#DD4B39;
	text-decoration:none;
}
#return-link {
	padding:10px 0;
	text-align:right;
}
#return-link a, #return-link a:visited {
	color:#DD4B39;
	font-family:Arial, Helvetica, sans-serif;
	text-decoration:none;
}
#return-link a:hover {
	color:#DD4B39;
	text-decoration:underline;
}
.results-container {
	color:#666;
	display:block;
	float:left;
	font-family:Verdana, Arial, Helvetica, sans-serif;
	font-size:.7em;
	height:272px;
	margin: 10px 0px 10px 0px;
	padding: 0px 10px 0px 10px;
	overflow: hidden;
	width:200px;
}
.results-image {
	display: table-cell;
	height: 170px;
	text-align: center;
	vertical-align: bottom;
	white-space: nowrap;
	width: 170px;
}
.results-thumb {
	max-height: 170px;
	max-width: 170px;
}
#results-meta {
	border-top:dotted 1px #DD4B39;
	margin:7px 0 0;
	padding:7px 0 0;
}
#results-title {
	font-weight:bold;
	height:52px;
	margin:0 0 5px;
}
#results-photographer {
	margin:0 0 3px;
}

.nobr {
	white-space:nowrap;
}
</style>


    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
    <script type="text/javascript" src="/js/jquery.google_menu.js"></script>
    
    <script>
            $('document').ready(function(){
                $('.menu').fixedMenu();
            });
    </script>
    
<div id="wrapper" class="clearfix" style="padding:75px">

<div id="content-wrapper" class="clearfix" style="max-width:1000px" >

<div id="results-content" class="clearfix">

<div id="results-header" class="clearfix">
<div id="results-header-toprow">
<?php

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

$fval = array('pname'=>'', 'month_start'=>'', 'month_stop'=>'', 'year_start'=>'', 'year_stop'=>'', 'van'=>'', 'lot'=>'', 'city'=>'',
                 'county'=>'', 'state'=>'', 'start'=>0);
$mons = array('1'=>'January ', '2'=>'February ', '3'=>'March ', '4'=>'April ', '5'=>'May ', '6'=>'June ', '7'=>'July ', '8'=>'August ',
                 '9'=>'September ', '10'=>'October ', '11'=>'November ', '12'=>'December ', '0'=>'');

if(isset($_GET['start']))
{

    $fval['pname'] = $_GET['pname'];
//    $fval['month_start'] = sanitize_int('month_start'); // something's not working here
    $fval['month_start'] = $_GET['month_start'];
    $fval['month_stop'] = $_GET['month_stop'];
    $fval['year_start'] = $_GET['year_start'];
    $fval['year_stop'] = $_GET['year_stop'];
    $fval['van'] = $_GET['van'];
    $fval['lot'] = $_GET['lot'];
    $fval['city'] = ($_GET['city'] != "NA") ? $_GET['city'] : '';
    $fval['county'] = $_GET['county'];
    $fval['state'] = $_GET['state'];
    $fval['start'] = $_GET['start'];

    $van_code = substr(get_post('van'), 0, 1);
    $van_string = substr(get_post('van'), 1);

// BUILD QUERYSTRING, ABSTRACTING VALUES AS APPROPRIATE
    if(get_post('search') != "") {
        $querySearch = "+" . get_post('search');
        $querySearch = str_replace(" ", " +", $querySearch);
        $querySearch = str_replace(" +NOT +", " -", $querySearch);
        $querySearch = str_replace("+NOT +", " -", $querySearch);
        $query = "SELECT month, year, title, thumb_url, small_url, pname, cnumber FROM photo  WHERE fips != 'NA' AND MATCH(pname, van0, van1, van2, city, county, state, country, title) AGAINST('" . $querySearch . "' IN BOOLEAN MODE) ";

	// PREPARE QUERY
		if (!($stmt = $mysqli->prepare($query))) {
			if ($DEBUGGING)	{
				die("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
			}
			else {
				die($ERROR_MSG);
			}
		}

    } else {
		$query = "SELECT month, year, title, thumb_url, small_url, pname, cnumber FROM photo WHERE fips != 'NA' AND pname LIKE ? ";
		$query .= ($fval['lot'] == '') ? "AND lotnum > ? " : "AND lotnum = ? ";
		$query .= "AND city LIKE ? AND " .
				 "county LIKE ? AND " .
				 "state LIKE ? AND " .
				 "year >= ? AND year <= ? AND " .
				 "month >= ? AND month <= ? ";

		if($van_code == "A") {
			$query = $query . " AND van0 = ?";
		} else if($van_code == "B") {
			$query = $query . " AND van1 = ?";
		} else if($van_code == "C") {
			$query = $query . " AND van2 = ?";
		} else {
			$query = $query . " AND van0 LIKE ?";
		}

		$query = $query . " ORDER BY year, month, cnumber";

	// ASSIGN VALUES TO VARIABLES
		$pname_query = "%" . $fval['pname'] . "%";
		$month_start_query = $fval['month_start'];
		$month_stop_query = $fval['month_stop'];
		$year_start_query = $fval['year_start'];
		$year_stop_query = $fval['year_stop'];
		$van_query = "%" . $fval['van'] . "%";
		$city_query = "%" . $fval['city'] . "%";
		$county_query = "%" . $fval['county'] . "%";
		$state_query = "%" . $fval['state'] . "%";
		$start_query = "%" . $fval['start'] . "%";
		$lotnum = ($fval['lot'] == '') ? -1 : $fval['lot'];

	// PREPARE QUERY
		if (!($stmt = $mysqli->prepare($query))) {
			if ($DEBUGGING)	{
				die("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
			}
			else {
				die($ERROR_MSG);
			}
		}

	// BIND PARAMETERS
		if (!$stmt->bind_param( 'sisssiiiis', $pname_query, $lotnum, $city_query, $county_query, $state_query, $year_start_query, $year_stop_query, $month_start_query, $month_stop_query, $van_query )) {
			if ($DEBUGGING)	{
				die("Binding parameters failed: (ERROR #" . $stmt->errno . ", ERROR MESSAGE: " . $stmt->error . " )<br />\n");
			}
			else {
				die($ERROR_MSG);
			}
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
	$stmt->bind_result($res_month, $res_year, $res_title, $res_thumb_url, $res_small_url, $res_pname, $res_cnumber);
    
    $rows = $stmt->num_rows;
   	$pictures = ( $rows > 1 ) ? "pictures" : "picture";

    if(get_post('search') != "") {
        echo '<div id="results-total"><h2>' . $rows . ' ' . $pictures . ' of ' . get_post('search') . '</h2>';
    } else {
        echo '<div id="results-total"><h2>Advanced Search</h2><br>';
		echo '<span>';
		echo $rows . " " . $pictures ." ";
		if ($fval['pname'] != '') { echo "by " . $fval['pname']; };
		if ($fval['lot'] != '') { echo " in Lot Number " . $fval['lot']; };
		echo  " from ";
		if ($fval['month_start'] != '') {echo $mons[$fval['month_start']] . " ";};
		if ($fval['month_start'] == '0') {echo "January ";};
		echo $fval['year_start'] . " to " . $mons[$fval['month_stop']] . " " .  $fval['year_stop'] . ": ";
        echo '</span>';
    }
	echo '</div><!--/#results-total-->' . PHP_EOL;

	echo build_pager_markup($rows, $_GET);

    echo '</div><!--/#results-header-toprow-->' . PHP_EOL;
    echo '</div><!--/#results-header-->' . PHP_EOL;
    echo '<div id="return-link" class="clearfix"><a href="/search/">Start New Search</a></div><!--/#return-link-->' . PHP_EOL;
    echo '<div id="results-gallery" class="clearfix">';

    for($j = sanitize_int('start'); $j < min(sanitize_int('start') + 60, $rows); ++$j)
    {
    	$stmt->data_seek($j);
    	$stmt->fetch();

        $pmon =  intval($res_month);
        $pdate = $mons[$pmon] . $res_year;
        $ptitle = $res_title;
        if(strlen($ptitle) > 90) {
		    $ptitle = preg_replace('/\s+?(\S+)?$/', '', substr($ptitle, 0, 85)) . "<span class='nobr'> . . .</span>";
        }
       echo '<div class="results-container">';
       echo '<div class="results-image"><a href=/records/index.php?record=' . $res_cnumber . '>';
       echo '<img class="results-thumb" src="';
    if (substr($res_thumb_url, -2) != 'NA') {
      if ($res_thumb_url == '') {
	      echo '/images/nophoto.png';
      }
      if ($res_thumb_url != '') {
  	  	echo 'http://photogrammar.research.yale.edu/photos' . $res_thumb_url;
  	  }
    }
    if (substr($res_thumb_url, -2) == 'NA') {
  	  echo 'http://photogrammar.research.yale.edu/photos' . $res_small_url;
    }
       echo '" />';
       echo '</a></div><!--/.results-image-->' . PHP_EOL;
       echo '<div id="results-meta">';
       echo '<div id="results-title">' . $ptitle . '</div>';
       echo '<div id="results-photographer">' .$res_pname . '</div>';
       echo '<div id="results-date">' . $pdate . '</div>';
       if(($j - sanitize_int('start')) % 6 == 5);
       echo '</div><!--/#results-meta-->' . PHP_EOL;
       echo '</div><!--/.results-container-->' . PHP_EOL;
    }
    echo '</div><!--/#results-gallery-->' . PHP_EOL;
	echo build_pager_markup($rows, $_GET);

    echo '</div><!--/#results-footer-->' . PHP_EOL;

}

$stmt->free_result();
$stmt->close();
$mysqli->close();

function get_post($var)
{
	return filter_input(INPUT_GET, $var, FILTER_SANITIZE_STRING);
}
function sanitize_int($var)
{
	return filter_input(INPUT_GET, $var, FILTER_SANITIZE_NUMBER_INT);
}

function build_pager_markup( $rows, $querystring )
{
	$pagerstring = "";
    if($rows != 0) {
    	$pagerstring = '<div id="results-pager"><span>Results: </span>';
    	if(sanitize_int('start') != 0) {
        	$query_arr = $_GET;
        	$query_arr["start"] = max($query_arr["start"] - 60, 0);
        	$query_call = http_build_query($query_arr);
        	$pagerstring .= '<a href="' . '/search/results.php?' . $query_call . '">&laquo; </a>';
    	}
        	$pagerstring .= (sanitize_int('start') + 1)  . ' &#8211; ' . min(sanitize_int('start') + 60, $rows) . ' of ' . $rows;
    }
	if(sanitize_int('start') + 60 < $rows) {
		$query_arr = $_GET;
		$query_arr["start"] = $query_arr["start"] + 60;
		$query_call = http_build_query($query_arr);
		$pagerstring .= '<a href="' . '/search/results.php?' . $query_call . '"> &raquo;</a>'; 
	}
    if($rows != 0) {
	    $pagerstring .= '</div><!--/#results-pager-->' . PHP_EOL;
	}

	return $pagerstring;
}

?>

</div><!--/#results-content-->

</div><!--/#content-wrapper-->

</div><!--/#wrapper-->

</body>

</html>
<?php
include '../footer.php'; 
?>
