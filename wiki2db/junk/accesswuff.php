<?php
$url="http://tim:nji9ol@sitebuilt.net/wuff/index.php?title=Economics&action=raw&ctype=text/javascript";
$text = file($url) or die ("ERROR: Unable to read file");
print_r( $text);
?>