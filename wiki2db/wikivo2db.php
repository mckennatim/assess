<?php
echo("hell");
include_once('../js/simple_html_dom.php');
include_once('../tm/utility.php');
include_once('../tm/dbinfo.php');
echo("ok after includes");
require_once('../../FirePHPCore/FirePHP.class.php');
require_once('../../FirePHPCore/fb.php');
require_once('../tm/utility.php');
ob_start(); //gotta have this
echo "OKfire";

$apage=nl2br(stripslashes($_POST[apage]));
$course=$_POST[course];
$asource=$_POST[asource];
$loc=$_POST[loc];
$subloc=$_POST[subloc];
$topic=$_POST[topic];
$unit=$_POST[unit];
$just=$_POST[just];

$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD,DB_DATABASE);
echo("-----afeter db is opened-----");

$uid=0;
$tid=0;

echo(" \n just is ".$just . " \n");
if ($just=='new-course'){
	$sql = "INSERT INTO `pathbost_assess`.`courses` (`course`) VALUES ('$course')";
	echo("sql= ".$sql);
	$rr=$db->query($sql) or die("Dead adding course");	
}
if ($just=='new-unit' or $just=='new-course'){
	$sql = "INSERT INTO `pathbost_assess`.`units` (`unit`, `course`) VALUES('$unit', '$course')";
	echo("sql= ".$sql);
	$rr=$db->query($sql) or die("Dead adding unit");	
	$uid = $db->insert_id;	
}
if ($just=='new-topic' or $just=='new-unit' or $just=='new-course'){
	if ($uid==0){
		$query="SELECT uid FROM units WHERE unit='$unit' limit 1";
		echo("sql= ".$query);
		$result = $db->query($query) or die("Dead finding units uid");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		//print_r($row);
		$uid = $row[uid];
		//echo(" in if uid= ".$uid);		
	}
	echo(" uid= ".$uid);
	$sql = "INSERT INTO `pathbost_assess`.`topics` (`topic`, `uid`) VALUES('$topic', '$uid')";
	echo(" sql= ". $sql);
	$rr=$db->query($sql) or die("Dead adding topic");
	$tid = $db->insert_id;	
}
if ($just=='new-source' or $just=='new-topic' or $just=='new-unit' or $just=='new-course'){
	if ($tid==0){
		$query="SELECT tid FROM topics WHERE topic='$topic' limit 1";
		echo("sql= ".$query);
		$result = $db->query($query) or die("Dead finding topics tid");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$tid = $row[tid];		
	}	
	echo(" tid= ".$tid);
	$sql = "INSERT INTO `pathbost_assess`.`sources` (`sourceID`, `tid`) VALUES('$asource', '$tid')";
	echo("sql= ".$sql);
	$rr=$db->query($sql) or die("Dead adding source");
}
//echo($apage);
//print_r($apage);
$i=0;
foreach (preg_split("/(\r?\n)/", $apage) as $line)
{
	//echo("------------".$i."------------");
	//echo($line);

	if(!strcmp(substr($line, 0,1), ";")) {
		$iss = substr($line, 0,1);
		$istrue= strcmp(substr($line, 0,1), ";");
		//echo("the substr of line 0,1 is " . $iss . $istrue. ">>\n<br>" );
		$sp = strpos($line,":");
		$thewrd = substr($line,1,$sp-1);
		$thedef = substr($line,$sp+2);
		$thedef = removeNonAscii($thedef);
		$thedef = rtrim($thedef, "<br />");
		//echo("word-> ".$thewrd." the def-> ".$thedef);
		$sql = "INSERT INTO `pathbost_assess`.`vdefs` (`def`) VALUES ('$thedef')";
		$rr=$db->query($sql);
		$did = $db->insert_id;
		//echo $did;
		$sql = "INSERT INTO `pathbost_assess`.`vwords` (`word`, `did`) VALUES ('$thewrd', $did)";
		$rr=$db->query($sql) or die(" dead not inserting a word");
		$sql = "INSERT INTO `pathbost_assess`.`vcontexts` (`did`, `sourceID`, `loc`, `subloc`) VALUES ('$did', '$asource', '$loc', '$subloc')";
		$rr=$db->query($sql) or die(" dead inserting vcontexts.source for the main def");
	}
	
	//if your usage else if a context
	if(!strcmp(substr($line, 0,3), "::*")) {
		$youruse = "";
		$rpos=strpos($line, "}");
		if ($rpos === false){
			$yourdef="";
			$rpos=2;
		}else{
			$lpos = strpos($line,"{");
			$yourdef = substr($line,$lpos+1, $rpos- $lpos-1);
			$yourdef = str_replace("'", "", $yourdef);
			$yourdef = trim($yourdef);
			$yourdef = rtrim($yourdef, "<br />");
			$yourdef = removeNonAscii($yourdef);
		}
		$lepos=strpos($line, "(");
		if ($lepos === false){
			$yourID="";
			$yourOK="";
			$rating="";
			$youruse = substr($line,$rpos+1);
			$lepos=strlen($line)-1;
		}else{
			$repos = strpos($line,")");
			$estr=substr($line, $lepos+1, $repos- $lepos-1 );
			$earr=explode(", ", $estr);
			$yourID=$earr[0];
			$yourOK=$earr[1];
			$rating=$earr[2];
		}
		$lbpos=strpos($line, "[");
		echo(" lbpos= ".$lbpos." isfalse?= ". ($lbpos === false));
		if ($lbpos === false){
			$ypos="";
			$lbpos=$lepos;
		}else{
			echo(" jumped to there is a bracket ");
			$rbpos = strpos($line,"]");
			$ypos = substr($line,$lbpos+1, $rbpos- $lbpos-1);
			$ypos = str_replace("'", "", $ypos);
			$ypos = trim($ypos);
			$ypos = rtrim($ypos, "<br />");
			$ypos = removeNonAscii($ypos);
			$youruse = substr($line,$rpos+1, $lbpos-$rpos-1);
		}
		$youruse = str_replace("'", "", $youruse);
		$youruse = trim($youruse);
		$youruse = rtrim($youruse, "<br />");
		$youruse = ltrim($youruse, ":*");
		$youruse = removeNonAscii($youruse);

		$sql = "INSERT INTO `pathbost_assess`.`vyour` (`yourdef`, `youruse`, `did`, `ypos`, `yourID`, `yourOK`, `rating`) VALUES ('$yourdef', '$youruse', '$did', '$ypos', '$yourID', '$yourOK', '$rating')";
		echo(" sql= ".$sql);
		$rr=$db->query($sql) or die(" Dead at vour" );
	} elseif(!strcmp(substr($line, 0,2), "::")) { //a context
		//insert a usage [and pos] -automatically use $did	
		$lbrpos = strpos($line,"[");
		if ($lprpos === false){
			$pos="";
		}else{
			$rbrpos = strpos($line,"]");
			$pos = substr($line,$lbrpos+1, $rbrpos- $lbrpos-1);
		}
		$use = substr($line,2,$lbrpos -2);
		$use = str_replace("'", "", $use);
		$use = trim($use);
		$use = rtrim($use, "<br />");
		$use = removeNonAscii($use);
		$query="SELECT did FROM vcontexts WHERE did='$did' AND sourceID='$asource' limit 1";
		echo("sql= ".$query);
		$result = $db->query($query) or die("Dead finding if there is a context for this did");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$xdid = $row[did];
		echo(" xdid= ".$xdid . " numrows= ". $result->num_rows);
		//if there is already a context for this def than update it else create new
		if ($result->num_rows > 0){
			$sql = "UPDATE `pathbost_assess`.`vcontexts` SET `sentence`='$use', `pos`='$pos', `sourceID`='$asource', `loc`='$loc', `subloc`='$subloc' WHERE did=$xdid"  ;
		}else{
			$sql = "INSERT INTO `pathbost_assess`.`vcontexts` (`sentence`, `pos`, `did`, `sourceID`, `loc`, `subloc`) VALUES ('$use', '$pos', '$did', '$asource', '$loc', '$subloc')";
		}
		echo("sql= ".$sql);
		$rr=$db->query($sql) or die(" Dead at vcontexts" );
		$cid = $db->insert_id;
	}
	
	//another def
	if (preg_match("/:[1-9]\./", substr($line,0,3), $matches)){
		//insert another vdef and word - use $theword
		$thedef = trim(substr($line,3));
		$thedef = removeNonAscii($thedef);
		$thedef = rtrim($thedef, "<br />");
		$sql = "INSERT INTO `pathbost_assess`.`vdefs` (`def`) VALUES ('$thedef')";
		$rr=$db->query($sql) or die(" died inserting another def ");
		//echo("sql= .$sql");
		$did = $db->insert_id;
		//echo("word again-> ". $thewrd. " another def-> ". $thedef. " ".$did." \n<br>");
		$sql = "INSERT INTO `pathbost_assess`.`vwords` (`word`, `did`) VALUES ('$thewrd', $did)";
		//echo("sql= ". $sql);
		$rr=$db->query($sql) or die(" dead not inserting another word");
		$sql = "INSERT INTO `pathbost_assess`.`vcontexts` (`sourceID`, `loc`, `subloc`, `did`) VALUES ('$asource', '$loc', '$subloc', '$did')";
		$rr=$db->query($sql) or die(" Dead at vcontexts for additional def" );
	} 
	$i++;
}
	
?> 