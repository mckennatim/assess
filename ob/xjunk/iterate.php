<?php
header('Content-Type: text/plain');
include_once('../js/simple_html_dom.php');

$urllist = whichPagesHaveDiscussion("obout/");

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
print_r($urllist);
?>
