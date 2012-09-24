<?php
require_once('../../FirePHPCore/FirePHP.class.php');
require_once('../../FirePHPCore/fb.php');
ob_start(); //gotta have this
$fip = FirePHP::getInstance(true);
$do='doggyhit';
$fip->log($do, 'smarrtest');
// defines database connection data
define('DB_HOST', 'localhost');
define('DB_USER', 'pathbost_tim');
define('DB_PASSWORD', 'nji9ol');
define('DB_DATABASE', 'pathbost_assess'); 
// defines the number of visible rows in grid
$testname=$_POST[testname];

// create a new XML document
$doc = new DomDocument('1.0');


// add root node
$root = $doc->createElement('root');
$root = $doc->appendChild($root);
//add outer testname element
$outer = $doc->createElement('quiz');
$outer = $root->appendChild($outer);


$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

$sqlt = "SELECT * FROM quiz WHERE tname='$testname'";

if ($result = $db->query($sqlt)) 
{
	$i=0;
	// fetch associative array 
	while ($row = $result->fetch_assoc()) 
	{
		foreach($row as $key=>$val){
			$trec[$key]=$val;		
			$child = $doc->createElement($key);
			$child = $outer->appendChild($child);
			$value = $doc->createTextNode($val);
			$value = $child->appendChild($value);		
		}
	}
}
else {
		echo 'ERROR: There was a problem with the query.';
}
$zid = $trec['zid'];
$sqlq = "SELECT `questiontxt`, `order`, `qtype`, `qid` FROM `questions` WHERE `zid` = $zid";
fb($sqlq);
if ($result = $db->query($sqlq)) 
{
	$i=0;
	// fetch associative array 
	while ($row = $result->fetch_assoc()) 
	{
		$middle = $doc->createElement('question');
		$middle = $outer->appendChild($middle);
		foreach($row as $key=>$val){
			$trec[$key]=$val;		
			$child = $doc->createElement($key);
			$child = $middle->appendChild($child);
			$value = $doc->createTextNode($val);
			$value = $child->appendChild($value);		
		}
		$qid = $trec['qid'];
		$sqla = "SELECT `answertxt`, `iscorrect` FROM `qanswers` WHERE `qid` = $qid";
		if ($aresult = $db->query($sqla)) 
		{
			// fetch associative array 
			while ($arow = $aresult->fetch_assoc()) 
			{
				$inner = $doc->createElement('answer');
				$inner = $middle->appendChild($inner);
				foreach($arow as $key=>$val){
					$arec[$key]=$val;		
					$child = $doc->createElement($key);
					$child = $inner->appendChild($child);
					$value = $doc->createTextNode($val);
					$value = $child->appendChild($value);		
				}
			}
		}
	}
}
else {
	echo 'ERROR: There was a problem with the query.';
}
//answers


// get completed xml document
$xml_string = $doc->saveXML();
header('Content-type: text/xml');
echo $xml_string;
$fip->log($trec, 'trec');
$fip->log($trec['tid'], 'trectid');
$fip->log($xml_string, 'sxsml');