<?
include_once('../tm/utility.php');
require_once('../tm/dbinfo.php');
require_once('../../FirePHPCore/FirePHP.class.php');
require_once('../../FirePHPCore/fb.php');
ob_start(); //gotta have this

$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

$sql="SELECT tname, zid FROM quiz";

/* You can add ookrder by clause to the sql statement if the names are to be displayed in alphabetical order */

$result = $db->query($sql);
echo('
<html>
<head>
</head>
<body>
<form id="form1" name="Update" method="get" action="createQuiz.php">
   <select name=testname value="">Student Name</option>"');

while($nt = $result->fetch_assoc()){//Array or records stored in $nt
	//fb($nt);
	echo "<option value=$nt[tname]>$nt[tname]</option>";
	/* Option values are added by looping through the array */
}
echo ('</select>
  <br />
  <label>
  maxchoices: <input type="text" name="maxchoices" id="textfield" size="2" value="4"/>
  </label>
  <label>
  maxquestions: <input type="text" name="maxquestions" id="textfield" size="3" value="20"/>
  </label>
  <br />
<INPUT TYPE=RADIO NAME="quiztype" VALUE="egg">egg<BR>
<INPUT TYPE=RADIO NAME="quiztype" VALUE="html">html<BR>

<INPUT TYPE = Hidden NAME = "name" VALUE = "mrmckt">
<INPUT TYPE = Hidden NAME = "email" VALUE = "mrmckenna@pathboston.com">
<input name="" type="submit" value="send" />
</form>
</body>
</html>
');
?>