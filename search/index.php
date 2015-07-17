<?php
require_once "../pg-config.php";
$page = "search"; 
include '../header.php';
?>

<style>
#search-content {
	margin:45px auto;
	padding:10px;
	width:500px;
	height:100%;
        background-color:#777777;
        color:white;
}
.search-row {
	margin:0 0 10px;
}
.search-label {
	display:inline-block;
	width:180px;
}
.search-field {
	display:inline-block;
}
#search-content h3 {
	display:inline;
	font-family: Arial, Helvetica, sans-serif;
	font-size:1.1em;
	font-weight:normal;
}
#search-content input {
	font-size:1em;
	width:300px;
}
#search-full .search-field input {
	width: 350px;
}
#search-button {
	margin:20px 0 0 182px;
}
#search-button button {
	font-family: Arial, Helvetica, sans-serif;
	font-size:1.4em;
	font-weight:bold;
	padding:5px 10px;
}
</style>
 
<link href="select2/select2.css" rel="stylesheet"/>
<link href="select2-bootstrap.css" rel="stylesheet"/>
<style>
div.select2-result-label, .select2-choice, .select2-searching, .select2-no-results {font-family:sans-serif;}
</style>
    
    <script type="text/javascript">
      function main() {
    history.navigationMode = 'compatible';
    window.onunload = function(){};
    
        $('document').ready(function(){
 
        	    $(window).bind('pageshow', function() { 
        	    
        	      $('#state').select2("val", null);
        	      $('input#county').val(null);
        	      $('input#city').val(null);
				
				 });
	
				
						   	 
			$("#van").select2({
            	placeholder: "Choose a Classification",
				allowClear: true,
				minimumInputLength: 3,
				ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
	                url: "http://dh.library.yale.edu/projects/photogrammar/tags.php",
	                dataType: 'json',
	                data: function (term, page) {
	                    return {
	                        q: term, // search term
	                    };
	                },
                results: function (data, page) { // parse the results into the format expected by Select2.
                    // since we are using custom formatting functions we do not need to alter remote JSON data
                    return {results: data.results};
                }
            }
        });

     
                $("#pname").select2({
               		 placeholder: "Choose a Photographer",
			   		 allowClear: true
			   	 });

                $("#lot").select2({
               		 placeholder: "Lot Number",
			   		 allowClear: true
			   	 });


			    $('#state').select2({
               	 placeholder: "Choose a State",
			   	 allowClear: true
			   	 }).on("change", function() {
			   	 	 if($('#state').val()=="Louisiana") {
			   	 	 
			   	 	 	$('#county').attr('placeholder', 'Choose a Parish');
			   	 	 }
			   	 	 else if($('#state').val()=="Alaska") {
			   	 	 	$('#county').attr('placeholder', 'Choose a Borough');

			   	 	 }
			   	 	 else {
			   	 	 	$('#county').attr('placeholder', 'Choose a County');
				   	 	 
			   	 	 };
		   	 		 $("#county").select2('val', ''); 
		   	 		 $("#city").select2('val', '');
		   	 		 $.ajax('counties.php?state=' + $('#state').val()).success(function(countyData) {						
			   	 		 $('#county').select2({ 
				   	 		 placeholder: "Choose a County",
				   	 		 data: countyData,
				   	 		 allowClear: true
				   	     }).on("change", function() {
							$("#city").select2('val', '');
							$.ajax('cities.php?state=' + $('#state').val() + '&county=' + $('#county').val()).success(function(cityData) {						
			   	 		 $('#city').select2({ 
				   	 		 placeholder: "Choose a City",
				   	 		 data: cityData,
				   	 		 allowClear: true
				   	     })}); 

						 })}); 
		   	 		 $.ajax('cities.php?state=' + $('#state').val() + '&county=' + $('#county').val()).success(function(cityData) {						
			   	 		 $('#city').select2({ 
				   	 		 placeholder: "Choose a City",
				   	 		 data: cityData,
				   	 		 allowClear: true
				   	     })}); 


			
					}).on("select2-loaded", function(e) { log ("loaded (data property omitted for brevity)");});
			    
			     
			    });
			    };
			   window.onload = main;
       </script>
 
<div style="background-color:#777777; width:100%">

<div id="wrapper"style="background-color:#777777" class="clearfix">

<div id="content-wrapper" class="clearfix">

<div id="search-content">

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

$fval = array('pname'=>'', 'month_start'=>'', 'month_stop'=>'', 'year_start'=>'', 'year_stop'=>'', 'van'=>'', 'lot'=>'', 'city'=>'', 'county'=>'', 'state'=>'', 'title'=>'', 'start'=>0);
?>

<form action='results.php' method='get' id='photosearch' class='clearfix'>
<input type ="hidden" id="start" name="start" value=0>
<fieldset>
<div>
<legend style="font-family:sans-serif;font-weight:bold;color:#fff;">Full Text</legend>
<div id="search-full" class="search-row">

    <div class="search-field">
    	<input type ="text" name="search"/> <button class="button" style='font-family: Arial, Helvetica, sans-serif;
	font-size:1em;
	font-weight:bold;
	padding:5px 10px;' type='submit' value='Search'>Search</button>
	
    </div>
</div><!--/#search-full-->
</div>
</fieldset>

<fieldset  style="margin-top:30px;">
<legend style="font-family:sans-serif;font-weight:bold;color:#fff;">Advanced</legend>

<div id="search-photographer" class="search-row">
	<div class="search-label">
    	<h3>Photographer</h3>
    </div>
    <div class="search-field">
	<select id="pname" name="pname" style="width:250px;">

<?php
	$pnamequery = "SELECT DISTINCT pname FROM photo2 ORDER BY pname ASC;";
	if ($result = $mysqli->query($pnamequery)) {
	} else {
		if ($DEBUGGING)	{
	    	echo "Photographer name query failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		else {
			die($ERROR_MSG);
		}
	}

	while($row = $result->fetch_object()){
		echo "<option value=\"" . $row->pname . "\">" . $row->pname . "</option>" . PHP_EOL;
	}

    $result->close();
?>
 
   	</select>
    </div>
           
</div><!--/#search-photographer-->

<div id="search-lot" class="search-row">
	<div class="search-label">
    	<h3>Lot Number</h3>
    </div>
    <div class="search-field">
   
     <select id="lot" name="lot" style="width:250px;">
     <option></option>

<?php
    $pnamequery = "SELECT DISTINCT lotnum FROM photo2 WHERE lotnum !='0' ORDER BY lotnum ASC";
	if ($result = $mysqli->query($pnamequery)) {
	} else {
		if ($DEBUGGING)	{
	    	echo "Lot number query failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		else {
			die($ERROR_MSG);
		}
	}

	while($row = $result->fetch_object()){
		echo "<option value=\"" . $row->lotnum . "\">" . $row->lotnum . "</option>" . PHP_EOL;
	}

    $result->close();
?>
   	</select>
    </div>
     <br><div  style="margin-left:190px;width:250px;color:#e3e3e3;font-size:.7em;font-family:sans-serif;">88,000 photographs were assigned a lot number, indicating a set of photographs organized primarily around a shooting assignment. As a result, lots tend to feature one photographer&rsquo;s set of photographs in a single place. For example, <a style="color:black;" href="/search/results.php?start=0&amp;search=&amp;pname=&amp;lot=1070&amp;van=&amp;state=&amp;county=&amp;city=&amp;year_start=1935&amp;month_start=1&amp;year_stop=1945&amp;month_stop=12">Lot 1070</a> features Arthur Rothstein&rsquo;s set in Clinton, Indiana in February 1940. Paul Vanderbilt developed the lot system. </div>

</div><!--/#search-lot-->

<div id="search-classification" class="search-row clearfix">
    <div class="search-label">
    	<h3>Classification Tags</h3>
    </div>
    <div class="search-field">
        <input type="hidden" id ="van" name="van" style="width:250px;" />

    </div><!--/.search-field-->
   <br><div style="margin-left:190px;width:250px;color:#e3e3e3;font-size:.7em;font-family:sans-serif;">88,000 photographs in the collection have tags assigned.  There are twelve main subject headings (ex. THE LAND) and 1300 sub-headings (ex. Mountains, Deserts, Foothills, Plains).   Paul Vanderbilt began to develop the classification system in 1942. </div>
</div><!--/#search-classification-->

<div id="search-place">
    <div id="search-state" class="search-row">
        <div class="search-label">
            <h3>Location</h3>
        </div>
        <div class="search-field">
     <select id="state" name="state" style="width:250px;">
<?php
    $pnamequery = "SELECT DISTINCT state FROM photo2 ORDER BY state ASC";
	if ($result = $mysqli->query($pnamequery)) {
	} else {
		if ($DEBUGGING)	{
	    	echo "State query failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		else {
			die($ERROR_MSG);
		}
	}

	while($row = $result->fetch_object()){
		echo "<option value=\"" . $row->state . "\">" . $row->state . "</option>" . PHP_EOL;
	}

    $result->close();
?>
 
   	</select>  
   	<input id="county" type="hidden"  style="width:200px;" name="county"/>  
   	<input id="city" type="hidden" style="width:200px;" name="city"/>    
   	</div>
    </div>
</div><!--/#search-place-->

<div id="search-year" class="search-row">
    <div class="search-label">
        <h3>From</h3>
    </div>
    <div class="search-field">
        <select name="year_start"> 
            <option value=1935 selected>1935</option>
            <option value=1936>1936</option>
            <option value=1937>1937</option>
            <option value=1938>1938</option>
            <option value=1939>1939</option>
            <option value=1940>1940</option>
            <option value=1941>1941</option>
            <option value=1942>1942</option>
            <option value=1943>1943</option>
            <option value=1944>1944</option>
            <option value=1945>1945</option>
    	</select>
    	<span> / </span>
        <select name="month_start"> 
            <option value=0 selected>Choose a Month</option>

            <option value=1>January</option>
            <option value=2>February</option>
            <option value=3>March</option>
            <option value=4>April</option>
            <option value=5>May</option>
            <option value=6>June</option>
            <option value=7>July</option>
            <option value=8>August</option>
            <option value=9>September</option>
            <option value=10>October</option>
            <option value=11>November</option>
            <option value=12>December</option>
        </select>
    </div>
</div><!--/#search-year-->
<div id="search-month" class="search-row">
    <div class="search-label">
    	<h3>To</h3>
    </div>
    <div class="search-field">
        <select name="year_stop"> 
            <option value=1935>1935</option>
            <option value=1936>1936</option>
            <option value=1937>1937</option>
            <option value=1938>1938</option>
            <option value=1939>1939</option>
            <option value=1940>1940</option>
            <option value=1941>1941</option>
            <option value=1942>1942</option>
            <option value=1943>1943</option>
            <option value=1944>1944</option>
            <option value=1945 selected>1945</option>
        </select>
        <span> / </span>
        <select name="month_stop"> 
            <option value=1 selected>January</option>
            <option value=2>February</option>
            <option value=3>March</option>
            <option value=4>April</option>
            <option value=5>May</option>
            <option value=6>June</option>
            <option value=7>July</option>
            <option value=8>August</option>
            <option value=9>September</option>
            <option value=10>October</option>
            <option value=11>November</option>
            <option value=12 selected>December</option>
        </select>
    </div>
</div><!--/#search-month-->

<div id="search-button">
	<button type='submit' style="font-size:1em;" value='Search'>Search</button>
</div><!--/#search-button-->
</fieldset>
</form>
</div>

<?php
$mysqli->close();

function get_post($var)
{
	return filter_input(INPUT_GET, $var, FILTER_SANITIZE_STRING);
}
function sanitize_int($var)
{
	return filter_input(INPUT_GET, $var, FILTER_SANITIZE_NUMBER_INT);
}
?>

</div><!--/#search-content-->

</div><!--/#content-wrapper-->

</div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="select2/select2.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/holder.js"></script>
  </body>
</html>
