<?php
header('Content-Type: text/plain');
include_once('../js/simple_html_dom.php');
include_once('../tm/utility.php');
include_once('../tm/dbinfo.php');
require_once('../../FirePHPCore/FirePHP.class.php');
require_once('../../FirePHPCore/fb.php');
ob_start(); //gotta have this

$url=$_POST[url];
$course=$_POST[course];
$submitter='098391';
$asource=$_POST[asource];
$loc=$_POST[loc];
$subloc=$_POST[subloc];
$topic=$_POST[topic];
$unit=$_POST[unit];
$just=$_POST[just];
$ok=1;

//$html = file_get_html('http://www.visualthesaurus.com/wordlists/22419');
$html = file_get_html($url);
//the real file: http://www.visualthesaurus.com/wordlists/22419
//
//one way to fill an array
$dl=array(); //declares an array
foreach($html->find('td[class="w_subject"] a') as $d)
{
	$path = $d->href;
	$dl[] = strip_tags($path); //adds to the end of the array
}
//print_r($dl);
$d=$dl[0];
$pg="";
foreach($dl as $d){
	$url= "http://occupyboston.wikispaces.com" . $d;
	$html = file_get_html($url);
	echo $url."\n\n";
	$dti = $html->find('h1[class="noSpacing"] ');
	$dtit = strip_tags($dti[0]);
	echo $dtit."\n";
	$uid=array(); //declares an array	
	$dat=array(); //declares an array	
	foreach($html->find('td[class="w_body"] strong' ) as $ut)
	{
	
		$dat[]= strip_tags($ut);
		//$dat[] = strip_tags($ut[1]); //adds to the end of the array
	}
	foreach($html->find('td[class="w_body"] a' ) as $ut)
	{
	
		$uid[]= strip_tags($ut);
		//$dat[] = strip_tags($ut[1]); //adds to the end of the array
	}	
	$raw=array(); //declares an array	
	foreach($html->find('div[class="wiki"] ') as $rt)
	{
		$raw[] = strip_tags($rt); //adds to the end of the array
		$raa = array_slice($raw,1,-2);
	}
	//echo count($uid);
	$ui=array();
	$di=array();
	for($i=0; $i< count($uid); $i++){
		if(($i+3) % 3){
			//do nothing
		} else {
			$ui[]=$uid[$i];
		}
		if(($i+2) % 3){
			//do nothing
		} else {
			$di[]=$uid[$i];
		}
	}
	
print_r($dat);	
print_r($di);	
print_r($ui);	
print_r($raa);	
$topic="";
$topic.="==".$dtit."==\n";
$topic.=":[[user:".$ui[0]."]] ".$di[0]."\n";
$topic.=$raa[0]."\n";
for ($i=1; $i < count($raa); $i++){
	$topic.="====".$dat[$i-1]."====\n";
	$topic.="::[[user:".$ui[$i]."]] ".$di[$i]."\n";
	$topic.=$raa[$i]."\n";	
}
$pg.= $topic;
}
echo $pg;
?>

