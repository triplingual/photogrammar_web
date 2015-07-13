<?php
$page = search;
$ERROR_MSG = "An error occurred. Please try again or send a message to <a href='mailto:thephotogrammar@gmail.com'>thephotogrammar@gmail.com</a> describing what happened.";
$bodyopts = 'onload="initialize()"';
include '../header.php';
require_once 'login.php';


if(isset($_GET['record']))
{
    
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
$mons = array('1'=>'January ', '2'=>'February ', '3'=>'March ', '4'=>'April ', '5'=>'May ', '6'=>'June ', '7'=>'July ', '8'=>'August ',
                 '9'=>'September ', '10'=>'October ', '11'=>'November ', '12'=>'December ', '0'=>'');
echo <<<_END

_END;

if(isset($_GET['record']))
{
    
    
    
    if($ptitle == "") $ptitle = "None";
    if($ploc == "") $ploc = "Unknown";
    if($pvannum0 == "" & $pvannum0 == "" & $pvannum0 == "") $pvannum0 = "None";

    echo '<div id="record-meta"><!--<h2>Record Information</h2>-->';
    echo '<div id="record-title"><h3 class="record-heading first">Caption <span style="font-size:.8em; color:grey; font-weight:normal;">(Original Description)</span></h3><span class="record-text">' . $ptitle . '</div><!--/#record-title--><div id="record-photographer"><h3 class="record-heading">Photographer</h3>';


     if($pnom != "") {
   
    }

     if($pnom == "") {
   
    echo '<span class="record-text">Unknown</span>';
    }    
    
    echo '</div><!--/#record-photographer-->';
    echo '<div id="record-created" class="record-field"><h3 class="record-heading">Created</h3>';
    if($pdate != "0") {
    }
    if($pdate == "0") { echo '<span class="record-text">Unknown</span>';};
    echo '</div><!--/#record-created-->';
    
    
     
    echo '<div id="record-notes" class="record-field"><h3 class="record-heading">Location</h3><span >' . $ploc . '</span></div><!--/#record-notes-->';
	if ($pvannum1!="") {
	};
    echo '<div id="record-notes" class="record-field"><h3 class="record-heading">Lot Number<span style="font-size:.8em;color:grey; font-weight:normal;"> (Shooting Assignment)</span></h3>';
    if($plot != "0") {
    };
    if($plot == "0") {
    echo '<span class="record-text">None</span>';
    }
    echo '</div><!--/#record-notes-->';
    
    echo '</div><!--/#record-meta-->';
    echo '<div id="record-image"><img src="';
    if (substr($photourl, -2) != 'NA') {
  if ($photourl == '') {
	      echo '/images/nophotolarge.png';
      }
      if ($photourl != '') {
  	  }
    }
    if (substr($photourl, -2) == 'NA') {
    }

    
    echo '"/></div><!--/#record-image-->';

}


function get_post($var)
{
}

echo <<<_END

</div><!--/#record-content-->

</div><!--/#content-wrapper-->


</div><!--/#wrapper-->

_END;

include '../footer.php'; ?>
