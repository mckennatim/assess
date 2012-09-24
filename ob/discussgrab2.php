<?php
header('Content-Type: text/xml');
include_once('simple_html_dom.php');

$dir  = "obout/"; //directory of all pages
$urllist = whichPagesHaveDiscussion($dir);

//print_r($urllist);

?>
<mediawiki xml:lang="en" xmlns="http://www.mediawiki.org/xml/export-0.5/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.mediawiki.org/xml/export-0.5/ http://www.mediawiki.org/xml/export-0.5.xsd" version="0.5" >
<?
$theXML = createDiscussionXML($urllist);

//echo $theXML;

function whichPagesHaveDiscussion($dir){
	// open a known directory, and proceed to read its contents
	$farr=array();
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if (filetype($dir . $file)=='file'){
					$farr[]=$file; //mak a list of just the files in the directory
				}
				//echo "filename: $file : filetype: " . filetype($dir . $file) . "\n";
			}
			closedir($dh);
		}
	}
	// print_r($farr); //a list of all files in dir
	$urll=array();
	foreach ($farr as $pah){
		$url="http://occupyboston.wikispaces.com/message/list/".$pah;
		$html = file_get_html($url);
		$d = $html->find('th[class="pagination"] ');
		foreach ($d as $dd){ 
			if (strlen($dd) >5 ) { //if this tag is found then there is discussion
				$urll[]=$url; //this page has a discussion, add it to list
			}
		}
	}
	return $urll; //a list of web pages with discussions
}

function createDiscussionXML($urllist){	
	
	foreach ($urllist as $url){
		$pgtitle = str_replace(" ", "_", substr($url,48));
		$html = file_get_html($url);
		$dl=array(); //declares an array
		foreach($html->find('td[class="w_subject"] a') as $d)
		{
			$path = $d->href;
			$dl[] = strip_tags($path); //adds to the end of the array
		}
		//print_r($dl);
		$d=$dl[0];
		$pg = 'This is my page, its not much anyway so what';
		$pg = createDiscussionPage($dl);
		?>
		<page>
			<title>User_talk:Mcktimo/take1/<?echo $pgtitle;?></title>
		  <revision>
	        <timestamp>2011-10-19T01:01:00Z</timestamp>
	      <contributor>
	        <username>Mcktimo</username>
	        <id>3</id>
	      </contributor>

	        <text xml:space="preserve">
	
	<?
echo $pg;
?>
</text>
      </revision>
</page>
<?		
		$xmlf.= $pg;
	}
	return $xmlf;
}

function createDiscussionPage($dlist) {

	$pg="";
	foreach($dlist as $d){
		$url= "http://occupyboston.wikispaces.com" . $d;
		$html = file_get_html($url);
		//echo $url."\n\n";
		$dti = $html->find('h1[class="noSpacing"] ');
		$dtit = strip_tags($dti[0]);
		//echo $dtit."\n";
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

		//print_r($dat);	
		//print_r($di);	
		//print_r($ui);	
		//print_r($raa);	
		$topic="\n";
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
	return $pg;
}
?>

</mediawiki>

