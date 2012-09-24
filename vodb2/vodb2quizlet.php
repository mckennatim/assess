<?php
include_once('../tm/utility.php');
include_once('../tm/dbinfo.php');
$limi=$_POST[limi];
$sourceID=$_POST[sourceID];
$loc=$_POST[loc];
$subloc=$_POST[subloc];

$wherestr = " WHERE sourceID='".$sourceID."'"; 
if ($limi=="yes"){
	$wherestr .= " AND loc='" . $loc . "' AND subloc='" . $subloc ."'";
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$queryString = "SELECT *
FROM vdefs
LEFT JOIN vwords
USING ( did )
LEFT JOIN vcontexts
USING ( did )" . $wherestr;

//echo $queryString;

// execute the query
$defarr=array();
if ($result = $db->query($queryString)) 
{
	$i=0;
	// fetch associative array 
	while ($row = $result->fetch_assoc()) 
	{
		foreach($row as $word=>$val){
			$defrec[$word]=$val;				
		}
		// close the results stream                     
		$defarr[$i]=$defrec;
		$i++;
		//echo $i;
	}
	$result->close();
}
//print_r($defrec);
$defarr=array_slice($defarr, 0, $numQuestions);;
$bigstr = '';
foreach($defarr as $rec){
	$bigstr .= $rec['word']." (".$rec['sentence'].")\t".$rec['def']."\n";
}
echo header("Content-type: text/plain");
//echo header('Content-type: application/octet-stream');
//echo header('Content-Disposition: inline; filename="filename.csv"');

echo $bigstr;
?>