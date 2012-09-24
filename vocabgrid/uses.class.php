<?php
// load configuration file
require_once('../tm/dbinfo.php');
define('ROWS_PER_VIEW', 2);
// start session

// includes functionality to manipulate the products list 
class Grid 
{  
	public $whereStr;   
	public $sourceArticle;
	public $locArticle;
	public $sublocArticle;
	// grid pages count
	public $mTotalPages;
	// grid items count
	public $mItemsCount;
	// index of page to be returned
	public $mReturnedPage;    
	// database handler
	private $mMysqli;
	// database handler
	private $grid;
	public $aList;
	public $loc;
	public $subloc;
	public $sourceID;
	public $aword;

	// class constructor
	function __construct() 
	{   
		// create the MySQL connection
		$this->mMysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD,
			DB_DATABASE);
		// call countAllRecords to get the number of grid records
		$this->mItemsCount = $this->countAllRecords();
	}

	// class destructor, closes database connection  
	function __destruct() 
	{
		$this->mMysqli->close();
	}
	// read a page of products and save it to $this->grid
	public function readPage($page)
	{
		// create the SQL query that returns a page of vocab
		$wvocabQuery = 'SELECT word, wid
		FROM vdefs
		LEFT JOIN vwords
		USING ( did )
		LEFT JOIN vcontexts
		USING ( did )';
		$wvocabQuery = $wvocabQuery . $this->whereStr;//$this->getWhereStr();
		$wqueryString = $this->createSubpageQuery($wvocabQuery, $page);
		fb('in readpage word '. $wqueryString);
		// execute the query
		if ($wresult = $this->mMysqli->query($wqueryString)) 
		{
			// fetch associative array 
			while ($wrow = $wresult->fetch_assoc()) 
			{
				// build the XML structure containing words and wid
				$this->grid .= '<words>';
				foreach($wrow as $wname=>$wval)
					$this->grid .= '<' . $wname . '>' . 
					htmlentities($wval) . 
					'</' . $wname . '>';
					$qwid = " AND wid=" . $wrow["wid"];
					fb($qwid . $wrow["word"]);
					$dvocabQuery = 'SELECT def, did
						FROM vdefs
						LEFT JOIN vwords
						USING ( did )
					LEFT JOIN vcontexts
					USING ( did )';
					$dvocabQuery = $dvocabQuery . $this->whereStr . $qwid;
					fb('in readpage did '. $dvocabQuery);
					// execute the query
					if ($dresult = $this->mMysqli->query($dvocabQuery)) 
					{
						// fetch associative array 
						while ($drow = $dresult->fetch_assoc()) 
						{
							// build the XML structure containing products
							$this->grid .= '<defs>';
							foreach($drow as $dname=>$dval)
								$this->grid .= '<' . $dname . '>' . 
								htmlentities($dval) . 
								'</' . $dname . '>';
								$qdid = " AND did=" . $drow["did"];
								$cvocabQuery = 'SELECT sentence, pos, sourceID, cid, loc, subloc
									FROM vdefs
									LEFT JOIN vwords
									USING ( did )
								LEFT JOIN vcontexts
								USING ( did )';
								$cvocabQuery = $cvocabQuery . $this->whereStr . $qdid;
								fb('in readpage context'. $cvocabQuery);
								// execute the query
								if ($cresult = $this->mMysqli->query($cvocabQuery)) 
								{
									// fetch associative array 
									while ($crow = $cresult->fetch_assoc()) 
									{
										// build the XML structure containing products
										$this->grid .= '<contexts>';
										foreach($crow as $cname=>$cval)
											$this->grid .= '<' . $cname . '>' . 
											htmlentities($cval) . 
											'</' . $cname . '>';
										$this->grid .= '</contexts>';   
									}
									// close the results stream                     
									$cresult->close();
								}
								$yvocabQuery = 'SELECT youruse, yourdef, yourID, block, yourOK, rating, yid
									FROM vdefs
									LEFT JOIN vwords
									USING ( did )
									LEFT JOIN vcontexts
									USING ( did )
									LEFT JOIN vyour
									USING ( did )';
								$yvocabQuery = $yvocabQuery . $this->whereStr . $qdid . ' AND yid IS NOT NULL';
								fb('in readpage vyour '. $yvocabQuery);
								// execute the query
								if ($yresult = $this->mMysqli->query($yvocabQuery)) 
								{
									// fetch associative array 
									while ($yrow = $yresult->fetch_assoc()) 
									{
										// build the XML structure containing products
										$this->grid .= '<youruses>';
										foreach($yrow as $yname=>$yval)
											$this->grid .= '<' . $yname . '>' . 
											htmlentities($yval) . 
											'</' . $yname . '>';
										$this->grid .= '</youruses>';   
									}
									// close the results stream                     
									$yresult->close();
								}
							$this->grid .= '</defs>';   
						}
						// close the results stream                     
						$dresult->close();
					}
				$this->grid .= '</words>';   
			}
			// close the results stream                     
			$wresult->close();
		}       
	}

	// update a product
	public function updateRecord($id, $word, $def, $sentence, $ok)
	{
		// escape input data for safely using it in SQL statements
		$id = $this->mMysqli->real_escape_string($id);
		$word = $this->mMysqli->real_escape_string($word);
		$def = $this->mMysqli->real_escape_string($def);
		$sentence = $this->mMysqli->real_escape_string($sentence);
		$ok = $this->mMysqli->real_escape_string($ok);
		// build the SQL query that updates a product record
		$queryVdefs =  "UPDATE vdefs
		SET
			def= '$def'
		WHERE did='$id'";

		$queryVcontexts =  "UPDATE vcontexts
		SET
			sentence='$sentence'	 
		WHERE did='$id'";       
		// execute the SQL command
		$this->mMysqli->query($queryVdefs);  
		$this->mMysqli->query($queryVcontexts) or die("dead since vcontexts aint doi it");  
	}
	public function updateDef($did, $def)
	{
		// escape input data for safely using it in SQL statements
		$did = $this->mMysqli->real_escape_string($did);
		$def = $this->mMysqli->real_escape_string($def);
		$queryVdefs =  "UPDATE vdefs
		SET
			def= '$def'
		WHERE did='$did'";
		$this->mMysqli->query($queryVdefs);  
	}
	public function updateContext($cid, $sentence, $pos)
	{
		// escape input data for safely using it in SQL statements
		$cid = $this->mMysqli->real_escape_string($cid);
		$sentence = $this->mMysqli->real_escape_string($sentence);
		$pos = $this->mMysqli->real_escape_string($pos);
		$queryVdefs =  "UPDATE vcontexts
		SET
			sentence= '$sentence',
			pos= '$pos'
		WHERE cid='$cid'";
		$this->mMysqli->query($queryVdefs);  
	}
	public function updateYour($yid, $yourdef, $youruse, $yourID, $block, $yourOK, $rating)
	{
		// escape input data for safely using it in SQL statements
		$yid = $this->mMysqli->real_escape_string($yid);
		$yourdef = $this->mMysqli->real_escape_string($yourdef);
		$youruse = $this->mMysqli->real_escape_string($youruse);
		$yourID = $this->mMysqli->real_escape_string($yourID);
		$block = $this->mMysqli->real_escape_string($block);
		$yourOK = $this->mMysqli->real_escape_string($yourOK);
		$rating = $this->mMysqli->real_escape_string($rating);
		$queryVdefs =  "UPDATE vyour
		SET
			yourdef= '$yourdef',
			youruse= '$youruse',
			yourID= '$yourID',
			block= '$block',
			yourOK= '$yourOK',
			rating= '$rating'
		WHERE yid='$yid'";
		fb(" in updte your " + $queryVdefs);
		$this->mMysqli->query($queryVdefs);  
	}
	public function addYour($did)
	{
		// escape input data for safely using it in SQL statements
		$did = $this->mMysqli->real_escape_string($did);
		$yourdef = "the meaning, in your own words";
		$youruse = "A sentence where you use the new word as part of the way you would normally speak";
		$yourID = "your username";
		$yourOK = 0;
		$rating = 2;
		$queryVdefs =  "INSERT INTO `vyour` (`yourdef`, `youruse`, `yourID`, `yourOK`, `rating`, `did`) 
		VALUES(
			'$yourdef',
			'$youruse',
			'$yourID',
			'$yourOK',
			'$rating',
			'$did')";
		$this->mMysqli->query($queryVdefs);
		fb("in addYour query is " . $queryVdefs);
		unset($_SESSION['record_count']);
		$this->whereStr = $_SESSION['whereStr'];
		$this->mItemsCount = $this->countAllRecords();
	}
	// returns data about the current request (number of grid pages, etc)
	public function getParamsXML()
	{ 
		// calculate the previous page number
		$previous_page = 
			($this->mReturnedPage == 1) ? '' : $this->mReturnedPage-1;    
		// calculate the next page number
		$next_page = ($this->mTotalPages == $this->mReturnedPage) ? 
			'' : $this->mReturnedPage + 1; 
		// return the parameters
		return '<params>' .
			'<returned_page>' . $this->mReturnedPage . '</returned_page>'.
			'<total_pages>' . $this->mTotalPages . '</total_pages>'.
			'<items_count>' . $this->mItemsCount . '</items_count>'.
			'<previous_page>' . $previous_page . '</previous_page>'.
			'<next_page>' . $next_page . '</next_page>' .
			'</params>';
	}

	// returns the current grid page in XML format
	public function getGridXML()
	{
		return '<grid>' . $this->grid . '</grid>';
	} 

	// returns the total number of records for the grid
	private function countAllRecords()
	{
		// if the record count isn't already cached in the session, 	
		if (!isset($_SESSION['record_count'])) 
		{
			// the query that returns the record count
			$count_query = 'SELECT COUNT(*)
			FROM vdefs
			LEFT JOIN vwords
			USING ( did )
			LEFT JOIN vcontexts
			USING ( did )';
			$count_query = $count_query . $this->whereStr;//$this->getWhereStr();
			fb("in countAllRecords" . $count_query);
			// execute the query and fetch the result 
			if ($result = $this->mMysqli->query($count_query)) 
			{
				// retrieve the first returned row
				$row = $result->fetch_row(); 
				// retrieve the first column of the first row (it represents the 
					$_SESSION['record_count'] = $row[0];
					// close the database handle
					$result->close();
				}
			}    
			// read the record count from the session and return it
			return $_SESSION['record_count'];
	}         

// receives a SELECT query that returns all products and modifies it
// to return only a page of products
	private function createSubpageQuery($queryString, $pageNo) 
	{
		// if we have few products then we don't implement pagination  
		if ($this->mItemsCount <= ROWS_PER_VIEW) 
		{
			$pageNo = 1;
			$this->mTotalPages = 1;
		}
		// else we calculate number of pages and build new SELECT query
		else 
		{
			$this->mTotalPages = ceil($this->mItemsCount / ROWS_PER_VIEW);
			$start_page = ($pageNo - 1) * ROWS_PER_VIEW;   
			$queryString .= ' LIMIT ' . $start_page . ',' . ROWS_PER_VIEW;
		}
		// save the number of the returned page
		$this->mReturnedPage = $pageNo;
		// returns the new query string
		return $queryString;
	} 
	
	public function fetchList(){
		$sql = 'SELECT sourceID FROM sources '; 
		if ($result = $this->mMysqli->query($sql))
		{
			// fetch associative array 
			while ($row = $result->fetch_assoc()){
				$this->aList .= '<article><source>' . $row['sourceID'] .'</source></article>';
			}                    
			$result->close();
		}	
	}
	public function aListXML(){
		return '<aList>' . $this->aList . '</aList>';
	}
	public function setSource($source){
		$this->sourceArticle = $source;
		$_SESSION['source'] = $source;
		$this->whereStr = " WHERE sourceID='$source'";
		//$this->setWhereStr($this->whereStr);
		$_SESSION['whereStr'] = $this->whereStr;
		unset($_SESSION['record_count']);
		$this->mItemsCount = $this->countAllRecords();
	}
	public function setSubSource($source, $loc, $subloc){
		$this->sourceArticle = $source;
		$_SESSION['source'] = $source;
		$this->locArticle = $loc;
		$_SESSION['loc'] = $loc;
		$this->sublocArticle = $subloc;
		$_SESSION['subloc'] = $subloc;
		$this->whereStr = " WHERE sourceID='$source' AND loc='$loc' AND subloc='$subloc'";
		//$this->setWhereStr($this->whereStr);
		$_SESSION['whereStr'] = $this->whereStr;
		unset($_SESSION['record_count']);
		$this->mItemsCount = $this->countAllRecords();
	}
	public function setWord($aword){
		$this->aword = $aword;
		$_SESSION['aword'] = $aword;
		$this->whereStr = " WHERE word='$aword'";
		//$this->setWhereStr($this->whereStr);
		$_SESSION['whereStr'] = $this->whereStr;
		unset($_SESSION['record_count']);
		$this->mItemsCount = $this->countAllRecords();
	}
	public function deleteRecord($id){
		$sql = "DELETE FROM `vdefs` WHERE `did` = '$id'";
		$this->mMysqli->query($sql);
		unset($_SESSION['record_count']);
		$this->whereStr = $_SESSION['whereStr'];
		$this->mItemsCount = $this->countAllRecords();
	}
	public function setWhereStr($whereStr){
		$query="UPDATE session SET whereStr=$whereStr  WHERE ssid=1 limit 1";
		$result = $this->mMysqli->query($query) or fb("Dead session");
	}
	public function getWhereStr(){
		$query="SELECT whereStr FROM session WHERE ssid=1 limit 1";
		$result = $this->mMysqli->query($query) or fb("Dead finding session");
		$row = $result->fetch_row;
		return $row->whereStr;
	}
// end class Grid
} 
?>
