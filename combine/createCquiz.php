<?php
//echo header("Content-type: text/plain");
include_once('../tm/utility.php');
require_once('../tm/dbinfo.php');
require_once('../../FirePHPCore/FirePHP.class.php');
require_once('../../FirePHPCore/fb.php');
ob_start(); //gotta have this
$testname = 'atest';
$quesfrom=explode(",",$_GET[quesfrom]);
$compques=explode(",",$_GET[compques]);
$choicefrom=explode(",",$_GET[choicefrom]);
$numQuestions=$_GET[numques];
$percentcomp=$_GET[percentcomp];
$quiztype = $_GET[quiztype];
$numcomp=round($percentcomp/100*$numQuestions);
$numchoices = 4;
$email = $_GET[email];
$name = $_GET[name];
$bpsid = $_GET[bpsid];
$course = $_GET[course];
$section = $_GET[section];




$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
$testmeta['testname'] = trim($_GET[compques]."+".$_GET[quesfrom]);//this is also in setCurrentQuiz.php line 18
$testmeta['quesfrom'] = $quesfrom;
$testmeta['compques'] = $compques;
$testmeta['choicefrom'] = $choicefrom;
$testmeta['numontest'] = $numcomp;
$testmeta['maxchoices'] = $numchoices;

$quarrc = qdb2arr($db, $testmeta); 

$numc = $quarrc['meta']['numCquestions'];

$numv = $numQuestions - $numc;
fb('numv = '.$numv  );
$testmeta['numontest'] = $numv;
$quarrv = vdb2arr($db, $testmeta);

$quarr['meta'] = array_merge($quarrc['meta'], $quarrv['meta']);
$quarr['data'] = array_merge($quarrc['data'], $quarrv['data']);
$quarr['data'] = shuffleArray($quarr['data']);
fb($quarrc);
fb($quarrv);
$quarr['meta']['totnumquestions'] =$quarr['meta']['numCquestions'] + $quarr['meta']['numVquestions'];
fb('totnumquestions = '.$quarr['meta']['totnumquestions']);




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
	$usermeta['bpsid'] = $bpsid;
	$usermeta['course'] = $course;
	$usermeta['section'] = $section;
	qarr2HtmlQuiz($quarr, $usermeta);
}
?>