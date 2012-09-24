<?php
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
$wrd=array(); //declares an array
foreach($html->find('div[class="description"] b') as $wd)
{
	$wrd[] = strip_tags($wd); //adds to the end of the array
}
//another way to fill an array
$u=0;
$use=array();
foreach($html->find('div[class="description"] ') as $t)
{
	$use[$u]= removeNonAscii($t);
	echo $use[$u] . "\n\n"; 
	$u++;
}

$def=array();
$pos=array();
$exa=array();
foreach ($wrd as $awrd) {
	$tags = get_meta_tags('http://www.answers.com/'.$awrd);
	$thede = $tags['description']."\n";//pick out the description tag and keep the string
	$sp = strpos($thede,":");
	echo " SP= ". $sp;
	if ($sp){
		$thedef = substr($thede,0,$sp);
		$example = substr($thede,$sp+2);	
		echo " AAAA ". $example;
	}else{
		$thedef=$thede;
		$example="";
	}
	$example =removeNonAscii($example);
	$exa[]=$example;
	$thewords = explode(" ", $thedef);//explode it 
	$tword = $thewords[0];
	//$bword = '<b>'.$tword.'</b>';
	//echo $bword;
	$thewords[0]="";//get rid of the word keep the def
	$pos[]= $thewords[3];
	$thewords[3]="";//get pos out of def
	$wikidef = implode(" ",$thewords);//put the string back together
	$widef = trim(from1stCap($wikidef));
	$bdef = removeNonAscii($widef);
	$def[]= $bdef;	
}
print_r($pos);
print_r($def);
print_r($exa);


function from1stCap($s){
	$chars = preg_split('/[A-Z]/', $s, -1, PREG_SPLIT_OFFSET_CAPTURE);
	$idx= $chars[1][1];
	$fc= substr($s, $idx-1);	
	return $fc;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD,DB_DATABASE);
if(!$db) {
	// Show error if we cannot connect.
	echo 'ERROR: Could not connect to the database.';
}
$uid = 0;
$tid = 0;
if ($just=='new-course'){
	$sql = "INSERT INTO `pathbost_assess`.`courses` (`course`) VALUES ('$course')";
	$rr=$db->query($sql) or die("Dead adding course");	
}
if ($just=='new-unit' or $just=='new-course'){
	$sql = "INSERT INTO `pathbost_assess`.`units` (`unit`, `course`) VALUES('$unit', '$course')";
	$rr=$db->query($sql) or die("Dead adding unit");	
	$uid = $db->insert_id;	
}
if ($just=='new-topic' or $just=='new-unit' or $just=='new-course'){
	if ($uid==0){
		$query="SELECT uid FROM units WHERE unit='".$unit."' limit 1";
		fb($query);
		$result = $db->query($query) or die("Dead finding units uid");
		$row = $result->fetch_assoc();
		$uid = $row['uid'];		
	}
	$sql = "INSERT INTO `pathbost_assess`.`topics` (`topic`, `uid`) VALUES('$topic', '$uid')";
	$rr=$db->query($sql) or die("Dead adding topic");
	$tid = $db->insert_id;	
}
if ($just=='new-source' or $just=='new-topic' or $just=='new-unit' or $just=='new-course'){
	if ($tid==0){
		$query="SELECT tid FROM topics WHERE topic='".$topic."' limit 1";
		fb($query);
		$result = $db->query($query) or die("Dead finding topics tid");
		$row = $result->fetch_assoc();
		fb($row);
		$tid = $row['tid'];	
		fb($tid);	
	}	
	$sql = "INSERT INTO `pathbost_assess`.`sources` (`sourceID`, `tid`) VALUES('$asource', '$tid')";
	fb($sql);
	$rr=$db->query($sql) or die("Dead adding source");
}
$i=0;
echo count($wrd);
foreach ($wrd as $bwrd){
	$sql = "INSERT INTO `pathbost_assess`.`vdefs` (`def`) VALUES ('$def[$i]')";
	$rr=$db->query($sql);
	$did = $db->insert_id;
	echo $did;
	echo $bwrd; 
	$sql = "INSERT INTO `pathbost_assess`.`vwords` (`word`, `did`) VALUES ('$bwrd', $did)";
	$rr=$db->query($sql) or die(" dead not inserting a word");
	$sql = "INSERT INTO `pathbost_assess`.`vcontexts` (`sentence`, `pos`, `did`, `sourceID`, `loc`, `subloc`) VALUES ('$use[$i]', '$pos[$i]', '$did', '$asource', '$loc', '$subloc')";
	$rr=$db->query($sql) or die(" Dead at vcontexts" );
	$cid = $db->insert_id;
	if (strlen($exa[$i])>5){
		$sql = "INSERT INTO `pathbost_assess`.`vyour` (`youruse`, `yourID`, `did`) VALUES ('$exa[$i]', 'answers.com', '$did')";
		echo($sql. " \n<br>");
		$rr=$db->query($sql) or die(" dead inserting in vyour" );
	}
	echo 'XXXX';
	echo $cid;
	$i++;
}

