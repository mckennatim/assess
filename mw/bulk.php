<?php
// defines database connection data
define('DB_HOST', 'localhost');
define('DB_USER', 'pathbost_tim');
define('DB_PASSWORD', 'nji9ol');
define('DB_DATABASE', 'pathbost_forms'); 
// defines the number of visible rows in grid
$course=$_POST[course];
$section=$_POST[section];
$apage=stripslashes($_POST[apage]);

$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
$sql = "SELECT wikiname FROM `students` WHERE course='$course' ORDER BY section, fname ASC";
//$queryString = "SELECT course, def, sentence FROM vocab WHERE source='$source' ORDER BY word ASC";

// execute the query
$defarr=array();
if ($result = $db->query($sql)) 
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
}
$result->close();

$bigstr = "";
$dstr = "";
$oblock = "Z";
foreach($defarr as $rec){
	$bigstr .= $rec['wikiname']."\n";
}
$File = "users.txt";
$Handle = fopen($File, 'w');
$Data = $bigstr;
fwrite($Handle, $Data);
fclose($Handle);
$File = "apage.mediawiki";
$Handle = fopen($File, 'w');
$Data = $apage;
fwrite($Handle, $Data);
fclose($Handle);
echo header("Content-type: text/plain");
//echo header('Content-type: application/octet-stream');
//echo header('Content-Disposition: inline; filename="filename.csv"');
echo $apage;
echo $bigstr;
?>