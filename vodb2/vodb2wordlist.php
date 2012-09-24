<BODY bgcolor='#ffffff'>
<HTML>
<HEAD>
<TITLE>Word Walls</TITLE>

<STYLE type="text/css">

.footer {font-family:sans-serif; font-size: 7pt; color: black; text-align: center; height: 25px;  }
.footerTiny {font-family:sans-serif; font-size: 7pt; color: black; text-align: center; height: 1px;  }


.cursive4 {font-family:Comic Sans MS, cursive; font-size: 95pt; color: #000000; height: 170px;}

.cursive3 {font-family:Comic Sans MS, cursive; font-size: 130pt; color: #000000; height: 250px;}

.cursive2 {font-family:Comic Sans MS, cursive; font-size: 130pt; color: #000000;  height: 250px;} 

.sans-serif4 {font-family:sans-serif; font-size: 100pt; color: #000000; height: 180px;}
.sans-serif3 {font-family:sans-serif; font-size: 130pt; color: #000000; height: 250px;}
.sans-serif2 {font-family:sans-serif; font-size: 140pt; color: #000000; height: 250px;}

table.boxed {border-style: solid; border-width: 5px; border-color: #000000;}
table.smallspacer {boder-style: none; height: 20px;}
table.bigspacer {boder-style: none;}



</STYLE>


</HEAD>

<BODY class="cursive4">

<?php
/*html code robbed from http://www.schoolexpress.com/wordwalls/wordwalls.php */
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
echo" <table class='bigspacer' width='100%'><tr class='boxed'><td align='left'  class='cursive3'>wordlist: ".$sourceID."</td></tr></table>";
echo" <table class='smallspacer' width='100%'><tr class='smallspacer'><td>&nbsp;</td></tr></table>";

foreach($defarr as $rec){
	echo "<table class='boxed' width='100%'><tr class='boxed'><td align='center'  class='cursive4'>" .$rec['word']."</td></tr></table>";
	echo "<table class='smallspacer' width='100%'><tr class='smallspacer'><td>&nbsp;</td></tr></table>";
}
?>
</BODY>

</HTML>