<?php
require_once "../../pg-config.php";
?>

<!doctype html>
<html lang=en>
<head>
<meta charset=utf-8>
<title>Photogrammar: FSA/OWI in Color</title>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0" />

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="tinycolor.js"></script>
<script type="text/javascript" src="jquery.tinysort.min.js"></script>
<script type="text/javascript" src="jquery.sparkline.min.js"></script>
</head>
<style>

/*

body {width:768px;}
*/
div.datef { font-size:.6em;margin-bottom:5px;}
div.ilsparkline {border:1px solid black;margin:2px;
	border-left:2px solid red;
width:165px;
float:left;
	border-right:2px solid red;
}
div.markerline {
	height:0;
	width:11px;
	border-top:1px solid white;
	position:relative;

}
div.colorhover {
	height:70px;
	width:10px;
	vertical-align:bottom;
	margin-left:1px;
	border-top:4px solid white;
	float:left;
}

.unbordered {
		border-top:1px solid white;
}

div#results {
	clear:both;
}
span.hue, span.sat, span.lum, span.count {display:none;}
p.heading {font-size:0.8em;
background-color: black;color:white;
padding:2px;margin-bottom:2px;
text-align: left;}
p.heading a, p.heading a:link, p.heading a:visited {text-decoration: none; font-weight:bold; color:white;}


@media only screen 
and (min-device-width : 768px) 
and (max-device-width : 1024px) 
and (orientation : landscape) { div.colorhover, div.markerline {	width:15px;}}


</style>

<script>
  function getHue(hex) {
  var result = tinycolor(hex).toHsv()  
  document.write('<span class="hue">' + Math.round(result.h) + '</span>');
  }
  function getSat(hex) {
  var result = tinycolor(hex).toHsv()  
  document.write('<span class="sat">' + (result.s * 100) + '</span>');
  }
  function getLum(hex) {
  var result = tinycolor(hex).toHsl()  
  document.write('<span class="lum">' + (result.l * 100) + '</span>');
  }
  </script>
<body style="font-family:sans-serif;">
<h3 style="margin:3px;">FSA/OWI Photography in Color. Touch a color! <span id="whichcolour"></span></h3>
<p class="heading" style="background-color:black;">
Touch to sort colors by: 
<a id="countbutton" href="javascript:$('div.colorhover').tsort('span.count',{order:'desc'});$('p.heading a').css('color','white');$('a#countbutton').css('color', 'red');$('span#whatorder').text('frequency');" style="color:red;">Frequency</a>
|
<a id="huebutton" href="javascript:$('div.colorhover').tsort('span.lum');$('div.colorhover').tsort('span.sat');$('div.colorhover').tsort('span.hue');$('p.heading a').css('color','white');$('a#huebutton').css('color', 'red');$('span#whatorder').text('hue');">Hue</a> 
|
<a id="satbutton" href="javascript:$('div.colorhover').tsort('span.hue');$('div.colorhover').tsort('span.lum');$('div.colorhover').tsort('span.sat');$('p.heading a').css('color','white');$('a#satbutton').css('color', 'red');$('span#whatorder').text('saturation');">Saturation</a> 
|
<a id="lumbutton" href="javascript:$('div.colorhover').tsort('span.hue');$('div.colorhover').tsort('span.sat');$('div.colorhover').tsort('span.lum');$('p.heading a').css('color','white');$('a#lumbutton').css('color', 'red');$('span#whatorder').text('luminosity');">Brightness</a> 

</p>
<div style="margin-bottom:5px;">
<?php
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
$hexquery = "SELECT hex, COUNT(hex) AS hexcount FROM colour GROUP BY hex ORDER BY hexcount DESC;";

if ($result = $mysqli->query($hexquery)) {
	while ($row = $result->fetch_assoc()) {
		echo '<div class="colorhover" style="background-color:#' . $row['hex'] .';">' . PHP_EOL . '	<div class="markerline" style="top:' . (70 - ($row['hexcount']*0.0965)) . 'px;"></div>' . PHP_EOL . '	<script type="text/javascript">getHue("#' . $row['hex'] . '");getSat("#' . $row['hex'] . '");getLum("#' . $row['hex'] . '");</script>' . PHP_EOL . '	<span class="count">' . $row['hexcount'] . '</span>' . PHP_EOL . '</div> <!-- /colorhover-->' . PHP_EOL;
	}
}
?>

</div>

<div id="results"></div>
</body>
  <script>

  $( document ).ready(function() {

  $( "div.colorhover" ).mouseenter(
  function() {
	  	$(this).css("border-top-color","black");	  
  });
  $( "div.colorhover" ).mouseleave(
  function() {
	  	$(this).css("border-top-color","white");	  
  });
  $( "div.colorhover" ).hover(
  function() {
  
  
  var color  = $(this).css("background-color");

  $.ajax({
   url: 'colours.php',
   data: { hex: tinycolor(color).toHex},
   type: "GET",
   success: function (response) {//response is value returned from php (for your example it's "bye bye"
     $("div#results").html(response);
$('.ilsparkline').sparkline('html',{type:'bar', tagValuesAttribute:"data-values", barWidth:3});
$( "span#thiscolour" ).text(tinycolor(color).toName());

   }
});
  var t = tinycolor(color)


    $( "span#whichcolour" ).text(tinycolor(color).toName() + ": " + $("span.count", this).text());
    
    $( "span#whichcolour" ).css('color',color);
   
  }, function() {
    
  }
);

 });
 
  </script>
</html>
