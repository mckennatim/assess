<?php
$return_arr = array();

$dbhost = 'localhost';
$dbuser = 'pathbost_tim';
$dbpass = 'nji9ol';
$dbname = 'pathbost_assess';

$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
mysql_select_db($dbname);

if ($conn)
{
	$fetch = mysql_query("SELECT DISTINCT sourceID, loc, subloc, CONCAT(sourceID,'.',loc,'.', subloc) AS sourceloc
	FROM vcontexts where sourceID like '%" . $_GET['term'] . "%'"); 

	while ($row = mysql_fetch_array($fetch, MYSQL_ASSOC)) {
		$row_array['id'] = $row['sourceID'];
		$row_array['value'] = $row['sourceloc'];
		$row_array['loc'] = $row['loc'];
		$row_array['subloc'] = $row['subloc'];
		$row_array['sourceID'] = $row['sourceID'];
		
        array_push($return_arr,$row_array);
    }	
}
/* Free connection resources. */
mysql_close($conn);
/* Toss back results as json encoded array. */
echo json_encode($return_arr);
?>