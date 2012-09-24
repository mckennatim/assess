<?php

function removeNonAscii($string) {
	$find[] = 'â€œ';  // left side double smart quote
	$find[] = 'â€';  // right side double smart quote
	$find[] = 'â€˜';  // left side single smart quote
	$find[] = 'â€™';  // right side single smart quote
	$find[] = 'â€¦';  // elipsis
	$find[] = 'â€”';  // em dash
	$find[] = 'â€“';  // en dash

	$replace[] = '"';
	$replace[] = '"';
	$replace[] = "'";
	$replace[] = "'";
	$replace[] = "...";
	$replace[] = "-";
	$replace[] = "-";

	$string = str_replace($find, $replace, $string);
	$string=strip_tags($string, "<b>");//strips tags except bold (but not enough)
	$string= filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH ); 
	$string= str_replace("&#34;","`",$string);
	$string= str_replace("&#39;","`",$string);
	return preg_replace('/[^\x20-\x7f]/','',$string);
}

function shuffleArray($arr){
	$dan= range(0,sizeof($arr)-1);
	shuffle($dan);
	$qz=array_combine($dan, $arr);
	ksort($qz);
	return $qz;
}
/*
function date_diff($d1, $d2){
	$d1 = (is_string($d1) ? strtotime($d1) : $d1);
	$d2 = (is_string($d2) ? strtotime($d2) : $d2);

	$diff_secs = abs($d1 - $d2);
	$base_year = min(date("Y", $d1), date("Y", $d2));

	$diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
	return array(
		"years" => date("Y", $diff) - $base_year,
		"months_total" => (date("Y", $diff) - $base_year) * 12 + date("n", $diff) - 1,
		"months" => date("n", $diff) - 1,
		"days_total" => floor($diff_secs / (3600 * 24)),
		"days" => date("j", $diff) - 1,
		"hours_total" => floor($diff_secs / 3600),
		"hours" => date("G", $diff),
		"minutes_total" => floor($diff_secs / 60),
		"minutes" => (int) date("i", $diff),
		"seconds_total" => $diff_secs,
		"seconds" => (int) date("s", $diff)
	);
}
*/
function savecsv($quizcsv, $fileloc){
	$handle = fopen($fileloc,"w+");	 
	if ($handle) {
		fwrite($handle, $quizcsv);
		fclose($handle);
	}
}
function qdb2arr($db, $testmeta){
	$testname = $testmeta['testname'];
	$numchoices = $testmeta['maxchoices'];
	$numontest = $testmeta['numontest'];
	$compques = $testmeta['compques'];
	$qwherestr= "";
	$idx=0;
	foreach ($compques as $qsource){
		$qsource = explode(".", $qsource);
		$asource = trim($qsource[0]);
		$loc = trim($qsource[1]);
		$subloc = trim($qsource[2]);
		if ($idx==0)
		{
			$qwherestr.= " WHERE ( tname ='".$asource."' \n";
		}else{
			$qwherestr.= " OR  tname ='".$asource."' \n";
		}
		$idx++;
	}
	$qwherestr.= ")\n";
	$sqlt = "SELECT * FROM quiz ".$qwherestr;
	fb($sqlt);
	$zid = array();
	if ($result = $db->query($sqlt)) 
	{
		$i=0;
		// fetch associative array 
		while ($row = $result->fetch_assoc()) 
		{
			foreach($row as $key=>$val){
				$trec[$key]=$val;			
			}
			$zid[] = $trec['zid'];
		}
	}
	else {
		echo 'ERROR: There was a problem with the query.';
	}
	fb($zid);
	$qqwherestr = "";
	for ($idx=0; $idx<sizeof($zid); $idx++){
		if ($idx==0)
		{
			$qqwherestr.= " WHERE ( zid = $zid[$idx] \n";
		}else{
			$qqwherestr.= " OR  zid = $zid[$idx] \n";
		}
	}
	$qqwherestr.= ")\n";
	
	//grab the questions
	$sqlq = "SELECT `questiontxt`, `order`, `qtype`, `qid` FROM `questions`" .$qqwherestr;
	fb($sqlq);
	$qarr = array();
	if ($result = $db->query($sqlq)) 
	{
		$i=0;
		// fetch associative array 
		while ($row = $result->fetch_assoc()) 
		{
			$qarr[$i]=$row;
			foreach($row as $key=>$val){
				$trec[$key]=$val;				
			}
			$qid = $trec['qid'];
			//echo($trec['questiontxt']);
			$qarr[$i]['type'] = 'comprehension';
			$qarr[$i]['rating'] = 5;
			$qarr[$i]['type'] = 'comprehension';

			$sqla = "SELECT `answertxt`, `iscorrect` FROM `qanswers` WHERE `qid` = $qid";
			//fb($sqla);
			if ($aresult = $db->query($sqla)) 
			{
				$aarr= array();
				$j=0;
				// fetch associative array 
				while ($arow = $aresult->fetch_assoc()) 
				{
					foreach($arow as $key=>$val){
						$arec[$key]=$val;		
					}
					//echo($arec['answertxt']);
					$aarr[$j]=$arec;
					$j++;
				}
			}
			$qarr[$i]['answers']=$aarr;
			$i++;
		}
	}
	else {
		fb('ERROR: There was a problem with the query.');
	}
	$sarr=shuffleArray($qarr);//in tm/utility.php
	$numquestions=sizeof($sarr);
	if ($numontest < $numquestions){
		$sarr = array_slice($sarr, 0, $numontest);
	} else {
		$numontest = $numquestions;
	}
	//fb('number of questions is '.$numontest);
	$k=0;
	foreach ($sarr as $ques){
		//fb($ques);
		$ans = $ques['answers'];
		if (sizeof($ans) > $numchoices) {
			$corans = array_slice($ans, 0, 1);
			$incans = array_slice($ans, 1); 
			$incans = shuffleArray($incans);
			$ans = array_merge($corans, array_slice($incans, 0 , $numchoices-1));
			//fb($ans);
		}
		$ans = shuffleArray($ans);
		//fb($ans);	
		$sarr[$k]['answers'] = $ans;
		$k++;
	}
	$numquestions = sizeof($sarr);
	$quarr['meta']['testname']= $testname;
	$quarr['meta']['compques']= $compques;
	$quarr['meta']['maxCchoices']= $numchoices;
	$quarr['meta']['numCquestions']= $numontest;
	$quarr['meta']['totnumquestions'] =$numontest;
	$quarr['data'] = $sarr;
	return $quarr;
}

function vdb2arr($db, $testmeta){
	$testname = $testmeta['testname'];
	$maxchoices = $testmeta['maxchoices'];
	$numontest = $testmeta['numontest'];
	$quesfrom = $testmeta['quesfrom'];
	$choicefrom = $testmeta['choicefrom'];	
	$qwherestr= "";
	$idx=0;
	foreach ($quesfrom as $qsource){
		$qsource = explode(".", $qsource);
		$asource = trim($qsource[0]);
		$loc = trim($qsource[1]);
		$subloc = trim($qsource[2]);
		if ($idx==0)
		{
			$qwherestr.= " WHERE (( sourceID ='".$asource."' AND loc='".$loc."' AND subloc='".$subloc."')\n";
		}else{
			$qwherestr.= " OR ( sourceID ='".$asource."' AND loc='".$loc."' AND subloc='".$subloc."')\n";
		}
		$idx++;
	}
	$qwherestr.= ")\n";

	$cwherestr='';
	$cdx=0;
	foreach ($choicefrom as $csource){
		$csource = trim($csource);
		if ($cdx==0){
			$cwherestr.= " WHERE (sourceID ='".$csource."'";
		}else {
			$cwherestr.= " OR sourceID ='".$csource."'\n";
		}
		$cdx++;
	}
	$cwherestr.= ") \n";

	$sqlQ="SELECT word, did, def, sentence
	FROM vdefs
	LEFT JOIN vwords
	USING ( did )
	LEFT JOIN vcontexts
	USING ( did )
	LEFT JOIN vyour x
	USING ( did )" .$qwherestr ;


	if ($result = $db->query($sqlQ)) 
	{
		$i=0;
		// fetch associative array 
		while ($row = $result->fetch_assoc()) 
		{
			foreach($row as $key=>$val){
				$qrec[$key]=$val;				
			}
			// close the results stream                     
			$qarr[$i]=$qrec;
			$i++;
			//echo $i;
		}
	} else {
		fb('ERROR: There was a problem with the query.');
	}	

	$sqlC = "SELECT word, did, def
		FROM vdefs
		LEFT JOIN vwords
	USING ( did )
	LEFT JOIN vcontexts
	USING ( did )".$cwherestr;

	fb("\n\n" . $sqlQ);
	fb("\n\n" . $sqlC);

	$result->close();
	//print_r($qarr);

	$charr=array();
	if ($result2 = $db->query($sqlC)) 
	{
		$i=0;
		// fetch associative array 
		while ($row = $result2->fetch_assoc()) 
		{
			foreach($row as $key=>$val){
				$chrec[$key]=$val;				
			}
			// close the results stream                     
			$charr[$i]=$chrec;
			$i++;
			//echo $i;
		}
	}else {
		echo 'ERROR: There was a problem with the query2.';
	}
	$result2->close();


	$qarr = shuffleArray($qarr);
	//fb($qarr);
	//fb($charr);

	$lenqarr = sizeof($qarr);

	if ($lenqarr<$numquestions){
		$numquestions = $lenqarr;
	}

	$numchoices= $maxchoices*$numquestions;
	$charr = shuffleArray($charr);
	$lencharr= sizeof($charr);
	if ($lencharr<$numchoices){
		$numneeded = $numchoices - $lencharr;
		$charr = arrary_merge($charr, array_slice($charr,0,$numneeded));
	}

	$qzarr = array();
	$i = 0;
	$u = 0;
	foreach ($qarr as $ques){
		$question['questiontxt'] = "'".$ques['sentence']."' Here, '".$ques['word']."' most closely means:";
		$question['rating'] = 5;
		$question['qtype'] = 'mc';
		$question['qorder'] = 1;
		$question['type'] = 'vocab';
		$question['qid'] = $ques['did'];
		$qzarr[$i] = $question;	
		$did = $ques['did'];
		$ans = array();
		$ans[0]['answertxt'] = $ques['def'];
		$ans[0]['iscorrect'] = 1;
		for ($j=1; $j<=$maxchoices-1; $j++){
			if($charr[$u]['did']==$did){
				$u++;
			}
			$ans[$j]['answertxt'] = $charr[$u]['def'];
			$ans[$j]['iscorrect'] = 0;
			$u++;
		}
		$ans = shuffleArray($ans);
		$qzarr[$i]['answers'] = $ans;
		$i++;  
	}
	$numquestions = sizeof($qzarr);
	fb('numquestions = '.$numquestions);
	fb('numontest = '.$numontest);
	if ($numontest < $numquestions){
		$qzarr = array_slice($qzarr, 0, $numontest);
	} else {
		$numontest = $numquestions;
	}
	$quarr['meta']['testname']= $testname;
	$quarr['meta']['quesfrom']= $quesfrom;
	$quarr['meta']['maxVchoices']= $maxchoices;
	$quarr['meta']['numVquestions']= $numontest;
	$quarr['meta']['totnumquestions'] =$numontest;
	$quarr['data'] = $qzarr;
	return $quarr;
}

function qarr2HtmlQuiz($quarr, $usermeta){
	echo('<html>
	<head>
	<style type="text/css">

	@media only screen and (max-device-width: 480px) {
		body {
		border: 1px solid green;
		max-width: 400px;
		width: 50%;
		}
	}

	</style>

		<script>
		function backButtonOverride()
		{
		  // Work around a Safari bug
		  // that sometimes produces a blank page
		  setTimeout("backButtonOverrideBody()", 1);

		}

		function backButtonOverrideBody()
		{
		  // Works if we backed up to get here
		  try {
		    history.forward();
		  } catch (e) {
		    // OK to ignore
		  }
		  // Every quarter-second, try again. The only
		  // guaranteed method for Opera, Firefox,
		  // and Safari, which dont always call
		  // onLoad but *do* resume any timers when
		  // returning to a page
		  setTimeout("backButtonOverrideBody()", 500);
		}
		</script>
		</head>
		<body onLoad="backButtonOverride()">
		<h3>'.$quarr['meta']['testname'].'</h3>
		<h5>'.date('l jS \of F Y h:i:s A').'</h5>
	<form id="form1" name="Update" method="get" action="../qdb2/qout2grader.php">');

	$m=1;//<body onLoad="backButtonOverride()"> rplace body with this to push forward
	$ai= array("A", "B", "C", "D", "E", "F");

	foreach ($quarr['data'] as $ques){
		echo('<br/>' . $m . ". ". $ques['questiontxt']. '<br/>');
		$k=0;
		foreach ($ques['answers'] as $aans){
			$astr = '<input type="radio" name="'. $m .'" value="'.$aans['iscorrect'] . '" />'. $ai[$k].'. '. $aans['answertxt'] .'<br />';
			echo($astr);
			//fb($astr);
			$k++; 
		}
		$m++;
	}

	echo('<input type="text" name="testid" id="textfield" value="'.$quarr['meta']['tid'].'" size="25"/>
	<input name="" type="submit" value="send" /><small>534'.$tid.'62</small>
	<INPUT TYPE = Hidden NAME = "startdate" VALUE = "'.date('l jS \of F Y h:i:s A').'">
	<INPUT TYPE = Hidden NAME = "numquestions" VALUE = "'.$quarr['meta']['totnumquestions'].'">
	<INPUT TYPE = Hidden NAME = "testname" VALUE = "'.$quarr['meta']['testname'].'">
	<INPUT TYPE = Hidden NAME = "name" VALUE = "'.$usermeta['name'].'">
	<INPUT TYPE = Hidden NAME = "email" VALUE = "'.$usermeta['email'].'">
	<INPUT TYPE = Hidden NAME = "bpsid" VALUE = "'.$usermeta['bpsid'].'">
	<INPUT TYPE = Hidden NAME = "course" VALUE = "'.$usermeta['course'].'">
	<INPUT TYPE = Hidden NAME = "section" VALUE = "'.$usermeta['section'].'">
	</form>
	</body>
	</html>');
}

function qarr2csv($sarr){
	$quizcsv = "";

	$quizcsv .= ("\"==quiz:".$testname."==\",0,4,,,,,,,\n");
	foreach ($sarr as $ques){
		fb('made it into for loop');
		$quizcsv .= ('"' . $ques['questiontxt']. '",');
		//find the number of choices and the correct choice
		$k=1;
		$ansstr = '';
		foreach ($ques['answers'] as $aans){
			if ($aans['iscorrect']==1) {
				$corrans = $k;
			}
			$ansstr.= '"'.$aans['answertxt'].'",';
			$k++; 
		}
		$k--;
		$commas = str_repeat(",", 6-$k);
		$quizcsv .= ($k.','.$corrans.','. $ansstr . $commas." \n");	
	}
	return $quizcsv;
}
?>