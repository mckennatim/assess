<?php
//echo header("Content-type: text/plain");
include_once('../tm/utility.php');
require_once('../tm/dbinfo.php');
require_once('../../FirePHPCore/FirePHP.class.php');
require_once('../../FirePHPCore/fb.php');
ob_start(); //gotta have this
$testname = 'atest';
$quesfrom=explode(",",$_POST[quesfrom]);
$choicefrom=explode(",",$_POST[choicefrom]);
$numQuestions=$_POST[numques];
$quiztype = $_POST[quiztype];
$numblank=round($percentBlank*$numQuestions);
$numdef=$numQuestions-$numblank;
$numchoices = 4;
$email = 'tim@sitebuilt.net';
$name = 'Tim McKenna';


$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
$testmeta['testname'] = $testname;
$testmeta['quesfrom'] = $quesfrom;
$testmeta['choicefrom'] = $choicefrom;
$testmeta['numontest'] = $numQuestions;
$testmeta['maxchoices'] = $numchoices;
$quarr=vdb2arr($db, $testmeta);

if (!strcmp($quiztype, 'egg')){
	$sarr = $quarr['data'];
	$csv = qarr2csv($sarr);
	fb($quarr);
	echo header("Content-type: text/plain");
	echo($csv);	
} else {
	fb($quarr);
	$sersarr = addslashes(serialize($quarr));
	$sql="INSERT INTO `qarray` (`ser`, `quizname`) VALUES ('$sersarr', '$testname')";
	fb($sql);
	$db->query($sql);
	$tid = $db->insert_id;
	fb($tid);
	$quarr['meta']['tid']= $tid;
	$usermeta['name'] = $name;
	$usermeta['email'] = $email;
	qarr2HtmlQuiz($quarr, $usermeta);
}
?>