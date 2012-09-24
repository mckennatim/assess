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
	$fetch = mysql_query("SELECT *
	FROM topics
	LEFT JOIN units
	USING ( uid )
	LEFT JOIN courses
	USING ( course ) where topic like '%" . $_GET['term'] . "%'"); 

	while ($row = mysql_fetch_array($fetch, MYSQL_ASSOC)) {
		$row_array['id'] = $row['tid'];
		$row_array['value'] = $row['topic'];
		$row_array['unit'] = $row['unit'];
		$row_array['course'] = $row['course'];
		
        array_push($return_arr,$row_array);
    }	
}
/* Free connection resources. */
mysql_close($conn);
/* Toss back results as json encoded array. */
echo json_encode($return_arr);
?>