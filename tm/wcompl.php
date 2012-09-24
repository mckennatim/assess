<?php
require_once('../tm/dbinfo.php');
$return_arr = array();

$conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die ('Error connecting to mysql');
mysql_select_db(DB_DATABASE);

if ($conn)
{
	$fetch = mysql_query("SELECT *
	FROM vwords
 	WHERE word LIKE '%" . $_GET['term'] . "%'"); 

	while ($row = mysql_fetch_array($fetch, MYSQL_ASSOC)) {
		$row_array['id'] = $row['wid'];
		$row_array['value'] = $row['word'];
		
        array_push($return_arr,$row_array);
    }	
}
/* Free connection resources. */
mysql_close($conn);
/* Toss back results as json encoded array. */
echo json_encode($return_arr);
?>