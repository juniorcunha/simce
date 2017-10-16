#!/usr/bin/php
<?php

include_once( '/opt/simce/index.php' );

global $app;

$khomp = $app['asterisk']->command("khomp summary");

if (preg_match("/Status: (\S+)\s+\|/", $khomp, $match)) {
	print "OK - EBS Status: {$match[1]}\n";
	if ($match[1] != 'UP')
		exit(2);
}

?>
