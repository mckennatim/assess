<?
echo header("Content-type: text/plain");
include_once('../tm/utility.php');
include_once('../tm/dbinfo.php');
require_once('../../FirePHPCore/FirePHP.class.php');
require_once('../../FirePHPCore/fb.php');
ob_start(); //gotta have this

$quizurl=$_GET[quizurl];
$course=$_GET[course];
$sections=$_GET[section];
$slen = count($sections);
echo("slen = ". $slen);
fb("slen = ". $slen);

$urlarray =parse_url($quizurl);
parse_str($urlarray[query]);
$quizname = trim($compques."+".$quesfrom); //this is also in creatCquiz.php line 25
fb($quizname);

$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
$iscurrent=1;
foreach($sections as $section){	
	$sql = "UPDATE currentquizzes 	
	SET iscurrent = 0 WHERE section = '$section'";
	$db->query($sql);
	fb($sql);
	$sql =  "INSERT INTO `currentquizzes` (`testname`, `testurl`, `section`, `course`, `iscurrent`) 
	VALUES(
		'$quizname',
		'$quizurl',
		'$section',
		'$course',
		'$iscurrent')";
	$db->query($sql);
	fb($sql);
}

?>