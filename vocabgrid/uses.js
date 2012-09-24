// stores the reference to the XMLHttpRequest object
var xmlHttp = createXmlHttpRequestObject();
// the name of the XSLT file
var xsltFileUrl = "uses.xsl";
// the file that returns the requested data in XML format
var feedGridUrl = "uses.php";
var phpFileUrl = "uses.php";
// the id of the grid div
var gridDivId = "gridDiv";
var listDivId = "list2Div";
// the grid of the status div
var statusDivId = "statusDiv";
// stores temporary row data
var tempRow;
// the ID of the product being edited
var editableId = null;
// the XSLT document
var stylesheetDoc;
var xslstr;
var jsxmlpage;
var def;
var ctx;
var ytx;

// eveything starts here
function init()
{
	
  // test if user has browser that supports native XSLT functionality
  if(window.XMLHttpRequest && window.XSLTProcessor && window.DOMParser)
  {
    // load the grid
    loadStylesheet();
	//getList();
    //loadGridPage(1);
    return;
  }
  // test if user has Internet Explorer with proper XSLT support
  if (window.ActiveXObject && createMsxml2DOMDocumentObject())
  {
    // load the grid
    loadStylesheet();
	//getList();
    //loadGridPage(1);
    // exit the function
    return;  
  }
  // if browser functionality testing failed, alert the user
  alert("Your browser doesn't support the necessary functionality.");
}

function createMsxml2DOMDocumentObject()
{
  // will store the reference to the MSXML object
  var msxml2DOM;
  // MSXML versions that can be used for our grid
  var msxml2DOMDocumentVersions = new Array("Msxml2.DOMDocument.6.0",
                                            "Msxml2.DOMDocument.5.0",
                                            "Msxml2.DOMDocument.4.0");
  // try to find a good MSXML object
  for (var i=0; i<msxml2DOMDocumentVersions.length && !msxml2DOM; i++) 
  {
    try 
    { 
      // try to create an object
      msxml2DOM = new ActiveXObject(msxml2DOMDocumentVersions[i]);
    } 
    catch (e) {}
  }
  // return the created object or display an error message
  if (!msxml2DOM)
    alert("Please upgrade your MSXML version from \n" + 
          "http://msdn.microsoft.com/XML/XMLDownloads/default.aspx");
  else 
    return msxml2DOM;
}

// creates an XMLHttpRequest instance
function createXmlHttpRequestObject() 
{
  // will store the reference to the XMLHttpRequest object
  var xmlHttp;
 
  // this should work for all browsers except IE6 and older
  try
  {
    // try to create XMLHttpRequest object
    xmlHttp = new XMLHttpRequest();
  }
  catch(e)
  {
    // assume IE6 or older
    var XmlHttpVersions = new Array("MSXML2.XMLHTTP.6.0",
                                    "MSXML2.XMLHTTP.5.0",
                                    "MSXML2.XMLHTTP.4.0",
                                    "MSXML2.XMLHTTP.3.0",
                                    "MSXML2.XMLHTTP",
                                    "Microsoft.XMLHTTP");
    // try every prog id until one works
    for (var i=0; i<XmlHttpVersions.length && !xmlHttp; i++) 
    {
      try 
      { 
        // try to create XMLHttpRequest object
        xmlHttp = new ActiveXObject(XmlHttpVersions[i]);
      } 
      catch (e) {}
    }
  }
  // return the created object or display an error message
  if (!xmlHttp)
    alert("Error creating the XMLHttpRequest object.");
  else 
    return xmlHttp;
}

// loads the stylesheet from the server using a synchronous request
function loadStylesheet()
{
	// load the file from the server
	xmlHttp.open("GET", xsltFileUrl, false);        
	xmlHttp.send(null);    
	sxlstr= xmlHttp.responseText;

	// try to load the XSLT document
	if (this.DOMParser) // browsers with native functionality
	{
		var dp = new DOMParser();
		stylesheetDoc = dp.parseFromString(xmlHttp.responseText, "text/xml");
	} 
	else if (window.ActiveXObject) // Internet Explorer? 
	{
		stylesheetDoc = createMsxml2DOMDocumentObject();         
		stylesheetDoc.async = false;         
		stylesheetDoc.load(xmlHttp.responseXML);
	}
}

// makes asynchronous request to load a new page of the grid
function loadGridPage(pageNo)
{
  // disable edit mode when loading new page
  editableId = false;
  // continue only if the XMLHttpRequest object isn't busy
  if (xmlHttp && (xmlHttp.readyState == 4 || xmlHttp.readyState == 0))
  {
    var query = feedGridUrl + "?action=FEED_GRID_PAGE&page=" + pageNo;
    xmlHttp.open("GET", query, true);
    xmlHttp.onreadystatechange = handleGridPageLoad;
    xmlHttp.send(null);
  }  
}
 
// handle receiving the server response with a new page of products
function handleGridPageLoad()
{
	// when readyState is 4, we read the server response
	if (xmlHttp.readyState == 4)
	{
		// continue only if HTTP status is "OK"
		if (xmlHttp.status == 200)
		{    
			// read the response
			response = xmlHttp.responseText;
			//alert(response);
			// server error?
			if (response.indexOf("ERRNO") >= 0 
			|| response.length == 0
			|| response.indexOf("error") >= 0 )
			{
				// display error message
				alert(response.length == 0 ? "Server serror." : "uses.js line 172" + response);
				// exit function
				//return;
			}
			// the server response in XML format
			xmlResponse = xmlHttp.responseXML;    
			jsxmlpage = jsxml.transReady(xmlResponse, stylesheetDoc);
			//alert(jsxmlpage);
			//alert(xmlResponse);
			// browser with native functionality?    
			if (window.XMLHttpRequest && window.XSLTProcessor && 
				window.DOMParser)
				{      
					// load the XSLT document
					var xsltProcessor = new XSLTProcessor();
					xsltProcessor.importStylesheet(stylesheetDoc);
					// generate the HTML code for the new page of products
					page = xsltProcessor.transformToFragment(xmlResponse, document);
					// display the page of products
			
					var gridDiv = document.getElementById(gridDivId);
					gridDiv.innerHTML = "";
					gridDiv.innerHTML = jsxmlpage;
					//gridDiv.appendChild(jsxmlpage);
				} 
				// Internet Explorer code
				else if (window.ActiveXObject) 
				{
					// load the XSLT document
					var theDocument = createMsxml2DOMDocumentObject();
					theDocument.async = false;
					theDocument.load(xmlResponse);
					// display the page of products
					var gridDiv = document.getElementById(gridDivId);
					gridDiv.innerHTML = theDocument.transformNode(stylesheetDoc);
				}
			} 
			else 
			{          
				alert("Error reading server response.")
			}
		} 
	}

// enters the product specified by id into edit mode if editMode is true,
// and cancels edit mode if editMode is false 
function editId(did, editMode)
{  
  // gets the <tr> element of the table that contains the table
  var productRow = document.getElementById(did).cells;  
  // are we enabling edit mode?
  if(editMode)
  {
    // we can have only one row in edit mode at one time
 
    if(editableId) editId(editableId, false);
    // store current data, in case the user decides to cancel the changes
    save(did);    
    // create editable text boxes
	productRow[1].innerHTML = 
	'<input id="word" class="editword" type="text" name="word" ' + 
	'value="' + productRow[1].innerHTML+'">';   
	productRow[2].innerHTML = '<textarea id="def" rows="4" cols="30" name="def">'+ productRow[2].innerHTML+'</textarea>';
	productRow[3].innerHTML = '<textarea id="sentence" rows="4" cols="50" name="sentence">'+ productRow[3].innerHTML+'</textarea>';
	productRow[4].getElementsByTagName("input")[0].disabled = false;
	productRow[5].innerHTML = '<a href="#" ' + 
	'onclick="updateRow(document.forms.grid_form_id,' + did + ')">Update</a>'+
	'<br/><a href="#" onclick="editId(' + did + ',false)">Cancel</a>'; 
	// save the id of the product being edited
	editableId = did;
  }
  // if disabling edit mode...
  else
  {    
		productRow[1].innerHTML = document.forms.grid_form_id.word.value; 
		productRow[2].innerHTML = document.forms.grid_form_id.def.value;
		productRow[3].innerHTML = document.forms.grid_form_id.sentence.value;
		productRow[4].getElementsByTagName("input")[0].disabled = true;     
		productRow[5].innerHTML = '<a href="#" onclick="editId(' + did + ',true)">Edit</a><br/><a href="#" ' +
		'onclick="deleteId(' + did +')">Delete</a>';
		// no product is being edited    
		editableId = null;
  }
}
function editContext(cid, editMode)
{  
	var contextTr = document.getElementById("ctr"+cid).cells;
	//var sentenceTd = contextTr.getElementsByTagName("td")[0];
	//var theSentence = sentenceTd.firstChild.nodeValue;
	var csentence = contextTr[0].innerHTML;
	//alert("hi nfrom edit context" + cid + contextTr + csentence +contextTr[0].type);


	// gets the <tr> element of the table that contains the table
	ctx = "ctr"+cid;
	var theContext = document.getElementById(ctx).cells;  
	// are we enabling edit mode?
	//alert("elements.length"+theContext.length);
	if(editMode)
	{
		// we can have only one row in edit mode at one time

		if(editableId) editId(editableId, false);
		// store current data, in case the user decides to cancel the changes
		//save(did);    
		// create editable text boxes
		theContext[0].innerHTML ='<textarea id="sentence" rows="2" cols="40" name="sentence">'+ theContext[0].innerHTML+'</textarea>';  
		theContext[1].innerHTML = 
		'<input id="pos" class="editword" type="text" name="pos" ' + 
		'value="' + theContext[1].innerHTML+'">';
		theContext[4].innerHTML = '<a href="#" ' + 
		'onclick="updateContext(document.getElementById(ctx).cells,' + cid + ')">Upd Context</a>';
		editableId = cid;
	}
	// if disabling edit mode...
	else
	{    
		theContext[0].innerHTML = document.getElementById(ctx).cells[0].childNodes[0].value; 
		theContext[1].innerHTML = document.getElementById(ctx).cells[1].childNodes[0].value;  
		theContext[4].innerHTML = '<a href="#" onclick="editContext(' + cid + ',true)">Edit Context</a>';
		// no product is being edited    
		editableId = null;
	}
}
function editYour(yid, editMode)
{  
	//var contextTr = document.getElementById("ctr"+cid).cells;
	//var sentenceTd = contextTr.getElementsByTagName("td")[0];
	//var theSentence = sentenceTd.firstChild.nodeValue;
	//var csentence = contextTr[0].innerHTML;
	//alert("hi nfrom edit context" + cid + contextTr + csentence +contextTr[0].type);


	// gets the <tr> element of the table that contains the table
	ytx = "ytr"+yid;
	//alert("yid is "+ytx);
	var theYour = document.getElementById(ytx).cells;  
	// are we enabling edit mode?
	//alert("elements.length"+theContext.length);
	if(editMode)
	{
		// we can have only one row in edit mode at one time

		if(editableId) editId(editableId, false);
		// store current data, in case the user decides to cancel the changes
		//save(did);    
		// create editable text boxes
		theYour[0].innerHTML ='<textarea id="yourdef" rows="3" cols="30" name="yourdef">'+ theYour[0].innerHTML+'</textarea>';  
		theYour[1].innerHTML ='<textarea id="youruse" rows="3" cols="30" name="youruse">'+ theYour[1].innerHTML+'</textarea>';  
		theYour[2].innerHTML = 
		'<input id="yourID" class="editword" size="10" type="text" name="yourID" ' + 
		'value="' + theYour[2].innerHTML+'">';
		theYour[3].innerHTML = 
		'<input id="block" class="editword" size="1" type="text" name="block" ' + 
		'value="' + theYour[3].innerHTML+'">';
		theYour[4].innerHTML = 
		'<input id="yourOK" class="editword" size="5" type="text" name="yourOK" ' + 
		'value="' + theYour[4].innerHTML+'">';
		theYour[5].innerHTML = 
		'<input id="rating" class="editword" size="5" type="text" name="rating" ' + 
		'value="' + theYour[5].innerHTML+'">';
		theYour[6].innerHTML = '<a href="#" ' + 
		'onclick="updateYour(document.getElementById(ytx).cells,' + yid + ')">Upd Your</a>';
		editableId = yid;
	}
	// if disabling edit mode...
	else
	{    
		theYour[0].innerHTML = document.getElementById(ytx).cells[0].childNodes[0].value; 
		theYour[1].innerHTML = document.getElementById(ytx).cells[1].childNodes[0].value;  
		theYour[2].innerHTML = document.getElementById(ytx).cells[2].childNodes[0].value;  
		theYour[3].innerHTML = document.getElementById(ytx).cells[3].childNodes[0].value;  
		theYour[4].innerHTML = document.getElementById(ytx).cells[4].childNodes[0].value;  
		theYour[5].innerHTML = document.getElementById(ytx).cells[5].childNodes[0].value;  
		theYour[6].innerHTML = '<a href="#" onclick="editYour(' + yid + ',true)">Edit Context</a>';
		// no product is being edited    
		editableId = null;
	}
}
function editDef(did, editMode)
{  
  // gets the <tr> element of the table that contains the table
	dtx = "dtr"+did;
	//alert("did is "+dtx);
	var theDef = document.getElementById(dtx).cells;
  if(editMode)
  {
    // we can have only one row in edit mode at one time
 
    if(editableId) editId(editableId, false);
    // store current data, in case the user decides to cancel the changes
    //save(did);    
    // create editable text boxes
	theDef[0].innerHTML = 
	'<textarea id="def" rows="2" cols="40" name="def">'+ theDef[0].innerHTML +'</textarea>';   
	theDef[2].innerHTML = '<a href="#" onclick="updateDef(document.getElementById(dtx).cells,' + did + ')">Upd Def</a>';
	editableId = did;
  }
  // if disabling edit mode...
  else
  {    
		theDef[0].innerHTML = document.getElementById(dtx).cells[0].childNodes[0].value; 
		theDef[2].innerHTML = '<a href="#" onclick="editDef(' + did + ',true)">Edit Def</a><br/><a href="#" ' +
		'onclick="deleteId(' + did +')">Delet Def</a>';
 		editableId = null;
  }
}


// saves the original product data before editing row
function save(did)
{
  // retrieve the product row
  var tr = document.getElementById(did).cells;
  // save the data
  tempRow = new Array(tr.length); 
  for(var i=0; i<tr.length; i++)   
    tempRow[i] = tr[i].innerHTML;   
}

// saves the original product data before editing row
function saveDef(did)
{
  // retrieve the product row
  var theDef = document.getElementById(did).value;   
}

// cancels editing a row, restoring original values
function undo(did)
{
  // retrieve the product row
  var tr = document.getElementById(did).cells;
  // copy old values
  for(var i=0; i<tempRow.length; i++) 
    tr[i].innerHTML = tempRow[i];
  // no editable row 
  editableId = null;    
}

// update one row in the grid if the connection is clear
function updateRow(grid, did)
{  
  // continue only if the XMLHttpRequest object isn't busy
  if (xmlHttp && (xmlHttp.readyState == 4 || xmlHttp.readyState == 0))
  {  
		var query = feedGridUrl + "?action=UPDATE_ROW&id=" + did + 
		"&" + createUpdateUrl(grid);
		xmlHttp.open("GET", query, true);
		xmlHttp.onreadystatechange = handleUpdatingRow;
		xmlHttp.send(null);
  } 
}
function updateDef(drow, did)
{ 
  // continue only if the XMLHttpRequest object isn't busy
  if (xmlHttp && (xmlHttp.readyState == 4 || xmlHttp.readyState == 0))
  {  
		var query = feedGridUrl + "?action=UPDATE_DEF&did=" + did  + "&" + createUpdateUrl(drow); ; 
		xmlHttp.open("GET", query, true);
		xmlHttp.onreadystatechange = handleUpdatingDef;
		xmlHttp.send(null);
  } 
}
function updateContext(crow, cid)
{ 
	//alert(document.getElementById(ctx).cells[0].childNodes[0].type)
  // continue only if the XMLHttpRequest object isn't busy
  if (xmlHttp && (xmlHttp.readyState == 4 || xmlHttp.readyState == 0))
  {  
		var query = feedGridUrl + "?action=UPDATE_CONTEXT&cid=" + cid + "&" + createUpdateUrl(crow);
		//alert("in update context" + query);
		xmlHttp.open("GET", query, true);
		xmlHttp.onreadystatechange = handleUpdatingContext;
		xmlHttp.send(null);
  } 
}
function updateYour(yrow, yid)
{ 
	//alert(document.getElementById(ctx).cells[0].childNodes[0].type)
	// continue only if the XMLHttpRequest object isn't busy
	if (xmlHttp && (xmlHttp.readyState == 4 || xmlHttp.readyState == 0))
	{  
		var query = feedGridUrl + "?action=UPDATE_YOUR&yid=" + yid + "&" + createUpdateUrl(yrow);
		//alert("in update context" + query);
		xmlHttp.open("GET", query, true);
		xmlHttp.onreadystatechange = handleUpdatingYour;
		xmlHttp.send(null);
	} 
}
function addYour(did, page)
{ 

	//alert("in addYour - did="+did +"page"+page);

	if (xmlHttp && (xmlHttp.readyState == 4 || xmlHttp.readyState == 0))
	{  
		var query = feedGridUrl + "?action=ADD_YOUR&did=" + did +"&page=" + page;
		//alert("in update context" + query);
		xmlHttp.open("GET", query, true);
		xmlHttp.onreadystatechange = handleGridPageLoad;
		xmlHttp.send(null);
	} 

}
function handleAddYour()
{ 
  // when readyState is 4, we read the server response
  if(xmlHttp.readyState == 4)
  {
    // continue only if HTTP status is "OK"
    if(xmlHttp.status == 200)
    {
      // read the response
      response = xmlHttp.responseText;
      // server error?
      if (response.indexOf("ERRNO") >= 0 
          || response.indexOf("error") >= 0
          || response.length == 0)
        alert(response.length == 0 ? "Server serror." : response);
      // if everything went well, cancel edit mode
      else 
        ;
    }
    else 
    {    
      // undo any changes in case of error
      undo(editableId);
      alert("Error on server side.");    
    }
  } 
}
 
// handle receiving a response from the server when updating a product
function handleUpdatingRow()
{ 
  // when readyState is 4, we read the server response
  if(xmlHttp.readyState == 4)
  {
    // continue only if HTTP status is "OK"
    if(xmlHttp.status == 200)
    {
      // read the response
      response = xmlHttp.responseText;
      // server error?
      if (response.indexOf("ERRNO") >= 0 
          || response.indexOf("error") >= 0
          || response.length == 0)
        alert(response.length == 0 ? "Server serror." : response);
      // if everything went well, cancel edit mode
      else 
        editId(editableId, false);
    }
    else 
    {    
      // undo any changes in case of error
      undo(editableId);
      alert("Error on server side.");    
    }
  } 
}
function handleUpdatingDef()
{ 
  // when readyState is 4, we read the server response
  if(xmlHttp.readyState == 4)
  {
    // continue only if HTTP status is "OK"
    if(xmlHttp.status == 200)
    {
      // read the response
      response = xmlHttp.responseText;
      // server error?
      if (response.indexOf("ERRNO") >= 0 
          || response.indexOf("error") >= 0
          || response.length == 0)
        alert(response.length == 0 ? "Server serror." : response);
      // if everything went well, cancel edit mode
      else 
        editDef(editableId, false);
    }
    else 
    {    
      // undo any changes in case of error
      undo(editableId);
      alert("Error on server side.");    
    }
  } 
}
function handleUpdatingContext()
{ 
  // when readyState is 4, we read the server response
  if(xmlHttp.readyState == 4)
  {
    // continue only if HTTP status is "OK"
    if(xmlHttp.status == 200)
    {
      // read the response
      response = xmlHttp.responseText;
      // server error?
      if (response.indexOf("ERRNO") >= 0 
          || response.indexOf("error") >= 0
          || response.length == 0)
        alert(response.length == 0 ? "Server serror." : response);
      // if everything went well, cancel edit mode
      else 
        editContext(editableId, false);
    }
    else 
    {    
      // undo any changes in case of error
      undo(editableId);
      alert("Error on server side.");    
    }
  } 
}
function handleUpdatingYour()
{ 
  // when readyState is 4, we read the server response
  if(xmlHttp.readyState == 4)
  {
    // continue only if HTTP status is "OK"
    if(xmlHttp.status == 200)
    {
      // read the response
      response = xmlHttp.responseText;
      // server error?
      if (response.indexOf("ERRNO") >= 0 
          || response.indexOf("error") >= 0
          || response.length == 0)
        alert(response.length == 0 ? "Server serror." : response);
      // if everything went well, cancel edit mode
      else 
        editYour(editableId, false);
    }
    else 
    {    
      // undo any changes in case of error
      undo(editableId);
      alert("Error on server side.");    
    }
  } 
}
// creates query string parameters for updating a row
function createUpdateUrl(grid)
{
	// initialize query string
	var str = "";
	// build a query string with the values of the editable grid elements
	for(var i=0; i<grid.length; i++){ 
		//alert(grid[i].childNodes[0].type);
		switch(grid[i].childNodes[0].type) 
		{
			case "text": 
			case "textarea":
			str += grid[i].childNodes[0].name + "=" + 
			escape(grid[i].childNodes[0].value) + "&";             
			break;   
			case "checkbox":
			if (!grid[i].childNodes[0].disabled) 
			str += grid[i].childNodes[0].name + "=" + 
			(grid[i].childNodes[0].checked ? 1 : 0) + "&";
			break;
		}
	}
	// return the query string
	return str;
}
function getList() {
	// disable edit mode when loading new page
	// continue only if the XMLHttpRequest object isn't busy
	if (xmlHttp && (xmlHttp.readyState == 4 || xmlHttp.readyState == 0))
	{
		xmlHttp.open('get', phpFileUrl+'?action=GET_LIST');
		xmlHttp.onreadystatechange = handleGetListResponse;
		xmlHttp.send(null);
	}
}

function handleGetListResponse() {
	if(xmlHttp.readyState == 4){
		var response = xmlHttp.responseText;
		//alert(response);
		//document.getElementById('listDiv').innerHTML = response;
		// read the response
		// server error?
		if (response.indexOf("ERRNO") >= 0 || response.indexOf("error") >= 0|| response.length == 0)
		{
			// display error message
			alert(response.length == 0 ? "Server serror." : response);
			// exit function
			return;
		}
		// the server response in XML format
		xmlResponse = xmlHttp.responseXML;
       
		// browser with native functionality?    
		if (window.XMLHttpRequest && window.XSLTProcessor && window.DOMParser)
		{      
			// load the XSLT document
			var xsltProcessor = new XSLTProcessor();
			xsltProcessor.importStylesheet(stylesheetDoc);
			// generate the HTML code for the new page of products
			page = xsltProcessor.transformToFragment(xmlResponse, document);
			// display the page of products
			var listDiv = document.getElementById(listDivId);
			listDiv.innerHTML = "";
			listDiv.appendChild(page);
		} 
		// Internet Explorer code
		else if (window.ActiveXObject) 
		{
			// load the XSLT document
			var theDocument = createMsxml2DOMDocumentObject();
			theDocument.async = false;
			theDocument.load(xmlResponse);
			// display the page of products
			var listDiv = document.getElementById(listDivId);
			listDiv.innerHTML = theDocument.transformNode(stylesheetDoc);
		}
		else 
		{          
			alert("Error reading server response.")
		}
	}
}
function changeSource(source){
	// disable edit mode when loading new page
	editableId = false;
	// continue only if the XMLHttpRequest object isn't busy
	if (xmlHttp && (xmlHttp.readyState == 4 || xmlHttp.readyState == 0))
	{
		var csurl=feedGridUrl + '?action=CHANGE_SOURCE&source='+source;
		xmlHttp.open('get', csurl);
		xmlHttp.onreadystatechange = handleGridPageLoad;
		xmlHttp.send(null);
	}
}
function changeSubSource(source,loc,subloc){
	// disable edit mode when loading new page
	editableId = false;
	// continue only if the XMLHttpRequest object isn't busy
	if (xmlHttp && (xmlHttp.readyState == 4 || xmlHttp.readyState == 0))
	{
		var csurl=feedGridUrl + '?action=CHANGE_SUBSOURCE&source='+source+'&loc='+loc+'&subloc='+subloc;
		xmlHttp.open('get', csurl);
		xmlHttp.onreadystatechange = handleGridPageLoad;
		xmlHttp.send(null);
	}
}
function changeWord(aword){
	// disable edit mode when loading new page
	editableId = false;
	// continue only if the XMLHttpRequest object isn't busy
	if (xmlHttp && (xmlHttp.readyState == 4 || xmlHttp.readyState == 0))
	{
		var csurl=feedGridUrl + '?action=CHANGE_WORD&aword='+aword;
		xmlHttp.open('get', csurl);
		xmlHttp.onreadystatechange = handleGridPageLoad;
		xmlHttp.send(null);
	}
}
function deleteId(did, page)
{  
	// continue only if the XMLHttpRequest object isn't busy
	if (xmlHttp && (xmlHttp.readyState == 4 || xmlHttp.readyState == 0))
	{  
		var query = feedGridUrl + "?action=DELETE_ROW&id=" + did + "&page=" + page;
		xmlHttp.open("GET", query, true);
		xmlHttp.onreadystatechange = handleGridPageLoad;
		xmlHttp.send(null);
	} 
}
function handleDeleteRow()
{
	// when readyState is 4, we read the server response
	if (xmlHttp.readyState == 4)
	{
		// continue only if HTTP status is "OK"
		if (xmlHttp.status == 200)
		{    
			// read the response
			response = xmlHttp.responseText;
			// server error?
			if (response.indexOf("ERRNO") >= 0 
			|| response.indexOf("error") >= 0
			|| response.length == 0)
			{
				// display error message
				alert(response.length == 0 ? "Server serror." : response);
				// exit function
				return;
			}
		}
	}
}
