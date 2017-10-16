#!/usr/bin/php
<?php

include_once( '/opt/simce/index.php' );

global $app;

list ( $basename, $link, $label ) = $argv;

$arr = array_pop(\SiMCE\Classes\E1Stats::getByQuery(
	" link = :link ORDER BY DATA DESC LIMIT 1 ",
	array( 'link' => $link )
));

if ($arr['status'] == 0) {
	print "OK - $label {$arr['info']}\n";
	exit(0);
} else {
	print "CRITICAL - $label {$arr['info']}\n";
	exit(2);
}

?>
