<?php
echo header("Content-type: text/plain");
include_once('../tm/utility.php');
include_once('../tm/dbinfo.php');
$limi=$_POST[limi];
$sourceID=$_POST[sourceID];
$loc=$_POST[loc];
$subloc=$_POST[subloc];

 
if ($limi=="yes"){
	$wherestr = "WHERE sourceID='".$sourceID. "' AND loc='" . $loc . "' AND subloc='" . $subloc ."' ORDER BY sourceID, loc, subloc, word";
} else {
	$wherestr = "WHERE sourceID='".$sourceID. "' ORDER BY sourceID, loc, subloc, word";
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$bstr="====".$sourceID.$loc.".".$subloc."==== \n";
// create the SQL query that returns a page of vocab
$wvocabQuery = "SELECT word, wid, did
	FROM vdefs LEFT JOIN vwords USING ( did ) LEFT JOIN vcontexts USING ( did )" .$wherestr;
//echo(" wvocabQuery= ".$wvocabQuery." \n");
if ($wresult = $db->query($wvocabQuery)) 
{
	// fetch associative array 
	$oldword = "";
	while ($wrow = $wresult->fetch_assoc()) 
	{
		$did = $wrow["did"];
		//echo(" did= ".$did. " \n");
		if ($oldword != $wrow["word"]) $bstr.= ";".$wrow["word"];
		$oldword = $wrow["word"] ;
		$dvocabQuery = "SELECT def, did FROM vdefs LEFT JOIN vwords USING ( did )
			LEFT JOIN vcontexts USING ( did ) WHERE did='".$did."'";
		//echo(" dvocabQuery= ".$dvocabQuery ." \n");
		if ($dresult = $db->query($dvocabQuery)) 
		{
			// fetch associative array 
			while ($drow = $dresult->fetch_assoc()) 
			{
				$bstr .= ": ".$drow["def"]."\n";
				$cvocabQuery = "SELECT sentence, pos, did FROM vcontexts WHERE did=".$did;
				//echo(" cvocabQuery= ".$cvocabQuery ." \n");
				if ($cresult = $db->query($cvocabQuery)) 
				{
					// fetch associative array 
					while ($crow = $cresult->fetch_assoc()) 
					{
						if (strlen($crow["sentence"]) > 0 or strlen($crow["pos"]) > 0)
						{
							$addstr = ":: ''".$crow['sentence']. "'' [".$crow['pos']. "]\n";
							//echo("\n addstr = ".$addstr);
							$bstr .= $addstr;
						}
					}
				}
				$yvocabQuery = "SELECT yourdef, youruse, ypos, yourID, yourOK, rating, did FROM vyour WHERE did=".$did;
				//echo(" yvocabQuery= ".$yvocabQuery ." \n");
				if ($yresult = $db->query($yvocabQuery)) 
				{
					// fetch associative array 
					while ($yrow = $yresult->fetch_assoc()) 
					{
						$bstr .= "::* {".$yrow["yourdef"]. "} ".trim($yrow["youruse"]). " [".$yrow["ypos"]. "] (". $yrow["yourID"]. ", ".$yrow["yourOK"]. ", ". $yrow["rating"].")\n";
					}
				}	
			}
		}
	}
}
echo($bstr);
?>