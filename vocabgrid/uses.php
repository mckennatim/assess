<?php
require_once('../../FirePHPCore/FirePHP.class.php');
require_once('../../FirePHPCore/fb.php');
ob_start(); //gotta have this
session_start();	// This connects to the existing session
session_register ("source");	// Create a session variable called name
session_register ("whereStr");	// Create a session variable called name
session_register ("sourceID");	// Create a session variable called name
session_register ("loc");	// Create a session variable called name
session_register ("subloc");	// Create a session variable called name
session_register ("aword");	
session_register ("record_count");	
// load error handling script and the Grid class
require_once('../js/error_handler.php');
require_once('uses.class.php');

/*howto debug
$firephp = FirePHP::getInstance(true);
$var = array('i'=>10, 'j'=>20);
$do='doggyhit';
$firephp->log($do, 'smarrtest');
$firephp->log($var, 'arr');
fb('why should I');
*/

// the 'action' parameter should be FEED_GRID_PAGE or UPDATE_ROW 
if (!isset($_GET['action']))
{  
	echo 'Server error: client command missing.';
	exit;
}      
else 
{
	// store the action to be performed in the $action variable
	$action = $_GET['action'];
	$grid = new Grid($action);
	if(ob_get_length()) ob_clean();
	// headers are sent to prevent browsers from caching
	header('Expires: Fri, 25 Dec 1980 00:00:00 GMT'); // time in the past
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT'); 
	header('Cache-Control: no-cache, must-revalidate'); 
	header('Pragma: no-cache');
	// generate the output in XML format
	header('Content-type: text/xml'); 
	echo '<?xml version="1.0" encoding="ISO-8859-1"?>';
	echo '<data>';
	echo '<action>' . $action . '</action>';
	echo '<theSource>' . $_SESSION['source'] . '</theSource>';
	echo '<loc>' . $_SESSION['loc'] . '</loc>';
	echo '<subloc>' . $_SESSION['subloc'] . '</subloc>';
	echo '<aword>' . $_SESSION['aword'] . '</aword>';
}

switch($action) {
	case 'UPDATE_ROW':
	// retrieve parameters
	$id = $_GET['id'];
	$word = $_GET['word'];
	$def = $_GET['def'];
	$usage = $_GET['sentence'];
	$ok = $_GET['ok'];
	$grid->phpStr = $_SERVER['QUERY_STRING'];
	// update the record
	$grid->updateRecord($id, $word, $def, $usage, $ok);
	break;
	case 'UPDATE_DEF':
	// retrieve parameters
	$did = $_GET['did'];
	$def = $_GET['def'];
	$grid->phpStr = $_SERVER['QUERY_STRING'];
	// update the record
	$grid->updateDef($did, $def);
	break;
	case 'ADD_YOUR':
	// retrieve parameters
	$did = $_GET['did'];
	$page = $_GET['page'];
	$grid->phpStr = $_SERVER['QUERY_STRING'];
	// update the record
	$grid->addYour($did);
	$grid->readPage($page);
	echo $grid->getGridXML();
	echo $grid->getParamsXML();		
	break;
	case 'UPDATE_CONTEXT':
	// retrieve parameters
	$cid = $_GET['cid'];
	$sentence = $_GET['sentence'];
	$pos = $_GET['pos'];
	$grid->phpStr = $_SERVER['QUERY_STRING'];
	// update the record
	$grid->updateContext($cid, $sentence, $pos);
	break;
	case 'UPDATE_YOUR':
	// retrieve parameters
	$yid = $_GET['yid'];
	$yourdef = $_GET['yourdef'];
	$youruse = $_GET['youruse'];
	$yourID = $_GET['yourID'];
	$block = $_GET['block'];
	$yourOK = $_GET['yourOK'];
	$rating = $_GET['rating'];
	$grid->phpStr = $_SERVER['QUERY_STRING'];
	// update the record
	$grid->updateYour($yid, $yourdef, $youruse, $yourID, $block, $yourOK, $rating);
	break;
	case 'FEED_GRID_PAGE':
	$page = $_GET['page'];
	$grid->whereStr = $_SESSION['whereStr'];
	fb('in FEED' . $grid->whereStr);
	//$grid->setSource($_SESSION['source']);
	$grid->readPage($_GET['page']);
	//echo $test->getSource();
	echo $grid->getGridXML();
	echo $grid->getParamsXML();
	break;
	case 'GET_LIST':
	/* do something */
	$grid->fetchList();
	echo $grid->aListXML();
	break;
	case 'CHANGE_SOURCE':
	$grid->setSource($_GET['source']);
	$grid->readPage(1);
	echo $grid->getGridXML();
	echo $grid->getParamsXML();	
	break;
	case 'CHANGE_SUBSOURCE':
	$grid->setSubSource($_GET['source'], $_GET['loc'], $_GET['subloc']);
	$grid->readPage(1);
	echo $grid->getGridXML();
	echo $grid->getParamsXML();	
	break;
	case 'CHANGE_WORD':
	$grid->setWord($_GET['aword']);
	$grid->readPage(1);
	echo $grid->getGridXML();
	echo $grid->getParamsXML();	
	break;
	case 'DELETE_ROW':
	$id = $_GET['id'];
	$page = $_GET['page'];
	$grid->deleteRecord($id);
	//$grid->setSource($_SESSION['source']); //kind of a kludge
	//not sure why it loses track of the source
	$grid->readPage($page);
	echo $grid->getGridXML();
	echo $grid->getParamsXML();	
	break;
	default:
	echo "you aint got nuthin";
}
echo '</data>';
?>
