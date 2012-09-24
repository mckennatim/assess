<?php
    $output = "Clean this copy of invalid non ASCII äócharacters.";
    $output = preg_replace('/[^(\x20-\x7F)]*/','', $output);
    echo($output);

$string=preg_replace('/[^(\037-\127)]*$/','', $string);

function removeNonAscii($string) {
return preg_replace('/[^\x20-\x7f]/','',$string);
}
?>