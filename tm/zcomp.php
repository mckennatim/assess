<?php
$dbhost = 'localhost';
$dbuser = 'pathbost_user';
$dbpass = 'user';
$dbname = 'pathbost_ob';

mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
mysql_select_db($dbname);

$zip=$_GET[zip];


$return_arr = array();

$trying ="LOOKING UP ZIPCODE ".$pid." is complete"; //fb($trying);
$sql = "SELECT *
FROM zip_codes
WHERE zip like '%" . $zip . "%' LIMIT 6";
//fb($sql);
$result = mysql_query($sql) or die($trying);


while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$row_array['zip'] = $row['zip'];
	$row_array['value'] = $row['zip'];
	$row_array['location'] = $row['city'];
	
	array_push($return_arr,$row_array);
    }	

/* Free connection resources. */
mysql_close($conn);
/* Toss back results as json ,  array.  ggg*/
echo json_encode($return_arr);
?>