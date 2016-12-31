<?php
/*

	[set headers and calculate margins etc for grid based layouts]

*/
/*tell 'em it's css**/
header("Content-Type: text/css");
header("X-Content-Type-Options: nosniff");
/*calculate margins etc depending on variables set*/
$gridvars=explode("-",$_GET['grid']);
$colcount=$gridvars[0];
$margin=$gridvars[1];
$fullwidth=$gridvars[2];
$colwidth=round((100-(($colcount-1)*$margin))/$colcount, 2, PHP_ROUND_HALF_DOWN);
?>