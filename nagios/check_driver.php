#!/usr/bin/php
<?php

include_once( '/opt/simce/index.php' );

global $app;

$khomp = $app['asterisk']->command("khomp summary");

if (preg_match("/Khomp channel driver - ([\d\.\_]+) \- \(rev: (\d+)\)/", $khomp, $match)) {
	print "OK - EBS Driver: {$match[1]} - (rev: {$match[2]})\n";
}

?>
