<?php

require_once('../../FirePHPCore/FirePHP.class.php');
require_once('../../FirePHPCore/fb.php');
include_once('../tm/utility.php');
include_once('../tm/dbinfo.php');
ob_start(); //gotta have this

$endtime=  date('l jS \of F Y h:i:s A');
//print_r($_GET);
$starttime=$_GET[startdate];
$bpsid=$_GET[bpsid];
$name=$_GET[name];
$email=$_GET[email];
$testname=$_GET[testname];
$numquestions=$_GET[numquestions];
$testid=$_GET[testid];
$course=$_GET[course];
$section=$_GET[section];

//echo('enddate is '.$enddate);
//$testtime= date_diff($enddate, $startdate);
//echo ('yeah '.$testtime['seconds']);
$scores = array_slice($_GET, 0, -9);//assumes there are 6 non-score fields
fb($scores);
$correct = array_sum($scores);
$den = count($scores);
$thescore= (' score is '. $correct/$numquestions*100 .'% </p>' );
fb('numquestions is '. $numquestions);
fb($thescore);
$score=round($correct/$numquestions*100, 0);
$sscores=serialize($scores);
echo('<html>
<head>
</head>
<body>
	<h5>'.$testname.'</h5>
	<h3>'.$name. ', your score is' .$thescore.'</h3>');

$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

$sql="INSERT INTO qgrades (`bpsid`, `course`, `block`, `name`, `email`, `testname`, `scores`, `thescore`, `correct`, `numquestions`, `starttime`, `endtime`) VALUES ('$bpsid', '$course', '$section', '$name', '$email', '$testname', '$sscores', '$score', '$correct', '$numquestions', '$starttime', '$endtime') ";
fb($sql);
$db->query($sql);

$sql="SELECT ser FROM `qarray` WHERE id='$testid'";
fb($sql);
$result = $db->query($sql);
$row = $result->fetch_assoc();
$quarr=unserialize(stripslashes($row['ser']));
$sarr=$quarr['data'];
fb($sarr);
echo('<form id="form1" name="Update" method="get" action="qout2graded.php">');

$m=1;
$ai= array("A", "B", "C", "D", "E", "F");

foreach ($sarr as $ques){
	echo('<br/>' . $m . ". ". $ques['questiontxt']. '<br/>');
	$k=0;
	foreach ($ques['answers'] as $aans){
		if ($aans['iscorrect']==1){
			$ck = '" CHECKED />';
		} else {
			$ck = '" />';
		}
		$astr = '<input type="radio" name="'. $m .'" value="'.$aans['iscorrect'].$ck.$ai[$k].'. '. $aans['answertxt'] .'<br />';
		echo($astr);
		fb($aans['iscorrect']);
		$k++; 
	}
	$m++;
}
echo('<input type="text" name="testid" id="textfield" value="'.$did.'" size="25"/>
<input name="" type="submit" value="send" /><small>534'.$did.'62</small>
<INPUT TYPE = Hidden NAME = "startdate" VALUE = "'.date('l jS \of F Y h:i:s A').'">
</form>
</body>
</html>');
?>
