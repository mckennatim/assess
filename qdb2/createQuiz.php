<?
require_once('../../FirePHPCore/FirePHP.class.php');
require_once('../../FirePHPCore/fb.php');
include_once('../tm/utility.php');
include_once('../tm/dbinfo.php');
ob_start(); //gotta have this

$testname = $_GET[testname];
$compques=explode(",",$testname);
$name = $_GET[name];
$email = $_GET[email];
$maxchoices = $_GET[maxchoices];
$numontest= $_GET[maxquestions];
$quiztype = $_GET[quiztype];
$eraseold= $_GET[eraseold];


$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

$testmeta = array();
$testmeta['testname'] = $testname;
$testmeta['compques'] = $compques;
$testmeta['maxchoices'] = $maxchoices;
$testmeta['numontest'] =$numontest; //$testmeta has to have $testname, maxchoices, numquestions
$quarr = qdb2arr($db, $testmeta); //from utility

if (!strcmp($quiztype, 'egg')){
	$sarr = $quarr['data'];
	$csv = qarr2csv($sarr);
	fb($quarr);
	echo header("Content-type: text/plain");
	echo($csv);

	$fileloc = '../csvs/test.csv';
	savecsv($quizcsv, $fileloc);
} else{
	fb($quarr);
	$sersarr = addslashes(serialize($quarr));
	$sql="INSERT INTO `qarray` (`ser`, `quizname`) VALUES ('$sersarr', '$testname')";
	//fb($sql);
	$db->query($sql);
	$tid = $db->insert_id;
	//fb($tid);

	$quarr['meta']['tid']= $tid;
	$usermeta['name'] = $name;
	$usermeta['email'] = $email;

	qarr2HtmlQuiz($quarr, $usermeta); //from utility
}


?>