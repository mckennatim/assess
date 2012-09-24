<?php
require_once('../../FirePHPCore/FirePHP.class.php');
require_once('../../FirePHPCore/fb.php');
require_once('../tm/utility.php');
ob_start(); //gotta have this
echo "OKfire";

$siteurl=$_POST[siteurl];
$wikipage=$_POST[wikipage];
$testname=$_POST[testname];
$quizdesc=$_POST[quizdesc];
$qorder=$_POST[qorder];
$sources=explode(",",$_POST[sources]);
$user="tim";
$pword="nji9ol";

//howto debug
$firephp = FirePHP::getInstance(true);
$var = array('i'=>10, 'j'=>20);
$do='doggyhit';
$firephp->log($var, 'smarrtest');
$firephp->log($do, 'do');
$firephp->warn('watch oi');
FB::warn('watch oit');
fb('why should I');

//http://tim:nj@sitebuilt.net/wuff/index.php?title=Economics&action=raw&ctype=text/javascript
//http://pathboston.com/hum310/index.php?title=Economics&action=raw&ctype=text/javascript
$url="http://".$user.":".$pword."@".$siteurl."/index.php?title=".$wikipage."&action=raw&ctype=text/javascript";
echo $url;
$text = file($url) or die ("ERROR: Unable to read file");
print_r($text);
$theanswers=explode(" ",$text[2]);//second line is space delimited answers as numbers
$text = array_slice($text, 2); //get rid of first 2 lines


$mysqli = new mysqli('localhost', 'pathbost_tim' ,'nji9ol', 'pathbost_assess');

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
$testsql = "INSERT INTO `pathbost_assess`.`quiz` (`tname`, `quizdesc`) VALUES ('$testname', '$quizdesc')";
$mysqli->query($testsql)or die("trying to insert quiz title");		
$zid = $mysqli->insert_id;
foreach ($sources as $source){
	$source=trim($source);
	$sourcesql = "INSERT INTO `pathbost_assess`.`qsources` (`sourceid`, `zid`) VALUES ('$source', '$zid')";
	$mysqli->query($sourcesql);
}

$qn=0;
foreach ($text as $line)
{
	$line = removeNonAscii($line);
	echo $line;
	if(!strcmp(substr($line, 1,1), "#")) {
		$ans = $mysqli->real_escape_string(substr($line,2));
		if ($corrand == $aidx)  {
			$iscorrect = 1;
		} else {
			$iscorrect = 0;
		}
		$aidx++;
		$anssql = "INSERT INTO `pathbost_assess`.`qanswers` (`qid`, `answertxt`, `iscorrect`) VALUES ('$qid', '$ans', $iscorrect)";
		$mysqli->query($anssql) or die("dead inserting aswers");		
        print("string is answer \"#\"<br />");
    } else {
		$aidx = 1;
		$corrand = $theanswers[$qn];
		$qn++;
		$ques = $mysqli->real_escape_string(substr($line,1));
		$sql = "INSERT INTO `pathbost_assess`.`questions` (`questiontxt`, `order`, `zid`) VALUES ('$ques', '$qorder', '$zid')";
		$mysqli->query($sql) or die("dead inserting questionns"); 
		$qid = $mysqli->insert_id;
        print("this is a question<br />");
    }
}

$mysqli->close();
?>