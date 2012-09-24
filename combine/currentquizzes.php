<?
//echo header("Content-type: text/plain");
include_once('../tm/utility.php');
include_once('../tm/dbinfo.php');
require_once('../../FirePHPCore/FirePHP.class.php');
require_once('../../FirePHPCore/fb.php');
ob_start(); //gotta have this

$bpsid=$_GET[bpsid];
$name=$_GET[name];
$email=$_GET[email];
$section=$_GET[section];
$course=$_GET[course];


$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$sql= "SELECT testname, testurl FROM currentquizzes WHERE course = '$course' AND section = '$section' AND iscurrent = 1 ";
fb($sql);

$darray= array();
if ($result = $db->query($sql)) 
{
	// fetch associative array 
	while ($row = $result->fetch_assoc()) 
	{
		$darray[]=$row;
		foreach($row as $fname=>$val)
			$fieldval=htmlentities($val);
			$fieldname = $fname;
	}
}
$testname = $darray[0]['testname'];
$testurl = $darray[0]['testurl'];
//check if $name already took $testname
$sql2 = "SELECT * FROM qgrades WHERE bpsid = '$bpsid' AND testname = '$testname'";
fb($sql2);
$garray= array();
if ($result = $db->query($sql2)) { 
	if($result->num_rows>0){
		fb($result->num_rows);
		while ($row = $result->fetch_assoc()) 
		{
			$garray[]=$row;
			foreach($row as $fname=>$val)
				$fieldval=htmlentities($val);
			$fieldname = $fname;
		}
		$earray=end($garray);
		fb($earray);

		echo('<html>
			<head>
			</head>
			<body>
			<p>Hi '.$name.', <br/><br/><br/> You already took this quiz on '.$earray[endtime].'.<br/><br/> You got '.$earray[thescore].'%, '.$earray[correct].' correct out of '.$earray[numquestions].'. <br/><br/>Talk with Mr. McKenna if you would like to find a way to revisit the material upon which this quiz is based.</p>
			</body>
			</html>');
	}else{
		echo('<html>
			<head>
			</head>
			<body>
			<p>Hi '.$name.' One last click to get to quiz. Good luck.</p>
			<a href="'.$testurl.'&bpsid='.$bpsid.'&name='.$name.'&email='.$email.'&course='.$course.'&section='.$section.'">Quiz:.'.$testname.'</a>
			</body>
			</html>');
	}
}

?>