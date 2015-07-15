<?php
require_once 'login.php';

header('content-type: application/xml');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; 

if (!isset($_GET['format'])){ // Initial format discovery query
	echo '<formats><format name="rdf_bibliontology" type="application/xml" docs="http://www.w3.org/1999/02/22-rdf-syntax-ns#"/></formats>';
};

if (isset($_GET['format'])){

	$thispic = get_post('id');

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

    $query = "SELECT photo2.title as phototitle, pname, month, year, photo2.state, cnumber2, van0, van1, van2, medium FROM photo2 JOIN photo_old ON photo_old.controlNum=photo2.cnumber WHERE photo_old.controlNum = ?";
	// PREPARE QUERY
	if (!($stmt = $mysqli->prepare($query))) {
		if ($DEBUGGING)	{
			die("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
		}
		else {
			die($ERROR_MSG);
		}
	}
	// BIND VARIABLES
	if (!$stmt->bind_param( 's', $thispic )) {
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
	$stmt->bind_result($phototitle, $pname, $month, $year, $state, $cnumber2, $van0, $van1, $van2, $medium);
    
	$stmt->fetch();

	$ptitle = $phototitle;
	$pshorttitle = preg_replace('/\s+?(\S+)?$/', '', substr($ptitle, 0, 41)) . "...";
	$pnom = $pname;
	if ($pnom == "") { $pnom = "Unknown";};
	$pnomarray = explode(' ', $pnom);
	$pnominitials = "";
	foreach ($pnomarray as $initialbuilder) {
		$pnominitials .= $initialbuilder[0];
		}
	$pnomlast = end($pnomarray);
	$pnomfirst = $pnomarray[0];
	$pmon =  intval($month);
	$mons = array('1'=>'January ', '2'=>'February ', '3'=>'March ', '4'=>'April ', '5'=>'May ', '6'=>'June ', '7'=>'July ', '8'=>'August ',                 '9'=>'September ', '10'=>'October ', '11'=>'November ', '12'=>'December ', '0'=>'');

	$pdate = $mons[$pmon] . $year;
	if ($pdate == "0") { $pdate = "Unknown";};
	$ploc = $state;
	$callnumber = $cnumber2;
	$pvannum0 = $van0;
	$pvannum1 = $van1;
	$pvannum2 = $van2;
	$medium = $medium;

	$mediumandsize = $medium;
	$mediumandsizeexploded = explode(" ; ", $mediumandsize);
	$medium = $mediumandsizeexploded[0];
	$size = $mediumandsizeexploded[1];

	echo '<rdf:RDF xmlns:link="http://purl.org/rss/1.0/modules/link/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:res="http://purl.org/vocab/resourcelist/schema#" xmlns:z="http://www.zotero.org/namespaces/export#" xmlns:ctag="http://commontag.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:bilbio="http://purl.org/net/biblio#" xmlns:bibo="http://purl.org/ontology/bibo/" xmlns:foaf="http://xmlns.com/foaf/0.1/">
	
	<bibo:Image rdf:about="http://photogrammar.yale.edu/records/index.php?record='. $thispic .'">
	<dcterms:title>' . $pshorttitle . '</dcterms:title>
	<dcterms:date>' . $pdate . '</dcterms:date>
	<dcterms:rights>http://www.loc.gov/rr/print/res/071_fsab.html</dcterms:rights>
	<dcterms:subject>' . $pvannum0 . '</dcterms:subject>
	<dcterms:subject>' . $pvannum1 . '</dcterms:subject>
	<dcterms:subject>' . $pvannum2 . '</dcterms:subject>
	<dcterms:abstract>' . $ptitle . '</dcterms:abstract>
	<dcterms:medium>' . $medium . '</dcterms:medium>
	<dcterms:extent>' . $size . '</dcterms:extent>
	<bibo:uri>http://photogrammar.yale.edu/records/index.php?record=' . $thispic . '</bibo:uri>
	<bibo:lccn>' . $callnumber . '</bibo:lccn>
	<z:repository>Library of Congress Prints and Photographs Division Washington, DC 20540</z:repository>
	<dcterms:creator rdf:nodeID="' . $pnominitials . '"/>
	
	<link:link rdf:resource="#'. str_replace('/PP','',$thispic) .'"/>
	
	<bibo:authorList>
	<foaf:Person rdf:nodeID="' . $pnominitials .'">
		<foaf:surname>' . $pnomlast .'</foaf:surname>
		<foaf:givenname>' . $pnomfirst .'</foaf:givenname>
	</foaf:Person>
	</bibo:authorList>
	</bibo:Image>
	    <z:attachment rdf:about="#'. str_replace('/PP','',$thispic) .'">
	    <z:itemType>attachment</z:itemType>
	    <dc:title>Photo Itself</dc:title>
	    <dcterms:dateSubmitted>2014-10-12 20:57:56</dcterms:dateSubmitted>
	    <dc:identifier>
	        <dcterms:URI>
	            <rdf:value>http://photogrammar.research.yale.edu/photos/service/pnp/fsa/8a21000/8a21200/8a21264v.jpg</rdf:value>
	        </dcterms:URI>
	    </dc:identifier>
    </z:attachment>
	</rdf:RDF>';};
?>